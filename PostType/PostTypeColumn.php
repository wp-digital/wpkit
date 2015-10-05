<?php

/**
 * WordPress post type column builder
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\PostType;

use WPKit\Helpers\Action;

class PostTypeColumn
{
    protected $_key = null;
    protected $_title = null;
    protected $_position = null;
    protected $_function = null;
    protected $_sortable = null;

    /**
     * Create post type table column
     *
     * @param PostType|string $post_type
     * @param string $title
     * @param callable $function
     * @param bool $sortable
     * @param int $position
     */
    public function __construct($post_type, $title, $function, $sortable = false, $position = -1)
    {
        if(is_array($title)) {
            $this->_title = $title[1];
            $this->_key = sanitize_key($title[0]);
        }
        else {
            $this->_title = $title;
            $this->_key = sanitize_key($title);
        }
        $this->_position = $position;
        $this->_sortable = $sortable;
        $this->_function = $function;

        if($post_type instanceof PostType) {
            $post_type = $post_type->get_key();
        }

        add_action("manage_edit-{$post_type}_columns", function($columns) {
            return $this->_add_column($columns, [$this->_key => $this->_title], $this->_position);
        });

        add_action("manage_{$post_type}_posts_custom_column", function($column){
            if($column == $this->_key) {
                return Action::execute($this->_function, $column);
            }
            return null;
        });

        if($sortable) {
            add_filter("manage_edit-{$post_type}_sortable_columns", function($columns) {
                return array_merge($columns, [$this->_key => $this->_key]);
            });
        }

    }

    protected function _add_column($columns, $column, $position)
    {
        $count = count($columns);
        $new_columns = [];
        $index = 0;

        if($position < 0) {
            $position = $count + $position;
            if($position < 0) {
                $position = 0;
            }
        }

        if($position >= $count) {
            $new_columns = array_merge($columns, $column);
        }
        else {
            foreach($columns as $id => $title) {
                if($position == $index) {
                    $new_columns = array_merge($new_columns, $column);
                }
                $new_columns[$id] = $title;
                $index++;
            }
        }

        return $new_columns;
    }

}