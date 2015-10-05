<?php

/**
 * Instagram API
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Vitaly Nikolaev <vitaly@pingbull.no>
 *
 */

namespace WPKit\Integrations;

use WPKit\Exception\WpException;

class Instagram
{
	/**
	 * Client ID from Instagram
	 * @var string
	 */
	private $_client_id = null;

	/**
	 * Access Token from Instagram
	 * @var string
	 */
	private $_access_token = null;

	/**
	 * Additional parameters from Instagram API
	 *
	 * @var array
	 */
	private $_params = [];

	function __construct($access_token = null, $params = [])
	{
		if (! is_null($access_token)) {
			$this->set_access_token($access_token);
		}
		$this->_params = (array) $params;
	}

	/**
	 * Set access token
	 *
	 * @param string $access_token
	 */
	public function set_access_token($access_token)
	{
		$this->_access_token = $access_token;
	}

	/**
	 * Get access token
	 *
	 * @return string
	 */
	public function get_access_token()
	{
		return $this->_access_token;
	}

	/**
	 * Set client id
	 *
	 * @param string $client_id
	 */
	public function set_client_id($client_id)
	{
		$this->_client_id = $client_id;
	}

	/**
	 * Get client id
	 *
	 * @return string
	 */
	public function get_client_id()
	{
		return $this->_client_id;
	}

	/**
	 * Set additional instagram params
	 *
	 * @param array $params
	 */
	public function set_params( array $params )
	{
        foreach ($params as $key => $param) {
            $this->_params[$key] = $param;
        }
	}

	/**
	 * Get params
	 *
	 * @return array
	 */
	public function get_params()
	{
		return $this->_params;
	}

	/**
	 * Get feed by user or hashtag from cache or generate new
	 *
	 * @param     $value
	 * @param int $page
	 *
	 * @return bool|mixed|object|\stdClass|string
	 */
	public function get_feed($value, $page = 0)
	{
		if (strpos($value, '@') !== false) {
			return $this->get_feed_by_user($value, $page);
		} else if (strpos($value, '#') !== false) {
			return $this->get_feed_by_tag($value, $page);
		}

		return 'There are wrong value entered - ' . $value;
	}

	/**
	 * Update feed cache
	 *
	 * @param     $value
	 * @param int $page
	 *
	 * @return bool|object|\stdClass|string
	 */
	public function update_feed($value, $page = 0)
	{
		if (strpos($value, '@') !== false) {
			return $this->update_feed_by_user($value, $page);
		} else if (strpos($value, '#') !== false) {
			return $this->update_feed_by_tag($value, $page);
		}

		return 'There are wrong value entered - ' . $value;
	}

	/**
	 * Get feed by tag from cache
	 *
	 * @param     $tag
	 * @param int $page
	 *
	 * @return mixed
	 */
	public function get_feed_by_tag($tag, $page = 0)
	{
		if (! $feed = $this->_get_cached_feed($tag, $page)) {
			$feed = $this->update_feed_by_tag($tag, $page);
		}

		return $feed->images;
	}

	/**
	 * Update feed by tag cache
	 *
	 * @param     $tag
	 * @param int $page
	 *
	 * @return bool|object|\stdClass
	 * @throws WpException
	 */
	protected function update_feed_by_tag($tag, $page = 0)
	{
		$tag  = trim($tag);
		$stag = urlencode(str_replace('#', '', trim($tag)));
		$feed = new \stdClass();
		if ($page > 0) {
			$prev_feed = $this->_get_cached_feed($tag, $page - 1);
			if ($prev_feed->next_max_id == 0) {
				return false;
			}
			$this->_params['max_tag_id'] = $prev_feed->next_max_id;
		}
		$url = "https://api.instagram.com/v1/tags/$stag/media/recent?" . $this->_build_request_params();

		$body        = $this->_make_request($url);
		$next_max_id = isset($body->pagination->next_max_id) ? $body->pagination->next_max_id : 0;
		$feed        = (object) ['next_max_id' => $next_max_id, 'images' => $body->data];
		$this->_set_cached_feed($feed, $tag, $page);

		return $feed;
	}

	/**
	 * get feed by user from cache
	 *
	 * @param     $user
	 * @param int $page
	 *
	 * @return bool|mixed|object|\stdClass|string
	 */
	public function get_feed_by_user($user, $page = 0)
	{
		if ( !$feed = $this->_get_cached_feed($user, $page)) {
			$feed = $this->update_feed_by_user($user, $page = 0);
		}

		return isset($feed->images) ? $feed->images : $feed;
	}

	/**
	 * Update feed by user cache
	 *
	 * @param     $user
	 * @param int $page
	 *
	 * @return bool|object|\stdClass|string
	 * @throws WpException
	 */
	protected function update_feed_by_user($user, $page = 0)
	{
		$user = trim($user);
		$suser  = urlencode(str_replace('@', '', $user));
		$feed   = new \stdClass();
		if ($page > 0) {
			$prev_feed        = $this->_get_cached_feed($user, $page - 1);
			if($prev_feed->next_max_id == 0){
				return false;
			}
			$this->_params['max_id'] = $prev_feed->next_max_id;
		}
		$user_id = $this->get_user_id($suser);
		if (is_wp_error($user_id)) {
			return $user_id->get_error_message();
		}
		$url      = "https://api.instagram.com/v1/users/$user_id/media/recent?" . $this->_build_request_params();
		$body = $this->_make_request($url);
		$next_max_id = isset($body->pagination->next_max_id) ? $body->pagination->next_max_id : 0;
		$feed = (object) ['next_max_id' => $next_max_id, 'images' => $body->data];
		$this->_set_cached_feed($feed, $user, $page);

		return $feed;
	}

	/**
	 * Get user id by username
	 *
	 * @param $username
	 *
	 * @return mixed|\WP_Error
	 * @throws WpException
	 */
	public function get_user_id($username)
	{
		if (! $user_id = get_transient('instagram-user-id-' . $username)) {
			$url = "https://api.instagram.com/v1/users/search?" . $this->_build_request_params(['q' => $username]);
			$body = $this->_make_request($url);
				if (empty($body->data)) {
					return new \WP_Error('404', 'No such user: ' . $username);
				}
				$user_id = $body->data[0]->id;
				set_transient('instagram-user-id-' . $username, $user_id);
		}

		return $user_id;
	}

	protected function _get_cached_feed($name, $page)
	{
		return json_decode(get_transient($this->_get_trasient_name($name, $page)));
	}

	protected function _get_trasient_name($name, $page)
	{
		return 'instagram-feed-' . $name . '-' . $page;
	}

	protected function _set_cached_feed($feed, $name, $page)
	{
		return set_transient($this->_get_trasient_name($name, $page), json_encode($feed), 5 * 60);
	}

	protected function _make_request($url)
	{
		$response = wp_remote_get($url);
		if (wp_remote_retrieve_response_code($response) != 200) {
			return new WpException(wp_remote_retrieve_response_code($response) . ' - ' . wp_remote_retrieve_response_message($response));
		}
		$body = json_decode(wp_remote_retrieve_body($response));

		return $body;
	}

    protected function _build_request_params($params = [])
    {
        $this->set_params($params);
		/*if (is_null($this->get_access_token())) {
			throw new WpException('You should set access token for Instagram');
		}*/
		$this->_params['access_token'] = $this->get_access_token();
		if (! is_null($this->get_client_id())) {
			$this->_params['client_id'] = $this->get_client_id();
		}

		return build_query($this->_params);
	}
}
