<?php

/**
 * Textarea field
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

class Textarea extends AbstractField
{
    protected $_type = 'textarea';
    protected $_attributes = ['rows' => 10, 'style' => 'resize: vertical'];
    protected $_classes = ['large-text code'];

    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        return sprintf(
            '<textarea name="%s" id="%s" class="%s" placeholder="%s" %s >%s</textarea>%s',
            $this->get_name(),
            $this->get_id(),
            $this->_get_classes(),
            $this->get_placeholder(),
            $this->_get_attributes(),
            $this->get_value(),
            $this->_get_description()
        );
    }

}