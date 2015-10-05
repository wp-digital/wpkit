<?php

/**
 * Table adapter for WP_List_Table
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

use WPKit\Exception\WpException;
use WP_List_Table;

require_once ABSPATH . "wp-admin/includes/class-wp-list-table.php";

class WpListTable extends WP_List_Table
{

    /**
     * @var TableColumn[]
     */
    protected $_columns = [];

    protected $_primary_key = null;
    protected $_data = [];
    protected $_functions = [];
    protected $_column_headers = null;
    protected $_items_per_page = 10;
    protected $_total_items = 0;

    public function __construct()
    {
        parent::__construct([
            'plural' => 'items',
            'singular' => 'item',
            'ajax' => false,
            'screen' => null,
        ]);
    }

    public function ajax_user_can()
    {
        return current_user_can('manage_options');
    }

    public function prepare_items()
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];

        $this->items = $this->_data;

        $this->set_pagination_args( [
            'total_items' => $this->_total_items,
            'per_page' => $this->_items_per_page,
            'total_pages' => ceil($this->_total_items / $this->_items_per_page)
        ] );
    }

    public function get_columns()
    {
        $columns = [];
        foreach($this->_columns as $column) {
            $columns[ $column->get_key() ] = $column->get_title();
        }
        if(count($this->_actions) > 0) {
            $columns = array_merge(['cb' => '<input type="checkbox" />'], $columns);
        }
        return $columns;
    }


    public function get_sortable_columns()
    {
        $columns = array_filter($this->_columns, function(TableColumn $column) {
            return $column->is_sortable();
        });
        return array_map(function(TableColumn $column) {
            return [$column->get_key(), 'asc'];
        }, $columns);
    }

    public function set_bulk_actions($actions)
    {
        $this->_actions = $actions;
    }

    /**
     * @param TableColumn[] $columns
     */
    public function set_columns(array $columns)
    {
        $this->_columns = $columns;
    }

    public function set_primary_key($key)
    {
        $this->_primary_key = $key;
    }

    public function set_data(array $data)
    {
        $this->_data = $data;
    }

    public function set_items_per_page($count)
    {
        $this->_items_per_page = (int) $count;
    }

    public function set_total_items($count)
    {
        $this->_total_items = $count;
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%s[]" value="%s" />', $this->_args['singular'], $item[$this->_primary_key]);
    }

    protected function column_default($item, $key)
    {
        if(isset($this->_columns[$key])) {
            return $this->_columns[$key]->render($item, $key);
        }

        return isset($item[$key]) ? $item[$key] : null;
    }

    /**
     * Display the bulk actions dropdown.
     *
     * @since 3.1.0
     * @access public
     */
    public function bulk_actions($which = '')
    {
        if ( is_null( $this->_actions ) ) {
            $no_new_actions = $this->_actions = $this->get_bulk_actions();
            /**
             * Filter the list table Bulk Actions drop-down.
             *
             * The dynamic portion of the hook name, $this->screen->id, refers
             * to the ID of the current screen, usually a string.
             *
             * This filter can currently only be used to remove bulk actions.
             *
             * @since 3.5.0
             *
             * @param array $actions An array of the available bulk actions.
             */
            $this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
            $this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
            $two = '';
        } else {
            $two = '2';
        }

        // modified by pingbull
        if( ! isset( $GLOBALS["wp-list-table-has-bulk-actions-{$this->screen->id}"] ) ) {
            $GLOBALS["wp-list-table-has-bulk-actions-{$this->screen->id}"] = true;
            $two = '';
        }
        else {
            $two = '2';
        }

        if ( empty( $this->_actions ) )
            return;

        echo "<select name='action$two'>\n";
        echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'wpkit' ) . "</option>\n";

        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

            echo "\t<option value='$name'$class>$title</option>\n";
        }

        echo "</select>\n";

        submit_button( __( 'Apply', 'wpkit' ), 'action', false, false, ['id' => "doaction$two"] );
        echo "\n";
    }


}