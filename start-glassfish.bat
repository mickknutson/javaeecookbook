@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Start Glass Fish                                                        ###
@ECHO ###-------------------------------------------------------------------------###

@echo off

@ECHO Set memory and security options:
set JAVA_OPTS=-Xmx512m -XX:MaxPermSize=512m
@ECHO setting JAVA_OPTS to %JAVA_OPTS%


@echo on
@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  glassfish 3
@ECHO [INFO] ------------------------------------------------------------------

@call %GLASSFISH_HOME%\bin\asadmin.bat start-domain domain1

set /p status="Please hit ENTER to close window"

