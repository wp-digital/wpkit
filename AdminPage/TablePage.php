<?php

/**
 * Class for WordPress admin page with WP_List_Table
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\AdminPage;

use WPKit\Table\Table;

class TablePage extends AbstractPage
{
    /**
     * @var Table
     */
    protected $_table = null;

    public function __construct($key, $title, $parent = null)
    {
        parent::__construct($key, $title, $parent);

        if(is_admin() && isset($_REQUEST['page']) && $_REQUEST['page'] == $this->_key) {
            add_action('init', function() {

                $this->_table->process_action();

                if( ! empty($_GET['_wp_http_referer']) ) {
                    wp_redirect( remove_query_arg( ['_wp_http_referer', '_wpnonce'], wp_unslash($_SERVER['REQUEST_URI']) ) );
                    exit;
                }
            });
        }
    }

    /**
     * Get page content html
     *
     * @return string
     */
    public function render()
    {
        echo '<div class="wrap">';
        echo "<h2>{$this->_title}</h2>";
        echo '<form method="get" action="">';
        echo "<input type='hidden' name='page' value='{$this->get_key()}' />";
        $this->_table->render();
        echo '</form>';
        echo '</div>';
    }

    /**
     * Set Table to display on admin page
     *
     * @param Table $table
     */
    public function set_table(Table $table)
    {
        $this->_table = $table;
    }

}