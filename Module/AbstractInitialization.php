<?php

/**
 * Abstract class for all modules initialization
 *
 * @package WPKit\Module
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 * @author Dmitry Korelsky <dima@pingbull.no>
 *
 */

namespace WPKit\Module;

use WPKit\Helpers\String;

abstract class AbstractInitialization
{
    /**
     * Init module
     */
    public function init()
    {
        $this->_execute_register_methods();
        $this->_execute_add_action_methods();
    }

    private function _execute_register_methods()
    {
        foreach(get_class_methods($this) as $method) {
            if(String::position($method, 'register_') === 0 || (is_admin() && String::position($method, 'admin_register_') === 0)) {
                call_user_func([$this, $method]);
            }
        }

    }

	private function _execute_add_action_methods()
	{
		foreach (get_class_methods($this) as $method) {
			if (String::position($method, 'add_action_') === 0 || String::position($method, 'add_filter_') === 0) {
				$action_name = str_replace(['add_filter_','add_action_'], '', $method);
				$reflection = new \ReflectionMethod($this, $method);
				$params_count = $reflection->getNumberOfParameters();
				add_filter($action_name, [$this, $method], 10, $params_count);
			}
		}
	}

    /**
     * Get current theme url
     *
     * @return string
     */
    protected function get_theme_url()
    {
        return get_template_directory_uri();
    }

    /**
     * Get current theme assets url
     *
     * @return string
     */
    protected function get_theme_assets_url()
    {
        return $this->get_theme_url() . '/assets';
    }
}