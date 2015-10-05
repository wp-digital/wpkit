<?php

/**
 * WordPress meta box builder
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\PostType;

use WPKit\Fields\AbstractField;
use WPKit\Exception\WpException;
use WPKit\Fields\Factory\FieldFactory;
use WPKit\Fields\Text;
use WPKit\Helpers\Action;
use WPKit\Helpers\GlobalStorage;

class MetaBox
{
    protected $_title = null;
    protected $_key = null;
    protected $_post_types = [];
	protected $_context = 'advanced';
	protected $_priority = 'default';

    /**
     * @var AbstractField[]
     */
    protected $_fields = [];
    protected $_fields_init = [];

    /**
     * Create new meta box
     *
     * @param string $key unique key
     * @param string $title box title
     * @throws WpException
     */
    public function __construct($key, $title)
    {
        $this->_title = $title;
        $this->_key = $this->_get_unique_key($key);

        add_action('add_meta_boxes', function() {
            foreach($this->_post_types as $post_type) {
                add_meta_box(
                    $this->get_key(),
                    $this->_title,
                    function($post) {
                        $this->_render($post);
                    },
                    $post_type,
                    $this->_context,
                    $this->_priority
                );
            }
        });

        add_action('save_post', function($post_id) {
            $this->_save($post_id);
        });


        // load all scripts
        $enqueue_scripts_function = function() {
            $this->_enqueue_javascript();
        };

        add_action('admin_print_scripts-post.php', $enqueue_scripts_function, 10);
        add_action('admin_print_scripts-post-new.php', $enqueue_scripts_function, 10);


        // load all styles
        $enqueue_styles_function = function() {
            $this->_enqueue_style();
        };

        add_action('admin_print_styles-post.php', $enqueue_styles_function, 10);
        add_action('admin_print_styles-post-new.php', $enqueue_styles_function, 10);

    }

	/**
	 * Set context for metabox
	 *
	 * @param $context string 'normal', 'advanced', or 'side'
	 */
	public function set_context($context){
		if(in_array($context, ['normal', 'advanced', 'side'])){
			$this->_context = (string) $context;
		}
	}

	/**
	 * Set priority to metabox
	 *
	 * @param $priority string 'high', 'core', 'default' or 'low'
	 */
	public function set_priority( $priority){
		if(in_array($priority, ['high', 'core', 'default', 'low'])){
			$this->_priority = (string) $priority;
		}
	}

    /**
     * Get meta box field value
     *
     * @param int $post_id
     * @param string $meta_box_key unique meta box key
     * @param string $field_key field key
     * @return mixed
     *
     */
    public static function get($post_id, $meta_box_key, $field_key)
    {
        $key = sanitize_key($meta_box_key) . '_' . sanitize_key($field_key);
        return get_post_meta($post_id, $key, true);
    }

    /**
     * Set meta box field value
     *
     * @param int $post_id
     * @param string $meta_box_key
     * @param string $field_key
     * @param mixed $value
     * @param callable|string $field
     * @return bool
     * @throws WpException
     */
    public static function set($post_id, $meta_box_key, $field_key, $value, $field = null)
    {
        $key = sanitize_key($meta_box_key) . '_' . sanitize_key($field_key);

        if($field) {

            $f = null;
            if(is_string($field)) {
                $f = FieldFactory::build($field);
            }
            elseif(Action::is_callable($field)) {
                $f = Action::execute($field);
            }

            if($f instanceof AbstractField) {
                $value = $f->apply_filter($value);
            }
        }

        return update_post_meta($post_id, $key, $value);
    }

    protected function _get_unique_key($key)
    {
        $key = sanitize_key($key);
        $keys = (array) GlobalStorage::get('meta_box', 'keys');
        if(in_array($key, $keys)) {
            throw new WpException("Meta box \"{$this->_title}\" has non unique key");
        }
        array_push($keys, $key);
        GlobalStorage::set('meta_box', $keys, 'keys');
        return $key;
    }

    /**
     * Attach a post type that will be used with this meta box
     *
     * @param string|PostType $post_type
     */
    public function add_post_type($post_type)
    {
        if($post_type instanceof PostType) {
            $post_type = $post_type->get_key();
        }
        if(! in_array($post_type, $this->_post_types)) {
            $this->_post_types[] = $post_type;
        }
    }

    /**
     * Add a field to meta box
     *
     * @param string $key
     * @param string $title
     * @param string|AbstractField|callable $field
     */
    public function add_field($key, $title, $field = null)
    {
        $key = $this->get_key() . '_' . sanitize_key($key);
        $this->_fields_init[$key] = [$key, $title, $field];
    }

    protected function _get_fields()
    {
        if($this->_fields == null || count($this->_fields) < count($this->_fields_init)) {
            foreach($this->_fields_init as $_key => $field_init) {

                if( array_key_exists($_key, $this->_fields) ) {
                    continue;
                }

                list($key, $title, $field) = $field_init;

                if($field == null) {
                    $this->_fields[$key] = new Text();
                }
                elseif(is_string($field)) {
                    $this->_fields[$key] = FieldFactory::build($field);
                }
                elseif(Action::is_callable($field)) {
                    $this->_fields[$key] = Action::execute($field);
                    if(! $this->_fields[$key] instanceof AbstractField) {
                        throw new WpException("Meta box field \"$title\" init function must return a Field.");
                    }
                }
                else {
                    throw new WpException("Invalid field type.");
                }

                $this->_fields[$key]->set_name($key);
                $this->_fields[$key]->set_label($title);
            }
        }
        return $this->_fields;
    }

    protected function _save($post_id)
    {
        if(! $this->_is_able_to_save($post_id)) {
            return $post_id;
        }

        foreach($this->_get_fields() as $field) {
            $value = isset($_POST[$field->get_name()]) ? $_POST[$field->get_name()] : null;
            $field->set_value($value);

            if($field->get_value() !== '' && $field->get_value() !== null) {
                update_post_meta($post_id, $field->get_name(), $field->get_value());
            }
            elseif ( !$field->is_disabled() ) {
                delete_post_meta($post_id, $field->get_name());
            }
        }
        return $post_id;
    }

    protected function _render($post)
    {
        wp_nonce_field($this->get_key() . "_inner_custom_box", $this->get_key() . "_inner_custom_box_nonce");

        foreach($this->_get_fields() as $field) {
            if($post->post_status != 'auto-draft') {
                $field->set_value( get_post_meta($post->ID, $field->get_name(), true) );
            }
            echo $field->render();
        }
    }

    protected function _is_able_to_save($post_id)
    {
        if(! isset( $_POST[$this->get_key() . "_inner_custom_box_nonce"])) {
            return false;
        }

        $nonce = $_POST[$this->get_key() . "_inner_custom_box_nonce"];

        if (! wp_verify_nonce( $nonce, $this->get_key() . "_inner_custom_box")) {
            return false;
        }

        if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
            return false;
        }

        if ('page' == $_POST['post_type']) {
            if (! current_user_can('edit_page', $post_id)) {
                return false;
            }
        }
        else {
            if (! current_user_can('edit_post', $post_id)) {
                return false;
            }
        }

        return true;
    }

    protected function _enqueue_javascript()
    {
        global $post_type;
        if(is_admin() && in_array($post_type, $this->_post_types)) {

            foreach($this->_get_fields() as $field) {
                $field->enqueue_javascript();
            }
        }
    }

    protected function _enqueue_style()
    {
        global $post_type;
        if(is_admin() && in_array($post_type, $this->_post_types)) {

            foreach($this->_get_fields() as $field) {
                $field->enqueue_style();
            }
        }
    }

    /**
     * Get meta box key
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

}