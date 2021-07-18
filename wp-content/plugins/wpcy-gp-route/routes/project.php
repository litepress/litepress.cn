<?php

class WPCY_Route_Project extends GP_Route_Project {

	public function single( $project_path ) {
		global $wpdb;

		$project = GP::$project->by_path( $project_path );

		if ( ! $project ) {
			return $this->die_with_404();
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

			wp_cache_set( $cache_key, $sub_projects, '', 86400 );
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
