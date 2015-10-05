<?php

/**
 * Abstract class for theme module functions
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

abstract class AbstractThemeFunctions extends AbstractFunctions
{
    /**
     * Get page id by template file name
     *
     * @param string $template_name
     * @return int
     */
    public static function get_page_id_by_template($template_name)
    {
        if( false === strpos($template_name, '.php') ) {
            $template_name = $template_name . '.php';
        }

        if( false === strpos($template_name, 'templates/') ) {
            $template_name = 'templates/' . $template_name;
        }

        if( false === ($page_id = wp_cache_get($template_name, 'template-page')) ) {
            global $wpdb;
            $page_id = (int) $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value = '%s' AND meta_key = '_wp_page_template' LIMIT 1", $template_name) );
            wp_cache_set($template_name, $page_id, 'template-page');
        }

        return $page_id;
    }

    /**
     * Get page url by template file name
     *
     * @param string $template_name
     * @return string
     */
    public static function get_page_url_by_template($template_name)
    {
        $page_id = static::get_page_id_by_template($template_name);
        return $page_id > 0 ? get_permalink($page_id) : null;
    }

}