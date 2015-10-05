<?php

/**
 * WPKit class loader
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit;

spl_autoload_register(function($class_name) {

    if(mb_strpos($class_name, __NAMESPACE__) !== false) {
        $filename = ABSPATH . ltrim(str_replace("\\", DIRECTORY_SEPARATOR, $class_name), DIRECTORY_SEPARATOR) . ".php";
        if(is_file($filename)) {
            return include_once $filename;
        }
    }
    return false;
});