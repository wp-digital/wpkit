<?php

/**
 * Color picker field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class Color extends AbstractField
{
	protected $_attributes = ['maxlength' => 7, 'data-type' => 'color'];
	protected $_placeholder = 'Hex';
    protected $_type = 'text';

    /**
     * wp_enqueue_script action
     */
	public function enqueue_javascript()
	{
		wp_enqueue_script('wp-color-picker');
        Script::enqueue_admin_inline_script('wpkit-field-color', $this->_render_javascript());
	}

    /**
     * wp_enqueue_style action
     */
    public function enqueue_style()
    {
        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Filtering field value
     *
     * @param string $value
     * @return string
     */
	public function apply_filter($value)
	{
		return $this->_esc_color($value);
	}

	protected function _render_javascript()
	{
        ob_start();
		?>
		<script type="text/javascript">
			jQuery(function () {
				jQuery('[data-type="color"]').wpColorPicker();
                jQuery(document).on('repeatable_row_added', function() {
                    jQuery('[data-type="color"]:visible').wpColorPicker();
                });
			});
		</script>
		<?php
        return ob_get_clean();
	}

    /**
     * Get field reload javascript
     *
     * @return string
     */
    public function reload_javascript()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('#<?= $this->get_id() ?>:visible').wpColorPicker();
            });
        </script>
        <?php
        return ob_get_clean();
    }

	protected function _esc_color($value)
	{
		if ( preg_match( '/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $value ) ) {
			$color = $value;
		}
        else if ( preg_match( '/^([a-f0-9]{6}|[a-f0-9]{3})$/i', $value ) ) {
			$color = "#{$value}";
		}
        else {
			$color = '';
		}

		return $color;
	}
}