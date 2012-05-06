# Licensed to the Apache Software Foundation (ASF) under one or more
# contributor license agreements.  See the NOTICE file distributed with
# this work for additional information regarding copyright ownership.
# The ASF licenses this file to You under the Apache License, Version 2.0
# (the "License"); you may not use this file except in compliance with
# the License.  You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
package Apache::TestRun;

use strict;
use warnings FATAL => 'all';

use Apache::Test ();
use Apache::TestMM ();
use Apache::TestConfig ();
use Apache::TestConfigC ();
use Apache::TestRequest ();
use Apache::TestHarness ();
use Apache::TestTrace;

use Cwd;
use ExtUtils::MakeMaker;
use File::Find qw(finddepth);
use File::Path;
use File::Spec::Functions qw(catfile catdir canonpath);
use File::Basename qw(basename dirname);
use Getopt::Long qw(GetOptions);
use Config;

use constant IS_APACHE_TEST_BUILD => Apache::TestConfig::IS_APACHE_TEST_BUILD;

use constant STARTUP_TIMEOUT => 300; # secs (good for extreme debug cases)

use subs qw(exit_shell exit_perl);

my $orig_command;
my $orig_cwd;
my $orig_conf_opts;

my %core_files  = ();
my %original_t_perms = ();

my @std_run      = qw(start-httpd run-tests stop-httpd);
my @others       = qw(verbose configure clean help ssl http11 bugreport
                      save no-httpd one-process);
my @flag_opts    = (@std_run, @others);
my @string_opts  = qw(order trace);
my @ostring_opts = qw(proxy ping);
my @debug_opts   = qw(debug);
my @num_opts     = qw(times);
my @list_opts    = qw(preamble postamble breakpoint);
my @hash_opts    = qw(header);
my @help_opts    = qw(clean help);
my @request_opts = qw(get post head);

my @exit_opts_no_need_httpd = (@help_opts);
my @exit_opts_need_httpd    = (@debug_opts, qw(ping));

my %usage = (
   'start-httpd'     => 'start the test server',
   'run-tests'       => 'run the tests',
   'times=N'         => 'repeat the tests N times',
   'order=mode'      => 'run the tests in one of the modes: ' .
                        '(repeat|rotate|random|SEED)',
   'stop-httpd'      => 'stop the test server',
   'no-httpd'        => 'run the tests without configuring or starting httpd',
   'verbose[=1]'     => 'verbose output',
   'configure'       => 'force regeneration of httpd.conf ' .
                        ' (tests will not be run)',
   'clean'           => 'remove all generated test files',
   'help'            => 'display this message',
   'bugreport'       => 'print the hint how to report problems',
   'preamble'        => 'config to add at the beginning of httpd.conf',
   'postamble'       => 'config to add at the end of httpd.conf',
   'ping[=block]'    => 'test if server is running or port in use',
   'debug[=name]'    => 'start server under debugger name (gdb, ddd, etc.)',
   'breakpoint=bp'   => 'set breakpoints (multiply bp can be set)',
   'header'          => "add headers to (" .
                         join('|', @request_opts) . ") request",
   'http11'          => 'run all tests with HTTP/1.1 (keep alive) requests',
   'ssl'             => 'run tests through ssl',
   'proxy'           => 'proxy requests (default proxy is localhost)',
   'trace=T'         => 'change tracing default to: warning, notice, ' .
                        'info, debug, ...',
   'save'            => 'save test paramaters into Apache::TestConfigData',
   'one-process'     => 'run the server in single process mode',
   (map { $_, "\U$_\E url" } @request_opts),
);

sub fixup {
    #make sure we use an absolute path to perl
    #else Test::Harness uses the perl in our PATH
    #which might not be the one we want
    $^X = $Config{perlpath} unless -e $^X;
}

# if the test suite was aborted because of a user-error we don't want
# to call the bugreport and invite users to submit a bug report -
# after all it's a user error. but we still want the program to fail,
# so raise this flag in such a case.
my $user_error = 0;
sub user_error {
    my $self = shift;
    $user_error = shift if @_;
    $user_error;
}

sub new {
    my $class = shift;

    my $self = bless {
        tests => [],
        @_,
    }, $class;

    $self->fixup;

    $self;
}

#split arguments into test files/dirs and options
#take extra care if -e, the file matches /\.t$/
#                if -d, the dir contains .t files
#so we dont slurp arguments that are not tests, example:
# httpd $HOME/apache-2.0/bin/httpd

