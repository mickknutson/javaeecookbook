@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Create Site Documentation                                               ###
@ECHO ###-------------------------------------------------------------------------###

@echo off

set JAVA_OPTS="-Xmx1024m -XX:MaxPermSize=1024m"

set "COMMAND=mvn site -e"

@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  running: %COMMAND%
@ECHO [INFO] ------------------------------------------------------------------

%COMMAND%

set /p status=Please hit ENTER to close window

