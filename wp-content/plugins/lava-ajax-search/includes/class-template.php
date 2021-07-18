<?php

class Lava_Ajax_Search_Template {

	Const STR_ENQUEUE_FORMAT = 'lava_ajax_search_%s';

	public $isActiveContentsHook = false;

	public function __construct() {
		$this->register_hooks();
	}

	public function register_hooks() {

		add_filter( 'template_include', array( $this, 'override_wp_native_results' ), 999 );
		add_filter( 'the_content', array( $this, 'append_search_page' ), 9 );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_csses' ) );
	}

	public function getEnqueuehandle( $name='' ) {
		return sprintf( self::STR_ENQUEUE_FORMAT, sanitize_title( $name ) );
	}

	public function override_wp_native_results( $template='' ) {
		global $wp_query;
		if( is_search() ) {

			$post = new WP_Post( (object) Array(
				'ID'                    => 0,
               'post_status'           => 'public',
               'post_author'           => 0,
               'post_parent'           => 0,
               'post_type'             => 'page',
               'post_date'             => 0,
               'post_date_gmt'         => 0,
               'post_modified'         => 0,
               'post_modified_gmt'     => 0,
               'post_content'          => '',
               'post_title'            => __('Search Results','lvbp-ajax-search'),
               'post_excerpt'          => '',
               'post_content_filtered' => '',
               'post_mime_type'        => '',
               'post_name'             => '',
               'guid'                  => '',
               'menu_order'            => 0,
               'pinged'                => '',
               'to_ping'               => '',
               'ping_status'           => '',
               'comment_status'        => 'closed',
               'comment_count'         => 0,
               'filter'                => 'raw',
               'is_404'                => false,
               'is_page'               => false,
               'is_single'             => false,
               'is_archive'            => false,
               'is_tax'                => false,
               'is_search'             => true,
			) );
			$wp_query->post = $post;
			$wp_query->posts = Array( $post );
			$wp_query->post_count = 1;
			$wp_query->max_num_pages = 0;

			$load_template = locate_template( array( 'page.php','single.php','index.php' ) );
			if( '' !== $load_template ) {
				$template = $load_template;
			}
		}
		return $template;
	}

	public function append_search_page( $content='' ) {
		if( ! $this->isActiveContentsHook && is_search() && ! is_admin() ) {
			remove_filter( 'the_content', array( $this, 'append_search_page' ), 9 );

			lava_ajaxSearch()->core->prepare_search_page();

			ob_start();
			$this->load_template(
				Array(
					'file' => 'results',
				),
				Array(
					'lvBpSearchResult' => (object) Array(
						'type' => 'page',
					),
				)
			);
			$content .= ob_get_clean();
			$this->isActiveContentsHook = true;
		}
		return $content;
	}

	public function register_scripts() {
		$arrScripts = Array(
			'search-form.js' => array( 'ver' => '1.0.0' ),
			'selectize.min.js' => array( 'ver' => '1.0.0' ),
		);

		if( !empty( $arrScripts ) ) {
			foreach( $arrScripts as $fileName => $fileMeta ) {
				wp_register_script( $this->getEnqueuehandle( $fileName ), lava_ajaxSearch()->assets_url . 'js/' . $fileName, array( 'jquery' ), $fileMeta[ 'ver' ], true );
			}
		}
	}

	public function register_csses() {
		$arrCsses = Array(
			'selectize.css' => array( 'ver' => '1.0.0' ),
			lava_ajaxSearch()->folder . '.css' => array( 'ver' => '1.0.0' ),
		);

		if( !empty( $arrCsses ) ) {
			foreach( $arrCsses as $fileName => $fileMeta ) {
				wp_enqueue_style( $this->getEnqueuehandle( $fileName ), lava_ajaxSearch()->assets_url . 'css/' . $fileName );
			}
		}

	}

	public function load_template( $args=Array(), $params=Array() ) {
		$args = wp_parse_args(
			$args,
			Array(
				'path' => lava_ajaxSearch()->template_path,
				'prefix' => 'template',
				'file' => '',
				'ext' => 'php',
				'once' => true,
			)
		);

		if( is_array( $params ) ) {
			extract( $params );
		}
		$strFileName = sprintf( '%1$s/%2$s-%3$s.%4$s', $args[ 'path' ], $args[ 'prefix' ], $args[ 'file' ], $args[ 'ext' ] );
		if( file_exists( $strFileName ) ) {
			if( $args[ 'once' ] ) {
				require_once $strFileName;
			}else{
				require $strFileName;
			}

			return true;
		}
		return false;
	}
}