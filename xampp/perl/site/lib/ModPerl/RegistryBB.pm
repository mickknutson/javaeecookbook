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
package ModPerl::RegistryBB;

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

# currently all the methods are inherited through the normal ISA
# search may

1;
__END__


=head1 NAME

ModPerl::RegistryBB - Run unaltered CGI scripts persistently under mod_perl

=head1 Synopsis

  # httpd.conf
  PerlModule ModPerl::RegistryBB
  Alias /perl/ /home/httpd/perl/
  <Location /perl>
      SetHandler perl-script
      PerlResponseHandler ModPerl::RegistryBB
      #PerlOptions +ParseHeaders
      #PerlOptions -GlobalRequest
      Options +ExecCGI
  </Location>

=head1 Description

C<ModPerl::RegistryBB> is similar to C<L<ModPerl::Registry>>, but does
the bare minimum (mnemonic: BB = Bare Bones) to compile a script file
once and run it many times, in order to get the maximum
performance. Whereas C<L<ModPerl::Registry>> does various checks,
which add a slight overhead to response times.

=head1 Authors

Doug MacEachern

Stas Bekman

=head1 See Also

C<L<ModPerl::RegistryCooker|docs::2.0::api::ModPerl::RegistryCooker>>,
C<L<ModPerl::Registry|docs::2.0::api::ModPerl::Registry>> and
C<L<ModPerl::PerlRun|docs::2.0::api::ModPerl::PerlRun>>.

=cut
