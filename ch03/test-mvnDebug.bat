@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Test with mvnDebug
@ECHO ###-------------------------------------------------------------------------###

@echo off

mvnDebug -DforkMode=never verify -e

set /p status=Please hit ENTER to close window
