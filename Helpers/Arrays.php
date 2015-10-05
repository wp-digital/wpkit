<?php

/**
 * Arrays helper
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

class Arrays
{
    /**
     * Check is associative array
     * @param array $array
     * @return bool
     */
    public static function is_assoc_array($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}