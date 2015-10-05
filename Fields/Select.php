<?php

/**
 * Select field
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

class Select extends AbstractField
{
    protected $_type = 'select';
    protected $_options = [];

    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        return sprintf(
            '<input type="hidden" name="%s" value="" />' .
            '<select name="%s" id="%s" class="%s" %s >%s</select>%s',
            $this->get_name(),
            $this->get_name() . ($this->is_multiple() ? '[]' : ''),
            $this->get_id(),
            $this->_get_classes(),
            $this->_get_attributes(),
            $this->_render_options(),
	        $this->_get_description()
        );
    }

    /**
     * Get options of select
     *
     * @return array
     */
    public function get_options()
    {
        return $this->_options;
    }

    /**
     * Set options of select
     *
     * @param array $options key => value to set
     */
    public function set_options(array $options)
    {
        foreach ($options as $key => $title) {
            $this->add_option($title, $key);
        }
    }

    /**
     * Add single option
     *
     * @param string $title
     * @param mixed $key
     */
    public function add_option($title, $key = null)
    {
        if ($key === null) {
            $key = sanitize_key($title);
        }

        $sufix = 1;
        while (isset($this->_options[$key])) {
            $key = sanitize_key($title) . '-' . $sufix;
            $sufix++;
        }

        $this->_options[$key] = esc_html($title);
    }

    /**
     * Sort options
     *
     * @param bool $desc
     * @return bool
     */
    public function sort_options($desc = false)
    {
        return $desc ? arsort($this->_options) : asort($this->_options);
    }

    /**
     * Get field value
     *
     * @param bool $humanic if $humanic = false, will return a key of option
     * @return null|string
     */
    public function get_value($humanic = false)
    {
        return $humanic ? $this->get_option_value(parent::get_value()) : parent::get_value();
    }

    /**
     * Set field value
     *
     * @param mixed $value field value
     */
    public function set_value($value)
    {
        if ($this->is_multiple() && is_array($value)) {
            $new_value = [];
            foreach ($value as $v) {
                $new_value[] = $this->_set_single_value($v);
            }
        } else {
            $new_value = $this->_set_single_value($value);
        }
        $this->_value = $new_value;
    }

    protected function _set_single_value($value)
    {
        $key = sanitize_key($value);
        if (array_key_exists($key, $this->_options)) {
            return $key;
        } else {
            foreach ($this->_options as $key => $option) {
                if ($value == $option) {
                    return $key;
                    break;
                }
            }
        }

        return null;
    }

    /**
     * Get filed <option> value by key
     *
     * @param $option_key
     * @return mixed
     */
    private function get_option_value($option_key)
    {
        return array_key_exists($option_key, $this->_options) ? $this->_options[$option_key] : null;
    }

    protected function _render_options()
    {
        $output = $this->get_placeholder() ? "<option value=''>{$this->get_placeholder()}</option>" : '';

        foreach ($this->get_options() as $key => $title) {
            $selected = $this->_selected($key);
            $output .= "<option {$selected} value='{$key}'>{$title}</option>";
        }

        return $output;
    }

    protected function _selected($value)
    {
        if ($this->is_multiple()) {
            if (is_array($this->get_value()) && in_array($value, $this->get_value())) {
                return selected($value, $value, false);
            }
            return "";
        }
        else {
            return selected($value, $this->get_value(), false);
        }
    }

    /**
     * Check, is select is multiple
     *
     * @return bool
     */
    public function is_multiple()
    {
        return isset($this->_attributes['multiple']);
    }

    /**
     * Set multiple attribute
     *
     * @param bool $is_multiple
     */
    public function set_multiple($is_multiple)
    {
        if($is_multiple) {
            $this->_attributes['multiple'] = 'multiple';
        }
        elseif($this->is_multiple()) {
            unset($this->_attributes['multiple']);
        }
    }

    public function __call($name, $args)
    {
        if(in_array($name, ['enable_select2', 'set_select2_options', 'add_select2_option', 'get_select2_options', 'set_sortable', 'is_sortable', 'reload_javascript_repeatable'])) {
            function_exists('_doing_it_wrong') && _doing_it_wrong("WPKit\\Fields\\Select::$name", 'Deprecated method', '1.6');
        }
    }
}