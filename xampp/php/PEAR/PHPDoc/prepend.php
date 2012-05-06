<?php

// 3/18/2002 Tim Gallagher<timg@sunflowerroad.com> moved next 
// section from index.php to prepend.php
// include the template setup file.
// this file is used by the front-end to display a drop down list of
// available templates, which then are sent to the doc object ($doc->setTemplateDirectory() )
// and/or could be used by the command line version to show a list of available templates.
include (PHPDOC_INCLUDE_DIR . "renderer/html/templates.php");

// 3/18/2002 Tim Gallagher<timg@sunflowerroad.com> moved next 
// section from index.php to prepend.php

// Important: set PHPDOC_LINEBREAK to the Linebreak sign for your system
if (substr(php_uname(), 0, 7) == "Windows") {
    $lnbreak = "\r\n";
} else {
    $lnbreak = "\n";
}; // end if
define("PHPDOC_LINEBREAK", $lnbreak);


// if set to true, the following const modifies the behavior of PhpdocParserCore.php
// to set the short description and long description seperately
// and not set the long to the short if the long does not appear in the doc comments.
// see PhpdocParserCore.php::getDescription() for more info.
define("PHPDOC_SEPARATE_DESCRIPTIONS",TRUE);

// 3/11/2002 Tim Gallagher<timg@sunflowerroad.com> added two defines
// that are used on generated documentation
// PHPDOCVERSION is the name of the tempate tag
define("PHPDOC_VERSION", "PHPDoc v1.5");
// PHPDOC_LINK is the name of the template tag
define("PHPDOC_LINK", '<a href="http://www.phpdoc.de/">www.phpdoc.de</a');


// 3/19/2002 Tim Gallagher<timg@sunflowerroad.com> added two defines
// that are used on generated documentation
// PHPDOCVERSION is the name of the tempate tag
define("PHPDOC_GENERATED_DATE", date('D, d M Y H:i:s O'));

require( PHPDOC_INCLUDE_DIR . "exceptions/PhpdocError.php" );

// Phpdoc Core 
require( PHPDOC_INCLUDE_DIR . "core/PhpdocObject.php" );
require( PHPDOC_INCLUDE_DIR . "core/PhpdocArgvHandler.php" );
require( PHPDOC_INCLUDE_DIR . "core/PhpdocSetupHandler.php" );
require( PHPDOC_INCLUDE_DIR . "core/Phpdoc.php" );

// Phpdoc Warning container
require( PHPDOC_INCLUDE_DIR . "warning/PhpdocWarning.php" );

// Phpdoc File Handler
require( PHPDOC_INCLUDE_DIR . "filehandler/PhpdocFileHandler.php" );

// Phpdoc Parser
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocParserRegExp.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocParserTags.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocParserCore.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocUseParser.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocConstantParser.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocModuleParser.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocVariableParser.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocFunctionParser.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocClassParser.php" );
require( PHPDOC_INCLUDE_DIR . "parser/PhpdocParser.php" );

// Phpdoc Analyser
require( PHPDOC_INCLUDE_DIR . "analyser/PhpdocAnalyser.php" );
require( PHPDOC_INCLUDE_DIR . "analyser/PhpdocClassAnalyser.php" );
require( PHPDOC_INCLUDE_DIR . "analyser/PhpdocModuleAnalyser.php" );

// Phpdoc Indexer
require( PHPDOC_INCLUDE_DIR . "indexer/PhpdocIndexer.php" );

// Phpdoc XML Writer
require( PHPDOC_INCLUDE_DIR . "xmlwriter/PhpdocXMLWriter.php" );

// Phpdoc XML Exporter
require( PHPDOC_INCLUDE_DIR . "xmlexporter/PhpdocXMLExporter.php" );
require( PHPDOC_INCLUDE_DIR . "xmlexporter/PhpdocXMLIndexExporter.php" );
require( PHPDOC_INCLUDE_DIR . "xmlexporter/PhpdocXMLWarningExporter.php" );
require( PHPDOC_INCLUDE_DIR . "xmlexporter/PhpdocXMLDocumentExporter.php");
require( PHPDOC_INCLUDE_DIR . "xmlexporter/PhpdocXMLModuleExporter.php" );
require( PHPDOC_INCLUDE_DIR . "xmlexporter/PhpdocXMLClassExporter.php" );


