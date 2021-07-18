<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_WPML' ) ) {

	class YITH_Tab_Manager_WPML {

		protected static $instance;

		public function __construct() {

			add_filter( 'yith_tab_manager_current_product_tabs', array(
				$this,
				'yith_tab_manager_normalize_tabs_wpml'
			), 30 );
		}

		/**
		 * @param array $tabs
		 *
		 * @return array
		 */
		public function yith_tab_manager_normalize_tabs_wpml( $tabs ) {

			$lang = apply_filters( 'wpml_default_language', false );

			if ( ! empty( $lang ) ) {

				foreach ( $tabs as & $tab ) {
					$tab->ID = apply_filters( 'wpml_object_id', $tab->ID, 'ywtm_tab', true, $lang );
				}
			}

			return $tabs;
		}

		/**
		 * @author YITHEMES
		 * @since 1.0.0
		 * @return YITH_Tab_Manager_WPML
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}

return YITH_Tab_Manager_WPML::get_instance();