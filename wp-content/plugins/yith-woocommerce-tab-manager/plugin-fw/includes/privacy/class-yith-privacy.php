<?php
/**
 * YITH Privacy Class
 * handle privacy for GDPR
 *
 * @class   YITH_Privacy
 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Privacy' ) ) {
	/**
	 * Class YITH_Privacy
	 */
	class YITH_Privacy {

		/**
		 * The single instance of the class.
		 *
		 * @var YITH_Privacy
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YITH_Privacy
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Deprecated singleton implementation.
		 * Kept for backward compatibility.
		 *
		 * @return YITH_Privacy
		 * @deprecated 3.5 | use YITH_Privacy::get_instance() instead.
		 */
		public static function get_instance() {
			return self::instance();
		}

		/**
		 * YITH_Privacy constructor.
		 */
		private function __construct() {
			add_action( 'admin_init', array( $this, 'add_privacy_message' ) );
		}

		/**
		 * Adds the privacy message on YITH privacy page.
		 */
		public function add_privacy_message() {
			if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
				$content = $this->get_privacy_message();

				if ( $content ) {
					$title = apply_filters( 'yith_plugin_fw_privacy_policy_guide_title', _x( 'YITH Plugins', 'Privacy Policy Guide Title', 'yith-plugin-fw' ) );
					wp_add_privacy_policy_content( $title, $content );
				}
			}
		}

		/**
		 * Get the privacy message.
		 *
		 * @return string
		 */
		public function get_privacy_message() {
			$privacy_content_path = YIT_CORE_PLUGIN_TEMPLATE_PATH . '/privacy/html-policy-content.php';
			ob_start();
			$sections = $this->get_sections();
			if ( file_exists( $privacy_content_path ) ) {
				include $privacy_content_path;
			}

			return apply_filters( 'yith_plugin_fw_privacy_policy_content', ob_get_clean() );
		}

		/**
		 * Get the sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			return apply_filters(
				'yith_plugin_fw_privacy_policy_content_sections',
				array(
					'general'           => array(
						'tutorial'    => _x( 'This sample language includes the basics around what personal data your store may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store will vary. We recommend consulting with a lawyer when deciding what information to disclose on your privacy policy.', 'Privacy Policy Content', 'yith-plugin-fw' ),
						'description' => '',
					),
					'collect_and_store' => array(
						'title' => _x( 'What we collect and store', 'Privacy Policy Content', 'yith-plugin-fw' ),
					),
					'has_access'        => array(
						'title' => _x( 'Who on our team has access', 'Privacy Policy Content', 'yith-plugin-fw' ),
					),
					'share'             => array(
						'title' => _x( 'What we share with others', 'Privacy Policy Content', 'yith-plugin-fw' ),
					),
					'payments'          => array(
						'title' => _x( 'Payments', 'Privacy Policy Content', 'yith-plugin-fw' ),
					),
				)
			);
		}
	}
}

YITH_Privacy::instance();