// Redistributed IT[X] Templates from the PHPLib
// 3/8/2002 - Tim Gallagher<timg@sunflowerroad.com>
// made the following change
// I'm going to leave both these and the pear ones in...
// and a person can comment/uncomment as they wish to do.
// so nothing breaks in case something in it[x] changes
// and it seems a little more "application-like" to have
// everything self-contained.  shoot me if i'm wrong.
// comment the next two lines out to use pear.
require( PHPDOC_INCLUDE_DIR . "redist/IT.php" );
require( PHPDOC_INCLUDE_DIR . "redist/ITX.php" );

/* uncomment this comment to use pear.
// IT[X] Templates
require_once "HTML/IT.php";
require_once "HTML/ITX.php";
*/

// XML Reader
require( PHPDOC_INCLUDE_DIR . "xmlreader/PhpdocXMLReader.php" );

// API to access XML data
require( PHPDOC_INCLUDE_DIR . "accessor/PhpdocAccessor.php" );
require( PHPDOC_INCLUDE_DIR . "accessor/PhpdocIndexAccessor.php" );
require( PHPDOC_INCLUDE_DIR . "accessor/PhpdocWarningAccessor.php" );
require( PHPDOC_INCLUDE_DIR . "accessor/PhpdocDocumentAccessor.php" );
require( PHPDOC_INCLUDE_DIR . "accessor/PhpdocClassAccessor.php" );
require( PHPDOC_INCLUDE_DIR . "accessor/PhpdocModuleAccessor.php" );

// Phpdoc Renderer
require( PHPDOC_INCLUDE_DIR . "renderer/PhpdocRendererObject.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLRenderer.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLIndexRenderer.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLWarningRenderer.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLDocumentRenderer.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLModuleRenderer.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLClassRenderer.php" );
require( PHPDOC_INCLUDE_DIR . "renderer/html/PhpdocHTMLRendererManager.php" );

/**
* The main page header for PHPDoc
* Created from the original index.php file written by Ulf Wendel
* @access Public
* @author Tim Gallagher<timg@sunflowerroad.com>
* @parameter
*/
function mainPageHeader()
{
    // in case we're running less than 4.1.0
    global $_REQUEST;
    global $_SERVER;

    global $PHPDOC_templates;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>PHPDOC Version 2.0</title>
</head>

<body>
<font face="Arial, Helvetica" size="1">
	<table width="750">
		<tr>
			<td align="left" valign="top">
				<h2><?PHP echo PHPDOC_VERSION; ?></h2>
						<P>PHPDoc currently requires a late PHP4 version (4.0.3dev or above) to work.
						Some earlier version like to crash with memory trouble.
						PHPDoc now requires at least version 4.0.0 and <i>might</i> run with
                        versions prior to 4.0.3dev on a good day, but you might have problems.
			</td>
		<tr>
			<td align="left" valign="top"><hr></td>
		</tr>
		<tr>
			<td align="left" valign="top">
            <?	include ("./front-end.php");
} // end func
/**
* The main page footer for PHPDoc
* Created from the original index.php file written by Ulf Wendel
* @access Public
* @author Tim Gallagher<timg@sunflowerroad.com>
*/
function mainPageFooter()
{
    // in case we're running less than 4.1.0
    global $_REQUEST;
    global $_SERVER;
?>
            </td>
		</tr>
		<tr>
			<td align="left" valign="top">
					<big>PHPDoc has finished.</big><br>
					The generated XML and HTML files can be found in <strong><?= $_REQUEST['PHPDOC_targetdir']; ?></strong><br>
					Don't be disappointed if PHPDoc makes documentation mistakes - remember it's only grabbing not parsing.
					If PHPDoc will develop as a standard this will change as soon as possible. Please be patient
					and wait until "... later this year." ;-)</p>
					<p>
					Have fun!
					</p>
					<a href="mailto:ulf.wendel@phpdoc.de">Ulf Wendel</a>
                    <?PHP echo PHPDOC_LINK; ?>
				</font>
			</td>
		</tr>
	</table>
</body>
</html><?PHP
}; // end func
?>
