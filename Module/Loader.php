<?php

/**
 * WPKit module loader
 *
 * @package WPKit\Module
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Module;

use WPKit\Exception\WpException;
use WPKit\Helpers\Strings;

class Loader
{

    public function __construct()
    {
        // load languages
        add_action('after_setup_theme', function() {
            load_theme_textdomain('wpkit', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Languages');
        });
    }

    /**
     * Load all theme modules
     *
     * @param array $modules list of modules
     * @throws \WPKit\Exception\WpException
     */
    public function load_modules(array $modules = null)
    {
        $this->_init_module_autoloader();

        $dir = TEMPLATEPATH . DIRECTORY_SEPARATOR . "modules";

        // load all modules from directory
        if($modules === null) {

            if( ! is_dir($dir) ) {
                throw new WpException("Invalid modules directory: $dir");
            }

            foreach (new \DirectoryIterator($dir) as $file) {
                if ($file->isDir() && !$file->isDot()) {
                    $this->_load_module($dir, $file->getFilename());
                }
            }

        }
        // load modules from list
        else {

            foreach($modules as $module) {
                $this->_load_module($dir, $module);
            }

        }
    }

    /**
     * Load module
     *
     * @param string $path modules directory path
     * @param string $name module name (folder)
     * @throws \WPKit\Exception\WpException
     */
    protected function _load_module($path, $name)
    {
        list($init_file, $class_file, $functions_file, $ajax_file) = $this->_get_module_files($path, $name);

        // if WPKit module
        if(is_file($class_file))
        {
            $class_name = "\\modules\\$name\\Initialization";
            if( ! class_exists($class_name) ) {
                throw new WpException("Module class $class_name not found.");
            }

            $class = new $class_name();
            if( ! ($class instanceof AbstractInitialization) ) {
                throw new WpException("Invalid module class $class_name.");
            }

            $class->init();

            $ajax_class_object = $this->_load_module_ajax_controller($ajax_file, $name);

            if( ! is_file($functions_file)) {
                throw new WpException("Module class $functions_file not found.");
            }

            $this->_create_module_functions_class($name, [
                'MODULE_NAME' => $name,
                'AJAX_PREFIX' => $ajax_class_object ? $ajax_class_object->get_prefix() : null,
            ]);
        }
        // if old module
        elseif(is_file($init_file))
        {
            require_once $init_file; // load init.php
        }
        // if module has not init files
        else
        {
            throw new WpException("Can not load module $name.");
        }
    }

    /**
     * Get all possible init files
     *
     * @param string $path modules directory path
     * @param string $name module name (folder)
     * @return array list of module init files path (init.php, Initialization, Functions, Ajax)
     */
    protected function _get_module_files($path, $name)
    {
        $module_path = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        return [
            $module_path . "init.php",
            $module_path . "Initialization.php",
            $module_path . "Functions.php",
            $module_path . "Ajax.php",
        ];
    }

    /**
     * Create Functions class
     *
     * @param string $name module name (folder)
     * @param array $constants list of module functions constants
     */
    protected function _create_module_functions_class($name, array $constants = [])
    {
        $class_name = Strings::capitalize($name);
        $functions_class_name = "\\modules\\$name\\Functions";

        $var = '';
        foreach($constants as $name => $value) {
            $var .= 'const ' . $name . ' = "' . $value . '"; ';
        }

        eval("class $class_name extends $functions_class_name { $var }");
    }

    /**
     * Init Ajax class
     *
     * @param string $ajax_file Ajax class file path
     * @param string $module_name module name (folder)
     * @return null|\WPKit\Module\AbstractAjax
     */
    private function _load_module_ajax_controller($ajax_file, $module_name)
    {
        $ajax_class_object = null;
        if(is_file($ajax_file)) {
            $ajax_class_name = "\\modules\\$module_name\\Ajax";
            if(class_exists($ajax_class_name)) {
                /**
                 * @var $ajax_class_object \WPKit\Module\AbstractAjax
                 */
                $ajax_class_object = new $ajax_class_name();
            }
        }
        return $ajax_class_object;
    }

    /**
     * Init autoload for classes in modules
     */
    private function _init_module_autoloader()
    {
        spl_autoload_register(function($class_name) {
            if(Strings::position($class_name, "modules") !== false) {
                $filename = TEMPLATEPATH . DIRECTORY_SEPARATOR . ltrim(str_replace("\\", DIRECTORY_SEPARATOR, $class_name), DIRECTORY_SEPARATOR) . ".php";
                if(is_file($filename)) {
                    require_once $filename;
                    return true;
                }
            }
            return false;
        });
    }
}