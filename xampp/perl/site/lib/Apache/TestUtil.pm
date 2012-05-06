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
package Apache::TestUtil;

use strict;
use warnings FATAL => 'all';

use File::Find ();
use File::Path ();
use Exporter ();
use Carp ();
use Config;
use File::Basename qw(dirname);
use File::Spec::Functions qw(catfile catdir file_name_is_absolute tmpdir);
use Symbol ();
use Fcntl qw(SEEK_END);

use Apache::Test ();
use Apache::TestConfig ();

use vars qw($VERSION @ISA @EXPORT @EXPORT_OK %CLEAN);

$VERSION = '0.02';
@ISA     = qw(Exporter);

@EXPORT = qw(t_cmp t_debug t_append_file t_write_file t_open_file
    t_mkdir t_rmtree t_is_equal t_filepath_cmp t_write_test_lib
    t_server_log_error_is_expected t_server_log_warn_is_expected
    t_client_log_error_is_expected t_client_log_warn_is_expected
);

@EXPORT_OK = qw(t_write_perl_script t_write_shell_script t_chown
               t_catfile_apache t_catfile
               t_start_error_log_watch t_finish_error_log_watch);

%CLEAN = ();

$Apache::TestUtil::DEBUG_OUTPUT = \*STDOUT;

# 5.005's Data::Dumper has problems to dump certain datastructures
use constant HAS_DUMPER => eval { $] >= 5.6 && require Data::Dumper; };
use constant INDENT     => 4;

{
    my $f;
    sub t_start_error_log_watch {

        my $name = File::Spec->catfile(Apache::Test::vars->{t_logs}, 'error_log');
        open $f, "$name" or die "ERROR: Cannot open $name: $!\n";
        seek $f, 0, SEEK_END;

        return;
    }

    sub t_finish_error_log_watch {

        local $/ = "\n";
        my @lines = <$f>;
        undef $f;

        return @lines;
    }
}

# because of the prototype and recursive call to itself a forward
# declaration is needed
sub t_is_equal ($$);

# compare any two datastructures (must pass references for non-scalars)
# undef()'s are valid args
sub t_is_equal ($$) {
    my ($a, $b) = @_;
    return 0 unless @_ == 2;

    # this was added in Apache::Test::VERSION 1.12 - remove deprecated
    # logic sometime around 1.15 or mid September, 2004.
    if (UNIVERSAL::isa($a, 'Regexp')) {
        my @warning = ("WARNING!!! t_is_equal() argument order has changed.",
                       "use of a regular expression as the first argument",
                       "is deprecated.  support will be removed soon.");
        t_debug(@warning);
        ($a, $b) = ($b, $a);
    }

    if (defined $a && defined $b) {
        my $ref_a = ref $a;
        my $ref_b = ref $b;
        if (!$ref_a && !$ref_b) {
            return $a eq $b;
        }
        elsif ($ref_a eq 'ARRAY' && $ref_b eq 'ARRAY') {
            return 0 unless @$a == @$b;
            for my $i (0..$#$a) {
                t_is_equal($a->[$i], $b->[$i]) || return 0;
            }
        }
        elsif ($ref_a eq 'HASH' && $ref_b eq 'HASH') {
            return 0 unless (keys %$a) == (keys %$b);
            for my $key (sort keys %$a) {
                return 0 unless exists $b->{$key};
                t_is_equal($a->{$key}, $b->{$key}) || return 0;
            }
        }
        elsif ($ref_b eq 'Regexp') {
            return $a =~ $b;
        }
        else {
            # try to compare the references
            return $a eq $b;
        }
    }
    else {
        # undef == undef! a valid test
        return (defined $a || defined $b) ? 0 : 1;
    }
    return 1;
}



