<?php

namespace LitePress\GlotPress\Customizations\Inc;

use GP;
use GP_Project;

/**
 * Locale Route Class.
 *
 * Provides the route for translate.wordpress.org/locale/$locale.
 */
class Locale {

	/**
	 * Retrieves contributors of a project.
	 *
	 * @param GP_Project $project A GlotPress project.
	 * @param string $locale_slug Slug of the locale.
	 * @param string $set_slug Slug of the translation set.
	 *
	 * @return array Contributors.
	 */
	public function get_locale_contributors( $project, $locale_slug, $set_slug ) {
		global $wpdb;

		$locale_contributors = [
			'editors'      => [
				'project'   => [],
				'inherited' => [],
			],
			'contributors' => [],
		];

		// Get the contributors of the project.
		$contributors = array();

		// In case the project has a translation set, like /wp-themes/twentysixteen.
		$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $set_slug, $locale_slug );
		if ( $translation_set ) {
			$contributors = array_merge(
				$contributors,
				$this->get_locale_contributors_by_translation_set( $translation_set )
			);
		}

		// Check if the project has sub-projects, like /wp-plugins/wordpress-importer.
		$sub_projects = $wpdb->get_col( $wpdb->prepare( "
			SELECT id
			FROM {$wpdb->gp_projects}
			WHERE
				parent_project_id = %d
				AND active = 1
		", $project->id ) );

		foreach ( $sub_projects as $sub_project ) {
			$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $sub_project, $set_slug, $locale_slug );
			if ( ! $translation_set ) {
				continue;
			}

			$contributors = array_merge(
				$contributors,
				$this->get_locale_contributors_by_translation_set( $translation_set )
			);
		}

		$projects = [];

		// Get the names of the contributors.
		foreach ( $contributors as $contributor ) {
			if ( isset( $locale_contributors['contributors'][ $contributor->user_id ] ) ) {
				// Update last updated and counts per status.
				$locale_contributors['contributors'][ $contributor->user_id ]->last_update = max(
					$locale_contributors['contributors'][ $contributor->user_id ]->last_update,
					$contributor->last_update
				);

				$locale_contributors['contributors'][ $contributor->user_id ]->total_count   += $contributor->total_count;
				$locale_contributors['contributors'][ $contributor->user_id ]->current_count += $contributor->current_count;
				$locale_contributors['contributors'][ $contributor->user_id ]->waiting_count += $contributor->waiting_count;
				$locale_contributors['contributors'][ $contributor->user_id ]->fuzzy_count   += $contributor->fuzzy_count;

				if ( ! isset( $projects[ $contributor->project_id ] ) ) {
					$projects[ $contributor->project_id ] = GP::$project->get( $contributor->project_id );
				}

				$locale_contributors['contributors'][ $contributor->user_id ]->detailed[ $contributor->project_id ] = (object) [
					'total_count'   => $contributor->total_count,
					'current_count' => $contributor->current_count,
					'waiting_count' => $contributor->waiting_count,
					'fuzzy_count'   => $contributor->fuzzy_count,
					'project'       => $projects[ $contributor->project_id ],
				];

				continue;
			}

			$user = get_user_by( 'id', $contributor->user_id );
			if ( ! $user ) {
				continue;
			}

			if ( ! isset( $projects[ $contributor->project_id ] ) ) {
				$projects[ $contributor->project_id ] = GP::$project->get( $contributor->project_id );
			}

			$locale_contributors['contributors'][ $contributor->user_id ] = (object) array(
				'login'         => $user->user_login,
				'nicename'      => $user->user_nicename,
				'display_name'  => $this->_encode( $user->display_name ),
				'email'         => $user->user_email,
				'last_update'   => $contributor->last_update,
				'total_count'   => $contributor->total_count,
				'current_count' => $contributor->current_count,
				'waiting_count' => $contributor->waiting_count,
				'fuzzy_count'   => $contributor->fuzzy_count,
				'detailed'      => [
					$contributor->project_id => (object) [
						'total_count'   => $contributor->total_count,
						'current_count' => $contributor->current_count,
						'waiting_count' => $contributor->waiting_count,
						'fuzzy_count'   => $contributor->fuzzy_count,
						'project'       => $projects[ $contributor->project_id ],
					],
				],
			);
		}
		unset( $contributors, $editor_ids );

		uasort( $locale_contributors['contributors'], function ( $a, $b ) {
			return $a->total_count < $b->total_count;
		} );

		return $locale_contributors;
	}

	private function get_locale_contributors_by_translation_set( $translation_set ) {
		global $wpdb;

		$contributors = $wpdb->get_results( $wpdb->prepare( "
			SELECT
				`t`.`user_id` as `user_id`,
				`o`.`project_id` as `project_id`,
				MAX( `t`.`date_added` ) AS `last_update`,
				COUNT( * ) as `total_count`,
				COUNT( CASE WHEN `t`.`status` = 'current' THEN `t`.`status` END ) AS `current_count`,
				COUNT( CASE WHEN `t`.`status` = 'waiting' THEN `t`.`status` END ) AS `waiting_count`,
				COUNT( CASE WHEN `t`.`status` = 'fuzzy' THEN `t`.`status` END ) AS `fuzzy_count`
			FROM `{$wpdb->gp_translations}` as `t`
			JOIN `{$wpdb->gp_originals}` as `o`
				ON `t`.`original_id` = `o`.`id` AND `o`.`status` = '+active'
			WHERE
				`t`.`translation_set_id` = %d
				AND `t`.`user_id` IS NOT NULL AND `t`.`user_id` != 0
				AND `t`.`status` IN( 'current', 'waiting', 'fuzzy' )
			GROUP BY `t`.`user_id`
		", $translation_set->id ) );

		return $contributors;
	}

	private function _encode( $raw ): string {
		$raw = mb_convert_encoding( $raw, 'UTF-8', 'ASCII, JIS, UTF-8, Windows-1252, ISO-8859-1' );
		return ent2ncr( htmlspecialchars_decode( htmlentities( $raw, ENT_NOQUOTES, 'UTF-8' ), ENT_NOQUOTES ) );
	}
}
