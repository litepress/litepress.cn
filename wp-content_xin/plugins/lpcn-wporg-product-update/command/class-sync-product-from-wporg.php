<?php

namespace LitePress\Store\WPOrg_Product_Update;

use Exception;
use LitePress\Store\WPOrg_Product_Update\Plugin_Readme\Parser;
use LitePress\Tools\Filesystem;
use LitePress\Tools\SVN;
use WP_CLI_Command;
use WP_CLI;

class Sync_Product_From_Wporg extends WP_CLI_Command {

	const PLUGIN_SVN_BASE = 'https://plugins.svn.wordpress.org';

	public function worker( $args, $assoc_args ) {
		echo json_encode( $this->export_and_parse_plugin( 'woocommerce' ) );
		echo 'ALL OK';
	}

	/**
	 * 处理 SVN 插件到插件目录的导入。
	 *
	 * @param string $plugin_slug The slug of the plugin to import.
	 * @param array $svn_changed_tags       A list of tags/trunk which the SVN change touched. Optional.
	 * @param array|int $svn_revision_triggered The SVN revision which this import has been triggered by. Optional.
	 *
	 *@throws \Exception
	 *
	 */
	public function import_from_svn( string $plugin_slug, array $svn_changed_tags = array( 'trunk' ), array|int $svn_revision_triggered = 0 ): bool {
		$plugin = Plugin_Directory::get_plugin_post( $plugin_slug );
		if ( ! $plugin ) {
			throw new Exception( 'Unknown Plugin' );
		}

		$data = $this->export_and_parse_plugin( $plugin_slug );

		$readme          = $data['readme'];
		$assets          = $data['assets'];
		$headers         = $data['plugin_headers'];
		$stable_tag      = $data['stable_tag'];
		$last_committer  = $data['last_committer'];
		$last_revision   = $data['last_revision'];
		$tagged_versions = $data['tagged_versions'];
		$last_modified   = $data['last_modified'];
		$blocks          = $data['blocks'];
		$block_files     = $data['block_files'];

		// Release confirmation
		if ( $plugin->release_confirmation ) {
			if ( 'trunk' === $stable_tag ) {
				throw new Exception( 'Plugin cannot be released from trunk due to release confirmation being enabled.' );
			}

			$release = Plugin_Directory::get_release( $plugin, $stable_tag );

			// This tag is unknown? Trigger email.
			if ( ! $release ) {
				Plugin_Directory::add_release(
					$plugin,
					[
						'tag'       => $stable_tag,
						'version'   => $headers->Version,
						'committer' => [ $last_committer ],
						'revision'  => [ $last_revision ]
					]
				);

				$email = new Release_Confirmation_Email(
					$plugin,
					Tools::get_plugin_committers( $plugin_slug ),
					[
						'who'     => $last_committer,
						'readme'  => $readme,
						'headers' => $headers,
					]
				);
				$email->send();

				throw new Exception( 'Plugin release not confirmed; email triggered.' );
			}

			// Check that the tag is approved.
			if ( ! $release['confirmed'] ) {

				if ( ! in_array( $last_committer, $release['committer'], true ) ) {
					$release['committer'][] = $last_committer;
				}
				if ( ! in_array( $last_revision, $release['revision'], true ) ) {
					$release['revision'][] = $last_revision;
				}

				// Update with ^
				Plugin_Directory::add_release( $plugin, $release );

				throw new Exception( 'Plugin release not confirmed.' );
			}

			// At this point we can assume that the release was confirmed, and should be imported.
		}

		$content = '';
		if ( $readme->sections ) {
			foreach ( $readme->sections as $section => $section_content ) {
				$content .= "\n\n<!--section={$section}-->\n{$section_content}";
			}
		} elseif ( ! empty( $headers->Description ) ) {
			$content = "<!--section=description-->\n{$headers->Description}";
		}

		// Fallback to the plugin title if the readme didn't contain it.
		$plugin->post_title   = trim( $readme->name ) ?: strip_tags( $headers->Name ) ?: $plugin->post_title;
		$plugin->post_content = trim( $content ) ?: $plugin->post_content;
		$plugin->post_excerpt = trim( $readme->short_description ) ?: $headers->Description ?: $plugin->post_excerpt;

		/*
		 * Bump last updated if:
		 * - The version has changed.
		 * - The post_modified is empty, which is the case for many initial checkins.
		 * - A tag (or trunk) commit is made to the current stable. The build has changed, even if not new version.
		 */
		if (
			( ! isset( $headers->Version ) || $headers->Version != get_post_meta( $plugin->ID, 'version', true ) ) ||
			$plugin->post_modified == '0000-00-00 00:00:00' ||
			( $svn_changed_tags && in_array( ( $stable_tag ?: 'trunk' ), $svn_changed_tags, true ) )
		) {
			if ( $last_modified ) {
				$plugin->post_modified = $plugin->post_modified_gmt = $last_modified;
			} else {
				$plugin->post_modified = $plugin->post_modified_gmt = current_time( 'mysql' );
			}
		}

		// Plugins should move from 'approved' to 'publish' on first parse
		// `export_and_parse_plugin()` will throw an exception in the case where plugin files cannot be found,
		// so by this time the plugin should be live.
		if ( 'approved' === $plugin->post_status ) {
			$plugin->post_status = 'publish';

			// The post date should be set to when the plugin is first set live.
			$plugin->post_date = $plugin->post_date_gmt = current_time( 'mysql' );
		}

		wp_update_post( $plugin );

		// Set categories if there aren't any yet. wp-admin takes precedent.
		if ( ! wp_get_object_terms( $plugin->ID, 'plugin_category', array( 'fields' => 'ids' ) ) ) {
			wp_set_object_terms( $plugin->ID, Tag_To_Category::map( $readme->tags ), 'plugin_category' );
		}

		// Set tags from the readme
		wp_set_object_terms( $plugin->ID, $readme->tags, 'plugin_tags' );

		// Update the contributors list
		wp_set_object_terms( $plugin->ID, $readme->contributors, 'plugin_contributors' );

		// Update the committers list
		Tools::sync_plugin_committers_with_taxonomy( $plugin->post_name );

		if ( in_array( 'adopt-me', $readme->tags ) ) {
			wp_set_object_terms( $plugin->ID, 'adopt-me', 'plugin_section' );
		} else {
			wp_remove_object_terms( $plugin->ID, 'adopt-me', 'plugin_section' );
		}

		// Update the tested-up-to value
		$tested = $readme->tested;
		if ( function_exists( 'wporg_get_version_equivalents' ) ) {
			foreach ( wporg_get_version_equivalents() as $latest_compatible_version => $compatible_with ) {
				if ( in_array( $readme->tested, $compatible_with, true ) ) {
					$tested = $latest_compatible_version;
					break;
				}
			}
		}

		// Update all readme meta
		foreach ( $this->readme_fields as $readme_field ) {
			$value = ( 'tested' == $readme_field ) ? $tested : $readme->$readme_field;
			update_post_meta( $plugin->ID, $readme_field, wp_slash( $value ) );
		}

		// Store the plugin headers we need. Note that 'Version', 'RequiresWP', and 'RequiresPHP' are handled below.
		foreach ( $this->plugin_headers as $plugin_header => $meta_field ) {
			update_post_meta( $plugin->ID, $meta_field, ( isset( $headers->$plugin_header ) ? wp_slash( $headers->$plugin_header ) : '' ) );
		}

		// Update the Requires and Requires PHP fields, prefering those from the Plugin Headers.
		// Unfortunately the value within $headers is not always a well-formed value.
		$requires     = $readme->requires;
		$requires_php = $readme->requires_php;
		if ( $headers->RequiresWP && preg_match( '!^[\d.]{3,}$!', $headers->RequiresWP ) ) {
			$requires = $headers->RequiresWP;
		}
		if ( $headers->RequiresPHP && preg_match( '!^[\d.]{3,}$!', $headers->RequiresPHP ) ) {
			$requires_php = $headers->RequiresPHP;
		}

		update_post_meta( $plugin->ID, 'requires',           wp_slash( $requires ) );
		update_post_meta( $plugin->ID, 'requires_php',       wp_slash( $requires_php ) );
		update_post_meta( $plugin->ID, 'tagged_versions',    wp_slash( array_keys( $tagged_versions ) ) );
		update_post_meta( $plugin->ID, 'tags',               wp_slash( $tagged_versions ) );
		update_post_meta( $plugin->ID, 'sections',           wp_slash( array_keys( $readme->sections ) ) );
		update_post_meta( $plugin->ID, 'assets_screenshots', wp_slash( $assets['screenshot'] ) );
		update_post_meta( $plugin->ID, 'assets_icons',       wp_slash( $assets['icon'] ) );
		update_post_meta( $plugin->ID, 'assets_banners',     wp_slash( $assets['banner'] ) );
		update_post_meta( $plugin->ID, 'last_updated',       wp_slash( $plugin->post_modified_gmt ) );
		update_post_meta( $plugin->ID, 'plugin_status',      wp_slash( $plugin->post_status ) );

		// Calculate the 'plugin color' from the average color of the banner if provided. This is used for fallback icons.
		$banner_average_color = '';
		if ( $first_banner = reset( $assets['banner'] ) ) {
			// The Banners are not stored locally, which is why a URL is used here
			$banner_average_color = Tools::get_image_average_color( Template::get_asset_url( $plugin, $first_banner, false /* no CDN */ ) );
		}
		update_post_meta( $plugin->ID, 'assets_banners_color', wp_slash( $banner_average_color ) );

		// Store the block data, if known
		if ( count( $blocks ) ) {
			$changed = update_post_meta( $plugin->ID, 'all_blocks', $blocks );
			if ( $changed || count ( get_post_meta( $plugin->ID, 'block_name' ) ) !== count ( $blocks ) ) {
				delete_post_meta( $plugin->ID, 'block_name' );
				delete_post_meta( $plugin->ID, 'block_title' );
				foreach ( $blocks as $block ) {
					add_post_meta( $plugin->ID, 'block_name', $block->name, false );
					add_post_meta( $plugin->ID, 'block_title', ( $block->title ?: $plugin->post_title ), false );
				}
			}
		} else {
			delete_post_meta( $plugin->ID, 'all_blocks' );
			delete_post_meta( $plugin->ID, 'block_name' );
			delete_post_meta( $plugin->ID, 'block_title' );
		}

		// Only store block_files for plugins in the block directory
		if ( count( $block_files ) && has_term( 'block', 'plugin_section', $plugin->ID ) ) {
			update_post_meta( $plugin->ID, 'block_files', $block_files );
		} else {
			delete_post_meta( $plugin->ID, 'block_files' );
		}

		$current_stable_tag = get_post_meta( $plugin->ID, 'stable_tag', true ) ?: 'trunk';

		$this->rebuild_affected_zips( $plugin_slug, $stable_tag, $current_stable_tag, $svn_changed_tags, $svn_revision_triggered );

		// Finally, set the new version live.
		update_post_meta( $plugin->ID, 'stable_tag', wp_slash( $stable_tag ) );
		update_post_meta( $plugin->ID, 'version', wp_slash( $headers->Version ) );

		// Ensure that the API gets the updated data
		API_Update_Updater::update_single_plugin( $plugin->post_name );

		// Import Tide data
		Tide_Sync::sync_data( $plugin->post_name );

		// Run the Block Directory e2e tests if applicable.
		if ( has_term( 'block', 'plugin_section', $plugin->ID ) ) {
			Block_e2e::run( $plugin->post_name );
		}

		/**
		 * Action that fires after a plugin is imported.
		 *
		 * @param WP_Post $plugin         The plugin updated.
		 * @param string  $stable_tag     The new stable tag for the plugin.
		 * @param string  $old_stable_tag The previous stable tag for the plugin.
		 * @param array   $changed_tags   The list of SVN tags/trunk affected to trigger the import.
		 * @param int     $svn_revision   The SVN revision that triggered the import.
		 */
		do_action( 'wporg_plugins_imported', $plugin, $stable_tag, $current_stable_tag, $svn_changed_tags, $svn_revision_triggered );

		return true;
	}

