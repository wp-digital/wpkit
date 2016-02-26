<?php

/**
 * WordPress custom post type builder
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

use WPKit\Helpers\Strings;
use WPKit\Exception\WpException;
use WPKit\Taxonomy\Taxonomy;

class PostType
{
	protected $_key = null;
	protected $_name = null;
	protected $_supports = ['title', 'editor', 'thumbnail'];
	protected $_menu_position = 5;
	protected $_has_archive = false;
	protected $_hierarchical = true;
	protected $_rewrite = [];
	protected $_public = true;
	protected $_type = 'post';
	protected $_icon = null;
	protected $_show_in_nav_menus = true;
    protected $_show_in_menu = null;
	protected $_publicly_queryable = true;
	protected $_exclude_from_search = false;
	protected $_capabilities = [];
	protected $_taxonomies = [];
	protected $_show_ui = true;

	protected $_pluralize = true;
	protected $_custom_labels = [];

	protected $_show_thumb = true;

	/**
	 * @var PostTypeColumn[]
	 */
	protected $_columns = [];

    /**
     * Create custom post type
     *
     * @param string $key post type slug
     * @param string $singular_name post type name
     * @param array $labels associative array with labels, see https://codex.wordpress.org/Function_Reference/register_post_type#Arguments
     * @throws WpException
     */
	public function __construct($key, $singular_name, array $labels = [])
	{
        $key = sanitize_key($key);

		if (post_type_exists($key)) {
			throw new WpException("Custom post type \"$key\" already exist.");
		}

		$this->_key = $key;
		$this->_name = $singular_name;
		$this->_custom_labels = $labels;

		add_action(
			'init',
			function () {
				$this->_register_post_type();
			},
			5
		);
	}

    /**
     * Attach a registered taxonomy that will be used with this post type
     *
     * @param Taxonomy $taxonomy taxonomy object
     */
	public function add_taxonomy(Taxonomy $taxonomy)
	{
		$taxonomy->add_post_type($this);
	}

	protected function _register_post_type()
	{
		$this->_check_post_thumbnails_support();

		register_post_type($this->_key, [
			'labels'              => $this->_get_labels(),
			'public'              => $this->_public,
			'rewrite'             => $this->get_rewrite(),
			'capability_type'     => $this->_type,
			'has_archive'         => $this->_has_archive,
			'hierarchical'        => $this->_hierarchical,
			'menu_position'       => $this->_menu_position,
			'supports'            => $this->_supports,
			'menu_icon'           => $this->_icon,
			'show_in_nav_menus'   => $this->_show_in_nav_menus,
            'show_in_menu'        => $this->_show_in_menu,
			'publicly_queryable'  => $this->_publicly_queryable,
			'exclude_from_search' => $this->_exclude_from_search,
			'capabilities'        => $this->_capabilities,
			'taxonomies'          => $this->_taxonomies,
			'show_ui'             => $this->_show_ui,
		]);
	}


	protected function _get_labels()
	{
		$singular_name           = Strings::capitalize($this->_name);
		$plural_name             = $this->_pluralize ? Strings::pluralize($singular_name) : $singular_name;
		$lowercase_singular_name = Strings::lowercase($singular_name);
		$lowercase_plural_name   = Strings::lowercase($plural_name);

        $default_labels = [
            'name'               => $plural_name,
            'singular_name'      => $singular_name,
            'add_new'            => __("Add New", 'wpkit'),
            'add_new_item'       => sprintf(__("Add New %s", 'wpkit'), $lowercase_singular_name),
            'edit_item'          => sprintf(__("Edit %s", 'wpkit'), $lowercase_singular_name),
            'new_item'           => sprintf(__("New %s", 'wpkit'), $lowercase_singular_name),
            'all_items'          => sprintf(__("All %s", 'wpkit'), $lowercase_plural_name),
            'view_item'          => sprintf(__("View %s", 'wpkit'), $lowercase_singular_name),
            'search_items'       => sprintf(__("Search %s", 'wpkit'), $lowercase_plural_name),
            'not_found'          => sprintf(__("No %s found", 'wpkit'), $lowercase_plural_name),
            'not_found_in_trash' => sprintf(__("No %s found in Trash", 'wpkit'), $lowercase_plural_name),
            'parent_item_colon'  => null,
            'menu_name'          => $plural_name,
        ];

		return wp_parse_args($this->_custom_labels, $default_labels);
	}

    /**
     * Get post type slug
     *
     * @return string
     */
	public function get_key()
	{
		return $this->_key;
	}

    /**
     * Get post type supports
     *
     * @return array
     */
	public function get_supports()
	{
		return $this->_supports;
	}

    /**
     * Get post type rewrite
     *
     * @return array|bool
     */
	public function get_rewrite()
	{
		if ($this->_rewrite === false) {
			return false;
		}

		return array_merge(['slug' => $this->_key, 'with_front' => false], $this->_rewrite);
	}

    /**
     * Set post type rewrite
     *
     * @param array|bool $rewrite
     */
	public function set_rewrite($rewrite)
	{
		$this->_rewrite = $rewrite;
	}

	/**
     * Set post type ui visibility
     *
	 * @param bool $show_ui
	 */
	public function set_show_ui($show_ui)
    {
		$this->_show_ui = (bool) $show_ui;
	}

    /**
     * Set post type supports
     *
     * @param array $supports
     */
	public function set_supports(array $supports)
	{
		$this->_supports = $supports;
	}

    /**
     * Add post type supports
     *
     * @param string $support
     */
	public function add_support($support)
	{
		if (is_array($support)) {
			$this->_supports = array_merge($this->_supports, $support);
		} elseif ( !in_array($support, $this->_supports)) {
			$this->_supports[] = $support;
		}
	}

    /**
     * Get post type menu position
     *
     * @return int
     */
	public function get_menu_position()
	{
		return $this->_menu_position;
	}

    /**
     * Set post type menu position
     *
     * @param int $position
     */
	public function set_menu_position($position)
	{
		$this->_menu_position = (int) $position;
	}

	/**
	 * Get is post type public
	 *
	 * @return bool
	 */
	public function is_public()
	{
		return $this->_public;
	}

	/**
	 * Set post type public
	 *
	 * @param bool $is_public
	 */
	public function set_public($is_public)
	{
		$this->_public = $is_public;
	}

    /**
     * Get is post type has archive
     *
     * @return bool
     */
	public function is_use_archive()
	{
		return $this->_has_archive;
	}

	/**
	 * Set is post type has archive
	 *
	 * @param bool|string $is_use_archive
	 */
	public function set_use_archive($is_use_archive)
	{
		$this->_has_archive = $is_use_archive;
	}

    /**
     * Get is post type is hierarchical
     *
     * @return bool
     */
	public function is_hierarchical()
	{
		return $this->_hierarchical;
	}

    /**
     * Set is post type is hierarchical
     *
     * @param bool $is_hierarchical
     */
	public function set_hierarchical($is_hierarchical)
	{
		$this->_hierarchical = (bool) $is_hierarchical;
		if($this->_hierarchical) {
			$this->add_support('page-attributes');
		}
	}

    /**
     * Get is post type is visible in nav menu
     *
     * @return bool
     */
	public function is_show_in_nav_menus()
	{
		return $this->_show_in_nav_menus;
	}

    /**
     * Set is post type is visible in nav menu
     *
     * @param bool $is_show_in_nav_menus
     */
	public function set_show_in_nav_menus($is_show_in_nav_menus)
	{
		$this->_show_in_nav_menus = (bool) $is_show_in_nav_menus;
	}

    public function set_show_in_menu($is_show_in_menu)
    {
        $this->_show_in_menu = (bool) $is_show_in_menu;
    }

	public function set_publicly_queryable($is_publicly_queryable)
	{
		$this->_publicly_queryable = (bool) $is_publicly_queryable;
	}

	public function is_publicly_queryable()
	{
		return $this->_publicly_queryable;
	}

	public function set_exclude_from_search($is_exclude_from_search)
	{
		$this->_exclude_from_search = (bool) $is_exclude_from_search;
	}

	public function is_exclude_from_search()
	{
		return $this->_exclude_from_search;
	}

	public function get_capabilities()
	{
		return $this->_capabilities;
	}

	public function set_capabilities($capabilities)
	{
		$this->_capabilities = (array) $capabilities;
	}

	public function get_taxonomies()
	{
		return $this->_taxonomies;
	}

	public function set_taxonomies($taxonomies)
	{
		$this->_taxonomies = (array) $taxonomies;
	}

	public function set_capability_type_page()
	{
		$this->_type = 'page';
	}

	public function set_capability_type_post()
	{
		$this->_type = 'post';
	}

    /**
     * Set image url, or svg/base64 code, or dashicons name (see https://developer.wordpress.org/resource/dashicons/)
     *
     * @param string $icon
     */
	public function set_menu_icon($icon)
	{
		$this->_icon = $icon;
	}

    /**
     * Get menu icon image
     *
     * @return string
     */
	public function get_menu_icon()
	{
		return $this->_icon;
	}

	private function _check_post_thumbnails_support()
	{
		if (in_array('thumbnail', $this->_supports)) {
			global $_wp_theme_features;
			$post_types = isset($_wp_theme_features['post-thumbnails']) ? $_wp_theme_features['post-thumbnails'] : false;
			if ($post_types !== true) {
				$post_types = is_array($post_types) ? array_merge($post_types[0], [$this->_key]) : [$this->_key];
				add_theme_support('post-thumbnails', $post_types);
			}
		}
	}

    /**
     * Set post type labels pluralize
     *
     * @param bool $pluralize
     */
	public function set_pluralize($pluralize)
	{
		$this->_pluralize = (bool) $pluralize;
	}

    /**
     * Get post type labels pluralize
     *
     * @return bool
     */
	public function is_pluralize()
	{
		return $this->_pluralize;
	}

    /**
     * Add column to post type grid view
     *
     * @param string $title column title
     * @param callable $function display function
     * @param bool $sortable is sortable
     * @param int $position column position
     */
	public function add_column($title, $function, $sortable = false, $position = - 1)
	{
		$key = is_array($title) ? $title[0] : sanitize_key($title);
		$this->_columns[ $key ] = new PostTypeColumn($this, $title, $function, $sortable, $position);
	}

    /**
     * Add thumbnail column to post type grid view
     *
     * @param string $title column title
     * @param int $position column position
     */
	public function add_column_thumbnail($title = 'Image', $position = 1)
	{
		if ( !in_array('thumbnail', $this->_supports)) {
			return;
		}

		$title = is_array($title) ? $title[1] : $title;

		$function = function ($column) {
			global $post;
			$img_id = get_post_thumbnail_id($post->ID);
			if ($img_id) {
				echo wp_get_attachment_image($img_id, [74, 60], true);
			}
		};

		$this->add_column(['posts', $title], $function, false, $position);
	}

    /**
     * Attach a meta box that will be used with this post type
     *
     * @param MetaBox $meta_box meta box object
     */
	public function add_meta_box(MetaBox $meta_box)
	{
		$meta_box->add_post_type($this);
	}

}