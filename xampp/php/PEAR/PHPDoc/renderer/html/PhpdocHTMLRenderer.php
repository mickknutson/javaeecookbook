<?php
/**
* Default HTML Renderer based on templates.
*
* @version $Id: PhpdocHTMLRenderer.php,v 1.3 2001/12/20 11:04:08 hayk Exp $
*/
class PhpdocHTMLRenderer extends PhpdocRendererObject {

    /**
    * Template object
    *
    * @var  object  IntegratedTemplate
    */    
    var $tpl;

    /**
    * XML data accessor object.
    *
    * @var  object  PhpdocAccessor
    */
    var $accessor;

    /**
    * Rootpath for Templatefiles.
    *
    * @var  string  $templateRoot
    * @see  setTemplateRoot()
    */
    var $templateRoot = "";

    /**
    * Directory path prefix.
    *
    * @var  string  $path
    */
    var $path = "";

    /**
    * Sets a directory path prefix.
    *
    * @param     string  $path
    */
    function setPath($path) {

        if (!empty($path) && "/" != substr($path, -1))
            $path .= "/";

        $this->path = $path;
    } // end func path

    /**
     * Sets the template directory.
     *
     * @param    string  $templateRoot
     */
    function setTemplateRoot($templateRoot) {

        if (!empty($templateRoot) && '/' != substr($templateRoot, -1))
            $templateRoot .= "/";

        $this->templateRoot = $templateRoot;
    } // end func setTemplateRoot

    /**
     * Encodes the given string.
     * 
     * This function gets used to encode all userdependend 
     * elements of the phpdoc xml files. Use it to 
     * customize your rendering result.
     * strip some tags.
     *
     * @param    string  $string    String to encode
     * @return   string  Encoded string
     */
    function encode($string) {
        $string = strip_tags ($string, PHPDOC_ALLOWEDHTMLTAGS);

        if ($regs = preg_split ("/(<pre>|<\/pre>)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            for ($i = 0; $i < sizeof ($regs); $i++)
            {
            	if (($regs[$i] == "<pre>") && ($regs[$i+2] == "</pre>")) $i += 2;
            	else $regs[$i] = preg_replace ("/(" . PHPDOC_LINEBREAK . PHPDOC_LINEBREAK . ")/", '<p>', $regs[$i]);
            }
			return implode ("", $regs);
        } else {
        	return preg_replace ("/(" . PHPDOC_LINEBREAK . PHPDOC_LINEBREAK . ")/", '<p>', $string);
        }

    } // end func encode

} // end class PhpdocHTMLRenderer
?>
