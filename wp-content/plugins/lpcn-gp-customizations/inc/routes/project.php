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

		$format = gp_get_import_file_format( gp_post( 'format', 'po' ), $_FILES['import-file']['name'] );

		if ( ! $format ) {
			$this->redirect_with_error( __( 'No such format.', 'glotpress' ) );

			return;
		}

		$translations = $format->read_originals_from_file( $_FILES['import-file']['tmp_name'] );

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

		$cache_key = sanitize_title( $_SERVER['REQUEST_URI'] );

		$sub_projects = wp_cache_get( $cache_key );
		if ( empty( $sub_projects ) ) {
			if ( 1 === (int) $project->id || 2 === (int) $project->id ) {
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

}
