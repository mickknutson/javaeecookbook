@rem = '--*-Perl-*--
@echo off
if "%OS%" == "Windows_NT" goto WinNT
perl.exe -x -S "%0" %1 %2 %3 %4 %5 %6 %7 %8 %9
goto endofperl
:WinNT
perl.exe -x -S %0 %*
if NOT "%COMSPEC%" == "%SystemRoot%\system32\cmd.exe" goto endofperl
if %errorlevel% == 9009 echo You do not have Perl in your PATH.
if errorlevel 1 goto script_failed_so_exit_with_non_zero_val 2>nul
goto endofperl
@rem ';
#!\xampp\perl\bin\perl.exe
#line 15
    eval 'exec perl.exe -S $0 ${1+"$@"}'
	if $running_under_some_shell;
#
#
# The code below cleans up perllocal.pod, removing outdated and duplicate entries.
# Just run it from the command line:
#
# perllocal
#
# Sourcecode from: http://www.perlmonks.org/?node_id=483020
#

use strict;
use warnings;
$|=1;

use Pod::Perldoc;

MAIN:
{
    # Find perllocal.pod
    my ($pod) = Pod::Perldoc->new()->grand_search_init([ 'perllocal' ]);
    if (! $pod) {
        print(STDERR "WARNING: 'perllocal.pod' not found\n");
        exit(1);
    }

    # Parse perllocal.pod
    my %pod;
    my $removed = 0;
    if (open(my $IN, $pod)) {
        my ($line, $module, $order);

        # Read up to first 'head2' line
        while ($line = readline($IN)) {
            if ($line =~ /^=head2/) {
                last;
            }
        }

        # Parse each module entry
        # Duplicates will be overwritten by later entries in the file
        do {
            # New module entry encountered
            if ($line =~ /^=head2/) {
                # Extract module name from 'head2' line
                ($module) = $line =~ /L<([^|]+)\|/;
                # See if it's a duplicate
                if (exists($pod{$module})) {
                    $removed++;
                }
                # Remember this module's order in the file
                $pod{$module}{'order'} = ++$order;
                # Save the text
                $pod{$module}{'text'} = $line;

            } else {
                # Concatenate text for current module entry
                $pod{$module}{'text'} .= $line;
            }
        } while ($line = readline($IN));
        close($IN);

    } else {
        print(STDERR "ERROR: Failure opening '$pod': $!\n");
        exit(1);
    }

    # Check for uninstalls
    if (@ARGV) {
        my $arg = shift(@ARGV);
        if ($arg eq '-u') {
            for my $mod (@ARGV) {
                if (delete($pod{$mod})) {
                    print("$mod removed from 'perllocal'\n");
                    $removed++;
                } else {
                    print("$mod not found in 'perllocal'\n");
                }
            }
        }
    }

    # Output the cleaned up results
    my $cnt = 0;
    if (open(my $OUT, "> $pod")) {
        # Sort by original order
        for my $module (sort { $pod{$a}{'order'} <=> $pod{$b}{'order'}}
                          keys(%pod))
        {
            # Output the module entry
            print($OUT $pod{$module}{'text'});
            $cnt++;
        }
        close($OUT);

    } else {
        print(STDERR "ERROR: Failure opening '$pod': $!\n");
        exit(1);
    }

    # Report on results
    print("'perllocal' now contains $cnt entries.  ($removed removed.)\n");
}

exit(0);

__END__
:endofperl
