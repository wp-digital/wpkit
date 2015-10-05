<?php

/**
 * Select2 field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 * @author Vitaly Nikolaev <vitaly@pingbull.no>
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class Select2 extends Select
{
    protected $_classes = ['select2'];
    protected $_select2_options = [];
    const SELECT_VERSION = '3.5.2';

    /**
     * Set field placeholder
     *
     * @param string $placeholder field placeholder
     */
    public function set_placeholder( $placeholder )
    {
        $this->_placeholder = $placeholder;
        $this->set_attribute('data-placeholder', $this->get_placeholder());
    }

    /**
     * Set select2 plugin options
     *
     * @see https://select2.github.io/
     * @param array $options
     */
    public function set_select2_options(array $options)
    {
        foreach ($options as $key => $value) {
            $this->add_select2_option($key, $value);
        }
    }

    /**
     * Add select2 plugin option
     *
     * @see https://select2.github.io/
     * @param string $key
     * @param string $value
     */
    public function add_select2_option($key, $value)
    {
        $this->_select2_options[$key] = $value;
    }

    /**
     * Get select2 plugin options
     *
     * @see https://select2.github.io/
     * @return array
     */
    protected function get_select2_options()
    {
        return $this->_select2_options;
    }

    /**
     * wp_enqueue_style action
     */
    public function enqueue_style()
    {
        wp_register_style('wpkit-select2-lib', "http://cdnjs.cloudflare.com/ajax/libs/select2/" . self::SELECT_VERSION . "/select2.min.css", '', self::SELECT_VERSION, 'all');
        wp_enqueue_style('wpkit-select2-lib');
        wp_add_inline_style('wpkit-select2-lib', $this->_render_stylesheets());
    }

    protected function _render_stylesheets()
    {
        ob_start();
        ?>
        .select2-container { margin-left: 0 !important; min-width: 150px; }
        .select2-container .select2-choice { height: 28px; line-height: 28px; -webkit-border-radius: 2px; -moz-border-radius: 2px; border-radius: 2px; border-color: #dcdcdc; background: #fff !important; filter: none; }
        .select2-container .select2-choice .select2-arrow { background: none; border: none; }
        .select2-container .select2-choice .select2-arrow b { background-position: 1px 1px; }
        .select2-dropdown-open .select2-choice .select2-arrow b { background-position: -17px 1px; }
        .select2-container-active .select2-choice, .select2-container-active .select2-choices, .select2-container .select2-choice:hover { border-color: #c2c2c2 !important; -webkit-box-shadow: none !important; -moz-box-shadow: none !important; box-shadow: none !important; }
        .select2-results { padding: 0; margin-right: 0; }
        .select2-search { padding-top: 4px; }
        .select2-drop, .select2-drop.select2-drop-above { border: 1px solid #aaa; -webkit-border-radius: 0; -moz-border-radius: 0; border-radius: 0; }
        .select2-drop.select2-drop-above.select2-drop-active { border-color: #aaa; margin-top: -5px; }
        .select2-drop-multi.select2-drop-above { margin-top: -1px !important; }
        .select2-container .select2-choice abbr { top: 7px;}
        .select2-container-multi .select2-choices { min-height: 28px; background: #fff; border: 1px solid #ddd; -webkit-border-radius: 2px; -moz-border-radius: 2px; border-radius: 2px; }
        .select2-container-multi .select2-choices .select2-search-field input { height: 24px; padding-bottom: 4px; }
        .select2-container-multi .select2-choices .select2-search-choice { background: #f8f8f8; height: 22px; line-height: 14px; margin: 2px 1px 1px 2px; -webkit-border-radius: 2px !important; -moz-border-radius: 2px !important; border-radius: 2px !important; padding: 3px 22px 3px 6px; border: 1px solid #aaa; -webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none; }
        .select2-container-multi .select2-search-choice-close { right: 3px; top: 4px; left: auto; }
        .select2-container-multi .select2-choices .select2-search-choice-focus { background: #e1e1e1; }
        .select2-dropdown-open.select2-drop-above .select2-choice, .select2-dropdown-open.select2-drop-above .select2-choices { background: #fff !important; border: 1px solid #dcdcdc; -webkit-border-radius: 2px; -moz-border-radius: 2px; border-radius: 2px; }
        .select2-container .select2-choice > .select2-chosen i, .select2-results .select2-result-label i, .select2-container-multi .select2-choices .select2-search-choice i { margin: -1px 6px 0 0; height: 15px; vertical-align: -2px }
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
        return $this->_render_javascript();
    }

    protected function _render_javascript()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            jQuery(function () {
                var initSelect2 = function() {
                    jQuery("select.select2:not('.select2-offscreen')").select2(<?= json_encode($this->get_select2_options()) ?>);
                };
                initSelect2();
                jQuery(document).on('repeatable_row_added', function() {
                    initSelect2();
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * wp_enqueue_script action
     */
    public function enqueue_javascript()
    {
        wp_enqueue_script('wpkit-select2', "https://cdnjs.cloudflare.com/ajax/libs/select2/" . self::SELECT_VERSION . "/select2.min.js", ['jquery'], self::SELECT_VERSION);
        Script::enqueue_admin_inline_script('wpkit-select2-init' . $this->get_id(), $this->_render_javascript());
    }
}