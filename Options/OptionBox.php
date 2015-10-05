<?php

/**
 * WordPress settings box
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Options;

use WPKit\Exception\WpException;
use WPKit\Helpers\String;
use WPKit\Helpers\GlobalStorage;
use WPKit\Fields\AbstractField;

class OptionBox
{
    const PAGE_GENERAL = 'general';
    const PAGE_READING = 'reading';
    const PAGE_WRITING = 'writing';
    const PAGE_DISCUSSION = 'discussion';
    const PAGE_MEDIA = 'media';
    const PAGE_PERMALINK = 'permalink';

    protected $_key = null;
    protected $_title = null;
    protected $_page = null;

    /**
     * @var Option[]
     */
    protected $_options = [];

    /**
     * Create setting box with options
     *
     * @param string $key
     * @param string $title
     * @throws WpException
     */
    public function __construct($key, $title)
    {
        $this->_key = $this->_get_unique_key($key);
        $this->_title = $title;

        add_action('admin_init', function() {
            $this->_admin_init();
        });
    }

    protected function _get_unique_key($key)
    {
        $key = sanitize_key($key);
        $keys = (array) GlobalStorage::get('option_box', 'keys');
        if(in_array($key, $keys)) {
            throw new WpException("Option box \"{$this->_title}\" has non unique key");
        }
        array_push($keys, $key);
        GlobalStorage::set('option_box', $keys, 'keys');
        return $key;
    }

    /**
     * Get admin page that will be used with this options box
     *
     * @return string
     */
    public function get_page()
    {
        return $this->_page;
    }

    /**
     * Set admin page that will be used with this options box
     *
     * @param string $page
     */
    public function set_page($page)
    {
        $this->_page = (string) $page;
    }

    /**
     * Get option key
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Add option to box
     *
     * @param Option $option
     * @return bool|string
     * @deprecated use add_field()
     */
    public function add_option(Option $option)
    {
        if(!isset($this->_options[$option->get_key()])) {
            $this->_options[$option->get_key()] = $option;
            return $option->get_key();
        }
        return false;
    }

    /**
     * Remove option from box
     *
     * @param string $slug returned by add_option
     */
    public function remove_option($slug)
    {
        if(isset($this->_options[$slug])) {
            unset($this->_options[$slug]);
        }
    }

    /**
     * Add field to options box
     *
     * @param string $key
     * @param string $title
     * @param string|AbstractField|callable $field
     */
    public function add_field($key, $title, $field = null)
    {
        $this->add_option( new Option($key, $title, $field) );
    }

    /**
     * Get option value
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return Option::get($key);
    }

    protected function _admin_init()
    {
        $this->_enqueue_javascript();
        add_settings_section(
            $this->_key,
            $this->_title,
            null,
            $this->_page
        );

        foreach($this->_options as $option)
        {
            register_setting($this->_page, $option->get_key(), [$option, 'filter']);
            add_settings_field(
                $option->get_key(),
                $option->get_label(),
                [$option, 'render'],
                $this->_page,
                $this->_key
            );
        }
    }

    protected function _enqueue_javascript()
    {
        if(is_admin() && ($this->_is_default_page() || isset($_REQUEST['page']) && $_REQUEST['page'] == $this->_page)) {
            foreach($this->_options as $option) {
                $option->get_field()->enqueue_javascript();
                $option->get_field()->enqueue_style();
            }
        }
    }

    protected function _is_default_page()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        if(in_array($this->_page, $reflection->getConstants()) && String::position($_SERVER['REQUEST_URI'], "options-{$this->_page}.php") !== false) {
            return true;
        }
        return false;
    }

}
