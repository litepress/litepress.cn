<?php

namespace LitePress\GlotPress\MT;

use GP;
use GP_Route;
use LitePress\Logger\Logger;
use LitePress\WP_Http\WP_Http;
use Translation_Entry;
use Translations;
use WP_Error;

use function LitePress\WP_Http\wp_remote_post;

/**
 * 机器翻译类文件
 */
class Translate {

	const TRANSLATE_SCRIPT = 'python3 ' . PLUGIN_DIR . '/py-translate-lib/translate.py';

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
		 * 获取翻译集
		 */
		$translation_set = GP::$translation_set->find_one( array( 'project_id' => $project_id ) );

		/**
		 * 切换登录用户到机翻引擎
		 */
		wp_set_current_user( 517 );

		/**
		 * 初始化术语表
		 */
		$glossaries = self::get_glossaries();
		foreach ( $excluded as $item ) {
			if ( empty( $item ) ) {
				continue;
			}
			$glossaries[ $item ] = $item;
		}

		/**
		 * 开始翻译流程
		 */
		// 开始机器翻译前先执行一些前置翻译，比如说尝试匹配记忆库，匹配上了就不需要再机器翻译了
		add_filter( 'gp_mt_pre_translate', array( __CLASS__, 'get_translate_from_memory' ), 10, 2 );

