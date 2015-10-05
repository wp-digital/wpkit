<?php

/**
 * Actions helper
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Helpers;

use WPKit\Exception\WpException;

class Action
{
    /**
     * Execute callable PHP function or WordPress action
     *
     * @param callable $function function|action to execute
     * @param mixed $args function arguments
     * @throws WpException
     */
    public static function execute($function, $args = null)
    {
        if(is_string($function) && has_action($function)) {
            return do_action($function, $args);
        }
        elseif(is_callable($function))
        {
            if(! is_array($args)) {
                $args = [$args];
            }
            return call_user_func_array($function, $args);
        }
        else {
            $name = is_string($function) ? $function : "Anonymous";
            throw new WpException($name . ' function was not found.');
        }
    }

    /**
     * Check is callable
     *
     * @param callable $function function|action to check
     * @return bool
     */
    public static function is_callable($function)
    {
        return is_callable($function) || (is_string($function) && has_action($function));
    }
}