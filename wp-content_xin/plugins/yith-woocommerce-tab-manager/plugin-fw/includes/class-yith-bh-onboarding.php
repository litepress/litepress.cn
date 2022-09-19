<?php
/**
 * YITH BH Onboarding Class
 *
 * @class   YITH_BH_Onboarding
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_BH_Onboarding' ) ) {
	/**
	 * Main Class
	 */
	class YITH_BH_Onboarding {
		/**
		 * The single instance of the class.
		 *
		 * @var self
		 */
		protected static $instance = null;

		/**
		 * Get class instance.
		 *
		 * @return self
		 */
		public static function get_instance() {
			return ! is_null( static::$instance ) ? static::$instance : static::$instance = new static();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {
			add_action( 'yith_bh_onboarding', array( $this, 'show_onboarding_content' ), 10, 1 );
			add_action( 'wp_ajax_yith_bh_onboarding', array( $this, 'save_options' ) );
		}

		/**
		 * Show onboarding content
		 *
		 * @param string $slug Slug of current plugin modal.
		 */
		public function show_onboarding_content( $slug ) {

			$options = apply_filters( 'yith_bh_onboarding_' . $slug, array() );

			if ( empty( $options ) || ! isset( $options['tabs'], $options['slug'] ) ) {
				return;
			}

			define( 'DOING_YITH_BH_ONBOARDING', true );

			if ( ! wp_script_is( 'yith-plugin-fw-fields', 'registered' ) || ! wp_style_is( 'yith-plugin-fw-fields', 'registered' ) ) {
				YIT_Assets::instance()->register_styles_and_scripts();
			}

			if ( isset( $options['enqueue_script'] ) ) {
				foreach ( $options['enqueue_script'] as $handle ) {
					wp_enqueue_script( $handle );
				}
			}

			if ( isset( $options['enqueue_style'] ) ) {
				foreach ( $options['enqueue_style'] as $handle ) {
					wp_enqueue_style( $handle );
				}
			}

			wp_enqueue_script( 'yith-bh-onboarding' );
			wp_enqueue_style( 'yith-bh-onboarding' );

			include YIT_CORE_PLUGIN_TEMPLATE_PATH . '/bh-onboarding/onboarding-tabs.php';
		}

		/**
		 * Save options
		 *
		 * @return void
		 */
		public function save_options() {
			check_ajax_referer( 'yith-bh-onboarding-save-options' );
			if ( ! isset( $_REQUEST['yith-plugin'], $_REQUEST['tab'] ) ) {
				wp_send_json_error( __( 'It is not possible save the options', 'yith-plugin-fw' ) );
			}

			$slug   = sanitize_text_field( wp_unslash( $_REQUEST['yith-plugin'] ) );
			$posted = $_REQUEST;
			// the options are filtered by each plugin.
			$options = apply_filters( 'yith_bh_onboarding_' . $slug, array() );
			$tab     = $posted['tab'];

			if ( apply_filters( 'yith_bh_onboarding_save_options_' . $slug, isset( $options['tabs'][ $tab ]['options'] ), $posted ) ) {
				foreach ( $options['tabs'][ $tab ]['options'] as $single_option ) {
					if ( isset( $posted[ $single_option['id'] ] ) ) {
						$value = $posted[ $single_option['id'] ] ?? false;
						$value = YIT_Plugin_Panel_WooCommerce::sanitize_option( $value, $single_option, $value );
						$value = apply_filters( 'yith_bh_onboarding_save_option_value', $value, $single_option, $slug );
						update_option( $single_option['id'], $value );
					}
				}
			}

			wp_send_json_success();
		}
	}
}

YITH_BH_Onboarding::get_instance();