sub t_cmp ($$;$) {
    Carp::carp(join(":", (caller)[1..2]) .
        ' usage: $res = t_cmp($received, $expected, [$comment])')
            if @_ < 2 || @_ > 3;

    my ($received, $expected) = @_;

    # this was added in Apache::Test::VERSION 1.12 - remove deprecated
    # logic sometime around 1.15 or mid September, 2004.
    if (UNIVERSAL::isa($_[0], 'Regexp')) {
        my @warning = ("WARNING!!! t_cmp() argument order has changed.",
                       "use of a regular expression as the first argument",
                       "is deprecated.  support will be removed soon.");
        t_debug(@warning);
        ($received, $expected) = ($expected, $received);
    }

    t_debug("testing : " . pop) if @_ == 3;
    t_debug("expected: " . struct_as_string(0, $expected));
    t_debug("received: " . struct_as_string(0, $received));
    return t_is_equal($received, $expected);
}

# Essentially t_cmp, but on Win32, first converts pathnames
# to their DOS long name.
sub t_filepath_cmp ($$;$) {
    my @a = (shift, shift);
    if (Apache::TestConfig::WIN32) {
        $a[0] = Win32::GetLongPathName($a[0]) if defined $a[0];
        $a[1] = Win32::GetLongPathName($a[1]) if defined $a[1];
    }
    return @_ == 1 ? t_cmp($a[0], $a[1], $_[0]) : t_cmp($a[0], $a[1]);
}


*expand = HAS_DUMPER ?
    sub { map { ref $_ ? Data::Dumper::Dumper($_) : $_ } @_ } :
    sub { @_ };

sub t_debug {
    my $out = $Apache::TestUtil::DEBUG_OUTPUT;
    print $out map {"# $_\n"} map {split /\n/} grep {defined} expand(@_);
}

sub t_open_file {
    my $file = shift;

    die "must pass a filename" unless defined $file;

    # create the parent dir if it doesn't exist yet
    makepath(dirname $file);

    my $fh = Symbol::gensym();
    open $fh, ">$file" or die "can't open $file: $!";
    t_debug("writing file: $file");
    $CLEAN{files}{$file}++;

    return $fh;
}

sub _temp_package_dir {
    return catdir(tmpdir(), 'apache_test');
}

sub t_write_test_lib {
    my $file = shift;

    die "must pass a filename" unless defined $file;

    t_write_file(catdir(_temp_package_dir(), $file), @_);
}

sub t_write_file {
    my $file = shift;

    die "must pass a filename" unless defined $file;

    # create the parent dir if it doesn't exist yet
    makepath(dirname $file);

    my $fh = Symbol::gensym();
    open $fh, ">$file" or die "can't open $file: $!";
    t_debug("writing file: $file");
    print $fh join '', @_ if @_;
    close $fh;
    $CLEAN{files}{$file}++;
}

sub t_append_file {
    my $file = shift;

    die "must pass a filename" unless defined $file;

    # create the parent dir if it doesn't exist yet
    makepath(dirname $file);

    # add to the cleanup list only if we created it now
    $CLEAN{files}{$file}++ unless -e $file;

    my $fh = Symbol::gensym();
    open $fh, ">>$file" or die "can't open $file: $!";
    print $fh join '', @_ if @_;
    close $fh;
}

sub t_write_shell_script {
    my $file = shift;

    my $code = join '', @_;
    my($ext, $shebang);

    if (Apache::TestConfig::WIN32()) {
        $code =~ s/echo$/echo./mg; #required to echo newline
        $ext = 'bat';
        $shebang = "\@echo off\nREM this is a bat";
    }
    else {
        $ext = 'sh';
        $shebang = '#!/bin/sh';
    }

    $file .= ".$ext";
    t_write_file($file, "$shebang\n", $code);
    $ext;
}

sub t_write_perl_script {
    my $file = shift;

    my $shebang = "#!$Config{perlpath}\n";
    my $warning = Apache::TestConfig->thaw->genwarning($file);
    t_write_file($file, $shebang, $warning, @_);
    chmod 0755, $file;
}


sub t_mkdir {
    my $dir = shift;
    makepath($dir);
}

