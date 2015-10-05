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

use WPKit\Helpers\Script;
use WPKit\Helpers\Date as DateHelper;

class Date extends AbstractField
{
	protected $_attributes = ['maxlength' => 10, 'data-type' => 'date'];
	protected $_type = 'text';
	protected $_placeholder = 'YYYY-MM-DD';

    /**
     * wp_enqueue_script action
     */
	public function enqueue_javascript()
	{
		wp_enqueue_script( 'jquery-ui-datepicker' );
		Script::enqueue_admin_inline_script( 'wpkit-field-date', $this->_render_javascript() );
	}

    /**
     * wp_enqueue_style action
     */
	public function enqueue_style()
	{
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style( 'jquery-ui-smoothness', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css" );
	}

    /**
     * Filtering field value
     *
     * @param string $value
     * @return string
     */
	public function apply_filter( $value )
	{
		return DateHelper::esc_date( $value );
	}

	protected function _render_javascript()
	{
		ob_start();
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('[data-type="date"]').each(function () {
					var $this = $(this),
						data = $this.data();

					delete data.type;
					data.dateFormat = 'yy-mm-dd';
					$this.datepicker(data);
				});
			});
		</script>
		<?php
		return ob_get_clean();
	}
}