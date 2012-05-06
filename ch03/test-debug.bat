@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Test in Debug Mode on port 5005
@ECHO ###-------------------------------------------------------------------------###

@echo off

mvn -Dmaven.surefire.debug verify -e

set /p status=Please hit ENTER to close window

