use strict;
package Email::Folder;
use Carp;
use Email::Simple;
use Email::FolderType qw/folder_type/;

use vars qw($VERSION);
$VERSION = "0.855";

=head1 NAME

Email::Folder - read all the messages from a folder as Email::Simple objects.

=head1 SYNOPSIS

 use Email::Folder;

 my $folder = Email::Folder->new("some_file");

 print join "\n", map { $_->header("Subject") } $folder->messages;

=head1 METHODS

=head2 new($folder, %options)

Takes the name of a folder, and a hash of options

If a 'reader' option is passed in then that is
used as the class to read in messages with.

=cut

sub new {
    my $class  = shift;
    my $folder = shift || carp "Must provide a folder name\n";
    my %self = @_;

    my $reader;

    if ($self{reader}) {
        $reader = $self{reader};
    } else {
        $reader = "Email::Folder::".folder_type($folder);
    }
    eval "require $reader" or die $@;

    $self{_folder} = $reader->new($folder, @_);

    return bless \%self, $class;
}

=head2 messages

Returns a list containing all of the messages in the folder.  Can only
be called once as it drains the iterator.

=cut

sub messages {
    my $self = shift;

    my @messages = $self->{_folder}->messages;
    my @ret;
    while (my $body = shift @messages) {
        push @ret, $self->bless_message( $body );
    }
    return @ret;
}


=head2 next_message

acts as an iterator.  reads the next message from a folder.  returns
false at the end of the folder

=cut

sub next_message {
    my $self = shift;

    my $body = $self->{_folder}->next_message or return;
    $self->bless_message( $body );
}


=head2 bless_message($message)

Takes a raw RFC822 message and blesses it into a class.

By default this is an Email::Simple object but can easily be overriden
in a subclass.

For example, this simple subclass just returns the raw rfc822 messages,
and exposes the speed of the parser.

 package Email::RawFolder;
 use base 'Email::Folder';
 sub bless_message { $_[1] };
 1;

=cut

sub bless_message {
    my $self    = shift;
    my $message = shift || die "You must pass a message\n";

    return Email::Simple->new($message);
}


=head2 reader

read-only accessor to the underlying Email::Reader subclass instance

=cut

sub reader {
    my $self = shift;
    return $self->{_folder};
}

1;

__END__

=head1 PERL EMAIL PROJECT

This module is maintained by the Perl Email Project

L<http://emailproject.perl.org/wiki/Email::Folder>

=head1 AUTHORS

Simon Wistow <simon@thegestalt.org>

Richard Clamp <richardc@unixbeard.net>

=head1 COPYING

Copyright 2006, Simon Wistow

Distributed under the same terms as Perl itself.

This software is under no warranty and will probably ruin your life,
kill your friends, burn your house and bring about the doobie brothers.

=head1 SEE ALSO

L<Email::LocalDelivery>, L<Email::FolderType>, L<Email::Simple>

=cut
