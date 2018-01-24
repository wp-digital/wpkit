<?php

/**
 * Slider field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2017, Innocode AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Viktor Kuliebiakin <viktor.kuliebiakin@innocode.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class Slider extends AbstractField
{
    protected $_type = 'hidden';

    /**
     * wp_enqueue_script action
     */
    public function enqueue_javascript()
    {
        wp_enqueue_script( 'jquery-ui-slider' );
        Script::enqueue_admin_inline_script( 'wpkit-field-slider', $this->_render_javascript() );
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
     * Render full field html (with label)
     *
     * @return string
     */
    public function render_field()
    {
        return sprintf(
            '<input type="text" id="%s_value" readonly>
            <div data-type="slider" style="margin: 7px;"></div>%s',
            $this->get_id(),
            parent::render_field()
        );
    }

    protected function _render_javascript()
    {
        ob_start(); ?>
        <script type="text/javascript">
            jQuery(function ($) {
                wp.wpkit = wp.wpkit || {};
                wp.wpkit.slider = {
                    init: function () {
                        $('[data-type="slider"]').each(function () {
                            var $this = $(this);
                            var $field = $('#<?= $this->get_id() ?>');
                            var $value = $('#<?= $this->get_id() ?>_value');
                            var data = $field.data();

                            $this.slider($.extend({}, data, {
                                create: function () {
                                    var $this = $(this);
                                    var values = $field.val().split(',');

                                    values[0] = parseInt(values[0], 10);

                                    if (!isNaN(values[0])) {
                                        $this.slider('value', values[0]);

                                        if (values.length === 2) {
                                            values[1] = parseInt(values[1], 10);

                                            if (isNaN(values[1])) {
                                                values[1] = values[0];
                                            }

                                            $this.slider('values', values);
                                        }
                                    }

                                    $value.val(values.join(' - '));
                                },
                                slide: function (event, ui) {
                                    if ($.isArray(ui.values)) {
                                        $field.val(ui.values.join(','));
                                        $value.val(ui.values.join(' - '));
                                    } else {
                                        $field.val(ui.value);
                                        $value.val(ui.value);
                                    }
                                }
                            }));
                        });
                    },
                    reinit: function () {
                        this.init();
                    }
                };
                wp.wpkit.slider.init();
            });
        </script>
        <?php return ob_get_clean();
    }
}