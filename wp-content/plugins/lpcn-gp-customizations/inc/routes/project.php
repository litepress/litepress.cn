<?php

namespace LitePress\GlotPress\Customizations\Inc\Routes;

use GP;
use GP_Locales;
use GP_Project;
use GP_Route_Project;

class Route_Project extends GP_Route_Project {

	public function import_originals_post( $project_path ) {
		$project_path = sanitize_text_field( $_POST['sub_project_path'] );

		$project = GP::$project->by_path( $project_path );

		if ( ! $project ) {
			return $this->die_with_404();
		}

		/**
		 * 不验证 Nonce
		 */
		//if ( $this->invalid_nonce_and_redirect( 'import-originals_' . $project->id ) ) {
		//	return;
		//}

		if ( $this->cannot_and_redirect( 'write', 'project', $project->id ) ) {
			return;
		}

		if ( ! is_uploaded_file( $_FILES['import-file']['tmp_name'] ) ) {
			// TODO: different errors for different upload conditions
			$this->redirect_with_error( __( 'Error uploading the file.', 'glotpress' ) );

			return;
		}

		// 检查上传的是否是 zip 压缩包。
		if ( str_ends_with( $_FILES['import-file']['name'], '.zip' ) ) {
			// 如果是 zip 的话就尝试从中提取 po 文件，再把 po 文件交给 GlotPress 处理
			$tmp_name = $_FILES['import-file']['tmp_name'];
			exec( sprintf( 'mv %s %s', $tmp_name, escapeshellarg( $tmp_name . '.zip' ) ), $output, $return_var );
			if ( $return_var ) {
				$this->redirect_with_error( '系统命令执行失败，上传的临时文件无法被重命名，请联系管理员解决：' . $output );

				return;
			}
			exec( sprintf( 'unzip -nq %s -d %s', escapeshellarg( $tmp_name . '.zip' ), $tmp_name ), $output, $return_var );
			if ( $return_var ) {
				$this->redirect_with_error( '该 Zip 压缩包无法被解压：' . $output );

				return;
			}

			$files = scandir( $tmp_name );
			if ( 3 === count( $files ) ) {
				$root_path = "$tmp_name/{$files[2]}";
			} else {
				$root_path = $tmp_name;
			}

			exec( sprintf( 'wp i18n make-pot %s %s --ignore-domain', escapeshellarg( $root_path ), escapeshellarg( '/tmp/lpcn-lang.pot' ) ), $output, $return_var );
			if ( $return_var || ! file_exists( '/tmp/lpcn-lang.pot' ) ) {
				$this->redirect_with_error( '翻译提取失败：' . $output[0] ?? '' );

				return;
			}

			if ( file_exists( $tmp_name ) ) {
				unlink( $tmp_name );
			}

			if ( file_exists( $tmp_name . '.zip' ) ) {
				unlink( $tmp_name . '.zip' );
			}

			$file_name = 'lpcn-lang.pot';
			$file_path = '/tmp/lpcn-lang.pot';
		} else {
			$file_name = $_FILES['import-file']['name'];
			$file_path = $_FILES['import-file']['tmp_name'];
		}

		$format = gp_get_import_file_format( gp_post( 'format', 'po' ), $file_name );
		if ( ! $format ) {
			$this->redirect_with_error( __( 'No such format.', 'glotpress' ) );

			return;
		}

		$translations = $format->read_originals_from_file( $file_path );

		if ( ! $translations ) {
			$this->redirect_with_error( __( 'Couldn&#8217;t load translations from file!', 'glotpress' ) );

			return;
		}

		list( $originals_added, $originals_existing, $originals_fuzzied, $originals_obsoleted, $originals_error ) = GP::$original->import_for_project( $project, $translations );

		$notice = sprintf(
		/* translators: 1: Added strings count. 2: Updated strings count. 3: Fuzzied strings count. 4: Obsoleted strings count. */
			__( '%1$s new strings added, %2$s updated, %3$s fuzzied, and %4$s obsoleted.', 'glotpress' ),
			$originals_added,
			$originals_existing,
			$originals_fuzzied,
			$originals_obsoleted
		);

		// 成功导入原文后将项目版本号更新上
		$version = sanitize_text_field( $_POST['version'] );
		gp_update_meta( $project->parent_project_id, 'version', $version, 'project' );

		if ( $originals_error ) {
			$notice .= ' ' . sprintf(
				/* translators: %s: number of errors */
					_n( '%s new string was not imported due to an error.', '%s new strings were not imported due to an error.', $originals_error, 'glotpress' ),
					$originals_error
				);
		}

		$this->notices[] = $notice;

		$this->redirect( gp_url_project( $project ) . 'zh-cn/default/' );
	}

