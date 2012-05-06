package Email::FolderType::Net;
# $Id: Net.pm,v 1.4 2005/05/04 02:13:08 cwest Exp $
use strict;

use vars qw[$VERSION];
$VERSION = '1.041';

use URI;

=head1 NAME

Email::FolderType::Net - Recognize folder types for network based message protocols.

=head1 SYNOPSIS

  use Email::FolderType qw[folder_type];

  my $type = folder_type($folder) || 'unknown';
  print "$folder is type $type.\n";

=head2 DESCRIPTION

Registers several mail folder types that are known as network based
messaging protocols. Folder names for these protocols should be
specified using a L<URI|URI> syntax.

=head2 IMAP

  print 'IMAP' if folder_type('imap://foo.com/folder') eq 'IMAP';

Returns this folder type if the scheme is C<imap>.

=head2 IMAPS

  print 'IMAPS' if folder_type('imaps://example.com') eq 'IMAPS';

Returns this folder type if the scheme is C<imaps>.

=head2 POP3

  print 'POP3' if folder_type('pop3://example.com:110') eq 'POP3';

Returns this folder type if the schem is C<pop> or C<pop3>.

=head2 POP3S

  print 'POP3S' if folder_type('pops://foo.com') eq 'POP3S';

returns this folder type if the scheme is C<pops> or C<pop3s>.

=cut

sub _from_scheme {
    my $scheme = shift;
    my $uri    = URI->new(shift);
    return 1 if lc($uri->scheme) eq $scheme;
    return;
}

sub _create_match {
    my (@schemes) = @_;
    return sub {
        Email::FolderType::Net::_from_scheme($_,@_)
          and return(1)
            for @schemes;
        return;
    };
}

package Email::FolderType::IMAP;
*match = Email::FolderType::Net::_create_match(qw[imap]);
package Email::FolderType::IMAPS;
*match = Email::FolderType::Net::_create_match(qw[imaps]);
package Email::FolderType::POP3;
*match = Email::FolderType::Net::_create_match(qw[pop pop3]);
package Email::FolderType::POP3S;
*match = Email::FolderType::Net::_create_match(qw[pops pop3s]);

__END__

=head1 SEE ALSO

L<Email::FolderType>,
L<Email::FolderType::Local>,
L<URI>.

=head1 PERL EMAIL PROJECT

This module is maintained by the Perl Email Project

L<http://emailproject.perl.org/wiki/Email::FolderType::Net>

=head1 AUTHOR

Casey West <F<casey@geeknest.com>>.

=head1 COPYRIGHT

  Copyright (c) 2004 Casey West.  All rights reserved.
  This module is free software; you can redistribute it and/or modify it
  under the same terms as Perl itself.

=cut
