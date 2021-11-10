<?php

/**
 * Video field. Supports all oEmbeds from WordPress
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

class Video extends AbstractField {
	protected $_type = 'video';

	protected $_video = null;

	protected $_classes = ['large-text'];

	public function __construct()
	{
		$this->set_description('Add video url like: <span style="font-family: monospace;white-space: pre;">http://www.youtube.com/watch?v=iwGFalTRHDA</span>');
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
		if($this->get_value()){
			require_once( ABSPATH . WPINC . '/class-wp-oembed.php' );
			/* @var \WP_oEmbed $oembed */
			$oembed = _wp_oembed_get_object();
			$provider = $oembed->get_provider( $this->get_value() );
			$data = $oembed->fetch($provider,$this->get_value());
			$this->_video = $data;
		}
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
		if(!$this->get_value()){
			return '';
		}
		if (!$this->_video) {
			return 'Wrong url entered';
		}
		add_thickbox();
		preg_match('/src="([^"]+)"/', $this->_video->html, $match);
		return sprintf('
			<dl>
				<dt>Title:</dt>
			   		<dd>%s</dd>
			   	<dt>Preview:</dt>
					<dd>
						<a href="%s&TB_iframe=true&width=%d&height=%d" class="thickbox">
							<img src="%s" alt="%s" style="max-width: 150px;">
						</a>
					</dd>
			</dl>

			',
			$this->_video->title,
			$match[1],
			$this->_video->width,
			$this->_video->height,
			$this->_video->thumbnail_url,
			$this->_video->title
		);
	}
}
