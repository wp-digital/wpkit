<?php

/**
 * Transient cache with background updates
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Cache;

use WPKit\Exception\WpException;
use WPKit\Helpers\Action;

class Transient
{
    /**
     * Create transient cache with background updates
     *
     * @param string $key unique transient key
     * @param callable $callback update function
     * @param string $recurrence how often the cache should be updated, default is 'hourly'
     *
     * @throws WpException
     */
    public static function create( $key, $callback, $recurrence = 'hourly' )
    {
        $key = sanitize_key( $key );

        if( empty($key) ) {
            throw new WpException('Invalid Transient key');
        }

        if( ! Action::is_callable( $callback ) ) {
            throw new WpException('Invalid Transient callback');
        }

        $schedules = wp_get_schedules();

        if ( !isset( $schedules[$recurrence] ) ) {
            throw new WpException('Invalid Transient recurrence');
        }

        $cron_action_key = static::_get_cron_action_key($key);

        add_action( $cron_action_key, function() use( $key, $callback ) {
            $value = Action::execute( $callback );
            set_transient( $key, $value, 0 );
        });

        if ( ! wp_next_scheduled( $cron_action_key ) ) {
            wp_schedule_event( time(), $recurrence, $cron_action_key );
        }

    }

    /**
     * Get cached data
     *
     * @param string $key unique transient key
     * @return mixed cached data
     */
    public static function get( $key )
    {
        $key = sanitize_key( $key );
        $value = get_transient( $key );
        if( false === $value ) {
            // todo: add lock system
            static::update( $key );
            $value = get_transient( $key );
        }
        return $value;
    }

    /**
     * Update cache forcibly
     *
     * @param string $key unique transient key
     */
    public static function update( $key )
    {
        $key = sanitize_key( $key );
        $cron_action_key = static::_get_cron_action_key( $key );
        do_action( $cron_action_key );
    }

    protected static function _get_cron_action_key( $key )
    {
        return '_cron_action_' . $key;
    }

} 