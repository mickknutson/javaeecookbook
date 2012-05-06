<?php
/**
* Renders files with warnings.
*
* @version  $Id: PhpdocHTMLWarningRenderer.php,v 1.2 2002/04/11 15:44:45 timmyg Exp $
*/
class PhpdocHTMLWarningRenderer extends PhpdocHTMLRenderer {

    /**
    * Sets the xml and template root directory.
    * 
    * @param    string  XML file path
    * @param    string  Template file path
    * @param    string  Name of the application
    * @param    string  Filename    extension
    * @see      setPath(), setTemplateRoot()
    */
    function PhpdocHTMLWarningRenderer($path, $templateRoot, $application, $extension = ".html") {

        $this->setPath($path);
        $this->setTemplateRoot($templateRoot);
        $this->application = $application;
        $this->file_extension = $extension;

        $this->accessor = new PhpdocWarningAccessor;
        $this->fileHandler = new PhpdocFileHandler;

    } // end constructor

    /**
    * Saves the generated report.
    * 
    * @see      addWarnings()
    * @access   public
    */
    function finishWarnings() {

        if (!is_object($this->tpl)) 
            return;

        // 3/11/2002 - Tim Gallagher added the next two lines
        // so the version and link could be put in and easily
        // changed as versions, and urls change.
        $this->tpl->setVariable("PHPDOCVERSION", PHPDOC_VERSION);
        $this->tpl->setVariable("PHPDOC_LINK", PHPDOC_LINK);
        $this->tpl->setVariable("PHPDOC_GENERATED_DATE", PHPDOC_GENERATED_DATE);
		
        $this->tpl->setVariable("APPNAME", $this->application);

        $this->fileHandler->createFile($this->path."phpdoc_warnings" . $this->file_extension, $this->tpl->get() );

		$this->tpl->free();
        
    }    // end func finishWarnings

    /**
    * Adds file with warnings to the warning list.
    * 
    * @param    string  XML file
    * @see      finishWarnings()
    * @access   public
    */
    function addWarnings($xmlfile) {

        $data = $this->accessor->getWarnings($this->path . $xmlfile);
        if (!is_object($this->tpl)) {
            $this->tpl = new IntegratedTemplate($this->templateRoot);
            $this->tpl->loadTemplateFile("warnings.html");
        }

        reset($data);
        while (list($file, $warnings) = each($data)) {

            $this->tpl->setCurrentBlock("warning_loop");            
            
            reset($warnings);
            while (list($k, $warning) = each($warnings)) {

                $this->tpl->setVariable("WARNINGTYPE", $warning["type"]);
                $this->tpl->setVariable("WARNING", $this->encode($warning["value"]));
                $this->tpl->setVariable("ELEMENT", htmlentities($warning["name"]));
                $this->tpl->setVariable("ELEMENTTYPE", $warning["elementtype"]);
                $this->tpl->parseCurrentBlock();

            }

            $this->tpl->setCurrentBlock("warning");
            $this->tpl->setVariable("FILE", $file);
            $this->tpl->setVariable("NUMWARNINGS", count($warnings));
            $this->tpl->parseCurrentBlock();

        }

        return true;
    } // end func addWarnings

} // end class PhpdocHTMLIndexRenderer
?>