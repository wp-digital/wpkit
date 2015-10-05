<?php

/**
 * Abstract class for module functions
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

abstract class AbstractFunctions
{
    const MODULE_NAME = null;
    const AJAX_PREFIX = null;

    /**
     * Current module name (slug)
     * @return null
     */
    public static function get_module_name()
    {
        return static::MODULE_NAME;
    }

    /**
     * Get module ajax url
     *
     * @param string $action
     * @return string
     * @throws WpException
     */
    public static function get_ajax_url($action)
    {
        if(static::AJAX_PREFIX == null) {
            throw new WpException('Class Ajax was not found in module "' . static::get_module_name() . '".');
        }
        else {
            return admin_url('admin-ajax.php') . '?action=' . static::get_ajax_action($action);
        }
    }

    /**
     * Get module unique ajax action
     *
     * @param $action
     * @return string
     */
    public static function get_ajax_action($action)
    {
        return static::AJAX_PREFIX . '_' . $action;
    }

	/**
     * Get module url
     *
	 * @return string
	 */
	public static function get_module_url()
	{
		$path = explode('\\', get_called_class());
		if (count($path) == 1) {
			return rtrim(get_template_directory_uri() . "/modules/" . strtolower($path[0]));
		}

		return rtrim(get_template_directory_uri() . "/" . $path[0] . "/" . $path[1]);
	}

	/**
     * Get module assets url
     *
	 * @return string
	 */
	public static function get_module_assets_url()
	{
		return self::get_module_url() . '/assets';
	}

}