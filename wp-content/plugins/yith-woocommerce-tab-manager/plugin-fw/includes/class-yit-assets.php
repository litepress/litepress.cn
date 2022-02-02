<?php
/**
 * YITH Assets Class. Assets Handler.
 *
 * @class      YIT_Assets
 * @package    YITH\PluginFramework\Classes
 * @since      3.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Assets' ) ) {
	/**
	 * YIT_Assets class.
	 *
	 * @author     Leanza Francesco <leanzafrancesco@gmail.com>
	 */
	class YIT_Assets {
		/**
		 * The framework version
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The single instance of the class.
		 *
		 * @var YIT_Assets
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YIT_Assets
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YIT_Assets constructor.
		 */
		private function __construct() {
			$this->version = yith_plugin_fw_get_version();
			add_action( 'admin_enqueue_scripts', array( $this, 'register_common_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_common_scripts' ) );
			add_action( 'elementor/editor/before_enqueue_styles', array( $this, 'register_common_scripts' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_and_scripts' ) );
		}

		/**
		 * Register common scripts
		 */
		public function register_common_scripts() {
			wp_register_style( 'yith-plugin-fw-icon-font', YIT_CORE_PLUGIN_URL . '/assets/css/yith-icon.css', array(), $this->version );
			wp_register_style( 'yith-plugin-fw-elementor', YIT_CORE_PLUGIN_URL . '/assets/css/elementor.css', array(), $this->version );
		}

		/**
		 * Register styles and scripts
		 */
		public function register_styles_and_scripts() {
			global $wp_scripts, $woocommerce, $wp_version, $pagenow;

			$screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$screen_id = $screen && is_a( $screen, 'WP_Screen' ) ? $screen->id : '';
			$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register scripts.
			wp_register_script( 'yith-ui', YIT_CORE_PLUGIN_URL . '/assets/js/yith-ui' . $suffix . '.js', array( 'jquery' ), $this->version, true );
			wp_register_script( 'yith-colorpicker', YIT_CORE_PLUGIN_URL . '/assets/js/yith-colorpicker.min.js', array( 'jquery', 'wp-color-picker' ), '3.0.0', true );
			wp_register_script( 'yith-plugin-fw-fields', YIT_CORE_PLUGIN_URL . '/assets/js/yith-fields' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'yith-colorpicker', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-tiptip', 'yith-ui' ), $this->version, true );
			wp_register_script( 'yith-date-format', YIT_CORE_PLUGIN_URL . '/assets/js/yith-date-format' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker' ), $this->version, true );

			wp_register_script( 'yit-metabox', YIT_CORE_PLUGIN_URL . '/assets/js/metabox' . $suffix . '.js', array( 'jquery', 'wp-color-picker', 'yith-plugin-fw-fields', 'yith-ui' ), $this->version, true );
			wp_register_script( 'yit-plugin-panel', YIT_CORE_PLUGIN_URL . '/assets/js/yit-plugin-panel' . $suffix . '.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'yith-plugin-fw-fields', 'yith-ui' ), $this->version, true );
			wp_register_script( 'colorbox', YIT_CORE_PLUGIN_URL . '/assets/js/jquery.colorbox' . $suffix . '.js', array( 'jquery' ), '1.6.3', true );
			wp_register_script( 'yith_how_to', YIT_CORE_PLUGIN_URL . '/assets/js/how-to' . $suffix . '.js', array( 'jquery' ), $this->version, true );
			wp_register_script( 'yith-plugin-fw-wp-pages', YIT_CORE_PLUGIN_URL . '/assets/js/wp-pages' . $suffix . '.js', array( 'jquery' ), $this->version, false );

			// Register styles.
			wp_register_style( 'yith-plugin-ui', YIT_CORE_PLUGIN_URL . '/assets/css/yith-plugin-ui.css', array( 'yith-plugin-fw-icon-font' ), $this->version );
			wp_register_style( 'yit-plugin-style', YIT_CORE_PLUGIN_URL . '/assets/css/yit-plugin-panel.css', array( 'yith-plugin-ui' ), $this->version );
			wp_register_style( 'jquery-ui-style', YIT_CORE_PLUGIN_URL . '/assets/css/jquery-ui/jquery-ui.min.css', array(), '1.11.4' );
			wp_register_style( 'colorbox', YIT_CORE_PLUGIN_URL . '/assets/css/colorbox.css', array(), $this->version );
			wp_register_style( 'yit-upgrade-to-pro', YIT_CORE_PLUGIN_URL . '/assets/css/yit-upgrade-to-pro.css', array( 'colorbox' ), $this->version );
			wp_register_style( 'yit-plugin-metaboxes', YIT_CORE_PLUGIN_URL . '/assets/css/metaboxes.css', array( 'yith-plugin-ui' ), $this->version );
			wp_register_style( 'yith-plugin-fw-fields', YIT_CORE_PLUGIN_URL . '/assets/css/yith-fields.css', array( 'yith-plugin-ui' ), $this->version );

			wp_register_style( 'raleway-font', '//fonts.googleapis.com/css?family=Raleway:100,200,300,400,500,600,700,800,900', array(), $this->version );

			$wc_version_suffix = '';
			if ( function_exists( 'WC' ) || ! empty( $woocommerce ) ) {
				$woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
				$wc_version_suffix   = version_compare( $woocommerce_version, '3.0.0', '>=' ) ? '' : '-wc-2.6';

				wp_register_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css', array(), $woocommerce_version );
			} else {
				wp_register_script( 'jquery-tiptip', YIT_CORE_PLUGIN_URL . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), '1.3', true );
				wp_register_script( 'select2', YIT_CORE_PLUGIN_URL . '/assets/js/select2/select2.min.js', array( 'jquery' ), '4.0.3', true );
				wp_register_style( 'yith-select2-no-wc', YIT_CORE_PLUGIN_URL . '/assets/css/yith-select2-no-wc.css', false, $this->version );
			}

			wp_register_script( 'yith-enhanced-select', YIT_CORE_PLUGIN_URL . '/assets/js/yith-enhanced-select' . $wc_version_suffix . $suffix . '.js', array( 'jquery', 'select2' ), $this->version, true );
			wp_localize_script(
				'yith-enhanced-select',
				'yith_framework_enhanced_select_params',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'search_posts_nonce'     => wp_create_nonce( 'search-posts' ),
					'search_terms_nonce'     => wp_create_nonce( 'search-terms' ),
					'search_customers_nonce' => wp_create_nonce( 'search-customers' ),
					'search_pages_nonce'     => wp_create_nonce( 'search-pages' ),
				)
			);

			wp_localize_script(
				'yith-plugin-fw-fields',
				'yith_framework_fw_fields',
				array(
					'admin_url' => admin_url( 'admin.php' ),
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
				)
			);

			wp_localize_script(
				'yith-ui',
				'yith_plugin_fw_ui',
				array(
					'i18n' => array(
						'confirm' => _x( 'Confirm', 'Button text', 'yith-plugin-fw' ),
						'cancel'  => _x( 'Cancel', 'Button text', 'yith-plugin-fw' ),
					),
				)
			);

			wp_localize_script(
				'yith-plugin-fw-wp-pages',
				'yith_plugin_fw_wp_pages',
				array(
					'bulk_delete_confirmation_enabled' => ! ! apply_filters( "yith_plugin_fw_{$screen_id}_bulk_delete_confirmation_enabled", in_array( $pagenow, array( 'edit.php', 'edit-tags.php' ), true ) ),
					'i18n'                             => array(
						'bulk_trash_confirm_title'    => __( 'Confirm trash', 'yith-plugin-fw' ),
						'bulk_trash_confirm_message'  => __( 'Are you sure you want to trash the selected items?', 'yith-plugin-fw' ),
						'bulk_trash_confirm_button'   => _x( 'Yes, move to trash', 'Trash confirmation action', 'yith-plugin-fw' ),
						'bulk_trash_cancel_button'    => __( 'No', 'yith-plugin-fw' ),
						'bulk_delete_confirm_title'   => __( 'Confirm delete', 'yith-plugin-fw' ),
						'bulk_delete_confirm_message' => __( 'Are you sure you want to delete the selected items?', 'yith-plugin-fw' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-plugin-fw' ),
						'bulk_delete_confirm_button'  => _x( 'Yes, delete', 'Delete confirmation action', 'yith-plugin-fw' ),
						'bulk_delete_cancel_button'   => __( 'No', 'yith-plugin-fw' ),
					),
				)
			);

			// Localize color-picker to avoid issues with WordPress 5.5.
			if ( version_compare( $wp_version, '5.5-RC', '>=' ) ) {
				wp_localize_script(
					'yith-colorpicker',
					'wpColorPickerL10n',
					array(
						'clear'            => __( 'Clear' ),
						'clearAriaLabel'   => __( 'Clear color' ),
						'defaultString'    => __( 'Default' ),
						'defaultAriaLabel' => __( 'Select default color' ),
						'pick'             => __( 'Select Color' ),
						'defaultLabel'     => __( 'Color value' ),
					)
				);
			}

			wp_enqueue_style( 'yith-plugin-fw-admin', YIT_CORE_PLUGIN_URL . '/assets/css/admin.css', array(), $this->version );
		}
	}
}

YIT_Assets::instance();