# returns a list of dirs successfully created
sub makepath {
    my($path) = @_;

    return if !defined($path) || -e $path;
    my $full_path = $path;

    # remember which dirs were created and should be cleaned up
    while (1) {
        $CLEAN{dirs}{$path} = 1;
        $path = dirname $path;
        last if -e $path;
    }

    return File::Path::mkpath($full_path, 0, 0755);
}

sub t_rmtree {
    die "must pass a dirname" unless defined $_[0];
    File::Path::rmtree((@_ > 1 ? \@_ : $_[0]), 0, 1);
}

#chown a file or directory to the test User/Group
#noop if chown is unsupported

sub t_chown {
    my $file = shift;
    my $config = Apache::Test::config();
    my($uid, $gid);

    eval {
        #XXX cache this lookup
        ($uid, $gid) = (getpwnam($config->{vars}->{user}))[2,3];
    };

    if ($@) {
        if ($@ =~ /^The getpwnam function is unimplemented/) {
            #ok if unsupported, e.g. win32
            return 1;
        }
        else {
            die $@;
        }
    }

    CORE::chown($uid, $gid, $file) || die "chown $file: $!";
}

# $string = struct_as_string($indent_level, $var);
#
# return any nested datastructure via Data::Dumper or ala Data::Dumper
# as a string. undef() is a valid arg.
#
# $indent_level should be 0 (used for nice indentation during
# recursive datastructure traversal)
sub struct_as_string{
    return "???"   unless @_ == 2;
    my $level = shift;

    return "undef" unless defined $_[0];
    my $pad  = ' ' x (($level + 1) * INDENT);
    my $spad = ' ' x ($level       * INDENT);

    if (HAS_DUMPER) {
        local $Data::Dumper::Terse = 1;
        $Data::Dumper::Terse = $Data::Dumper::Terse; # warn
        my $data = Data::Dumper::Dumper(@_);
        $data =~ s/\n$//; # \n is handled by the caller
        return $data;
    }
    else {
        if (ref($_[0]) eq 'ARRAY') {
            my @data = ();
            for my $i (0..$#{ $_[0] }) {
                push @data,
                    struct_as_string($level+1, $_[0]->[$i]);
            }
            return join "\n", "[", map({"$pad$_,"} @data), "$spad\]";
        } elsif ( ref($_[0])eq 'HASH') {
            my @data = ();
            for my $key (keys %{ $_[0] }) {
                push @data,
                    "$key => " .
                    struct_as_string($level+1, $_[0]->{$key});
            }
            return join "\n", "{", map({"$pad$_,"} @data), "$spad\}";
        } else {
            return $_[0];
        }
    }
}

my $banner_format =
    "\n*** The following %s expected and harmless ***\n";

sub is_expected_banner {
    my $type  = shift;
    my $count = @_ ? shift : 1;
    sprintf $banner_format, $count == 1
        ? "$type entry is"
        : "$count $type entries are";
}

sub t_server_log_is_expected {
    print STDERR is_expected_banner(@_);
}

sub t_client_log_is_expected {
    my $vars = Apache::Test::config()->{vars};
    my $log_file = catfile $vars->{serverroot}, "logs", "error_log";

    my $fh = Symbol::gensym();
    open $fh, ">>$log_file" or die "Can't open $log_file: $!";
    my $oldfh = select($fh); $| = 1; select($oldfh);
    print $fh is_expected_banner(@_);
    close $fh;
}

sub t_server_log_error_is_expected { t_server_log_is_expected("error", @_);}
sub t_server_log_warn_is_expected  { t_server_log_is_expected("warn", @_); }
sub t_client_log_error_is_expected { t_client_log_is_expected("error", @_);}
sub t_client_log_warn_is_expected  { t_client_log_is_expected("warn", @_); }

