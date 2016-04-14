<?php

/**
 * WordPress meta box with related posts
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Viktor Kuliebiakin <victor@pingbull.no>
 *
 */

namespace WPKit\PostType;

use WP_Query;
use WPKit\Helpers\Script;

//todo: MetaBoxRelatedPosts should not extends MetaBox
class MetaBoxRelatedPosts extends MetaBox
{
	protected $_hidden_field_key = 'posts';
	protected $_posts_per_page = 10;
	protected $_limit = 0;
	protected $_related_post_types = ['post', 'page'];
	protected $_classes = ['large-text'];
	protected $_attributes = ['autocomplete' => 'off'];

	/**
	 * @param $key
	 * @param $title
	 *
	 * @throws \WPKit\Exception\WpException
	 */
	public function __construct( $key, $title )
	{
		parent::__construct( $key, $title );

		$this->add_field( $this->_get_hidden_field_key(), '', 'Hidden' );
		$this->_ajax_actions();
	}

	/**
	 * @param $related_post_types
	 */
	public function add_related_post_types( $related_post_types )
	{
		foreach ( (array) $related_post_types as $related_post_type ) {

			if ( $related_post_type instanceof PostType ) {
				$related_post_type = $related_post_type->get_key();
			}

			if ( !in_array( $related_post_type, $this->_related_post_types ) ) {
				$this->_related_post_types[] = $related_post_type;
			}
		}
	}

	/**
	 * @param array $related_post_types
	 */
	public function set_related_post_types( $related_post_types )
	{
		$this->_related_post_types = (array) $related_post_types;
	}

	/**
	 * @param integer $limit Limit of items to add
	 */
	public function set_items_limit($limit){
		$this->_limit = (int) $limit;
	}

	/**
	 * @return int Limit of posts
	 */
	private function _get_items_limit(){
		return $this->_limit;
	}

	/**
	 * @return string
	 */
	public function get_field_key()
	{
		return $this->get_key() . '_' . $this->_get_hidden_field_key();
	}

	/**
	 * @return string
	 */
	protected function _get_hidden_field_key()
	{
		return $this->_hidden_field_key;
	}

	/**
	 * @param $post_id
	 * @param $meta_box_key
	 *
	 * @return array|null
	 */
	public static function get_field_value( $post_id, $meta_box_key )
	{
		$key = sanitize_key( $meta_box_key ) . '_posts';
		$posts = get_post_meta( $post_id, $key, true );

		return $posts ? explode( ',', $posts ) : null;
	}

	/**
	 * @param $post
	 *
	 * @throws \WPKit\Exception\WpException
	 */
	protected function _render( $post )
	{
		echo $this->_render_search_field( $post->ID );
		echo $this->_render_row_template();
		echo $this->_render_field_javascript( $post->ID );

		parent::_render( $post );
	}

	/**
	 * @return int
	 */
	protected function _get_posts_per_page()
	{
		return $this->_posts_per_page;
	}

	/**
	 * @param $post_id
	 *
	 * @return array|null
	 */
	protected function _get_field_value( $post_id )
	{
		$posts = get_post_meta( $post_id, $this->get_key(), true );

		return $posts ? explode( ',', $posts ) : null;
	}

	protected function _enqueue_javascript()
	{
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-effects-highlight' );

		Script::enqueue_admin_inline_script( 'wpkit-meta-box-related-posts', $this->_render_javascript() );

		parent::_enqueue_javascript();
	}

	protected function _enqueue_style()
	{
		?>
		<style type="text/css">
			 .wpkit-query-results {
				 border: 1px #dfdfdf solid;
				 -webkit-box-sizing: border-box;
				 -moz-box-sizing: border-box;
				 box-sizing: border-box;
				 margin: 15px 1px 1px;
				 background: #fff;
				 overflow: auto;
				 height: 200px;
				 width: 99%;
			 }
			 .wpkit-query-results .query-notice,
			 .wpkit-query-results li {
				 clear: both;
				 margin-bottom: 0;
				 border-bottom: 1px solid #f1f1f1;
				 color: #333;
				 padding: 4px 6px;
				 cursor: pointer;
				 position: relative;
			 }
			 .wpkit-query-results ul {
				 list-style: none;
				 margin: 0;
				 padding: 0;
			 }
			 .wpkit-query-results .waiting {
				 display: none;
				 padding: 10px 0;
			 }
			 .wpkit-query-results .waiting .spinner {
				 margin: 0 auto;
				 display: block;
				 float: none;
			 }
			 .wpkit-query-results li:hover {
				 background: #eaf2fa;
				 color: #151515;
			 }
			 .wpkit-query-results .item-info {
				 text-transform: uppercase;
				 color: #666;
				 font-size: 11px;
				 position: absolute;
				 right: 5px;
				 top: 5px;
			 }
			.wpkit-table .drag-handle {
				cursor: row-resize;
				vertical-align: top
			}
			 .wpkit-table .item-info {
				 text-transform: uppercase;
			 }
		</style>
		<?php
		parent::_enqueue_style();
	}

