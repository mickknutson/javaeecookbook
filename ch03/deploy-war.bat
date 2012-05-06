@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Build and run WAR                                                       ###
@ECHO ###-------------------------------------------------------------------------###

@echo off

REM set JAVA_OPTS="-Xmx128m -XX:MaxPermSize=256m %JAVA_OPTS%"


@echo on
@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  (really) clean
@ECHO [INFO] ------------------------------------------------------------------

RD .\target /Q /S

@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  package glassfish:deploy -DskipTests=true -e
@ECHO [INFO] ------------------------------------------------------------------

mvn package glassfish:deploy -DskipTests=true -e

set /p status=Please hit ENTER to close window

