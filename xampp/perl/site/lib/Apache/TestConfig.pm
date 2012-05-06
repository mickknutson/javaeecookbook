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
package Apache::TestConfig;

use strict;
use warnings FATAL => 'all';

use constant WIN32   => $^O eq 'MSWin32';
use constant OSX     => $^O eq 'darwin';
use constant CYGWIN  => $^O eq 'cygwin';
use constant NETWARE => $^O eq 'NetWare';
use constant SOLARIS => $^O eq 'solaris';
use constant WINFU   => WIN32 || NETWARE;
use constant COLOR   => ($ENV{APACHE_TEST_COLOR} && -t STDOUT) ? 1 : 0;

use constant DEFAULT_PORT => 8529;

use constant IS_MOD_PERL_2       =>
    eval { require mod_perl2 } || 0;

use constant IS_MOD_PERL_2_BUILD => IS_MOD_PERL_2 &&
    require Apache2::Build && Apache2::Build::IS_MOD_PERL_BUILD();

use constant IS_APACHE_TEST_BUILD =>
    grep { -e "$_/lib/Apache/TestConfig.pm" } qw(Apache-Test . ..);

use constant CUSTOM_CONFIG_FILE => 'Apache/TestConfigData.pm';

use lib ();
use File::Copy ();
use File::Find qw(finddepth);
use File::Basename qw(dirname);
use File::Path ();
use File::Spec::Functions qw(catfile abs2rel splitdir canonpath
                             catdir file_name_is_absolute devnull);
use Cwd qw(fastcwd);
use Socket ();
use Symbol ();

use Apache::TestConfigPerl ();
use Apache::TestConfigParse ();
use Apache::TestTrace;
use Apache::TestServer ();
use Apache::TestRun ();

use vars qw(%Usage);

# variables stored in $Apache::TestConfigData::vars
my @data_vars_must = qw(httpd apxs);
my @data_vars_opt  = qw(user group port);
# mapping from $Apache::TestConfigData::vars to $ENV settings
my %vars_to_env = (
    httpd => 'APACHE_TEST_HTTPD',
    apxs  => 'APACHE_TEST_APXS',
    user  => 'APACHE_TEST_USER',
    group => 'APACHE_TEST_GROUP',
    port  => 'APACHE_TEST_PORT',
);

%Usage = (
   top_dir         => 'top-level directory (default is $PWD)',
   t_dir           => 'the t/ test directory (default is $top_dir/t)',
   t_conf          => 'the conf/ test directory (default is $t_dir/conf)',
   t_logs          => 'the logs/ test directory (default is $t_dir/logs)',
   t_pid_file      => 'location of the pid file (default is $t_logs/httpd.pid)',
   t_conf_file     => 'test httpd.conf file (default is $t_conf/httpd.conf)',
   src_dir         => 'source directory to look for mod_foos.so',
   serverroot      => 'ServerRoot (default is $t_dir)',
   documentroot    => 'DocumentRoot (default is $ServerRoot/htdocs',
   port            => 'Port [port_number|select] (default ' . DEFAULT_PORT . ')',
   servername      => 'ServerName (default is localhost)',
   user            => 'User to run test server as (default is $USER)',
   group           => 'Group to run test server as (default is $GROUP)',
   bindir          => 'Apache bin/ dir (default is apxs -q BINDIR)',
   sbindir         => 'Apache sbin/ dir (default is apxs -q SBINDIR)',
   httpd           => 'server to use for testing (default is $bindir/httpd)',
   target          => 'name of server binary (default is apxs -q TARGET)',
   apxs            => 'location of apxs (default is from Apache2::BuildConfig)',
   startup_timeout => 'seconds to wait for the server to start (default is 60)',
   httpd_conf      => 'inherit config from this file (default is apxs derived)',
   httpd_conf_extra=> 'inherit additional config from this file',
   minclients      => 'minimum number of concurrent clients (default is 1)',
   maxclients      => 'maximum number of concurrent clients (default is minclients+1)',
   perlpod         => 'location of perl pod documents (for testing downloads)',
   proxyssl_url    => 'url for testing ProxyPass / https (default is localhost)',
   sslca           => 'location of SSL CA (default is $t_conf/ssl/ca)',
   sslcaorg        => 'SSL CA organization to use for tests (default is asf)',
   libmodperl      => 'path to mod_perl\'s .so (full or relative to LIBEXECDIR)',
   defines         => 'values to add as -D defines (for example, "VAR1 VAR2")',
   (map { $_ . '_module_name', "$_ module name"} qw(cgi ssl thread access auth php)),
);

my %filepath_conf_opts = map { $_ => 1 }
    qw(top_dir t_dir t_conf t_logs t_pid_file t_conf_file src_dir serverroot
       documentroot bindir sbindir httpd apxs httpd_conf httpd_conf_extra
       perlpod sslca libmodperl);

sub conf_opt_is_a_filepath {
    my $opt = shift;
    $opt && exists $filepath_conf_opts{$opt};
}

sub usage {
    for my $hash (\%Usage) {
        for (sort keys %$hash){
            printf "  -%-18s %s\n", $_, $hash->{$_};
        }
    }
}

sub filter_args {
    my($args, $wanted_args) = @_;
    my(@pass, %keep);

    my @filter = @$args;

    if (ref($filter[0])) {
        push @pass, shift @filter;
    }

    while (@filter) {
        my $key = shift @filter;
        # optinal - or -- prefix
        if (defined $key && $key =~ /^-?-?(.+)/ && exists $wanted_args->{$1}) {
            if (@filter) {
                $keep{$1} = shift @filter;
            }
            else {
                die "key $1 requires a matching value";
            }
        }
        else {
            push @pass, $key;
        }
    }

    return (\@pass, \%keep);
}

my %passenv = map { $_,1 } qw{
    APACHE_TEST_APXS
    APACHE_TEST_HTTPD
    APACHE_TEST_GROUP
    APACHE_TEST_USER
    APACHE_TEST_PORT
};

sub passenv {
    \%passenv;
}

sub passenv_makestr {
    my @vars;

    for (keys %passenv) {
        push @vars, "$_=\$($_)";
    }

    "@vars";
}

sub server { shift->{server} }

sub modperl_build_config {

    my $self = shift;

    my $server = ref $self ? $self->server : new_test_server();

    # we don't want to get mp2 preconfigured data in order to be able
    # to get the interactive tests running.
    return undef if $ENV{APACHE_TEST_INTERACTIVE_CONFIG_TEST};

    # we can't do this if we're using httpd 1.3.X
    # even if mod_perl2 is installed on the box
    # similarly, we shouldn't be loading mp2 if we're not
    # absolutely certain we're in a 2.X environment yet
    # (such as mod_perl's own build or runtime environment)
    if (($server->{rev} && $server->{rev} == 2) ||
        IS_MOD_PERL_2_BUILD || $ENV{MOD_PERL_API_VERSION}) {
        eval {
            require Apache2::Build;
        } or return;
        return Apache2::Build->build_config;
    }

    return;
}

sub new_test_server {
    my($self, $args) = @_;
    Apache::TestServer->new($args || $self)
}

# setup httpd-independent components
# for httpd-specific call $self->httpd_config()
sub new {
    my $class = shift;

    my $args;

    $args = shift if $_[0] and ref $_[0];

    $args = $args ? {%$args} : {@_}; #copy

    #see Apache::TestMM::{filter_args,generate_script}
    #we do this so 'perl Makefile.PL' can be passed options such as apxs
    #without forcing regeneration of configuration and recompilation of c-modules
    #as 't/TEST apxs /path/to/apache/bin/apxs' would do
    while (my($key, $val) = each %Apache::TestConfig::Argv) {
        $args->{$key} = $val;
    }

    my $top_dir = fastcwd;
    $top_dir = pop_dir($top_dir, 't');
    # untaint as we are going to use it a lot later on in -T sensitive
    # operations (.e.g @INC)
    $top_dir = $1 if $top_dir =~ /(.*)/;

    # make sure that t/conf/apache_test_config.pm is found
    # (unfortunately sometimes we get thrown into / by Apache so we
    # can't just rely on $top_dir
    lib->import($top_dir);

    my $thaw = {};
    #thaw current config
    for (qw(conf t/conf)) {
        last if eval {
            require "$_/apache_test_config.pm";
            $thaw = 'apache_test_config'->new;
            delete $thaw->{save};
            #incase class that generated the config was
            #something else, which we can't be sure how to load
            bless $thaw, 'Apache::TestConfig';
        };
    }

    if ($args->{thaw} and ref($thaw) ne 'HASH') {
        #dont generate any new config
        $thaw->{vars}->{$_} = $args->{$_} for keys %$args;
        $thaw->{server} = $thaw->new_test_server;
        $thaw->add_inc;
        return $thaw;
    }

    #regenerating config, so forget old
    if ($args->{save}) {
        for (qw(vhosts inherit_config modules inc cmodules)) {
            delete $thaw->{$_} if exists $thaw->{$_};
        }
    }

    # custom config options from Apache::TestConfigData
    # again, this should force reconfiguration
    custom_config_add_conf_opts($args);

    my $self = bless {
        clean => {},
        vhosts => {},
        inherit_config => {},
        modules => {},
        inc => [],
        %$thaw,
        mpm => "",
        httpd_defines => {},
        vars => $args,
        postamble => [],
        preamble => [],
        postamble_hooks => [],
        preamble_hooks => [],
    }, ref($class) || $class;

    my $vars = $self->{vars}; #things that can be overridden

    for (qw(save verbose)) {
        next unless exists $args->{$_};
        $self->{$_} = delete $args->{$_};
    }

    $vars->{top_dir} ||= $top_dir;

    $self->add_inc;

    #help to find libmodperl.so
    my $src_dir = catfile $vars->{top_dir}, qw(src modules perl);
    $vars->{src_dir}      ||= $src_dir if -d $src_dir;

    $vars->{t_dir}        ||= catfile $vars->{top_dir}, 't';
    $vars->{serverroot}   ||= $vars->{t_dir};
    $vars->{documentroot} ||= catfile $vars->{serverroot}, 'htdocs';
    $vars->{perlpod}      ||= $self->find_in_inc('pods') ||
                              $self->find_in_inc('pod');
    $vars->{perl}         ||= $^X;
    $vars->{t_conf}       ||= catfile $vars->{serverroot}, 'conf';
    $vars->{sslca}        ||= catfile $vars->{t_conf}, 'ssl', 'ca';
    $vars->{sslcaorg}     ||= 'asf';
    $vars->{t_logs}       ||= catfile $vars->{serverroot}, 'logs';
    $vars->{t_conf_file}  ||= catfile $vars->{t_conf},   'httpd.conf';
    $vars->{t_pid_file}   ||= catfile $vars->{t_logs},   'httpd.pid';

    if (WINFU) {
        for (keys %$vars) {
            $vars->{$_} =~ s|\\|\/|g if defined $vars->{$_};
        }
    }

    $vars->{scheme}       ||= 'http';
    $vars->{servername}   ||= $self->default_servername;
    $vars->{port}           = $self->select_first_port;
    $vars->{remote_addr}  ||= $self->our_remote_addr;

    $vars->{user}         ||= $self->default_user;
    $vars->{group}        ||= $self->default_group;
    $vars->{serveradmin}  ||= $self->default_serveradmin;

    $vars->{minclients}   ||= 1;
    $vars->{maxclients_preset} = $vars->{maxclients} || 0;
    # if maxclients wasn't explicitly passed try to
    # prevent 'server reached MaxClients setting' errors
    $vars->{maxclients}   ||= $vars->{minclients} + 1;

    # if a preset maxclients valus is smaller than minclients,
    # maxclients overrides minclients
    if ($vars->{maxclients_preset} &&
        $vars->{maxclients_preset} < $vars->{minclients}) {
        $vars->{minclients} = $vars->{maxclients_preset};
    }

    # for threaded mpms MaxClients must be a multiple of
    # ThreadsPerChild (i.e. maxclients % minclients == 0)
    # so unless -maxclients was explicitly specified use a double of
    # minclients
    $vars->{maxclientsthreadedmpm} =
        $vars->{maxclients_preset} || $vars->{minclients} * 2;

    $vars->{proxy}        ||= 'off';
    $vars->{proxyssl_url} ||= '';
    $vars->{defines}      ||= '';

    $self->{hostport} = $self->hostport;
    $self->{server} = $self->new_test_server;

    return $self;

}

