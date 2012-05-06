package Email::Date;
use strict;

use vars qw[$VERSION @EXPORT @EXPORT_OK];
$VERSION = '1.103';
@EXPORT    = qw[find_date format_date];
@EXPORT_OK = qw[format_gmdate];

use base qw[Exporter];
use Date::Parse ();
use Email::Date::Format;
use Time::Piece ();

=head1 NAME

Email::Date - Find and Format Date Headers

=head1 SYNOPSIS

  use Email::Date;
  
  my $email = join '', <>;
  my $date  = find_date($email);
  print $date->ymd;
  
  my $header = format_date($date->epoch);
  
  Email::Simple->create(
      header => [
          Date => $header,
      ],
      body => '...',
  );

=head1 DESCRIPTION

RFC 2822 defines the C<Date:> header. It declares the header a required
part of an email message. The syntax for date headers is clearly laid
out. Stil, even a perfectly planned world has storms. The truth is, many
programs get it wrong. Very wrong. Or, they don't include a C<Date:> header
at all. This often forces you to look elsewhere for the date, and hoping
to find something.

For this reason, the tedious process of looking for a valid date has been
encapsulated in this software. Further, the process of creating RFC
compliant date strings is also found in this software.

=head2 FUNCTIONS

=over 4

=item find_date

  my $time_piece = find_date $email;

C<find_date> accepts an email message in any format
L<Email::Abstract|Email::Abstract> can understand. It looks through the email
message and finds a date, converting it to a L<Time::Piece|Time::Piece> object.

If it can't find a date, it returns false.

C<find_date> is exported by default.

=cut

sub find_date {
    require Email::Abstract;
    my $email = Email::Abstract->new($_[0]);

    my $date = $email->get_header('Date')
            || _find_date_received($email->get_header('Received'))
            || $email->get_header('Resent-Date');

    return unless $date and length $date;

    Time::Piece->new(Date::Parse::str2time $date);
}

sub _find_date_received {
    return unless defined $_[0] and length $_[0];
    my $date = pop;
    $date =~ s/.+;//;
    $date;
}

=item format_date

  my $date = format_date; # now
  my $date = format_date( time - 60*60 ); # one hour ago

C<format_date> accepts an epoch value, such as the one returned by C<time>.
It returns a string representing the date and time of the input, as
specified in RFC 2822. If no input value is provided, the current value
of C<time> is used.

C<format_date> is exported by default.

=item format_gmdate

  my $date = format_gmdate;

C<format_gmdate> is identical to C<format_date>, but it will return a string
indicating the time in Greenwich Mean Time, rather than local time.

C<format_gmdate> is exported on demand, but not by default.

=cut

BEGIN {
  *format_date   = \&Email::Date::Format::email_date;
  *format_gmdate = \&Email::Date::Format::email_gmdate;
};

1;

__END__

=back

=head1 PERL EMAIL PROJECT

This module is maintained by the Perl Email Project

L<http://emailproject.perl.org/wiki/Email::Date>

=head1 SEE ALSO

L<Email::Abstract>,
L<Time::Piece>,
L<Date::Parse>,
L<perl>.

=head1 AUTHOR

Casey West, <F<casey@geeknest.com>>.

Ricardo SIGNES, <F<rjbs@cpan.org>>.

=head1 COPYRIGHT

  Copyright (c) 2004 Casey West.  All rights reserved.
  This module is free software; you can redistribute it and/or modify it
  under the same terms as Perl itself.

=cut