	public function edit_post( $project_path ) {
		$project = GP::$project->by_path( $project_path );

		if ( ! $project ) {
			$this->die_with_404();
		}

		if ( $this->invalid_nonce_and_redirect( 'edit-project_' . $project->id ) ) {
			return;
		}

		if ( $this->cannot_and_redirect( 'write', 'project', $project->id ) ) {
			return;
		}

		$updated_project = new GP_Project( gp_post( 'project' ) );
		if ( $this->invalid_and_redirect( $updated_project, gp_url_project( $project, '-edit' ) ) ) {
			return;
		}

		// TODO: add id check as a validation rule
		if ( $project->id == $updated_project->parent_project_id ) {
			$this->errors[] = __( 'The project cannot be parent of itself!', 'glotpress' );
		} elseif ( $project->save( $updated_project ) ) {
			$this->notices[] = __( 'The project was saved.', 'glotpress' );
		} else {
			$this->errors[] = __( 'Error in saving project!', 'glotpress' );
		}

		/**
		 * 插入封面图
		 */
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		if ( isset( $_FILES['icon'] ) && ! empty( $_FILES['icon'] ) ) {
			$uploadedfile     = $_FILES['icon'];
			$upload_overrides = array(
				'test_form' => false
			);

			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			if ( $movefile && ! isset( $movefile['error'] ) ) {
				gp_update_meta( $project->id, 'icon', $movefile['url'], 'project' );
			} else {
				$this->errors[] = '封面图上传失败：' . $movefile['error'];
			}
		}

		$project->reload();

		$this->redirect( gp_url_project( $project ) );
	}

	public function new_post() {
		if ( $this->invalid_nonce_and_redirect( 'add-project' ) ) {
			return;
		}

		if ( ! isset( $_FILES['icon'] ) || empty( $_FILES['icon'] ) ) {
			$project        = new GP_Project();
			$this->errors[] = '封面图不能为空';
			$this->tmpl( 'project-new', get_defined_vars() );

			return;
		}

		$post              = gp_post( 'project' );
		$parent_project_id = gp_array_get( $post, 'parent_project_id', null );

		if ( $this->cannot_and_redirect( 'write', 'project', $parent_project_id ) ) {
			return;
		}

		$new_project = new GP_Project( $post );

		if ( $this->invalid_and_redirect( $new_project ) ) {
			return;
		}

		$project = GP::$project->create_and_select( $new_project );

		/**
		 * 插入封面图
		 */
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$uploadedfile     = $_FILES['icon'];
		$upload_overrides = array(
			'test_form' => false
		);

		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
		if ( $movefile && ! isset( $movefile['error'] ) ) {
			gp_update_meta( $project->id, 'icon', $movefile['url'], 'project' );
		} else {
			$this->errors[] = '封面图上传失败：' . $movefile['error'];
		}

		/**
		 * 创建子项目
		 */
		$new_project2 = array(
			'name'                => '程序主体',
			'slug'                => 'body',
			'description'         => '',
			'source_url_template' => $project->source_url_template,
			'parent_project_id'   => $project->id,
			'active'              => 'on',
		);

		/**
		 * @var GP_Project $project2
		 */
		$project2 = GP::$project->create_and_select( $new_project2 );

		$args = array(
			'name'       => '简体中文',
			'slug'       => 'default',
			'project_id' => $project2->id,
			'locale'     => 'zh-cn'
		);
		GP::$translation_set->create( $args );

		if ( ! $project ) {
			$project        = new GP_Project();
			$this->errors[] = __( 'Error in creating project!', 'glotpress' );
			$this->tmpl( 'project-new', get_defined_vars() );
		} else {
			$this->notices[] = __( 'The project was created!', 'glotpress' );
			$this->redirect( gp_url_project( $project ) );
		}
	}

