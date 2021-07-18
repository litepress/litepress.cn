<?php

class Lava_Ajax_Search_Shortcode {

	Const STR_SHORTCODE_PREFIX = 'lava_ajax_%s';

	public function __construct() {
		$this->shortcodes[ 'search_form' ] = Array( $this, 'search_form' );

		add_action( 'init', array( $this, 'createShortcodes' ), 15 );
		add_action( 'init', array( $this, 'register_vc' ), 20 );
		add_action( 'Javo/Footer/Render', Array( $this, 'search_enqueue_script' ) );
	}

	public function getShortcodeName( $name='' ) {
		return sprintf( self::STR_SHORTCODE_PREFIX, $name );
	}

	public function createShortcodes() {
		$arrShortcodes = apply_filters( 'lava_ajax_search_shortcodes', $this->shortcodes, $this );
		if( empty( $arrShortcodes ) || !is_array( $arrShortcodes ) ) {
			return false;
		}
		foreach( $arrShortcodes as $strShortcode => $fnCallBack ) {
			add_shortcode( $this->getShortcodeName( $strShortcode ), $fnCallBack );
		}
	}

	public function register_vc() {

		if( !function_exists( 'vc_map' ) ) {
			return;
		}

		vc_map( Array(
			'name' => esc_html__( "Lava ajax Search", 'lvbp-ajax-search' ),
			'base' => $this->getShortcodeName( 'search_form' ),
			'category' => 'Lava',
			'params' => array(
				Array(
					'type'			=> 'textfield',
					'heading'		=> __( 'Height', 'lvbp-ajax-search' ),
					'holder'		=> 'div',
					'class'			=> '',
					'param_name'	=> 'height',
					'value'			=> '',
				),
			),
		) );
	}

	public function search_form( $atts=Array(), $content='' ) {
		$options = shortcode_atts(
			Array(
				'height' => false,
				'strip_form' => false,
				'default_value' => null,
				'submit_button' => true,
				'field_name' => 's',
			),
			$atts
		);

		add_action( 'wp_footer', array( $this, 'search_enqueue_script' ) );

		ob_start();
		lava_ajaxSearch()->template->load_template(
			Array(
				'file' => 'search-form',
				'once' => false,
			 ),
			Array(
				'LAS_PARAM' => Array(
					'strip_form' => $options[ 'strip_form' ],
					'submit_button' => $options[ 'submit_button' ],
					'field_name' => $options[ 'field_name' ],
					'default_value' => $options[ 'default_value' ],
					'height' => is_numeric( $options[ 'height' ] ) ? intVal( $options[ 'height' ] ) : false,
				),
			)
		);

		return ob_get_clean();
	}

	public function responseTerms( $taxonomy='' ) {
		$arrResponse = Array();
		/*
		if('yes' != lava_ajaxSearch()->admin->get_settings( 'placeholder_dropdown' )){
			return json_encode($arrResponse);
		} */
		if( !taxonomy_exists( $taxonomy ) ) {
			return json_encode($arrResponse);
		}
		$arrTerms = get_terms( Array( 'taxonomy' => $taxonomy, 'fields' => 'id=>name' ) );
		if( !empty( $arrTerms ) ) {
			foreach( $arrTerms as $term_id => $term_name ) {
				$icon = '';
				if( function_exists( 'lava_directory' ) ) {
					$icon = lava_directory()->admin->getTermOption( $term_id, 'icon', $taxonomy, '' );
				}
				$termInstance = new Lava_Ajax_Search_Terms( $taxonomy );
				ob_start();
				lava_ajaxSearch()->template->load_template(
					Array(
						'file' => 'result-loop',
						'once' => false,
					),
					Array(
						'lvBpSearchResult' => (object) Array(
							'type' => $taxonomy,
							'search_type' => $taxonomy,
							'queried_id' => $term_id,
							'count' => $GLOBALS[ 'wpdb' ]->get_var( $termInstance->sql( $taxonomy, true ) ),
						),
					)
				);
				$html = ob_get_clean();
				$arrResponse[] = (object) Array(
					'type' => $taxonomy,
					'label' => $term_name,
					/*
					'value' => apply_filters(
						'lava_ajax_search_taxonomy_template',
						sprintf( '<span><a href="%s" data-object-id="%s">%s</a></span>', get_term_link( $term_id, $taxonomy ), $term_id, $term_name ),
						$term_id, $taxonomy
					), */
					'value' => $html,
					'icon' => sprintf( '<i class="%s"></i>', $icon ),
					'object_id' => $term_id,
					'type_label' => '',
					'result_type' => 'ajax',

				);
			}
		}

		return json_encode( $arrResponse );
	}

	public function search_enqueue_script() {
		wp_localize_script(
			lava_ajaxSearch()->template->getEnqueuehandle( 'search-form.js' ),
			'lava_ajax_search_args',
			Array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'listing_category' => $this->responseTerms( 'listing_category' ),
				'show_category' => lava_ajaxSearch()->admin->get_settings( 'show_categories' ),
				'min_search_length' => max(0, intVal(lava_ajaxSearch()->admin->get_settings( 'min_search_length' ))),
			)
		);
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( lava_ajaxSearch()->template->getEnqueuehandle( 'search-form.js' ) );
	}

}