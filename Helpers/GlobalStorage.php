<?php

/**
 * Storage of global variables
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

class GlobalStorage
{
    protected static $_data = [];

    /**
     * Check is data exist in global storage
     *
     * @param string $key unique data key
     * @param string $group data group
     * @return bool
     */
    public static function has($key, $group = 'global')
    {
        return isset( static::$_data[ $group ][ $key ] );
    }

    /**
     * Set data to global storage
     *
     * @param string $key unique data key
     * @param mixed $value data
     * @param string $group data group
     */
    public static function set($key, $value, $group = 'global')
    {
        static::$_data[ $group ][ $key ] = $value;
    }

    /**
     * Get data from global storage
     *
     * @param string $key unique data key
     * @param string $group data group
     * @return mixed
     */
    public static function get($key, $group = 'global')
    {
        if( ! static::has( $key, $group ) ) {
            return null;
        }
        return static::$_data[ $group ][ $key ];
    }

}