# figure out where httpd is and run extra config hooks which require
# knowledge of where httpd is
sub httpd_config {
    my $self = shift;

    $self->configure_apxs;
    $self->configure_httpd;

    my $vars = $self->{vars};
    unless ($vars->{httpd} or $vars->{apxs}) {

        # mod_perl 2.0 build (almost) always knows the right httpd

        # location (and optionally apxs). if we get here we can't
        # continue because the interactive config can't work with
        # mod_perl 2.0 build (by design)
        if (IS_MOD_PERL_2_BUILD){
            my $mp2_build = $self->modperl_build_config();
            # if mod_perl 2 was built against the httpd source it
            # doesn't know where to find apxs/httpd, so in this case
            # fall back to interactive config
            unless ($mp2_build->{MP_APXS}) {
                die "mod_perl 2 was built against Apache sources, we " .
                "don't know where httpd/apxs executables are, therefore " .
                "skipping the test suite execution"
            }

            # not sure what else could go wrong but we can't continue
            die "something is wrong, mod_perl 2.0 build should have " .
                "supplied all the needed information to run the tests. " .
                "Please post lib/Apache/BuildConfig.pm along with the " .
                "bug report";
        }

        if ($ENV{APACHE_TEST_NO_STICKY_PREFERENCES}) {
            error "You specified APACHE_TEST_NO_STICKY_PREFERENCES=1 " .
                "in which case you must explicitly specify -httpd " .
                "and/or -apxs options";
            Apache::TestRun::exit_perl(0);
        }

        $self->clean(1);
        # this method restarts the whole program via exec
        # so it never returns
        $self->custom_config_first_time($self->{vars});
    }
    else {
        debug "Using httpd: $vars->{httpd}";
    }

    # if we have gotten that far we know at least about the location
    # of httpd and or apxs, so let's save it if we haven't saved any
    # custom configs yet
    unless (custom_config_exists()) {
        $self->custom_config_save($self->{vars});
    }

    $self->inherit_config; #see TestConfigParse.pm
    $self->configure_httpd_eapi; #must come after inherit_config

    $self->default_module(cgi    => [qw(mod_cgi mod_cgid)]);
    $self->default_module(thread => [qw(worker threaded)]);
    $self->default_module(ssl    => [qw(mod_ssl)]);
    $self->default_module(access => [qw(mod_access mod_authz_host)]);
    $self->default_module(auth   => [qw(mod_auth mod_auth_basic)]);
    $self->default_module(php    => [qw(sapi_apache2 mod_php4 mod_php5)]);

    $self->{server}->post_config;

    $self;
}

sub default_module {
    my($self, $name, $choices) = @_;

    my $mname = $name . '_module_name';

    unless ($self->{vars}->{$mname}) {
        ($self->{vars}->{$mname}) = grep {
            $self->{modules}->{"$_.c"};
        } @$choices;

        $self->{vars}->{$mname} ||= $choices->[0];
    }

    $self->{vars}->{$name . '_module'} =
      $self->{vars}->{$mname} . '.c'
}

sub configure_apxs {
    my $self = shift;

    $self->{APXS} = $self->default_apxs;

    return unless $self->{APXS};

    $self->{APXS} =~ s{/}{\\}g if WIN32;

    my $vars = $self->{vars};

    $vars->{bindir}   ||= $self->apxs('BINDIR', 1);
    $vars->{sbindir}  ||= $self->apxs('SBINDIR');
    $vars->{target}   ||= $self->apxs('TARGET');
    $vars->{conf_dir} ||= $self->apxs('SYSCONFDIR');

    if ($vars->{conf_dir}) {
        $vars->{httpd_conf} ||= catfile $vars->{conf_dir}, 'httpd.conf';
    }
}

sub configure_httpd {
    my $self = shift;
    my $vars = $self->{vars};

    debug "configuring httpd";

    $vars->{target} ||= (WIN32 ? 'Apache.EXE' : 'httpd');

    unless ($vars->{httpd}) {
        #sbindir should be bin/ with the default layout
        #but its eaiser to workaround apxs than fix apxs
        for my $dir (map { $vars->{$_} } qw(sbindir bindir)) {
            next unless defined $dir;
            my $httpd = catfile $dir, $vars->{target};
            next unless -x $httpd;
            $vars->{httpd} = $httpd;
            last;
        }

        $vars->{httpd} ||= $self->default_httpd;
    }

    if ($vars->{httpd}) {
        my @chunks = splitdir $vars->{httpd};
        #handle both $prefix/bin/httpd and $prefix/Apache.exe
        for (1,2) {
            pop @chunks;
            last unless @chunks;
            $self->{httpd_basedir} = catfile @chunks;
            last if -d "$self->{httpd_basedir}/bin";
        }
    }

    #cleanup httpd droppings
    my $sem = catfile $vars->{t_logs}, 'apache_runtime_status.sem';
    unless (-e $sem) {
        $self->clean_add_file($sem);
    }
}

sub configure_httpd_eapi {
    my $self = shift;
    my $vars = $self->{vars};

    #deal with EAPI_MM_CORE_PATH if defined.
    if (defined($self->{httpd_defines}->{EAPI_MM_CORE_PATH})) {
        my $path = $self->{httpd_defines}->{EAPI_MM_CORE_PATH};

        #ensure the directory exists
        my @chunks = splitdir $path;
        pop @chunks; #the file component of the path
        $path = catdir @chunks;
        unless (file_name_is_absolute $path) {
            $path = catdir $vars->{serverroot}, $path;
        }
        $self->gendir($path);
    }
}

sub configure_proxy {
    my $self = shift;
    my $vars = $self->{vars};

    #if we proxy to ourselves, must bump the maxclients
    if ($vars->{proxy} =~ /^on$/i) {
        unless ($vars->{maxclients_preset}) {
            $vars->{minclients}++;
            $vars->{maxclients}++;
        }
        $vars->{proxy} = $self->{vhosts}->{'mod_proxy'}->{hostport};
        return $vars->{proxy};
    }

    return undef;
}

# adds the config to the head of the group instead of the tail
# XXX: would be even better to add to a different sub-group
# (e.g. preamble_first) of only those that want to be first and then,
# make sure that they are dumped to the config file first in the same
# group (e.g. preamble)
sub add_config_first {
    my $self = shift;
    my $where = shift;
    unshift @{ $self->{$where} }, $self->massage_config_args(@_);
}

sub add_config_last {
    my $self = shift;
    my $where = shift;
    push @{ $self->{$where} }, $self->massage_config_args(@_);
}

sub massage_config_args {
    my $self = shift;
    my($directive, $arg, $data) = @_;
    my $args = "";

    if ($data) {
        $args = "<$directive $arg>\n";
        if (ref($data) eq 'HASH') {
            while (my($k,$v) = each %$data) {
                $args .= "    $k $v\n";
            }
        }
        elsif (ref($data) eq 'ARRAY') {
            # balanced (key=>val) list
            my $pairs = @$data / 2;
            for my $i (0..($pairs-1)) {
                $args .= sprintf "    %s %s\n", $data->[$i*2], $data->[$i*2+1];
            }
        }
        else {
            $args .= "    $data";
        }
        $args .= "</$directive>\n";
    }
    elsif (ref($directive) eq 'ARRAY') {
        $args = join "\n", @$directive;
    }
    else {
        $args = join " ", grep length($_), $directive,
          (ref($arg) && (ref($arg) eq 'ARRAY') ? "@$arg" : $arg || "");
    }

    return $args;
}

