<?php
/**
 * YITH Pointers Class.
 *
 * @class   YIT_Pointers
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Pointers' ) ) {
	/**
	 * YIT_Pointers class.
	 * Initializes the pointers.
	 */
	class YIT_Pointers {

		/**
		 * The single instance of the class.
		 *
		 * @var YIT_Pointers
		 */
		private static $instance;

		/**
		 * The screen IDs.
		 *
		 * @var array
		 */
		public $screen_ids = array();

		/**
		 * The pointers.
		 *
		 * @var array
		 */
		public $pointers = array();

		/**
		 * Special Screen Ids that require a particular action
		 *
		 * @var array|mixed|void
		 */
		public $special_screen = array();

		/**
		 * Default pointers.
		 *
		 * @var array|mixed
		 */
		protected $default_pointer = array();

		/**
		 * The default position
		 *
		 * @var string[]
		 */
		protected $default_position = array(
			'edge'  => 'left',
			'align' => 'center',
		);

		/**
		 * Singleton implementation.
		 *
		 * @return YIT_Pointers
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Construct
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since  1.0
		 */
		private function __construct() {

			$title            = __( 'Plugins Activated', 'yith-plugin-fw' );
			$message          = __( 'From now on, you can find all plugin options in YITH menu. Plugin customization settings will be available as a new entry in YITH menu.', 'yith-plugin-fw' );
			$discover_message = sprintf(
			// translators: 1. YITH site link; 2. WordPress site link.
				__( 'Discover all our plugins available on: %1$s and %2$s', 'yith-plugin-fw' ),
				'<a href="https://yithemes.com/product-category/plugins/" target="_blank">yithemes.com</a>',
				'<a href="https://profiles.wordpress.org/yithemes/" target="_blank">Wordpress.org</a>'
			);

			$this->default_pointer['plugins'] = array(
				'screen_id' => 'plugins',
				'options'   => array(
					'content' => "<h3>{$title}</h3><p>{$message}</p><p>{$discover_message}</p>",
				),
			);

			$title            = __( 'Plugins Upgraded', 'yith-plugin-fw' );
			$message          = __( 'From now on, you can find the option panel of YITH plugins in YITH menu. Every time one of our plugins is added, a new entry will be added to this menu. For example, after the update, plugin options (such as for YITH WooCommerce Wishlist, YITH WooCommerce Ajax Search, etc.) will be moved from previous location to YITH menu.', 'yith-plugin-fw' );
			$discover_message = sprintf(
			// translators: 1. YITH site link; 2. WordPress site link.
				__( 'Discover all our plugins available on: %1$s and %2$s', 'yith-plugin-fw' ),
				'<a href="https://yithemes.com/product-category/plugins/" target="_blank">yithemes.com</a>',
				'<a href="https://profiles.wordpress.org/yithemes/" target="_blank">Wordpress.org</a>'
			);

			$this->default_pointer['update'] = array(
				'screen_id' => 'update',
				'options'   => array(
					'content' => "<h3>{$title}</h3><p>{$message}</p><p>{$discover_message}</p>",
				),
			);

			$this->default_pointer = $this->parse_args( $this->default_pointer );

			// DEPRECATED 'yit-pointer-special-screen' filter since 3.5 | use yith_plugin_fw_pointers_special_screens instead.
			$this->special_screen = apply_filters( 'yit-pointer-special-screen', array( 'plugins', 'update' ) ); //phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			$this->special_screen = apply_filters( 'yith_plugin_fw_pointers_special_screens', array( 'plugins', 'update' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'pointer_load' ) );
			add_action( 'admin_init', array( $this, 'add_pointers' ), 100 );
		}

		/**
		 * Parse args for the default pointer.
		 *
		 * @param array $args The arguments to be parse.
		 *
		 * @return array
		 */
		public function parse_args( $args ) {
			$default = array(
				'pointer_id' => 'yith_default_pointer',
				'target'     => '#toplevel_page_yit_plugin_panel',
				'init'       => null,
			);

			foreach ( $args as $id => $pointer ) {
				$args[ $id ]                        = wp_parse_args( $pointer, $default );
				$args[ $id ]['options']['position'] = $this->default_position;
			}

			return $args;
		}

		/**
		 * Add pointers
		 */
		public function add_pointers() {
			if ( ! empty( $this->screen_ids ) ) {
				foreach ( $this->screen_ids as $screen_id ) {
					add_filter( "yit_pointers-{$screen_id}", array( $this, 'pointers' ) );
				}
			}
		}

		/**
		 * Register pointers.
		 *
		 * @param array $pointers The pointers.
		 */
		public function register( $pointers ) {
			foreach ( $pointers as $id => $pointer ) {
				$pointer_id = isset( $pointer['pointer_id'] ) ? $pointer['pointer_id'] : false;
				$target     = isset( $pointer['target'] ) ? $pointer['target'] : false;
				$content    = isset( $pointer['content'] ) ? $pointer['content'] : false;
				$position   = isset( $pointer['position'] ) ? $pointer['position'] : false;
				$screen_id  = isset( $pointer['screen_id'] ) ? $pointer['screen_id'] : false;
				$init       = isset( $pointer['init'] ) ? $pointer['init'] : false;

				if ( ! $pointer_id || ! $target || ! $content || ! $position || ! $screen_id ) {
					continue;
				}

				if ( ! in_array( $screen_id, $this->screen_ids, true ) ) {
					$this->screen_ids[] = $screen_id;
				}

				$this->pointers[ $screen_id ][ $pointer_id ] = array(
					'target'  => $target,
					'options' => array(
						'content'  => $content,
						'position' => $position,
					),
					'init'    => $init,
				);
			}
		}

		/**
		 * Retrieve the registered pointers array where the keys will be the plugin init(s).
		 *
		 * @param string $screen_id The screen ID.
		 *
		 * @return array
		 */
		public function get_plugins_init( $screen_id ) {

			$registered = array();

			foreach ( $this->pointers[ $screen_id ] as $pointer_id => $pointer ) {
				$registered[ $pointer['init'] ] = $pointer_id;
			}

			return $registered;
		}

		/**
		 * Load the pointer.
		 *
		 * @param bool $deprecated Deprecated param.
		 */
		public function pointer_load( $deprecated = false ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			// phpcs:disable WordPress.Security.NonceVerification.Missing

			// Retrieve the pointers for the current screen.
			$screen   = get_current_screen();
			$pointers = apply_filters( "yit_pointers-{$screen->id}", array() ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

			if ( ! $pointers || ! is_array( $pointers ) ) {
				return;
			}

			// Get dismissed pointers.
			$dismissed      = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
			$valid_pointers = array();

			// Show pointers only on plugin activate action.
			if ( in_array( $screen->id, $this->special_screen, true ) ) {
				$show              = false;
				$registered        = $this->get_plugins_init( $screen->id );
				$recently_activate = get_option( 'yit_recently_activated', array() );

				// For "plugins" screen.
				$is_single_activate = isset( $_GET['activate'] ) && 'true' === $_GET['activate'];
				$is_multi_activate  = isset( $_GET['activate-multi'] ) && 'true' === $_GET['activate-multi'];

				/**
				 * For "update" screen
				 * Single plugin update use GET method
				 * Multi update plugins with bulk action send two post args called "action" and "action2"
				 * action refer to first bulk action button (at the top of plugins table)
				 * action2 refer to last bulk action button (at the bottom of plugins table)
				 */
				$is_single_upgrade = isset( $_GET['action'] ) && 'upgrade-plugin' === $_GET['action'];
				$is_multi_upgrade  = ( isset( $_POST['action'] ) && 'update-selected' === $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'update-selected' === $_POST['action2'] );

				if ( $is_single_activate || $is_single_upgrade ) {
					foreach ( $registered as $init => $p_id ) {
						if ( in_array( $init, $recently_activate, true ) ) {
							$point_id = $p_id;
							$pointer  = $pointers[ $point_id ];

							if ( ! ( in_array( $point_id, $dismissed, true ) || empty( $pointer ) || empty( $point_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) ) {
								$pointer['pointer_id']        = $point_id;
								$valid_pointers['pointers'][] = $pointer;
								$show                         = true;
							}
							break;
						}
					}
				} elseif ( $is_multi_activate || $is_multi_upgrade ) {
					$point_id  = array();
					$screen_id = $screen->id;

					if ( $is_multi_upgrade && isset( $_POST['checked'] ) && ( count( $_POST['checked'] ) > 0 ) ) {
						$recently_activate = sanitize_file_name( wp_unslash( $_POST['checked'] ) );
						$screen_id         = 'update';
						$pointers          = apply_filters( "yit_pointers-{$screen_id}", array() ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
					}

					foreach ( $registered as $init => $p_id ) {
						if ( in_array( $init, $recently_activate, true ) ) {
							$point_id[] = $p_id;
						}
					}

					/**
					 * Bulk Action: Activate Plugins
					 * count( $point_id ) is the number of YITH plugins that have registered specific pointers
					 * case 0   -> No pointers -> Exit
					 * case 1   -> Only one pointers to show -> Use the specific plugin pointer
					 * default  -> Two or more plugins need to show a pointer -> use a generic pointers
					 */
					switch ( count( $point_id ) ) {
						case 0:
							$show = false;
							break;

						case 1:
							$point_id = array_pop( $point_id );
							$pointer  = $pointers[ $point_id ];
							if ( ! ( in_array( $point_id, $dismissed, true ) || empty( $pointer ) || empty( $point_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) ) {
								$pointer['pointer_id']        = $point_id;
								$valid_pointers['pointers'][] = $pointer;
								$show                         = true;
							}
							break;

						default:
							$valid_pointers['pointers'][] = $this->default_pointer[ $screen_id ];
							$show                         = true;
							break;
					}
				}

				update_option( 'yit_recently_activated', array() );

				if ( ! $show ) {
					return;
				}
			} else {
				// Check pointers and remove dismissed ones.
				foreach ( $pointers as $pointer_id => $pointer ) {

					if ( in_array( $pointer_id, $dismissed, true ) || empty( $pointer ) || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
						continue;
					}

					$pointer['pointer_id'] = $pointer_id;

					$valid_pointers['pointers'][] = $pointer;
				}
			}

			if ( empty( $valid_pointers ) ) {
				return;
			}

			$script_file = function_exists( 'yit_load_js_file' ) ? yit_load_js_file( 'yit-wp-pointer.js' ) : 'yit-wp-pointer.min.js';

			// Enqueue pointer scripts and styles.
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );

			wp_enqueue_script( 'yit-wp-pointer', YIT_CORE_PLUGIN_URL . '/assets/js/' . $script_file, array( 'wp-pointer' ), yith_plugin_fw_get_version(), true );
			wp_localize_script( 'yit-wp-pointer', 'custom_pointer', $valid_pointers );

			// phpcs:enable
		}

		/**
		 * Filter pointers.
		 *
		 * @param array $pointers The pointers.
		 *
		 * @return array
		 */
		public function pointers( $pointers ) {
			$screen_id       = str_replace( 'yit_pointers-', '', current_filter() );
			$pointers_to_add = $this->get_pointers( $screen_id );

			return ! empty( $pointers_to_add ) ? array_merge( $pointers, $pointers_to_add ) : $pointers;
		}

		/**
		 * Retrieve pointers for the specified screen ID.
		 *
		 * @param string $screen_id The Screen ID.
		 *
		 * @return array|mixed
		 */
		public function get_pointers( $screen_id ) {
			return isset( $this->pointers[ $screen_id ] ) ? $this->pointers[ $screen_id ] : array();
		}
	}
}

if ( ! function_exists( 'YIT_Pointers' ) ) {

	/**
	 * Single instance of YIT_Pointers
	 *
	 * @return YIT_Pointers
	 */
	function YIT_Pointers() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return YIT_Pointers::instance();
	}
}

YIT_Pointers();
