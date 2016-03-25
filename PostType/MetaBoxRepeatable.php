<?php

/**
 * Repeatable meta box builder
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 * @author Vitaly Nikolaev <vitaly@pingbull.no>
 * @author Maksim Viter <maksim@pingbull.no>
 *
 */

namespace WPKit\PostType;

use WPKit\Fields\AbstractField;

class MetaBoxRepeatable extends MetaBox
{
    protected $_limit = null;

    protected $_vertical = false;

    protected function _render($post)
    {
        wp_nonce_field($this->get_key() . "_inner_custom_box", $this->get_key() . "_inner_custom_box_nonce");
        $table_class = $this->_vertical ? ' vertical': "";
        ?>
        <table class="wp-list-table widefat tags<?= $table_class ?>">
            <thead>
                <tr>
                    <th class="check-column" scope="col"></th>
                    <?php foreach($this->_get_fields() as $field): ?>
                        <th scope="col"><span><?= $field->get_label() ?></span></th>
                    <?php endforeach ?>
                    <th scope="col" class="check-column"></th>
                </tr>
            </thead>

            <tbody><?= $this->_render_rows($post) ?></tbody>
        </table>

        <h4><a class="hide-if-no-js wpkit-add-row" href="#" data-limit="<?= (int) $this->_limit ?>">+ Add New Row</a></h4>

        <input type="hidden" value="" name="<?= $this->get_key() ?>_order" />

        <?php
        echo $this->_render_row_template();
        echo $this->_render_javascript();
        echo $this->_render_style();
    }


    protected function _render_rows($post)
    {
        $count = $this->_get_rows_count($post);

        if($count == 0) {
            $count = 1; // show one empty row
        }

        $html = '';
        for($i = 0; $i < $count; $i++) {
            $html .= $this->_render_row($i, $post);
        }
        return $html;
    }

    protected function _render_field($index, AbstractField $field, $value)
    {
        $_field = clone $field;
        $_field->set_value($value);
        $_field->set_name( $field->get_name() . "[$index]" , false );

        return '<td>' . $_field->render_label() . $_field->render_field() . '</td>';
    }

    protected function _render_row($index, $post)
    {
        $class = $index % 2 == 0 ? ' class="alternate"' : '';
        $html = "<tr{$class} data-index=\"{$index}\">";
        $html .= '<th class="plugins drag-handle" scope="row"><i class="dashicons dashicons-menu"></i></th>';
        foreach($this->_get_fields() as $field) {
            $value = $this->_get_field_value($field, $post, $index);
            $html .= $this->_render_field($index, $field, $value);
        }
        $html .= '<th class="plugins"><a href="#" class="delete hide-if-no-js dashicons-before dashicons-no-alt" title="Delete"></a></th>';
        $html .= '</tr>';
        return $html;
    }


    protected function _get_field_value(AbstractField $field, $post, $index)
    {
        if( ! $post ) {
            return null;
        }

        $values = get_post_meta($post->ID, $field->get_name(), true);

        if( !is_array($values) ) {
            return null;
        }

        return array_key_exists($index, $values) ? $values[$index] : null;
    }

    protected function _get_rows_count($post)
    {
        $first_field = current( $this->_get_fields() );
        if( ! $first_field ) {
            return 0;
        }

        $data = get_post_meta($post->ID, $first_field->get_name(), true);

        if( ! is_array($data) ) {
            return 0;
        }

        return count($data);
    }

    protected function _render_row_template()
    {
        $html = '<script id="html-template-' . $this->get_key() . '" type="text/html">';
        $html .= $this->_render_row('__i__', null);
        $html .= '</script>';
        return $html;
    }

