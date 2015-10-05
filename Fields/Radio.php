<?php

/**
 * Radio field
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

class Radio extends Select
{
    protected $_type = 'radio';

    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        return $this->_render_options();
    }

    protected function _render_options()
    {
        $output = sprintf('<input type="hidden" name="%s" value="" />', $this->get_name());
        foreach($this->get_options() as $key => $title) {
            $output .= sprintf(
                '<label><input type="radio" name="%s" id="%s" class="%s" value="%s" %s %s /> %s</label><br/>',
                $this->get_name(),
                $this->get_id() . "_$key",
                $this->_get_classes(),
                $key,
                checked($key, $this->get_value(), false),
                $this->_get_attributes(),
                $title
            );
        }
        return $output;
    }

    /**
     * Render only label html
     *
     * @return string
     */
    public function render_label()
    {
        if($this->get_label()) {
            return sprintf('<label style="font-weight: bold">%s</label>', $this->get_label());
        }
        return '';
    }

}