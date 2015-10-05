<?php

/**
 * WordPress Tinymce editor field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 * @author Vitaly Nikolaev <vitaly@pingbull.no>
 *
 */

namespace WPKit\Fields;

class WPEditor extends Textarea
{
    /**
     * Render only field html
     *
     * @return string
     */
    public function render_field()
    {
        ob_start();

	    $defaults = [
		    'wpautop' => true,
		    'media_buttons' => false,
		    'default_editor' => '',
		    'drag_drop_upload' => false,
		    'textarea_name' => $this->get_name(),
		    'textarea_rows' => 20,
		    'tabindex' => '',
		    'tabfocus_elements' => ':prev,:next',
		    'editor_css' => '',
		    'editor_class' => '',
		    'teeny' => false,
		    'dfw' => false,
		    '_content_editor_dfw' => false,
		    'tinymce' => true,
		    'quicktags' => true
	    ];

	    $settings = wp_parse_args( $this->_attributes, $defaults );
	    wp_editor( $this->get_value(), $this->get_id(), $settings );

	    return ob_get_clean();
    }

}