    protected function _render_javascript()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            jQuery(function(){
                var $repeatableBox = jQuery('#<?= $this->get_key() ?>');
                var refreshGrid = function() {
                    $repeatableBox.find('table:first tbody tr:visible').removeClass('alternate').filter(':even').addClass('alternate');
                };
                var saveOrderGrid = function() {
                    var order = [];
                    $repeatableBox.find('table:first tbody tr:visible').each(function() {
                        order.push( jQuery(this).attr('data-index') );
                    });
                    $repeatableBox.find('input[name="<?= $this->get_key() ?>_order"]').val( order.join(',') );
                };
                $repeatableBox.find('.wpkit-add-row').click(function(e){
                    e.preventDefault();
                    var html = jQuery('#html-template-<?= $this->get_key() ?>').html();
                    var $table = $repeatableBox.find('table:first');
                    var rows_count = $table.find('tbody tr').size();
                    var limit = parseInt( jQuery(this).data('limit') );
                    if( limit == 0 || limit > rows_count ) {
                        $table.find('tbody').append(html.replace(/__i__/g, rows_count));
                        refreshGrid();
                        jQuery(document).trigger('repeatable_row_added', $table.find('tbody tr:last'), rows_count);
                    }
                    else {
                        alert('Reached the maximum number of fields');
                        jQuery(document).trigger('repeatable_row_limit_reached');
                    }
                });
                $repeatableBox.on('click', 'td > .delete', function(e) {
                    e.preventDefault();
                    if(confirm('<?php _e('Are you sure you want to do this?') ?>')) {
                        jQuery(this).parent('td').parent('tr').remove();
                        refreshGrid();
                        jQuery(document).trigger('repeatable_row_deleted');
                    }
                });

                $repeatableBox.find('tbody').sortable({
                    items: '> tr',
                    axis: 'y',
                    handle: '.drag-handle',
                    placeholder: 'row-placeholder',
                    helper: function(event, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function(index) {
                            jQuery(this).width($originals.eq(index).width());
                        });
                        return $helper;
                    },
                    stop: function(event, ui) {
                        refreshGrid();
                        ui.item.effect('highlight', {color: '#2ea2cc'});
                        saveOrderGrid();
                    },
                    sort: function(event, ui) {
                        $repeatableBox.find('table:first tbody tr:visible').removeClass('alternate');
                    },
                    start: function(event, ui) {
                        ui.placeholder.height(ui.helper.outerHeight());
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    protected function _render_style()
    {
        ob_start();
        ?>
        <style>
            #<?= $this->get_key() ?> tbody .drag-handle { cursor: row-resize; vertical-align: top }
            #<?= $this->get_key() ?> tbody th { width: 20px; vertical-align: top }
            #<?= $this->get_key() ?> tbody label { display: none; }
            #<?= $this->get_key() ?> tbody input.large-text, #<?= $this->get_key() ?> tbody textarea.large-text{ width: 100%}
            #<?= $this->get_key() ?> .vertical thead { display: none; }
            #<?= $this->get_key() ?> .vertical tbody td { display: block; }
            #<?= $this->get_key() ?> .vertical tbody label { display: inline; }
            #<?= $this->get_key() ?> tbody tr.ui-sortable-helper { background: #E5E5E5; opacity: .8; }
            #<?= $this->get_key() ?> tbody tr.row-placeholder { background-color: #F9F9F9; }
        </style>
        <?php
        return ob_get_clean();
    }

    protected function _filter_rows($data)
    {
        if( ! is_array($data) ) {
            return [];
        }

        // calculate rows count
        $count = 0;
        foreach($this->_get_fields() as $field) {
            $key = $field->get_name();
            if( isset($data[ $key ]) && is_array($data[ $key ]) ) {
                $count = max($count, count($data[ $key ]));
            }
            else {
                $data[ $key ] = [];
            }
        }
        $filtered_data = [];

        // filter each value
        foreach($this->_get_fields() as $field) {
            $key = $field->get_name();

            $values = $data[ $key ];
            $filtered_values = [];

            foreach($values as $value){
                $filtered_values[] = $field->apply_filter($value);
            }

            $filtered_data[ $key ] = $filtered_values;
        }

        //reorder data
        /*if( ! empty($data[ $this->get_key() . '_order' ]) ) {


            $order = explode(',', $data[ $this->get_key() . '_order' ]);
            if( is_array($order) ) {
                $ordered_filtered_data = array();
                foreach($order as $index => $key) {

                    foreach($filtered_data as $field_name => $values) {
                        $ordered_filtered_data[ $field_name ][ $index ] = $values[ $key ];
                    }

                }
                $filtered_data = $ordered_filtered_data;
            }
        }*/

        // filter empty rows
        $sorted_filtered_data = [];
        for($i = 0; $i < $count; $i++) {

            $empty = true;
            foreach($filtered_data as $values) {
                if( isset($values[ $i ]) && ! empty($values[ $i ]) ) {
                    $empty = false;
                    break;
                }
            }

            if( ! $empty ) {
                foreach($filtered_data as $field_name => $values) {
                    $sorted_filtered_data[ $field_name ][] = $values[ $i ];
                }
            }
        }
        $filtered_data = $sorted_filtered_data;


        return $filtered_data;
    }

    protected function _save($post_id)
    {
        if( ! $this->_is_able_to_save($post_id) ) {
            return $post_id;
        }

        $data = $this->_filter_rows($_POST);
        foreach ($this->_get_fields() as $field) {
            if(!isset($data[$field->get_name()])){
                delete_post_meta($post_id, $field->get_name());
                continue;
            }
            $values = $data[$field->get_name()];
            if (!empty($values)) {
                update_post_meta($post_id, $field->get_name(), $values);
            } else {
                delete_post_meta($post_id, $field->get_name());
            }
        }
        return $post_id;
    }

    protected function _enqueue_javascript()
    {
        wp_enqueue_script('jquery-effects-highlight');
        parent::_enqueue_javascript();
        add_action('admin_print_footer_scripts', function () {
            echo "<script>
					jQuery(function ($) {
						'use strict';
						var reInitEditor = function (id) {
							tinymce.EditorManager.execCommand('mceRemoveEditor', true, id);
							tinymce.EditorManager.execCommand('mceAddEditor', true, id);
						};
						$(document).on('repeatable_row_added', function (e,el) {
							reInitEditor($(el).find('.wp-editor-area').attr('id'));
							if(typeof wp.wpkit != 'undefined'){
							    if(typeof wp.wpkit.datepicker != 'undefined'){
                                    wp.wpkit.datepicker.reinit();							
							    }
							}
						});
						$('#campaign_slider').on('sortstop', function (event, ui) {
							ui.item.find('.wp-editor-area').each(function () {
								reInitEditor($(this).attr('id'));
							});
						});
					});
				</script>";
        });
    }

    /**
     * Set maximum count of rows
     *
     * @param int $limit
     */
    public function set_limit($limit)
    {
        $limit = intval($limit);
        if ($limit > 0) {
            $this->_limit = $limit;
        } else {
            $this->remove_limit();
        }
    }

    /**
     * Get maximum count of rows
     *
     * @return int
     */
    public function get_limit()
    {
        return $this->_limit;
    }

    /**
     * Remove row count limit
     */
    public function remove_limit()
    {
        $this->_limit = null;
    }

    public function set_vertical($state = true)
    {
        $this->_vertical = $state;
    }
}