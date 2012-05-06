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
package ModPerl::PerlRun;

use strict;
use warnings FATAL => 'all';

# we try to develop so we reload ourselves without die'ing on the warning
no warnings qw(redefine); # XXX, this should go away in production!

our $VERSION = '1.99';

use base qw(ModPerl::RegistryCooker);

sub handler : method {
    my $class = (@_ >= 2) ? shift : __PACKAGE__;
    my $r = shift;
    return $class->new($r)->default_handler();
}

my $parent = 'ModPerl::RegistryCooker';
# the following code:
# - specifies package's behavior different from default of $parent class
# - speeds things up by shortcutting @ISA search, so even if the
#   default is used we still use the alias
my %aliases = (
    new             => 'new',
    init            => 'init',
    default_handler => 'default_handler',
    run             => 'run',
    can_compile     => 'can_compile',
    make_namespace  => 'make_namespace',
    namespace_root  => 'namespace_root',
    namespace_from  => 'namespace_from_filename',
    is_cached       => 'FALSE',
    should_compile  => 'TRUE',
    flush_namespace => 'flush_namespace_normal',
    cache_table     => 'cache_table_common',
    cache_it        => 'NOP',
    read_script     => 'read_script',
    shebang_to_perl => 'shebang_to_perl',
    get_script_name => 'get_script_name',
    chdir_file      => 'NOP',
    get_mark_line   => 'get_mark_line',
    compile         => 'compile',
    error_check     => 'error_check',
    should_reset_inc_hash => 'TRUE',
    strip_end_data_segment             => 'strip_end_data_segment',
    convert_script_to_compiled_handler => 'convert_script_to_compiled_handler',
);

# in this module, all the methods are inherited from the same parent
# class, so we fixup aliases instead of using the source package in
# first place.
$aliases{$_} = $parent . "::" . $aliases{$_} for keys %aliases;

__PACKAGE__->install_aliases(\%aliases);





1;
__END__


=head1 NAME

ModPerl::PerlRun - Run unaltered CGI scripts under mod_perl

=head1 Synopsis

  # httpd.conf
  PerlModule ModPerl::PerlRun
  Alias /perl-run/ /home/httpd/perl/
  <Location /perl-run>
      SetHandler perl-script
      PerlResponseHandler ModPerl::PerlRun
      PerlOptions +ParseHeaders
      Options +ExecCGI
  </Location>


=head1 Description

META: document that for now we don't chdir() into the script's dir,
because it affects the whole process under
threads. C<L<ModPerl::PerlRunPrefork|docs::2.0::api::ModPerl::PerlRunPrefork>>
should be used by those who run only under prefork MPM.


=head1 Special Blocks


=head2 C<BEGIN> Blocks

When running under the C<ModPerl::PerlRun> handler C<BEGIN> blocks
behave as follows:

=over

=item *

C<BEGIN> blocks defined in scripts running under the
C<ModPerl::PerlRun> handler are executed on each and every request.

=item *

C<BEGIN> blocks defined in modules loaded from scripts running under
C<ModPerl::PerlRun> (and which weren't already loaded prior to the
request) are executed on each and every request only if those modules
declare no package. If a package is declared C<BEGIN> blocks will be
run only the first time each module is loaded, since those modules
don't get reloaded on subsequent requests.

=back

See also L<C<BEGIN> blocks in mod_perl
handlers|docs::2.0::user::coding::coding/C_BEGIN__Blocks>.


=head2 C<CHECK> and C<INIT> Blocks

Same as normal L<mod_perl
handlers|docs::2.0::user::coding::coding/C_CHECK__and_C_INIT__Blocks>.



=head2 C<END> Blocks

Same as
C<L<ModPerl::Registry|docs::2.0::api::ModPerl::Registry/C_BEGIN__Blocks>>.


=head1 Authors

Doug MacEachern

Stas Bekman



=head1 See Also

C<L<ModPerl::RegistryCooker|docs::2.0::api::ModPerl::RegistryCooker>>
and C<L<ModPerl::Registry|docs::2.0::api::ModPerl::Registry>>.

=cut