	/**
	 * 从 SVN 签出插件并读取该插件的所有信息
	 *
	 * - 签出 /trunk/。
	 * - 如果指定 tag，则创建 /stable/ 的签出，否则签出 /trunk/。
	 * - 对于 readme.md 和 readme.txt 优先后者。
	 * - 在 /$stable/ 和 /assets/ 中搜索屏幕截图（远程列出）。
	 *
	 * @param string $plugin_slug 要解析的插件的 slug。
	 *
	 * @return array {
	 *   'readme', 'stable_tag', 'plugin_headers', 'assets', 'tagged_versions'
	 * }
	 * @throws \Exception
	 *
	 */
	private function export_and_parse_plugin( string $plugin_slug ): array {
		$tmp_dir = Filesystem::temp_directory( "process-{$plugin_slug}" );

		// 假设用户使用 trunk 分支发布插件的稳定版
		$stable_tag = 'trunk';

		// 查找 trunk 自述文件，远程列出以避免签出整个目录。
		$trunk_files = SVN::ls( self::PLUGIN_SVN_BASE . "/{$plugin_slug}/trunk" ) ?: array();

		// 查找插件的标记版本列表。
		$tagged_versions     = [];
		$tagged_versions_raw = SVN::ls( "https://plugins.svn.wordpress.org/{$plugin_slug}/tags/", true ) ?: [];
		foreach ( $tagged_versions_raw as $entry ) {
			// 放弃文件
			if ( 'dir' !== $entry['kind'] ) {
				continue;
			}

			$tag = $entry['filename'];

			// 为以 . 开头的插件版本添加前缀 0，变为例如 0.1
			if ( str_starts_with( $tag, '.' ) ) {
				$tag = "0{$tag}";
			}

			$tagged_versions[ $tag ] = [
				'tag'    => $entry['filename'],
				'author' => $entry['author'],
				'date'   => $entry['date'],
			];
		}

		// 并非所有插件都使用“trunk”，有些插件使用版本标签。
		if ( ! $trunk_files ) {
			if ( ! $tagged_versions ) {
				throw new Exception( '插件在 trunk 中没有文件，也没有版本标签' );
			}

			$stable_tag = array_reduce( array_keys( $tagged_versions ), function ( $a, $b ) {
				return version_compare( $a, $b, '>' ) ? $a : $b;
			} );
		}

		// 插件不必有自述文件。
		$trunk_readme_files = preg_grep( '!^readme.(txt|md)$!i', $trunk_files );
		if ( $trunk_readme_files ) {
			$trunk_readme_file = reset( $trunk_readme_files );
			// 如果两者都存在，则首选 readme.txt 而不是 readme.md。
			foreach ( $trunk_readme_files as $f ) {
				if ( '.txt' == strtolower( substr( $f, - 4 ) ) ) {
					$trunk_readme_file = $f;
					break;
				}
			}

			$trunk_readme_file = self::PLUGIN_SVN_BASE . "/{$plugin_slug}/trunk/{$trunk_readme_file}";
			$trunk_readme      = new Parser( $trunk_readme_file );

			$stable_tag = $trunk_readme->stable_tag;
		}

		$svn_info = false;
		if ( $stable_tag && 'trunk' != $stable_tag ) {
			$stable_url = self::PLUGIN_SVN_BASE . "/{$plugin_slug}/tags/{$stable_tag}";
			$svn_info   = SVN::info( $stable_url );

			if ( ! $svn_info['result'] && str_starts_with( $stable_tag, '0.' ) ) {
				// 处理存储为 0.blah 但位于 /tags/.blah 中的标签
				$_stable_tag = substr( $stable_tag, 1 );
				$stable_url  = self::PLUGIN_SVN_BASE . "/{$plugin_slug}/tags/{$_stable_tag}";
				$svn_info    = SVN::info( $stable_url );
			}

			// 验证标签是否有文件，如果没有则回退到主干。
			if ( ! SVN::ls( $stable_url ) ) {
				$svn_info = false;
			}
		}

		if ( ! $svn_info || ! $svn_info['result'] ) {
			$stable_tag = 'trunk';
			$stable_url = self::PLUGIN_SVN_BASE . "/{$plugin_slug}/trunk";
			$svn_info   = SVN::info( $stable_url );
		}

		if ( ! $svn_info['result'] ) {
			throw new Exception( 'Could not find stable SVN URL: ' . implode( ' ', reset( $svn_info['errors'] ) ) );
		}

		$last_modified = false;
		if ( preg_match( '/^([0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{1,2}:[0-9]{2}:[0-9]{2})/', $svn_info['result']['Last Changed Date'] ?? '', $m ) ) {
			$last_modified = $m[0];
		}

		$last_committer = $svn_info['result']['Last Changed Author'] ?? '';
		$last_revision  = $svn_info['result']['Last Changed Rev'] ?? 0;

		$svn_export = SVN::export(
			$stable_url,
			$tmp_dir . '/export',
			array(
				'ignore-externals',
			)
		);

		if ( ! $svn_export['result'] || empty( $svn_export['revision'] ) ) {
			if ( ! $trunk_files ) {
				throw new Exception( '插件在 trunk 中没有文件，也没有版本标签' );
			}

			throw new Exception( '无法读取 SVN: ' . implode( ' ', reset( $svn_export['errors'] ) ) );
		}

		// readme 可能实际上并不存在，但没关系。
		$readme = $this->find_readme_file( $tmp_dir . '/export' );
		$readme = new Parser( $readme );

		// 但是必须有有效的插件标头。
		$plugin_headers = $this->find_plugin_headers( "$tmp_dir/export" );
		if ( ! $plugin_headers ) {
			throw new Exception( '找不到插件标头。' );
		}

		// 现在我们在 /assets/ 文件夹中查找横幅、屏幕截图和图标。
		$assets = array(
			'screenshot' => array(),
			'banner'     => array(),
			'icon'       => array(),
		);

		$asset_limits = array(
			'screenshot' => 10 * MB_IN_BYTES,
			'banner'     => 4 * MB_IN_BYTES,
			'icon'       => 1 * MB_IN_BYTES,
		);

		$svn_assets_folder = SVN::ls( self::PLUGIN_SVN_BASE . "/{$plugin_slug}/assets/", true /* verbose */ );
		if ( $svn_assets_folder ) { // /assets/ may not exist.
			foreach ( $svn_assets_folder as $asset ) {
				// screenshot-0(-rtl)(-de_DE).(png|jpg|jpeg|gif) || banner-772x250.PNG || icon.svg
				if ( ! preg_match( '!^(?P<type>screenshot|banner|icon)(?:-(?P<resolution>\d+(?:\D\d+)?)(-rtl)?(?:-(?P<locale>[a-z]{2,3}(?:_[A-Z]{2})?(?:_[a-z0-9]+)?))?\.(png|jpg|jpeg|gif)|\.svg)$!iu', $asset['filename'], $m ) ) {
					continue;
				}

				$type = strtolower( $m['type'] );

				// 不要导入过大的资产。
				if ( $asset['filesize'] > $asset_limits[ $type ] ) {
					continue;
				}

				$filename   = $asset['filename'];
				$revision   = $asset['revision'];
				$location   = 'assets';
				$resolution = $m['resolution'] ?? false;
				$locale     = $m['locale'] ?? false;

				// 确保分辨率采用预期的 123x123 格式。
				// 分辨率也是屏幕截图编号，在这种情况下，它只是字符串数字。
				if ( $resolution && 'screenshot' === $type ) {
					$resolution = (string) ( (int) $resolution );
				} else if ( $resolution ) {
					$resolution = preg_replace( '/[^0-9]/u', 'x', $resolution );
				}

				$assets[ $type ][ $asset['filename'] ] = compact( 'filename', 'revision', 'resolution', 'location', 'locale' );
			}
		}

		// 在 stable 插件文件夹中查找屏幕截图（但不要覆盖 /assets/）
		foreach ( Filesystem::list_files( "$tmp_dir/export/", false /* non-recursive */, '!^screenshot-\d+\.(jpeg|jpg|png|gif)$!' ) as $plugin_screenshot ) {
			$filename      = basename( $plugin_screenshot );
			$screenshot_id = substr( $filename, strpos( $filename, '-' ) + 1 );
			$screenshot_id = substr( $screenshot_id, 0, strpos( $screenshot_id, '.' ) );

			if ( isset( $assets['screenshot'][ $filename ] ) ) {
				// 跳过它，它已经存在于 /assets/ 中
				continue;
			}

			// 不要导入过大的资产。
			if ( filesize( $plugin_screenshot ) > $asset_limits['screenshot'] ) {
				continue;
			}

			$assets['screenshot'][ $filename ] = array(
				'filename'   => $filename,
				'revision'   => $svn_export['revision'],
				'resolution' => $screenshot_id,
				'location'   => 'plugin',
			);
		}

		if ( 'trunk' === $stable_tag ) {
			$stable_path = $stable_tag;
		} else {
			$stable_path = 'tags/';
			$stable_path .= $_stable_tag ?? $stable_tag;
		}

		// 查找已注册的块及其文件。
		$blocks                      = array();
		$block_files                 = array();
		$potential_block_directories = array( '.' );
		$base_dir                    = "$tmp_dir/export";

		$block_json_files = Filesystem::list_files( $base_dir, true, '!(?:^|/)block\.json$!i' );
		if ( ! empty( $block_json_files ) ) {
			foreach ( $block_json_files as $filename ) {
				$blocks_in_file                = $this->find_blocks_in_file( $filename );
				$relative_filename             = str_replace( "$base_dir/", '', $filename );
				$potential_block_directories[] = dirname( $relative_filename );
				foreach ( $blocks_in_file as $block ) {
					$blocks[ $block->name ] = $block;

					$extracted_files = $this->extract_file_paths_from_block_json( $block, dirname( $relative_filename ) );
					if ( ! empty( $extracted_files ) ) {
						$block_files = array_merge(
							$block_files,
							array_map(
								function ( $file ) use ( $stable_path ) {
									return "/$stable_path/" . ltrim( $file, '\\' );
								},
								$extracted_files
							)
						);
					}
				}
			}
		} else {
			foreach ( Filesystem::list_files( $base_dir, true, '!\.(?:php|js|jsx)$!i' ) as $filename ) {
				$blocks_in_file = $this->find_blocks_in_file( $filename );
				if ( ! empty( $blocks_in_file ) ) {
					$relative_filename             = str_replace( "$base_dir/", '', $filename );
					$potential_block_directories[] = dirname( $relative_filename );
					foreach ( $blocks_in_file as $block ) {
						$blocks[ $block->name ] = $block;
					}
				}
			}
		}

		foreach ( $blocks as $block_name => $block ) {
			if ( empty( $block->title ) ) {
				$blocks[ $block_name ]->title = $readme->name;
			}
		}

		// 从块列表中删除所有核心块。
		$blocks = array_filter(
			$blocks,
			function ( $block_name ) {
				return ! str_starts_with( $block_name, 'core/' );
			},
			ARRAY_FILTER_USE_KEY
		);

		// 过滤块列表，使父块排在第一位。
		if ( count( $blocks ) > 1 ) {
			$children = array_filter(
				$blocks,
				function ( $block ) {
					return isset( $block->parent ) && count( $block->parent );
				}
			);

			$parent = array_filter(
				$blocks,
				function ( $block ) {
					return ! isset( $block->parent ) || ! count( $block->parent );
				}
			);

			$blocks = array_merge( $parent, $children );
		}

		// 如果在 block.json 中没有找到块文件，则仅搜索块文件。
		if ( empty( $block_files ) ) {
			$build_files = self::find_possible_block_assets( $base_dir, $potential_block_directories );

			foreach ( $build_files as $file ) {
				$block_files[] = "/$stable_path/" . ltrim( str_replace( "$base_dir/", '', $file ), '/' );
			}
		}

		// 只允许 js 或 css 文件
		$block_files = array_unique( array_filter( $block_files, function ( $filename ) {
			return preg_match( '!\.(?:js|jsx|css)$!i', $filename );
		} ) );

		return compact(
			'readme',
			'stable_tag',
			'last_modified',
			'last_committer',
			'last_revision',
			'tmp_dir',
			'plugin_headers',
			'assets',
			'tagged_versions',
			'blocks',
			'block_files'
		);
	}

