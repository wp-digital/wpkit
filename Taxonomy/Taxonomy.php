<?php

/**
 * WordPress custom taxonomy builder
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

use WPKit\PostType\PostType;
use WPKit\Exception\WpException;
use WPKit\Helpers\Strings;
use WPKit\Fields\AbstractField;

class Taxonomy
{
    protected $_key = null;
    protected $_name = null;
    protected $_post_types = [];
    protected $_hierarchical = true;
    protected $_display_in_table = true;
    protected $_show_ui = true;
	protected $_show_in_nav_menus = true;
	protected $_public = true;
    protected $_capabilities = [];
	protected $_rewrite = [];

    protected $_pluralize = true;
    protected $_custom_labels = [];

    /**
     * @var TaxonomyField[]
     */
    protected $_custom_fields = [];

    /**
     * Create taxonomy
     *
     * @param string $key
     * @param string $singular_name
     * @param array $labels
     * @throws WpException
     */
    public function __construct($key, $singular_name = null, array $labels = [])
    {
        $key = sanitize_key($key);

        if(taxonomy_exists($key)) {
            throw new WpException("Taxonomy \"$key\" already exist.");
        }

        $this->_key = $key;

        if(empty($singular_name)) {
            $singular_name = str_replace(['-', '_'], ' ', $key);
        }

        $this->_name = $singular_name;
        $this->_custom_labels = $labels;

        $this->_rewrite = ['slug' => $this->_key, 'with_front' => false];

        add_action('init', function() {
            $this->_register_taxonomy();
        }, 6);
    }


    /**
     * Attach a post type that will be used with this taxonomy
     *
     * @param $post_type
     */
    public function add_post_type($post_type)
    {
        if($post_type instanceof PostType) {
            $post_type = $post_type->get_key();
        }
        if( ! in_array($post_type, $this->_post_types)) {
            array_push($this->_post_types, $post_type);
        }
    }

    /**
     * Get taxonomy slug
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    protected function _register_taxonomy()
    {
        foreach($this->_post_types as $post_type) {
            if(! post_type_exists($post_type)) {
                throw new WpException("Taxonomy \"{$this->_key}\" can't be attached to undefined post type \"{$post_type}\".");
            }
        }

        register_taxonomy($this->_key, $this->_post_types, [
            'hierarchical'          => $this->_hierarchical,
            'labels'                => $this->_get_labels(),
            'show_ui'               => $this->_show_ui,
            'show_admin_column'     => $this->_display_in_table,
            'query_var'             => true,
			'show_in_nav_menus'     => $this->_show_in_nav_menus,
            'capabilities'          => $this->_capabilities,
            'rewrite'               => $this->get_rewrite(),
	        'public' =>$this->_public
        ]);
    }

    protected function _get_labels()
    {
        $singular_name = Strings::capitalize($this->_name);
        $plural_name = $this->_pluralize ? Strings::pluralize($singular_name) : $singular_name;
        $lowercase_plural_name = Strings::lowercase($plural_name);

        return wp_parse_args($this->_custom_labels, [
            'name'                       => $plural_name,
            'singular_name'              => $singular_name,
            'search_items'               => sprintf(__("Search %s", 'wpkit'), $plural_name),
            'popular_items'              => sprintf(__("Popular %s", 'wpkit'), $plural_name),
            'all_items'                  => sprintf(__("All %s", 'wpkit'), $plural_name),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => sprintf(__("Edit %s", 'wpkit'), $singular_name),
            'update_item'                => sprintf(__("Update %s", 'wpkit'), $singular_name),
            'add_new_item'               => sprintf(__("Add New %s", 'wpkit'), $singular_name),
            'new_item_name'              => sprintf(__("New %s Name", 'wpkit'), $singular_name),
            'separate_items_with_commas' => sprintf(__("Separate %s with commas", 'wpkit'), $lowercase_plural_name),
            'add_or_remove_items'        => sprintf(__("Add or remove %s", 'wpkit'), $lowercase_plural_name),
            'choose_from_most_used'      => sprintf(__("Choose from the most used %s", 'wpkit'), $lowercase_plural_name),
            'not_found'                  => sprintf(__("No %s found", 'wpkit'), $lowercase_plural_name),
            'menu_name'                  => $plural_name,
        ]);
    }

    /**
     * Get taxonomy rewrite options
     *
     * @return array|bool
     */
	public function get_rewrite()
	{
		return $this->_rewrite;
	}

	/**
     * Set taxonomy rewrite options
     *
	 * @param $rewrite bool|array
	 */
	public function set_rewrite($rewrite)
	{
		$this->_rewrite = $rewrite;
	}

    /**
     * Set taxonomy labels pluralize
     *
     * @param $pluralize
     */
    public function set_pluralize($pluralize)
    {
        $this->_pluralize = (bool) $pluralize;
    }

    /**
     * Get taxonomy labels pluralize
     *
     * @return bool
     */
    public function is_pluralize()
    {
        return $this->_pluralize;
    }

    public function is_hierarchical()
    {
        return $this->_hierarchical;
    }

    public function set_hierarchical($is_hierarchical)
    {
        $this->_hierarchical = (bool) $is_hierarchical;
    }

    public function is_show_ui()
    {
        return $this->_show_ui;
    }

    public function set_show_ui($is_show_ui)
    {
        $this->_show_ui = (bool) $is_show_ui;
    }

	public function set_public($is_public)
	{
		$this->_public = (bool) $is_public;
	}
    
	public function is_show_in_nav_menus()
	{
		return $this->_show_in_nav_menus;
	}

    public function set_show_in_nav_menus($is_show_in_nav_menus)
    {
	    $this->_show_in_nav_menus = (bool) $is_show_in_nav_menus;
    }

    public function set_display_in_table($is_display)
    {
        $this->_display_in_table = (bool) $is_display;
    }

    public function is_display_in_table()
    {
        return $this->_display_in_table;
    }

    /**
     * Add a field to taxonomy
     *
     * @param string $key
     * @param string $title
     * @param string|AbstractField|callable $field
     */
    public function add_custom_field($key, $title, $field = null)
    {
        $taxonomy_field = new TaxonomyField($this, $key, $title, $field);
        $this->_custom_fields[ $taxonomy_field->get_key() ] = $taxonomy_field;
    }

    public static function get_custom_field_value($term_id, $key)
    {
        return TaxonomyField::get( $term_id, $key );
    }

    public function get_capabilities()
    {
        return $this->_capabilities;
    }

    public function set_capabilities($capabilities)
    {
        $this->_capabilities = (array) $capabilities;
    }

}