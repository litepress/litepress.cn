<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class manage the frontend features
 *
 * @author YITH
 * @package YITH WooCommerce Tab Manager\Classes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_WCTM_Frontend' ) ) {
	/**
	 * The class for the frontend
	 */
	class YITH_WCTM_Frontend {

		/**
		 * The unique access of the class
		 *
		 * @var YITH_WCTM_Frontend
		 */
		protected static $instance;

		/**
		 * The construct of the class
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs_woocommerce' ), 20 );
		}

		/**
		 * Get the instance of the class
		 *
		 * @author YITH
		 * @since 1.0.0
		 * @return YITH_WCTM_Frontend
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Add_global_tabs_woocommerce
		 *
		 * @author YITH
		 * @since 1.0.0
		 * @param array $tabs the plugin tabs.
		 * @return array
		 * @use woocommerce_product_tabs filter
		 */
		public function add_tabs_woocommerce( $tabs ) {

			$yith_tabs = YITH_WCTM_Post_Type()->get_tabs();
			$priority  = apply_filters( 'ywctm_priority_tab', 30 );

			foreach ( $yith_tabs as $tab ) {

				$tabs[ $tab['id'] ] = array(
					'title'    => $tab['title'],
					'priority' => $tab['priority'] + $priority,
					'callback' => array( $this, 'put_content_tabs' ),
				);

			}

			return $tabs;
		}

		/**
		 * Put the content at the tabs
		 *
		 * @param string $key The tab key.
		 * @param array  $tab The tab object.
		 * @author YITH
		 * @since 1.0.0
		 */
		public function put_content_tabs( $key, $tab ) {

			$args['content'] = get_post_meta( $key, '_ywtm_text_tab', true );

			wc_get_template( 'default.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
		}
	}
}

/**
 * Return the unique instance of th class.
 *
 * @author YITH
 * @return YITH_WCTM_Frontend|YITH_WCTM_Frontend_Premium
 */
function YITH_Tab_Manager_Frontend() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	if ( defined( 'YWTM_PREMIUM' ) && class_exists( 'YITH_WCTM_Frontend_Premium' ) ) {
		return YITH_WCTM_Frontend_Premium::get_instance();
	} else {
		return YITH_WCTM_Frontend::get_instance();
	}
}