	/**
	 * 找到插件自述文件。
	 *
	 * 查找 readme.txt 或 readme.md 文件，优先考虑 readme.txt。
	 *
	 * @param string $directory 在其中搜索自述文件的目录。
	 *
	 * @return string 插件 readme.txt 或 readme.md 文件名。
	 */
	public static function find_readme_file( string $directory ): string {
		$files = Filesystem::list_files( $directory, false /* non-recursive */, '!(?:^|/)readme\.(txt|md)$!i' );

		// 优先考虑 readme.txt
		foreach ( $files as $f ) {
			if ( '.txt' == strtolower( substr( $f, - 4 ) ) ) {
				return $f;
			}
		}

		return reset( $files );
	}

	/**
	 * 查找给定目录的插件标头。
	 *
	 * @param string $directory 插件的目录。
	 *
	 * @return object|bool 插件标头。
	 */
	public static function find_plugin_headers( string $directory ): object|bool {
		$files = Filesystem::list_files( $directory, false, '!\.php$!i' );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// 插件可能包含多个文件，我们根据文件中是否包含 `Plugin Name:` 来判断该文件是否是入口文件。
		$possible_headers = false;
		foreach ( $files as $file ) {
			$data = get_plugin_data( $file, false, false );
			if ( array_filter( $data ) ) {
				if ( $data['Name'] ) {
					return (object) $data;
				} else {
					$possible_headers = (object) $data;
				}
			}
		}

		if ( $possible_headers ) {
			return $possible_headers;
		}

		return false;
	}

