package Email::FolderType::Mbox;
use strict;

=head1 NAME

Email::FolderType::Mbox - class to help Email::FolderType recognise MH mail directories

=cut

# since Mbox is the default always return 1

sub match { 1 }

1;