sub postamble_first {
    shift->add_config_first(postamble => @_);
}

sub postamble {
    shift->add_config_last(postamble => @_);
}

sub preamble_first {
    shift->add_config_first(preamble => @_);
}

sub preamble {
    shift->add_config_last(preamble => @_);
}

sub postamble_register {
    push @{ shift->{postamble_hooks} }, @_;
}

sub preamble_register {
    push @{ shift->{preamble_hooks} }, @_;
}

sub add_config_hooks_run {
    my($self, $where, $out) = @_;

    for (@{ $self->{"${where}_hooks"} }) {
        if ((ref($_) and ref($_) eq 'CODE') or $self->can($_)) {
            $self->$_();
        }
        else {
            error "cannot run configure hook: `$_'";
        }
    }

    for (@{ $self->{$where} }) {
        $self->replace;
        print $out "$_\n";
    }
}

sub postamble_run {
    shift->add_config_hooks_run(postamble => @_);
}

sub preamble_run {
    shift->add_config_hooks_run(preamble => @_);
}

sub default_group {
    return if WINFU;

    my $gid = $);

    #use only first value if $) contains more than one
    $gid =~ s/^(\d+).*$/$1/;

    my $group = $ENV{APACHE_TEST_GROUP} || (getgrgid($gid) || "#$gid");

    if ($group eq 'root') {
        # similar to default_user, we want to avoid perms problems,
        # when the server is started with group 'root'. When running
        # under group root it may fail to create dirs and files,
        # writable only by user
        my $user = default_user();
        my $gid = $user ? (getpwnam($user))[3] : '';
        $group = (getgrgid($gid) || "#$gid") if $gid;
    }

    $group;
}

sub default_user {
    return if WINFU;

    my $uid = $>;

    my $user = $ENV{APACHE_TEST_USER} || (getpwuid($uid) || "#$uid");

    if ($user eq 'root') {
        my $other = (getpwnam('nobody'))[0];
        if ($other) {
            $user = $other;
        }
        else {
            die "cannot run tests as User root";
            #XXX: prompt for another username
        }
    }

    $user;
}

sub default_serveradmin {
    my $vars = shift->{vars};
    join '@', ($vars->{user} || 'unknown'), $vars->{servername};
}

sub default_apxs {
    my $self = shift;

    return $self->{vars}->{apxs} if $self->{vars}->{apxs};

    if (my $build_config = $self->modperl_build_config()) {
        return $build_config->{MP_APXS};
    }

    $ENV{APACHE_TEST_APXS};
}

sub default_httpd {
    my $self = shift;

    my $vars = $self->{vars};

    if (my $build_config = $self->modperl_build_config()) {
        if (my $p = $build_config->{MP_AP_PREFIX}) {
            for my $bindir (qw(bin sbin)) {
                my $httpd = catfile $p, $bindir, $vars->{target};
                return $httpd if -e $httpd;
                # The executable on Win32 in Apache/2.2 is httpd.exe,
                # so try that if Apache.exe doesn't exist
                if (WIN32) {
                    $httpd = catfile $p, $bindir, 'httpd.EXE';
                    if (-e $httpd) {
                        $vars->{target} = 'httpd.EXE';
                        return $httpd;
                    }
                }
            }
        }
    }

    $ENV{APACHE_TEST_HTTPD};
}

my $localhost;

sub default_localhost {
    my $localhost_addr = pack('C4', 127, 0, 0, 1);
    gethostbyaddr($localhost_addr, Socket::AF_INET()) || 'localhost';
}

sub default_servername {
    my $self = shift;
    $localhost ||= $self->default_localhost;
    die "Can't figure out the default localhost's server name"
        unless $localhost;
}

# memoize the selected value (so we make sure that the same port is used
# via select). The problem is that select_first_port() is called 3 times after
# -clean, and it's possible that a lower port will get released
# between calls, leading to various places in the test suite getting a
# different base port selection.
#
# XXX: There is still a problem if two t/TEST's configure at the same
# time, so they both see the same port free, but only the first one to
# bind() will actually get the port. So there is a need in another
# check and reconfiguration just before the server starts.
#
my $port_memoized;
sub select_first_port {
    my $self = shift;

    my $port ||= $port_memoized || $ENV{APACHE_TEST_PORT}
        || $self->{vars}{port} || DEFAULT_PORT;

    # memoize
    $port_memoized = $port;

    return $port unless $port eq 'select';

    # port select mode: try to find another available port, take into
    # account that each instance of the test suite may use more than
    # one port for virtual hosts, therefore try to check ports in big
    # steps (20?).
    my $step  = 20;
    my $tries = 20;
    $port = DEFAULT_PORT;
    until (Apache::TestServer->port_available($port)) {
        unless (--$tries) {
            error "no ports available";
            error "tried ports @{[DEFAULT_PORT]} - $port in $step increments";
            return 0;
        }
        $port += $step;
    }

    info "the default base port is used, using base port $port instead"
        unless $port == DEFAULT_PORT;

    # memoize
    $port_memoized = $port;

    return $port;
}

my $remote_addr;

sub our_remote_addr {
    my $self = shift;
    my $name = $self->default_servername;
    my $iaddr = (gethostbyname($name))[-1];
    unless (defined $iaddr) {
        error "Can't resolve host: '$name' (check /etc/hosts)";
        exit 1;
    }
    $remote_addr ||= Socket::inet_ntoa($iaddr);
}

sub default_loopback {
    '127.0.0.1';
}

sub port {
    my($self, $module) = @_;

    unless ($module) {
        my $vars = $self->{vars};
        return $self->select_first_port() unless $vars->{scheme} eq 'https';
        $module = $vars->{ssl_module_name};
    }
    return $self->{vhosts}->{$module}->{port};
}

sub hostport {
    my $self = shift;
    my $vars = shift || $self->{vars};
    my $module = shift || '';

    my $name = $vars->{servername};

    join ':', $name , $self->port($module || '');
}

#look for mod_foo.so
sub find_apache_module {
    my($self, $module) = @_;

    die "find_apache_module: module name argument is required"
        unless $module;

    my $vars = $self->{vars};
    my $sroot = $vars->{serverroot};

    my @trys = grep { $_ }
      ($vars->{src_dir},
       $self->apxs('LIBEXECDIR'),
       catfile($sroot, 'modules'),
       catfile($sroot, 'libexec'));

    for (@trys) {
        my $file = catfile $_, $module;
        if (-e $file) {
            debug "found $module => $file";
            return $file;
        }
    }

    # if the module wasn't found try to lookup in the list of modules
    # inherited from the system-wide httpd.conf
    my $name = $module;
    $name =~ s/\.s[ol]$/.c/;  #mod_info.so => mod_info.c
    $name =~ s/^lib/mod_/; #libphp4.so => mod_php4.c
    return $self->{modules}->{$name} if $self->{modules}->{$name};

}

#generate files and directories

