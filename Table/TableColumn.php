<?php

/**
 * Column for table
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

use WPKit\Helpers\Action;

class TableColumn
{
    protected $_title = null;
    protected $_key = null;
    protected $_function = null;
    protected $_sortable = false;
    protected $_searchable = false;

    /**
     * Create table column
     *
     * @param string $key
     * @param string $title
     * @param callable $function
     * @param bool $sortable
     * @param bool $searchable
     */
    public function __construct($key, $title, $function, $sortable = false, $searchable = false)
    {
        $this->_title = $title;
        $this->_key = $key;
        $this->_function = $function;
        $this->_sortable = $sortable;
        $this->_searchable = $searchable;
    }

    /**
     * Is column is sortable
     *
     * @return bool
     */
    public function is_sortable()
    {
        return $this->_sortable;
    }

    /**
     * Is column is searchable
     *
     * @return bool
     */
    public function is_searchable()
    {
        return $this->_searchable;
    }

    /**
     * Get column title
     *
     * @return string
     */
    public function get_title()
    {
        return $this->_title;
    }

    /**
     * Get column slug
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Ger column html
     *
     * @param array $item
     * @param string $key
     * @throws \WPKit\Exception\WpException
     */
    public function render($item, $key)
    {
        return Action::execute($this->_function, [$item, $key]);
    }

}