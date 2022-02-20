<?php

namespace LitePress\GlotPress\Generate_Pack;

use GP;
use GP_Locales;
use LitePress\Logger\Logger;
use stdClass;
use Translation_Entry;
use WP_Error;

class Generate_Pack {

	const BASE_PACK_DIR = WP_CONTENT_DIR . '/language-pack';

	const PACKAGE_THRESHOLD = 80;

	private string $slug = '';

	private string $type = '';

	/**
	 * -------------------------------------------------------------
	 * 标记项目原始类型
	 * -------------------------------------------------------------
	 * 对于第三方托管项目，其 type 为 other，此时难以通过 API 为用户推送翻译，
	 * 因为不知道该项目到底是插件还是主题。由此为新项目引入名为 type_raw 的
	 * meta 字段，来标识项目的“原始类型”，其值只可能是 plugin 或 theme 。
	 *
	 * 对于数据库中的 type_raw 字段，如果项目自身不存在 type_raw 值的话，则会
	 * 调用他的 type 值，由此其值将也可能是 core、other
	 *
	 * @var string
	 */
	private string $type_raw = '';

	private string $version = '';

	private array $branches = array();

	public function job( string $slug, string $type, string $version, string $branch = '', string $type_raw = '' ) {
		$this->slug     = $slug;
		$this->type     = $type;
		$this->version  = $version;
		$this->type_raw = $type_raw;

		switch ( $type ) {
			case 'plugin':
			case 'other':
				$this->branches = array(
					'body' => $slug,
				);

				$this->generate();
				break;
			case 'theme':
				$this->branches = array(
					$slug => $slug,
				);

				$this->generate();
				break;
			case 'core':
				$this->branches = array(
					'body'    => '',
					'cc'      => 'continents-cities',
					'admin'   => 'admin',
					'network' => 'admin-network',
				);

				$this->generate();
				break;
			default:
				Logger::error( 'LanguagePack', '无效的应用类型：' . $type );
		}
	}