	public function single( $project_path ) {
		global $wpdb;

		$project = GP::$project->by_path( $project_path );

		if ( ! $project ) {
			$this->die_with_404();
		}

		// $sub_projects     = $project->sub_projects();
		$page    = gp_get( 'page', 1 );
		$filters = gp_get( 'filters', array() );
		$sort    = gp_get( 'sort', array() );
		$s       = gp_get( 's', '' );

		if ( is_array( $sort ) && 'random' === gp_array_get( $sort, 'by' ) ) {
			add_filter( 'gp_pagination', '__return_null' );
		}

		$per_page = (int) get_user_option( 'gp_per_page' );
		if ( 0 === $per_page ) {
			$per_page = GP::$translation->per_page;
		} else {
			GP::$translation->per_page = $per_page;
		}

		if ( ! is_array( $filters ) ) {
			$filters = array();
		}

		if ( ! is_array( $sort ) ) {
			$sort = array();
		}

		$cache_key       = sanitize_title( $_SERVER['REQUEST_URI'] );
		$is_not_finished = $_GET['not_finished'] ?? '';

		$sub_projects = wp_cache_get( $cache_key );
		if ( empty( $sub_projects ) ) {
			if ( 1 === (int) $project->id || 2 === (int) $project->id ) {
				if ( empty( $is_not_finished ) ) {
					$sql = <<<SQL
select id,
       name,
       author,
       gp.slug,
       path,
       description,
       parent_project_id,
       source_url_template,
       active
from wp_4_gp_projects gp
         INNER JOIN (select mid.slug, wc.total_sales
                     from wp_3_wc_product_meta_lookup wc
                              INNER JOIN lp_api_projects mid ON mid.product_id = wc.product_id
                     where 1 = 1
                       and mid.type = %s
                       and mid.slug in (select slug from wp_4_gp_projects)
         			   and mid.name like %s
                     order by wc.total_sales desc, mid.product_id desc
                     limit %d, 15) as mid
where 1 = 1
  and gp.slug = mid.slug
  and gp.parent_project_id = %d
order by mid.total_sales desc
SQL;
				} else {
					$not_finished_project_slugs = wp_cache_get( 'translation_not_finished_project_slugs', 'litepress-cn' );

					if ( empty( $not_finished_project_slugs ) ) {
						$sql                      = <<<SQL
 select parent_project_id
    from wp_4_gp_projects
    where id in (
        select project_id
        from wp_4_gp_originals
        where id not in (
            select original_id from wp_4_gp_translations where status = 'current'
        )
        and status='+active'
        group by project_id
    )
    group by parent_project_id
SQL;
						$r                        = $wpdb->get_results( $sql );
						$not_finished_project_ids = array_map( function ( $item ) {
							return $item->parent_project_id;
						}, $r );

						$sql                        = <<<SQL
 select slug
from wp_4_gp_projects
where id in (%s)
SQL;
						$r                          = $wpdb->get_results( sprintf( $sql, join( ',', $not_finished_project_ids ) ) );
						$not_finished_project_slugs = array_map( function ( $item ) {
							return "'$item->slug'";
						}, $r );

						$not_finished_project_slugs = join( ',', $not_finished_project_slugs );

						wp_cache_set( 'translation_not_finished_project_slugs', $not_finished_project_slugs, 'litepress-cn', 7200 );
					}

					$sql = <<<SQL
select id,
       name,
       author,
       gp.slug,
       path,
       description,
       parent_project_id,
       source_url_template,
       active
from wp_4_gp_projects gp
         INNER JOIN (select mid.slug, wc.total_sales
                     from wp_3_wc_product_meta_lookup wc
                              INNER JOIN lp_api_projects mid ON mid.product_id = wc.product_id
                     where 1 = 1
                       and mid.type = %s
                       and mid.slug in ($not_finished_project_slugs)
         			   and mid.name like %s
                     order by wc.total_sales desc, mid.product_id desc
                     limit %d, 15) as mid
where 1 = 1
  and gp.slug = mid.slug
  and gp.parent_project_id = %d
order by mid.total_sales desc
SQL;
				}

				$sql = $wpdb->prepare( $sql,
					1 === $project->id ? 'plugin' : 'theme',
					'%' . $s . '%',
					( $page - 1 ) * 15,
					$project->id,
				);
			} else {
				$sql = "select * from {$wpdb->prefix}gp_projects where parent_project_id=%d and name like %s limit %d, 15;";
				$sql = $wpdb->prepare( $sql,
					$project->id,
					'%' . $s . '%',
					( $page - 1 ) * 15
				);
			}
			$sub_projects = GP::$project->many( $sql );

			wp_cache_set( $cache_key, $sub_projects, '', 7200 );
		}

		$translation_sets = GP::$translation_set->by_project_id( $project->id );

		$sub_projects_num = $wpdb->get_var( $wpdb->prepare( "select COUNT(*) from {$wpdb->prefix}gp_projects where `parent_project_id`=%d and `name` like %s;", [
			$project->id,
			'%' . $s . '%'
		] ) );

		foreach ( $translation_sets as $set ) {
			$locale = GP_Locales::by_slug( $set->locale );

			$set->name_with_locale   = $set->name_with_locale();
			$set->current_count      = $set->current_count();
			$set->untranslated_count = $set->untranslated_count();
			$set->waiting_count      = $set->waiting_count();
			$set->fuzzy_count        = $set->fuzzy_count();
			$set->percent_translated = $set->percent_translated();
			$set->all_count          = $set->all_count();
			$set->wp_locale          = $locale->wp_locale;
			if ( $this->api ) {
				$set->last_modified = $set->current_count ? $set->last_modified() : false;
			}
		}

		usort(
			$translation_sets,
			function ( $a, $b ) {
				return ( $a->current_count < $b->current_count );
			}
		);

		/**
		 * Filter the list of translation sets of a project.
		 *
		 * Can also be used to sort the sets to a custom order.
		 *
		 * @param GP_Translation_Sets[] $translation_sets An array of translation sets.
		 *
		 * @since 1.0.0
		 *
		 */
		$translation_sets = apply_filters( 'gp_translation_sets_sort', $translation_sets );

		$title     = sprintf(
		/* translators: %s: project name */
			__( '%s project', 'glotpress' ),
			esc_html( $project->name )
		);
		$can_write = $this->can( 'write', 'project', $project->id );
		$this->tmpl( 'project', get_defined_vars() );
	}

