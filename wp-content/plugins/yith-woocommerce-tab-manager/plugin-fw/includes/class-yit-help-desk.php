<?php
/**
 * YITH Help Desk static Class.
 *
 * @class   YIT_Plugin_Panel
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Help_Desk' ) ) {
	/**
	 * Class YIT_Help_Desk
	 *
	 * Contains static utilities for help desk integrations
	 */
	class YIT_Help_Desk {

		/**
		 * URL to contact to query zendesk API
		 * It can be overridden, by defining YIT_HELP_CENTER_DEBUG_URL constant
		 *
		 * @const string API url.
		 */
		const PRODUCTION_URL = 'https://support.yithemes.com/api/v2/help_center/en-us/';

		/**
		 * Retrieves latest articles flagged with a give set of labels (and featured)
		 *
		 * @param  array $labels Array of labest to search; default to empty array (all featured articles will be retrieved).
		 *
		 * @return array Array of articles, formatted as follows:
		 * [
		 *   [
		 *     'title' => 'Lorem ipsum dolor sit amet',
		 *     'url'   => 'https://example.com/lorem-ipsum-dolor-sit-amet'
		 *   ],
		 *   ...
		 * ]
		 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
		 */
		public static function get_latest_articles( $labels = array() ) {
			$latest_articles = get_site_transient( 'yith-plugin-fw-latest-hc-articles' );
			$latest_articles = $latest_articles ? $latest_articles : array();

			$labels = (array) $labels;

			// add featured label.
			if ( ! in_array( 'featured', $labels, true ) ) {
				$labels[] = 'featured';
			}

			// format labels to a valid query string param.
			$labels = implode( ',', array_map( 'sanitize_text_field', $labels ) );

			if ( ! empty( $latest_articles[ $labels ] ) && ! isset( $_GET['yith_plugin_fw_reset_hc_articles'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// if we can find articles in cache, return them.
				return $latest_articles[ $labels ];
			} else {
				// otherwise try to retrieve them.
				try {
					$response = self::call(
						'articles.json',
						'GET',
						array(
							'label_names' => $labels,
							'sort_by'     => 'created_at',
							'sort_order'  => 'desc',
							'per_page'    => 5,
						)
					);
				} catch ( Exception $e ) {
					return array();
				}

				// invalid answer from Zendesk server.
				if ( ! isset( $response['articles'] ) || ! is_array( $response['articles'] ) ) {
					return array();
				}

				$formatted_articles = array();

				foreach ( $response['articles'] as $article ) {
					// invalid article from Zendesk server.
					if ( ! isset( $article['html_url'] ) || ! isset( $article['title'] ) ) {
						continue;
					}

					// add valid articles.
					$formatted_articles[] = array(
						'title' => $article['title'],
						'url'   => esc_url( $article['html_url'] ),
					);
				}

				$latest_articles[ $labels ] = $formatted_articles;

				// update cache.
				set_site_transient( 'yith-plugin-fw-latest-hc-articles', $latest_articles, 15 * DAY_IN_SECONDS );

				return $formatted_articles;
			}
		}

		/**
		 * Performs any API request to HC API
		 *
		 * @param string $request Endpoint to call.
		 * @param string $method  HTTP method for the call.
		 * @param array  $query   Query string parameters to include with the request.
		 * @param array  $body    Parameters to send as json_encoded content of the request.
		 * @param array  $args    Array of parameters to pass to {wp_remote_request}.
		 *
		 * @return string Parsed body of the answer; if content is valid JSON string, it will be decoded before return.
		 * @throws Exception When an error occurs with API call; error contains more details about the type of problem.
		 *
		 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
		 */
		public static function call( $request, $method = 'GET', $query = array(), $body = array(), $args = array() ) {
			$destination_url = self::get_url( $request );

			if ( ! empty( $query ) ) {
				$destination_url = add_query_arg( $query, $destination_url );
			}

			$body = 'GET' === $method ? $body : wp_json_encode( $body );

			$args = array_merge(
				array(
					'timeout'            => apply_filters( 'yit_plugin_fw_help_desk_request_timeout', 2 ),
					'reject_unsafe_urls' => true,
					'blocking'           => true,
					'sslverify'          => true,
					'attempts'           => 0,
				),
				$args,
				array(
					'method' => $method,
					'body'   => $body,
				)
			);

			$response = wp_remote_request( $destination_url, $args );

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message(), 400 );
			} else {
				$resp_body = isset( $response['body'] ) ? @json_decode( $response['body'], true ) : ''; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$status    = isset( $response['response'] ) ? absint( $response['response']['code'] ) : false;

				if ( ! in_array( $status, apply_filters( 'yit_plugin_fw_help_desk_valid_response_statuses', array( 200 ) ), true ) ) {
					throw new Exception( __( 'There was an error with your request; please try again later.', 'yith-plugin-fw' ), $status );
				} else {
					return $resp_body;
				}
			}
		}

		/**
		 * Get formatted url for API calls
		 *
		 * @param  string $request Endpoint to call with url.
		 * @return string Formatted url.
		 *
		 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
		 */
		public static function get_url( $request = '' ) {
			$base_url = self::PRODUCTION_URL;

			if ( defined( 'YIT_HELP_CENTER_DEBUG_URL' ) ) {
				$alternative_url = filter_var( YIT_HELP_CENTER_DEBUG_URL, FILTER_VALIDATE_URL );
				$base_url        = $alternative_url ? $alternative_url : $base_url;
			}

			if ( 0 !== strrpos( $base_url, '/' ) ) {
				$base_url = trailingslashit( $base_url );
			}

			if ( $request ) {
				$base_url .= $request;
			}

			return $base_url;
		}

	}
}
