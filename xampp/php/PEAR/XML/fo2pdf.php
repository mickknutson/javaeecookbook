<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@phant.ch>                         |
// +----------------------------------------------------------------------+
//
// $Id: fo2pdf.php,v 1.10 2003/03/20 15:32:16 arnaud Exp $

/**
 * Required files
 */
require_once 'PEAR.php';

/**
 * FO to pdf converter.
 *
 * with fo (formating objects) it's quite easy to convert xml-documents into
 *  pdf-docs (and not only pdf, but also ps, pcl, txt and more)
 *
 * see README.fo2pdf for details
 *
 * @author   Christian Stocker <chregu@nomad.ch>
 * @version  $Id: fo2pdf.php,v 1.10 2003/03/20 15:32:16 arnaud Exp $
 * @package  XML
 */
class XML_fo2pdf
{
    /**
     * fo-file used in this class
     *
     * @var  string
     */
    var $fo = '';

    /**
     * pdf-file used in this class
     *
     * @var  string
     */
    var $pdf = '';

    /**
     * Where the temporary fo and pdf files should be stored
     *
     * @var  string
     */
    var $tmpdir = '/tmp';

    /**
     * A prefix for the temporary files
     *
     * @var  string
     */
    var $tmppdfprefix = 'pdffo';

    /**
     * the render Type. At the moment (fop 0.20.1), possible values are
     * - awt  
     * - mif
     * - pcl
     * - pdf
     * - ps  
     * - txt 
     * - xml
     *
     * @var string
     */
    var $renderer = 'pdf';

    /**
     * the content-type to be sent if printPDF is called.
     *
     * @var contenttype
     * @see printPDF()
     */
    var $contenttype = 'application/pdf';

    /** 
     * If you need more Fonts or have some other stuff, which needs a
     *  Fop-Configfile, you can assign one
     *
     * See http://xml.apache.org/fop/fonts.html for Details about
     *  embedding fonts.
     *
     * @var configFile
     */
    var $configFile = null;

      
    /**
     * constructor
     * ATTENTION (you've been warned!):
     * You should not pass the values here, 'cause then you don't have
     *  Error-Reporting. This variables are only here for Backwards Compatibilty..
     *  Use $fop->run("input.fo","output.pdf") instead.
     *
     * @param    string  $fo     file input fo-file (do not use it anymore)
     * @param    string  $pdf    file output pdf-file (do not use it anymore)
     * @see run(), runFromString(), runFromFile()
     * @access public
     */
    function xml_fo2pdf ($fo = null, $pdf = '')
    {
        if (!(is_null($fo))) {
           $this->run($fo, $pdf);
        }
    }

    /**
     * Calls the Main Fop-Java-Programm
     *
     * One has to pass an input fo-file
     *  and if the pdf should be stored permanently, a filename/path for
     *  the pdf.
     *  if the pdf is not passed or empty/false, a temporary pdf-file
     *   will be created
     *
     * @param    string  $fo     file input fo-file
     * @param    string  $pdf    file output pdf-file
     * @param    boolean $DelFo  if the fo should be deleted after execution
     * @see runFromString()
     */
    function run($fo, $pdf = '', $DelFo = false)
    {
        $returnuri = false;
        if (!$pdf) {
            $pdf = tempnam($this->tmpdir, $this->tmppdfprefix);
            $returnuri = true;
        }

        $this->pdf = $pdf;
        $this->fo  = $fo;
        $options   = array();
        //$options   = array('-d');
        if ($this->configFile) {
            $options = array('-c', $this->configFile);
            //array_push($options, '-c', $this->configFile);
        }
        
        array_push($options, $this->fo, '-' . $this->renderer, $this->pdf);

        if (!$options = @new Java("org.apache.fop.apps.CommandLineOptions", $options)) {
        	return PEAR::raiseError('Unable to create Java Virtual Machine in ' . __FILE__ . ':' . __LINE__, 11, PEAR_ERROR_RETURN, null, null);
        } // if

        $starter = $options->getStarter();
        $starter->run();

        if ($DelFo) {
            $this->deleteFo($fo);
        }
   
        if ($returnuri) {
        	return $pdf;
        } else {
            return true;
        } // if
    }