	public function import_translations_post_by_api() {
		header( 'Content-Type: application/json' );

		$project_path = sanitize_text_field( gp_post( 'project_path' ) );

		$project = GP::$project->by_path( $project_path );
		if ( ! $project ) {
			// 如果获取不到就看看“第三方托管”中是否包含此项目
			$project_path = str_replace( array( 'plugins', 'themes' ), 'others', $project_path );
			$project      = GP::$project->by_path( $project_path );
		}

		if ( ! $project ) {
			echo json_encode( array( 'error' => '请求的翻译项目不存在' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		if ( ! isset( $_FILES['po_file'] ) || ! is_uploaded_file( $_FILES['po_file']['tmp_name'] ) ) {
			echo json_encode( array( 'error' => '未选择要上传的 po 文件' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		$locale_slug = 'zh-cn';

		$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, 'default', $locale_slug );
		if ( ! $translation_set ) {
			echo json_encode( array( 'error' => '该项目的简体中文翻译集获取失败' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		$can_import_current = $this->can( 'approve', 'translation-set', $translation_set->id );
		$can_import_waiting = $can_import_current || $this->can( 'import-waiting', 'translation-set', $translation_set->id );

		if ( ! $can_import_current && ! $can_import_waiting ) {
			echo json_encode( array( 'error' => '你没有翻译导入的权限' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		$import_status = $can_import_current ? 'current' : 'waiting';

		$format = gp_get_import_file_format( gp_post( 'format', 'po' ), $_FILES['po_file']['name'] );
		if ( ! $format ) {
			echo json_encode( array( 'error' => '接口只允许上传 po 文件' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		$translations = $format->read_translations_from_file( $_FILES['po_file']['tmp_name'], $project );
		if ( ! $translations ) {
			echo json_encode( array( 'error' => '无法从上传的 po 文件中读取到有效的翻译数据' ), JSON_UNESCAPED_SLASHES );
			exit;
		}

		$translations_added = $translation_set->import( $translations, $import_status );

		echo json_encode( array( 'message' => sprintf( '成功导入了 %d 条翻译。', $translations_added ) ), JSON_UNESCAPED_SLASHES );
	}

}
