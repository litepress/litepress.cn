<?php

namespace LitePress\GlotPress\MT;

use GP;
use GP_Route;
use LitePress\Logger\Logger;
use LitePress\WP_Http\WP_Http;
use Translation_Entry;
use Translations;
use WP_Error;
use function LitePress\WP_Http\wp_remote_get;

/**
 * 机器翻译引擎
 *
 * 该引擎只对 litepress.cn/translate 上托管的项目生效。引擎同时提供了 WEB 端和 API 端的外部接口。
 * 对于 WEB 端的请求，会直接保存进对应的项目，而 API 端则将结果返回。
 */
class Translate extends GP_Route {

	/**
	 * 面向 WEB 场景的外部接口函数
	 */
	public function web( int $project_id, array $originals ): bool {

		/**
		 * 获取翻译集
		 */
		$translation_set = GP::$translation_set->find_one( array( 'project_id' => $project_id ) );
		if ( empty( $translation_set ) ) {
			Logger::error( 'Translate', '从 WEB 端为机器翻译引擎传入的项目 ID 无法获取到对应的翻译集', array(
				'project_id' => $project_id,
			) );

			return false;
		}

		$translations = $this->job( $project_id, $originals );

		/**
		 * 翻译入库
		 */
		$translation_set->import( $translations );

		return true;
	}

