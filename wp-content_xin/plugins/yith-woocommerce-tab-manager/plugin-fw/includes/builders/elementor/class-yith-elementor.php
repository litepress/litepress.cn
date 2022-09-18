<?php
/**
 * Elementor Class.
 *
 * @class   YITH_Elementor
 * @package YITH\PluginFramework\Classes
 * @since   3.6.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Elementor' ) ) {
	/**
	 * YITH_Elementor class.
	 *
	 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
	 */
	class YITH_Elementor {

		/**
		 * The single instance of the class.
		 *
		 * @var YITH_Elementor
		 */
		private static $instance;

		/**
		 * The registered widgets.
		 *
		 * @var array
		 */
		private $widgets = array();

		/**
		 * Singleton implementation.
		 *
		 * @return YITH_Elementor
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YITH_Elementor constructor.
		 */
		private function __construct() {
			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) {
				add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
				add_action( 'elementor/elements/categories_registered', array( $this, 'add_yith_category' ) );

				add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_styles' ) );
				add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_styles' ) );
			}
		}

		/**
		 * Register Elementor widget
		 *
		 * @param string $widget_name    The widget name.
		 * @param array  $widget_options The widget options.
		 */
		public function register_widget( $widget_name, $widget_options ) {
			if ( ! isset( $widget_options['name'] ) ) {
				$widget_options['name'] = $widget_name;
			}
			$this->widgets[ $widget_name ] = $widget_options;
		}

		/**
		 * Let's start with Elementor
		 *
		 * @deprecated 3.7.2
		 */
		public function init() {
		}

		/**
		 * Load files
		 */
		private function load_files() {
			require_once 'class-yith-elementor-widget.php';
		}

		/**
		 * Register Elementor Widgets
		 */
		public function register_widgets() {
			if ( $this->widgets ) {
				$this->load_files();
			}

			foreach ( $this->widgets as $widget ) {
				\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new YITH_Elementor_Widget( array(), array( 'yith_data' => $widget ) ) );
			}
		}

		/**
		 * Add "YITH" category for Elementor widgets
		 *
		 * @param Elementor\Elements_Manager $elements_manager Elements Manager.
		 */
		public function add_yith_category( $elements_manager ) {
			// If the category is empty, it'll be automatically hidden by Elementor.
			$elements_manager->add_category(
				'yith',
				array(
					'title'  => 'YITH',
					'icon'   => 'fa fa-plug',
					'active' => false,
				)
			);
		}

		/**
		 * Enqueue styles in elementor
		 */
		public function enqueue_styles() {
			if ( $this->widgets ) {
				if ( \Elementor\Plugin::$instance->preview->is_preview_mode() || \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					wp_enqueue_style( 'yith-plugin-fw-icon-font' );
					wp_enqueue_style( 'yith-plugin-fw-elementor' );
				}
			}
		}
	}
}

YITH_Elementor::instance();
