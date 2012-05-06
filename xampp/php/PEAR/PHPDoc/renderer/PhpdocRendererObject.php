<?php
/**
* Superclass of all Renderer. 
*
* Derive all custom renderer from this class.
*
* @version $Id: PhpdocRendererObject.php,v 1.1 2001/05/08 04:48:38 sbergmann Exp $
*/
class PhpdocRendererObject extends PhpdocObject {

    var $warn;

    var $accessor;

    /**
    * Extension for generated files.
    * @var  string  
    */
    var $file_extension = ".html";

} // end class PhpdocRendererObject
?>