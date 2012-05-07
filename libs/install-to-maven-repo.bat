@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Install jars into local Maven repo
@ECHO ###-------------------------------------------------------------------------###

@echo off

set JAVA_OPTS="-Xmx512m -XX:MaxPermSize=512m"

set ROOTDIR=%CD%

@echo on

mvn install:install-file -Dfile=GoogleAdMobAdsSdk-4.3.1.jar -DgroupId=com.google.android -DartifactId=GoogleAdMobAdsSdk -Dversion=4.3.1 -Dpackaging=jar



mvn install:install-file -Dfile=jmxremote_optional-1.0.1_04.jar -DgroupId=javax.management -DartifactId=jmxremote_optional -Dversion=1.0.1_04 -Dpackaging=jar

mvn install:install-file -Dfile=jmxremote-1.0.1_04.jar -DgroupId=javax.management -DartifactId=jmxremote -Dversion=1.0.1_04 -Dpackaging=jar

mvn install:install-file -Dfile=jmxtools-1.2.1.jar -DgroupId=com.sun.jdmk -DartifactId=jmxtools -Dversion=1.2.1 -Dpackaging=jar

mvn install:install-file -Dfile=jmxri-1.2.1.jar -DgroupId=com.sun.jmx -DartifactId=jmxri -Dversion=1.2.1 -Dpackaging=jar


set /p status=Please hit ENTER to close window
