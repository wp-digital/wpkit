<?php

/**
 * Google map field
 *
 * @package   WPKit
 *
 * @link      https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license   http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author    Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\Fields;

use WPKit\Helpers\Script;

class Map extends AbstractField {
	protected $_attributes = ['data-type' => 'map', 'data-map-type' => 'ROADMAP', 'data-zoom' => 14];
	protected $_type = 'hidden';
	protected $_size = ['width' => '100%', 'height' => '350px'];
	protected $_classes = ['large-text'];
	protected $_placeholder = 'Address';
	protected $_description = 'You can select the location by moving the marker, or by entering the coordinates in Latitude and Longitude fields, or by writing address to Address field.';

	/**
	 * wp_enqueue_script action
	 */
	public function enqueue_javascript()
	{
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('google-maps-api', '//maps.google.com/maps/api/js', [], false, true);
		Script::enqueue_admin_inline_script('wpkit-field-map', $this->_render_javascript());
	}

	protected function _render_javascript()
	{
		ob_start();
		?>
		<script type="text/javascript">

			(function () {

				'use strict';

				var MapField = (function () {
					var self = {},
						_$map_field = null,
						_$map_address_field = null,
						_$map_latitude_field = null,
						_$map_longitude_field = null,
						_$reset_button = null,
						_coordinates = null,
						_default = {
							latitude: 59.8938549,
							longitude: 10.7851165,
							map_type: 'ROADMAP',
							zoom: 14,
                            scrollwheel: false
						},
						_geocoder = null,
						_map = null,
						_map_container = null,
						_map_type = null,
						_marker = null,
						_options = {
							streetViewControl: false
						},
						_timer = 0,
						_zoom = null;

					function MapField($map_field) {
						self = this;

						this.initialize_html_objects($map_field);

						_coordinates = _$map_field.val();
						_map_type = _$map_address_field.data('map-type');
						_zoom = _$map_address_field.data('zoom');

						this.set_center();
						this.set_type();
						this.set_zoom();
						this.initialize_geocoder();
						this.initialize_map();
						this.initialize_marker();
						this.add_autocomplete();
						this.add_listeners();
					}

					MapField.prototype.initialize_html_objects = function ($map_field) {
						var id;

						_$map_field = $map_field;
						_$map_field.addClass('rendered');
						id = _$map_field.attr('id');
						_$map_address_field = jQuery('#' + id + '_address');
						_$map_latitude_field = jQuery('#' + id + '_latitude');
						_$map_longitude_field = jQuery('#' + id + '_longitude');
						_$reset_button = jQuery('#' + id + '_reset');
						_map_container = document.getElementById(id + '_container');
						jQuery(_map_container).resize(function () {
							setTimeout(function () {
								google.maps.event.trigger(_map, 'resize');
							}, 3000);
						});
					};

					MapField.prototype.initialize_map = function () {
						_map = new google.maps.Map(_map_container, _options);

						if (_$map_field.val()) {
							this.update_coordinates(_options.center);
						}
					};

					MapField.prototype.initialize_marker = function () {
						_marker = new google.maps.Marker({
							position: _options.center,
							map: _map,
							draggable: true
						});
					};

					MapField.prototype.initialize_geocoder = function () {
						_geocoder = new google.maps.Geocoder();
					};

					MapField.prototype.set_zoom = function () {

						if (_zoom) {
							_options.zoom = _zoom;
						} else {
							_options.zoom = _default.zoom;
						}
					};

					MapField.prototype.set_center = function () {
						var coordinatesArray = [],
							latitude,
							longitude;

						if (_coordinates) {
							coordinatesArray = _coordinates.toString().split(',');
							latitude = coordinatesArray[0];
							longitude = coordinatesArray[1];
						} else {
							latitude = _default.latitude;
							longitude = _default.longitude;
						}
						_options.center = new google.maps.LatLng(latitude, longitude);
					};

					MapField.prototype.set_type = function () {

						if (_map_type) {
							_options.mapTypeId = google.maps.MapTypeId[_map_type];
						} else {
							_options.mapTypeId = google.maps.MapTypeId[_default.map_type];
						}
					};

					MapField.prototype.add_listeners = function () {

						(function (map, marker, $map_field, $map_address_field, $map_latitude_field, $map_longitude_field) {
							var prev_value;

							google.maps.event.addListener(_marker, 'drag', function (event) {
								self.update_coordinates(event.latLng, $map_field, $map_address_field, $map_latitude_field, $map_longitude_field);
							});

							$map_latitude_field.add($map_longitude_field).on('focus', function () {
								prev_value = jQuery(this).val();
							}).on('change', function () {
								var id = $map_field.attr('id'),
									latitude = jQuery('#' + id + '_latitude').val(),
									longitude = jQuery('#' + id + '_longitude').val(),
									lat_lng = new google.maps.LatLng(latitude, longitude);

								if (!isNaN(lat_lng.lat()) && !isNaN(lat_lng.lng())) {
									map.setCenter(lat_lng);
									marker.setPosition(lat_lng);
									self.update_coordinates(lat_lng, $map_field, $map_address_field, $map_latitude_field, $map_longitude_field);
								} else {
									jQuery(this).val(prev_value);
								}
							});

							_$reset_button.on('click', function (event) {
								event.preventDefault();

								self.reset($map_field, $map_address_field, $map_latitude_field, $map_longitude_field);
							});
						})(_map, _marker, _$map_field, _$map_address_field, _$map_latitude_field, _$map_longitude_field);
					};

					MapField.prototype.update_coordinates = function (lat_lng, $map_field, $map_address_field, $map_latitude_field, $map_longitude_field) {

						if (typeof $map_field === 'undefined' || $map_field === null) {
							$map_field = _$map_field;
						}

						if (typeof $map_address_field === 'undefined' || $map_address_field === null) {
							$map_address_field = _$map_address_field;
						}

						if (typeof $map_latitude_field === 'undefined' || $map_latitude_field === null) {
							$map_latitude_field = _$map_latitude_field;
						}

						if (typeof $map_longitude_field === 'undefined' || $map_longitude_field === null) {
							$map_longitude_field = _$map_longitude_field;
						}

						if (typeof lat_lng.lat === 'function' && typeof lat_lng.lng === 'function') {
							$map_field.val(lat_lng.lat() + ',' + lat_lng.lng());
							$map_latitude_field.val(lat_lng.lat());
							$map_longitude_field.val(lat_lng.lng());

							if (!_timer) {
								this.call_geocoder(lat_lng, $map_address_field);
							} else {
								$map_address_field.addClass('ui-autocomplete-loading').val('');
							}

							_timer && clearTimeout(_timer);
							_timer = setTimeout(function () {
								self.call_geocoder(lat_lng, $map_address_field);
							}, 1000);
						}
					};

					MapField.prototype.add_autocomplete = function () {

						(function (map, marker, $map_field, $map_address_field, $map_latitude_field, $map_longitude_field) {
							_$map_address_field.autocomplete({
								source: function (request, response) {
									var requested_value = request.term;
									_geocoder.geocode({'address': requested_value}, function (results, status) {
										if (status === google.maps.GeocoderStatus.OK) {
											response(jQuery.map(results, function (item) {
												return {
													value: item.formatted_address,
													latitude: item.geometry.location.lat(),
													longitude: item.geometry.location.lng()
												};
											}));
										} else {
											response([{label: 'No matches found', value: requested_value}]);
										}
									});
								},
								select: function (event, ui) {
									if (typeof ui.item.latitude == 'undefined') {
										return;
									}
									var lat_lng = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);

									map.setCenter(lat_lng);
									marker.setPosition(lat_lng);
									self.update_coordinates(lat_lng, $map_field, $map_address_field, $map_latitude_field, $map_longitude_field);
								}
							});
						})(_map, _marker, _$map_field, _$map_address_field, _$map_latitude_field, _$map_longitude_field);
					};

					MapField.prototype.call_geocoder = function (lat_lng, $map_address_field) {

						if (typeof lat_lng.lat === 'function' && typeof lat_lng.lng === 'function') {
							_geocoder.geocode({'location': lat_lng}, function (results, status) {

								if (status === google.maps.GeocoderStatus.OK) {
									$map_address_field.val(results[0].formatted_address);
								} else {
									$map_address_field.val('');
								}
								$map_address_field.removeClass('ui-autocomplete-loading');
							});
						}
					};

					MapField.prototype.reset = function ($map_field, $map_address_field, $map_latitude_field, $map_longitude_field) {

						if (typeof $map_field === 'undefined' || $map_field === null) {
							$map_field = _$map_field;
						}

						if (typeof $map_address_field === 'undefined' || $map_address_field === null) {
							$map_address_field = _$map_address_field;
						}

						if (typeof $map_latitude_field === 'undefined' || $map_latitude_field === null) {
							$map_latitude_field = _$map_latitude_field;
						}

						if (typeof $map_longitude_field === 'undefined' || $map_longitude_field === null) {
							$map_longitude_field = _$map_longitude_field;
						}
						$map_field.val('');
						$map_address_field.val('');
						$map_latitude_field.val('');
						$map_longitude_field.val('');
					};

					return MapField;
				})();
				var InitMap = function () {
					jQuery('[data-type="map"]').not('.rendered').each(function () {
						if (typeof document.mapFields == 'undefined') {
							document.mapFields = [];
						}
						document.mapFields.push(new MapField(jQuery(this)));

					});
				};

				jQuery(function () {
					jQuery(document).on('ready repeatable_row_added', InitMap);
				});

			})();
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * wp_enqueue_style action
	 */
	public function enqueue_style()
	{
		global $wp_scripts;
		$ui = $wp_scripts->query('jquery-ui-core');
		wp_enqueue_style('jquery-ui-smoothness', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css");
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
		return $this->_esc_coordinates($value);
	}

	protected function _esc_coordinates($value)
	{
		return preg_match('/(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)/', $value) ? $value : '';
	}

	/**
	 * Render full field html (with label)
	 *
	 * @return string
	 */
	public function render_field()
	{
		return sprintf(
			'<p>
				<input type="text" id="%s_address" class="%s" placeholder="%s" />
				<input type="%s" name="%s" id="%s" value="%s" %s />
			</p>
			<p>
				<input type="text" id="%s_latitude" value="%s" placeholder="%s" style="width: 40%%;" />
				<input type="text" id="%s_longitude" value="%s" placeholder="%s" style="width: 40%%;" />
			</p>
			%s
			<p>
				<button id="%s_reset" class="button">%s</button>
			</p>

			<p id="%s_container" style="%s"></p>',
			$this->get_id(),
			$this->_get_classes(),
			$this->get_placeholder(),
			$this->get_type(),
			$this->get_name(),
			$this->get_id(),
			$this->get_value(),
			$this->_get_attributes(),
			$this->get_id(),
			$this->_get_latitude(),
			__('Latitude', 'wpkit'),
			$this->get_id(),
			$this->_get_longitude(),
			__('Longitude', 'wpkit'),
			$this->_get_description(),
			$this->get_id(),
			__('Reset', 'wpkit'),
			$this->get_id(),
			$this->_get_size()
		);
	}

	protected function _get_latitude()
	{
		$coordinates = explode(',', $this->get_value());

		return !empty($coordinates[0]) ? trim($coordinates[0]) : null;
	}

	protected function _get_longitude()
	{
		$coordinates = explode(',', $this->get_value());

		return !empty($coordinates[1]) ? trim($coordinates[1]) : null;
	}

	protected function _get_size()
	{
		return implode(' ', array_map(function ($v, $k) {
			return sprintf('%s: %s;', $k, $v);
		}, $this->_size, array_keys($this->_size)));
	}

	/**
	 * Set map size ['width' => '100%', 'height' => '350px']
	 *
	 * @param array $size
	 */
	public function set_size(array $size)
	{
		$this->_size = wp_parse_args($this->_size, $size);
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
}