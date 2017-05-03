<?php

/**
 * Post select field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\Fields;

/**
 * Class PostSelect
 *
 * @package WPKit\Fields
 */
class PostSelect extends Select2
{
    /**
     * Query arguments
     *
     * @var array
     */
    protected $_query = [];

    /**
     * Set query arguments
     *
     * @param array $query
     */
    public function set_query( array $query )
    {
        foreach ( $query as $key => $value ) {
            $this->add_query_argument( $key, $value );
        }
    }

    /**
     * Add query argument
     *
     * @param string $key
     * @param string $value
     */
    public function add_query_argument( $key, $value )
    {
        $this->_query[ $key ] = $value;
    }

    /**
     * Get query arguments
     *
     * @return array
     */
    protected function get_query()
    {
        return $this->_query;
    }

    /**
     * Filtering field value
     *
     * @param string $value
     * @return int
     */
    public function apply_filter( $value )
    {
        return absint( $value );
    }

    /**
     * Set field value
     *
     * @param $value
     *
     * @return int|null|string
     */
    protected function _set_single_value( $value )
    {
        return static::apply_filter( $value );
    }

    /**
     * Render select field options html
     *
     * @return string
     */
    protected function _render_options()
    {
        $output = $this->get_placeholder() ? '<option value=""></option>' : '';
        $post_id = $this->get_value();

        if ( $post_id ) {
            $output .= "<option selected value=\"$post_id\">" . get_the_title( $post_id ) . "</option>";
        }

        return $output;
    }

    /**
     * Render JavaScript
     *
     * @return string
     */
    protected function _render_javascript()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            jQuery(function ($) {

                'use strict';

                var initPostSelect = function () {
                    var $field = $('select.select2:not(".select2-offscreen")[id^="<?= $this->get_id() ?>"]');

                    $field.select2($.extend({
                        ajax: {
                            url: '<?= esc_url_raw( rest_url( 'wp/v2/posts' ) ) ?>',
                            data: function (params) {
                                var query = $.extend({
                                    search: params.term,
                                    page: params.page
                                }, <?= json_encode( $this->get_query() ) ?>);
                                var selected = $field.val();

                                if (selected) {
                                    query.exclude = selected;
                                }

                                return query;
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map(function (post) {
                                        return {
                                            id: post.id,
                                            text: post.title.rendered
                                        };
                                    })
                                };
                            }
                        }
                    }, <?= json_encode( $this->get_select2_options() ) ?>));
                };

                initPostSelect();
                $(document).on('repeatable_row_added', initPostSelect);
            });
        </script>
        <?php return ob_get_clean();
    }
}