sub split_test_args {
    my($self) = @_;

    my(@tests);
    my $top_dir = $self->{test_config}->{vars}->{top_dir};
    my $t_dir = $self->{test_config}->{vars}->{t_dir};

    my $argv = $self->{argv};
    my @leftovers = ();
    for (@$argv) {
        my $arg = $_;
        # need the t/ (or t\) for stat-ing, but don't want to include
        # it in test output
        $arg =~ s@^(?:\.[\\/])?t[\\/]@@;
        my $file = catfile $t_dir, $arg;
        if (-d $file and $_ ne '/') {
            my @files = <$file/*.t>;
            my $remove = catfile $top_dir, "";
            if (@files) {
                push @tests, map { s,^\Q$remove,,; $_ } @files;
                next;
            }
        }
        else {
            if ($file =~ /\.t$/ and -e $file) {
                push @tests, "t/$arg";
                next;
            }
            elsif (-e "$file.t") {
                push @tests, "t/$arg.t";
                next;
            }
            elsif (/^[\d.]+$/) {
                my @t = $_;
                #support range of subtests: t/TEST t/foo/bar 60..65
                if (/^(\d+)\.\.(\d+)$/) {
                    @t =  $1..$2;
                }

                push @{ $self->{subtests} }, @t;
                next;
            }
        }
        push @leftovers, $_;
    }

    $self->{tests} = [ map { canonpath($_) } @tests ];
    $self->{argv}  = \@leftovers;
}

sub die_on_invalid_args {
    my($self) = @_;

    # at this stage $self->{argv} should be empty
    my @invalid_argv = @{ $self->{argv} };
    if (@invalid_argv) {
        error "unknown opts or test names: @invalid_argv\n" .
            "-help will list options\n";
        exit_perl 0;
    }

}

sub passenv {
    my $passenv = Apache::TestConfig->passenv;
    for (keys %$passenv) {
        return 1 if $ENV{$_};
    }
    0;
}

sub getopts {
    my($self, $argv) = @_;

    local *ARGV = $argv;
    my(%opts, %vopts, %conf_opts);

    # a workaround to support -verbose and -verbose=0|1
    # $Getopt::Long::VERSION > 2.26 can use the "verbose:1" rule
    # but we have to support older versions as well
    @ARGV = grep defined,
        map {/-verbose=(\d)/ ? ($1 ? '-verbose' : undef) : $_ } @ARGV;

    # permute      : optional values can come before the options
    # pass_through : all unknown things are to be left in @ARGV
    Getopt::Long::Configure(qw(pass_through permute));

    # grab from @ARGV only the options that we expect
    GetOptions(\%opts, @flag_opts, @help_opts,
               (map "$_:s", @debug_opts, @request_opts, @ostring_opts),
               (map "$_=s", @string_opts),
               (map "$_=i", @num_opts),
               (map { ("$_=s", $vopts{$_} ||= []) } @list_opts),
               (map { ("$_=s", $vopts{$_} ||= {}) } @hash_opts));

    $opts{$_} = $vopts{$_} for keys %vopts;

    # separate configuration options and test files/dirs
    my $req_wanted_args = Apache::TestRequest::wanted_args();
    my @argv = ();
    my %req_args = ();

    while (@ARGV) {
        my $val = shift @ARGV;
        if ($val =~ /^--?(.+)/) { # must have a leading - or --
            my $key = lc $1;
            # a known config option?
            if (exists $Apache::TestConfig::Usage{$key}) {
                $conf_opts{$key} = shift @ARGV;
                next;
            } # a TestRequest config option?
            elsif (exists $req_wanted_args->{$key}) {
                $req_args{$key} = shift @ARGV;
                next;
            }
        }
        # to be processed later
        push @argv, $val;
    }

    # save the orig args (make a deep copy)
    $orig_conf_opts = { %conf_opts };

    # fixup the filepath options on win32 (spaces, short names, etc.)
    if (Apache::TestConfig::WIN32) {
        for my $key (keys %conf_opts) {
            next unless Apache::TestConfig::conf_opt_is_a_filepath($key);
            next unless -e $conf_opts{$key};
            $conf_opts{$key} = Win32::GetShortPathName($conf_opts{$key});
        }
    }

    $opts{req_args} = \%req_args;

    # only test files/dirs if any at all are left in argv
    $self->{argv} = \@argv;

    # force regeneration of httpd.conf if commandline args want to
    # modify it. configure_opts() has more checks to decide whether to
    # reconfigure or not.
    # XXX: $self->passenv() is already tested in need_reconfiguration()
    $self->{reconfigure} = $opts{configure} ||
      (grep { $opts{$_}->[0] } qw(preamble postamble)) ||
        (grep { $Apache::TestConfig::Usage{$_} } keys %conf_opts ) ||
          $self->passenv() || (! -e 't/conf/httpd.conf');

    if (exists $opts{debug}) {
        $opts{debugger} = $opts{debug};
        $opts{debug} = 1;
    }

    if ($opts{trace}) {
        my %levels = map {$_ => 1} @Apache::TestTrace::Levels;
        if (exists $levels{ $opts{trace} }) {
            $Apache::TestTrace::Level = $opts{trace};
            # propogate the override for the server-side.
            # -trace overrides any previous APACHE_TEST_TRACE_LEVEL settings
            $ENV{APACHE_TEST_TRACE_LEVEL} = $opts{trace};
        }
        else {
            error "unknown trace level: $opts{trace}",
                "valid levels are: @Apache::TestTrace::Levels";
            exit_perl 0;
        }
    }

    # breakpoint automatically turns the debug mode on
    if (@{ $opts{breakpoint} }) {
        $opts{debug} ||= 1;
    }

    if ($self->{reconfigure}) {
        $conf_opts{save} = 1;
        delete $self->{reconfigure};
    }
    else {
        $conf_opts{thaw} = 1;
    }

    #propagate some values
    for (qw(verbose)) {
        $conf_opts{$_} = $opts{$_};
    }

    $self->{opts} = \%opts;
    $self->{conf_opts} = \%conf_opts;
}

sub default_run_opts {
    my $self = shift;
    my($opts, $tests) = ($self->{opts}, $self->{tests});

    unless (grep { exists $opts->{$_} } @std_run, @request_opts) {
        if (@$tests && $self->{server}->ping) {
            # if certain tests are specified and server is running,
            # dont restart
            $opts->{'run-tests'} = 1;
        }
        else {
            #default is start-server run-tests stop-server
            $opts->{$_} = 1 for @std_run;
        }
    }

    $opts->{'run-tests'} ||= @$tests;
}

my $parent_pid = $$;
sub is_parent { $$ == $parent_pid }

my $caught_sig_int = 0;

sub install_sighandlers {
    my $self = shift;

    my($server, $opts) = ($self->{server}, $self->{opts});

    $SIG{__DIE__} = sub {
        return unless $_[0] =~ /^Failed/i; #dont catch Test::ok failures

        # _show_results() calls die() under a few conditions, such as
        # when no tests are run or when tests fail.  make sure the message
        # is propagated back to the user.
        print $_[0] if (caller(1))[3]||'' eq 'Test::Harness::_show_results';

        $server->stop(1) if $opts->{'start-httpd'};
        $server->failed_msg("error running tests");
        exit_perl 0;
    };

    $SIG{INT} = sub {
        if ($caught_sig_int++) {
            warning "\ncaught SIGINT";
            exit_perl 0;
        }
        warning "\nhalting tests";
        $server->stop if $opts->{'start-httpd'};
        exit_perl 0;
    };

    #try to make sure we scan for core no matter what happens
    #must eval "" to "install" this END block, otherwise it will
    #always run, a subclass might not want that
    eval 'END {
        return unless is_parent(); # because of fork
        $self ||=
            Apache::TestRun->new(test_config => Apache::TestConfig->thaw);
        {
            local $?; # preserve the exit status
            eval {
               $self->scan_core;
            };
        }
        $self->try_bug_report();
    }';
    die "failed: $@" if $@;

}

sub try_bug_report {
    my $self = shift;
    if ($? && !$self->user_error &&
        $self->{opts}->{bugreport} && $self->can('bug_report')) {
        $self->bug_report;
    }
}

#throw away cached config and start fresh
sub refresh {
    my $self = shift;
    $self->opt_clean(1);
    $self->{conf_opts}->{save} = delete $self->{conf_opts}->{thaw} || 1;
    $self->{test_config} = $self->new_test_config()->httpd_config;
    $self->{test_config}->{server}->{run} = $self;
    $self->{server} = $self->{test_config}->server;
}

sub configure_opts {
    my $self = shift;
    my $save = shift;
    my $refreshed = 0;

    my($test_config, $opts) = ($self->{test_config}, $self->{opts});

    $test_config->{vars}->{scheme} =
      $opts->{ssl} ? 'https' :
        $self->{conf_opts}->{scheme} || 'http';

    if ($opts->{http11}) {
        $ENV{APACHE_TEST_HTTP11} = 1;
    }

    # unless we are already reconfiguring, check for .conf.in files changes
    if (!$$save &&
        (my @reasons =
         $self->{test_config}->need_reconfiguration($self->{conf_opts}))) {
        warning "forcing re-configuration:";
        warning "\t- $_." for @reasons;
        unless ($refreshed) {
            $self->refresh;
            $refreshed = 1;
            $test_config = $self->{test_config};
        }
    }

    # unless we are already reconfiguring, check for -proxy
    if (!$$save && exists $opts->{proxy}) {
        my $max = $test_config->{vars}->{maxclients};
        $opts->{proxy} ||= 'on';

        #if config is cached and MaxClients == 1, must reconfigure
        if (!$$save and $opts->{proxy} eq 'on' and $max == 1) {
            $$save = 1;
            warning "server is reconfigured for proxy";
            unless ($refreshed) {
                $self->refresh;
                $refreshed = 1;
                $test_config = $self->{test_config};
            }
        }

        $test_config->{vars}->{proxy} = $opts->{proxy};
    }
    else {
        $test_config->{vars}->{proxy} = 'off';
    }

    return unless $$save;

    my $preamble  = sub { shift->preamble($opts->{preamble}) };
    my $postamble = sub { shift->postamble($opts->{postamble}) };

    $test_config->preamble_register($preamble);
    $test_config->postamble_register($postamble);
}

sub pre_configure { }

sub configure {
    my $self = shift;

    if ($self->{opts}->{'no-httpd'}) {
        warning "skipping httpd configuration";
        return;
    }

    # create the conf dir as early as possible
    $self->{test_config}->prepare_t_conf();

    my $save = \$self->{conf_opts}->{save};
    $self->configure_opts($save);

    my $config = $self->{test_config};
    unless ($$save) {
        my $addr = \$config->{vars}->{remote_addr};
        my $remote_addr = $config->our_remote_addr;
        unless ($$addr eq $remote_addr) {
            warning "local ip address has changed, updating config cache";
            $$addr = $remote_addr;
        }
        #update minor changes to cached config
        #without complete regeneration
        #for example this allows switching between
        #'t/TEST' and 't/TEST -ssl'
        $config->sync_vars(qw(scheme proxy remote_addr));
        return;
    }

    my $test_config = $self->{test_config};
    $test_config->sslca_generate;
    $test_config->generate_ssl_conf if $self->{opts}->{ssl};
    $test_config->cmodules_configure;
    $test_config->generate_httpd_conf;
    $test_config->save;

    # custom config save if
    # 1) requested to save
    # 2) no saved config yet
    if ($self->{opts}->{save} or
        !Apache::TestConfig::custom_config_exists()) {
        $test_config->custom_config_save($self->{conf_opts});
    }
}

sub try_exit_opts {
    my $self = shift;
    my @opts = @_;

    for (@opts) {
        next unless exists $self->{opts}->{$_};
        my $method = "opt_$_";
        my $rc = $self->$method();
        exit_perl $rc if $rc;
    }

    if ($self->{opts}->{'stop-httpd'}) {
        my $ok = 1;
        if ($self->{server}->ping) {
            $ok = $self->{server}->stop;
            $ok = $ok < 0 ? 0 : 1; # adjust to 0/1 logic
        }
        else {
            warning "server $self->{server}->{name} is not running";
            # cleanup a stale pid file if found
            my $pid_file  = $self->{test_config}->{vars}->{t_pid_file};
            unlink $pid_file if -e $pid_file;
        }
        exit_perl $ok;
    }
}

sub start {
    my $self = shift;

    my $opts = $self->{opts};
    my $server = $self->{server};

    #if t/TEST -d is running make sure we don't try to stop/start the server
    my $file = $server->debugger_file;
    if (-e $file and $opts->{'start-httpd'}) {
        if ($server->ping) {
            warning "server is running under the debugger, " .
                "defaulting to -run";
            $opts->{'start-httpd'} = $opts->{'stop-httpd'} = 0;
        }
        else {
            warning "removing stale debugger note: $file";
            unlink $file;
        }
    }

    $self->adjust_t_perms();

    if ($opts->{'start-httpd'}) {
        exit_perl 0 unless $server->start;
    }
    elsif ($opts->{'run-tests'}) {
        my $is_up = $server->ping
            || (exists $self->{opts}->{ping}
                && $self->{opts}->{ping}  eq 'block'
                && $server->wait_till_is_up(STARTUP_TIMEOUT));
        unless ($is_up) {
            error "server is not ready yet, try again.";
            exit_perl 0;
        }
    }
}

sub run_tests {
    my $self = shift;

    my $test_opts = {
        verbose => $self->{opts}->{verbose},
        tests   => $self->{tests},
        times   => $self->{opts}->{times},
        order   => $self->{opts}->{order},
        subtests => $self->{subtests} || [],
    };

    if (grep { exists $self->{opts}->{$_} } @request_opts) {
        run_request($self->{test_config}, $self->{opts});
    }
    else {
        Apache::TestHarness->run($test_opts)
            if $self->{opts}->{'run-tests'};
    }
}

sub stop {
    my $self = shift;

    $self->restore_t_perms;

    return $self->{server}->stop if $self->{opts}->{'stop-httpd'};
}

sub new_test_config {
    my $self = shift;

    Apache::TestConfig->new($self->{conf_opts});
}

sub set_ulimit_via_sh {
    return if Apache::TestConfig::WINFU;
    return if $ENV{APACHE_TEST_ULIMIT_SET};

    # only root can allow unlimited core dumps on Solaris (8 && 9?)
    if (Apache::TestConfig::SOLARIS) {
        my $user = getpwuid($>) || '';
        if ($user ne 'root') {
            warning "Skipping 'set unlimited ulimit for coredumps', " .
                "since we are running as a non-root user on Solaris";
            return;
        }
    }

    my $binsh = '/bin/sh';
    return unless -e $binsh;
    $ENV{APACHE_TEST_ULIMIT_SET} = 1;

    my $sh = Symbol::gensym();
    open $sh, "echo ulimit -a | $binsh|" or die;
    local $_;
    while (<$sh>) {
        if (/^core.*unlimited$/) {
            #already set to unlimited
            $ENV{APACHE_TEST_ULIMIT_SET} = 1;
            return;
        }
    }
    close $sh;

    $orig_command = "ulimit -c unlimited; $orig_command";
    warning "setting ulimit to allow core files\n$orig_command";
    # use 'or die' to avoid warnings due to possible overrides of die
    exec $orig_command or die "exec $orig_command has failed";
}

sub set_ulimit {
    my $self = shift;
    #return if $self->set_ulimit_via_bsd_resource;
    eval { $self->set_ulimit_via_sh };
}

sub set_env {
    #export some environment variables for t/modules/env.t
    #(the values are unimportant)
    $ENV{APACHE_TEST_HOSTNAME} = 'test.host.name';
    $ENV{APACHE_TEST_HOSTTYPE} = 'z80';
}

sub run {
    my $self = shift;

    # assuming that test files are always in the same directory as the
    # driving script, make it possible to run the test suite from any place
    # use a full path, which will work after chdir (e.g. ./TEST)
    $0 = File::Spec->rel2abs($0);
    if (-e $0) {
        my $top = dirname dirname $0;
        chdir $top if $top and -d $top;
    }

    # reconstruct argv, preserve multiwords args, eg 'PerlTrace all'
    my $argv = join " ", map { /^-/ ? $_ : qq['$_'] } @ARGV;
    $orig_command = "$^X $0 $argv";
    $orig_cwd = Cwd::cwd();
    $self->set_ulimit;
    $self->set_env; #make sure these are always set

    $self->detect_relocation($orig_cwd);

    my(@argv) = @_;

    $self->getopts(\@argv);

    # must be called after getopts so the tracing will be set right
    Apache::TestConfig::custom_config_load();

    $self->pre_configure();

    # can't setup the httpd-specific parts of the config object yet
    $self->{test_config} = $self->new_test_config();

    $self->warn_core();

    # give TestServer access to our runtime configuration directives
    # so we can tell the server stuff if we need to
    $self->{test_config}->{server}->{run} = $self;

    $self->{server} = $self->{test_config}->server;

    local($SIG{__DIE__}, $SIG{INT});
    $self->install_sighandlers;

    $self->try_exit_opts(@exit_opts_no_need_httpd);

    # httpd is found here (unless it was already configured before)
    $self->{test_config}->httpd_config();

    $self->try_exit_opts(@exit_opts_need_httpd);

    if ($self->{opts}->{configure}) {
        warning "cleaning out current configuration";
        $self->opt_clean(1);
    }

    # if configure() fails for some reason before it has flushed the
    # config to a file, save it so -clean will be able to clean
    unless ($self->{opts}->{clean}) {
        eval { $self->configure };
        if ($@) {
            error "configure() has failed:\n$@";
            warning "forcing Apache::TestConfig object save";
            $self->{test_config}->save;
            warning "run 't/TEST -clean' to clean up before continuing";
            exit_perl 0;
        }
    }

    if ($self->{opts}->{configure}) {
        warning "reconfiguration done";
        exit_perl 1;
    }

    $self->default_run_opts;

    $self->split_test_args;

    $self->die_on_invalid_args;

    $self->start unless $self->{opts}->{'no-httpd'};

    $self->run_tests;

    $self->stop unless $self->{opts}->{'no-httpd'};
}

sub rerun {
    my $vars = shift;

    # in %$vars
    # - httpd will be always set
    # - apxs is optional

    $orig_cwd ||= Cwd::cwd();
    chdir $orig_cwd;
    my $new_opts = " -httpd $vars->{httpd}";
    $new_opts .= " -apxs $vars->{apxs}" if $vars->{apxs};

    my $new_command = $orig_command;

    # strip any old bogus -httpd/-apxs
    $new_command =~ s/--?httpd\s+$orig_conf_opts->{httpd}//
        if $orig_conf_opts->{httpd};
    $new_command =~ s/--?httpd\s+$orig_conf_opts->{httpd}//
        if $orig_conf_opts->{httpd} and $vars->{apxs};

    # add new opts
    $new_command .= $new_opts;

    warning "running with new config opts: $new_command";

    # use 'or die' to avoid warnings due to possible overrides of die
    exec $new_command or die "exec $new_command has failed";
}


# make it easy to move the whole distro w/o running
# 't/TEST -clean' before moving. when moving the whole package,
# the old cached config will stay, so we want to nuke it only if
# we realize that it's no longer valid. we can't just check the
# existance of the saved top_dir value, since the project may have
# been copied and the old dir could be still there, but that's not
# the one that we work in
sub detect_relocation {
    my($self, $cur_top_dir) = @_;

    my $config_file = catfile qw(t conf apache_test_config.pm);
    return unless -e $config_file;

    my %inc = %INC;
    eval { require "$config_file" };
    %INC = %inc; # be stealth
    warn($@), return if $@;

    my $cfg = 'apache_test_config'->new;

    # if the top_dir from saved config doesn't match the current
    # top_dir, that means that the whole project was relocated to a
    # different directory, w/o running t/TEST -clean first (in each
    # directory with a test suite)
    my $cfg_top_dir = $cfg->{vars}->{top_dir};
    return unless $cfg_top_dir;
    return if $cfg_top_dir eq $cur_top_dir;

    # if that's the case silently fixup the saved config to use the
    # new paths, and force a complete cleanup. if we don't fixup the
    # config files, the cleanup process won't be able to locate files
    # to delete and re-configuration will fail
    {
        # in place editing
        local @ARGV = $config_file;
        local $^I = ".bak";  # Win32 needs a backup
        while (<>) {
            s{$cfg_top_dir}{$cur_top_dir}g;
            print;
        }
        unlink $config_file . $^I;
    }

    my $cleanup_cmd = "$^X $0 -clean";
    warning "cleaning up the old config";
    # XXX: do we care to check success?
    system $cleanup_cmd;

    # XXX: I tried hard to accomplish that w/o starting a new process,
    # but too many things get on the way, so for now just keep it as an
    # external process, as it's absolutely transparent to the normal
    # app-run
}

my @oh = qw(jeez golly gosh darn shucks dangit rats nuts dangnabit crap);
sub oh {
    $oh[ rand scalar @oh ];
}

#e.g. t/core or t/core.12499
my $core_pat = '^core(\.\d+)?' . "\$";

# $self->scan_core_incremental([$only_top_dir])
# normally would be called after each test
# and since it updates the list of seen core files
# scan_core() won't report these again
# currently used in Apache::TestSmoke
#
# if $only_t_dir arg is true only the t_dir dir (t/) will be scanned
sub scan_core_incremental {
    my($self, $only_t_dir) = @_;
    my $vars = $self->{test_config}->{vars};

    # no core files dropped on win32
    return () if Apache::TestConfig::WIN32;

    if ($only_t_dir) {
        require IO::Dir;
        my @cores = ();
        for (IO::Dir->new($vars->{t_dir})->read) {
            my $file = catfile $vars->{t_dir}, $_;
            next unless -f $file;
            next unless /$core_pat/o;
            next if exists $core_files{$file} &&
                $core_files{$file} == -M $file;
            $core_files{$file} = -M $file;
            push @cores, $file;
        }
        return @cores
            ? join "\n", "server dumped core, for stacktrace, run:",
                map { "gdb $vars->{httpd} -core $_" } @cores
            : ();
    }

    my @msg = ();
    finddepth({ no_chdir => 1,
                wanted   => sub {
        return unless -f $_;
        my $file = basename $File::Find::name;
        return unless $file =~ /$core_pat/o;
        my $core = $File::Find::name;
        unless (exists $core_files{$core} && $core_files{$core} == -M $core) {
            # new core file!

            # XXX: could rename the file if it doesn't include the pid
            # in its name (i.e., just called 'core', instead of 'core.365')

            # XXX: could pass the test name and rename the core file
            # to use that name as a suffix, plus pid, time or some
            # other unique identifier, in case the same test is run
            # more than once and each time it caused a segfault
            $core_files{$core} = -M $core;
            push @msg, "server dumped core, for stacktrace, run:\n" .
                "gdb $vars->{httpd} -core $core";
        }
    }}, $vars->{top_dir});

    return @msg;

}

sub scan_core {
    my $self = shift;
    my $vars = $self->{test_config}->{vars};
    my $times = 0;

    # no core files dropped on win32
    return if Apache::TestConfig::WIN32;

    finddepth({ no_chdir => 1,
                wanted   => sub {
        return unless -f $_;
        my $file = basename $File::Find::name;
        return unless $file =~ /$core_pat/o;
        my $core = $File::Find::name;
        if (exists $core_files{$core} && $core_files{$core} == -M $core) {
            # we have seen this core file before the start of the test
            info "an old core file has been found: $core";
        }
        else {
            my $oh = oh();
            my $again = $times++ ? "again" : "";
            error "oh $oh, server dumped core $again";
            error "for stacktrace, run: gdb $vars->{httpd} -core $core";
        }
    }}, $vars->{top_dir});
}

# warn the user that there is a core file before the tests
# start. suggest to delete it before proceeding or a false alarm can
# be generated at the end of the test routine run.
sub warn_core {
    my $self = shift;
    my $vars = $self->{test_config}->{vars};
    %core_files = (); # reset global

    # no core files dropped on win32
    return if Apache::TestConfig::WIN32;

    finddepth(sub {
        return unless -f $_;
        return unless /$core_pat/o;
        my $core = "$File::Find::dir/$_";
        info "consider removing an old $core file before running tests";
        # remember the timestamp of $core so we can check if it's the
        # old core file at the end of the run and not complain then
        $core_files{$core} = -M $core;
    }, $vars->{top_dir});
}

# this function handles the cases when the test suite is run under
# 'root':
#
# 1. When user 'bar' is chosen to run Apache with, files and dirs
#    created by 'root' might be not writable/readable by 'bar'
#
# 2. when the source is extracted as user 'foo', and the chosen user
#    to run Apache under is 'bar', in which case normally 'bar' won't
#    have the right permissions to write into the fs created by 'foo'.
#
# We solve that by 'chown -R bar.bar t/' in a portable way.
#
# 3. If the parent directory is not rwx for the chosen user, that user
#    won't be able to read/write the DocumentRoot. In which case we
#    have nothing else to do, but to tell the user to fix the situation.
#
sub adjust_t_perms {
    my $self = shift;

    return if Apache::TestConfig::WINFU;

    %original_t_perms = (); # reset global

    my $user = getpwuid($>) || '';
    if ($user eq 'root') {
        my $vars = $self->{test_config}->{vars};
        my $user = $vars->{user};
        my($uid, $gid) = (getpwnam($user))[2..3]
            or die "Can't find out uid/gid of '$user'";

        warning "root mode: ".
            "changing the files ownership to '$user' ($uid:$gid)";
        finddepth(sub {
            $original_t_perms{$File::Find::name} = [(stat $_)[4..5]];
            chown $uid, $gid, $_;
        }, $vars->{t_dir});

        $self->check_perms($user, $uid, $gid);

        $self->become_nonroot($user, $uid, $gid);
    }
}

sub restore_t_perms {
    my $self = shift;

    return if Apache::TestConfig::WINFU;

    if (%original_t_perms) {
        warning "root mode: restoring the original files ownership";
        my $vars = $self->{test_config}->{vars};
        while (my($file, $ids) = each %original_t_perms) {
            next unless -e $file; # files could be deleted
            chown @$ids, $file;
        }
    }
}

# this sub is executed from an external process only, since it
# "sudo"'s into a uid/gid of choice
sub run_root_fs_test {
    my($uid, $gid, $dir) = @_;

    # first must change gid and egid ("$gid $gid" for an empty
    # setgroups() call as explained in perlvar.pod)
    my $groups = "$gid $gid";
    $( = $) = $groups;
    die "failed to change gid to $gid"
        unless $( eq $groups && $) eq $groups;

    # only now can change uid and euid
    $< = $> = $uid+0;
    die "failed to change uid to $uid" unless $< == $uid && $> == $uid;

    my $file = catfile $dir, ".apache-test-file-$$-".time.int(rand);
    eval "END { unlink q[$file] }";

    # unfortunately we can't run the what seems to be an obvious test:
    # -r $dir && -w _ && -x _
    # since not all perl implementations do it right (e.g. sometimes
    # acls are ignored, at other times setid/gid change is ignored)
    # therefore we test by trying to attempt to read/write/execute

    # -w
    open TEST, ">$file" or die "failed to open $file: $!";

    # -x
    -f $file or die "$file cannot be looked up";
    close TEST;

    # -r
    opendir DIR, $dir or die "failed to open dir $dir: $!";
    defined readdir DIR or die "failed to read dir $dir: $!";
    close DIR;

    # all tests passed
    print "OK";
}

sub check_perms {
    my ($self, $user, $uid, $gid) = @_;

    # test that the base dir is rwx by the selected non-root user
    my $vars = $self->{test_config}->{vars};
    my $dir  = $vars->{t_dir};
    my $perl = Apache::TestConfig::shell_ready($vars->{perl});

    # find where Apache::TestRun was loaded from, so we load this
    # exact package from the external process
    my $inc = dirname dirname $INC{"Apache/TestRun.pm"};
    my $sub = "Apache::TestRun::run_root_fs_test";
    my $check = <<"EOI";
$perl -Mlib=$inc -MApache::TestRun -e 'eval { $sub($uid, $gid, q[$dir]) }';
EOI
    warning "testing whether '$user' is able to -rwx $dir\n$check\n";

    my $res = qx[$check] || '';
    warning "result: $res";
    unless ($res eq 'OK') {
        $self->user_error(1);
        #$self->restore_t_perms;
        error <<"EOI";
You are running the test suite under user 'root'.
Apache cannot spawn child processes as 'root', therefore
we attempt to run the test suite with user '$user' ($uid:$gid).
The problem is that the path (including all parent directories):
  $dir
must be 'rwx' by user '$user', so Apache can read and write under that
path.

There are several ways to resolve this issue. One is to move and
rebuild the distribution to '/tmp/' and repeat the 'make test'
phase. The other is not to run 'make test' as root (i.e. building
under your /home/user directory).

You can test whether some directory is suitable for 'make test' under
'root', by running a simple test. For example to test a directory
'$dir', run:

  % $check
Only if the test prints 'OK', the directory is suitable to be used for
testing.
EOI
        skip_test_suite();
        exit_perl 0;
    }
}

# in case the client side creates any files after the initial chown
# adjustments we want the server side to be able to read/write them, so
# they better be with the same permissions. dropping root permissions
# and becoming the same user as the server side solves this problem.
sub become_nonroot {
    my ($self, $user, $uid, $gid) = @_;

    warning "the client side drops 'root' permissions and becomes '$user'";

    # first must change gid and egid ("$gid $gid" for an empty
    # setgroups() call as explained in perlvar.pod)
    my $groups = "$gid $gid";
    $( = $) = $groups;
    die "failed to change gid to $gid" unless $( eq $groups && $) eq $groups;

    # only now can change uid and euid
    $< = $> = $uid+0;
    die "failed to change uid to $uid" unless $< == $uid && $> == $uid;
}

sub run_request {
    my($test_config, $opts) = @_;

    my @args = (%{ $opts->{header} }, %{ $opts->{req_args} });

    my($request, $url) = ("", "");

    for (@request_opts) {
        next unless exists $opts->{$_};
        $url = $opts->{$_} if $opts->{$_};
        $request = join $request ? '_' : '', $request, $_;
    }

    if ($request) {
        my $method = \&{"Apache::TestRequest::\U$request"};
        my $res = $method->($url, @args);
        print Apache::TestRequest::to_string($res);
    }
}

sub opt_clean {
    my($self, $level) = @_;
    my $test_config = $self->{test_config};
    $test_config->server->stop;
    $test_config->clean($level);
    1;
}

sub opt_ping {
    my($self) = @_;

    my $test_config = $self->{test_config};
    my $server = $test_config->server;
    my $pid = $server->ping;
    my $name = $server->{name};
    # support t/TEST -ping=block -run ...
    my $exit = not $self->{opts}->{'run-tests'};

    if ($pid) {
        if ($pid == -1) {
            error "port $test_config->{vars}->{port} is in use, ".
                  "but cannot determine server pid";
        }
        else {
            my $version = $server->{version};
            warning "server $name running (pid=$pid, version=$version)";
        }
        return $exit;
    }

    if (exists $self->{opts}->{ping} && $self->{opts}->{ping} eq 'block') {
        $server->wait_till_is_up(STARTUP_TIMEOUT);
    }
    else {
        warning "no server is running on $name";
    }

    return $exit; #means call exit() if true
}

sub test_inc {
    map { "$_/Apache-Test/lib" } qw(. ..);
}

sub set_perl5lib {
    $ENV{PERL5LIB} = join $Config{path_sep}, shift->test_inc();
}

sub set_perldb_opts {
    my $config = shift->{test_config};
    my $file = catfile $config->{vars}->{t_logs}, 'perldb.out';
    $config->genfile($file); #mark for -clean
    $ENV{PERLDB_OPTS} = "NonStop frame=4 AutoTrace LineInfo=$file";
    warning "perldb log is t/logs/perldb.out";
}

sub opt_debug {
    my $self = shift;
    my $server = $self->{server};

    my $opts = $self->{opts};
    my $debug_opts = {};

    for (qw(debugger breakpoint)) {
        $debug_opts->{$_} = $opts->{$_};
    }

    if (my $db = $opts->{debugger}) {
        if ($db =~ s/^perl=?//) {
            $opts->{'run-tests'} = 1;
            $self->start; #if not already running
            $self->set_perl5lib;
            $self->set_perldb_opts if $db eq 'nostop';
            system $^X, '-MApache::TestPerlDB', '-d', @{ $self->{tests} };
            $self->stop;
            return 1;
        }
        elsif ($db =~ s/^lwp[=:]?//) {
            $ENV{APACHE_TEST_DEBUG_LWP} = $db || 1;
            $opts->{verbose} = 1;
            return 0;
        }
    }

    $server->stop;
    $server->start_debugger($debug_opts);
    1;
}

sub opt_help {
    my $self = shift;

    print <<EOM;
usage: TEST [options ...]
   where options include:
EOM

    for (sort keys %usage){
        printf "  -%-13s %s\n", $_, $usage{$_};
    }

    print "\n   configuration options:\n";

    Apache::TestConfig->usage;
    1;
}

# generate t/TEST script (or a different filename) which will drive
# Apache::TestRun
sub generate_script {
    my ($class, @opts) = @_;

    my %opts = ();

    # back-compat
    if (@opts == 1) {
        $opts{file} = $opts[0];
    }
    else {
        %opts = @opts;
        $opts{file} ||= catfile 't', 'TEST';
    }

    my $body = "BEGIN { eval { require blib && blib->import; } }\n";

    my %args = @Apache::TestMM::Argv;
    while (my($k, $v) = each %args) {
        $v =~ s/\|/\\|/g;
        $body .= "\n\$Apache::TestConfig::Argv{'$k'} = q|$v|;\n";
    }

    my $header = Apache::TestConfig->perlscript_header;

    $body .= join "\n",
        $header, "use $class ();";

    if (my $report = $opts{bugreport}) {
        $body .= "\n\npackage $class;\n" .
                 "sub bug_report { print '$report' }\n\n";
    }

    $body .= "$class->new->run(\@ARGV);";

    Apache::Test::basic_config()->write_perlscript($opts{file},
                                                   $body);
}

# in idiomatic perl functions return 1 on success and 0 on
# failure. Shell expects the opposite behavior. So this function
# reverses the status.
sub exit_perl {
    exit_shell $_[0] ? 0 : 1;
}

# expects shell's exit status values (0==success)
sub exit_shell {
#    require Carp;
#    Carp::cluck('exiting');
    CORE::exit $_[0];
}

# successfully abort the test suite execution (to allow automatic
# tools like CPAN.pm, to continue with installation).
#
# if a true value is passed, quit right away
# otherwise ask the user, if they may want to change their mind which
# will return them back to where they left
sub skip_test_suite {
    my $no_doubt = shift;

    # we can't prompt when STDIN is not attached to tty, unless we
    # were told that's it OK via env var (in which case some program
    # will feed the interactive prompts
    unless (-t STDIN || $ENV{APACHE_TEST_INTERACTIVE_PROMPT_OK}) {
        $no_doubt = 1;
    }

    print qq[

Running the test suite is important to make sure that the module that
you are about to install works on your system. If you choose not to
run the test suite and you have a problem using this module, make sure
to return and run this test suite before reporting any problems to the
developers of this module.

];
    unless ($no_doubt) {
        my $default = 'No';
        my $prompt = 'Skip the test suite?';
        my $ans = ExtUtils::MakeMaker::prompt($prompt, $default);
        return if lc($ans) =~ /no/;
    }

    error "Skipping the test suite execution, while returning success status";
    exit_perl 1;
}

1;

__END__

=head1 NAME

Apache::TestRun - Run the test suite

=head1 SYNOPSIS


=head1 DESCRIPTION

The C<Apache::TestRun> package controls the configuration and running
of the test suite.

=head1 METHODS

Several methods are sub-classable, if the default behavior should be
changed.

=head2 C<bug_report>

The C<bug_report()> method is executed when C<t/TEST> was executed
with the C<-bugreport> option, and C<make test> (or C<t/TEST>)
fail. Normally this is callback which you can use to tell the user how
to deal with the problem, e.g. suggesting to read some document or
email some details to someone who can take care of it. By default
nothing is executed.

The C<-bugreport> option is needed so this feature won't become
annoying to developers themselves. It's automatically added to the
C<run_tests> target in F<Makefile>. So if you repeateadly have to test
your code, just don't use C<make test> but run C<t/TEST>
directly. Here is an example of a custom C<t/TEST>

  My::TestRun->new->run(@ARGV);

  package My::TestRun;
  use base 'Apache::TestRun';

  sub bug_report {
      my $self = shift;

      print <<EOI;
  +--------------------------------------------------------+
  | Please file a bug report: http://perl.apache.org/bugs/ |
  +--------------------------------------------------------+
  EOI
  }

=head2 C<pre_configure>

The C<pre_configure()> method is executed before the configuration for
C<Apache::Test> is generated. So if you need to adjust the setup
before I<httpd.conf> and other files are autogenerated, this is the
right place to do so.

For example if you don't want to inherit a LoadModule directive for
I<mod_apreq.so> but to make sure that the local version is used, you
can sub-class C<Apache::TestRun> and override this method in
I<t/TEST.PL>:

  package My::TestRun;
  use base 'Apache::TestRun';
  use Apache::TestConfig;
  __PACKAGE__->new->run(@ARGV);

  sub pre_configure {
      my $self = shift;
      # Don't load an installed mod_apreq
      Apache::TestConfig::autoconfig_skip_module_add('mod_apreq.c');

      $self->SUPER::pre_configure();
  }

Notice that the extension is I<.c>, and not I<.so>.

Don't forget to run the super class' c<pre_configure()> method.



=head2 C<new_test_config>

META: to be completed



=head1 Persistent Custom Configuration

When C<Apache::Test> is first installed or used, it will save the
values of C<httpd>, C<apxs>, C<port>, C<user>, and C<group>, if set,
to a configuration file C<Apache::TestConfigData>.  This information
will then be used in setting these options for subsequent uses of
C<Apache-Test> unless temprorarily overridden, either by setting the
appropriate environment variable (C<APACHE_TEST_HTTPD>,
C<APACHE_TEST_APXS>, C<APACHE_TEST_PORT>, C<APACHE_TEST_USER>, and
C<APACHE_TEST_GROUP>) or by giving the relevant option (C<-httpd>,
C<-apxs>, C<-port>, C<-user>, and C<-group>) when the C<TEST> script
is run.

To avoid either using previous persistent configurations or saving
current configurations, set the C<APACHE_TEST_NO_STICKY_PREFERENCES>
environment variable to a true value.

Finally it's possible to permanently override the previously saved
options by passing C<L<-save|/Saving_Custom_Configuration_Options>>.

Here is the algorithm of how and when options are saved for the first
time and when they are used. We will use a few variables to simplify
the pseudo-code/pseudo-chart flow:

C<$config_exists> - custom configuration has already been saved, to
get this setting run C<custom_config_exists()>, which tests whether
either C<apxs> or C<httpd> values are set. It doesn't check for other
values, since all we need is C<apxs> or C<httpd> to get the test suite
running. custom_config_exists() checks in the following order
F<lib/Apache/TestConfigData.pm> (if during Apache-Test build) ,
F<~/.apache-test/Apache/TestConfigData.pm> and
F<Apache/TestConfigData.pm> in the perl's libraries.

C<$config_overriden> - that means that we have either C<apxs> or
C<httpd> values provided by user, via env vars or command line options.

=over

=item 1 Building Apache-Test or modperl-2.0 (or any other project that
bundles Apache-Test).

  1) perl Apache-Test/Makefile.PL
  (for bundles top-level Makefile.PL will run this as well)

  if $config_exists
      do nothing
  else
      create lib/Apache/TestConfigData.pm w/ empty config: {}

  2) make

  3) make test

  if $config_exists
      if $config_overriden
          override saved options (for those that were overriden)
      else
          use saved options
  else
      if $config_overriden
          save them in lib/Apache/TestConfigData.pm
          (which will be installed on 'make install')
      else
          - run interactive prompt for C<httpd> and optionally for C<apxs>
          - save the custom config in lib/Apache/TestConfigData.pm
          - restart the currently run program

  modperl-2.0 is a special case in (3). it always overrides 'httpd'
  and 'apxs' settings. Other settings like 'port', can be used from
  the saved config.

  4) make install

     if $config_exists only in lib/Apache/TestConfigData.pm
        it will be installed system-wide
     else
        nothing changes (since lib/Apache/TestConfigData.pm won't exist)

=item 2 Testing 3rd party modules (after Apache-Test was installed)

Notice that the following situation is quite possible:

  cd Apache-Test
  perl Makefile.PL && make install

so that Apache-Test was installed but no custom configuration saved
(since its C<make test> wasn't run). In which case the interactive
configuration should kick in (unless config options were passed) and
in any case saved once configured.

C<$custom_config_path> - perl's F<Apache/TestConfigData.pm> (at the
same location as F<Apache/TestConfig.pm>) if that area is writable by
that user (e.g. perl's lib is not owned by 'root'). If not, in
F<~/.apache-test/Apache/TestConfigData.pm>.

  1) perl Apache-Test/Makefile.PL
  2) make
  3) make test

  if $config_exists
      if $config_overriden
          override saved options (for those that were overriden)
      else
          use saved options
  else
      if $config_overriden
          save them in $custom_config_path
      else
          - run interactive prompt for C<httpd> and optionally for C<apxs>
          - save the custom config in $custom_config_path
          - restart the currently run program

  4) make install

=back



=head2 Saving Custom Configuration Options

If you want to override the existing custom configurations options to
C<Apache::TestConfigData>, use the C<-save> flag when running C<TEST>.

If you are running C<Apache::Test> as a user who does not have
permission to alter the system C<Apache::TestConfigData>, you can
place your own private configuration file F<TestConfigData.pm> under
C<$ENV{HOME}/.apache-test/Apache/>, which C<Apache::Test> will use, if
present. An example of such a configuration file is

  # file $ENV{HOME}/.apache-test/Apache/TestConfigData.pm
  package Apache::TestConfigData;
  use strict;
  use warnings;
  use vars qw($vars);

  $vars = {
      'group' => 'me',
      'user' => 'myself',
      'port' => '8529',
      'httpd' => '/xampp/apache/bin/httpd',

  };
  1;




=cut
