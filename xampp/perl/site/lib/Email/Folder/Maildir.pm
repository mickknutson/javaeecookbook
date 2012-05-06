package Email::Folder::Maildir;
use strict;
use Carp;
use IO::File;
use Email::Folder::Reader;
use base 'Email::Folder::Reader';

=head1 NAME

Email::Folder::Maildir - reads raw RFC822 mails from a maildir

=head1 SYNOPSIS

This isa Email::Folder::Reader - read about its API there.

=head1 DESCRIPTION

Does exactly what it says on the tin - fetches raw RFC822 mails from a
maildir.

The maildir format is described at http://www.qmail.org/man/man5/maildir.html

=cut

sub _what_is_there {
    my $self = shift;
    my $dir = $self->{_file};

    croak "$dir does not exist"    unless (-e $dir);
    croak "$dir is not a maildir"  unless (-d $dir);
    croak "$dir is not a maildir"  unless (-e "$dir/cur" && -d "$dir/cur");
    croak "$dir is not a maildir"  unless (-e "$dir/new" && -d "$dir/new");

    my @messages;
    # ignore the tmp directory although the spec
    # says to delete anything in tmp/ that is older than 36 hours
    for my $sub (qw(new cur)) {
        opendir(DIR,"$dir/$sub") or croak "Could not open '$dir/$sub'";
        foreach my $file (readdir DIR) {
            next if $file =~ /^\./; # as suggested by DJB
            push @messages, "$dir/$sub/$file";
        }
    }

    $self->{_messages} = \@messages;
}

sub next_message {
    my $self = shift;
    my $what = $self->{_messages} || $self->_what_is_there;

    my $file = shift @$what or return;
    local *FILE;
    open FILE, $file or croak "couldn't open '$file' for reading";
    join '', <FILE>;
}

1;

__END__

=head1 AUTHOR

Simon Wistow <simon@thegestalt.org>

=head1 COPYING

Copyright 2003, Simon Wistow

Distributed under the same terms as Perl itself.

This software is under no warranty and will probably ruin your life, kill your friends, burn your house and brin$

=head1 SEE ALSO

L<Email::LocalDelivery>, L<Email::Folder>

=cut
