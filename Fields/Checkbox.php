<?php

/**
 * Checkbox field
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

class Checkbox extends AbstractField
{
    protected $_type = 'checkbox';

    /**
     * Set field value
     *
     * @param mixed $value field value
     */
    public function set_value($value)
    {
        parent::set_value($value);
        $this->set_attribute('checked', $this->_value ? $this->_value : null);
    }

    /**
     * Filtering field value
     *
     * @param string $value
     * @return string
     */
    public function apply_filter($value)
    {
        return (bool) $value;
    }

    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        return sprintf(
            '<input type="hidden" name="%s" value="" />' .
            '<input type="%s" name="%s" id="%s" class="%s" value="1" %s />',
            $this->get_name(),
            $this->get_type(),
            $this->get_name(),
            $this->get_id(),
            $this->_get_classes(),
            $this->_get_attributes()
        );
    }

    /**
     * Render full field html (with label)
     *
     * @return string
     */
    public function render()
    {
        $output = '<div class="form-group" style="margin-bottom: 15px">';
        $output .= $this->render_field();
        $output .= ' ';
        $output .= $this->render_label();
        $output .= '</div>';
        return $output;
    }
}