    /**
     * If the fo is a string, not a file, use this.
     *
     * If you generate the fo dynamically (for example with a
     *  xsl-stylesheet), you can use this method
     *
     * The Fop-Java program needs a file as an input, so a
     *  temporary fo-file is created here (and will be deleted
     *  in the run() function.)
     *
     * @param    string  $fostring   fo input fo-string
     * @param    string  $pdf        file output pdf-file
     * @see run()
     */
    function runFromString($fostring, $pdf = '')
    {
        $fo = tempnam($this->tmpdir, $this->tmppdfprefix);
        $fp = fopen($fo, 'w+');
        fwrite($fp, $fostring);
        fclose($fp);
        return $this->run($fo, $pdf, true);
    }
    /**
     * A wrapper to run for better readabilty
     *
     * This method just calls run....
     *
     * @param    string  $fo     fo input fo-string
     * @param    string  $pdf    file output pdf-file
     * @see run()
     */
   function runFromFile($fo, $pdf = '')
    {
        return $this->run($fo, $pdf);
    }

    /**
     * Deletes the created pdf
     *
     * If you dynamically create pdfs and you store them
     *  for example in a Cache, you don't need it afterwards.
     * If no pdf is given, the one generated in run() is deleted
     *
     * @param    string  $pdf    file output pdf-file
     * @access public
     */
    function deletePDF($pdf = '')
    {
        if (!$pdf) {
            $pdf = $this->pdf;
        }

        @unlink($pdf);
    }

    /**
     * Deletes the created fo
     *
     * If you dynamically create fos, you don't need it afterwards.
     * If no fo-file is given, the one generated in run() is deleted
     *
     * @param    string  $fo  file input fo-file
     */
    function deleteFo($fo = '')
    {
        if (!$fo) {
            $fo = $this->fo;
        }

        @unlink($fo);
    }

    /**
     * Prints the content header and the generated pdf to the output
     *
     * If you want to dynamically generate pdfs and return them directly
     *  to the browser, use this.
     * If no pdf-file is given, the generated from run() is taken.
     *
     * @param    string  $pdf    file output pdf-file
     * @see returnPDF()
     * @access public    
     */
    function printPDF($pdf = '')
    {
        $pdf = $this->returnPDF($pdf);
        Header('Content-type: ' . $this->contenttype . "\r\nContent-Length: " . strlen($pdf));
        print $pdf;
    }

    /**
     * Returns the pdf
     *
     * If no pdf-file is given, the generated from run() is taken.
     *
     * @param    string  $pdf    file output pdf-file
     * @return   string pdf
     * @see run()    
     */
    function returnPDF($pdf = '')
    {
        if (!$pdf) {
            $pdf = $this->pdf;
        }

        $fd = fopen($pdf, "r");
        $content = fread( $fd, filesize($pdf) );
        fclose($fd);
        return $content;
    }
    
    /**
     * sets the rendertype
     *
     * @param    string  $renderer    the type of renderer which should be used
     * @param    string  $overwriteContentType if the contentType should be set to a approptiate one    
     * @see $renderer
     * @access public
     */  
    
    function setRenderer($renderer = 'pdf', $overwriteContentType = true)
    {
        $this->renderer = $renderer;
        if ($overwriteContentType) {
            switch ($renderer) {        
                    case 'pdf':
                    $this->contenttype = 'application/pdf';
                    break;
                    case 'ps':
                    $this->contenttype = 'application/ps';
                    break;
                    case 'pcl':
                    $this->contenttype = 'application/pcl';
                    break;                    
                    case 'txt':
                    $this->contenttype = 'text/plain';
                    break;                                        
                    case 'xml':
                    $this->contenttype = 'text/xml';
                    break;                                        
             }
         }
    }

    /**
     * sets the content-type
     *
     * @param string $contenttype the content-type for the http-header
     * @see $contenttype
     * @access public
     */  
    function setContentType($contenttype = 'application/pdf')
    {
        $this->contenttype = $contenttype;
    }

    /**
     * Sets the configfile-type.
     *
     * @param   string $configFile the config file for fop 
     * @access  public
     * @see     $configFile
     */  
    function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }
}
?>