@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Build and run WAR                                                       ###
@ECHO ###-------------------------------------------------------------------------###

@echo off

set JAVA_OPTS="-Xmx512m -XX:MaxPermSize=512m"

set "COMMAND=package tomcat7:run-war -DskipTests=true -e"

@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  running: %COMMAND%
@ECHO [INFO] ------------------------------------------------------------------

mvn %COMMAND%

set /p status=Please hit ENTER to close window

