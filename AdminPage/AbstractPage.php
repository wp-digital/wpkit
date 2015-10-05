<?php

/**
 * Abstract class for WordPress admin pages
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\AdminPage;

use WPKit\Helpers\GlobalStorage;
use WPKit\Exception\WpException;

abstract class AbstractPage
{
    protected $_key = null;
    protected $_title = null;
    protected $_parent = null;

    protected $_menu_icon = null;
    protected $_menu_position = null;

    public function __construct($key, $title, $parent = null)
    {
        $this->_title = $title;
        $this->_key = $this->_get_unique_key($key);
        $this->_parent = $parent;

        add_action('admin_menu', function() {
            $this->_add_action_admin_menu();
        });
    }

    protected function _get_unique_key($key)
    {
        $key = sanitize_key($key);
        $keys = (array) GlobalStorage::get('admin_page', 'keys');
        if(in_array($key, $keys)) {
            throw new WpException("Admin page \"{$this->_title}\" has non unique key");
        }
        array_push($keys, $key);
        GlobalStorage::set('admin_page', $keys, 'keys');
        return $key;
    }

    public function __toString()
    {
        return (string) $this->_key;
    }

    /**
     * Get page slug
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Set position in admin menu
     *
     * @param int $position
     */
    public function set_menu_position($position)
    {
        $this->_menu_position = $position;
    }

    /**
     * Get position in admin menu
     *
     * @return int
     */
    public function get_menu_position()
    {
        return $this->_menu_position;
    }

    /**
     * Set menu item image url, or svg/base64 code, or dashicons name (see https://developer.wordpress.org/resource/dashicons/)
     *
     * @param string $icon
     */
    public function set_menu_icon($icon)
    {
        $this->_menu_icon = $icon;
    }

    /**
     * Get menu item image
     *
     * @return string
     */
    public function get_menu_icon()
    {
        return $this->_menu_icon;
    }

    protected function _add_action_admin_menu()
    {
        if(is_null($this->_parent)) {
            add_menu_page($this->_title, $this->_title, 'manage_options', $this->_key, [$this, 'render'], $this->_menu_icon, $this->_menu_position);
        }
        else {
            add_submenu_page($this->_parent, $this->_title, $this->_title, 'manage_options', $this->_key, [$this, 'render']);
        }
    }

    /**
     * Get page title
     *
     * @return string
     */
    public function get_title()
    {
        return $this->_title;
    }

    /**
     * Get page url
     *
     * @return string
     */
    public function get_page_url()
    {
	    $s = strpos(admin_url($this->_parent), '?') ? '&' : '?';

	    return admin_url($this->_parent) . $s . 'page=' . $_REQUEST['page'];

    }

    /**
     * Get page content html
     *
     * @return string
     */
    abstract public function render();
}