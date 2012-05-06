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
package ModPerl::Const;

use DynaLoader ();

our $VERSION = do { require mod_perl2; $mod_perl2::VERSION };
our @ISA = qw(DynaLoader);

#dlopen("Const.so", RTDL_GLOBAL);
#XXX: dl_dlopen.xs check isn't portable; works for hpux
# - on aix this is dl_aix.xs, and depending on release, RTDL_GLOBAL is
#   available or not, e.g. 4.3 doesn't have it in the headers, while
#   5.1 does have it
# - from looking at ext/DynaLoader/dl_*.xs when 0x01 is used when it's
#   not supported perl issues a warning and passes the right flag to dlopen
# - currently (patchlevel 18958) dl_aix.xs always issues a warning
#   even when RTDL_GLOBAL is available, patch submitted to p5p
use Config ();
use constant DL_GLOBAL =>
  ( $Config::Config{dlsrc} eq 'dl_dlopen.xs' && $^O ne 'openbsd' ) ? 0x01 : 0x0;
sub dl_load_flags { DL_GLOBAL }

#only bootstrap for use outside of mod_perl
unless (defined &ModPerl::Const::compile) {
    __PACKAGE__->bootstrap($VERSION);
}

sub import {
    my $class = shift;
    my $arg;

    if ($_[0] and $_[0] =~ /^-compile/) {
        $arg = shift; #just compile the constants subs, export nothing
    }

    $arg ||= scalar caller; #compile and export into caller's namespace

    $class->compile($arg, @_ ? @_ : ':common');
}

1;

=head1 NAME

ModPerl::Const -- ModPerl Constants





=head1 Synopsis

  # make the constants available but don't import them
  use ModPerl::Const -compile => qw(constant names ...);
  
  # w/o the => syntax sugar
  use ModPerl::Const ("-compile", qw(constant names ...));
  
  # compile and import the constants
  use ModPerl::Const qw(constant names ...);






=head1 Description

This package contains constants specific to mod_perl features.

Refer to C<L<the Apache2::Const description
section|docs::2.0::api::Apache2::Const/Description>> for more
information.







=head1 Constants



=head2 Other Constants


=head3 C<ModPerl::EXIT>

=over

=item since: 2.0.00

=back

See C<L<ModPerl::Util::exit|docs::2.0::api::ModPerl::Util/C_exit_>>.





=head1 See Also

L<mod_perl 2.0 documentation|docs::2.0::index>.




=head1 Copyright

mod_perl 2.0 and its core modules are copyrighted under
The Apache Software License, Version 2.0.




=head1 Authors

L<The mod_perl development team and numerous
contributors|about::contributors::people>.

=cut
