<?php

/**
 * Button field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2018, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Vitaly Nikolaiev <trilliput@gmail.com>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class Button extends AbstractField {
	protected $_type = 'button';
	protected $_classes = [ 'button' ];
	protected $_text = '';
	protected $_action = '';

	public function render_field() {
		return sprintf(
			'<span class="spinner"></span><button type="%s" data-action="%s" id="%s" class="wpkit-button %s" %s>%s</button><div class="dashicons-before dashicons-yes" style="display:none;color:#00AA00;float:right;margin-right:-30px"><br></div>%s',
			$this->get_type(),
			$this->get_action(),
			$this->get_id(),
			$this->_get_classes(),
			$this->_get_attributes(),
			$this->get_text(),
			$this->_get_description()
		);
	}

	/**
	 * Get AJAX action. If not set field name will be used.
	 *
	 * @return string
	 */
	public function get_action() {
		return $this->_action ?: $this->get_name();
	}

	/**
	 * @param string $action
	 */
	public function set_action( $action ) {
		$this->_action = (string) $action;
	}

	/**
	 * Get button text
	 *
	 * @return string
	 */
	public function get_text() {
		return $this->_text;
	}

	/**
	 * Set button text
	 *
	 * @param $text
	 */
	public function set_text( $text ) {
		$this->_text = esc_html( $text );
	}

	public function enqueue_javascript() {
		wp_enqueue_script( 'wp-util' );
		Script::enqueue_admin_inline_script( 'wpkit-ajax-button', $this->_render_javascript() );
	}

	protected function _render_javascript() {
		ob_start();
		?>
        <script type="text/javascript">
            jQuery(function ($) {
                $(".wpkit-button").click(function (e) {
                    e.preventDefault();
                    var $button = $(this),
                        $spinner = $button.prev(),
                        $success = $button.next();
                    $spinner.addClass("is-active");
                    $button.attr("disabled", true);
                    $success.hide();
                    wp.ajax.send($button.data('action'), {
                        data: $button.data(),
                        error: function (error) {
                            alert(error);
                            $button.attr("disabled", false);
                            $spinner.removeClass("is-active");
                            $('body').trigger('wpkit.button.error', [$button]);
                        },
                        success: function () {
                            $button.attr("disabled", false);
                            $spinner.removeClass("is-active");
                            $success.fadeIn(500);
                            $('body').trigger('wpkit.button.success', [$button]);
                        }
                    });
                })
            });
        </script>
		<?php
		return ob_get_clean();
	}

}