	/**
	 * 核心函数
	 *
	 * 翻译填充的流程：
	 * 1. 尝试匹配记忆库，匹配时会按优先级处理匹配关系
	 * 2. 对原文进行术语库替换，同时将作品的作者及名称一并替换
	 * 3. 调用谷歌翻译对处理后的原文进行翻译
	 * 4. 恢复被替换的术语
	 *
	 * 该函数批量处理机器翻译工作并返回最终结果
	 */
	private function job( int $project_id, array $originals ): Translations {

		/**
		 * 初始化翻译实体
		 */
		$translations = new Translations();

		/**
		 * 初始化术语表
		 */
		$glossaries = $this->get_glossaries();

		$excluded = array();

		$project_name = $this->get_project_name( $project_id );
		if ( ! empty( $project_name ) ) {
			$excluded[] = $project_name;
		}

		foreach ( $excluded as $item ) {
			if ( empty( $item ) ) {
				continue;
			}
			$glossaries[ $item ] = array(
				'translation'    => $item,
				'part_of_speech' => 'noun',
			);
		}

		/**
		 * 开始翻译流程
		 */
		foreach ( $originals as $original_key => $original ) {
			// 开始机器翻译前先执行一些前置翻译，比如说尝试匹配记忆库，匹配上了就不需要再机器翻译了
			$translation = $glossaries[ strtolower( $original->singular ) ]['translation'] ?? '';
			$translation = $translation ?: $this->query_memory( $original->singular );

			if ( ! empty( $translation ) ) {
				$entry = new Translation_Entry( array(
					'singular'     => $original?->singular,
					'plural'       => $original?->plural,
					'translations' => array( $translation ),
					'context'      => $original?->context,
					'references'   => $original?->references,
					'flags'        => array(
						'current'
					),
				) );
				$translations->add_entry( $entry );

				// 如果已经被预翻译了，就不要再进行机翻了
				unset( $originals[ $original_key ] );
			}
		}

		// 调用机器翻译
		$tasks = array();
		end( $originals );
		$key_last    = key( $originals );
		$sources_len = 0;

		foreach ( $originals as $original_key => $original ) {
			$original   = $original->fields();
			$source     = $original['singular'] ?? '';
			$source_esc = $source;
			if ( empty( $source ) ) {
				continue;
			}

			/**
			 * 文本替换前进行预处理（主要去除HTML标签，这样防止在后续处理的时候把HTML标签也处理了）
			 */
			preg_match_all( '/<.+?>/', $source_esc, $matches );
			$matches = $matches[0] ?? array();

			// 结果集去重
			$matches = array_unique( $matches );

			// 原文中被替换的关键字以及代号 id
			$id_map = array();

			foreach ( $matches as $match ) {
				// 排除掉结束标签（替换开始标签时会顺带替换对应的结束标签）
				if ( str_contains( $match, '</' ) ) {
					continue;
				}

				$rand_id = rand( 0, 99999 );

				/**
				 * 替换 HTML 开始标签
				 */
				$h_id            = "<$rand_id>";
				$id_map[ $h_id ] = $match;
				// 替换 HTML 后对两端增加空格，以使标识符与周围单词划开界限
				$source_esc = str_replace( $match, " $h_id ", $source_esc );

				/**
				 * 替换 HTML 开始标签
				 */
				$h_id = "</$rand_id>";

				// 先提取一下这个标签究竟是何方牛马
				preg_match_all( '/<(\w+)[^>]*>/', $match, $tag_matches );
				$tag_name = $tag_matches[1][0] ?? '';

				if ( ! empty( $tag_name ) ) {
					$id_map[ $h_id ] = "</$tag_name>";
					// 替换 HTML 后对两端增加空格，以使标识符与周围单词划开界限
					$source_esc = str_replace( "</$tag_name>", " $h_id ", $source_esc );

					// 为了防止有的人不讲武德，写不规范的结束标签，所以多匹配一种情况
					$h_id            = "</ $rand_id>";
					$id_map[ $h_id ] = "</$tag_name>";
					// 替换 HTML 后对两端增加空格，以使标识符与周围单词划开界限
					$source_esc = str_replace( "</ $rand_id>", " $h_id ", $source_esc );
				}
			}

			/**
			 * 替换简码
			 */
			preg_match_all( '/\[.+?]/', $source_esc, $matches );

			foreach ( $matches[0] ?? array() as $match ) {
				$id            = rand( 0, 99999 );
				$id_map[ $id ] = $match;
				$source_esc    = str_replace( $match, "#$id", $source_esc );
			}

			/**
			 * 斯坦福开源的 postag 库很准确但是相当占服务器资源，其他库很弱智，加和不加一样，所以暂时将该特性屏蔽
			 */
			//$pos_map = self::get_pos_tags( $source_esc );

			/**
			 * 替换术语库
			 */
			foreach ( $glossaries as $key => $value ) {
				// 开始替换前先检查词性是否匹配，如果不匹配则跳过
				//if ( ! key_exists( $key, $pos_map ) ) {
				//	continue;
				//}

				//if ( $pos_map[ $key ] !== $value['part_of_speech'] ) {
				//	continue;
				//}

				$id         = rand( 0, 99999 );
				$key        = preg_quote( $key, '/' );
				$source_esc = preg_replace( "/\b$key\b/m", "#$id", $source_esc, - 1, $is_replace );

				// 如果内容被替换过，就将当前的id写到map里
				if ( $is_replace ) {
					$id_map[ $id ] = $value['translation'];
				}
			}

			$tasks[ $source ] = array(
				'original_id' => $original_key,
				'source'      => $source,
				'source_esc'  => $source_esc,
				'target'      => '',
				'glossaries'  => $id_map,
			);

			$source_len = strlen( urlencode( $source_esc ) );
			// 如果当前语句单条的长度就超限的话就直接略过
			if ( $source_len > 4300 ) {
				continue;
			}

			$sources_len += $source_len;

			/**
			 * 计算当前累计的字符数（谷歌限制为 5000 字符一次提交，这里限制为 4000 进行一次批量提交，因为后续还需要往里面插入换行符）
			 */
			if ( $sources_len > 4000 || $key_last === $original_key ) {
				// 如果最后一次任务的字符数加进去大于 4300 的话就挪到下一次任务
				if ( $sources_len > 4300 ) {
					unset( $tasks[ $source ] );
				}

				$sources_esc = array();
				foreach ( $tasks as $task ) {
					$sources_esc[] = $task['source_esc'];
				}

				$data = self::google_translate( $sources_esc );
				if ( is_wp_error( $data ) ) {
					Logger::error( 'mt_error', '机器翻译失败：' . $data->get_error_message(), array(
						'project_id' => $project_id,
						'data'       => $data->get_all_error_data(),
					) );

					goto over;
				}

				foreach ( $tasks as &$task ) {
					$task['target'] = $data[ $task['source_esc'] ];

					foreach ( $task['glossaries'] as $k => $v ) {
						// 替换关键字
						$task['target'] = preg_replace( "~(\s*)#(\s*)$k(\s*)~m", $v, $task['target'] );
						// 替换 HTML 标签等不太会被谷歌翻译导致插入空格的内容
						$task['target'] = preg_replace( "~(\s*)$k(\s*)~m", $v, $task['target'] );
					}

					if ( ! empty( $task['target'] ) ) {
						$o     = $originals[ $task['original_id'] ];
						$entry = new Translation_Entry( array(
							'singular'     => $o?->singular,
							'plural'       => $o?->plural,
							'translations' => array( $task['target'] ),
							'context'      => $o?->context,
							'references'   => $o?->references,
							'flags'        => array(
								'fuzzy',
							),
						) );
						$translations->add_entry( $entry );
					}
				}
				unset( $task );

				over:
				unset( $tasks );
				$tasks = array();
				if ( $sources_len > 4300 ) {
					$tasks[ $source ] = array(
						'original_id' => $original_key,
						'source'      => $source,
						'source_esc'  => $source_esc,
						'target'      => '',
						'glossaries'  => $id_map,
					);
				}

				$sources_len = empty( $tasks ) ? 0 : $source_len;
			}
		}


		return $translations;
	}

