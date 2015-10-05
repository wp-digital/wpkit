<?php

/**
 * Field label
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Fields\Label;

use WPKit\Fields\AbstractField;

class Label
{
    protected $_classes = [];
    protected $_text = null;

    /**
     * @var AbstractField
     */
    protected $_field = null;

    /**
     * @param AbstractField $field associated field
     */
    public function __construct(AbstractField $field)
    {
        $this->_field = $field;
    }

    /**
     * Add label CSS class
     *
     * @param string $class CSS class
     */
    public function add_class($class)
    {
        array_push($this->_classes, $class);
    }

    /**
     * Set label text
     *
     * @param string $text label text
     */
    public function set_text($text)
    {
        $this->_text = $text;
    }

    /**
     * Get label text
     *
     * @return string label text
     */
    public function get_text()
    {
        return $this->_text;
    }

    /**
     * Get label html
     *
     * @return string html
     */
    public function render()
    {
        return sprintf('<label%s%s>%s</label>', $this->_render_for_attribute(), $this->_render_class_attribute(), $this->_text);
    }

    protected function _render_class_attribute()
    {
        if(empty($this->_classes)) {
            return '';
        }
        else {
            return ' class="' . implode(' ', $this->_classes) . '"';
        }
    }

    protected function _render_for_attribute()
    {
        return ' for="' . $this->_field->get_id() . '"';
    }
}