		foreach ( $originals as $original_key => $original ) {
			$translation = $glossaries[ strtolower( $original->singular ) ] ?? '';
			$translation = $translation ?: apply_filters( 'gp_mt_pre_translate', '', $original->singular );

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

		/**
		 * 导入这一批翻译到数据库
		 */
		$translation_set->import( $translations );

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

			$h_map = array();
			foreach ( $matches[0] ?? array() as $match ) {
				$h_id            = rand( 0, 99999 );
				$h_map["H$h_id"] = $match;
				$source_esc      = str_replace( $match, "H$h_id", $source_esc );
			}


			$id_map = array();
			/**
			 * 替换简码
			 */
			preg_match_all( '/\[.+?]/', $source_esc, $matches );

			foreach ( $matches[0] ?? array() as $match ) {
				$id            = rand( 0, 99999 );
				$id_map[ $id ] = $match;
				$source_esc    = str_replace( $match, "<code>#$id</code>", $source_esc );
			}

			/**
			 * 替换术语库
			 */
			foreach ( $glossaries as $key => $value ) {
				$id         = rand( 0, 99999 );
				$key        = preg_quote( $key, '/' );
				$source_esc = preg_replace( "/\b$key\b/m", "<code>#$id</code>", $source_esc, - 1, $is_replace );

				// 如果内容被替换过，就将当前的id写到map里
				if ( $is_replace ) {
					$id_map[ $id ] = $value;
				}
			}
/*
			if ( stristr( $source, ' Recommended:' ) ) {
				var_dump($glossaries);
				var_dump( $source_esc );
				exit;
			}*/

			/**
			 * 术语库替换完得在翻译触发前把前面预处理去掉的HTML标签恢复一下
			 */
			foreach ( $h_map as $k => $v ) {
				$source_esc = str_replace( $k, $v, $source_esc );
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

				$data = self::mt( $sources_esc );
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
						$task['target'] = preg_replace( "/(\s*)<(\s*)(code|代码)(\s*)>#(\s*)$k(\s*)<(\s*)\/(code|代码)>(\s*)/m", $v, $task['target'] );
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

				/**
				 * 导入这一批翻译到数据库
				 */
				$translation_set->import( $translations );

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

	}

	/**
	 * 获取术语库
	 *
	 * @return array
	 */
	private
	static function get_glossaries(): array {
		$glossaries = wp_cache_get( 'glossaries', 'litepress-cn' );
		if ( empty( $glossaries ) ) {
			$glossaries = array();

			$glossary_entries = GP::$glossary_entry->all();

			// 为术语衍生不同时态的版本
			foreach ( $glossary_entries as $key => $value ) {
				if ( empty( $value->term ) || empty( $value->translation ) ) {
					continue;
				}

				$terms = array();

				$quoted_term = preg_quote( $value->term, '/' );

				$terms[] = $quoted_term;
				$terms[] = $quoted_term . 's';

				if ( 'y' === substr( $value->term, - 1 ) ) {
					$terms[] = preg_quote( substr( $value->term, 0, - 1 ), '/' ) . 'ies';
				} elseif ( 'f' === substr( $value->term, - 1 ) ) {
					$terms[] = preg_quote( substr( $value->term, 0, - 1 ), '/' ) . 'ves';
				} elseif ( 'fe' === substr( $value->term, - 2 ) ) {
					$terms[] = preg_quote( substr( $value->term, 0, - 2 ), '/' ) . 'ves';
				} else {
					if ( 'an' === substr( $value->term, - 2 ) ) {
						$terms[] = preg_quote( substr( $value->term, 0, - 2 ), '/' ) . 'en';
					}
					$terms[] = $quoted_term . 'es';
					$terms[] = $quoted_term . 'ed';
					$terms[] = $quoted_term . 'ing';
				}

				foreach ( $terms as $term ) {
					$glossaries[$term] = $value->translation;
				}
			}

			/**
			 * 对术语库按键的长度降序排序，这样防止先匹配短的术语导致长术语无法匹配
			 */
			uksort( $glossaries, function ( $a, $b ) {
				return mb_strlen( $a ) < mb_strlen( $b );
			} );

			wp_cache_set( 'glossaries', $glossaries, 'litepress-cn', 86400 );
		}

		return $glossaries;
	}

	private
	static function mt(
		array $sources
	): string|array|WP_Error {
		// 不允许原文中出现换行符，因为计划用换行符来分割多条原文。
		$sources_urlencoded = array_map( function ( $source ) {
			return urlencode( str_replace( array( "\n", "\n\r", "\r\n" ), '', $source ) );
		}, $sources );

		$q = join( "\n", $sources_urlencoded );

		$base_url = 'https://101.32.10.79/translate_a/t';

		$args = array(
			'client' => 'dict-chrome-ex',
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
		$r    = wp_remote_post( $url, $args );
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
	 * 从记忆库匹配使用次数大于10的翻译，暂时认为被多次引用的翻译是准确的翻译
	 *
	 * 这里的记忆库和 ES 中的不是一个。这里的是从翻译数据实时生成并缓存到 Redis 中的
	 *
	 * @param string $source
	 *
	 * @return string|\WP_Error
	 */
	public
	static function get_translate_from_memory(
		string $translate,
		string $source,
	): string|WP_Error {
		global $wpdb;

		$source = strtolower( $source );

		$memory = wp_cache_get( 'gp_memory', 'litepress-cn' );

		if ( empty( $memory ) ) {
			/**
			 * 这里因为还是会查询原文相同的语言对，所以按升序排，这样从第一条开始压入数组，后面的高使用比例的就会覆盖前面的低使用比例
			 */
			$r = $wpdb->get_results( 'select o.singular as source, t.translation_0 as target, COUNT(t.translation_0) as o2
from wp_4_gp_translations as t
         join wp_4_gp_originals as o on t.original_id = o.id
GROUP BY  target
HAVING o2 > 4
order by o2 asc' );

			foreach ( $r as $item ) {
				$memory[ strtolower( $item->source ) ] = $item->target;
			}

			// 缓存七天
			wp_cache_set( 'gp_memory', $memory, 'litepress-cn', 604800 );
		}

		return $memory[ $source ] ?? '';
	}

	/**
	 * 创建机器翻译填充任务
	 */
	public function schedule_gp_mt( $project_id ) {
		$project_id = (int) $project_id;

		$project = GP::$project->find_one( array( 'id' => $project_id ) )->fields();

		$project_name = $this->get_name_by_project_id( $project['id'] );

		// 获取待翻译原文
		$sql = <<<SQL
select *
from wp_4_gp_originals
where project_id = {$project_id}
  and id not in (
    select original_id
    from wp_4_gp_translations
    where translation_set_id = (
        select id
        from wp_4_gp_translation_sets
        where project_id = {$project_id}
    )
)
  and status = '+active';
SQL;

		$originals = GP::$original->many( $sql );

		$excluded = array(
			$project_name
		);
		for ( $i = 0; true; $i += 500 ) {
			$item = array_slice( $originals, $i, 500, true );
			if ( empty( $item ) ) {
				break;
			}

			do_action( 'lpcn_schedule_gp_mt', $project_id, $item, $excluded );
			/*
			wp_schedule_single_event( time() + 60, 'lpcn_schedule_gp_mt', [
				'project_id' => $project_id,
				'originals'  => $item,
				'excluded'   => $excluded
			] );*/
		}

		$referer = gp_url_project( $project['path'] );
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer = $_SERVER['HTTP_REFERER'];
		}

		$route            = new GP_Route();
		$route->notices[] = '该请求已加入队列，请稍后刷新页面';
		$route->redirect( $referer );
	}

	/**
	 * 通过项目ID获取项目名
	 *
	 * 这不是简单的读取项目属性，因为项目属性中的项目名通常包含了长尾副词，所以这个函数尝试用项目原文中分析项目名称
	 */
	private function get_name_by_project_id( int $project_id ): string {
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

	public function before_request() {
	}

	public function after_request() {
	}

}
