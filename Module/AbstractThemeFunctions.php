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
            $the_query = new \WP_Query( [
                'posts_per_page' => 1,
                'post_type'      => 'page',
                'fields'         => 'ids',
                'meta_query'     => [
                    [
                        'key'   => '_wp_page_template',
                        'value' => $template_name,
                    ],
                ],
            ] );
            $page_id = $the_query->have_posts() ? $the_query->get_posts()[0] : 0;
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