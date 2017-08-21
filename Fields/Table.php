<?php

/**
 * Handsontable field
 *
 * @package   WPKit
 *
 * @link      https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license   http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author    Viktor Kuliebiakin <viktor.kuliebiakin@innocode.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class Table extends Textarea
{
    /**
     * wp_enqueue_script action
     */
    public function enqueue_javascript()
    {
        wp_enqueue_script(
            'handsontable',
            'https://cdnjs.cloudflare.com/ajax/libs/handsontable/0.34.0/handsontable.full.min.js',
            [],
            '0.34.0',
            true
        );
        Script::enqueue_admin_inline_script( 'wpkit-field-table', $this->_render_javascript() );
    }

    protected function _render_javascript()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            (function ($) {

                'use strict';

                var initTable = function (el) {
                    var $field = $(el).prev();

                    var table = new Handsontable(el, {
                        data: $field.val().split('\n').map(function (cols) {
                            return cols.split(',');
                        }),
                        minSpareCols: 1,
                        minSpareRows: 1,
                        rowHeaders: true,
                        colHeaders: true,
                        contextMenu: ['row_above', 'row_below', '---------', 'col_left', 'col_right', '---------', 'remove_row', 'remove_col', '---------', 'undo', 'redo'],
                        stretchH: 'all',
                        height: 300,
                        afterChange: function () {
                            var data;

                            if (table) {
                                data = table.getData();
                                $field.val(data.slice(0, -1).map(function (cols) {
                                    return cols.slice(0, -1).join(',');
                                }).join('\n'));
                            }
                        }
                    });

                    return table;
                };
                var init = function () {
                    $('[data-type="table"]').not('.handsontable').each(function (index, el) {
                        document.tableFields = document.tableFields || [];
                        document.tableFields.push(initTable(el));
                    });
                };

                $(function () {
                     init();
                     $(document).on('repeatable_row_added', init);
                });
            })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * wp_enqueue_style action
     */
    public function enqueue_style()
    {
        wp_enqueue_style(
            'handsontable',
            'https://cdnjs.cloudflare.com/ajax/libs/handsontable/0.34.0/handsontable.full.min.css',
            [],
            '0.34.0'
        );
        wp_add_inline_style( 'handsontable', $this->_render_stylesheets() );
    }

    protected function _render_stylesheets()
    {
        ob_start();
        ?>
        #<?= $this->get_id() ?> + [data-type="table"].handsontable table tbody th { width: auto; }
        .vertical #<?= $this->get_id() ?> + [data-type="table"].handsontable table thead { display: table-header-group; }
        .vertical #<?= $this->get_id() ?> + [data-type="table"].handsontable table tbody td { display: table-cell; }
        <?php
        return ob_get_clean();
    }

    /**
     * Render full field html (with label)
     *
     * @return string
     */
    public function render_field()
    {
        return sprintf(
            '%s<div data-type="table" class="hide-if-no-js"></div>',
            parent::render_field()
        );
    }

    /**
     * Get field reload javascript
     *
     * @return string
     */
    public function reload_javascript()
    {
        return $this->_render_javascript();
    }

    /**
     * @return string
     */
    protected function _get_classes()
    {
        return parent::_get_classes() . ' hide-if-js';
    }
}