	/**
	 * 获取术语表
	 */
	private function get_glossaries(): array {
		return array();
	}

	/**
	 * 获取项目名
	 *
	 * 这不是简单的读取项目属性，因为项目属性中的项目名通常包含了长尾副词，所以这个函数尝试用项目原文中分析项目名称
	 */
	private function get_project_name( int $project_id ): string {
		$allowed = array(
			'Theme Name of the theme',
			'Plugin Name of the plugin',
			'Name of the plugin',
			'Name of the theme',
		);

		$original = GP::$original->find_one( array(
			'project_id' => $project_id,
			'comment'    => $allowed,
		) );

		if ( $original ) {
			return $original->singular;
		} else {
			return '';
		}
	}

	public
	static function query_memory(
		string $source,
	): string|WP_Error {
		global $wpdb;

		$source = strtolower( $source );

		$memory = wp_cache_get( 'gp_memory', 'litepress-cn' );

		if ( empty( $memory ) ) {
			/**
			 * 这里因为还是会查询原文相同的语言对，所以按升序排，这样从第一条开始压入数组，后面的高使用比例的就会覆盖前面的低使用比例
			 */
			$r = $wpdb->get_results( "select o.singular as source, t.translation_0 as target, COUNT(t.translation_0) as o2
from wp_4_gp_translations as t
         join wp_4_gp_originals as o on t.original_id = o.id
where t.status='current'
GROUP BY  target
HAVING o2 > 5
order by o2 asc" );

			foreach ( $r as $item ) {
				$memory[ strtolower( $item->source ) ] = $item->target;
			}

			// 缓存七天
			wp_cache_set( 'gp_memory', $memory, 'litepress-cn', 604800 );
		}

		return $memory[ $source ] ?? '';
	}

	/**
	 * 谷歌翻译接口封装函数
	 */
	private function google_translate( array $sources ): string|array|WP_Error {
		// 不允许原文中出现换行符，因为计划用换行符来分割多条原文。
		$sources_urlencoded = array_map( function ( $source ) {
			return urlencode( str_replace( array( "\n", "\n\r", "\r\n" ), '', $source ) );
		}, $sources );

		$q = join( "\n", $sources_urlencoded );

		$base_url = 'https://43.154.75.224/translate_a/t';

		$args = array(
			//'client' => 'dict-chrome-ex',
			'client' => 'gtx',
			'sl'     => 'en',
			'tl'     => 'zh-CN',
			'q'      => $q,
		);
		$url  = add_query_arg( $args, $base_url );

		$args = array(
			'headers'    => array(
				'Host'            => 'clients2.google.com',
				'Accept'          => '*/*',
				'Accept-Encoding' => 'gzip, deflate, br',
			),
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0',
			'sslverify'  => false,
			'timeout'    => 30,
		);
		$r    = wp_remote_get( $url, $args );
		if ( is_wp_error( $r ) ) {
			return $r;
		}

		$trans_data = wp_remote_retrieve_body( $r );

		$status_code = wp_remote_retrieve_response_code( $r );
		if ( WP_Http::OK !== $status_code ) {
			return new WP_Error( 'mt_error', '翻译接口返回状态码：' . $status_code, array(
				'body' => $trans_data,
			) );
		}

		$trans_data = json_decode( $trans_data, true );

		/**
		 * ----------------------------------------------------------
		 * 翻译结果处理
		 * ----------------------------------------------------------
		 *
		 * 谷歌翻译的返回值有两种情况，一种是翻译的语句很复杂的情况下，会返回一个复杂的 JSON 字符串
		 * 另一种是当翻译语句比较简单时只会返回一个仅包含译文的 JSON，例如：[ "你好" ]
		 *
		 */
		if ( count( $trans_data ) === 1 and isset( $trans_data[0] ) ) {
			return array_combine( $sources, $trans_data );
		}

		$sentences = $trans_data['sentences'] ?? array();

		$any_trans = '';

		// 去除最后一个字段，因为最后一个是所有译文的拼音
		unset( $sentences[ count( $sentences ) - 1 ] );

		foreach ( $sentences as $sentence ) {
			$any_trans .= $sentence['trans'];
		}

		$trans_list = explode( "\n", $any_trans );

		// 如果翻译的数量和原文的数量对不上的话就记录错误日志同时返回空数组
		if ( count( $trans_list ) !== count( $sources ) ) {
			Logger::error( 'mt_error', '翻译接口返回的译文数量与传入的原文数量不符', array(
				'sources' => $sources,
				'trans'   => $trans_list,
			) );

			return array();
		}

		return array_combine( $sources, $trans_list );
	}

	/**
	 * 面向 API 场景的外部接口函数
	 */
	public function api() {
		header( 'Content-Type: application/json' );

		$request_originals_text = gp_post( 'originals', '[]' );
		$request_originals      = json_decode( $request_originals_text, true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			echo json_encode( array( 'error' => '参数错误，不是标准的 Json 字符串' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		$gp_originals = GP::$original->find_many( array( 'singular' => $request_originals, 'status' => '+active' ) );
		if ( empty( $gp_originals ) ) {
			echo json_encode( array( 'error' => '你请求的字符串未托管在 LitePress 翻译平台上。碍于系统资源有限，我们暂时无法将接口完全对外开放。' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		// 取用户请求翻译的字符串与 GlotPress 数据库中积累的项目字符串的交集，也就是说，只允许翻译项目中存在的字符串
		$originals = array();
		foreach ( $gp_originals as $gp_original ) {
			if ( in_array( $gp_original->singular, $request_originals ) ) {
				$originals[] = $gp_original;
			}
		}

		$translations = $this->job( 0, $originals );


		$data = array();
		foreach ( $translations->entries as $translation ) {
			$data[ $translation->singular ] = $translation->translations[0] ?? '';
		}

		echo json_encode( $data, JSON_UNESCAPED_SLASHES );
	}

}
