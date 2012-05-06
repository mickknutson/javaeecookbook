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
package APR::PerlIO;

require 5.006001;

our $VERSION = '0.009000';

# The PerlIO layer is available only since 5.8.0 (5.7.2@13534)
use Config;
use constant PERLIO_LAYERS_ARE_ENABLED => $Config{useperlio} && $] >= 5.00703;

use APR ();
use APR::XSLoader ();
APR::XSLoader::load __PACKAGE__;


1;

=head1 NAME

APR::PerlIO -- Perl IO layer for APR

=head1 Synopsis

  # under mod_perl
  use APR::PerlIO ();
  
  sub handler {
      my $r = shift;
  
      die "This Perl build doesn't support PerlIO layers"
          unless APR::PerlIO::PERLIO_LAYERS_ARE_ENABLED;
  
      open my $fh, ">:APR", $filename, $r->pool or die $!;
      # work with $fh as normal $fh
      close $fh;
  
      return Apache2::Const::OK;
  }

  # outside mod_perl
  % perl -MAPR -MAPR::PerlIO -MAPR::Pool -le \
  'open my $fh, ">:APR", "/tmp/apr", APR::Pool->new or die "$!"; \
   print $fh "whoah!"; \
   close $fh;'


=head1 Description

C<APR::PerlIO> implements a Perl IO layer using APR's file
manipulation API internally.

Why do you want to use this? Normally you shouldn't, probably it won't
be faster than Perl's default layer. It's only useful when you need to
manipulate a filehandle opened at the APR side, while using Perl.

Normally you won't call open() with APR layer attribute, but some
mod_perl functions will return a filehandle which is internally hooked
to APR. But you can use APR Perl IO directly if you want.


=head1 Prerequisites

Not every Perl will have full C<APR::PerlIO> functionality available.

Before using the Perl IO APR layer one has to check whether it's
supported by the used APR/Perl build. Perl 5.8.x or higher with perlio
enabled is required. You can check whether your Perl fits the bill by
running:

  % perl -V:useperlio
  useperlio='define';

It should say I<define>.

If you need to do the checking in the code, there is a special
constant provided by C<APR::PerlIO>, which can be used as follows:

  use APR::PerlIO ();
  die "This Perl build doesn't support PerlIO layers"
      unless APR::PerlIO::PERLIO_LAYERS_ARE_ENABLED;

Notice that loading C<APR::PerlIO> won't fail when Perl IO layers
aren't available since C<APR::PerlIO> provides functionality for Perl
builds not supporting Perl IO layers.


=head1 Constants



=head2 C<APR::PerlIO::PERLIO_LAYERS_ARE_ENABLED>

See L<Prerequisites|/Prerequisites>.







=head1 API

Most of the API is as in normal perl IO with a few nuances listed in
the following sections.

META: need to rework the exception mechanism here. Current success in
using errno ($!) being set (e.g. on open()) is purely accidental and
not guaranteed across all platforms and functions. So don't rely on
$!. Will use C<L<APR::Error|docs::2.0::api::APR::Error>> for that
purpose.



=head2 C<open>

Open a file via APR Perl IO layer.

  open my $fh, ">:APR", $filename, $r->pool or die $!;

=over 4

=item arg1: C<$fh> ( GLOB filehandle )

The filehandle.

=item arg2: C<$mode> ( string )

The mode to open the file, constructed from two sections separated by
the C<:> character: the first section is the mode to open the file
under (E<gt>, E<lt>, etc) and the second section must be a string
I<APR>. For more information refer to the I<open> entry in the
I<perlfunc> manpage.

=item arg3: C<$filename> ( string )

The path to the filename to open

=item arg4: C<$p> ( C<L<APR::Pool|docs::2.0::api::APR::Pool>> )

The pool object to use to allocate APR::PerlIO layer.

=item ret: ( integer )

success or failure value (boolean).

=item since: 2.0.00

=back






=head2 C<seek>

Sets C<$fh>'s position, just like the C<seek()> Perl call:

  seek($fh, $offset, $whence);

If C<$offset> is zero, C<seek()> works normally.

However if C<$offset> is non-zero and Perl has been compiled with with
large files support (C<-Duselargefiles>), whereas APR wasn't, this
function will croak. This is because largefile size C<Off_t> simply
cannot fit into a non-largefile size C<apr_off_t>.

To solve the problem, rebuild Perl with C<-Uuselargefiles>. Currently
there is no way to force APR to build with large files support.

=over 4

=item since: 2.0.00

=back





=head1 C API

The C API provides functions to convert between Perl IO and APR Perl
IO filehandles.

META: document these



=head1 See Also

L<mod_perl 2.0 documentation|docs::2.0::index>. The I<perliol(1)>,
I<perlapio(1)> and I<perl(1)> manpages.




=head1 Copyright

mod_perl 2.0 and its core modules are copyrighted under
The Apache Software License, Version 2.0.




=head1 Authors

L<The mod_perl development team and numerous
contributors|about::contributors::people>.


=cut

