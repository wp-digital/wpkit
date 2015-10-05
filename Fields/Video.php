<?php

/**
 * Video field. Now supports only Youtube
 *
 * @use       Integrations\Youtube
 *
 * @package   WPKit
 *
 * @link      https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license   http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author    Vitaly Nikolaev <vitaly@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Integrations\Youtube;

class Video extends AbstractField {
	protected $_type = 'video';

	/* @var Youtube $_video */
	protected $_video = null;

	protected $_classes = ['large-text'];

	public function __construct()
	{
		$this->set_description('Add video url like: <span style="font-family: monospace;white-space: pre;">http://youtube.com/watch?v=8Vc-69M-UWk</span> <br>Supported services: YouTube');
	}

	/**
	 * Render full field html (with label)
	 *
	 * @return string
	 */
	public function render()
	{
		$label  = $this->render_label();
		$output = '<div class="form-group" style="margin-bottom: 15px; word-break: break-word;">';
		$output .= $label ? $label . '<br/>' : '';
		$output .= $this->render_field();
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render only field html
	 *
	 * @return string
	 */
	public function render_field()
	{
		$this->_video = new Youtube();
		$this->_video->set_url($this->get_value());

		return sprintf(
			'<input type="text" name="%s" id="%s" class="video_field %s" value="%s" placeholder="%s" %s />
			 %s<div class="video_info">%s</div>',
			$this->get_name(),
			$this->get_id(),
			$this->_get_classes(),
			$this->get_value(),
			$this->get_placeholder(),
			$this->_get_attributes(),
			$this->_get_description(),
			$this->_get_video_info()
		);
	}

	/**
	 * Filtering field value
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function apply_filter($value)
	{
		return esc_url($value);
	}

	private function _get_video_info()
	{
		$info = $this->_video->get_info();
		if ( !empty($this->_value) && empty($info)) {
			return 'Wrong url entered';
		}
		if ( !is_array($info)) {
			return $info;
		}
		add_thickbox();

		return sprintf('
			<dl>
				<dt>Title:</dt>
			   		<dd>%s</dd>
			   	<dt>Description:</dt>
			   		<dd>%s</dd>
			   	<dt>Preview:</dt>
					<dd>
						<a href="%s&TB_iframe=true&width=600&height=550" class="thickbox">
							<img src="%s" alt="%s">
						</a>
					</dd>
				<dt>Duration:</dt>
			   		<dd>%s</dd>
			</dl>

			',
			$this->_video->get_title(),
			wp_trim_words($this->_video->get_description()),
			$this->_video->get_preview(),
			$this->_video->get_thumbnail(),
			$this->_video->get_title(),
			gmdate("H:i:s", $this->_video->get_duration())
		);
	}
}
