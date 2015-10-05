<?php

/**
 * Meta box with fields for users
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Vitaly Nikolaev <vitaly@pingbull.no
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\User;

use WPKit\Exception\WpException;
use WPKit\Fields\AbstractField;
use WPKit\Fields\Factory\FieldFactory;
use WPKit\Fields\Text;
use WPKit\Helpers\Action;
use WPKit\Helpers\GlobalStorage;


class UserMetaBox
{
	/**
	 * @var AbstractField[]
	 */
	protected $_fields = [];

	/**
	 * @var array
	 */
	protected $_fields_init = [];

	/**
	 * @var string
	 */
	protected $_key = 'wpkit';

	/**
	 * @var null
	 */
	protected $_title = null;

	/**
     * Create users meta box
     *
	 * @param string $title
	 * @param string $key It is not mandatory for backward compatibility
	 *
	 * @throws WpException
	 */
	public function __construct( $title = null, $key = null )
	{
		$this->_title = $title;

		if ( $key ) {
			$this->_key = $this->_get_unique_key( $key );
		}
		$user_profile = function ( $user ) {
			$this->_render( $user );
		};

		add_action( 'show_user_profile', $user_profile );
		add_action( 'edit_user_profile', $user_profile );

		$save_profile = function ( $user_id ) {
			$this->_save( $user_id );
		};

		add_action( 'personal_options_update', $save_profile );
		add_action( 'edit_user_profile_update', $save_profile );


		$enqueue_scripts_function = function () {
			$this->_enqueue_javascript();
		};

		add_action( 'admin_print_scripts-profile.php', $enqueue_scripts_function, 10 );
		add_action( 'admin_print_scripts-user-edit.php', $enqueue_scripts_function, 10 );

		$enqueue_styles_function = function () {
			$this->_enqueue_style();
		};

		add_action( 'admin_print_styles-profile.php', $enqueue_styles_function, 10 );
		add_action( 'admin_print_styles-user-edit.php', $enqueue_styles_function, 10 );
	}

	/**
	 * @return null|string
	 */
	public function get_key()
	{
		return $this->_key;
	}

	/**
	 * @param      $key
	 * @param      $title
	 * @param null $field
	 */
	public function add_field( $key, $title, $field = null )
	{
		$key = $this->_get_prefix() . sanitize_key( $key );
		$this->_fields_init[$key] = [$key, $title, $field];
	}

	/**
	 * For backward compatibility
	 *
	 * @return string
	 */
	protected function _get_prefix()
	{
		$key = $this->get_key();

		if ( $key != 'wpkit' ) {
			return $key . '_';
		}

		return '';
	}

	/**
	 * @param $key
	 *
	 * @return string
	 * @throws WpException
	 */
	protected function _get_unique_key( $key )
	{
		$key = sanitize_key( $key );
		$keys = (array) GlobalStorage::get( 'user_meta_box', 'keys' );

		if ( in_array( $key, $keys ) ) {
			throw new WpException( "User meta box \"{$this->_title}\" has non unique key" );
		}
		array_push( $keys, $key );
		GlobalStorage::set( 'user_meta_box', $keys, 'keys' );

		return $key;
	}

	/**
	 * @return array
	 * @throws WpException
	 */
	protected function _get_fields()
	{
		if ( $this->_fields == null || count( $this->_fields ) < count( $this->_fields_init ) ) {
			foreach ( $this->_fields_init as $_key => $field_init ) {
				if ( array_key_exists( $_key, $this->_fields ) ) {
					continue;
				}
				list( $key, $title, $field ) = $field_init;

				if ( $field == null ) {
					$this->_fields[$key] = new Text();
				} elseif ( is_string( $field ) ) {
					$this->_fields[$key] = FieldFactory::build( $field );
				} elseif ( Action::is_callable( $field ) ) {
					$this->_fields[$key] = Action::execute( $field );

					if ( !$this->_fields[$key] instanceof AbstractField ) {
						throw new WpException( "Option \"$title\" init function must return a Field." );
					}
				} else {
					throw new WpException( 'Invalid field type.' );
				}
				$this->_fields[$key]->set_name( $key );
				$this->_fields[$key]->set_label( $title );
			}
		}

		return $this->_fields;
	}

	/**
	 * @param $user
	 */
	protected function _render( $user )
	{
		wp_nonce_field( $this->get_key() . '_inner_custom_box', $this->get_key() . '_inner_custom_box_nonce' );
		echo $this->_render_title() . '<table class="form-table">';

		foreach ( $this->_get_fields() as $field ) {
			$field->set_value( get_user_meta( $user->ID, $field->get_name(), true ) );
			echo $this->_render_row( $field );
		}
		echo '</table>';
	}

	/**
	 * @return string
	 */
	protected function _render_title()
	{
		$title = $this->_title;

		if ( !empty( $title ) ) {
			return sprintf( '<h3>%s</h3>', $title );
		}

		return '';
	}

	/**
	 * @param AbstractField $field
	 *
	 * @return string
	 */
	protected function _render_row( AbstractField $field )
	{
		return sprintf(
			'<tr>%s%s</tr>',
			$this->_render_label( $field ),
			$this->_render_field( $field )
		);
	}

	/**
	 * @param AbstractField $field
	 *
	 * @return string
	 */
	protected function _render_label( AbstractField $field )
	{
		return sprintf(
			'<th>%s</th>',
			$field->render_label()
		);
	}

	/**
	 * @param AbstractField $field
	 *
	 * @return string
	 */
	protected function _render_field( AbstractField $field )
	{
		$css_classes = $field->get_classes();

		if ( $field instanceof Text && in_array( 'large-text', $css_classes ) ) {
			$key = array_search( 'large-text', $css_classes );
			$css_classes[$key] = 'regular-text';
			$field->set_classes( $css_classes );
		}

		return sprintf(
			'<td>%s</td>',
			$field->render_field()
		);
	}

	/**
	 * @param $user_id
	 *
	 * @return int|string
	 */
	protected function _save( $user_id )
	{
		if ( !$this->_is_able_to_save( $user_id ) ) {
			return $user_id;
		}

		foreach ( $this->_get_fields() as $field ) {
			$name = $field->get_name();

			if ( isset( $_POST[$name] ) ) {
				$field->set_value( $_POST[$name] );

				update_user_meta( $user_id, $name, $field->get_value() );
			} elseif ( !$field->is_disabled() ) {
				delete_user_meta( $user_id, $name );
			}
		}

		return $user_id;
	}

	/**
	 * @param $user_id
	 *
	 * @return bool
	 */
	protected function _is_able_to_save( $user_id )
	{
		if ( !isset( $_POST[$this->get_key() . '_inner_custom_box_nonce'] ) ) {
			return false;
		}
		$nonce = $_POST[$this->get_key() . '_inner_custom_box_nonce'];

		if ( !wp_verify_nonce( $nonce, $this->get_key() . '_inner_custom_box' ) ) {
			return false;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		return true;
	}

	protected function _enqueue_javascript()
	{
		if ( is_admin() ) {
			foreach ( $this->_get_fields() as $field ) {
				$field->enqueue_javascript();
			}
		}
	}

	protected function _enqueue_style()
	{
		if ( is_admin() ) {
			foreach ( $this->_get_fields() as $field ) {
				$field->enqueue_style();
			}
		}
	}

	/**
	 * With backward compatibility
	 *
	 * @param      $user_id
	 * @param      $field_key
	 * @param null $meta_box_key
	 *
	 * @return mixed
	 */
	public static function get( $user_id, $field_key, $meta_box_key = null )
	{
		$key = sanitize_key( $field_key );

		if ( !empty( $meta_box_key ) ) {
			$key = sanitize_key( $meta_box_key ) . '_' . $key;
		}

		return get_user_meta( $user_id, $key, true );
	}
}