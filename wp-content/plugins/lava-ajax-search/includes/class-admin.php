<?php

class Lava_Ajax_Search_Admin {

	const GROUPKEY_FORMAT = 'lava_bp_%s_search';

	public $post_type = 'post';


	public function __construct() {

		$this->setVariables();

		$this->register_hooks();

	}

	public function setVariables() {
		$this->optionGroup = sprintf( self::GROUPKEY_FORMAT, $this->post_type );
		$this->options = get_option( $this->getOptionFieldName() );

	}

	public function register_hooks() {
		add_action( 'admin_init', Array( $this, 'register_options' ) );
		//add_filter( "lava_{$this->post_type}_admin_tab"	, Array( $this, 'add_addons_tab' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	public function add_addons_tab( $args ) {
		return wp_parse_args(
			Array(
				'bp_search'		=> Array(
					'label'		=> __( "Ajax Search", 'lvbp-ajax-search' ),
					'group'	=> $this->optionGroup,
					'file'		=> lava_ajaxSearch()->template_path . '/template-admin-index.php',
				)
			), $args
		);
	}

	public function register_menu() {
		add_options_page( 'Lava ajax search settings', 'Lava Ajax Search', 'manage_options', 'lava-ajax-search', array( $this, 'option_page' ) );
	}

	public function option_page() {
		lava_ajaxSearch()->template->load_template( Array( 'file' => 'admin-index' ) );
	}

	public function register_options() {
		register_setting( $this->optionGroup , $this->getOptionFieldName() );
	}

	public function getOptionFieldName( $option_name=false ){    // option field name

		$strFieldName = $this->optionGroup . '_param';

		if( $option_name )
			$strFieldName = sprintf( '%1$s[%2$s]', $strFieldName, $option_name );

		return $strFieldName;
	}

	public function get_settings( $option_key, $default=false ) {
		if( array_key_exists( $option_key, (Array) $this->options ) )
			if( $value = $this->options[ $option_key ] )
				$default = $value;
		return $default;
	}





}