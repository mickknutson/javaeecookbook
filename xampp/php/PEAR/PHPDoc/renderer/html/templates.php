<?php

// file added on 3/18/2002 by Tim Gallagher<timg@sunflowerroad.com>
// an include file used by template authors to set their template up for use in PHPDoc

// the default templates
// originally distributed with PHPDoc
$PHPDOC_templates['default']['static_files_path'] = PHPDOC_TEMPLATE_DIR . "renderer/html/default/static/";
$PHPDOC_templates['default']['path'] = PHPDOC_TEMPLATE_DIR . "renderer/html/default/";
$PHPDOC_templates['default']['display_name'] = "Default";
$PHPDOC_templates['default']['description'] = "The built in templates that come with PHPDoc.  Created by Ulf Wendel";

// add your templates below this line

// timmyg's templates created for enhanced readability
// and css layout control
$PHPDOC_templates['timmyg']['static_files_path'] = PHPDOC_TEMPLATE_DIR . "renderer/html/timmyg/static/";
$PHPDOC_templates['timmyg']['path'] = PHPDOC_TEMPLATE_DIR . "renderer/html/timmyg/";
$PHPDOC_templates['timmyg']['display_name'] = "timmyg";
$PHPDOC_templates['timmyg']['description'] = "timmyg's templates created for enhanced readability, and css layout control." .
                                             "Created by timmyg - May be Internet Explorer specific.";



// add your templates above this line.

// we read in a list of file names that the file copier will use
foreach ($PHPDOC_templates as $key => $value)
{
    $handle = opendir($value['static_files_path']);
    while (false !== ($file = readdir($handle))) {
        if (is_file($PHPDOC_templates[$key]['static_files_path'] . $file)) {
            $PHPDOC_templates[$key]['files'][] = $file;
        }; // end if
    }; // end while loop
    closedir($handle);
}; // end foreach loop

?>