my %warn_style = (
    html    => sub { "<!-- @_ -->" },
    c       => sub { "/* @_ */" },
    php     => sub { "<?php /* \n@_ \n*/ ?>" },
    default => sub { join '', grep {s/^/\# /gm} @_ },
);

my %file_ext = (
    map({$_ => 'html'} qw(htm html)),
    map({$_ => 'c'   } qw(c h)),
    map({$_ => 'php' } qw(php)),
);

# return the passed file's extension or '' if there is no one
# note: that '/foo/bar.conf.in' returns an extension: 'conf.in';
# note: a hidden file .foo will be recognized as an extension 'foo'
sub filename_ext {
    my ($self, $filename) = @_;
    my $ext = (File::Basename::fileparse($filename, '\..*'))[2] || '';
    $ext =~ s/^\.(.*)/lc $1/e;
    $ext;
}

sub warn_style_sub_ref {
    my ($self, $filename) = @_;
    my $ext = $self->filename_ext($filename);
    return $warn_style{ $file_ext{$ext} || 'default' };
}

sub genwarning {
    my($self, $filename, $from_filename) = @_;
    return unless $filename;
    my $time = scalar localtime;
    my $warning = "WARNING: this file is generated";
    $warning .= " (from $from_filename)" if defined $from_filename;
    $warning .= ", do not edit\n";
    $warning .= "generated on $time\n";
    $warning .= calls_trace();
    return $self->warn_style_sub_ref($filename)->($warning);
}

sub calls_trace {
    my $frame = 1;
    my $trace = '';

    while (1) {
        my($package, $filename, $line) = caller($frame);
        last unless $filename;
        $trace .= sprintf "%02d: %s:%d\n", $frame, $filename, $line;
        $frame++;
    }

    return $trace;
}

sub clean_add_file {
    my($self, $file) = @_;

    $self->{clean}->{files}->{ rel2abs($file) } = 1;
}

sub clean_add_path {
    my($self, $path) = @_;

    $path = rel2abs($path);

    # remember which dirs were created and should be cleaned up
    while (1) {
        $self->{clean}->{dirs}->{$path} = 1;
        $path = dirname $path;
        last if -e $path;
    }
}

sub genfile_trace {
    my($self, $file, $from_file) = @_;
    my $name = abs2rel $file, $self->{vars}->{t_dir};
    my $msg = "generating $name";
    $msg .= " from $from_file" if defined $from_file;
    debug $msg;
}

sub genfile_warning {
    my($self, $file, $from_file, $fh) = @_;

    if (my $msg = $self->genwarning($file, $from_file)) {
        print $fh $msg, "\n";
    }
}

# $from_file == undef if there was no templates used
sub genfile {
    my($self, $file, $from_file, $nowarning) = @_;

    # create the parent dir if it doesn't exist yet
    my $dir = dirname $file;
    $self->makepath($dir);

    $self->genfile_trace($file, $from_file);

    my $fh = Symbol::gensym();
    open $fh, ">$file" or die "open $file: $!";

    $self->genfile_warning($file, $from_file, $fh) unless $nowarning;

    $self->clean_add_file($file);

    return $fh;
}

# gen + write file
sub writefile {
    my($self, $file, $content, $nowarning) = @_;

    my $fh = $self->genfile($file, undef, $nowarning);

    print $fh $content if $content;

    close $fh;
}

sub perlscript_header {

    require FindBin;

    my @dirs = ();

    # mp2 needs its modper-2.0/lib before blib was created
    if (IS_MOD_PERL_2_BUILD || $ENV{APACHE_TEST_LIVE_DEV}) {
        # the live 'lib/' dir of the distro
        # (e.g. modperl-2.0/ModPerl-Registry/lib)
        my $dir = canonpath catdir $FindBin::Bin, "lib";
        push @dirs, $dir if -d $dir;

        # the live dir of the top dir if any  (e.g. modperl-2.0/lib)
        if (-e catfile($FindBin::Bin, "..", "Makefile.PL")) {
            my $dir = canonpath catdir $FindBin::Bin, "..", "lib";
            push @dirs, $dir if -d $dir;
        }
    }

    for (qw(. ..)) {
        my $dir = canonpath catdir $FindBin::Bin, $_ , "Apache-Test", "lib";
        if (-d $dir) {
            push @dirs, $dir;
            last;
        }
    }

    {
        my $dir = canonpath catdir $FindBin::Bin, "t", "lib";
        push @dirs, $dir if -d $dir;
    }

    my $dirs = join("\n    ", '', @dirs) . "\n";;

    return <<"EOF";

use strict;
use warnings FATAL => 'all';

use lib qw($dirs);

EOF
}

# gen + write executable perl script file
sub write_perlscript {
    my($self, $file, $content) = @_;

    my $fh = $self->genfile($file, undef, 1);

    my $shebang = make_shebang();
    print $fh $shebang;

    $self->genfile_warning($file, undef, $fh);

    print $fh $content if $content;

    close $fh;
    chmod 0755, $file;
}

sub make_shebang {
    # if perlpath is longer than 62 chars, some shells on certain
    # platforms won't be able to run the shebang line, so when seeing
    # a long perlpath use the eval workaround.
    # see: http://en.wikipedia.org/wiki/Shebang
    # http://homepages.cwi.nl/~aeb/std/shebang/
    my $shebang = length $Config{perlpath} < 62
        ? "#!$Config{perlpath}\n"
        : <<EOI;
$Config{'startperl'}
    eval 'exec $Config{perlpath} -S \$0 \${1+"\$@"}'
        if \$running_under_some_shell;
EOI

    return $shebang;
}

sub cpfile {
    my($self, $from, $to) = @_;
    File::Copy::copy($from, $to);
    $self->clean_add_file($to);
}

sub symlink {
    my($self, $from, $to) = @_;
    CORE::symlink($from, $to);
    $self->clean_add_file($to);
}

sub gendir {
    my($self, $dir) = @_;
    $self->makepath($dir);
}

# returns a list of dirs successfully created
sub makepath {
    my($self, $path) = @_;

    return if !defined($path) || -e $path;

    $self->clean_add_path($path);

    return File::Path::mkpath($path, 0, 0755);
}

sub open_cmd {
    my($self, $cmd) = @_;
    # untaint some %ENV fields
    local @ENV{ qw(IFS CDPATH ENV BASH_ENV) };
    local $ENV{PATH} = untaint_path($ENV{PATH});

    # launder for -T
    $cmd = $1 if $cmd =~ /(.*)/;

    my $handle = Symbol::gensym();
    open $handle, "$cmd|" or die "$cmd failed: $!";

    return $handle;
}

sub clean {
    my $self = shift;
    $self->{clean_level} = shift || 2; #2 == really clean, 1 == reconfigure

    $self->new_test_server->clean;
    $self->cmodules_clean;
    $self->sslca_clean;

    for (keys %{ $self->{clean}->{files} }) {
        if (-e $_) {
            debug "unlink $_";
            unlink $_;
        }
        else {
            debug "unlink $_: $!";
        }
    }

    # if /foo comes before /foo/bar, /foo will never be removed
    # hence ensure that sub-dirs are always treated before a parent dir
    for (reverse sort keys %{ $self->{clean}->{dirs} }) {
        if (-d $_) {
            my $dh = Symbol::gensym();
            opendir($dh, $_);
            my $notempty = grep { ! /^\.{1,2}$/ } readdir $dh;
            closedir $dh;
            next if $notempty;
            debug "rmdir $_";
            rmdir $_;
        }
    }
}

my %special_tokens = (
    nextavailableport => sub { shift->server->select_next_port }
);

sub replace {
    my $self = shift;
    my $file = $Apache::TestConfig::File
        ? "in file $Apache::TestConfig::File" : '';

    s[@(\w+)@]
     [ my $key = lc $1;
       if (my $callback = $special_tokens{$key}) {
           $self->$callback;
       }
       elsif (exists $self->{vars}->{$key}) {
           $self->{vars}->{$key};
       }
       else {
           die "invalid token: \@$1\@ $file\n";
       }
     ]ge;
}

#need to configure the vhost port for redirects and $ENV{SERVER_PORT}
#to have the correct values
my %servername_config = (
    0 => sub {
        my($name, $port) = @_;
        [ServerName => ''], [Port => 0];
    },
    1 => sub {
        my($name, $port) = @_;
        [ServerName => $name], [Port => $port];
    },
    2 => sub {
        my($name, $port) = @_;
        [ServerName => "$name:$port"];
    },
);

sub servername_config {
    my $self = shift;
    $self->server->version_of(\%servername_config)->(@_);
}

sub parse_vhost {
    my($self, $line) = @_;

    my($indent, $module, $namebased);
    if ($line =~ /^(\s*)<VirtualHost\s+(?:_default_:|([^:]+):(?!:))?(.*?)\s*>\s*$/) {
        $indent    = $1 || "";
        $namebased = $2 || "";
        $module    = $3;
    }
    else {
        return undef;
    }

    my $vars = $self->{vars};
    my $mods = $self->{modules};
    my $have_module = "$module.c";
    my $ssl_module = $vars->{ssl_module};

    #if module ends with _ssl and it is not the module that implements ssl,
    #then assume this module is a vhost with SSLEngine On (or similar)
    #see mod_echo in extra.conf.in for example
    if ($module =~ /^(mod_\w+)_ssl$/ and $have_module ne $ssl_module) {
        $have_module = "$1.c"; #e.g. s/mod_echo_ssl.c/mod_echo.c/
        return undef unless $mods->{$ssl_module};
    }

    #don't allocate a port if this module is not configured
    #assumes the configuration is inside an <IfModule $have_module>
    if ($module =~ /^mod_/ and not $mods->{$have_module}) {
        return undef;
    }

    #allocate a port and configure this module into $self->{vhosts}
    my $port = $self->new_vhost($module, $namebased);

    #extra config that should go *inside* the <VirtualHost ...>
    my @in_config = $self->servername_config($namebased
                                                 ? $namebased
                                                 : $vars->{servername},
                                             $port);

    my @out_config = ();
    if ($self->{vhosts}->{$module}->{namebased} < 2) {
        #extra config that should go *outside* the <VirtualHost ...>
        @out_config = ([Listen => '0.0.0.0:' . $port]);

        if ($self->{vhosts}->{$module}->{namebased}) {
            push @out_config => [NameVirtualHost => "*:$port"];
        }
    }

    $self->{vars}->{$module . '_port'} = $port;

    #there are two ways of building a vhost
    #first is when we parse test .pm and .c files
    #second is when we scan *.conf.in
    my $form_postamble = sub {
        my $indent = shift;
        for my $pair (@_) {
            $self->postamble("$indent@$pair");
        }
    };

    my $form_string = sub {
        my $indent = shift;
        join "\n", map { "$indent@$_\n" } @_;
    };

    my $double_indent = $indent ? $indent x 2 : ' ' x 4;
    return {
        port          => $port,
        #used when parsing .pm and .c test modules
        in_postamble  => sub { $form_postamble->($double_indent, @in_config) },
        out_postamble => sub { $form_postamble->($indent, @out_config) },
        #used when parsing *.conf.in files
        in_string     => $form_string->($double_indent, @in_config),
        out_string    => $form_string->($indent, @out_config),
        line          => "$indent<VirtualHost " . ($namebased ? '*' : '_default_') .
                         ":$port>",
    };
}

sub find_and_load_module {
    my ($self, $name) = @_;
    my $mod_path = $self->find_apache_module($name) or return;
    my ($sym) = $name =~ m/mod_(\w+)\./;

    if ($mod_path && -e $mod_path) {
        $self->preamble(IfModule => "!mod_$sym.c",
                        qq{LoadModule ${sym}_module "$mod_path"\n});
    }
    return 1;
}

sub replace_vhost_modules {
    my $self = shift;

    if (my $cfg = $self->parse_vhost($_)) {
        $_ = '';
        for my $key (qw(out_string line in_string)) {
            next unless $cfg->{$key};
            $_ .= "$cfg->{$key}\n";
        }
    }
}

sub replace_vars {
    my($self, $in, $out) = @_;

    local $_;
    while (<$in>) {
        $self->replace;
        $self->replace_vhost_modules;
        print $out $_;
    }
}

sub index_html_template {
    my $self = shift;
    return "welcome to $self->{server}->{name}\n";
}

sub generate_index_html {
    my $self = shift;
    my $dir = $self->{vars}->{documentroot};
    $self->gendir($dir);
    my $file = catfile $dir, 'index.html';
    return if -e $file;
    my $fh = $self->genfile($file);
    print $fh $self->index_html_template;
}

sub types_config_template {
    return <<EOF;
text/html  html htm
image/gif  gif
image/jpeg jpeg jpg jpe
image/png  png
text/plain asc txt
EOF
}

sub generate_types_config {
    my $self = shift;

    # handle the case when mod_mime is built as a shared object
    # but wasn't included in the system-wide httpd.conf
    $self->find_and_load_module('mod_mime.so');

    unless ($self->{inherit_config}->{TypesConfig}) {
        my $types = catfile $self->{vars}->{t_conf}, 'mime.types';
        unless (-e $types) {
            my $fh = $self->genfile($types);
            print $fh $self->types_config_template;
            close $fh;
        }
        $self->postamble(<<EOI);
<IfModule mod_mime.c>
    TypesConfig "$types"
</IfModule>
EOI
    }
}

# various dup bugs in older perl and perlio in perl < 5.8.4 need a
# workaround to explicitly rewind the dupped DATA fh before using it
my $DATA_pos = tell DATA;
sub httpd_conf_template {
    my($self, $try) = @_;

    my $in = Symbol::gensym();
    if (open $in, $try) {
        return $in;
    }
    else {
        my $dup = Symbol::gensym();
        open $dup, "<&DATA" or die "Can't dup DATA: $!";
        seek $dup, $DATA_pos, 0; # rewind to the beginning
        return $dup; # so we don't close DATA
    }
}

#certain variables may not be available until certain config files
#are generated.  for example, we don't know the ssl port until ssl.conf.in
#is parsed.  ssl port is needed for proxyssl testing

sub check_vars {
    my $self = shift;
    my $vars = $self->{vars};

    unless ($vars->{proxyssl_url}) {
        my $ssl = $self->{vhosts}->{ $vars->{ssl_module_name} };
        if ($ssl) {
            $vars->{proxyssl_url} ||= $ssl->{hostport};
        }

        if ($vars->{proxyssl_url}) {
            unless ($vars->{maxclients_preset}) {
                $vars->{minclients}++;
                $vars->{maxclients}++;
            }
        }
    }
}

sub extra_conf_files_needing_update {
    my $self = shift;

    my @need_update = ();
    finddepth(sub {
        return unless /\.in$/;
        (my $generated = $File::Find::name) =~ s/\.in$//;
        push @need_update, $generated
            unless -e $generated && -M $generated < -M $File::Find::name;
    }, $self->{vars}->{t_conf});

    return @need_update;
}

sub generate_extra_conf {
    my $self = shift;

    my(@extra_conf, @conf_in, @conf_files);

    finddepth(sub {
        return unless /\.in$/;
        push @conf_in, catdir $File::Find::dir, $_;
    }, $self->{vars}->{t_conf});

    #make ssl port always be 8530 when available
    for my $file (@conf_in) {
        if (basename($file) =~ /^ssl/) {
            unshift @conf_files, $file;
        }
        else {
            push @conf_files, $file;
        }
    }

    for my $file (@conf_files) {
        (my $generated = $file) =~ s/\.in$//;
        debug "Will 'Include' $generated config file";
        push @extra_conf, $generated;
    }

    # regenerate .conf files
    for my $file (@conf_files) {
        local $Apache::TestConfig::File = $file;

        my $in = Symbol::gensym();
        open($in, $file) or next;

        (my $generated = $file) =~ s/\.in$//;
        my $out = $self->genfile($generated, $file);
        $self->replace_vars($in, $out);

        close $in;
        close $out;

        $self->check_vars;
    }

    #we changed order to give ssl the first port after DEFAULT_PORT
    #but we want extra.conf Included first so vhosts inherit base config
    #such as LimitRequest*
    return [ sort @extra_conf ];
}

sub sslca_can {
    my($self, $check) = @_;

    my $vars = $self->{vars};
    return 0 unless $self->{modules}->{ $vars->{ssl_module} };
    return 0 unless -d "$vars->{t_conf}/ssl";

    require Apache::TestSSLCA;

    if ($check) {
        my $openssl = Apache::TestSSLCA::openssl();
        if (which($openssl)) {
            return 1;
        }

        error "cannot locate '$openssl' program required to generate SSL CA";
        exit(1);
    }

    return 1;
}

sub sslca_generate {
    my $self = shift;

    my $ca = $self->{vars}->{sslca};
    return if $ca and -d $ca; #t/conf/ssl/ca

    return unless $self->sslca_can(1);

    Apache::TestSSLCA::generate($self);
}

sub sslca_clean {
    my $self = shift;

    # XXX: httpd config is required, for now just skip ssl clean if
    # there is none. should probably add some flag which will tell us
    # when httpd_config was already run
    return unless $self->{vars}->{httpd} && $self->{vars}->{ssl_module};

    return unless $self->sslca_can;

    Apache::TestSSLCA::clean($self);
}

#XXX: just a quick hack to support t/TEST -ssl
#outside of httpd-test/perl-framework
sub generate_ssl_conf {
    my $self = shift;
    my $vars = $self->{vars};
    my $conf = "$vars->{t_conf}/ssl";
    my $httpd_test_ssl = "../httpd-test/perl-framework/t/conf/ssl";
    my $ssl_conf = "$vars->{top_dir}/$httpd_test_ssl";

    if (-d $ssl_conf and not -d $conf) {
        $self->gendir($conf);
        for (qw(ssl.conf.in)) {
            $self->cpfile("$ssl_conf/$_", "$conf/$_");
        }
        for (qw(certs keys crl)) {
            $self->symlink("$ssl_conf/$_", "$conf/$_");
        }
    }
}

sub find_in_inc {
    my($self, $dir) = @_;
    for my $path (@INC) {
        my $location = "$path/$dir";
        return $location if -d $location;
    }
    return "";
}

sub prepare_t_conf {
    my $self = shift;
    $self->gendir($self->{vars}->{t_conf});
}

my %aliases = (
    "perl-pod"     => "perlpod",
    "binary-httpd" => "httpd",
    "binary-perl"  => "perl",
);
sub generate_httpd_conf {
    my $self = shift;
    my $vars = $self->{vars};

    #generated httpd.conf depends on these things to exist
    $self->generate_types_config;
    $self->generate_index_html;

    $self->gendir($vars->{t_logs});
    $self->gendir($vars->{t_conf});

    my @very_last_postamble = ();
    if (my $extra_conf = $self->generate_extra_conf) {
        for my $file (@$extra_conf) {
            my $entry;
            if ($file =~ /\.conf$/) {
                next if $file =~ m|/httpd\.conf$|;
                $entry = qq(Include "$file");
            }
            elsif ($file =~ /\.pl$/) {
                $entry = qq(<IfModule mod_perl.c>\n    PerlRequire "$file"\n</IfModule>\n);
            }
            else {
                next;
            }

            # put the .last includes very last
            if ($file =~ /\.last\.(conf|pl)$/) {
                 push @very_last_postamble, $entry;
            }
            else {
                $self->postamble($entry);
            }

        }
    }

    $self->configure_proxy;

    my $conf_file = $vars->{t_conf_file};
    my $conf_file_in = join '.', $conf_file, 'in';

    my $in = $self->httpd_conf_template($conf_file_in);

    my $out = $self->genfile($conf_file);

    $self->find_and_load_module('mod_alias.so');

    $self->preamble_run($out);

    for my $name (qw(user group)) { #win32
        if ($vars->{$name}) {
            print $out qq[\u$name    "$vars->{$name}"\n];
        }
    }

    #2.0: ServerName $ServerName:$Port
    #1.3: ServerName $ServerName
    #     Port       $Port
    my @name_cfg = $self->servername_config($vars->{servername},
                                            $vars->{port});
    for my $pair (@name_cfg) {
        print $out "@$pair\n";
    }

    $self->replace_vars($in, $out);

    # handle the case when mod_alias is built as a shared object
    # but wasn't included in the system-wide httpd.conf

    print $out "<IfModule mod_alias.c>\n";
    for (keys %aliases) {
        next unless $vars->{$aliases{$_}};
        print $out "    Alias /getfiles-$_ $vars->{$aliases{$_}}\n";
    }
    print $out "</IfModule>\n";

    print $out "\n";

    $self->postamble_run($out);

    print $out join "\n", @very_last_postamble;

    close $in;
    close $out or die "close $conf_file: $!";
}

sub need_reconfiguration {
    my($self, $conf_opts) = @_;
    my @reasons = ();
    my $vars = $self->{vars};

    # if '-port select' we need to check from scratch which ports are
    # available
    if (my $port = $conf_opts->{port} || $Apache::TestConfig::Argv{port}) {
        if ($port eq 'select') {
            push @reasons, "'-port $port' requires reconfiguration";
        }
    }

    my $exe = $vars->{apxs} || $vars->{httpd} || '';
    # if httpd.conf is older than executable
    push @reasons,
        "$exe is newer than $vars->{t_conf_file}"
            if -e $exe &&
               -e $vars->{t_conf_file} &&
               -M $exe < -M $vars->{t_conf_file};

    # any .in files are newer than their derived versions?
    if (my @files = $self->extra_conf_files_needing_update) {
        # invalidate the vhosts cache, since a different port could be
        # assigned on reparse
        $self->{vhosts} = {};
        for my $file (@files) {
            push @reasons, "$file.in is newer than $file";
        }
    }

    # if special env variables are used (since they can change any time)
    # XXX: may be we could check whether they have changed since the
    # last run and thus avoid the reconfiguration?
    {
        my $passenv = passenv();
        if (my @env_vars = grep { $ENV{$_} } keys %$passenv) {
            push @reasons, "environment variables (@env_vars) are set";
        }
    }

    # if the generated config was created with a version of Apache-Test
    # less than the current version
    {
      my $current = Apache::Test->VERSION;
      my $config  = $self->{apache_test_version};

      if (! $config || $config < $current) {
          push @reasons, "configuration generated with old Apache-Test";
      }
    }

    return @reasons;
}

sub error_log {
    my($self, $rel) = @_;
    my $file = catfile $self->{vars}->{t_logs}, 'error_log';
    my $rfile = abs2rel $file, $self->{vars}->{top_dir};
    return wantarray ? ($file, $rfile) :
      $rel ? $rfile : $file;
}

#utils

#For Win32 systems, stores the extensions used for executable files
#They may be . prefixed, so we will strip the leading periods.

my @path_ext = ();

if (WIN32) {
    if ($ENV{PATHEXT}) {
        push @path_ext, split ';', $ENV{PATHEXT};
        for my $ext (@path_ext) {
            $ext =~ s/^\.*(.+)$/$1/;
        }
    }
    else {
        #Win9X: doesn't have PATHEXT
        push @path_ext, qw(com exe bat);
    }
}

sub which {
    my $program = shift;

    return undef unless $program;

    my @dirs = File::Spec->path();

    require Config;
    my $perl_bin = $Config::Config{bin} || '';
    push @dirs, $perl_bin if $perl_bin and -d $perl_bin;

    for my $base (map { catfile $_, $program } @dirs) {
        if ($ENV{HOME} and not WIN32) {
            # only works on Unix, but that's normal:
            # on Win32 the shell doesn't have special treatment of '~'
            $base =~ s/~/$ENV{HOME}/o;
        }

        return $base if -x $base && -f _;

        if (WIN32) {
            for my $ext (@path_ext) {
                return "$base.$ext" if -x "$base.$ext" && -f _;
            }
        }
    }
}

sub apxs {
    my($self, $q, $ok_fail) = @_;
    return unless $self->{APXS};
    my $val;
    unless (exists $self->{_apxs}{$q}) {
        local @ENV{ qw(IFS CDPATH ENV BASH_ENV) };
        local $ENV{PATH} = untaint_path($ENV{PATH});
        my $devnull = devnull();
        my $apxs = shell_ready($self->{APXS});
        $val = qx($apxs -q $q 2>$devnull);
        chomp $val if defined $val; # apxs post-2.0.40 adds a new line
        if ($val) {
            $self->{_apxs}{$q} = $val;
        }
        unless ($val) {
            if ($ok_fail) {
                return "";
            }
            else {
                warn "APXS ($self->{APXS}) query for $q failed\n";
                return $val;
            }
        }
    }
    $self->{_apxs}{$q};
}

# return an untainted PATH
sub untaint_path {
    my $path = shift;
    ($path) = ( $path =~ /(.*)/ );
    # win32 uses ';' for a path separator, assume others use ':'
    my $sep = WIN32 ? ';' : ':';
    # -T disallows relative and empty directories in the PATH
    return join $sep, grep File::Spec->file_name_is_absolute($_),
        grep length($_), split /$sep/, $path;
}

sub pop_dir {
    my $dir = shift;

    my @chunks = splitdir $dir;
    while (my $remove = shift) {
        pop @chunks if $chunks[-1] eq $remove;
    }

    catfile @chunks;
}

sub add_inc {
    my $self = shift;
    return if $ENV{MOD_PERL}; #already setup by mod_perl
    require lib;
    # make sure that Apache-Test/lib will be first in @INC,
    # followed by modperl-2.0/lib (or some other project's lib/),
    # followed by blib/ and finally system-wide libs.
    my $top_dir = $self->{vars}->{top_dir};
    my @dirs = map { catdir $top_dir, "blib", $_ } qw(lib arch);

    my $apache_test_dir = catdir $top_dir, "Apache-Test";
    unshift @dirs, $apache_test_dir if -d $apache_test_dir;

    lib::->import(@dirs);

    if ($ENV{APACHE_TEST_LIVE_DEV}) {
        # add lib/ in a separate call to ensure that it'll end up on
        # top of @INC
        my $lib_dir = catdir $top_dir, "lib";
        lib::->import($lib_dir) if -d $lib_dir;
    }

    #print join "\n", "add_inc", @INC, "";
}

#freeze/thaw so other processes can access config

sub thaw {
    my $class = shift;
    $class->new({thaw => 1, @_});
}

sub freeze {
    require Data::Dumper;
    local $Data::Dumper::Terse = 1;
    my $data = Data::Dumper::Dumper(shift);
    chomp $data;
    $data;
}

sub sync_vars {
    my $self = shift;

    return if $self->{save}; #this is not a cached config

    my $changed = 0;
    my $thaw = $self->thaw;
    my $tvars = $thaw->{vars};
    my $svars = $self->{vars};

    for my $key (@_) {
        for my $v ($tvars, $svars) {
            if (exists $v->{$key} and not defined $v->{$key}) {
                $v->{$key} = ''; #rid undef
            }
        }
        next if exists $tvars->{$key} and exists $svars->{$key} and
                       $tvars->{$key} eq $svars->{$key};
        $tvars->{$key} = $svars->{$key};
        $changed = 1;
    }

    return unless $changed;

    $thaw->{save} = 1;
    $thaw->save;
}

sub save {
    my($self) = @_;

    return unless $self->{save};

    # add in the Apache-Test version for later comparisions
    $self->{apache_test_version} = Apache::Test->VERSION;

    my $name = 'apache_test_config';
    my $file = catfile $self->{vars}->{t_conf}, "$name.pm";
    my $fh = $self->genfile($file);

    debug "saving config data to $name.pm";

    (my $obj = $self->freeze) =~ s/^/    /;

    print $fh <<EOF;
package $name;

sub new {
$obj;
}

1;
EOF

    close $fh or die "failed to write $file: $!";
}

sub as_string {
    my $cfg = '';
    my $command = '';

    # httpd opts
    my $test_config = Apache::TestConfig->new({thaw=>1});
    # XXX: need to run httpd config to get the value of httpd
    if (my $httpd = $test_config->{vars}->{httpd}) {
        $httpd = shell_ready($httpd);
        $command = "$httpd -V";
        $cfg .= "\n*** $command\n";
        $cfg .= qx{$command};

        $cfg .= ldd_as_string($httpd);
    }
    else {
        $cfg .= "\n\n*** The httpd binary was not found\n";
    }

    # perl opts
    my $perl = shell_ready($^X);
    $command = "$perl -V";
    $cfg .= "\n\n*** $command\n";
    $cfg .= qx{$command};

    return $cfg;
}

sub ldd_as_string {
    my $httpd = shift;

    my $command;
    if (OSX) {
        my $otool = which('otool');
        $command = "$otool -L $httpd" if $otool;
    }
    elsif (!WIN32) {
        my $ldd = which('ldd');
        $command = "$ldd $httpd" if $ldd;
    }

    my $cfg = '';
    if ($command) {
        $cfg .= "\n*** $command\n";
        $cfg .= qx{$command};
    }

    return $cfg;
}

# make a string suitable for feed to shell calls (wrap in quotes and
# escape quotes)
sub shell_ready {
    my $arg = shift;
    $arg =~ s!\\?"!\\"!g;
    return qq["$arg"];
}

### Permanent custom configuration functions ###

# determine which configuration file Apache/TestConfigData.pm to use
# (as there could be several). The order searched is:
# 1) $ENV{HOME}/.apache-test/
# 2) in @INC
my $custom_config_path;
sub custom_config_path {

    return $custom_config_path if $custom_config_path;

    my @inc  = ();

    # XXX $ENV{HOME} isn't propagated in mod_perl
    push @inc, catdir $ENV{HOME}, '.apache-test' if $ENV{HOME};

    push @inc, @INC;

    for (@inc) {
        my $candidate = File::Spec->rel2abs(catfile $_, CUSTOM_CONFIG_FILE);
        next unless -e $candidate;
        # launder for -T
        ($candidate) = $candidate =~ /^(.*)/;
        return $custom_config_path = $candidate;
    }

    return '';
}

# tries to nuke all occurences of custom config
# used by things outside the A-T test suite
sub custom_config_nuke {
    my $cwd = fastcwd();

    # 1) create a fake empty (blib/)?lib/Apache/TestConfigData.pm
    # (don't delete it since it may mess up with MakeMaker)
    my $path = catfile $cwd, "lib", Apache::TestConfig::CUSTOM_CONFIG_FILE;
    # overwrite the empty stub
    Apache::TestConfig::custom_config_write($path, '') if -e $path;

    $path = catfile $cwd, "blib", "lib",
        Apache::TestConfig::CUSTOM_CONFIG_FILE;
    if (-e $path) {
        my $mode = (stat _)[2];
        my $mode_new = $mode | 0200;
        chmod $mode_new, $path;
        debug  "emptying $path";
        Apache::TestConfig::custom_config_write($path, '');
        chmod $mode, $path;
    }

    # 2) go through @INC = ~/.apache-test and nuke any occurences of
    #    CUSTOM_CONFIG_FILE
    my @inc  = ();

    push @inc, catdir $ENV{HOME}, '.apache-test' if $ENV{HOME};

    push @inc, @INC;

    for (@inc) {
        my $victim = File::Spec->rel2abs(catfile $_, CUSTOM_CONFIG_FILE);
        next unless -e $victim;
        debug "unlinking $victim";
        unlink $victim;
    }
}

sub custom_config_exists {
    # try to load custom config if it wasn't loaded yet (there are
    # many entry points to this API)
    custom_config_load();

    # it's enough to check whether we have a custom
    # config for 'httpd' or 'apxs'.
    my $httpd = $Apache::TestConfigData::vars->{httpd} || '';
    return 1 if $httpd && -e $httpd && -x _;

    my $apxs = $Apache::TestConfigData::vars->{apxs} || '';
    return 1 if $apxs && -e $apxs && -x _;

    return 0;
}

# to be used only from Apache-Test/Makefile.PL to write the custom
# configuration module so it'll be copied to blib during 'make' and
# updated to use custom config data during 'make test' and then
# installed system-wide via 'make install'
#
# it gets written only if the custom configuration didn't exist
# already
sub custom_config_file_stub_write {

    return if custom_config_exists();

    # It doesn't matter whether it gets written under modperl-2.0/lib
    # or Apache-Test/lib root, since Apache::TestRun uses the same
    # logic and will update that file with real config data, which
    # 'make install' will then pick and install system-wide. but
    # remember that $FindBin::Bin is the location of top-level
    # 'Makefile.PL'
    require FindBin; # load it only for this particular use
    my $path = catfile $FindBin::Bin, "lib",
        Apache::TestConfig::CUSTOM_CONFIG_FILE;

    # write an empty stub
    Apache::TestConfig::custom_config_write($path, '');
}

sub custom_config_save {
    my $self = shift;
    my $conf_opts = shift;

    if ($ENV{APACHE_TEST_NO_STICKY_PREFERENCES}) {
        debug "APACHE_TEST_NO_STICKY_PREFERENCES=1 => " .
            "skipping save of custom config data";
        return;
    }

    my $vars = $self->{vars};
    my $config_dump = '';

    # minimum httpd and/or apxs needs to be set
    return 0 unless $vars->{httpd} or $Apache::TestConfigData::vars->{httpd}
        or          $vars->{apxs}  or $Apache::TestConfigData::vars->{apxs};

    # it doesn't matter how these vars were set (httpd may or may not
    # get set using the path to apxs, w/o an explicit -httpd value)
    for (@data_vars_must) {
        next unless my $var = $vars->{$_} || $conf_opts->{$_};
        $config_dump .= qq{    '$_' => '$var',\n};
    }

    # save these vars only if they were explicitly set via command line
    # options. For example if someone builds A-T as user 'foo', then
    # installs it as root and we save it, all users will now try to
    # configure under that user 'foo' which won't quite work.
    for (@data_vars_opt) {
        next unless my $var = $conf_opts->{$_};
        $config_dump .= qq{    '$_' => '$var',\n};
    }

    if (IS_APACHE_TEST_BUILD) {
        my $path = catfile $vars->{top_dir}, 'lib', CUSTOM_CONFIG_FILE;
        # if it doesn't exist, then we already have a global config file
        # if it does, then we have need to update it and its blib/ copy
        if (-e $path and custom_config_path_is_writable($path)) {
            custom_config_write($path, $config_dump);
            # also update blib/lib, since usually that's the one that
            # appears in @INC when t/TEST is run. and it won't be
            # synced with blib/ unless 'make' was run
            my $blib_path = catfile $vars->{top_dir},
                'blib', 'lib', CUSTOM_CONFIG_FILE;
            if (-e $blib_path and custom_config_path_is_writable($blib_path)) {
                custom_config_write($blib_path, $config_dump);
            }
            return 1;
        }
    }

    my $path;
    if ($path = custom_config_path() ) {
        # do nothing, the config file already exists (global)
        debug "Found custom config '$path'";
    }
    elsif (File::Spec->file_name_is_absolute(__FILE__)) {
        # next try a global location, as if it was configured before
        # Apache::Test's 'make install' (install in the same dir as
        # Apache/TestRun.pm)
        # if the filename is not absolute that means that we are still
        # in Apache-Test build (could just test for IS_APACHE_TEST_BUILD)
        my $base = dirname dirname __FILE__;
        $path = catdir $base, CUSTOM_CONFIG_FILE;
    }

    # check whether we can write to the directory of the chosen path
    # (e.g. root-owned directory)
    if ($path and custom_config_path_is_writable($path)) {
        custom_config_write($path, $config_dump);
        return 1;
    }
    # if we have no writable path yet, try to use ~
    elsif ($ENV{HOME}) {
        $path = catfile $ENV{HOME}, '.apache-test', CUSTOM_CONFIG_FILE;
        if ($path and custom_config_path_is_writable($path)) {
            custom_config_write($path, $config_dump);
            return 1;
        }
    }

    # XXX: should we croak since we failed to write config
    error "Failed to find a config file to save the custom " .
        "configuration in";
    return 0;
}

sub custom_config_path_is_writable {
    my $path = shift;

    return 0 unless $path;

    my $file_created    = '';
    my $top_dir_created = '';
    # first make sure that the file is writable if it exists
    # already (it might be non-writable if installed via EU::MM or in
    # blib/)
    if (-e $path) {
        my $mode = (stat _)[2];
        $mode |= 0200;
        chmod $mode, $path; # it's ok if we fail
        # keep it writable if we have changed it from not being one
        # so that custom_config_save will be able to just overwrite it
    }
    else {
        my $dir = dirname $path;
        if ($dir and !-e $dir) {
            my @dirs = File::Path::mkpath($dir, 0, 0755);
            # the top level dir to nuke on cleanup if it was created
            $top_dir_created = shift @dirs if @dirs;
        }
        # not really create yet, but will be in the moment
        $file_created = 1;
    }

    # try to open for append (even though it may not exist
    my $fh = Symbol::gensym;
    if (open $fh, ">>$path") {
        close $fh;
        # cleanup if we just created the file
        unlink $path if $file_created;
        File::Path::rmtree([$top_dir_created], 0, 0) if $top_dir_created;
        return 1;
    }

    return 0;
}

sub custom_config_write {
    my($path, $config_dump) = @_;

    my $pkg = << "EOC";
package Apache::TestConfigData;

use strict;
use warnings;

\$Apache::TestConfigData::vars = {
$config_dump
};

1;

=head1 NAME

Apache::TestConfigData - Configuration file for Apache::Test

=cut
EOC

    debug "Writing custom config $path";
    my $dir = dirname $path;
    File::Path::mkpath($dir, 0, 0755) unless -e $dir;
    my $fh = Symbol::gensym;
    open $fh, ">$path" or die "Cannot open $path: $!";
    print $fh $pkg;
    close $fh;
}

sub custom_config_add_conf_opts {
    my $args = shift;

    return unless $Apache::TestConfigData::vars and
        keys %$Apache::TestConfigData::vars;

    debug "overlaying custom config data";

    # the logic is quite complicated with 'httpd' and 'apxs', since
    # one is enough to run the test suite, and we need to avoid the
    # situation where both are saved in custom config but only one
    # (let's say httpd) is overriden by the command line /env var and
    # a hell may break loose if we take that overriden httpd value and
    # also use apxs from custom config which could point to a different
    # server. So if there is an override of apxs or httpd, do not use
    # the custom config for apxs or httpd.
    my $vars_must_overriden = grep {
        $ENV{ $vars_to_env{$_} } || $args->{$_}
    } @data_vars_must;

    # mod_perl 2.0 build always knows the right httpd location (and
    # optionally apxs)
    $vars_must_overriden++ if IS_MOD_PERL_2_BUILD();

    unless ($vars_must_overriden) {
        for (@data_vars_must) {
            next unless $Apache::TestConfigData::vars->{$_};
            $args->{$_} = $Apache::TestConfigData::vars->{$_};
        }
    }

    for (@data_vars_opt) {
        next unless $Apache::TestConfigData::vars->{$_};
        # env vars override custom config
        my $env_value = $ENV{ $vars_to_env{$_} };
        next unless defined $env_value and length $env_value;
        $args->{$_} ||= $Apache::TestConfigData::vars->{$_};
    }
}

my $custom_config_loaded = 0;
sub custom_config_load {

    if ($ENV{APACHE_TEST_NO_STICKY_PREFERENCES}) {
        debug "APACHE_TEST_NO_STICKY_PREFERENCES=1 => " .
            "skipping load of custom config data";
        return;
    }

    if ($ENV{APACHE_TEST_INTERACTIVE_CONFIG_TEST}) {
        debug "APACHE_TEST_INTERACTIVE_CONFIG_TEST=1 => " .
            "skipping load of custom config data";
        return;
    }

    return if $custom_config_loaded;

    if (my $custom_config_path = custom_config_path()) {
        debug "loading custom config data from: '$custom_config_path'";
        $custom_config_loaded++;
        require $custom_config_path;
    }
    else {
        debug "no custom config data was loaded";
    }
}

sub custom_config_first_time {
    my $self = shift;
    my $conf_opts = shift;

    # we can't prompt when STDIN is not attached to tty, unless we
    # were told that's it OK via env var (in which case some program
    # will feed the interactive prompts
    unless (-t STDIN || $ENV{APACHE_TEST_INTERACTIVE_PROMPT_OK}) {
        error "STDIN is not attached to tty, skip interactive config";
        Apache::TestRun::skip_test_suite();
    }

    my $vars = $self->{vars};

    print qq[

We are now going to configure the Apache-Test framework.
This configuration process needs to be done only once.

];

    print qq[

First we need to know where the 'httpd' executable is located.
If you have more than one Apache server is installed, make sure
you supply the path to the one you are going to use for testing.
You can always override this setting at run time via the '-httpd'
option. For example:

  % t/TEST -httpd /path/to/alternative/httpd

or via the environment variable APACHE_TEST_HTTPD. For example:

  % APACHE_TEST_HTTPD=/path/to/alternative/httpd t/TEST

If for some reason you want to skip the test suite, type: skip
];

    {
        my %choices = ();
        my @tries = qw(httpd httpd2);
        # Win32 uses Apache not apache
        push @tries, WIN32 ? qw(Apache) : qw(apache);
        for (grep defined $_,
             map({ catfile $vars->{$_}, $vars->{target} } qw(sbindir bindir)),
             $self->default_httpd, which($vars->{target}),
             $ENV{APACHE}, $ENV{APACHE2},
             $ENV{APACHE_TEST_HTTPD}, $ENV{APACHE_TEST_HTTPD2},
             map {which($_)} @tries) {
            $choices{$_}++ if -e $_ && -x _;
        }
        my $optional = 0;
        my $wanted = 'httpd';
        $vars->{$wanted} =
            _custom_config_prompt_path($wanted, \%choices, $optional);
    }

    print qq[

Next we need to know where the 'apxs' script is located. This script
provides a lot of information about the apache installation, and makes
it easier to find things. However it's not available on all platforms,
therefore it's optional.

If you don't have it installed it's not a problem. Just press Enter.

Notice that if you have Apache 2.x installed that script could be
called as 'apxs2'.

If you have more than one Apache server is installed, make sure you
supply the path to the apxs script you are going to use for testing.
You can always override this setting at run time via the '-apxs'
option. For example:

  % t/TEST -apxs /path/to/alternative/apxs

or via the environment variable APACHE_TEST_APXS. For example:

  % APACHE_TEST_APXS=/path/to/alternative/apxs t/TEST

];
    {
        my %choices = ();
        for (grep defined $_,
             map({ catfile $vars->{$_}, 'apxs' } qw(sbindir bindir)),
             $self->default_apxs,
             $ENV{APXS},  $ENV{APACHE_TEST_APXS},  which('apxs'),
             $ENV{APXS2}, $ENV{APACHE_TEST_APXS2}, which('apxs2')) {
            $choices{$_}++ if -e $_ && -x _;
        }
        my $optional = 1;
        my $wanted = 'apxs';
        $vars->{$wanted} =
            _custom_config_prompt_path($wanted, \%choices, $optional);
    }

    $self->custom_config_save($conf_opts);

    # we probably could reconfigure on the fly ($self->configure), but
    # the problem is various cached data which won't be refreshed. so
    # the simplest is just to restart the run from scratch
    Apache::TestRun::rerun($vars);
}

sub _custom_config_prompt_path {
    my($wanted, $rh_choices, $optional) = @_;

    my $ans;
    my $default = '';
    my $optional_str = $optional ? " (optional)" : '';
    my $prompt =
        "\nPlease provide a full path to$optional_str '$wanted' executable";

    my @choices = ();
    if (%$rh_choices) {
        $prompt .= " or choose from the following options:\n\n";
        my $c = 0;
        for (sort keys %$rh_choices) {
            $c++;
            $prompt .= "    [$c] $_\n";
            push @choices, $_;
        }
        $prompt .= " \n";
        $default = 1; # a wild guess
    }
    else {
        $prompt .= ":\n\n";
    }

    while (1) {
        $ans = ExtUtils::MakeMaker::prompt($prompt, $default);

        # strip leading/closing spaces
        $ans =~ s/^\s*|\s*$//g;

        # convert the item number to the path
        if ($ans =~ /^(\d+)$/) {
            if ($1 > 0 and $choices[$1-1]) {
                $ans = $choices[$1-1];
            }
            else {
                warn "The choice '$ans' doesn't exist\n";
                next;
            }
        }

        if ($optional) {
            return '' unless $ans;
        }

        # stop the test suite without an error (so automatic tools
        # like CPAN.pm will be able to continue)
        if (lc($ans) eq 'skip' && !$optional) {
            Apache::TestRun::skip_test_suite();
            next; # in case they change their mind
        }

        unless (File::Spec->file_name_is_absolute($ans)) {
            warn "The path '$ans' is not an absolute path. " .
                "Please specify an absolute path\n";
            next;
        }

        warn("'$ans' doesn't exist.\n"),     next unless -e $ans;
        warn("'$ans' is not a file.\n"),     next unless -f _;
        warn("'$ans' is not executable.\n"), next unless -x _;

        return $ans;
    }
}

1;

=head1 NAME

Apache::TestConfig -- Test Configuration setup module

=head1 SYNOPSIS

  use Apache::TestConfig;

  my $cfg = Apache::TestConfig->new(%args)
  my $fh = $cfg->genfile($file);
  $cfg->writefile($file, $content);
  $cfg->gendir($dir);
  ...

=head1 DESCRIPTION

C<Apache::TestConfig> is used in creating the C<Apache::Test>
configuration files.

=head1 FUNCTIONS

=over

=item genwarning()

  my $warn = $cfg->genwarning($filename)

genwarning() returns a warning string as a comment, saying that the
file was autogenerated and that it's not a good idea to modify this
file. After the warning a perl trace of calls to this this function is
appended. This trace is useful for finding what code has created the
file.

  my $warn = $cfg->genwarning($filename, $from_filename)

If C<$from_filename> is specified it'll be used in the warning to tell
which file it was generated from.

genwarning() automatically recognizes the comment type based on the
file extension. If the extension is not recognized, the default C<#>
style is used.

Currently it support C<E<lt>!-- --E<gt>>, C</* ... */> and C<#>
styles.

=item genfile()

  my $fh = $cfg->genfile($file);

genfile() creates a new file C<$file> for writing and returns a file
handle.

If parent directories of C<$file> don't exist they will be
automagically created.

The file C<$file> and any created parent directories (if found empty)
will be automatically removed on cleanup.

A comment with a warning and calls trace is added to the top of this
file. See genwarning() for more info about this comment.

  my $fh = $cfg->genfile($file, $from_file);

If C<$from_filename> is specified it'll be used in the warning to tell
which file it was generated from.

  my $fh = $cfg->genfile($file, $from_file, $nowarning);

If C<$nowarning> is true, the warning won't be added. If using this
optional argument and there is no C<$from_file> you must pass undef as
in:

  my $fh = $cfg->genfile($file, undef, $nowarning);


=item writefile()

  $cfg->writefile($file, $content, [$nowarning]);

writefile() creates a new file C<$file> with the content of
C<$content>.

A comment with a warning and calls trace is added to the top of this
file unless C<$nowarnings> is passed and set to a true value. See
genwarning() for more info about this comment.

If parent directories of C<$file> don't exist they will be
automagically created.

The file C<$file> and any created parent directories (if found empty)
will be automatically removed on cleanup.

=item write_perlscript()

  $cfg->write_perlscript($filename, @lines);

Similar to writefile() but creates an executable Perl script with
correctly set shebang line.

=item gendir()

  $cfg->gendir($dir);

gendir() creates a new directory C<$dir>.

If parent directories of C<$dir> don't exist they will be
automagically created.

The directory C<$dir> and any created parent directories will be
automatically removed on cleanup if found empty.

=back

=head1 Environment Variables

The following environment variables affect the configuration and the
run-time of the C<Apache::Test> framework:

=head2 APACHE_TEST_COLOR

To aid visual control over the configuration process and the run-time
phase, C<Apache::Test> uses coloured fonts when the environment
variable C<APACHE_TEST_COLOR> is set to a true value.

=head2 APACHE_TEST_LIVE_DEV

When using C<Apache::Test> during the project development phase, it's
often convenient to have the I<project/lib> (live) directory appearing
first in C<@INC> so any changes to the Perl modules, residing in it,
immediately affect the server, without a need to rerun C<make> to
update I<blib/lib>. When the environment variable
C<APACHE_TEST_LIVE_DEV> is set to a true value during the
configuration phase (C<t/TEST -config>, C<Apache::Test> will
automatically unshift the I<project/lib> directory into C<@INC>, via
the autogenerated I<t/conf/modperl_inc.pl> file.

=head2 APACHE_TEST_INTERACTIVE_PROMPT_OK

Normally interactive prompts aren't run when STDIN is not attached to
a tty. But sometimes there is a program that can answer the prompts
(e.g. when testing A-T itself). If this variable is true the
interactive config won't be skipped (if needed).


=head1 Special Placeholders

When generating configuration files from the I<*.in> templates,
special placeholder variables get substituted. To embed a placeholder
use the C<@foo@> syntax. For example in I<extra.conf.in> you can
write:

  Include @ServerRoot@/conf/myconfig.conf

When I<extra.conf> is generated, C<@ServerRoot@> will get replaced
with the location of the server root.

Placeholders are case-insensitive.

Available placeholders:

=head2 Configuration Options

All configuration variables that can be passed to C<t/TEST>, such as
C<MaxClients>, C<DocumentRoot>, C<ServerRoot>, etc. To see the
complete list run:

  % t/TEST --help

and you will find them in the C<configuration options> sections.

=head2 NextAvailablePort

Every time this placeholder is encountered it'll be replaced with the
next available port. This is very useful if you need to allocate a
special port, but not hardcode it. Later when running:

  % t/TEST -port=select

it's possible to run several concurrent test suites on the same
machine, w/o having port collisions.

=head1 AUTHOR

=head1 SEE ALSO

perl(1), Apache::Test(3)

=cut


__DATA__
Listen     0.0.0.0:@Port@

ServerRoot   "@ServerRoot@"
DocumentRoot "@DocumentRoot@"

PidFile     @t_pid_file@
ErrorLog    @t_logs@/error_log
LogLevel    debug

<IfModule mod_log_config.c>
    TransferLog @t_logs@/access_log
</IfModule>

<IfModule mod_cgid.c>
    ScriptSock @t_logs@/cgisock
</IfModule>

ServerAdmin @ServerAdmin@

#needed for http/1.1 testing
KeepAlive       On

HostnameLookups Off

<Directory />
    Options FollowSymLinks
    AllowOverride None
</Directory>

<IfModule @THREAD_MODULE@>
    LockFile             @t_logs@/accept.lock
    StartServers         1
    MinSpareThreads      @MinClients@
    MaxSpareThreads      @MinClients@
    ThreadsPerChild      @MinClients@
    MaxClients           @MaxClientsThreadedMPM@
    MaxRequestsPerChild  0
</IfModule>

<IfModule perchild.c>
    LockFile             @t_logs@/accept.lock
    NumServers           1
    StartThreads         @MinClients@
    MinSpareThreads      @MinClients@
    MaxSpareThreads      @MinClients@
    MaxThreadsPerChild   @MaxClients@
    MaxRequestsPerChild  0
</IfModule>

<IfModule prefork.c>
    LockFile             @t_logs@/accept.lock
    StartServers         @MinClients@
    MinSpareServers      @MinClients@
    MaxSpareServers      @MinClients@
    MaxClients           @MaxClients@
    MaxRequestsPerChild  0
</IfModule>

<IfDefine APACHE1>
    LockFile             @t_logs@/accept.lock
    StartServers         @MinClients@
    MinSpareServers      @MinClients@
    MaxSpareServers      @MinClients@
    MaxClients           @MaxClients@
    MaxRequestsPerChild  0
</IfDefine>

<IfModule mpm_winnt.c>
    ThreadsPerChild      50
    MaxRequestsPerChild  0
</IfModule>

<Location /server-info>
    SetHandler server-info
</Location>

<Location /server-status>
    SetHandler server-status
</Location>

