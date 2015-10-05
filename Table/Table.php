<?php

/**
 * Table viewer for WP_List_Table
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
use WPKit\Helpers\Action;
use wpdb;

class Table
{

    /**
     * @var WpListTable
     */
    private $_adapter = null;

    /**
     * @var  wpdb
     */
    private $wpdb;

    private $_table;

    /**
     * @var
     */

    private $_data;
    private $_primary_key = null;
    private $_general_column = null;
    private $_columns = [];
    private $_actions = [];
    private $_items_per_page = null;
    private $_total_items = null;

    /**
     * @var AbstractTableSingleView[]
     */
    private $_single_views = [];

    private $_display = 'table';

    /**
     * Create table view
     *
     * @param string $table_name DB table
     * @param string $primary_key DB table primary key
     * @param int $limit items per page
     */
    public function __construct($table_name, $primary_key, $limit = 20)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->_table = $this->wpdb->prefix . $table_name;
        $this->_primary_key = $primary_key;
        $this->_items_per_page = $limit;
    }

    /**
     * Display table html
     */
    public function render()
    {
        $this->_get_adapter()->set_bulk_actions($this->_get_bulk_actions());

        if ($this->_display == 'table') {
            $this->_render_table();
        }
        elseif ($this->_display == 'single_view') {
            $this->_render_single_view();
        }
    }

    protected function _render_table()
    {
        $table = $this->_get_adapter();
        $table->set_data($this->get_data());
        $table->set_primary_key($this->_primary_key);
        $table->set_items_per_page($this->_items_per_page);
        $table->set_columns($this->_columns);
        $table->set_total_items($this->get_total_items());
        $table->prepare_items();
        $this->render_search();
        $table->display();
    }

    protected function _render_single_view()
    {
        $view = $this->_single_views[$this->_get_current_action()];
        $view->set_primary_key($this->_primary_key);
        $view->set_data( $this->_load_data_item( (int) $_REQUEST['item'] ) );
        $view->render();
    }

    /**
     * Get count of found items
     *
     * @return int
     */
    public function get_total_items()
    {
        if (empty($this->_total_items)) {
            $this->_total_items = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->_table} {$this->_get_search_params()}");
        }
        return $this->_total_items;
    }


    /**
     * Setup table column
     *
     * @param string $column_key DB table column
     * @param string $title table title
     * @param callable $function display function
     * @param bool $sortable is column is sortable
     * @param bool $searchable is column is searchable
     */
    public function setup_column($column_key, $title, $function = null, $sortable = false, $searchable = false)
    {
        if ($function == null) {
            $function = function ($item, $key) {
                return $item[$key];
            };
        }
        $this->_columns[$column_key] = new TableColumn($column_key, $title, $function, $sortable, $searchable);
    }

    /**
     * Setup the main table column (column with actions)
     *
     * @param string $column_key DB table column
     * @param string $title table title
     * @param callable $function display function
     * @param bool $sortable is column is sortable
     * @param bool $searchable is column is searchable
     * @throws \WPKit\Exception\WpException
     */
    public function setup_general_column($column_key, $title, $function = null, $sortable = false, $searchable = false)
    {
        if ($this->_general_column != null) {
            throw new WpException("Table already has general column \"{$this->_general_column}\".");
        }
        $this->setup_column($column_key, $title, $this->_get_general_column_function($function), $sortable, $searchable);
        $this->_general_column = $column_key;
    }

    protected function _get_general_column_function($function)
    {
        return function ($item, $key) use ($function) {

            try {
                $value = Action::execute($function, [$item, $key]);
            } catch (WpException $e) {
                $value = $item[$key];
            }

            if ($this->has_single_view()) {
                $single_actions = [];

                foreach ($this->_single_views as $single_view) {
                    $single_actions[$single_view->get_key()] = (object) [
                        'name'     => $single_view->get_name(),
                        'key'      => $single_view->get_key(),
                        'function' => null,
                        'bulk'     => false,
                    ];
                }

                $this->_actions = array_merge($single_actions, $this->_actions);
            }

            $actions = [];
            foreach ($this->_actions as $key => $action) {
                $href =  add_query_arg( [
                    'page'      => $_REQUEST['page'],
                    'action'    => $key,
                    'item'      => $item[$this->_primary_key],
                ] ) ;
                $actions[$key] = sprintf('<a href="%s">%s</a>', $href, $action->name);
            }

            return $value . " " . $this->_get_adapter()->row_actions($actions);
        };
    }

    /**
     * Get searchable columns
     *
     * @return TableColumn[]
     */
    public function get_searchable_columns()
    {
        return array_filter($this->_columns, function(TableColumn $column) {
            return $column->is_searchable();
        });
    }

    /**
     * Get search form html
     */
    protected function render_search()
    {
        $this->_get_adapter()->search_box(__('Search', 'wpkit'), 'search');
    }

    protected function _load_data()
    {
        $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
        $offset = ($paged - 1) * $this->_items_per_page;
        return $this->wpdb->get_results("SELECT * FROM {$this->_table} {$this->_get_search_params()} {$this->_get_order_by()} LIMIT $offset, {$this->_items_per_page}", ARRAY_A);
    }

    protected function _load_data_item($item)
    {
        return $this->wpdb->get_row("SELECT * FROM {$this->_table} WHERE {$this->_primary_key} = '$item'", ARRAY_A);
    }

    protected function _get_order_by()
    {
        $order_by = isset($this->_columns[@$_REQUEST['orderby']]) ? $_REQUEST['orderby'] : $this->_primary_key;
        $order = @$_REQUEST['order'] == 'desc' ? 'DESC' : 'ASC';
        return "ORDER BY $order_by $order";
    }

    protected function _get_search_params()
    {
        $search_text = $this->get_search_text();
        if ( empty($search_text) ) {
            return '';
        }

        $where = [];
        foreach ($this->get_searchable_columns() as $column) {
            $where[] = "{$column->get_key()} LIKE '%{$search_text}%'";
        }
        return 'WHERE ' . implode(' OR ', $where);
    }

    /**
     * Get filtered search query
     *
     * @return String
     */
    public function get_search_text()
    {
        $search = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';
        $search = apply_filters('get_search_query', $search);
        return esc_attr($search);
    }

    /**
     * Get table data
     *
     * @return array
     */
    public function get_data()
    {
        if($this->_data == null) {
            $this->_data = $this->_load_data();
        }
        return $this->_data;
    }

    /**
     * Add table item action
     *
     * @param string $name action name
     * @param callable $function action function
     * @param bool $bulk can be used for bulk action
     */
    public function add_action($name, $function, $bulk = false)
    {
        $key = sanitize_key($name);
        $this->_actions[$key] = (object) [
            'name'     => $name,
            'key'      => $key,
            'function' => $function,
            'bulk'     => $bulk,
        ];
    }

    /**
     * Remove table item action
     *
     * @param string $name action name
     */
    public function remove_action($name)
    {
        $key = sanitize_key($name);
        if(array_key_exists($key, $this->_actions)) {
            unset($this->_actions[$key]);
        }
    }

    protected function _setup_view($action)
    {
        if ($action && $this->has_single_view($action) && isset($_REQUEST['item']) && is_numeric($_REQUEST['item'])) {
            $this->_display = 'single_view';
        }
        else {
            $this->_display = 'table';
        }
    }

    /**
     * Execute table items actions
     */
    public function process_action()
    {
        $action = $this->_get_current_action();
        $this->_setup_view($action);

        if ($action && array_key_exists($action, $this->_actions)) {


            $function = $this->_actions[$action]->function;
            $args = [
                'action' => $action,
                'item'   => $_REQUEST['item'],
            ];

            Action::execute($function, $args);

            wp_redirect( remove_query_arg( ['action', 'action2', 'item'], wp_unslash($_SERVER['REQUEST_URI']) ) );
            exit;

        }

    }

    protected function _get_bulk_actions()
    {
        $actions = array_filter($this->_actions, function($action){
            return $action->bulk;
        });
        return wp_list_pluck($actions, 'name');
    }

    /**
     * Is table has singe view
     *
     * @param string $name
     * @return bool
     */
    public function has_single_view($name = null)
    {
        if ($name == null) {
            return count($this->_single_views) > 0;
        }
        else {
            return isset($this->_single_views[$name]);
        }
    }

    /**
     * Add table item single view to table
     *
     * @param AbstractTableSingleView $single_view
     */
    public function add_single_view(AbstractTableSingleView $single_view)
    {
        $this->_single_views[$single_view->get_key()] = $single_view;
    }

    /**
     * @return \WPKit\Table\WpListTable
     */
    protected function _get_adapter()
    {
        if ($this->_adapter == null) {
            $this->set_adapter(new WpListTable());
        }
        return $this->_adapter;
    }

    /**
     * Set table adapter
     *
     * @param WpListTable $adapter
     */
    public function set_adapter(WpListTable $adapter)
    {
        $this->_adapter = $adapter;
    }

    protected function _get_current_action()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
        if($action == null || $action == -1) {
            $action = isset($_REQUEST['action2']) ? $_REQUEST['action2'] : null;
        }
        if($action == -1) {
            $action = null;
        }
        return $action && array_key_exists( $action, array_merge($this->_actions, $this->_single_views) ) ? $action : null;
    }
}