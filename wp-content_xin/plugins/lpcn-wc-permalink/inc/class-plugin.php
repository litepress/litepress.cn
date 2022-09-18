<?php

namespace LitePress\WC_Permalink;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		//add_filter( 'post_link', array( $this, 'custom_post_permalinks' ), 99, 2 );
		add_filter( 'post_type_link', array( $this, 'custom_post_permalinks' ), 99, 2 );
		add_filter( 'request', array( $this, 'custom_request' ), 99, 3 );
		add_filter( 'woocommerce_product_get_slug', array( $this, 'custom_slug' ) );
	}

	public function custom_slug( $slug ) {
		return preg_replace( array( '/plugin-/', '/theme-/' ), '', $slug, 1 );
	}

	public function custom_request( $query, $request_url = false, $return_object = false ) {
		$url = $_SERVER['REQUEST_URI'];

		$pos = strpos( $url, '?' );
		if ( $pos ) {
			$url = substr( $url, 0, $pos - strlen( $url ) );
		}

		/**
		 * 产品标签请求
		 */
		$tag = explode( '/product-tag/', $url )[1] ?? '';
		if ( ! empty( $tag ) ) {
			return array( 'product_tag' => $tag );
		}

		/**
		 * Ajax请求
		 */
		$ajax_route = explode( 'store/wp-json', $url )[1] ?? '';
		if ( ! empty( $ajax_route ) ) {
			return array( 'rest_route' => $ajax_route );
		}

		/**
		 * 针对顶级目录
		 */
		$cat = match ( $url ) {
			'/store/plugins' => array( 'product_cat' => 'plugins' ),
			'/store/themes' => array( 'product_cat' => 'themes' ),
			default => false,
		};
		if ( $cat ) {
			return $cat;
		}

		/**
		 * 前方规则没匹配上的，统一加上 plugin、theme 前缀，然后返回
		 */
		if ( isset( $query['product_cat'] ) && isset( $query['product'] ) && isset( $query['name'] ) ) {
			if ( 'plugins' === $query['product_cat'] ) {
				$query['product'] = "plugin-{$query['product']}";
				$query['name']    = "plugin-{$query['name']}";
			} elseif ( 'themes' === $query['product_cat'] ) {
				$query['product'] = "theme-{$query['product']}";
				$query['name']    = "theme-{$query['name']}";
			}
		}

		return $query;
	}

	public function custom_post_permalinks( $permalink, $post ): string {
		$permalink = str_replace( '/store/product/', '/', $permalink );

		$tmp_list = explode( '/', $permalink );

		$full_slug = end( $tmp_list );
		$slug      = preg_replace( array( '/plugin-/', '/theme-/' ), '', $full_slug, 1 );

		$permalink = '';
		for ( $i = 0; $i < count( $tmp_list ) - 1; $i ++ ) {
			$permalink .= "/{$tmp_list[ $i ]}";
		}
		$permalink = substr( $permalink, 1, strlen( $permalink ) );
		$permalink .= "/$slug";

		return $permalink;
	}

}
