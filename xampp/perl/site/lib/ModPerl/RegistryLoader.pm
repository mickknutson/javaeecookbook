# Licensed to the Apache Software Foundation (ASF) under one or more
# contributor license agreements.  See the NOTICE file distributed with
# this work for additional information regarding copyright ownership.
# The ASF licenses this file to You under the Apache License, Version 2.0
# (the "License"); you may not use this file except in compliance with
# the License.  You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
package ModPerl::RegistryLoader;

use strict;
use warnings;

use ModPerl::RegistryCooker ();
use Apache2::ServerUtil ();
use Apache2::Log ();
use APR::Pool ();

use Carp;
use File::Spec ();

use Apache2::Const -compile => qw(OK HTTP_OK OPT_EXECCGI);

our @ISA = ();

sub new {
    my $class = shift;
    my $self = bless {@_} => ref($class)||$class;
    $self->{package} ||= 'ModPerl::Registry';
    $self->{pool} = APR::Pool->new();
    $self->load_package($self->{package});
    return $self;
}

sub handler {
    my ($self, $uri, $filename, $virthost) = @_;

    # set the inheritance rules at run time
    @ISA = $self->{package};

    unless (defined $uri) {
        $self->warn("uri is a required argument");
        return;
    }

    if (defined $filename) {
        unless (-e $filename) {
            $self->warn("Cannot find: $filename");
            return;
        }
    }
    else {
        # try to translate URI->filename
        if (exists $self->{trans} and ref($self->{trans}) eq 'CODE') {
            no strict 'refs';
            $filename = $self->{trans}->($uri);
            unless (-e $filename) {
                $self->warn("Cannot find a translated from uri: $filename");
                return;
            }
        }
        else {
            # try to guess
            (my $guess = $uri) =~ s|^/||;

            $self->warn("Trying to guess filename based on uri")
                if $self->{debug};

            $filename = File::Spec->catfile(Apache2::ServerUtil::server_root,
                                            $guess);
            unless (-e $filename) {
                $self->warn("Cannot find guessed file: $filename",
                            "provide \$filename or 'trans' sub");
                return;
            }
        }
    }

    if ($self->{debug}) {
        $self->warn("*** uri=$uri, filename=$filename");
    }

    my $rl = bless {
        uri      => $uri,
        filename => $filename,
        package  => $self->{package},
    } => ref($self) || $self;

    $rl->{virthost} = $virthost if defined $virthost;

    # can't call SUPER::handler here, because it usually calls new()
    # and then the ModPerlRegistryLoader::new() will get called,
    # instead of the super class' new, so we implement the super
    # class' handler here. Hopefully all other subclasses use the same
    # handler.
    __PACKAGE__->SUPER::new($rl)->default_handler();

}

# XXX: s/my_// for qw(my_finfo my_slurp_filename);
# when when finfo() and slurp_filename() are ported to 2.0 and
# RegistryCooker is starting to use them

sub get_server_name { return $_[0]->{virthost} if exists $_[0]->{virthost} }
sub filename { shift->{filename} }
sub status { Apache2::Const::HTTP_OK }
sub my_finfo    { shift->{filename} }
sub uri      { shift->{uri} }
sub path_info {}
sub allow_options { Apache2::Const::OPT_EXECCGI } #will be checked again at run-time
sub log_error { shift; die @_ if $@; warn @_; }
sub run { return Apache2::Const::OK } # don't run the script
sub server { shift }
sub is_virtual { exists shift->{virthost} }

# the preloaded file needs to be precompiled into the package
# specified by the 'package' attribute, not RegistryLoader
sub namespace_root {
    join '::', ModPerl::RegistryCooker::NAMESPACE_ROOT,
        shift->{REQ}->{package};
}

# override Apache class methods called by Modperl::Registry*. normally
# only available at request-time via blessed request_rec pointer
sub slurp_filename {
    my $r = shift;
    my $tainted = @_ ? shift : 1;
    my $filename = $r->filename;
    open my $fh, $filename or die "can't open $filename: $!";
    local $/;
    my $code = <$fh>;
    unless ($tainted) {
        ($code) = $code =~ /(.*)/s; # untaint
    }
    close $fh;
    return \$code;
}

sub load_package {
    my ($self, $package) = @_;

    croak "package to load wasn't specified" unless defined $package;

    $package =~ s|::|/|g;
    $package .= ".pm";
    require $package;
};

sub warn {
    my $self = shift;
    Apache2::Log->warn(__PACKAGE__ . ": @_\n");
}

1;
__END__

=head1 NAME

ModPerl::RegistryLoader - Compile ModPerl::RegistryCooker scripts at server startup

=head1 Synopsis

  # in startup.pl
  use ModPerl::RegistryLoader ();
  use File::Spec ();
  
  # explicit uri => filename mapping
  my $rlbb = ModPerl::RegistryLoader->new(
      package => 'ModPerl::RegistryBB',
      debug   => 1, # default 0
  );

  $rlbb->handler($uri, $filename);
  
  ###
  # uri => filename mapping using a helper function
  sub trans {
      my $uri = shift;
      $uri =~ s|^/registry/|cgi-bin/|;
      return File::Spec->catfile(Apache2::ServerUtil::server_root, $uri);
  }
  my $rl = ModPerl::RegistryLoader->new(
      package => 'ModPerl::Registry',
      trans   => \&trans,
  );
  $rl->handler($uri);
  
  ###
  $rlbb->handler($uri, $filename, $virtual_hostname);


