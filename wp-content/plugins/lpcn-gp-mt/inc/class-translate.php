<?php

namespace LitePress\GlotPress\MT;

use Exception;
use GP;
use LitePress\Logger\Logger;
use LitePress\WP_Http\WP_Http;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Translation_Entry;
use Translations;
use WP_Error;

/**
 * 机器翻译类文件
 */
class Translate {

	public static function job( int $project_id, array $originals, array $excluded ): void {
		/**
		 * 翻译填充的流程：
		 *
		 * 1. 尝试匹配记忆库，匹配时会按优先级处理匹配关系
		 * 2. 对原文进行术语库替换，同时将作品的作者及名称一并替换
		 * 3. 调用谷歌翻译对处理后的原文进行翻译
		 * 4. 恢复被替换的术语
		 */

		/**
		 * 初始化翻译实体
		 */
		$translations = new Translations();

		/**
		 * 初始化术语表
		 */
		$glossaries = self::get_glossaries();
		foreach ( $excluded as $item ) {
			$glossaries[ $item ] = $item;
		}

		/**
		 * 开始翻译流程
		 */
		foreach ( $originals as $original ) {
			$original = $original->fields();
			$source   = $original['singular'] ?? '';
			if ( empty( $source ) ) {
				continue;
			}

			/**
			 * 记忆库直接匹配的话十分不靠谱，所以应该只匹配一些经过人工审核确定通用的词汇
			 */
			$translation = '';
			//$translation = self::get_translate_from_memory( $source );
			if ( is_wp_error( $translation ) ) {
				$translation = '';
			}

			if ( empty( $translation ) ) {
				// 所有匹配不上记忆库的翻译都标记为模糊
				$flags = array(
					'fuzzy',
				);

				$id_map = array();
				foreach ( $glossaries as $key => $value ) {
					$tmp    = $source;
					$id     = rand( 0, 99999 );
					$key    = preg_quote( $key, '/' );
					$source = preg_replace( "/\b$key\b/m", "<code>#$id</code>", $source );

					// 如果内容被替换过，就将当前的id写到map里
					if ( $tmp !== $source ) {
						$id_map[ $id ] = $value;
					}
				}

				$args     = array(
					'verify'  => false,
					'timeout' => 30,
					'headers' => array(
						'Host' => 'translate.google.cn'
					)
				);
				$tr       = new GoogleTranslate( 'zh-CN', null, $args );
				$proxy_ip = WP_Http::get_proxy_ip();
				$tr->setUrl( "http://{$proxy_ip}/translate_a/single" );
				$tr->setTarget( 'zh' );
				try {
					$translation = $tr->translate( $source );
				} catch ( Exception $e ) {
					Logger::error( 'GP_MT', '谷歌翻译失败：' . $e->getMessage() );
					continue;
				}

				// 将术语库内容还原回去
				foreach ( $id_map as $key => $value ) {
					$translation = preg_replace( "/\s*<code>#$key<\/code>\s*/m", $value, $translation );
				}
			} else {
				$flags = array(
					'current',
				);
			}

			$entry = new Translation_Entry( array(
				'singular'     => $source,
				'plural'       => $original['plural'] ?? null,
				'translations' => array( $translation ),
				'flags'        => $flags,
			) );
			$translations->add_entry( $entry );
		}

		$translation_set = GP::$translation_set->find_one( array( 'project_id' => $project_id ) );

		wp_set_current_user( 517 );
		$translation_set->import( $translations );
	}

	/**
	 * 获取术语库
	 *
	 * @return array
	 */
	private static function get_glossaries(): array {
		$glossaries = wp_cache_get( 'glossaries', 'litepress-cn' );
		if ( empty( $glossaries ) ) {
			$glossaries = array();

			$glossary_entries = GP::$glossary_entry->all();

			foreach ( $glossary_entries as $glossary ) {
				if ( empty( $glossary->term ) || empty( $glossary->translation ) ) {
					continue;
				}

				$glossaries[ $glossary->term ] = $glossary->translation;
			}

			/**
			 * 对术语库按键的长度降序排序，这样防止先匹配短的术语导致长术语无法匹配
			 */
			uksort( $glossaries, function ( $a, $b ) {
				$a_len = strlen( $a );
				$b_len = strlen( $b );

				return $a_len == $b_len ? 0 : ( $a_len > $b_len ? - 1 : 1 );
			} );

			wp_cache_set( 'glossaries', $glossaries, 'litepress-cn', 86400 );
		}

		return $glossaries;
	}

	/**
	 * 从ES读取记忆库翻译
	 *
	 * @param string $source
	 *
	 * @return string|\WP_Error
	 */
	private static function get_translate_from_memory( string $source ): string|WP_Error {
		$body = array(
			'query' => array(
				"bool" => array(
					'must' => array(
						'term' => array(
							'source.keyword' => $source
						),
					),
				),
			),
			'size'  => 10,
		);
		$body = wp_json_encode( $body );

		$request = wp_remote_post(
			'http://localhost:9200/translate_memory/_search',
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
			return new WP_Error( 'empty result' );
		}

		return $result['hits']['hits'][0]['_source']['target'] ?? '';
	}

}