END {
    # remove files that were created via this package
    for (grep {-e $_ && -f _ } keys %{ $CLEAN{files} } ) {
        t_debug("removing file: $_");
        unlink $_;
    }

    # remove dirs that were created via this package
    for (grep {-e $_ && -d _ } keys %{ $CLEAN{dirs} } ) {
        t_debug("removing dir tree: $_");
        t_rmtree($_);
    }
}

# essentially File::Spec->catfile, but on Win32
# returns the long path name, if the file is absolute
sub t_catfile {
    my $f = catfile(@_);
    return $f unless file_name_is_absolute($f);
    return Apache::TestConfig::WIN32 ?
        Win32::GetLongPathName($f) : $f;
}

# Apache uses a Unix-style specification for files, with
# forward slashes for directory separators. This is
# essentially File::Spec::Unix->catfile, but on Win32
# returns the long path name, if the file is absolute
sub t_catfile_apache {
    my $f = File::Spec::Unix->catfile(@_);
    return $f unless file_name_is_absolute($f);
    return Apache::TestConfig::WIN32 ?
        Win32::GetLongPathName($f) : $f;
}

1;
__END__


=head1 NAME

Apache::TestUtil - Utility functions for writing tests

=head1 SYNOPSIS

  use Apache::Test;
  use Apache::TestUtil;

  ok t_cmp("foo", "foo", "sanity check");
  t_write_file("filename", @content);
  my $fh = t_open_file($filename);
  t_mkdir("/foo/bar");
  t_rmtree("/foo/bar");
  t_is_equal($a, $b);

=head1 DESCRIPTION

C<Apache::TestUtil> automatically exports a number of functions useful
in writing tests.

All the files and directories created using the functions from this
package will be automatically destroyed at the end of the program
execution (via END block). You should not use these functions other
than from within tests which should cleanup all the created
directories and files at the end of the test.

=head1 FUNCTIONS

=over

=item t_cmp()

  t_cmp($received, $expected, $comment);

t_cmp() prints the values of I<$comment>, I<$expected> and
I<$received>. e.g.:

  t_cmp(1, 1, "1 == 1?");

prints:

  # testing : 1 == 1?
  # expected: 1
  # received: 1

then it returns the result of comparison of the I<$expected> and the
I<$received> variables. Usually, the return value of this function is
fed directly to the ok() function, like this:

  ok t_cmp(1, 1, "1 == 1?");

the third argument (I<$comment>) is optional, mostly useful for
telling what the comparison is trying to do.

It is valid to use C<undef> as an expected value. Therefore:

  my $foo;
  t_cmp(undef, $foo, "undef == undef?");

will return a I<true> value.

You can compare any two data-structures with t_cmp(). Just make sure
that if you pass non-scalars, you have to pass their references. The
datastructures can be deeply nested. For example you can compare:

  t_cmp({1 => [2..3,{5..8}], 4 => [5..6]},
        {1 => [2..3,{5..8}], 4 => [5..6]},
        "hash of array of hashes");

You can also compare the second argument against the first as a
regex. Use the C<qr//> function in the second argument. For example:

  t_cmp("abcd", qr/^abc/, "regex compare");

will do:

  "abcd" =~ /^abc/;

This function is exported by default.

=item t_filepath_cmp()

This function is used to compare two filepaths via t_cmp().
For non-Win32, it simply uses t_cmp() for the comparison,
but for Win32, Win32::GetLongPathName() is invoked to convert
the first two arguments to their DOS long pathname. This is useful
when there is a possibility the two paths being compared
are not both represented by their long or short pathname.

This function is exported by default.

=item t_debug()

  t_debug("testing feature foo");
  t_debug("test", [1..3], 5, {a=>[1..5]});

t_debug() prints out any datastructure while prepending C<#> at the
beginning of each line, to make the debug printouts comply with
C<Test::Harness>'s requirements. This function should be always used
for debug prints, since if in the future the debug printing will
change (e.g. redirected into a file) your tests won't need to be
changed.