	/**
	 * 查找在单个文件中注册的 Gutenberg 块。
	 *
	 * @param string $filename 文件的路径名。
	 *
	 * @return array 表示块的对象数组，在可能的情况下对应于 block.json 格式。
	 */
	static function find_blocks_in_file( string $filename ): array {

		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		$blocks = array();

		if ( 'js' === $ext || 'jsx' === $ext ) {
			// 解析一个 js 风格的 registerBlockType() 调用。
			// 请注意，这仅适用于块名称和标题的文字字符串，并假定该顺序。
			$contents = file_get_contents( $filename );
			if ( $contents && preg_match_all( "#registerBlockType[^{}]{0,500}[(]\s*[\"']([-\w]+/[-\w]+)[\"']\s*,\s*[{]\s*title\s*:[\s\w(]*[\"']([^\"']*)[\"']#ms", $contents, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$blocks[] = (object) [
						'name'  => $match[1],
						'title' => $match[2],
					];
				}
			}
		}
		if ( 'php' === $ext ) {
			// 解析一个 php 风格的 register_block_type() 调用。
			// 这再次假设文字字符串，并且只解析名称和标题。
			$contents = file_get_contents( $filename );
			if ( $contents && preg_match_all( "#register_block_type\s*[(]\s*['\"]([-\w]+/[-\w]+)['\"]#ms", $contents, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$blocks[] = (object) [
						'name'  => $match[1],
						'title' => null,
					];
				}
			}
		}
		if ( 'block.json' === basename( $filename ) ) {
			// 一个 block.json 文件应该有我们想要的一切。
			$validator = new Block_JSON\Validator();
			$block     = Block_JSON\Parser::parse( array( 'file' => $filename ) );
			$result    = $validator->validate( $block );
			if ( ! is_wp_error( $block ) && is_wp_error( $result ) ) {
				$required_valid_props = array(
					'block.json',
					'block.json:editorScript',
					'block.json:editorStyle',
					'block.json:name',
					'block.json:script',
					'block.json:style',
				);
				$invalid_props        = array_intersect( $required_valid_props, $result->get_error_data( 'error' ) ?: [] );
				if ( empty( $invalid_props ) ) {
					$blocks[] = $block;
				}
			} elseif ( true === $result ) {
				$blocks[] = $block;
			}
		}

		return $blocks;
	}