	private function generate(): bool {
		$pack_file_paths   = array();
		$build_directory   = self::BASE_PACK_DIR . "/{$this->type}s/{$this->slug}/{$this->version}";
		$working_directory = "/tmp/litepress/language-pack-tmp/{$this->slug}";
		$export_directory  = "{$working_directory}/{$this->version}/zh_CN";

		foreach ( $this->branches as $branch => $textdomain ) {
			$gp_project = GP::$project->by_path( "{$this->type}s/$this->slug" );
			if ( ! $gp_project ) {
				Logger::error( 'LanguagePack', '无效的 slug' );

				return false;
			}

			$gp_project = GP::$project->by_path( "{$this->type}s/$this->slug/$branch" );
			if ( ! $gp_project ) {
				Logger::error( 'LanguagePack', '项目信息获取失败，项目slug：' . $this->slug );

				return false;
			}

			$translation_sets = GP::$translation_set->by_project_id( $gp_project->id );
			if ( ! $translation_sets ) {
				Logger::error( 'LanguagePack', '翻译集获取失败，项目slug：' . $this->slug );

				return false;
			}

			if ( ! $this->version ) {
				Logger::error( 'LanguagePack', '版本号为空，项目slug：' . $this->slug );

				return false;
			}

			$data                    = new stdClass();
			$data->type              = $this->type;
			$data->type_raw          = $this->type_raw;
			$data->domain            = $textdomain;
			$data->slug              = $this->slug;
			$data->version           = $this->version;
			$data->translation_sets  = $translation_sets;
			$data->gp_project        = $gp_project;
			$data->working_directory = $working_directory;
			$data->export_directory  = $export_directory;
			$r                       = $this->build_language_packs( $data );
			if ( ! $r ) {
				return false;
			}

			$pack_file_paths[] = $r;
		}

		$zip_file       = "{$export_directory}/{$this->slug}-zh_CN.zip";
		$build_zip_file = "{$build_directory}/zh_CN.zip";

		// 创建 ZIP 压缩包
		$pack_files = array();
		foreach ( $pack_file_paths as $pack_file_path ) {
			$pack_files[] = $pack_file_path['po_file'];
			$pack_files[] = $pack_file_path['mo_file'];
			foreach ( (array) $pack_file_path['json_files'] as $item ) {
				$pack_files[] = $item;
			}
		}
		$result = $this->execute_command( sprintf(
			'zip -9 -j %s %s 2>&1',
			escapeshellarg( $zip_file ),
			implode( ' ', array_map( 'escapeshellarg', $pack_files ) )
		) );

		if ( is_wp_error( $result ) ) {
			Logger::warning( 'LanguagePack', "ZIP generation for zh_CN failed.", $result->get_error_data() );

			// 清理工作目录
			$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $working_directory ) ) );

			return false;
		}

		// 创建语言包存储目录
		$result = $this->execute_command( sprintf(
			'mkdir -p %s 2>&1',
			escapeshellarg( $build_directory )
		) );

		if ( is_wp_error( $result ) ) {
			Logger::warning( 'LanguagePack', "Creating build directories for zh_CN failed.", $result->get_error_data() );

			// 清理工作目录
			$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $working_directory ) ) );

			return false;
		}

		// 将翻译的 ZIP 压缩包移动到语言包存储目录中
		$result = $this->execute_command( sprintf(
			'mv %s %s 2>&1',
			escapeshellarg( $zip_file ),
			escapeshellarg( $build_zip_file )
		) );

		if ( is_wp_error( $result ) ) {
			Logger::warning( 'LanguagePack', "Moving ZIP file for zh_CN failed.", $result->get_error_data() );

			// 清理工作目录
			$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $working_directory ) ) );

			return false;
		}

		/**
		 * 将语言包信息插入数据库。
		 */
		// 插入前先计算所有生成的文本域中的最大的一个“最后修改时间”
		$last_modified = '';
		foreach ( $pack_file_paths as $pack_file_path ) {
			if ( strtotime( $pack_file_path['last_modified'] ) > strtotime( $last_modified ) ) {
				$last_modified = $pack_file_path['last_modified'];
			}
		}
		$result = $this->insert_language_pack( $data->type, $data->type_raw, $data->slug, 'zh_CN', $data->version, $last_modified );

		if ( is_wp_error( $result ) ) {
			Logger::warning( 'LanguagePack', sprintf( "Language pack for zh_CN failed: %s", $result->get_error_message() ) );

			// Clean up.
			$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $working_directory ) ) );

			return false;
		}

		// 清理工作目录
		$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $working_directory ) ) );

		Logger::info( 'LanguagePack', "为 {$this->slug} 的 zh_CN 成功生成了语言包" );


		return true;
	}

	private function build_language_packs( $data ) {
		$existing_packs = $this->get_active_language_packs( $data->type_raw, $data->slug, $data->version );

		$set = current( $data->translation_sets );
		// 获取 WP locale.
		$gp_locale = GP_Locales::by_slug( $set->locale );
		if ( ! isset( $gp_locale->wp_locale ) ) {
			return false;
		}

		// 设置 wp_locale 直到 GlotPress 为变体返回正确的 wp_locale。
		$wp_locale = $gp_locale->wp_locale;
		if ( 'default' !== $set->slug ) {
			$wp_locale = $wp_locale . '_' . $set->slug;
		}

		// 检查是否不存在任何”当前“翻译
		if ( 0 === $set->current_count() ) {
			Logger::info( 'LanguagePack', "Skip {$wp_locale}, no translations." );

			return false;
		}

		// 检查项目的翻译百分比是否高于阈值
		$has_existing_pack = $this->has_active_language_pack( $data->type_raw, $data->slug, $wp_locale );
		if ( ! $has_existing_pack ) {
			$percent_translated = $set->percent_translated();
			if ( $percent_translated < self::PACKAGE_THRESHOLD ) {
				Logger::info( 'LanguagePack', "Skip {$wp_locale}, translations below threshold ({$percent_translated}%)." );

				return false;
			}
		} else {
			Logger::info( 'LanguagePack', "Skipping threshold check for {$wp_locale}, has existing language pack." );
		}

		// 检查自从上次打包以来翻译是否被更新过
		if ( isset( $existing_packs[ $wp_locale ] ) ) {
			$pack_time      = strtotime( $existing_packs[ $wp_locale ]->updated );
			$glotpress_time = strtotime( $set->last_modified() );

			if ( $pack_time >= $glotpress_time ) {
				Logger::info( 'LanguagePack', "Skip {$wp_locale}, no new translations." );

				return false;
			}
		}

		$entries = GP::$translation->for_export( $data->gp_project, $set, array( 'status' => 'current' ) );
		if ( ! $entries ) {
			Logger::warning( 'LanguagePack', "No current translations available for {$wp_locale}." );

			return false;
		}

		if ( empty( $data->domain ) ) {
			$filename = "$wp_locale";
		} else {
			$filename = "{$data->domain}-{$wp_locale}";
		}
		$json_file_base = "{$data->export_directory}/{$filename}";
		$po_file        = "{$data->export_directory}/{$filename}.po";
		$mo_file        = "{$data->export_directory}/{$filename}.mo";

		// 创建目录
		$this->create_directory( $data->export_directory );

		// 根据翻译条目出现的位置构建映射并分隔 po 条目。
		$mapping    = $this->build_mapping( $entries );
		$po_entries = array_key_exists( 'po', $mapping ) ? $mapping['po'] : [];

		unset( $mapping['po'] );

		// 为每个 JS 文件创建 JED json 文件。
		$json_files = $this->build_json_files( $data->gp_project, $gp_locale, $set, $mapping, $json_file_base );

		// 创建 PO 文件
		$last_modified = $this->build_po_file( $data->gp_project, $gp_locale, $set, $po_entries, $po_file );
		if ( is_wp_error( $last_modified ) ) {
			Logger::warning( 'LanguagePack', sprintf( "PO generation for {$wp_locale} failed: %s", $last_modified->get_error_message() ) );

			// 清理工作目录
			$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $data->working_directory ) ) );

			return false;
		}

		// 创建 MO 文件
		$result = $this->execute_command( sprintf(
			'msgfmt %s -o %s 2>&1',
			escapeshellarg( $po_file ),
			escapeshellarg( $mo_file )
		) );

		if ( is_wp_error( $result ) ) {
			Logger::warning( 'LanguagePack', "MO generation for {$wp_locale} failed.", $result->get_error_data() );

			// 清理工作目录
			$this->execute_command( sprintf( 'rm -rf %s', escapeshellarg( $data->working_directory ) ) );

			return false;
		}

		return array(
			'po_file'       => $po_file,
			'mo_file'       => $mo_file,
			'json_files'    => $json_files,
			'last_modified' => $last_modified,
		);
	}

	private function get_active_language_packs( $type_raw, $domain, $version ): array|object {
		global $wpdb;

		$active_language_packs = $wpdb->get_results( $wpdb->prepare(
			'SELECT language, updated FROM language_packs WHERE type_raw = %s AND domain = %s AND version = %s AND active = 1',
			$type_raw,
			$domain,
			$version
		), OBJECT_K );

		if ( ! $active_language_packs ) {
			return array();
		}

		return $active_language_packs;
	}

	private function has_active_language_pack( $type_raw, $domain, $locale ): bool {
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			'SELECT updated FROM language_packs WHERE type_raw = %s AND domain = %s AND language = %s AND active = 1 LIMIT 1',
			$type_raw,
			$domain,
			$locale
		) );
	}

	/**
	 * 创建缺失的目录
	 *
	 * @param $directory
	 */
	private function create_directory( $directory ) {
		$this->execute_command( sprintf(
			'mkdir --parents %s 2>/dev/null',
			escapeshellarg( $directory )
		) );
	}

	private function execute_command( $command ): WP_Error|bool {
		exec( $command, $output, $return_var );

		if ( $return_var ) {
			return new WP_Error( $return_var, 'Error while executing the command.', $output );
		}

		return true;
	}

	private function build_mapping( $entries ): array {
		$mapping = [];

		foreach ( $entries as $entry ) {
			/** @var Translation_Entry $entry */

			// Find all unique sources this translation originates from.
			if ( ! empty( $entry->references ) ) {
				$sources = array_map(
					function ( $reference ) {
						$parts = explode( ':', $reference );
						$file  = $parts[0];

						if ( str_ends_with( $file, '.min.js' ) ) {
							return substr( $file, 0, - 7 ) . '.js';
						}

						if ( str_ends_with( $file, '.js' ) ) {
							return $file;
						}

						return 'po';
					},
					$entry->references
				);

				$sources = array_unique( $sources );
			} else {
				$sources = [ 'po' ];
			}

			foreach ( $sources as $source ) {
				$mapping[ $source ][] = $entry;
			}
		}

		return $mapping;
	}

	private function build_json_files( $gp_project, $gp_locale, $set, $mapping, $base_dest ): array {
		$files  = array();
		$format = gp_array_get( GP::$formats, 'jed1x' );

		foreach ( $mapping as $file => $entries ) {
			// 不要为源文件创建 JSON 文件。
			if ( str_starts_with( $file, 'src/' ) || str_contains( $file, '/src/' ) ) {
				continue;
			}

			// 获取 Jed 1.x 兼容 JSON 格式的翻译。
			$json_content = $format->print_exported_file( $gp_project, $gp_locale, $set, $entries );

			// 解码并添加带有文件引用的注释以进行调试。
			$json_content_decoded          = json_decode( $json_content );
			$json_content_decoded->comment = [ 'reference' => $file ];

			$json_content = wp_json_encode( $json_content_decoded );

			$hash = md5( $file );
			$dest = "{$base_dest}-{$hash}.json";

			file_put_contents( $dest, $json_content );

			$files[] = $dest;
		}

		return $files;
	}

	private function build_po_file( $gp_project, $gp_locale, $set, $entries, $dest ): string|WP_Error {
		$format     = gp_array_get( GP::$formats, 'po' );
		$po_content = $format->print_exported_file( $gp_project, $gp_locale, $set, $entries );

		// Get last updated.
		preg_match( '/^"PO-Revision-Date: (.*)\+\d+\\\n/m', $po_content, $match );
		if ( empty( $match[1] ) ) {
			return new WP_Error( 'invalid_format', '无法解析日期。' );
		}

		file_put_contents( $dest, $po_content );

		return $match[1];
	}

	private function insert_language_pack( $type, $type_raw, $domain, $language, $version, $updated ): WP_Error|bool {
		global $wpdb;

		$type_raw = $type_raw ?: $type;

		$existing = $wpdb->get_var( $wpdb->prepare(
			'SELECT id FROM language_packs WHERE type = %s AND type_raw = %s AND domain = %s AND language = %s AND version = %s AND updated = %s AND active = 1',
			$type,
			$type_raw,
			$domain,
			$language,
			$version,
			$updated
		) );

		if ( $existing ) {
			return true;
		}

		$now      = current_time( 'mysql', 1 );
		$inserted = $wpdb->insert( 'language_packs', [
			'type'          => $type,
			'type_raw'      => $type_raw,
			'domain'        => $domain,
			'language'      => $language,
			'version'       => $version,
			'updated'       => $updated,
			'active'        => 1,
			'date_added'    => $now,
			'date_modified' => $now,
		] );

		if ( ! $inserted ) {
			return new WP_Error( 'language_pack_not_inserted', '未插入语言包。' );
		}

		// 将相同版本的旧语言包标记为非活动。
		$wpdb->query( $wpdb->prepare(
			'UPDATE language_packs SET active = 0, date_modified = %s WHERE type = %s AND type_raw = %s AND domain = %s AND language = %s AND version = %s AND id <> %d',
			$now,
			$type,
			$type_raw,
			$domain,
			$language,
			$version,
			$wpdb->insert_id
		) );

		return true;
	}

}
