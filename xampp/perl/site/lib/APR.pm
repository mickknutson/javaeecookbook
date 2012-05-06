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
package APR;

use DynaLoader ();
our $VERSION = '0.009000';
our @ISA = qw(DynaLoader);

#dlopen("APR.so", RTDL_GLOBAL); so we only need to link libapr.a once
# XXX: see xs/ModPerl/Const/Const.pm for issues of using 0x01
use Config ();
use constant DL_GLOBAL =>
  ( $Config::Config{dlsrc} eq 'dl_dlopen.xs' && $^O ne 'openbsd' ) ? 0x01 : 0x0;
sub dl_load_flags { DL_GLOBAL }

unless (defined &APR::XSLoader::BOOTSTRAP) {
    __PACKAGE__->bootstrap($VERSION);
    *APR::XSLoader::BOOTSTRAP = sub () { 1 };
}

1;
__END__

=head1 NAME

APR - Perl Interface for Apache Portable Runtime (libapr and
libaprutil Libraries)





=head1 Synopsis

  use APR ();






=head1 Description

On load this modules prepares the APR enviroment (initializes memory
pools, data structures, etc.)

You don't need to use this module explicitly, since it's already
loaded internally by all C<APR::*> modules.






=head1 Using APR modules outside mod_perl 2.0

You'd use the C<APR::*> modules outside mod_perl 2.0, just like you'd
use it with mod_perl 2.0. For example to get a random unique string
you could call:

  % perl -MAPR::UUID -le 'print APR::UUID->new->format'






=head1 See Also

L<mod_perl 2.0 documentation|docs::2.0::index>.




=head1 Copyright

mod_perl 2.0 and its core modules are copyrighted under
The Apache Software License, Version 2.0.




=head1 Authors

L<The mod_perl development team and numerous
contributors|about::contributors::people>.


=cut