the special global variable $Apache::TestUtil::DEBUG_OUTPUT can
be used to redirect the output from t_debug() and related calls
such as t_write_file().  for example, from a server-side test
you would probably need to redirect it to STDERR:

  sub handler {
    plan $r, tests => 1;

    local $Apache::TestUtil::DEBUG_OUTPUT = \*STDERR;

    t_write_file('/tmp/foo', 'bar');
    ...
  }

left to its own devices, t_debug() will collide with the standard
HTTP protocol during server-side tests, resulting in a situation
both confusing difficult to debug.  but STDOUT is left as the
default, since you probably don't want debug output under normal
circumstances unless running under verbose mode.

This function is exported by default.

=item t_write_test_lib()

  t_write_test_lib($filename, @lines)

t_write_test_lib() creates a new file at I<$filename> or overwrites
the existing file with the content passed in I<@lines>.  The file
is created in a temporary directory which is added to @INC at
test configuration time.  It is intended to be used for creating
temporary packages for testing which can be modified at run time,
see the Apache::Reload unit tests for an example.

=item t_write_file()

  t_write_file($filename, @lines);

t_write_file() creates a new file at I<$filename> or overwrites the
existing file with the content passed in I<@lines>. If only the
I<$filename> is passed, an empty file will be created.

If parent directories of C<$filename> don't exist they will be
automagically created.

The generated file will be automatically deleted at the end of the
program's execution.

This function is exported by default.

=item t_append_file()

  t_append_file($filename, @lines);

t_append_file() is similar to t_write_file(), but it doesn't clobber
existing files and appends C<@lines> to the end of the file. If the
file doesn't exist it will create it.

If parent directories of C<$filename> don't exist they will be
automagically created.

The generated file will be registered to be automatically deleted at
the end of the program's execution, only if the file was created by
t_append_file().

This function is exported by default.

=item t_write_shell_script()

  Apache::TestUtil::t_write_shell_script($filename, @lines);

Similar to t_write_file() but creates a portable shell/batch
script. The created filename is constructed from C<$filename> and an
appropriate extension automatically selected according to the platform
the code is running under.

It returns the extension of the created file.

=item t_write_perl_script()

  Apache::TestUtil::t_write_perl_script($filename, @lines);

Similar to t_write_file() but creates a executable Perl script with
correctly set shebang line.

=item t_open_file()

  my $fh = t_open_file($filename);

t_open_file() opens a file I<$filename> for writing and returns the
file handle to the opened file.

If parent directories of C<$filename> don't exist they will be
automagically created.

The generated file will be automatically deleted at the end of the
program's execution.

This function is exported by default.

=item t_mkdir()

  t_mkdir($dirname);

t_mkdir() creates a directory I<$dirname>. The operation will fail if
the parent directory doesn't exist.

If parent directories of C<$dirname> don't exist they will be
automagically created.

The generated directory will be automatically deleted at the end of
the program's execution.

This function is exported by default.

=item t_rmtree()

  t_rmtree(@dirs);

t_rmtree() deletes the whole directories trees passed in I<@dirs>.

This function is exported by default.

=item t_chown()

  Apache::TestUtil::t_chown($file);

Change ownership of $file to the test's I<User>/I<Group>.  This
function is noop on platforms where chown(2) is unsupported
(e.g. Win32).

=item t_is_equal()

  t_is_equal($a, $b);

t_is_equal() compares any two datastructures and returns 1 if they are
exactly the same, otherwise 0. The datastructures can be nested
hashes, arrays, scalars, undefs or a combination of any of these.  See
t_cmp() for an example.

If C<$b> is a regex reference, the regex comparison C<$a =~ $b> is
performed. For example:

  t_is_equal($server_version, qr{^Apache});

If comparing non-scalars make sure to pass the references to the
datastructures.

This function is exported by default.

=item t_server_log_error_is_expected()

If the handler's execution results in an error or a warning logged to
the I<error_log> file which is expected, it's a good idea to have a
disclaimer printed before the error itself, so one can tell real
problems with tests from expected errors. For example when testing how
the package behaves under error conditions the I<error_log> file might
be loaded with errors, most of which are expected.

