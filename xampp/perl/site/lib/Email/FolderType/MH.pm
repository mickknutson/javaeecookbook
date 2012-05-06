package Email::FolderType::MH;
use strict;

=head1 NAME

Email::FolderType::MH - class to help Email::FolderType recognise MH mail directories

=cut

sub match { @_ || return 0; $_[0] =~ m{/\.$}  }


1;
