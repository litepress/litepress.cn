<?php

namespace WordPressdotorg\GlotPress\TranslationSuggestions;

use GP;
use Text_Diff;
use WP_Error;
use WP_Http;
use WP_Text_Diff_Renderer_inline;

require_once ABSPATH . '/wp-includes/wp-diff.php';

class Translation_Memory_Client {

	const API_ENDPOINT = 'http://localhost:9200/translate_memory/_search';
	const API_BULK_ENDPOINT = 'http://localhost:9200/translate_memory/_bulk';

	/**
	 * 更新翻译记忆库内容
	 *
	 * 这里将记忆库后端由WordPress.com更改为本地服务器上的ES
	 *
	 * @param array $translations List of translation IDs, keyed by original ID.
	 *
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function update( array $translations ) {
		$requests = [];

		foreach ( $translations as $original_id => $translation_id ) {
			$translation = GP::$translation->get( $translation_id );

			// Check again in case the translation was changed.
			if ( 'current' !== $translation->status ) {
				continue;
			}

			$original        = GP::$original->get( $original_id );
			$translation_set = GP::$translation_set->get( $translation->translation_set_id );

			$locale = $translation_set->locale;
			if ( 'default' !== $translation_set->slug ) {
				$locale .= '_' . $translation_set->slug;
			}


			$source = $original->fields()['singular'] ?? '';

			if ( empty( $source ) || empty( $translation->translation_0 ) ) {
				continue;
			}

			$source = strtolower( trim( $source ) );
			$target = trim( $translation->translation_0 );

			$id = md5(
				$source
				. '|'
				. $target
			);

			$requests[] = wp_json_encode( array(
				'index' => array(
					'_id' => $id,
				),
			) );
			$requests[] = wp_json_encode( array(
				'id'       => $id,
				'source'   => $source,
				'target'   => $target,
				'priority' => 0,
			) );
		}

		if ( ! $requests ) {
			return new WP_Error( 'no_translations' );
		}

		$body = join( PHP_EOL, $requests );
		$body .= PHP_EOL;

		$request = wp_remote_post(
			self::API_BULK_ENDPOINT,
			[
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/x-ndjson',
				),
				'body'    => $body,
			]
		);

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( WP_Http::OK !== wp_remote_retrieve_response_code( $request ) ) {
			return new WP_Error( 'response_code_not_ok' );
		}

		$body   = wp_remote_retrieve_body( $request );
		$result = json_decode( $body, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error( 'json_parse_error' );
		}

		return $result ?: new WP_Error( 'unknown_error' );
	}

	/**
	 * Queries translation memory for a string.
	 *
	 * @param string $text Text to search translations for.
	 * @param string $target_locale Locale to search in.
	 *
	 * @return array|\WP_Error      List of suggestions on success, WP_Error on failure.
	 */
	public static function query( string $text, string $target_locale ) {
		$body = array(
			'query' => array(
				'match' => array(
					'source' => array(
						'query'                => $text,
						'minimum_should_match' => '70%'
					)
				),
			),
			'size'  => 10,
		);
		$body = wp_json_encode( $body );

		$request = wp_remote_post(
			self::API_ENDPOINT,
			[
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => $body,
			]
		);

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( WP_Http::OK !== wp_remote_retrieve_response_code( $request ) ) {
			return new WP_Error( 'response_code_not_ok' );
		}

		$body   = wp_remote_retrieve_body( $request );
		$result = json_decode( $body, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error( 'json_parse_error' );
		}

		if ( empty( $result ) ) {
			return [];
		}

		$result = $result['hits']['hits'] ?? array();

		$suggestions = [];
		foreach ( $result as $match ) {
			$source      = $match['_source']['source'] ?? '';
			$translation = $match['_source']['target'] ?? '';

			$text   = strtolower( trim( $text ) );
			$source = strtolower( trim( $source ) );

			similar_text( $source, $text, $similarity_score );

			if ( $similarity_score < 70 ) {
				continue;
			}

			$suggestions[] = [
				'similarity_score' => $similarity_score,
				'source'           => $source,
				'translation'      => $translation,
				'diff'             => ( 100 === $similarity_score ) ? null : self::diff( $text, $source ),
			];
		}

		array_multisort( $suggestions, SORT_DESC );

		return $suggestions;
	}

	/**
	 * Generates the differences between two sequences of strings.
	 *
	 * @param string $previous_text Previous text.
	 * @param string $text New text.
	 *
	 * @return string HTML markup for the differences between the two texts.
	 */
	protected static function diff( $previous_text, $text ) {
		$diff     = new  Text_Diff( 'auto', [ [ $previous_text ], [ $text ] ] );
		$renderer = new WP_Text_Diff_Renderer_inline();

		return $renderer->render( $diff );
	}
}
