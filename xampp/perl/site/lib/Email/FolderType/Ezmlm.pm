package Email::FolderType::Ezmlm;
use strict;

=head1 NAME

Email::FolderType::Ezmlm - class to help Email::FolderType recognise ezmlm archives

=cut

sub match {
  my $folder = shift;
  return ($folder =~ m{//$}  || -d "$folder/archive");
}


1;
