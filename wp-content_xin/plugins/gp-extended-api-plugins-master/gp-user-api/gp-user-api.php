<?php
/**
 *  Expands the GP API by adding User endpoints.
 *  Ultimate goal here being inclusion in the appropriate parts of GP core.
 *
 *  Put this file in the folder: /glotpress/p
 */


class GP_Route_User extends GP_Route_Main {

	function __construct() {
		$this->template_path = dirname( __FILE__ ) . '/templates/';
	}

	function get_current_user_details() {
		if ( ! GP::$user->logged_in() ){
			$this->die_with_error( json_encode( array( 'logged_in' => false, 'message' => 'Please login.' ) ), 401 );
		}
		$gp_user = GP::$user->current();
		$gp_user = $this->resolve_permissions( $gp_user );
		$gp_user = $this->sanitize_user_data( $gp_user );
		$this->tmpl( 'user', get_defined_vars(), true );
	}

	function get_other_user_details( $user_id ) {
		if( ! GP::$user->logged_in() || ! GP::$user->current()->can( 'admin' ) ) {
			$this->cannot_and_redirect( 'read', 'user', $user_id );
			return false;
		}
		$gp_user = GP::$user->get( $user_id );
		$gp_user = $this->resolve_permissions( $gp_user );
		$gp_user = $this->sanitize_user_data( $gp_user );
		$this->tmpl( 'user', get_defined_vars(), true );
	}

	private function resolve_permissions( $gp_user ) {
		if ( $gp_user->can( 'admin') ) {
			$gp_user->admin = true;
			$gp_user->permissions = array( '*' );
		} else {
			$gp_user->admin = false;
			$gp_user->permissions = json_decode( gp_array_of_things_to_json( GP::$permission->find_many( array( 'user_id' => $gp_user->id  ) ) ) );
		}

		return $gp_user;
	}

	private function sanitize_user_data( $gp_user ) {
		$whitelist = array(
				'id',
				'user_login',
				'user_email',
				'user_url',
				'user_registered',
				'admin',
				'display_name',
				'permissions',
			);

		$whitelisted_user_data = new stdClass();

		foreach ( $whitelist as $whitelisted_field ) {
			$whitelisted_user_data->$whitelisted_field = $gp_user->$whitelisted_field;
		}

		return $whitelisted_user_data;
	}
}

class GP_User_API_Loader extends GP_Plugin {
	function __construct() {
		parent::__construct();
		$this->init_new_routes();
	}

	function init_new_routes() {
		GP::$router->add( '/users/me', array( 'GP_Route_User', 'get_current_user_details' ) );
		GP::$router->add( '/users/(\d+)', array( 'GP_Route_User', 'get_other_user_details' ) );
	}
}

GP::$plugins->user_api = new GP_User_API_Loader();
