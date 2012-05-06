package Email::FolderType::Maildir;
use strict;

=head1 NAME

Email::FolderType::Maildir - class to help Email::FolderType recognise maildirs

=cut

sub match {
  my $folder = shift;
  return ($folder =~ m{[^/]/$} || -d "$folder/cur");
}

1;
