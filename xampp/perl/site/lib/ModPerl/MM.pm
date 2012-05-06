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
package ModPerl::MM;

use strict;
use warnings;

use ExtUtils::MakeMaker ();
use ExtUtils::Install ();

use Cwd ();
use Carp;

our %PM; #add files to installation

# MM methods that this package overrides
no strict 'refs';
my $stash = \%{__PACKAGE__ . '::MY::'};
my @methods = grep *{$stash->{$_}}{CODE}, keys %$stash;
my $eu_mm_mv_all_methods_overriden = 0;

use strict 'refs';

sub override_eu_mm_mv_all_methods {
    my @methods = @_;

    my $orig_sub = \&ExtUtils::MakeMaker::mv_all_methods;
    no warnings 'redefine';
    *ExtUtils::MakeMaker::mv_all_methods = sub {
        # do the normal move
        $orig_sub->(@_);
        # for all the overloaded methods mv_all_method installs a stab
        # eval "package MY; sub $method { shift->SUPER::$method(\@_); }";
        # therefore we undefine our methods so on the recursive invocation of
        # Makefile.PL they will be undef, unless defined in Makefile.PL
        # and my_import will override these methods properly
        for my $sym (@methods) {
            my $name = "MY::$sym";
            undef &$name if defined &$name;
        }
    };
}

sub add_dep {
    my ($string, $targ, $add) = @_;
    $$string =~ s/($targ\s+::)/$1 $add/;
}

sub add_dep_before {
    my ($string, $targ, $before_targ, $add) = @_;
    $$string =~ s/($targ\s+::.*?) ($before_targ)/$1 $add $2/;
}

sub add_dep_after {
    my ($string, $targ, $after_targ, $add) = @_;
    $$string =~ s/($targ\s+::.*?$after_targ)/$1 $add/;
}

sub build_config {
    my $key = shift;
    require Apache2::Build;
    my $build = Apache2::Build->build_config;
    return $build unless $key;
    $build->{$key};
}

#the parent WriteMakefile moves MY:: methods into a different class
#so alias them each time WriteMakefile is called in a subdir

sub my_import {
    my $package = shift;
    no strict 'refs';
    my $stash = \%{$package . '::MY::'};
    for my $sym (keys %$stash) {
        next unless *{$stash->{$sym}}{CODE};
        my $name = "MY::$sym";
        # the method is defined in Makefile.PL
        next if defined &$name;
        # do the override behind the scenes
        *$name = *{$stash->{$sym}}{CODE};
    }
}

my @default_opts = qw(CCFLAGS LIBS INC OPTIMIZE LDDLFLAGS TYPEMAPS);
my @default_dlib_opts = qw(OTHERLDFLAGS);
my @default_macro_opts = ();
my $b = build_config();
my %opts = (
    CCFLAGS      => sub { $b->{MODPERL_CCOPTS}                        },
    LIBS         => sub { join ' ', $b->apache_libs, $b->modperl_libs },
    INC          => sub { $b->inc;                                    },
    OPTIMIZE     => sub { $b->perl_config('optimize');                },
    LDDLFLAGS    => sub { $b->perl_config('lddlflags');               },
    TYPEMAPS     => sub { $b->typemaps;                               },
    OTHERLDFLAGS => sub { $b->otherldflags;                           },
);

sub get_def_opt {
    my $opt = shift;
    return $opts{$opt}->() if exists $opts{$opt};
    # handle cases when Makefile.PL wants an option we don't have a
    # default for. XXX: some options expect [] rather than scalar.
    Carp::carp("!!! no default argument defined for argument: $opt");
    return '';
}

sub WriteMakefile {
    my %args = @_;

    # override ExtUtils::MakeMaker::mv_all_methods
    # can't do that on loading since ModPerl::MM is also use()'d
    # by ModPerl::BuildMM which itself overrides it
    unless ($eu_mm_mv_all_methods_overriden) {
        override_eu_mm_mv_all_methods(@methods);
        $eu_mm_mv_all_methods_overriden++;
    }

    my $build = build_config();
    my_import(__PACKAGE__);

    # set top-level WriteMakefile's values if weren't set already
    for (@default_opts) {
        $args{$_} = get_def_opt($_) unless exists $args{$_}; # already defined
    }

    # set dynamic_lib-level WriteMakefile's values if weren't set already
    $args{dynamic_lib} ||= {};
    my $dlib = $args{dynamic_lib};
    for (@default_dlib_opts) {
        $dlib->{$_} = get_def_opt($_) unless exists $dlib->{$_};
    }

    # set macro-level WriteMakefile's values if weren't set already
    $args{macro} ||= {};
    my $macro = $args{macro};
    for (@default_macro_opts) {
        $macro->{$_} = get_def_opt($_) unless exists $macro->{$_};
    }

    ExtUtils::MakeMaker::WriteMakefile(%args);
}

#### MM overrides ####

