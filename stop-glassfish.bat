@cls
@ECHO ###-------------------------------------------------------------------------###
@ECHO ### Stop Glass Fish                                                         ###
@ECHO ###-------------------------------------------------------------------------###

@call %GLASSFISH_HOME%\bin\asadmin.bat stop-domain domain1

set /p status="Please hit ENTER to close window"