	/**
	 * 从导入的 block.json 获取脚本和样式文件路径。
	 *
	 * @param object $parsed_json
	 * @param string $block_json_path
	 *
	 * @return array
	 */
	static function extract_file_paths_from_block_json( object $parsed_json, string $block_json_path = '' ): array {
		$files = array();

		$props = array( 'editorScript', 'script', 'editorStyle', 'style' );

		foreach ( $props as $prop ) {
			if ( isset( $parsed_json->$prop ) ) {
				$files[] = trailingslashit( $block_json_path ) . $parsed_json->$prop;
			}
		}

		return $files;
	}

	/**
	 * 在给定目录中查找可能的 JS 和 CSS 块资产文件。
	 *
	 * @param string $base_dir 搜索的基本路径。
	 * @param array|null $potential_block_directories 可选。可能包含块资产的子目录（如果已知）。
	 *
	 * @return array
	 */
	static function find_possible_block_assets( string $base_dir, array $potential_block_directories = null ): array {
		if ( empty( $potential_block_directories ) || ! is_array( $potential_block_directories ) ) {
			$potential_block_directories = array( '.' );
		}

		$build_files = array();

		foreach ( $potential_block_directories as $block_dir ) {
			// dirname() 返回 . 当没有目录分隔符时。
			if ( '.' === $block_dir ) {
				$block_dir = '';
			}

			// 首先寻找一个专用的“build”或“dist”目录。
			foreach ( array( 'build', 'dist' ) as $dirname ) {
				if ( is_dir( "$base_dir/$block_dir/$dirname" ) ) {
					$build_files += Filesystem::list_files( "$base_dir/$block_dir/$dirname", true, '!\.(?:js|jsx|css)$!i' );
				}
			}

			// 至少要有一个 JS 文件，如果只找到 css 就继续找。
			if ( empty( preg_grep( '!\.(?:js|jsx)$!i', $build_files ) ) ) {
				// 然后检查当前目录中文件名中带有“build”或“min”的文件。
				$build_files += Filesystem::list_files( "$base_dir/$block_dir", false, '![_\-\.]+(?:build|dist|min)[_\-\.]+!i' );
			}

			if ( empty( preg_grep( '!\.(?:js|jsx)$!i', $build_files ) ) ) {
				// 最后，只需抓取当前目录中的所有 js/css 文件。
				$build_files += Filesystem::list_files( "$base_dir/$block_dir", false, '#(?<!webpack\.config)\.(?:js|jsx|css)$#i' );
			}
		}

		if ( empty( preg_grep( '!\.(?:js|jsx)$!i', $build_files ) ) ) {
			// 潜在的块目录中没有任何内容。 检查我们是否以某种方式错过了根目录中的 build/dist 目录。
			foreach ( array( 'build', 'dist' ) as $dirname ) {
				if ( is_dir( "$base_dir/$dirname" ) ) {
					$build_files += Filesystem::list_files( "$base_dir/$dirname", true, '!\.(?:js|jsx|css)$!i' );
				}
			}
		}

		if ( empty( preg_grep( '!\.(?:js|jsx)$!i', $build_files ) ) ) {
			// 依然没有。 进行最后一次疯狂查找。
			$build_files += Filesystem::list_files( $base_dir, false, '!\.(?:js|jsx|css)$!i' );
		}

		return array_unique( $build_files );
	}

}

WP_CLI::add_command( 'lpcn sync_product_from_wporg', __NAMESPACE__ . '\Sync_Product_From_Wporg' );