sub ModPerl::MM::MY::post_initialize {
    my $self = shift;

    my $build = build_config();
    my $pm = $self->{PM};

    while (my ($k, $v) = each %PM) {
        if (-e $k) {
            $pm->{$k} = $v;
        }
    }

    '';
}

1;

=head1 NAME

ModPerl::MM -- A "subclass" of ExtUtils::MakeMaker for mod_perl 2.0

=head1 Synopsis

  use ModPerl::MM;
  
  # ModPerl::MM takes care of doing all the dirty job of overriding 
  ModPerl::MM::WriteMakefile(...);

  # if there is a need to extend the default methods 
  sub MY::constants {
      my $self = shift;
      $self->ModPerl::MM::MY::constants;
      # do something else;
  }

  # or prevent overriding completely
  sub MY::constants { shift->MM::constants(@_); }";

  # override the default value of WriteMakefile's attribute
  my $extra_inc = "/foo/include";
  ModPerl::MM::WriteMakefile(
      ...
      INC => $extra_inc,
      ...
  );

  # extend the default value of WriteMakefile's attribute
  my $extra_inc = "/foo/include";
  ModPerl::MM::WriteMakefile(
      ...
      INC => join " ", $extra_inc, ModPerl::MM::get_def_opt('INC'),
      ...
  );

=head1 Description

C<ModPerl::MM> is a "subclass" of C<ExtUtils::MakeMaker> for mod_perl
2.0, to a degree of sub-classability of C<ExtUtils::MakeMaker>. 

When C<ModPerl::MM::WriteMakefile()> is used instead of
C<ExtUtils::MakeMaker::WriteMakefile()>, C<ModPerl::MM> overrides
several C<ExtUtils::MakeMaker> methods behind the scenes and supplies
default C<WriteMakefile()> arguments adjusted for mod_perl 2.0
build. It's written in such a way so that normally 3rd party module
developers for mod_perl 2.0, don't need to mess with I<Makefile.PL> at
all.

=head1 C<MY::> Default Methods

C<ModPerl::MM> overrides method I<foo> as long as I<Makefile.PL>
hasn't already specified a method I<MY::foo>. If the latter happens,
C<ModPerl::MM> will DWIM and do nothing.

In case the functionality of C<ModPerl::MM> methods needs to be
extended, rather than completely overriden, the C<ModPerl::MM> methods
can be called internally. For example if you need to modify constants
in addition to the modifications applied by
C<ModPerl::MM::MY::constants>, call the C<ModPerl::MM::MY::constants>
method (notice that it resides in the package C<ModPerl::MM::MY> and
not C<ModPerl::MM>), then do your extra manipulations on constants:

  # if there is a need to extend the methods 
  sub MY::constants {
      my $self = shift;
      $self->ModPerl::MM::MY::constants;
      # do something else;
  }

In certain cases a developers may want to prevent from C<ModPerl::MM>
to override certain methods. In that case an explicit override in
I<Makefile.PL> will do the job. For example if you don't want the
C<constants()> method to be overriden by C<ModPerl::MM>, add to your
I<Makefile.PL>:

  sub MY::constants { shift->MM::constants(@_); }";

C<ModPerl::MM> overrides the following methods:

=head2 C<ModPerl::MM::MY::post_initialize>

This method is deprecated.

=head1 C<WriteMakefile()> Default Arguments

C<ModPerl::MM::WriteMakefile> supplies default arguments such as
C<INC> and C<TYPEMAPS> unless they weren't passed to
C<ModPerl::MM::WriteMakefile> from I<Makefile.PL>.

If the default values aren't satisfying these should be overriden in
I<Makefile.PL>. For example to supply an empty INC, explicitly set the
argument in I<Makefile.PL>.

  ModPerl::MM::WriteMakefile(
      ...
      INC => '',
      ...
  );

If instead of fully overriding the default arguments, you want to
extend or modify them, they can be retrieved using the
C<ModPerl::MM::get_def_opt()> function. The following example appends
an extra value to the default C<INC> attribute:

  my $extra_inc = "/foo/include";
  ModPerl::MM::WriteMakefile(
      ...
      INC => join " ", $extra_inc, ModPerl::MM::get_def_opt('INC'),
      ...
  );

C<ModPerl::MM> supplies default values for the following
C<ModPerl::MM::WriteMakefile> attributes:

=head2 C<CCFLAGS>


=head2 C<LIBS>


=head2 C<INC>


=head2 C<OPTIMIZE>


=head2 C<LDDLFLAGS>


=head2 C<TYPEMAPS>


=head2 C<dynamic_lib>

=head3 C<OTHERLDFLAGS>

  dynamic_lib => { OTHERLDFLAGS => ... }

=head2 C<macro>

=head3 C<MOD_INSTALL>

  macro => { MOD_INSTALL => ... }

makes sure that Apache-Test/ is added to @INC.

=head1 Public API

The following functions are a part of the public API. They are
described elsewhere in this document.

=head2 C<WriteMakefile()>

  ModPerl::MM::WriteMakefile(...);

=head2 C<get_def_opt()>

  my $def_val = ModPerl::MM::get_def_opt($key);

=cut
