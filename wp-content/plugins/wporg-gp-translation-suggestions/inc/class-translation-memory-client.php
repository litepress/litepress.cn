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
	const API_BULK_ENDPOINT = 'https://translate.wordpress.com/api/tm/-bulk';

	/**
	 * 更新翻译记忆库内容
	 *
	 * 这里将记忆库后端由WordPress.com更改为PostgreSQL中的数据表
	 *
	 * @param array $translations List of translation IDs, keyed by original ID.
	 *
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function update( array $translations ) {
		if ( ! $translations ) {
			return new WP_Error( 'no_translations' );
		}

		$dbconn = pg_connect( sprintf( 'host=%s dbname=%s user=%s password=%s', PG_DB_HOST, PG_DB_NAME, PG_DB_USER, PG_DB_PASSWORD ) );
		if ( false === $dbconn ) {
			return new WP_Error( 'pg_not_connect', 'PostgreSQL数据库链接失败' );
		}

		foreach ( $translations as $original_id => $translation_id ) {
			$translation = GP::$translation->get( $translation_id );

			// Check again in case the translation was changed.
			if ( 'current' !== $translation->status ) {
				continue;
			}

			$original = GP::$original->get( $original_id );

			// 数据入库
			$source = str_replace( "'", "''", $original->singular );
			$target = str_replace( "'", "''", $translation->translation_0 );

			$query = sprintf( "SELECT * FROM base_translationmemoryentry WHERE source='%s' AND target='%s';", $source, $target );

			$result = pg_query( $dbconn, $query );
			if ( false === $result ) {
				return new WP_Error( 'memory_query_error', '翻译记忆库查询失败' );
			}

			$line = pg_fetch_array( $result, null, PGSQL_ASSOC );
			if ( empty( $line ) ) {
				$query  = sprintf( "INSERT INTO base_translationmemoryentry (source, target,entity_id,locale_id,project_id,translation_id) VALUES ('%s','%s',%d,%d,%d,%d);",
					$source,
					$target,
					1,
					19,// 简体中文的编号是19
					1,
					1
				);
				$result = pg_query( $dbconn, $query );
				if ( false === $result ) {
					return new WP_Error( 'pg_not_insert', '翻译记忆库插入失败' );
				}
			}

			pg_free_result( $result );
		}

		pg_close( $dbconn );

		return true;
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
