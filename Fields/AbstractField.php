<?php

/**
 * Abstract WPKit Field class
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Fields\Label\Label;

abstract class AbstractField
{
	protected $_name = null;
	protected $_value = null;
	protected $_type = null;
	protected $_attributes = [];
	protected $_classes = [];
	protected $_id = null;
	protected $_description = null;
	protected $_placeholder = null;

	/**
	 * @var Label
	 */
	protected $_label = null;

    /**
     * Get field label
     *
     * @return Label
     */
	public function label()
    {
		if ( $this->_label == null ) {
			$this->_label = new Label( $this );
		}
		return $this->_label;
	}

    /**
     * Set field name
     *
     * @param string $name field name
     * @param bool $sanitize use sanitize filter
     */
	public function set_name( $name, $sanitize = true )
    {
		$this->_name = $sanitize ? sanitize_key( $name ) : $name;
	}

    /**
     * Get field name
     *
     * @return string field name
     */
	public function get_name()
    {
		return $this->_name;
	}

    /**
     * Set label text
     *
     * @param string $label
     */
	public function set_label( $label )
    {
		$this->label()->set_text( $label );
	}

    /**
     * Get label text
     *
     * @return string
     */
	public function get_label()
    {
		return $this->label()->get_text();
	}

	/**
	 * Set field value
     *
	 * @param mixed $value field value
	 */
	public function set_value( $value )
    {
		$this->_value = $this->apply_filter( $value );
	}

    /**
     * Get field value
     *
     * @return string
     */
	public function get_value()
    {
		return $this->_value;
	}

    /**
     * Set field type
     *
     * @param string $type field type
     */
	public function set_type( $type )
    {
		$this->_type = $type;
	}

    /**
     * Get field type
     *
     * @return string field type
     */
	public function get_type()
    {
		return $this->_type;
	}

    /**
     * Add field CSS class
     * @param string $class CSS class
     */
	public function add_class( $class )
    {
		if ( ! $this->isset_class( $class ) ) {
			$this->_classes[] = $class;
		}
	}

    /**
     * Remove field CSS class
     *
     * @param string $class CSS class
     */
	public function remove_class( $class )
    {
		if ( $this->isset_class( $class ) ) {
			unset( $this->_classes[ array_search( $class, $this->_classes ) ] );
		}
	}

    /**
     * Set field CSS classes
     * @param array $classes CSS classes
     */
	public function set_classes( array $classes )
    {
		$this->_classes = $classes;
	}

    /**
     * Get field CSS classes
     *
     * @return array CSS classes
     */
	public function get_classes()
    {
		return $this->_classes;
	}

    /**
     * Check is field has CSS class
     *
     * @param string $class CSS class
     * @return bool
     */
    public function isset_class( $class )
    {
		return in_array( $class, $this->_classes );
	}

    /**
     * Set field placeholder
     *
     * @param string $placeholder field placeholder
     */
	public function set_placeholder( $placeholder )
    {
		$this->_placeholder = $placeholder;
	}

    /**
     * Get field placeholder
     *
     * @return string placeholder
     */
	public function get_placeholder()
    {
		return $this->_placeholder;
	}

    /**
     * Set field id
     *
     * @param string $id field id
     */
	public function set_id( $id )
    {
		$this->_id = $id;
	}

    /**
     * Get field id
     *
     * @return string field id
     */
	public function get_id()
    {
		if ( $this->_id == null ) {
			$this->_id = sanitize_key( $this->_name );
		}
		return $this->_id;
	}

    /**
     * Set field attribute
     *
     * @param string $name attribute name
     * @param string $value attribute value
     */
	public function set_attribute( $name, $value )
    {
		if ( $value === '' || $value === null ) {
			$this->remove_attribute( $name );
		} else {
			$this->_attributes[ $name ] = $value;
		}
	}

    /**
     * Remove field attribute
     *
     * @param string $name attribute name
     */
	public function remove_attribute( $name )
    {
		if ( array_key_exists( $name, $this->_attributes ) ) {
			unset( $this->_attributes[ $name ] );
		}
	}

    /**
     * Set "disabled" attribute
     *
     * @param bool $is_disabled is disabled (true or false)
     */
	public function set_disabled( $is_disabled ) {
		if ( $is_disabled ) {
			$this->set_attribute( "disabled", "disabled" );
		} else {
			$this->remove_attribute( "disabled" );
		}
	}

    /**
     * Check is field is disabled
     *
     * @return bool
     */
	public function is_disabled()
	{
		return isset( $this->_attributes['disabled'] );
	}

    /**
     * Render full field html (with label)
     *
     * @return string
     */
	public function render()
    {
		$label  = $this->render_label();
		$output = '<div class="form-group" style="margin-bottom: 15px">';
		$output .= $label ? $label . '<br/>' : '';
		$output .= $this->render_field();
		$output .= '</div>';
		return $output;
	}

    /**
     * Render only field html
     *
     * @return string
     */
	public function render_field()
    {
		return sprintf(
			'<input type="%s" name="%s" id="%s" class="%s" value="%s" placeholder="%s" %s />%s',
			$this->get_type(),
			$this->get_name(),
			$this->get_id(),
			$this->_get_classes(),
			$this->get_value(),
			$this->get_placeholder(),
			$this->_get_attributes(),
			$this->_get_description()
		);
	}

    /**
     * Render only label html
     *
     * @return string
     */
	public function render_label()
    {
		if ( $this->get_label() ) {
			return sprintf( '<label for="%s" style="font-weight: bold">%s</label>', $this->get_id(), $this->get_label() );
		}
		return '';
	}

    /**
     * Get field html
     *
     * @return string
     */
	public function __toString()
    {
		return $this->render();
	}

	protected function _get_attributes()
    {
		$output = "";
		foreach ( $this->_attributes as $key => $value ) {
			$output .= "$key=\"$value\" ";
		}
		return $output;
	}

	protected function _get_classes()
    {
		return implode( ' ', $this->_classes );
	}

	protected function _get_description()
    {
		return empty( $this->_description ) ? '' : "<p class=\"description\">{$this->_description}</p>";
	}

    /**
     * Filtering field value
     *
     * @param string $value
     * @return string
     */
	public function apply_filter( $value )
    {
		return $value;
	}

    /**
     * Set field description
     *
     * @param string $description field description
     */
	public function set_description( $description )
    {
		$this->_description = $description;
	}

    /**
     * Get field description
     *
     * @return string
     */
	public function get_description()
    {
		return $this->_description;
	}

    /**
     * wp_enqueue_script action
     */
	public function enqueue_javascript()
    {
		//wp_enqueue_script
	}

    /**
     * wp_enqueue_style action
     */
	public function enqueue_style()
    {
		//wp_enqueue_style
	}

    /**
     * Get field reload javascript
     *
     * @return string
     */
	public function reload_javascript()
    {
		return '';
	}

}