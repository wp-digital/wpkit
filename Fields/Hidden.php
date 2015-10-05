<?php

/**
 * Hidden field
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

class Hidden extends AbstractField
{
    protected $_type = 'hidden';

    /**
     * Render full field html (with label)
     *
     * @return string
     */
    public function render()
    {
        return $this->render_field();
    }

    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        return sprintf(
            '<input type="%s" name="%s" id="%s" class="%s" value="%s" %s />',
            $this->get_type(),
            $this->get_name(),
            $this->get_id(),
            $this->_get_classes(),
            $this->get_value(),
            $this->_get_attributes()
        );
    }

    /**
     * Render only label html
     *
     * @return string
     */
    public function render_label()
    {
        return '';
    }
}