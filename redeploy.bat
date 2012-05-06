@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Redeploy war's to Glassfish                                             ###
@ECHO ###-------------------------------------------------------------------------###

@ECHO Set memory and security options:
set JAVA_OPTS="-Xmx512m -XX:MaxPermSize=512m"
@ECHO setting JAVA_OPTS to %JAVA_OPTS%

set ROOTDIR=%CD%


cd %ROOTDIR%/ch02/
set "COMMAND=mvn clean install -DskipTests=true -e"

@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  running: %COMMAND%
@ECHO [INFO] ------------------------------------------------------------------
mvn %COMMAND%


cd %ROOTDIR%/ch03/
set "COMMAND=mvn package glassfish:redeploy -DskipTests=true -e"

@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  running: %COMMAND%
@ECHO [INFO] ------------------------------------------------------------------
mvn %COMMAND%


cd %ROOTDIR%/ch06-web-mobile/
set "COMMAND=mvn package glassfish:redeploy -DskipTests=true -e"

@ECHO [INFO] ------------------------------------------------------------------
@ECHO [INFO]  running: %COMMAND%
@ECHO [INFO] ------------------------------------------------------------------
mvn %COMMAND%



cd %ROOTDIR%

set /p status="Please hit ENTER to close window"
