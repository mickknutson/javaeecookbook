package Email::LocalDelivery;
use strict;

use File::Path::Expand qw(expand_filename);
use Email::FolderType qw(folder_type);
use Carp;

use vars qw($VERSION);
$VERSION = '0.217';

=head1 NAME

Email::LocalDelivery - Deliver a piece of email - simply

=head1 SYNOPSIS

  use Email::LocalDelivery;
  my @delivered_to = Email::LocalDelivery->deliver($mail, @boxes);

=head1 DESCRIPTION

This module delivers an email to a list of mailboxes.

=head1 METHODS

=head2 deliver

This takes an email, as a plain string, and a list of mailboxes to
deliver that mail to. It returns the list of boxes actually written to.
If no boxes are given, it assumes the standard Unix mailbox. (Either
C<$ENV{MAIL}>, F</var/spool/mail/you>, F</var/mail/you>, or
F<~you/Maildir/>)

=cut

sub deliver {
    my ($class, $mail, @boxes) = @_;

    croak "Mail argument to deliver should just be a plain string"
        if ref $mail;

    if (!@boxes) {
        my $default_unixbox = ( grep { -d $_ } qw(/var/spool/mail/ /var/mail/) )[0] . getpwuid($>);
        my $default_maildir = ((getpwuid($>))[7])."/Maildir/";

        @boxes = $ENV{MAIL}
            || (-e $default_unixbox && $default_unixbox)
            || (-d $default_maildir."cur" && $default_maildir);

    }
    my %to_deliver;

    for my $box (@boxes) {
      $box = expand_filename($box);
      push @{$to_deliver{folder_type($box)}}, $box;
    }

    my @rv;
    for my $method (keys %to_deliver) {
        eval "require Email::LocalDelivery::$method";
        croak "Couldn't load a module to handle $method mailboxes" if $@;
        push @rv,
        "Email::LocalDelivery::$method"->deliver($mail,
                                                @{$to_deliver{$method}});
    }
    return @rv;
}

1;

__END__

=head1 PERL EMAIL PROJECT

This module is maintained by the Perl Email Project

L<http://emailproject.perl.org/wiki/Email::LocalDelivery>

=head1 CONTACT INFO

To report bugs, please use the request tracker at L<http://rt.cpan.org>.  For
all other information, please contact the PEP mailing list (see the wiki,
above) or Ricardo SIGNES.

=head1 COPYRIGHT AND LICENSE

Copyright 2003 by Simon Cozens

Copyright 2004 by Casey West

This library is free software; you can redistribute it and/or modify
it under the same terms as Perl itself.

=cut
