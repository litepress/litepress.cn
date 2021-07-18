<?php

class Helper_User_Info extends GP_Translation_Helper {

	public $priority = 0;
	public $title = 'User stats';
	public $has_async_content = true;

	public $translation = false;


	function set_data( $args ) {
		parent::set_data( $args );
		if ( ! isset( $this->data['translation'] ) && isset( $this->data['translation_id'] ) ) {
			$this->data['translation'] = GP::$translation->get( $this->data['translation_id'] );
		}
	}

	function get_async_content() {
		if ( $this->data['translation']->user_id ) {
			return GP::$translation->find_many_no_map( array( 'user_id' => $this->data['translation']->user_id ) );
		}

		return false;
	}

	function async_output_callback( $translations ) {

		$user = get_userdata( $this->data['translation']->user_id );
		$output = "<b>User</b>: {$user->display_name} ({$user->user_login})<br/>";

		$total = count( $translations );
		$translations_by_status = array();
		foreach ( $translations as $translation ) {
			if ( isset( $translations_by_status[ $translation->status ] ) ) {
				$translations_by_status[ $translation->status ]++;
			} else {
				$translations_by_status[ $translation->status ] = 1;
			}
		}

		$output .= sprintf( '<b>Stats</b>: %d total translations. %d%% accepted, %d%% rejected, %d%% waiting', $total , number_format( $translations_by_status['current'] * 100 / $total ), number_format( $translations_by_status['rejected'] * 100 / $total ), number_format( $translations_by_status['waiting'] * 100 / $total ) );

		return $output;
	}

	function activate() {
		return $this->data['translation'] && $this->data['translation']->user_id;
	}
}
