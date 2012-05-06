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
@ECHO [INFO]  package glassfish:redeploy -DskipTests=true -e -o
@ECHO [INFO] ------------------------------------------------------------------

mvn package glassfish:redeploy -DskipTests=true -e -o

set /p status=Please hit ENTER to close window

