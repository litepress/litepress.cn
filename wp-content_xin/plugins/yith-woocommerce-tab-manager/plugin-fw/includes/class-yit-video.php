<?php
/**
 * YITH Video Class
 * manage videos from youtube, vimeo and other services.
 *
 * @class   YIT_Video
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Video' ) ) {
	/**
	 * YIT_Video class.
	 *
	 * @deprecated 3.5
	 */
	class YIT_Video {

		/**
		 * Generate the HTML for a youtube video
		 *
		 * @param array $args Array of arguments to configure the video to generate.
		 *
		 * @return string
		 */
		public static function youtube( $args = array() ) {
			$defaults = array(
				'id'     => '',
				'url'    => '',
				'width'  => 425,
				'height' => 356,
				'echo'   => false,
			);
			$args     = wp_parse_args( $args, $defaults );

			$id     = $args['id'];
			$url    = $args['url'];
			$width  = $args['width'];
			$height = $args['height'];
			$echo   = $args['echo'];
			$html   = '';

			// Retrieve the video ID if we have only the URL.
			if ( ! $id && ! ! $url ) {
				$id = self::video_id_by_url( $url );
			}

			if ( $id ) {
				$id  = preg_replace( '/[&|&amp;]feature=([\w\-]*)/', '', $id );
				$id  = preg_replace( '/(youtube|vimeo):/', '', $id );
				$url = "https://www.youtube.com/embed/{$id}?wmode=transparent";

				$html = '<div class="post_video youtube">' .
						'<iframe wmode="transparent" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" src="' . esc_url( $url ) . '" frameborder="0" allowfullscreen></iframe>' .
						'</div>';
				$html = apply_filters( 'yit_video_youtube', $html );
			}

			if ( $echo ) {
				echo wp_kses_post( $html );
			}

			return $html;
		}

		/**
		 * Generate the HTML for a vimeo video
		 *
		 * @param array $args Array of arguments to configure the video to generate.
		 *
		 * @return string
		 */
		public static function vimeo( $args = array() ) {
			$defaults = array(
				'id'     => '',
				'url'    => '',
				'width'  => 425,
				'height' => 356,
				'echo'   => false,
			);
			$args     = wp_parse_args( $args, $defaults );

			$id     = $args['id'];
			$url    = $args['url'];
			$width  = $args['width'];
			$height = $args['height'];
			$echo   = $args['echo'];
			$html   = '';

			// Retrieve the video ID if we have only the URL.
			if ( ! $id && ! ! $url ) {
				$id = self::video_id_by_url( $url );
			}

			if ( $id ) {
				$id       = preg_replace( '/[&|&amp;]feature=([\w\-]*)/', '', $id );
				$id       = preg_replace( '/(youtube|vimeo):/', '', $id );
				$protocol = is_ssl() ? 'https' : 'http';
				$url      = "{$protocol}://player.vimeo.com/video/{$id}?title=0&amp;byline=0&amp;portrait=0";

				$html = '<div class="post_video youtube">' .
						'<iframe wmode="transparent" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" src="' . esc_url( $url ) . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>' .
						'</div>';
				$html = apply_filters( 'yit_video_vimeo', $html );
			}

			if ( $echo ) {
				echo wp_kses_post( $html );
			}

			return $html;
		}

		/**
		 * Retrieve video ID from URL
		 *
		 * @param string $url URL of the video.
		 *
		 * @return bool|string
		 */
		public static function video_id_by_url( $url ) {
			$parsed = wp_parse_url( esc_url( $url ) );
			$host   = isset( $parsed['host'] ) ? $parsed['host'] : false;

			switch ( $host ) {
				case 'youtube.com':
				case 'www.youtube.com':
				case 'youtu.be':
				case 'www.youtu.be':
					$id       = self::youtube_id_by_url( $url );
					$video_id = "youtube:$id";
					break;

				case 'www.vimeo.com':
				case 'vimeo.com':
					preg_match( '/http(s)?:\/\/(\w+.)?vimeo\.com\/(.*\/)?([0-9]+)/', $url, $matches );

					$id       = trim( $matches[4], '/' );
					$video_id = "vimeo:$id";
					break;

				default:
					$video_id = false;

			}

			return $video_id;
		}

		/**
		 * Retrieve video ID from URL
		 *
		 * @param string $url URL of the video.
		 *
		 * @return bool|string
		 */
		protected static function youtube_id_by_url( $url ) {
			if ( preg_match( '/http(s)?:\/\/youtu.be/', $url, $matches ) ) {
				$url = wp_parse_url( $url, PHP_URL_PATH );
				$url = str_replace( '/', '', $url );

				return $url;

			} elseif ( preg_match( '/watch/', $url, $matches ) ) {
				$arr = wp_parse_url( $url );
				$url = str_replace( 'v=', '', $arr['query'] );

				return $url;

			} elseif ( preg_match( '/http(s)?:\/\/(\w+.)?youtube.com\/v/', $url, $matches ) ) {
				$arr = wp_parse_url( $url );
				$url = str_replace( '/v/', '', $arr['path'] );

				return $url;

			} elseif ( preg_match( '/http(s)?:\/\/(\w+.)?youtube.com\/embed/', $url, $matches ) ) {
				$arr = wp_parse_url( $url );
				$url = str_replace( '/embed/', '', $arr['path'] );

				return $url;

			} elseif ( preg_match( "#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=[0-9]/)[^&\n]+|(?<=v=)[^&\n]+#", $url, $matches ) ) {
				return $matches[0];

			} else {
				return false;
			}
		}

	}
}
