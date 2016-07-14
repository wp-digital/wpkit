<?php

/**
 * Image field
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

use WPKit\Exception\WpException;

class Image extends File
{
    protected $_type = 'image';
    protected $_accepts = ['image'];
    protected $_labels = ['title' => 'Set Image', 'upload_button' => 'Set Image', 'remove_button' => 'Remove Image'];

    /**
     * Set file types accepts is not allowed
     *
     * @param array $accepts
     * @throws WpException
     */
    public function set_accepts(array $accepts)
    {
        throw new WpException('Image Field can accept only images.');
    }

    protected function _render_file()
    {
        if ( $this->_value ) {
            add_filter( 'wp_calculate_image_srcset', '__return_false' );
            $image = wp_get_attachment_image( $this->_value, 'thumbnail', true );
            remove_filter( 'wp_calculate_image_srcset', '__return_false' );

            return $image . '<strong style="display: none" data-role="title">' . get_the_title( $this->_value ) . '</strong>';
        } else {
            return '<img style="display: none" src="" alt="" /><strong style="display: none" data-role="title"></strong>';
        }
    }

}