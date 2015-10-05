<?php

/**
 * Abstract class for module initialization
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

abstract class AbstractModuleInitialization extends AbstractInitialization
{
    /**
     * Get module path
     *
     * @return string
     */
    protected function get_module_path()
    {
        return rtrim(TEMPLATEPATH . DIRECTORY_SEPARATOR . str_replace(["\\", "Initialization"], [DIRECTORY_SEPARATOR, ""], get_called_class()), DIRECTORY_SEPARATOR);
    }

    /**
     * Get module url
     *
     * @return string
     */
    protected function get_module_url()
    {
        return rtrim(get_template_directory_uri() . "/" . str_replace(["\\", "Initialization"], ["/", ''], get_called_class()), "/");
    }

    /**
     * Get module assets url
     *
     * @return string
     */
    protected function get_module_assets_url()
    {
        return $this->get_module_url() . '/assets';
    }
}