<?php

namespace LitePress\WC_I18n;

use LitePress\I18n\i18n;
use function LitePress\Helper\get_product_type_by_categories;
use function LitePress\Helper\get_product_type_by_category_ids;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;
	private string $type = '';

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
		add_filter( 'the_content', array( $this, 'the_content' ), 1, 3 );
		add_filter( 'the_title', array( $this, 'title' ), 9999 );
		add_filter( 'woocommerce_product_title', array( $this, 'title' ), 9999 );
		add_filter( 'woocommerce_product_get_short_description', array( $this, 'short_description' ), 9999 );
	}

	/**
	 * 对应用的标题及短描述翻译
	 */
	public function title( $value ): string {
		global $product;

		if ( ! is_object( $product ) || ! method_exists( $product, 'get_name' ) ) {
			return $value;
		}

		if ( empty( $product ) || $value !== $product->get_name() ) {
			return $value;
		}

		$type = get_product_type_by_category_ids( $product->get_category_ids() );
		switch ( $type ) {
			case 'plugin':
				$gp_project_path = sprintf( 'plugins/%s/readme', $product->get_slug() );
				break;
			case 'theme':
				$gp_project_path = sprintf( 'themes/%1$s/%1$s', $product->get_slug() );
				break;
			default:
				return $value;
		}

		$cache_key = sprintf( '%s_%s_title', $type, $product->get_slug() );

		return i18n::get_instance()->translate( $cache_key, $value, $gp_project_path, true );
	}

	public function short_description( $value ): string {
		global $product;

		if ( ! is_object( $product ) || ! method_exists( $product, 'get_slug' ) ) {
			return $value;
		}

		if ( empty( $product ) ) {
			return $value;
		}

		$type = get_product_type_by_category_ids( $product->get_category_ids() );
		switch ( $type ) {
			case 'plugin':
				$gp_project_path = sprintf( 'plugins/%s/readme', $product->get_slug() );
				break;
			case 'theme':
				$gp_project_path = sprintf( 'themes/%1$s/%1$s', $product->get_slug() );
				break;
			default:
				return $value;
		}

		$cache_key = sprintf( '%s_%s_short_description', $type, $product->get_slug() );

		return i18n::get_instance()->translate( $cache_key, $value, $gp_project_path );
	}

	/**
	 * 对应用详情进行翻译
	 */
	public function the_content( $content, $product_id = 0, $key = 'content' ): string {
		if ( 0 === (int) $product_id ) {
			return $content;
		}

		$categories = get_the_terms( $product_id, 'product_cat' );
		$type       = get_product_type_by_categories( $categories );
		$product    = get_post( $product_id );

		switch ( $type ) {
			case 'plugin':
				$gp_project_path = sprintf( 'plugins/%s/readme', $product->post_name );
				break;
			case 'theme':
				$gp_project_path = sprintf( 'themes/%1$s/%1$s', $product->post_name );
				break;
			default:
				return $content;
		}

		$key = match ( (int) $key ) {
			51 => 'content',
			47 => 'log',
			365 => 'install',
			default => 'un',
		};

		$cache_key = sprintf( '%s_%s_' . $key, $type, $product->post_name );

		return i18n::get_instance()->translate( $cache_key, $content, $gp_project_path );
	}

}
