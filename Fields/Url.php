<?php

/**
 * Url field
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

use WPKit\Helpers\Script;

class Url extends Text {
    protected $_type = 'url';

    public function __construct()
    {
        $this->set_attribute('onblur', "wpkitCheckURL(this)");
    }

    /**
     * Filtering field value
     *
     * @param string $value
     *
     * @return string
     */
    public function apply_filter($value)
    {
        return esc_url($value);
    }

    /**
     * wp_enqueue_script action
     */
    public function enqueue_javascript()
    {
        wp_enqueue_script('jquery-ui-datepicker');
        Script::enqueue_admin_inline_script('wpkit-field-url', $this->_render_javascript());
    }

    protected function _render_javascript()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            function wpkitCheckURL(url) {
                var string = url.value;
                if (string.length && !~string.indexOf("http")) {
                    string = "http://" + string;
                }
                url.value = string;
                return url
            }
        </script>
        <?php
        return ob_get_clean();
    }
}