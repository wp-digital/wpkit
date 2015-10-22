<?php

/**
 * WP Query posts loader for home builder
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Query;

use WP_Query;
use WP_Post;

class PostsLoader
{

    protected $_post_types = [];
    protected $_limit = 0;
    protected $_offset = 0;
    protected $_sticky_posts = [];
    protected $_sticky_post_types = [];

    protected $_preload_terms = true;
    protected $_preload_meta = true;

    protected $_cache_key = null;
    protected $_cache_expire = 0;

    private $_empty_post_object = null;

    public function __construct( $post_types, $limit )
    {
        if( null == $post_types ) {
            $post_types = 'all';
        }
        $this->_post_types = is_array( $post_types ) ? $post_types : [ $post_types ];
        $this->_limit = absint( $limit );
    }

    public function set_offset( $offset )
    {
        $this->_offset = absint( $offset );
    }

    public function get_offset()
    {
        return $this->_offset;
    }

    public function set_sticky_post( $index, $post_id )
    {
        $index = absint( $index );
        $post_id = absint( $post_id );
        $this->_sticky_posts[ $index ] = $post_id;
    }

    public function get_sticky_posts()
    {
        return $this->_sticky_posts;
    }

    public function set_sticky_post_type( $index, $post_type ) // todo: order?
    {
        $index = absint( $index );
        $this->_sticky_post_types[ $index ] = $post_type;
    }

    public function set_meta_preload( $value )
    {
        $this->_preload_meta = boolval( $value );
    }

    public function get_meta_preload()
    {
        return $this->_preload_meta;
    }

    public function set_term_preload( $value )
    {
        $this->_preload_terms = boolval( $value );
    }

    public function get_term_preload()
    {
        return $this->_preload_terms;
    }

    public function enable_cache( $transient_key, $expire )
    {
        $this->_cache_key = $transient_key;
        $this->_cache_expire = $expire;
    }

    public function disable_cache()
    {
        $this->_cache_key = null;
        $this->_cache_expire = 0;
    }


    public function get_query()
    {

        if( $this->_cache_key ) {

            if( false === ( $ordered_posts_ids_data = get_transient( $this->_cache_key ) ) ) {
                $ordered_posts_ids_data = $this->_get_ordered_posts_ids_data();
                set_transient( $this->_cache_key, $ordered_posts_ids_data, $this->_cache_expire );
            }
        }
        else {
            $ordered_posts_ids_data = $this->_get_ordered_posts_ids_data();
        }

        list( $ordered_ids, $posts_not_found ) = $ordered_posts_ids_data;

        $args = [
            'post_type'                 => 'any',
            'posts_per_page'            => -1,
            'post_status'               => 'publish',
            'ignore_sticky_posts'       => true,
            'no_found_rows'             => true,
            'post__in'                  => $ordered_ids,
            'orderby'                   => 'post__in',
            'update_post_meta_cache'    => $this->_preload_meta,
            'update_post_term_cache'    => $this->_preload_terms,
        ];

        $query = new WP_Query( $args );

        if( 0 < count( $posts_not_found ) ) {
            // create create objects for not found posts

            $no_found_count = count( $posts_not_found );

            $query->post_count += $no_found_count;
            $query->found_posts += $no_found_count;

            $tmp_query = $query->posts;
            $query->posts = [];

            reset( $tmp_query );

            for( $index = $this->_offset; $index < $this->_offset + $this->_limit; $index++ ) {
                if( in_array( $index, $posts_not_found ) ) {
                    $query->posts[] = $this->_get_empty_post_object();
                }
                else {
                    $query->posts[] = current( $tmp_query );
                    @next( $tmp_query );
                }
            }
        }

        return $query;
    }


    protected function _get_ordered_posts_ids_data()
    {
        $sticky_posts = array_values( $this->_sticky_posts );

        if( 0 < count( $sticky_posts ) ) {

            // check sticky posts exists
            $args = [
                'fields'                => 'ids',
                'post_status'           => 'publish',
                'post_type'             => 'any',
                'posts_per_page'        => -1,
                'orderby'               => 'ID',
                'ignore_sticky_posts'   => true,
                'no_found_rows'         => true,
                'has_password'          => false,
                'post__in'              => $sticky_posts,
            ];
            $sticky_posts = get_posts( $args );

            foreach( $this->_sticky_posts as $index => $post_id ) {
                if( ! in_array( $post_id, $sticky_posts ) ) {
                    unset( $this->_sticky_posts[ $index ] );
                }
            }

        }

        // calculate counts and offsets

        $count_per_sticky_post_type = [];
        $count_of_default_post_type = 0;
        $count_of_sticky_posts = 0;

        $offset_per_sticky_post_type = [];
        $offset_of_default_post_type = 0;

        for( $index = 0; $index < $this->_offset + $this->_limit; $index++ ) {

            if( isset( $this->_sticky_posts[ $index ] ) ) { // sticky posts have top priority
                // this index has sticky post

                $count_of_sticky_posts ++;
            }
            elseif( isset( $this->_sticky_post_types[ $index ] ) ) {
                // this index has sticky post type

                $post_type = $this->_sticky_post_types[ $index ];

                if( $index < $this->_offset ) {

                    if( ! isset( $offset_per_sticky_post_type[ $post_type ] ) ) {
                        $offset_per_sticky_post_type[ $post_type ] = 0;
                    }
                    $offset_per_sticky_post_type[ $post_type ]++;
                }
                else {

                    if( ! isset( $count_per_sticky_post_type[ $post_type ] ) ) {
                        $count_per_sticky_post_type[ $post_type ] = 0;
                    }
                    $count_per_sticky_post_type[ $post_type ]++;
                }
            }
            else {
                // nothing

                if( $index < $this->_offset ) {
                    $offset_of_default_post_type++;
                }
                else {
                    $count_of_default_post_type++;
                }

            }

        }

        // load posts ids
        $pots_ids_per_type = [];

        // extra post types
        foreach( $count_per_sticky_post_type as $post_type => $count ) {

            $offset = isset( $offset_per_sticky_post_type[ $post_type ] ) ? $offset_per_sticky_post_type[ $post_type ] : 0;

            $args = [
                'fields'                => 'ids',
                'post_status'           => 'publish',
                'post_type'             => $post_type,
                'posts_per_page'        => $count,
                'offset'                => $offset,
                'post__not_in'          => $sticky_posts, // todo: replace post__not_in for better performance
                'ignore_sticky_posts'   => true,
                'no_found_rows'         => true,
                'has_password'          => false,
            ];

            $pots_ids_per_type[ $post_type ] = get_posts( $args );
        }

        // default post types
        $args = [
            'fields'                => 'ids',
            'post_status'           => 'publish',
            'post_type'             => $this->_post_types,
            'posts_per_page'        => $count_of_default_post_type,
            'offset'                => $offset_of_default_post_type,
            'post__not_in'          => $sticky_posts, // todo: replace post__not_in for better performance
            'ignore_sticky_posts'   => true,
            'no_found_rows'         => true,
            'has_password'          => false,
        ];

        $pots_ids_per_type['__default'] = get_posts( $args );


        // paranoid
        foreach( $pots_ids_per_type as $key => $_ ) {
            @reset( $pots_ids_per_type[ $key ] );
        }

        $ordered_ids = [];
        $posts_not_found = [];

        for( $index = $this->_offset; $index < $this->_offset + $this->_limit; $index++ ) {

            if( isset( $this->_sticky_posts[ $index ] ) ) { // sticky posts have top priority
                // this index has sticky post
                $ordered_ids[] = $this->_sticky_posts[ $index ];
            }
            elseif( isset( $this->_sticky_post_types[ $index ] ) ) {
                // this index has sticky post type

                $post_type = $this->_sticky_post_types[ $index ];

                if( isset( $pots_ids_per_type[ $post_type ] ) && $id = current( $pots_ids_per_type[ $post_type ] ) ) {
                    $ordered_ids[] = $id;
                    @next( $pots_ids_per_type[ $post_type ] );
                }
                else {
                    $posts_not_found[] = $index;
                }

            }
            else {
                // nothing


                if( $id = current( $pots_ids_per_type['__default'] ) ) {
                    $ordered_ids[] = $id;
                    @next( $pots_ids_per_type[ '__default' ] );
                }
                else {
                    $posts_not_found[] = $index;
                }

            }

        }

        return [ $ordered_ids, $posts_not_found ];
    }

    protected function _get_empty_post_object()
    {
        if( null == $this->_empty_post_object ) {
            $vars = [
                'ID'                    => null,
                'post_author'           => null,
                'post_date'             => null,
                'post_date_gmt'         => null,
                'post_content'          => null,
                'post_title'            => null,
                'post_excerpt'          => null,
                'post_status'           => null,
                'comment_status'        => null,
                'ping_status'           => null,
                'post_password'         => null,
                'post_name'             => null,
                'to_ping'               => null,
                'pinged'                => null,
                'post_modified'         => null,
                'post_modified_gmt'     => null,
                'post_content_filtered' => null,
                'post_parent'           => null,
                'guid'                  => null,
                'menu_order'            => null,
                'post_type'             => null,
                'post_mime_type'        => null,
                'comment_count'         => null,
            ];
            $this->_empty_post_object = new WP_Post( (object) $vars );
        }
        return $this->_empty_post_object;
    }


}
