<?php

/**
 * Taxonomy custom field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Taxonomy;

use WPKit\Fields\Text;
use WPKit\Fields\AbstractField;
use WPKit\Fields\Factory\FieldFactory;
use WPKit\Helpers\Action;
use WPKit\Exception\WpException;
use WPKit\Helpers\GlobalStorage;

class TaxonomyField
{
    protected $_key = null;
    protected $_title = null;
    protected $_taxonomy = null;

    /**
     * @var AbstractField
     */
    protected $_field = null;
    protected $_field_init = null;

    /**
     * Create taxonomy custom field
     *
     * @param string|Taxonomy $taxonomy
     * @param string $key
     * @param string $title
     * @param string|AbstractField|callable $field
     * @throws WpException
     */
    public function __construct($taxonomy, $key, $title, $field = null)
    {
        $this->_key = $this->_get_unique_key($key);
        $this->_title = $title;
        $this->_field_init = $field;

        if($taxonomy instanceof Taxonomy) {
            $taxonomy = $taxonomy->get_key();
        }

        $this->_taxonomy = $taxonomy;

        $this->_register_actions();
    }


    protected function _register_actions()
    {
        // render
        add_action("{$this->_taxonomy}_add_form_fields", function($tag) {
            echo $this->render_new();
        });

        add_action("{$this->_taxonomy}_edit_form_fields", function($tag) {
            echo $this->render_edit($tag->term_id);
        });

        // save
        $save_custom_fields_function = function($term_id) {
            $this->_save($term_id);
        };

        add_action("create_{$this->_taxonomy}", $save_custom_fields_function);
        add_action("edited_{$this->_taxonomy}", $save_custom_fields_function);

        // load assets
        add_action('admin_print_scripts-edit-tags.php', function() {
            $this->get_field()->enqueue_javascript();
        }, 10);

        add_action('admin_print_styles-edit-tags.php', function() {
            $this->get_field()->enqueue_style();
            wp_add_inline_style('wp-admin', $this->_style_fix());
        });
    }

    protected function _get_unique_key($key)
    {
        $key = sanitize_key($key);
        $keys = (array) GlobalStorage::get('taxonomy-meta', 'keys');
        if(in_array($key, $keys)) {
            throw new WpException("Taxonomy Field \"{$this->_title}\" has non unique key");
        }
        array_push($keys, $key);
        GlobalStorage::set('taxonomy-meta', $keys, 'keys');
        return $key;
    }

    /**
     * Get taxonomy field value
     *
     * @param int $term_id
     * @param string $key
     * @return mixed
     */
    public static function get($term_id, $key)
    {
        return TaxonomyMeta::get_instance()->get($term_id, sanitize_key($key));
    }

    /**
     * Get taxonomy field key
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Get taxonomy title
     *
     * @return null
     */
    public function get_title()
    {
        return $this->_title;
    }

    /**
     * Get taxonomy field
     *
     * @return AbstractField
     * @throws WpException
     */
    public function get_field()
    {
        if($this->_field == null) {
            if($this->_field_init == null){
                $this->_field = new Text();
            }
            elseif(is_string($this->_field_init)) {
                $this->_field = FieldFactory::build($this->_field_init);

            }
            elseif(Action::is_callable($this->_field_init)) {
                $this->_field = Action::execute($this->_field_init);
                if(! $this->_field instanceof AbstractField) {
                    throw new WpException("Option \"{$this->_title}\" init function must return a Field.");
                }
            }
            else {
                throw new WpException("Invalid field type.");
            }

            $this->_field->set_name($this->_key);
            $this->_field->set_label($this->_title);
        }
        return $this->_field;
    }

    /**
     * Get field html for "edit taxonomy" page
     *
     * @param int $term_id
     * @return string
     * @throws WpException
     */
    public function render_edit($term_id)
    {
        $taxonomy_meta = TaxonomyMeta::get_instance();
        $this->get_field()->set_value( $taxonomy_meta->get($term_id, $this->get_key()) );

        $html = '<tr class="form-field-fixed">';
        $html .= "<th scope=\"row\">{$this->get_field()->render_label()}</th>";
        $html .= "<td>{$this->get_field()->render_field()}</td>";
        $html .= '</tr>';
        return $html;
    }

    /**
     * Get field html for "new taxonomy" page
     *
     * @return string
     * @throws WpException
     */
    public function render_new()
    {
        $html = '<div class="form-field-fixed">';
        $html .= $this->get_field()->render_label();
        $html .= $this->get_field()->render_field();
        $html .= '</div>';
        return $html;
    }

    /**
     * Filtering field value
     *
     * @param $input
     * @return string
     * @throws WpException
     */
    public function filter($input)
    {
        $this->get_field()->set_value($input);
        $input = $this->get_field()->get_value();
        return $input;
    }

    protected function _style_fix()
    {
        return '.form-field-fixed td > *, div.form-field-fixed > * { max-width: 95%; }
                @media only screen and (max-width: 768px) { tr.form-field-fixed td > *, div.form-field-fixed > * { max-width: 99%; } }
                .form-wrap .form-field-fixed { margin: 0 0 10px; padding: 8px 0; }';
    }

    protected function _save($term_id)
    {
        $taxonomy_meta = TaxonomyMeta::get_instance();

        if ( !$this->get_field()->is_disabled() ) {
            $value = isset( $_POST[ $this->get_key() ] ) ? $_POST[ $this->get_key() ] : false;
            $this->get_field()->set_value( $value );
            $value = $this->get_field()->get_value();

            if($value) {
                $taxonomy_meta->update($term_id, $this->get_key(), $value);
            }
            else {
                $taxonomy_meta->delete($term_id, $this->get_key());
            }
        }
    }

}