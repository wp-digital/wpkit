<?php

/**
 * Abstract class for Table single view
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Table;

abstract class AbstractTableSingleView
{
    protected $_back_link = null;

    protected $_name = null;
    protected $_key = null;

    protected $_data = [];
    protected $_primary_key = null;

    /**
     * Create table single view
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->_name = $name;
        $this->_key = sanitize_key($name);
    }

    /**
     * Get view slug
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Get view name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->_name;
    }

    /**
     * Set full table data
     *
     * @param array $data
     */
    public function set_data($data)
    {
        $this->_data = $data;
    }

    /**
     * Set table primary key
     *
     * @param string $key
     */
    public function set_primary_key($key)
    {
        $this->_primary_key = $key;
    }

    /**
     * Get table primary key
     *
     * @return string
     */
    public function get_primary_key()
    {
        return $this->_primary_key;
    }

    /**
     * Display single view html
     */
    abstract public function render();
}