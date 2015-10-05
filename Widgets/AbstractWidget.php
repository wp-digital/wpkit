<?php

/**
 * Abstract class for WordPress Widgets
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Vadim Markov <vadim@pingbull.no>
 *
 */

namespace WPKit\Widgets;

use WP_Widget;
use WPKit\Exception\WpException;
use WPKit\Fields\AbstractField;
use WPKit\Fields\Factory\FieldFactory;
use WPKit\Fields\Text;
use WPKit\Helpers\Action;
use WPKit\Helpers\GlobalStorage;


abstract class AbstractWidget extends WP_Widget
{
    const KEY_ID = 'id';
    const KEY_NAME = 'name';
    const KEY_WIDGET_OPTIONS = 'widget_options';
    const KEY_CONTROL_OPTIONS = 'control_options';

    protected $_config = [];

    /**
     * @var AbstractField[]
     */
    protected $_fields = [];

    /**
     * Register widget with WordPress.
     */
    function __construct()
    {
        // Validate config and get with formatted values
        $this->_config = $this->_validate_config($this->_get_config());

        $id_base = $this->_get_unique_key(
            $this->_config[self::KEY_ID],
            $this->_config[self::KEY_NAME]
        );

        // load assets
        add_action('admin_print_scripts-widgets.php', function() {
            $this->_enqueue_assets();
        }, 10);

        parent::__construct(
            $id_base, // Base ID
            $this->_config[self::KEY_NAME], // Name
            $this->_config[self::KEY_WIDGET_OPTIONS], // Widget options
            $this->_config[self::KEY_CONTROL_OPTIONS] // Control options
        );
    }

    /**
     * @return string
     */
    protected static function _get_class()
    {
        return get_called_class();
    }

    /**
     * Register current widget in WordPress
     */
    public static function register()
    {
        $class_name = self::_get_class();

        add_action('widgets_init', function() use (&$class_name){
            register_widget($class_name);
        });
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     * $instance Previously saved values from database.
     * @param array
     * @return string|void
     */
    public function form($instance)
    {
        $this->_build_fields();

        foreach($this->_fields as $key => $field) {
            echo '<p>';
            $value = isset($instance[$key]) ? $instance[$key] : '';
            $field->set_value($value);
            echo $field->render();
            echo '</p>';
	        if ( defined('DOING_AJAX') && DOING_AJAX == true ) {
				echo $field->reload_javascript();
			}
        }

    }

    /**
     * Returns config array with widget's info
     *
     * @return mixed
     */
    abstract protected function _get_config();

    /**
     * @throws \WPKit\Exception\WpException
     */
    protected function _validate_config($config)
    {
        if (!is_array($config)) {
            throw new WpException("Widget " . self::getClass() ." has no config");
        }
        if (empty($config[self::KEY_ID])) {
            throw new WpException("Widget " . self::getClass() ." has no ID");
        }
        if (empty($config[self::KEY_NAME])) {
            throw new WpException("Widget " . self::getClass() ." has no Name");
        }
        if (empty($config[self::KEY_WIDGET_OPTIONS]) || !is_array($config[self::KEY_WIDGET_OPTIONS])) {
            $config[self::KEY_WIDGET_OPTIONS] = [];
        }
        if (empty($config[self::KEY_CONTROL_OPTIONS]) || !is_array($config[self::KEY_CONTROL_OPTIONS])) {
            $config[self::KEY_CONTROL_OPTIONS] = [];
        }
        return $config;
    }

    /**
     * @param $key
     * @param $name
     * @return string
     * @throws \WPKit\Exception\WpException
     */
    protected function _get_unique_key($key, $name)
    {
        $key = sanitize_key($key);
        $keys = (array) GlobalStorage::get('widget', 'keys');
        if(in_array($key, $keys)) {
            throw new WpException("Widget \"{$name}\" has non unique key");
        }
        array_push($keys, $key);
        GlobalStorage::set('widget', $keys, 'keys');
        return $key;
    }

    /**
     * Method for adding fields on widget settings (use $this->_add_field())
     */
    abstract protected function _build_fields();

    /**
     * Display widget html
     */
    abstract protected function _render($args, $data);


    public function widget($args, $instance)
    {
        $this->_render($args, $instance);
    }

    /**
     * @param $key
     * @param $title
     * @param null $field
     * @return AbstractField
     * @throws \WPKit\Exception\WpException
     */
    protected function _add_field($key, $title, $field = null)
    {
        $key = sanitize_key($key);

        if ($field == null) {
            $this->_fields[$key] = new Text();
        }
        elseif(is_string($field)) {
            $this->_fields[$key] = FieldFactory::build($field);
        }
        elseif(Action::is_callable($field)) {
            $this->_fields[$key] = Action::execute($field);
            if(!$this->_fields[$key] instanceof AbstractField) {
                throw new WpException("Widget field \"$title\" init function must return a Field.");
            }
        }
        else {
            throw new WpException("Invalid field type.");
        }
        $this->_fields[$key]->set_id($this->get_field_id($key));
        $this->_fields[$key]->set_name($this->get_field_name($key), false);
        $this->_fields[$key]->set_label($title);
        return $this->_fields[$key];
    }


    protected function _enqueue_assets()
    {
        // trololo kostil'
        $fields = $this->_fields;
        $this->_build_fields();


        foreach($this->_fields as $field) {
            $field->enqueue_javascript();
            $field->enqueue_style();
        }

        // trololo kostil'
        $this->_fields = $fields;
    }

}