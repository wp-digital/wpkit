<?php

/**
 * String helper (utf-8)
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

class String {

    /**
     * Pluralize word
     *
     * @param string $word word to pluralize
     * @param string $locale lang locale
     * @return string
     */
	public static function pluralize($word, $locale = null)
	{
        if( null == $locale ) {
            $locale = get_locale();
        }

		$prefix = '';

		if (is_numeric($word[0])) {
			$parts   = explode(' ', $word);
			$word    = array_pop($parts);
			$if_many = $parts[0];
			$prefix  = implode(' ', $parts) . ' ';
		}

		if (isset($if_many) && $if_many == 1) {
			$word = self::singularize($word);
		}
        else {
			if ($locale == 'nb_NO') {
				$plural = [
					'/$/'   => 'er',
					'/r$/i' => 're',
					'/e$/i' => 'er',
				];
			}
            else {
				$plural = [
					'/(quiz)$/i'               => '\1zes',
					'/^(ox)$/i'                => '\1en',
					'/([m|l])ouse$/i'          => '\1ice',
					'/(matr|vert|ind)ix|ex$/i' => '\1ices',
					'/(x|ch|ss|sh)$/i'         => '\1es',
					'/([^aeiouy]|qu)y$/i'      => '\1ies',
					'/(hive)$/i'               => '\1s',
					'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
					'/sis$/i'                  => 'ses',
					'/([ti])um$/i'             => '\1a',
					'/(buffal|tomat)o$/i'      => '\1oes',
					'/(bu)s$/i'                => '\1ses',
					'/(alias|status)/i'        => '\1es',
					'/(octop|vir)us$/i'        => '\1i',
					'/(ax|test)is$/i'          => '\1es',
					'/s$/i'                    => 's',
					'/$/'                      => 's'
				];
			}
			if ($locale == 'nb_NO') {
				$irregular = ['konto' => 'konti'];
			}
            else {
				$irregular = [
					'person' => 'people',
					'man'    => 'men',
					'child'  => 'children',
					'sex'    => 'sexes',
					'move'   => 'moves'
				];
			}
			$ignore = [
				'equipment',
				'information',
				'rice',
				'money',
				'species',
				'series',
				'fish',
				'sheep',
				'data'
			];

			$lower_word = strtolower($word);
			foreach ($ignore as $ignore_word) {
				if (substr($lower_word, (- 1 * strlen($ignore_word))) == $ignore_word) {
					return $prefix . $word;
				}
			}

			foreach ($irregular as $_plural => $_singular) {
				if (preg_match('/(' . $_plural . ')$/i', $word, $arr)) {
					return $prefix . preg_replace('/(' . $_plural . ')$/i', substr($arr[0], 0, 1) . substr($_singular, 1), $word);
				}
			}

			foreach ($plural as $rule => $replacement) {
				if (preg_match($rule, $word)) {
					return $prefix . preg_replace($rule, $replacement, $word);
				}
			}
		}

		return $prefix . $word;
	}

    /**
     * Singularize word
     *
     * @param string $word word to singularize
     * @param string $locale lang locale
     * @return string
     */
	public static function singularize($word, $locale = null)
	{
        if( null == $locale ) {
            $locale = get_locale();
        }

		if ($locale == 'nb_NO') {
			$singular = [
				'/er$/i' => '',
				'/re$/i' => 'r',
			];
		}
        else {
			$singular = [
				'/(quiz)zes$/i'                                                    => '\\1',
				'/(matr)ices$/i'                                                   => '\\1ix',
				'/(vert|ind)ices$/i'                                               => '\\1ex',
				'/^(ox)en/i'                                                       => '\\1',
				'/(alias|status)es$/i'                                             => '\\1',
				'/([octop|vir])i$/i'                                               => '\\1us',
				'/(cris|ax|test)es$/i'                                             => '\\1is',
				'/(shoe)s$/i'                                                      => '\\1',
				'/(o)es$/i'                                                        => '\\1',
				'/(bus)es$/i'                                                      => '\\1',
				'/([m|l])ice$/i'                                                   => '\\1ouse',
				'/(x|ch|ss|sh)es$/i'                                               => '\\1',
				'/(m)ovies$/i'                                                     => '\\1ovie',
				'/(s)eries$/i'                                                     => '\\1eries',
				'/([^aeiouy]|qu)ies$/i'                                            => '\\1y',
				'/([lr])ves$/i'                                                    => '\\1f',
				'/(tive)s$/i'                                                      => '\\1',
				'/(hive)s$/i'                                                      => '\\1',
				'/([^f])ves$/i'                                                    => '\\1fe',
				'/(^analy)ses$/i'                                                  => '\\1sis',
				'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
				'/([ti])a$/i'                                                      => '\\1um',
				'/(n)ews$/i'                                                       => '\\1ews',
				'/s$/i'                                                            => ''
			];
		}

		$irregular = [
			'person' => 'people',
			'man'    => 'men',
			'child'  => 'children',
			'sex'    => 'sexes',
			'move'   => 'moves'
		];

		$ignore = [
			'equipment',
			'information',
			'rice',
			'money',
			'species',
			'series',
			'fish',
			'sheep',
			'press',
			'sms',
		];

		$lower_word = strtolower($word);
		foreach ($ignore as $ignore_word) {
			if (substr($lower_word, (- 1 * strlen($ignore_word))) == $ignore_word) {
				return $word;
			}
		}

		foreach ($irregular as $singular_word => $plural_word) {
			if (preg_match('/(' . $plural_word . ')$/i', $word, $arr)) {
				return preg_replace('/(' . $plural_word . ')$/i', substr($arr[0], 0, 1) . substr($singular_word, 1), $word);
			}
		}

		foreach ($singular as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}

		return $word;
	}

    /**
     * Capitalize word
     * @param string $word word to capitalize
     * @return string
     */
	public static function capitalize($word)
	{
		$word[0] = mb_strtoupper($word[0]);
		return $word;
	}

    /**
     * UTF-8 lowercase
     *
     * @param string $word word to lowercase
     * @return string
     */
	public static function lowercase($word)
	{
		return mb_strtolower($word, 'UTF-8');
	}

    /**
     * UTF-8 uppercase
     *
     * @param string $word word to uppercase
     * @return string
     */
	public static function uppercase($word)
	{
		return mb_strtoupper($word, 'UTF-8');
	}

    /**
     * UTF-8 strlen
     *
     * @param string $word word/text
     * @return string
     */
	public static function length($word)
	{
		return mb_strlen($word, 'UTF-8');
	}

    /**
     * UTF-8 strpos
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return int
     */
	public static function position($haystack, $needle, $offset = null)
	{
		return mb_strpos($haystack, $needle, $offset, 'UTF-8');
	}

    /**
     * UTF-8 substr
     *
     * @param string $string
     * @param int $start
     * @param int $length
     * @return string
     */
	public static function sub_string($string, $start, $length = null)
	{
		if ($length === null) {
			$length = static::length($string);
		}

		return mb_substr($string, $start, $length, 'UTF-8');
	}
}