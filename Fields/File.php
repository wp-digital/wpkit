<?php

/**
 * File field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class File extends AbstractField
{
    protected $_type = 'file';
    protected $_classes = ['button'];
    protected $_accepts = ['image', 'video', 'text', 'audio', 'application'];
    protected $_labels = ['title' => 'Set File', 'upload_button' => 'Set File', 'remove_button' => 'Remove File'];

    /**
     * Render full field html (with label)
     *
     * @return string
     */
    public function render()
    {
        $label = $this->render_label();
        $output = '<div class="form-group" style="margin-bottom: 15px">';
        $output .= $label ? $label . '<br/>' : '';
        $output .= $this->render_field();
        $output .= '</div>';
        return $output;
    }

    /**
     * wp_enqueue_script action
     */
    public function enqueue_javascript()
    {
        wp_enqueue_media();
        Script::enqueue_admin_inline_script('wpkit-field-file', $this->_render_javascript());
    }

    /**
     * Render only label html
     *
     * @return string
     */
    public function render_label()
    {
        if($this->get_label()) {
            return sprintf('<label style="font-weight: bold">%s</label>', $this->get_label());
        }
        return '';
    }

    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        $class = $this->_value ? '' : $this->_get_classes();

        return sprintf(
            '<span data-type="file"><a id="%s" href="#" data-role="file-load" data-title="%s" data-button_text="%s" data-accepts="%s" class="%s">%s%s</a>%s<br />%s</span>%s',
            $this->get_id(),
            $this->_labels['title'],
            $this->_labels['upload_button'],
            implode(', ', $this->_accepts),
            $class,
            $this->_render_button_label(),
            $this->_render_file(),
            $this->_render_hidden_input(),
            $this->_render_remove_button(),
            $this->_get_description()
        );
    }

    /**
     * Set file types accepts
     *
     * @param array $accepts
     */
    public function set_accepts(array $accepts)
    {
        $this->_accepts = $accepts;
    }

    protected function _render_file()
    {
        if ( $this->_value ) {
            return wp_get_attachment_image( $this->_value, [80, 60], true ) . '<br /><strong data-role="title">' . get_the_title( $this->_value ) . '</strong>';
        } else {
            return '<img style="display: none" src="" alt="" height="60" /><br /><strong data-role="title"></strong>';
        }
    }

    protected function _render_hidden_input()
    {
        return sprintf(
            '<input id="hidden_%s" data-role="value" type="hidden" name="%s" value="%s" />',
            $this->get_id(),
            $this->get_name(),
            $this->get_value()
        );
    }

    protected function _render_button_label()
    {
        $attr = '';

        if ( $this->_value ) {
            $attr = 'style="display: none"';
        }

        return sprintf( '<span %s>%s</span>', $attr, $this->_labels['upload_button'] );
    }

    protected function _render_remove_button()
    {
        $attr = '';

        if ( !$this->_value ) {
            $attr = 'style="display: none"';
        }

        return sprintf( '<a id="remove_%s" data-role="file-remove" href="#" %s>%s</a>', $this->get_id(), $attr, $this->_labels['remove_button'] );
    }

    protected function _render_javascript()
    {
        ob_start();
        ?>
        <script>
            jQuery(function () {
                var wp_media_file_frame = null;

                jQuery('body').on('click', '[data-role="file-load"]', function (event) {
                    event.preventDefault();

                    var $parent = jQuery(this).parent('[data-type="file"]').first(),
                        $button = jQuery(this),
                        $button_remove = $parent.find('[data-role="file-remove"]'),
                        $field = $parent.find('[data-role="value"]'),
                        $title = $parent.find('[data-role="title"]'),
                        title = $button.data('title'),
                        button_text = $button.data('button_text'),
                        accepts = $button.data('accepts');

                    if (wp_media_file_frame != null) {
                        wp_media_file_frame.close();
                    }

                    wp_media_file_frame = wp.media.frames.customHeader = wp.media({
                        title: title,
                        button: {
                            text: button_text
                        },
                        library: {
                            type: accepts
                        },
                        multiple: false
                    });

                    wp_media_file_frame.on('select', function() {
                        var _attachment = wp_media_file_frame.state().get('selection').first().toJSON(),
                            _url = _attachment.icon;

                        if(_attachment.mime.indexOf('image') != -1) {

                            if (typeof _attachment.sizes.thumbnail !== 'undefined' && _attachment.sizes.thumbnail !== null) {
                                _url = _attachment.sizes.thumbnail.url;
                            }
                            else {
                                _url = _attachment.sizes.full.url;
                            }
                        }

                        $field.val(_attachment.id);
                        $title.text(_attachment.name);
                        $button.removeAttr('class').find('img').show().attr('src', _url);
                        $button.find('span').hide();
                        $button_remove.show();
                    });

                    wp_media_file_frame.on('open', function() {
                        if ($field.val() != '' && wp_media_file_frame.state().get('selection').length == 0) {
                            wp_media_file_frame.state().get('selection').push(wp.media.attachment($field.val()));
                        }
                    });

                    wp_media_file_frame.open();
                }).on('click', '[data-role="file-remove"]', function(event){
                    event.preventDefault();

                    var $parent = jQuery(this).parent('[data-type="file"]').first(),
                        $button = $parent.find('[data-role="file-load"]'),
                        $button_remove = jQuery(this),
                        $field = $parent.find('[data-role="value"]'),
                        $title = $parent.find('[data-role="title"]');

                    $field.val('');
                    $title.empty();
                    $button.attr('class', '<?= $this->_get_classes() ?>').find('img').hide().attr('src', '');
                    $button.find('span').show();
                    $button_remove.hide();
                });

            });
        </script>
        <?php
        return ob_get_clean();
    }
}