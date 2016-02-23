<?php

/**
 * Abstract class for module ajax actions
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

use WPKit\Helpers\Strings;

abstract class AbstractAjax
{
    final public function __construct()
    {
        $this->_register_actions();
    }

    /**
     * Get unique module ajax prefix
     *
     * @return string
     */
    public function get_prefix()
    {
        return sanitize_key(get_called_class());
    }

    private function _register_actions()
    {
        foreach(get_class_methods($this) as $method) {

            if(Strings::position($method, 'action_') === 0) {
                $action = Strings::sub_string($method, 7);
                add_action('wp_ajax_'. $this->get_prefix() . '_' . $action, [$this, $method]);
                add_action('wp_ajax_nopriv_'. $this->get_prefix() . '_' . $action, [$this, $method]);
            }

        }
    }

}