For example if a handler is about to generate a run-time error, this
function can be used as:

  use Apache::TestUtil;
  ...
  sub handler {
      my $r = shift;
      ...
      t_server_log_error_is_expected();
      die "failed because ...";
  }

After running this handler the I<error_log> file will include:

  *** The following error entry is expected and harmless ***
  [Tue Apr 01 14:00:21 2003] [error] failed because ...

When more than one entry is expected, an optional numerical argument,
indicating how many entries to expect, can be passed. For example:

  t_server_log_error_is_expected(2);

will generate:

  *** The following 2 error entries are expected and harmless ***

If the error is generated at compile time, the logging must be done in
the BEGIN block at the very beginning of the file:

  BEGIN {
      use Apache::TestUtil;
      t_server_log_error_is_expected();
  }
  use DOES_NOT_exist;

After attempting to run this handler the I<error_log> file will
include:

  *** The following error entry is expected and harmless ***
  [Tue Apr 01 14:04:49 2003] [error] Can't locate "DOES_NOT_exist.pm"
  in @INC (@INC contains: ...

Also see C<t_server_log_warn_is_expected()> which is similar but used
for warnings.

This function is exported by default.

=item t_server_log_warn_is_expected()

C<t_server_log_warn_is_expected()> generates a disclaimer for expected
warnings.

See the explanation for C<t_server_log_error_is_expected()> for more
details.

This function is exported by default.

=item t_client_log_error_is_expected()

C<t_client_log_error_is_expected()> generates a disclaimer for
expected errors. But in contrast to
C<t_server_log_error_is_expected()> called by the client side of the
script.

See the explanation for C<t_server_log_error_is_expected()> for more
details.

For example the following client script fails to find the handler:

  use Apache::Test;
  use Apache::TestUtil;
  use Apache::TestRequest qw(GET);

  plan tests => 1;

  t_client_log_error_is_expected();
  my $url = "/error_document/cannot_be_found";
  my $res = GET($url);
  ok t_cmp(404, $res->code, "test 404");

After running this test the I<error_log> file will include an entry
similar to the following snippet:

  *** The following error entry is expected and harmless ***
  [Tue Apr 01 14:02:55 2003] [error] [client 127.0.0.1]
  File does not exist: /tmp/test/t/htdocs/error

When more than one entry is expected, an optional numerical argument,
indicating how many entries to expect, can be passed. For example:

  t_client_log_error_is_expected(2);

will generate:

  *** The following 2 error entries are expected and harmless ***

This function is exported by default.

=item t_client_log_warn_is_expected()

C<t_client_log_warn_is_expected()> generates a disclaimer for expected
warnings on the client side.

See the explanation for C<t_client_log_error_is_expected()> for more
details.

This function is exported by default.

=item t_catfile('a', 'b', 'c')

This function is essentially C<File::Spec-E<gt>catfile>, but
on Win32 will use C<Win32::GetLongpathName()> to convert the
result to a long path name (if the result is an absolute file).
The function is not exported by default.

=item t_catfile_apache('a', 'b', 'c')

This function is essentially C<File::Spec::Unix-E<gt>catfile>, but
on Win32 will use C<Win32::GetLongpathName()> to convert the
result to a long path name (if the result is an absolute file).
It is useful when comparing something to that returned by Apache,
which uses a Unix-style specification with forward slashes for
directory separators. The function is not exported by default.

=item t_start_error_log_watch(), t_finish_error_log_watch()

This pair of functions provides an easy interface for checking
the presence or absense of any particular message or messages
in the httpd error_log that were generated by the httpd daemon
as part of a test suite.  It is likely, that you should proceed
this with a call to one of the t_*_is_expected() functions.

  t_start_error_log_watch();
  do_it;
  ok grep {...} t_finish_error_log_watch()

=back

=head1 AUTHOR

Stas Bekman <stas@stason.org>

=head1 SEE ALSO

perl(1)

=cut

