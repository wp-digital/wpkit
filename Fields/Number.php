<?php

/**
 * Number field
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

class Number extends Text
{
    protected $_type = 'number';
    protected $_classes = [];
    protected $_attributes = ['step' => 'any'];
    protected $_min = null;
    protected $_max = null;

    /**
     * Set maximum value
     *
     * @param int $value
     */
    public function set_max($value)
    {
        if( is_numeric($value) ) {
            $this->_attributes['max'] = $value;
        }
        elseif( array_key_exists('max', $this->_attributes) ) {
            unset($this->_attributes['max']);
        }
    }

    /**
     * Set minimum value
     *
     * @param int $value
     */
    public function set_min($value)
    {
        if( is_numeric($value) ) {
            $this->_attributes['min'] = $value;
        }
        elseif( array_key_exists('min', $this->_attributes) ) {
            unset($this->_attributes['min']);
        }
    }

    /**
     * Filtering field value
     *
     * @param string $value
     * @return string
     */
    public function apply_filter($value)
    {
        if( ! is_numeric($value) ) {
            return null;
        }

	    $value = (float) $value;

	    if( $this->_min !== null && $this->_min > $value) {
            $value = $this->_min;
        }

        if( $this->_max !== null && $this->_max < $value) {
            $value = $this->_max;
        }

        return $value;
    }
}