<?php

/**
 * Date helper
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\Helpers;

class Date
{
	/**
     * Escape date string
     *
	 * @param string $value date string
	 * @param string $format date format
	 * @param null $new_format new date format
	 *
	 * @return string
	 */
	public static function esc_date( $value, $format = 'Y-m-d', $new_format = null )
	{
		$date = \DateTime::createFromFormat( $format, $value );
		return $date ? $date->format( !empty( $new_format ) ? $new_format : $format ) : '';
	}
}