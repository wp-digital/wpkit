<?php

/**
 * JavaScript helper
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

class Script
{
    /**
     * Enqueue inline script for admin panel
     *
     * @param string $handle unique key
     * @param string $source JavaScript source
     */
    public static function enqueue_admin_inline_script($handle, $source)
    {
        wp_enqueue_script($handle, 'http://', [], false, true);
        $source = preg_replace(['#<script(.*?)>#is', '#</script>#is'], '', $source);
        add_action('admin_footer', function() use($handle, $source) {
            global $wp_scripts;
            $wp_scripts->add_data($handle, 'data', $source);
        });
    }
}