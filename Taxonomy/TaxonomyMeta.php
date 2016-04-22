<?php

/**
 * Taxonomy meta table
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Taxonomy;

class TaxonomyMeta
{
    /**
     * @deprecated
     *
     * @var string
     */
    protected $_table = 'term_meta';

    /**
     * @var string
     */
    protected $_migration_key;

    /**
     * @var TaxonomyMeta
     */
    protected static $instance = null;

    /**
     * @deprecated
     *
     * @var \wpdb
     */
    protected $_db;

    protected function __construct()
    {
        $this->_migration_key = "_migrate_from_{$this->_table}_to_termmeta";

        if ( !get_option( $this->_migration_key ) ) {
            global $wpdb;

            $this->_db = $wpdb;
            $this->_table = $this->_db->prefix . $this->_table;

            $this->_migrate_from_term_meta_to_termmeta();
        }
    }

    /**
     * Get singleton instance (why singleton? mb i was drunk)
     *
     * @return TaxonomyMeta
     */
    public static function get_instance()
    {
        if(static::$instance == null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    private function __clone() {}
    private function __wakeup() {}

    /**
     * Update taxonomy meta
     *
     * @param int $term_id
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function update($term_id, $key, $value)
    {
        return update_term_meta( $term_id, $key, $value );
    }

    /**
     * Add taxonomy meta
     *
     * @param int $term_id
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function add($term_id, $key, $value)
    {
        return $this->update($term_id, $key, $value);
    }

    /**
     * Get taxonomy meta
     *
     * @param int $term_id
     * @param string $key
     * @return mixed
     */
    public function get($term_id, $key)
    {
        return get_term_meta( $term_id, $key, true );
    }

    /**
     * Delete taxonomy meta
     *
     * @param int $term_id
     * @param string $key
     * @return bool
     */
    public function delete($term_id, $key)
    {
        return delete_term_meta( $term_id, $key );
    }

    /**
     * @deprecated
     *
     * @return bool
     */
    protected function _is_table_exist()
    {
        return count($this->_db->get_results("SHOW TABLES LIKE '{$this->_table}'")) > 0;
    }

    /**
     * Migrate from custom db table to core due to termmeta support since WordPress 4.4.0
     */
    protected function _migrate_from_term_meta_to_termmeta()
    {
        if( $this->_is_table_exist() ) {
            $this->_merge_tables();
            $this->_drop_table();
        }

        update_option( $this->_migration_key, 1 );
    }

    /**
     * Rename custom db table to core due to termmeta support since WordPress 4.4.0
     */
    protected function _merge_tables()
    {
        return $this->_db->query( "INSERT INTO {$this->_db->termmeta} (term_id, meta_key, meta_value) 
            SELECT term_id, meta_key, meta_value FROM {$this->_table}" );
    }

    /**
     * Drop custom db table due to termmeta support since WordPress 4.4.0
     */
    protected function _drop_table()
    {
        return $this->_db->query( "DROP TABLE IF EXISTS {$this->_table}" );
    }
}