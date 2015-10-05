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
    protected $_table = 'term_meta';

    /**
     * @var TaxonomyMeta
     */
    protected static $instance = null;

    /**
     * @var \wpdb
     */
    protected $_db;

    protected function __construct()
    {
        global $wpdb;
        $this->_db = $wpdb;
        $this->_table = $this->_db->prefix . $this->_table;

        if( ! $this->_is_table_exist() ) {
            $this->_create_table();
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
     * @return int
     */
    public function update($term_id, $key, $value)
    {
        $old_value = $this->get($term_id, $key);

        if($old_value === false) {
            return $this->_add($term_id, $key, $value);
        }

        if($old_value === $value) {
            return true;
        }

        wp_cache_set($this->_get_cache_key($term_id, $key), $value, 'taxonomy-meta');
        return $this->_db->update( $this->_table, ['meta_value' => maybe_serialize($value)], ['term_id' => $term_id, 'meta_key' => $key] );
    }

    /**
     * Add taxonomy meta
     *
     * @param int $term_id
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function add($term_id, $key, $value)
    {
        return $this->update($term_id, $key, $value);
    }

    protected function _add($term_id, $key, $value)
    {
        wp_cache_set($this->_get_cache_key($term_id, $key), $value, 'taxonomy-meta');
        return $this->_db->insert( $this->_table, ['term_id' => $term_id, 'meta_key' => $key, 'meta_value' => maybe_serialize($value)] );
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
        if( $value = wp_cache_get($this->_get_cache_key($term_id, $key), 'taxonomy-meta')) {
            return $value;
        }

        $row = $this->_db->get_row(
            $this->_db->prepare( "SELECT meta_value FROM {$this->_table} WHERE term_id = %d AND meta_key = %s LIMIT 1", $term_id, $key )
        );

        if ( is_object( $row ) ) {
            $value = maybe_unserialize($row->meta_value);
        }
        else {
            $value = false;
        }

        wp_cache_set($this->_get_cache_key($term_id, $key), $value, 'taxonomy-meta');
        return $value;
    }

    /**
     * Delete taxonomy meta
     *
     * @param int $term_id
     * @param string $key
     * @return int
     */
    public function delete($term_id, $key)
    {
        wp_cache_delete($this->_get_cache_key($term_id, $key), 'taxonomy-meta');
        return $this->_db->delete( $this->_table, ['term_id' => $term_id, 'meta_key' => $key] );
    }

    protected function _get_cache_key($term_id, $key)
    {
        return "{$key}-{$term_id}";
    }

    protected function _is_table_exist()
    {
        return count($this->_db->get_results("SHOW TABLES LIKE '{$this->_table}'")) > 0;
    }

    protected function _create_table()
    {
        $this->_db->query("CREATE TABLE IF NOT EXISTS `{$this->_table}` (
              `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
              `meta_key` varchar(255) DEFAULT NULL,
              `meta_value` longtext,
              PRIMARY KEY (`meta_id`),
              KEY `term_id` (`term_id`),
              KEY `meta_key` (`meta_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    }

}