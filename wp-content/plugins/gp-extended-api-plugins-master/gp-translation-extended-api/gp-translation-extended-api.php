<?php
/**
 *  Expands the GP API by adding extended Translation endpoints.
 *  Ultimate goal here being inclusion in the appropriate parts of GP core.
 *
 *  Put this file in the folder: /glotpress/plugins/
 */


class GP_Route_Translation_Extended extends GP_Route_Main {

	function __construct() {
		$this->template_path = dirname( __FILE__ ) . '/templates/';
	}

	function translations_get_by_originals() {
		if ( ! $this->api ) {
			$this->die_with_error( __( "Yer not 'spose ta be here." ), 403 );
		}

		$project_path          = gp_post( 'project' );
		$locale_slug           = gp_post( 'locale_slug' );
		$translation_set_slug  = gp_post( 'translation_set_slug', 'default' );
		$original_strings      = json_decode( gp_post( 'original_strings', array() ) );

		if ( ! $project_path || ! $locale_slug || ! $translation_set_slug || ! $original_strings ) {
			$this->die_with_404();
		}

		$project = GP::$project->by_path( $project_path );
		$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $translation_set_slug, $locale_slug );

		if ( ! $project || ! $translation_set ) {
			$this->die_with_404();
		}

		foreach ( $original_strings as $original ) {
			$original_record = GP::$original->by_project_id_and_entry( $project->id, $original );
			if ( ! $original_record ) {
				$translations['originals_not_found'][] = $original;
				continue;
			}
			$query_result                    = new stdClass();
			$query_result->original_id       = $original_record->id;
			$query_result->original          = $original;
			$query_result->original_comment  = $original_record->comment;

			$query_result->translations  = GP::$translation->find_many( "original_id = '{$query_result->original_id}' AND translation_set_id = '{$translation_set->id}' AND ( status = 'waiting' OR status = 'fuzzy' OR status = 'current' )" );

			foreach ( $query_result->translations as $key => $current_translation ) {
				$query_result->translations[$key] = GP::$translation->prepare_fields_for_save( $current_translation );
				$query_result->translations[$key]['translation_id'] = $current_translation->id;
			}

			$translations[] = $query_result;
		}
		$this->tmpl( 'translations-extended', get_defined_vars(), true );
	}

	function save_translation() {
		if ( ! $this->api ) {
			$this->die_with_error( __( "Yer not 'spose ta be here." ), 403 );
		}

		$this->logged_in_or_forbidden();

		$project_path          = gp_post( 'project' );
		$locale_slug           = gp_post( 'locale_slug' );
		$translation_set_slug  = gp_post( 'translation_set_slug', 'default' );

		if ( ! $project_path || ! $locale_slug || ! $translation_set_slug ) {
			$this->die_with_404();
		}

		$project = GP::$project->by_path( $project_path );
		$locale = GP_Locales::by_slug( $locale_slug );
		if ( ! $project || ! $locale ) {
			$this->die_with_404();
		}

		$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $translation_set_slug, $locale_slug );
		if ( ! $translation_set ) {
			$this->die_with_404();
		}

		$output = array();
		foreach( gp_post( 'translation', array() ) as $original_id => $translations ) {
			$data = compact('original_id');
			$data['user_id'] = get_current_user_id();
			$data['translation_set_id'] = $translation_set->id;

			foreach( range( 0, GP::$translation->get_static( 'number_of_plural_translations' ) ) as $i ) {
				if ( isset( $translations[$i] ) ) $data["translation_$i"] = $translations[$i];
			}

			$original = GP::$original->get( $original_id );
			$data['warnings'] = GP::$translation_warnings->check( $original->singular, $original->plural, $translations, $locale );

			if ( empty( $data['warnings'] ) && ( $this->can( 'approve', 'translation-set', $translation_set->id ) || $this->can( 'write', 'project', $project->id ) ) ) {
				$data['status'] = 'current';
			} else {
				$data['status'] = 'waiting';
			}

			$existing_translations = GP::$translation->for_translation( $project, $translation_set, 'no-limit', array('original_id' => $original_id, 'status' => 'current_or_waiting' ), array() );
			foreach( $existing_translations as $e ) {
				if ( array_pad( $translations, $locale->nplurals, null ) == $e->translations ) {
					return $this->die_with_error( __( 'Identical current or waiting translation already exists.' ), 409 );
				}
			}

			$translation = GP::$translation->create( $data );
			if ( ! $translation->validate() ) {
				$error_output = $translation->errors;
				$translation->delete();
				$this->die_with_error( $error_output, 422 );
			}

			if ( 'current' == $data['status'] ) {
				$translation->set_status( 'current' );
			}

			gp_clean_translation_set_cache( $translation_set->id );
			$translations = GP::$translation->for_translation( $project, $translation_set, 'no-limit', array( 'translation_id' => $translation->id ), array() );

			if ( ! $translations ) {
				$output[$original_id] = false;
			}

			$output[$original_id] = $translations[0];
		}

		$translations = $output;
		$this->tmpl( 'translations-extended', get_defined_vars(), true );
	}

	function set_status( $translation_id ) {
		if ( ! $this->api ) {
			$this->die_with_error( __( "Yer not 'spose ta be here." ), 403 );
		}

		$translation = GP::$translation->get( $translation_id );
		if ( ! $translation ) {
			$this->die_with_error( 'Translation doesn&#8217;t exist!' );
		}

		$this->can_approve_translation_or_forbidden( $translation );

		$result = $translation->set_status( gp_post( 'status' ) );
		if ( ! $result ) {
			$this->die_with_error( 'Error in saving the translation status!' );
		}

		$translations = $this->translation_record_by_id( $translation_id );
		if ( ! $translations ) {
			$this->die_with_error( 'Error in retrieving translation record!' );
		}

		$this->tmpl( 'translations-extended', get_defined_vars() );
	}

	private function can_approve_translation_or_forbidden( $translation ) {
		$can_reject_self = ( GP::$user->current()->id == $translation->user_id && $translation->status == "waiting" );
		if ( $can_reject_self ) {
			return;
		}
		$this->can_or_forbidden( 'approve', 'translation-set', $translation->translation_set_id );
	}

	private function translation_record_by_id( $translation_id ) {
		global $gpdb;
		return $gpdb->get_results( $gpdb->prepare( "SELECT * FROM $gpdb->translations WHERE id = %d", $translation_id ) );
	}
}

add_action( 'gp_init', 'gp_translation_exended_api_init' );

function gp_translation_exended_api_init() {
	
	GP::$router->add( '/translations/-new', array( 'GP_Route_Translation_Extended', 'save_translation' ), 'post' );
	GP::$router->add( '/translations/(\d+)/-set-status', array( 'GP_Route_Translation_Extended', 'set_status' ), 'post' );
	GP::$router->add( '/translations/-query-by-originals', array( 'GP_Route_Translation_Extended', 'translations_get_by_originals' ), 'post' );
}
