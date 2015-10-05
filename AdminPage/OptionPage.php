<?php

/**
 * Class for WordPress admin page with options boxes
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

use WPKit\Options\OptionBox;

class OptionPage extends AbstractPage
{
    /**
     * Get page content html
     *
     * @return string
     */
	public function render()
	{
		print '<div class="wrap">';
		print "<h2>{$this->_title}</h2>";
		print '<form action="' . admin_url('options.php') . '" method="post" enctype="modules/x-www-form-urlencoded">';
		settings_fields($this->_key);
		do_settings_sections($this->_key);
		submit_button();
		print '</form>';
		print '</div>';
	}

    /**
     * Add OptionBox to display on admin page
     *
     * @param OptionBox $box
     */
	public function add_box(OptionBox $box)
	{
		$box->set_page($this);
	}

}