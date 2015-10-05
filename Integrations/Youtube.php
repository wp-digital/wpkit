<?php

/**
 * Youtube Data API (mainly apis for retrieving data)
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Vitaly Nikolaev <vitaly@pingbull.no>
 * @author Maksim Viter <maksim@pingbull.no>
 *
 */

namespace WPKit\Integrations;

class Youtube {

	/**
	 * Youtube constructor.
	 *
	 * @param $key API key for youtube
	 */
	public function __construct( $key )
	{
		$this->set_api_key($key);
	}

	/**
	 * API key for Youtube
	 * @var string
	 */
	private $_api_key = null;

	/**
	 * Video ID from YouTube
	 * @var string
	 */
	private $_vid = false;

	/**
	 * Info of video
	 * @var array
	 *
	 */
	private $_info = null;

	/**
	 * Sets the video id
	 *
	 * @param string $vid
	 */
	public function set_vid( $vid ) {
		$this->_vid = $vid;
		$this->get_info();
	}

	/**
	 * Sets the API key
	 *
	 * @param string $key
	 */
	public function set_api_key( $key ) {
		$this->_api_key = $key;
	}

	/**
	 * Sets the video id from url
	 *
	 * @param string $url
	 *
	 * @throws \Exception
	 */
	public function set_url( $url ) {
		$this->_vid = self::parseVIdFromURL( $url );
		$this->get_info();
	}


	/**
	 * Gets info from youtube API
	 *
	 * @return array Info of video
	 * @throws \WPKit\Exception\WpException
	 */
	public function get_info() {
		if ( empty( $this->_vid ) ) {
			return "";
		}
		if ( ! $this->_info ) {
			if ( ! $this->_vid ) {
				return 'Invalid video url';
			}
			$url      = 'https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&maxResults=1&id=' . $this->_vid . '&key=' . $this->get_api_key();
			$response = wp_remote_get( $url );
			if ( $response instanceof \WP_Error || $response['response']['code'] != 200 ) {
				return $response['response']['message'];
			}
			$items_obj = json_decode( $response['body'], true );
			$items_obj = $items_obj['items'];
			if ( ! empty( $items_obj[0] ) ) {
				$this->_info = $items_obj[0];
			}
		}

		return $this->_info;
	}

	/**
	 * @return string
	 */
	public function get_vid() {
		return $this->_vid;
	}

	/**
	 * @return string
	 */
	private function get_api_key() {
		return $this->_api_key;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->get_info()['snippet']['title'];
	}

	/**
	 * @return int Duration in seconds
	 */
	public function get_duration() {
		return (int) $this->_parse_duration( $this->_info['contentDetails']['duration'] );
	}

	/**
	 * @param $duration
	 *
	 * @return int
	 */
	private function _parse_duration( $duration ) {
		return date_create( '@0' )->add( new \DateInterval($duration) )->getTimestamp();
	}

	/**
	 * @return string YouTube player URL
	 */
	public function get_preview() {
		return $this->get_thumbnail('high');
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->_info['snippet']['description'];
	}

	/**
	 * @return int Count of likes
	 */
	public function get_likes() {
		return (int) $this->_info['statistics']['likesCount'];
	}

	/**
	 * @return int Count of dislikes
	 */
	public function get_dislikes() {
		return (int) $this->_info['statistics']['dislikesCount'];
	}

	/**
	 * @return int Count of views
	 */
	public function get_views() {
		return (int) $this->_info['statistics']['viewCount'];
	}

	/**
	 * @param string $type Could be one of next:
	 *                      default,
	 *                      medium,
	 *                      high,
	 *                      standard
	 *
	 *
	 * @return string|bool Thumbnail of video or false if not exist
	 */
	public function get_thumbnail( $type = 'default' ) {
		if ( isset( $this->_info['snippet'] ) && isset( $this->_info['snippet']['thumbnails'] ) ) {
			foreach ( $this->_info['snippet']['thumbnails'] as $key => $thumbnail ) {
				if ( $key != $type ) {
					continue;
				}

				return $thumbnail['url'];
			}
		}

		return false;
	}


	/**
	 * Parse a youtube URL to get the youtube Vid.
	 * Support both full URL (www.youtube.com) and short URL (youtu.be)
	 *
	 * @param  string $youtube_url
	 *
	 * @throws \Exception
	 * @return string Video Id
	 */
	public static function parseVIdFromURL( $youtube_url ) {
		if ( strpos( $youtube_url, 'youtube.com' ) ) {
			$params = self::_parse_url_query( $youtube_url );

			return $params['v'];
		} else if ( strpos( $youtube_url, 'youtu.be' ) ) {
			$path = self::_parse_url_path( $youtube_url );
			$vid  = substr( $path, 1 );

			return $vid;
		} else {
			return false;
		}
	}


	/**
	 * Parse the input url string and return just the path part
	 *
	 * @param  string $url the URL
	 *
	 * @return string      the path string
	 */
	private static function _parse_url_path( $url ) {
		$array = parse_url( $url );

		return $array['path'];
	}

	/**
	 * Parse the input url string and return an array of query params
	 *
	 * @param  string $url the URL
	 *
	 * @return array      array of query params
	 */
	private static function _parse_url_query( $url ) {
		$array      = parse_url( $url );
		$query      = $array['query'];
		$queryParts = explode( '&', $query );
		$params     = [ ];
		foreach ( $queryParts as $param ) {
			$item               = explode( '=', $param );
			$params[ $item[0] ] = $item[1];
		}

		return $params;
	}


}
