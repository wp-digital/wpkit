<?php

/**
 * Date field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Date as DateHelper;
use WPKit\Helpers\Script;

class DateTime extends AbstractField {
	protected $_attributes = [
		'maxlength'                     => 10,
		'data-type'                     => 'datetime',
		'data-format'                   => 'yyyy-mm-dd hh:ii',
		'data-disableDblClickSelection' => 'true',
		'data-pick-time'                => 'true'
	];
	protected $_type = 'text';
	protected $_placeholder = 'yyyy-mm-dd hh:ii';
	private $dp_ver = '1.5.5';

	/**
	 * wp_enqueue_script action
	 */
	public function enqueue_javascript() {
		wp_enqueue_script( 'foundation-datepicker', 'https://cdnjs.cloudflare.com/ajax/libs/foundation-datepicker/' . $this->dp_ver . '/js/foundation-datepicker.min.js' );
		Script::enqueue_admin_inline_script( 'wpkit-field-datetime', $this->_render_javascript() );
	}

	protected function _render_javascript() {
		ob_start();
		?>
        <script type="text/javascript">
            jQuery(function ($) {
                wp.wpkit = wp.wpkit || {};
                wp.wpkit.datepicker = {
                    init: function () {
                        $('[data-type="datetime"]').each(function () {
                            var $this = $(this),
                                data = $this.data();

                            delete data.type;
                            $this.fdatepicker(data);
                        });
                    },
                    reinit: function () {
                        this.init();
                    }
                };
                wp.wpkit.datepicker.init();
            });
        </script>
		<?php
		return ob_get_clean();
	}

	/**
	 * wp_enqueue_style action
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'jquery-ui-smoothness', 'https://cdnjs.cloudflare.com/ajax/libs/foundation-datepicker/' . $this->dp_ver . '/css/foundation-datepicker.min.css' );
	}

	/**
	 * Filtering field value
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function apply_filter( $value ) {
		return $value;//DateHelper::esc_date( $value );
	}


}