=head1 Description

This modules allows compilation of scripts, running under packages
derived from C<ModPerl::RegistryCooker>, at server startup.  The
script's handler routine is compiled by the parent server, of which
children get a copy and thus saves some memory by initially sharing
the compiled copy with the parent and saving the overhead of script's
compilation on the first request in every httpd instance.

This module is of course useless for those running the
C<L<ModPerl::PerlRun>> handler, because the scripts get recompiled on
each request under this handler.

=head1 Methods

=over

=item new()

When creating a new C<ModPerl::RegistryLoader> object, one has to
specify which of the C<ModPerl::RegistryCooker> derived modules to
use. For example if a script is going to run under
C<ModPerl::RegistryBB> the object is initialized as:

  my $rlbb = ModPerl::RegistryLoader->new(
      package => 'ModPerl::RegistryBB',
  );

If the package is not specified C<ModPerl::Registry> is assumed:

  my $rlbb = ModPerl::RegistryLoader->new();

To turn the debugging on, set the I<debug> attribute to a true value:

  my $rlbb = ModPerl::RegistryLoader->new(
      package => 'ModPerl::RegistryBB',
      debug   => 1,
  );

Instead of specifying explicitly a filename for each uri passed to
handler(), a special attribute I<trans> can be set to a subroutine to
perform automatic remapping.

  my $rlbb = ModPerl::RegistryLoader->new(
      package => 'ModPerl::RegistryBB',
      trans   => \&trans,
  );

See the handler() item for an example of using the I<trans> attribute.

=item handler()

  $rl->handler($uri, [$filename, [$virtual_hostname]]);

The handler() method takes argument of C<uri> and optionally of
C<filename> and of C<virtual_hostname>.

URI to filename translation normally doesn't happen until HTTP request
time, so we're forced to roll our own translation. If the filename is
supplied it's used in translation.

If the filename is omitted and a C<trans> subroutine was not set in
new(), the loader will try using the C<uri> relative to the
C<ServerRoot> configuration directive.  For example:

  httpd.conf:
  -----------
  ServerRoot /xampp/apache
  Alias /registry/ /xampp/apache/cgi-bin/

  startup.pl:
  -----------
  use ModPerl::RegistryLoader ();
  my $rl = ModPerl::RegistryLoader->new(
      package => 'ModPerl::Registry',
  );
  # preload /xampp/apache/cgi-bin/test.pl
  $rl->handler(/registry/test.pl);

To make the loader smarter about the URI-E<gt>filename translation,
you may provide the C<new()> method with a C<trans()> function to
translate the uri to filename.

The following example will pre-load all files ending with I<.pl> in
the I<cgi-bin> directory relative to C<ServerRoot>.

  httpd.conf:
  -----------
  ServerRoot /xampp/apache
  Alias /registry/ /xampp/apache/cgi-bin/

  startup.pl:
  -----------
  {
      # test the scripts pre-loading by using trans sub
      use ModPerl::RegistryLoader ();
      use File::Spec ();
      use DirHandle ();
      use strict;
  
      my $dir = File::Spec->catdir(Apache2::ServerUtil::server_root,
                                  "cgi-bin");
  
      sub trans {
          my $uri = shift; 
          $uri =~ s|^/registry/|cgi-bin/|;
          return File::Spec->catfile(Apache2::ServerUtil::server_root,
                                     $uri);
      }
  
      my $rl = ModPerl::RegistryLoader->new(
          package => "ModPerl::Registry",
          trans   => \&trans,
      );
      my $dh = DirHandle->new($dir) or die $!;
  
      for my $file ($dh->read) {
          next unless $file =~ /\.pl$/;
          $rl->handler("/registry/$file");
      }
  }

If C<$virtual_hostname> argument is passed it'll be used in the
creation of the package name the script will be compiled into for
those registry handlers that use I<namespace_from_uri()> method.  See
also the notes on C<$ModPerl::RegistryCooker::NameWithVirtualHost> in
the C<L<ModPerl::RegistryCooker>> documentation.

Also
explained in the C<L<ModPerl::RegistryLoader>> documentation, this
only has an effect at run time if
C<$ModPerl::RegistryCooker::NameWithVirtualHost> is set to true,
otherwise the C<$virtual_hostname> argument is ignored.

=back


=head1 Implementation Notes

C<ModPerl::RegistryLoader> performs a very simple job, at run time it
loads and sub-classes the module passed via the I<package> attribute
and overrides some of its functions, to emulate the run-time
environment. This allows to preload the same script into different
registry environments.

=head1 Authors

The original C<Apache2::RegistryLoader> implemented by Doug MacEachern.

Stas Bekman did the porting to the new registry framework based on
C<ModPerl::RegistryLoader>.

=head1 SEE ALSO

C<L<ModPerl::RegistryCooker>>, C<L<ModPerl::Registry>>,
C<L<ModPerl::RegistryBB>>, C<L<ModPerl::PerlRun>>, Apache(3),
mod_perl(3)
