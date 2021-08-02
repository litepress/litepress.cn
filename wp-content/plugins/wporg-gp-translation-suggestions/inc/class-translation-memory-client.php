<?php

namespace WordPressdotorg\GlotPress\TranslationSuggestions;

use GP;
use Text_Diff;
use WP_Error;
use WP_Http;
use WP_Text_Diff_Renderer_inline;

require_once ABSPATH . '/wp-includes/wp-diff.php';

class Translation_Memory_Client {

	const API_ENDPOINT = 'http://192.168.1.3:8000/translation-memory/';
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
			$id     = md5(
				strtolower( trim( $source ) )
				. '|'
				. $translation->translation_0
			);

			$requests[] = wp_json_encode( array(
				'index' => array(
					'_id' => $id,
				),
			) );
			$requests[] = wp_json_encode( array(
				'id'       => $id,
				'source'   => $source,
				'target'   => $translation->translation_0,
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
		$url = add_query_arg( urlencode_deep( [
			'text'   => $text,
			'locale' => 'zh-CN',
		] ), self::API_ENDPOINT );


		$request = wp_remote_get(
			$url,
			[
				'timeout' => 5,
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

		$suggestions = [];
		foreach ( $result as $match ) {
			$suggestions[] = [
				'similarity_score' => $match['quality'],
				'source'           => $match['source'],
				'translation'      => $match['target'],
				'diff'             => ( 1 === $match['quality'] ) ? null : self::diff( $text, $match['source'] ),
			];
		}

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
