<?php

/**
 * Abstract class for theme module initialization
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

abstract class AbstractThemeInitialization extends AbstractInitialization
{
    /**
     * Example method for setting image sizes
     */
    abstract public function register_image_sizes();

    /**
     * Example method for init nav menus
     */
    abstract public function register_nav_menus();

    /**
     * Example method for init sidebars
     */
    abstract public function register_dynamic_sidebars();

}