	/**
	 * @return array
	 */
	protected function _get_related_post_types()
	{
		return $this->_related_post_types;
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function _render_search_field( $post_id )
	{
		return sprintf(
			'<input type="search" id="%s-search-field" class="%s" %s />%s%s%s',
			$this->get_field_key(),
			$this->_get_classes(),
			$this->_get_attributes(),
			$this->_render_search_list(),
			$this->_render_recent_list( $post_id ),
			$this->_render_table( $post_id )
		);
	}

	/**
	 * @return array
	 */
	protected function _get_classes()
	{
		return implode( ' ', $this->_classes );
	}

	/**
	 * @return array
	 */
	protected function _get_attributes()
	{
		$output = '';

		foreach ( $this->_attributes as $key => $value ) {
			$output .= "$key=\"$value\" ";
		}

		return $output;
	}

	/**
	 * @return string
	 */
	protected function _render_search_list()
	{
		return sprintf(
			'<div id="%s-search-list" class="wpkit-query-results hide-if-no-js" style="display: none;"><ul></ul>%s</div>',
			$this->get_field_key(),
			$this->_render_spinner()
		);
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function _render_recent_list( $post_id )
	{
		$posts = (array) $this->get_field_value( $post_id, $this->get_key() );
		array_push($posts, $post_id);

		return sprintf(
			'<div id="%s-recent-list" class="wpkit-query-results hide-if-no-js"><div class="query-notice"><em>%s</em></div><ul>%s</ul>%s</div>',
			$this->get_field_key(),
			__( 'No search term specified. Showing recent items.', 'wpkit' ),
			$this->_get_posts([
				'post__not_in' => $posts,
			]),
			$this->_render_spinner()
		);
	}

	/**
	 * @return string
	 */
	protected function _render_spinner()
	{
		return '<div class="waiting"><span class="spinner"></span></div>';
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function _render_table( $post_id )
	{
		$posts = $this->get_field_value( $post_id, $this->get_key() );

		ob_start();
		?>
		<br />
		<?php if($this->_get_items_limit()): ?>
		<div class="description"><?php _e( 'Limit' ) ?>: <?= $this->_get_items_limit() ?></div>
		<?php endif; ?>
		<table id="<?= $this->get_field_key() ?>-table" class="wpkit-table wp-list-table widefat tags">
			<thead>
				<tr>
					<th class="check-column" scope="col"></th>
					<th scope="col">
						<span><?php _e( 'Title' ) ?></span>
					</th>
					<th scope="col"></th>
					<th class="check-column" scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty( $posts ) ) {
					foreach ( $posts as $id ) {
						echo $this->_render_row( $id );
					}
				} ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param      $id
	 * @param null $title
	 * @param null $post_type
	 *
	 * @return string
	 */
	protected function _render_row( $id, $title = null, $post_type = null )
	{
		if ( empty( $title ) ) {
			$title = get_the_title( $id );

			if ( empty( $title ) ) {
				return null;
			}
		}

		if ( empty( $post_type ) ) {
			$post_type = get_post_type( $id );

			if ( empty( $post_type ) ) {
				return null;
			}
		}

		ob_start();
		?>
		<tr data-id="<?= $id ?>">
			<td class="plugins drag-handle" scope="row">
				<i class="dashicons dashicons-menu"></i>
			</td>
			<td>
				<?= $title ?>
			</td>
			<td class="item-info">
				<?= $post_type ?>
			</td>
			<td class="plugins">
				<a href="#" class="delete hide-if-no-js dashicons-before dashicons-no-alt" title="<?php _e( 'Delete' ) ?>"></a>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	protected function _render_row_template()
	{
		ob_start();
		?>
		<script id="<?= $this->get_field_key() ?>-tmpl" type="text/html">
			<?= $this->_render_row( '<%= id %>', '<%= title %>', '<%= postType %>' ) ?>
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	protected function _get_posts( $args = [] )
	{
		$posts = '';
		global $post;
		$native_post = $post;
		$the_query = new WP_Query( wp_parse_args( $args, [
			'posts_per_page' => $this->_get_posts_per_page(),
		    'post_type'      => $this->_get_related_post_types(),
			'post_status'    => 'publish',
		] ) );

		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$posts .= '<li data-id="' . get_the_ID() . '"><span class="item-title">' . get_the_title() .  '</span><span class="item-info">' . get_post_type() . '</span></li>';
			}
		}

		unset( $the_query );
		$post = $native_post;
		wp_reset_postdata();

		return $posts;
	}

	protected function _ajax_actions()
	{
		add_action( 'wp_ajax_wpkit_related_posts', function () {

			if ( !empty( $_POST['s'] ) || !empty( $_POST['paged'] ) ) {
				$args['paged'] = !empty( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

				if ( !empty( $_POST['posts_per_page'] ) ) {
					$args['posts_per_page'] = absint( $_POST['posts_per_page'] );
				}

				if ( !empty( $_POST['post_type'] ) ) {
					$args['post_type'] = explode( ',', wp_unslash( $_POST['post_type'] ) );
				}

				if ( !empty( $_POST['post__not_in'] ) ) {
					$args['post__not_in'] = [ absint( $_POST['post__not_in'] ) ];
				}

				if ( !empty( $_POST['s'] ) ) {
					$args['s'] = wp_unslash( $_POST['s'] );
				}

				wp_send_json_success( $this->_get_posts( $args ) );
			}

			wp_send_json_error( 'Something wrong with your request.' );
		});
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function _render_field_javascript( $post_id )
	{
		ob_start();
		?>
		<script type="text/javascript">

			jQuery(function () {
				new WPKit.MetaBox.RelatedPosts({
					selector: '<?= $this->get_field_key() ?>',
					perPage: <?= $this->_get_posts_per_page() ?>,
					postNotIn: <?= $post_id ?>,
					postType: '<?= implode( ',', $this->_get_related_post_types() ) ?>',
					limit: <?= $this->_get_items_limit() ?>
				});
			});

		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	protected function _render_javascript()
	{
		ob_start();
		?>
		<script type="text/javascript">

			(function ($, window) {

				'use strict';

				window.WPKit = window.WPKit || {};
				window.WPKit.MetaBox = window.WPKit.MetaBox || {};

				window.WPKit.MetaBox.RelatedPosts = (function () {
					var _busy = false;

					function RelatedPosts(field) {
						this.hold = false;
						this.page = 1;
						this.s = null;
						this.perPage = field.perPage;
						this.postNotIn = field.postNotIn;
						this.postType = field.postType;
						this.limit = field.limit;
						this.$searchField = $('#' + field.selector + '-search-field');
						this.$searchList = $('#' + field.selector + '-search-list');
						this.$recentList = $('#' + field.selector + '-recent-list');
						this.$table = $('#' + field.selector + '-table');
						this.$tmpl = $('#' + field.selector + '-tmpl');
						this.$field = $('#' + field.selector);
						this.template = _.template(this.$tmpl.html());

						this.initSortable();
						this.highlight();
						this.addEventListeners();
					}

					RelatedPosts.prototype.setBusy = function () {

						if (_busy) {
							return false;
						} else {
							_busy = true;
							this.$searchList.add(this.$recentList)
											.filter(':visible')
											.find('.waiting')
											.show();

							return true;
						}
					};

					RelatedPosts.prototype.unsetBusy = function () {

						if (_busy) {
							_busy = false;
							this.$searchList.add(this.$recentList)
											.filter(':visible')
											.find('.waiting')
											.hide();

							return true;
						} else {
							return false;
						}
					};

					RelatedPosts.prototype.addEventListeners = function () {
						this.searchFieldListener();
						this.scrollListListener();
						this.itemSelectListener();
						this.removeItemListener();
					};

					RelatedPosts.prototype.searchFieldListener = function () {

						this.$searchField.on('input change', (function (self) {
							return function () {

								if (!_busy && this.value.length > 1) {
									self.showSearch();

									if (self.s !== this.value) {
										self.page = 1;
										self.s = this.value;
										self.loadPosts();
									}
								} else if (!_busy) {
									self.s = null;
									self.showRecent();
								}
							};
						})(this));
					};

					RelatedPosts.prototype.scrollListListener = function () {

						this.$searchList.add(this.$recentList).on('scroll', (function (self) {
							return function () {
								var page;

								if (!self.hold && !_busy && self.maybeLoad($(this))) {
									page = $(this).find('li').length / self.perPage + 1;

									if (page > 1 && page % 1 === 0) {
										self.page = page + 1;
										self.loadPosts();
									} else {
										self.hold = true;
									}
								}
							};
						})(this));
					};

					RelatedPosts.prototype.itemSelectListener = function () {

						this.$searchList.add(this.$recentList).on('click', 'li', (function (self) {
							return function () {
								if(self.limit > 0 && self.limit <= self.$table.find('tbody tr').length){
									alert('You have reached the limit: '+self.limit);
									return;
								}
								var data = {
									id: $(this).data('id'),
									title: $(this).find('.item-title').text(),
									postType: $(this).find('.item-info').text()
								};

								if (data.id && data.title) {
									self.$table.find('tbody').append(self.template(data));
									self.initSortable();
									self.highlight();
									self.save();
									$(this).hide();
									self.$searchList.trigger('scroll');
								}
							};
						})(this));
					};

					RelatedPosts.prototype.removeItemListener = function () {

						this.$table.on('click', '.delete', (function (self) {
							return function (event) {
								event.preventDefault();
								var msg = '<?php _e( 'Are u sure?' ) ?>';

								if (commonL10n && commonL10n.hasOwnProperty('warnDelete')) {
									msg = commonL10n.warnDelete;
								}

								if (confirm(msg)) {
									$(this).parents('tr').remove();
									self.$searchList.add(self.$recentList).find('[data-id="' + $(this).parents('tr').data('id') + '"]').show();
									self.highlight();
									self.save();
								}
							};
						})(this));
					};

					RelatedPosts.prototype.maybeLoad = function ($target) {
						return $target.scrollTop() >= ($target.find('ul').height() - $target.height()) * 0.7;
					};

					RelatedPosts.prototype.loadPosts = function () {
						(function (self) {

							if (ajaxurl) {
								$.ajax({
									url: ajaxurl,
									data: {
										action: 'wpkit_related_posts',
										paged: self.page,
										posts_per_page: self.perPage,
										post_type: self.postType,
										post__not_in: self.postNotIn,
										s: self.s
									},
									method: 'post',
									beforeSend: function () {
										self.setBusy();
									},
									success: function (response) {

										if (response.success && response.data.length) {

											if (self.page > 1) {

												if (!self.s) {
													self.showRecent();
													self.$recentList.find('ul')
																	.append(response.data);
												} else {
													self.$searchList.find('ul')
																	.append(response.data);
												}
											} else {
												self.hold = false;
												self.$searchList.find('ul')
																.html(response.data);
											}
										} else {
											self.hold = true;
										}
									},
									complete: function () {
										self.unsetBusy();
										self.highlight();
									}
								});
							}
						})(this);
					};

					RelatedPosts.prototype.initSortable = function () {
						(function (self) {
							self.$table.find('tbody').sortable({
								items: '> tr',
								axis: 'y',
								handle: '.drag-handle',
								stop: function () {
									self.highlight();
									self.save();
								}
							});
						})(this);
					};

					RelatedPosts.prototype.save = function () {
						var data = [];

						this.$table.find('tbody > tr').each(function () {
							var id = $(this).data('id');

							if (id) {
								data.push(id);
							}
						});

						this.$field.val(data.join(','));
					};

					RelatedPosts.prototype.showRecent = function () {
						this.$searchList.hide().find('ul').empty();
						this.$recentList.show();
					};

					RelatedPosts.prototype.showSearch = function () {
						this.$recentList.hide();
						this.$searchList.show();
					};

					RelatedPosts.prototype.highlight = function () {
						this.$searchList.add(this.$recentList).find('li')
										.add(this.$table.find('tr'))
										.removeClass('alternate')
										.filter(':even')
										.addClass('alternate');
					};

					return RelatedPosts;
				})();

			})(jQuery, window);

		</script>
		<?php
		return ob_get_clean();
	}
}