package ModPerl::PerlRunPrefork;

use strict;
use warnings FATAL => 'all';

our $VERSION = '0.01';

use base qw(ModPerl::PerlRun);

if ($ENV{MOD_PERL}) {
    require Apache2::MPM;
    die "This package can't be used under threaded MPMs"
        if Apache2::MPM->is_threaded;
}

sub handler : method {
    my $class = (@_ >= 2) ? shift : __PACKAGE__;
    my $r = shift;
    return $class->new($r)->default_handler();
}

*chdir_file = \&ModPerl::RegistryCooker::chdir_file_normal;

1;
__END__

=head1 NAME

ModPerl::PerlRunPrefork - Run unaltered CGI scripts under mod_perl

=head1 Synopsis

  # httpd.conf
  PerlModule ModPerl::PerlRunPrefork
  Alias /perl-run/ /home/httpd/perl/
  <Location /perl-run>
      SetHandler perl-script
      PerlResponseHandler ModPerl::PerlRunPrefork
      PerlOptions +ParseHeaders
      Options +ExecCGI
  </Location>


=head1 Description



=head1 Copyright

mod_perl 2.0 and its core modules are copyrighted under
The Apache Software License, Version 2.0.




=head1 Authors

L<The mod_perl development team and numerous
contributors|about::contributors::people>.


=head1 See Also

C<L<ModPerl::RegistryCooker|docs::2.0::api::ModPerl::RegistryCooker>>
and C<L<ModPerl::Registry|docs::2.0::api::ModPerl::Registry>>.

=cut
