<?php
/**
 * YITH Plugin Panel Class.
 *
 * @class   YIT_Plugin_Panel
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Plugin_Panel' ) ) {
	/**
	 * Class YIT_Plugin_Panel
	 */
	class YIT_Plugin_Panel {
		/**
		 * Version of the class.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * List of settings parameters.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * Tab Path Files.
		 *
		 * @var array
		 */
		protected $tabs_path_files;

		/**
		 * Main array of options.
		 *
		 * @var array
		 */
		protected $main_array_options;

		/**
		 * Tabs hierarchy.
		 *
		 * @var array
		 */
		protected $tabs_hierarchy;

		/**
		 * Tabs in WP Pages.
		 *
		 * @var array
		 */
		protected static $panel_tabs_in_wp_pages = array();

		/**
		 * Array of links.
		 *
		 * @var array
		 */
		public $links;

		/**
		 * Are the actions initialized?
		 *
		 * @var bool
		 */
		protected static $actions_initialized = false;

		/**
		 * YIT_Plugin_Panel constructor.
		 *
		 * @param array $args The panel arguments.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function __construct( $args = array() ) {
			if ( ! empty( $args ) ) {
				$default_args = array(
					'parent_slug' => 'edit.php?',
					'page_title'  => __( 'Plugin Settings', 'yith-plugin-fw' ),
					'menu_title'  => __( 'Settings', 'yith-plugin-fw' ),
					'capability'  => 'manage_options',
					'icon_url'    => '',
					'position'    => null,
				);

				$args = apply_filters( 'yit_plugin_fw_panel_option_args', wp_parse_args( $args, $default_args ) );
				if ( isset( $args['parent_page'] ) && 'yit_plugin_panel' === $args['parent_page'] ) {
					$args['parent_page'] = 'yith_plugin_panel';
				}

				$this->settings        = $args;
				$this->tabs_path_files = $this->get_tabs_path_files();

				if ( isset( $this->settings['create_menu_page'] ) && $this->settings['create_menu_page'] ) {
					$this->add_menu_page();
				}

				if ( ! empty( $this->settings['links'] ) ) {
					$this->links = $this->settings['links'];
				}

				$this->maybe_init_help_tab();
				$this->maybe_init_premium_tab();
				$this->maybe_init_welcome_modals();

				add_action( 'admin_init', array( $this, 'register_settings' ) );
				add_action( 'admin_menu', array( $this, 'add_setting_page' ), 20 );
				add_action( 'admin_menu', array( $this, 'add_premium_version_upgrade_to_menu' ), 100 );
				add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
				add_action( 'admin_init', array( $this, 'add_fields' ) );

				add_action( 'admin_enqueue_scripts', array( $this, 'init_wp_with_tabs' ), 11 );
				add_action( 'admin_init', array( $this, 'maybe_redirect_to_proper_wp_page' ) );

				/* Add UTM tracking code on premium tab */
				add_filter( 'yith_plugin_fw_premium_landing_uri', array( $this, 'add_utm_data_on_premium_tab' ), 10, 2 );
				// Init actions once to prevent multiple initialization.
				static::init_actions();
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			add_action( 'yith_plugin_fw_before_yith_panel', array( $this, 'add_plugin_banner' ), 10, 1 );
			add_action( 'wp_ajax_yith_plugin_fw_save_toggle_element', array( $this, 'save_toggle_element_options' ) );
		}

		/**
		 * Is this a custom post type page?
		 *
		 * @return bool
		 * @see      YIT_Plugin_Panel::init_wp_with_tabs
		 * @since    3.4.17
		 */
		protected function is_custom_post_type_page() {
			global $pagenow, $post_type;
			$excluded_post_types = array( 'product', 'page', 'post' );

			return in_array( $pagenow, array( 'post.php', 'post-new.php', 'edit.php' ), true ) && ! in_array( $post_type, $excluded_post_types, true );
		}

		/**
		 * Is this a custom taxonomy page?
		 *
		 * @return bool
		 * @see      YIT_Plugin_Panel::init_wp_with_tabs
		 * @since    3.4.17
		 */
		protected function is_custom_taxonomy_page() {
			global $pagenow, $taxonomy;
			$excluded_taxonomies = array( 'category', 'post_tag', 'product_cat', 'product_tag' );

			return in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) && ! in_array( $taxonomy, $excluded_taxonomies, true );
		}

		/**
		 * Init actions to show YITH Panel tabs in WP Pages
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.4.0
		 */
		public function init_wp_with_tabs() {
			if ( ! current_user_can( $this->settings['capability'] ) ) {
				return;
			}

			global $post_type, $taxonomy;
			$tabs = false;

			if ( $this->is_custom_post_type_page() ) {
				$tabs = $this->get_post_type_tabs( $post_type );
			} elseif ( $this->is_custom_taxonomy_page() ) {
				$tabs = $this->get_taxonomy_tabs( $taxonomy );
			}

			$screen          = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$is_block_editor = ! ! $screen && is_callable( array( $screen, 'is_block_editor' ) ) && $screen->is_block_editor();

			if ( $tabs ) {
				$current_tab_args = array(
					'page'            => $this->settings['page'],
					'current_tab'     => isset( $tabs['tab'] ) ? $tabs['tab'] : '',
					'current_sub_tab' => isset( $tabs['sub_tab'] ) ? $tabs['sub_tab'] : '',
					'options'         => isset( $tabs['options'] ) ? $tabs['options'] : array(),
				);

				if ( ! $is_block_editor ) {
					wp_enqueue_style( 'yit-plugin-style' );
					wp_enqueue_style( 'yith-plugin-fw-fields' );
					wp_enqueue_script( 'yith-plugin-fw-wp-pages' );
				}

				if ( ! self::$panel_tabs_in_wp_pages ) {
					self::$panel_tabs_in_wp_pages = $current_tab_args;
					if ( ! $is_block_editor ) {
						add_action( 'all_admin_notices', array( $this, 'print_panel_tabs_in_wp_pages' ) );
						add_action( 'admin_footer', array( $this, 'print_panel_tabs_in_wp_pages_end' ) );
					}
					add_filter( 'parent_file', array( $this, 'set_parent_file_to_handle_menu_for_wp_pages' ) );
					add_filter( 'submenu_file', array( $this, 'set_submenu_file_to_handle_menu_for_wp_pages' ), 10, 2 );
				}
			}
		}

		/**
		 * Init actions.
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since  3.0.0
		 */
		protected static function init_actions() {
			if ( ! static::$actions_initialized ) {
				add_filter( 'admin_body_class', array( __CLASS__, 'add_body_class' ) );

				// Sort plugins by name in YITH Plugins menu.
				add_action( 'admin_menu', array( __CLASS__, 'sort_plugins' ), 90 );
				add_filter( 'add_menu_classes', array( __CLASS__, 'add_menu_class_in_yith_plugin' ) );

				static::$actions_initialized = true;
			}
		}

		/**
		 * Maybe init vars
		 */
		protected function maybe_init_vars() {
			if ( ! isset( $this->main_array_options ) && ! isset( $this->tabs_hierarchy ) ) {
				$options_path             = $this->settings['options-path'];
				$this->main_array_options = array();
				$this->tabs_hierarchy     = array();

				foreach ( $this->settings['admin-tabs'] as $item => $v ) {
					$path = trailingslashit( $options_path ) . $item . '-options.php';
					$path = apply_filters( 'yith_plugin_panel_item_options_path', $path, $options_path, $item, $this );
					if ( file_exists( $path ) ) {
						$_tab                     = include $path;
						$this->main_array_options = array_merge( $this->main_array_options, $_tab );
						$sub_tabs                 = $this->get_sub_tabs( $_tab );
						$current_tab_key          = array_keys( $_tab )[0];

						$this->tabs_hierarchy[ $current_tab_key ] = array_merge(
							array(
								'parent'       => '',
								'has_sub_tabs' => ! ! $sub_tabs,
							),
							$this->get_tab_info_by_options( $_tab[ $current_tab_key ] )
						);

						foreach ( $sub_tabs as $sub_item => $sub_options ) {
							if ( strpos( $sub_item, $item . '-' ) === 0 ) {
								$sub_item = substr( $sub_item, strlen( $item ) + 1 );
							}
							$sub_tab_path = $sub_options['options_path'] ?? ( $options_path . '/' . $item . '/' . $sub_item . '-options.php' );
							$sub_tab_path = apply_filters( 'yith_plugin_panel_sub_tab_item_options_path', $sub_tab_path, $sub_tabs, $sub_item, $this );

							if ( file_exists( $sub_tab_path ) ) {
								$_sub_tab                 = include $sub_tab_path;
								$this->main_array_options = array_merge( $this->main_array_options, $_sub_tab );

								$current_sub_tab_key                          = array_keys( $_sub_tab )[0];
								$this->tabs_hierarchy[ $current_sub_tab_key ] = array_merge( array( 'parent' => $current_tab_key ), $this->get_tab_info_by_options( $_sub_tab[ $current_sub_tab_key ] ) );
							}
						}
					}
				}
			}
		}

		/**
		 * Add yith-plugin-fw-panel in body classes in Panel pages
		 *
		 * @param string $admin_body_classes Body classes.
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return string
		 * @since  3.0.0
		 */
		public static function add_body_class( $admin_body_classes ) {
			global $pagenow;
			if ( ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, 'yith-plugins_page' ) !== false ) ) {
				$admin_body_classes = ! substr_count( $admin_body_classes, ' yith-plugin-fw-panel ' ) ? $admin_body_classes . ' yith-plugin-fw-panel ' : $admin_body_classes;
			}

			return $admin_body_classes;
		}

		/**
		 * Add Menu page link
		 *
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function add_menu_page() {
			global $admin_page_hooks;

			if ( ! isset( $admin_page_hooks['yith_plugin_panel'] ) ) {
				$position   = apply_filters( 'yit_plugins_menu_item_position', '62.32' );
				$capability = apply_filters( 'yit_plugin_panel_menu_page_capability', 'manage_options' );
				$show       = apply_filters( 'yit_plugin_panel_menu_page_show', true );

				// YITH text must NOT be translated.
				if ( ! ! $show ) {
					add_menu_page( 'yith_plugin_panel', 'YITH', $capability, 'yith_plugin_panel', null, yith_plugin_fw_get_default_logo(), $position );
					// Prevent issues for backward compatibility.
					$admin_page_hooks['yith_plugin_panel'] = 'yith-plugins'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
			}
		}

		/**
		 * Remove duplicate submenu
		 * Submenu page hack: Remove the duplicate YIT Plugin link on subpages
		 *
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function remove_duplicate_submenu_page() {
			remove_submenu_page( 'yith_plugin_panel', 'yith_plugin_panel' );
		}

		/**
		 * Enqueue script and styles in admin side
		 * Add style and scripts to administrator
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 */
		public function admin_enqueue_scripts() {
			global $pagenow;

			if ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, $this->settings['page'] ) !== false || apply_filters( 'yit_plugin_panel_asset_loading', false ) ) {
				wp_enqueue_media();

				wp_enqueue_style( 'yith-plugin-fw-fields' );
				wp_enqueue_style( 'jquery-ui-style' );

				wp_enqueue_script( 'jquery-ui' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'yith_how_to' );
				wp_enqueue_script( 'yith-plugin-fw-fields' );
			}

			if ( ( 'admin.php' === $pagenow && yith_plugin_fw_is_panel() ) || apply_filters( 'yit_plugin_panel_asset_loading', false ) ) {
				wp_enqueue_media();
				wp_enqueue_style( 'yit-plugin-style' );
				wp_enqueue_script( 'yit-plugin-panel' );
			}

			if ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, 'yith_upgrade_premium_version' ) !== false ) {
				wp_enqueue_style( 'yit-upgrade-to-pro' );
				wp_enqueue_script( 'colorbox' );
			}
		}

		/**
		 * Register Settings
		 * Generate wp-admin settings pages by registering your settings and using a few callbacks to control the output
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function register_settings() {
			register_setting( 'yit_' . $this->settings['parent'] . '_options', 'yit_' . $this->settings['parent'] . '_options', array( $this, 'options_validate' ) );
		}

		/**
		 * Add Setting SubPage
		 * add Setting SubPage to WordPress administrator
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function add_setting_page() {
			$this->settings['icon_url'] = isset( $this->settings['icon_url'] ) ? $this->settings['icon_url'] : '';
			$this->settings['position'] = isset( $this->settings['position'] ) ? $this->settings['position'] : null;
			$parent                     = $this->settings['parent_slug'] . $this->settings['parent_page'];

			if ( ! empty( $parent ) ) {
				add_submenu_page( $parent, $this->settings['page_title'], $this->settings['menu_title'], $this->settings['capability'], $this->settings['page'], array( $this, 'yit_panel' ) );
			} else {
				add_menu_page( $this->settings['page_title'], $this->settings['menu_title'], $this->settings['capability'], $this->settings['page'], array( $this, 'yit_panel' ), $this->settings['icon_url'], $this->settings['position'] );
			}
			// Duplicate Items Hack.
			$this->remove_duplicate_submenu_page();
			do_action( 'yit_after_add_settings_page' );

		}

		/**
		 * Options Validate
		 * a callback function called by Register Settings function
		 *
		 * @param array $field The field to validate.
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return array validated fields
		 */
		public function options_validate( $field ) {
			$option_key       = ! empty( $field['option_key'] ) ? $field['option_key'] : 'general';
			$yit_options      = $this->get_main_array_options();
			$validated_fields = $this->get_options();

			foreach ( $yit_options[ $option_key ] as $section => $data ) {
				foreach ( $data as $option ) {
					if ( isset( $option['sanitize_call'] ) && isset( $option['id'] ) ) {
						if ( is_array( $option['sanitize_call'] ) ) {
							foreach ( $option['sanitize_call'] as $callback ) {
								if ( is_array( $field[ $option['id'] ] ) ) {
									$validated_fields[ $option['id'] ] = array_map( $callback, $field[ $option['id'] ] );
								} else {
									$validated_fields[ $option['id'] ] = call_user_func( $callback, $field[ $option['id'] ] );
								}
							}
						} else {
							if ( is_array( $field[ $option['id'] ] ) ) {
								$validated_fields[ $option['id'] ] = array_map( $option['sanitize_call'], $field[ $option['id'] ] );
							} else {
								$validated_fields[ $option['id'] ] = call_user_func( $option['sanitize_call'], $field[ $option['id'] ] );
							}
						}
					} else {
						if ( isset( $option['id'] ) ) {
							$value = isset( $field[ $option['id'] ] ) ? $field[ $option['id'] ] : false;
							if ( isset( $option['type'] ) && in_array( $option['type'], array( 'checkbox', 'onoff' ), true ) ) {
								$value = yith_plugin_fw_is_true( $value ) ? 'yes' : 'no';
							}

							if ( ! empty( $option['yith-sanitize-callback'] ) && is_callable( $option['yith-sanitize-callback'] ) ) {
								$value = call_user_func( $option['yith-sanitize-callback'], $value );
							}

							$validated_fields[ $option['id'] ] = $value;
						}
					}
				}
			}

			return $validated_fields;
		}

		/**
		 * Add Premium Version upgrade menu item
		 *
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since    2.9.13
		 */
		public function add_premium_version_upgrade_to_menu() {
			// Add the How To menu item only if the customer haven't a premium version enabled.
			if ( function_exists( 'YIT_Plugin_Licence' ) && ! ! YIT_Plugin_Licence()->get_products() ) {
				return;
			}

			global $submenu;
			if ( apply_filters( 'yit_show_upgrade_to_premium_version', isset( $submenu['yith_plugin_panel'] ) ) ) {
				$how_to_menu                            = array(
					sprintf( '%s%s%s', '<span id="yith-how-to-premium">', __( 'How to install premium version', 'yith-plugin-fw' ), '</span>' ),
					'install_plugins',
					'//support.yithemes.com/hc/en-us/articles/217840988',
					__( 'How to install premium version', 'yith-plugin-fw' ),
				);
				$submenu['yith_plugin_panel']['how_to'] = $how_to_menu; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}

		/**
		 * Print the tabs navigation
		 *
		 * @param array $args Nav Arguments.
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.4.0
		 */
		public function print_tabs_nav( $args = array() ) {
			$defaults = array(
				'current_tab'   => $this->get_current_tab(),
				'premium_class' => isset( $this->settings['class'] ) ? 'yith-premium' : 'premium',
				'page'          => $this->settings['page'],
				'parent_page'   => $this->settings['parent_page'],
				'wrapper_class' => 'nav-tab-wrapper',
			);
			$args     = wp_parse_args( $args, $defaults );

			list ( $wrapper_class ) = yith_plugin_fw_extract( $args, 'wrapper_class' );

			$tabs = '<ul class="yith-plugin-fw-tabs">';

			foreach ( $this->settings['admin-tabs'] as $tab => $tab_value ) {
				$tabs .= $this->get_tab_nav( $tab, $tab_value, $args );
			}

			$tabs .= '</ul>';
			?>
			<h2 class="<?php echo esc_attr( $wrapper_class ); ?>">
				<?php echo wp_kses_post( $tabs ); ?>
			</h2>
			<?php
			$this->print_sub_tabs_nav( $args );
		}

		/**
		 * Get HTML for single tab in tabs navigation
		 *
		 * @param string $tab_slug Tab slug.
		 * @param string $tab_name Tab name.
		 * @param array  $args     Nav Arguments.
		 *
		 * @author   Antonio La Rocca <antonio.larocca@yithemes.com>
		 * @return string Tab HTML
		 *
		 * @since    3.4.0
		 */
		protected function get_tab_nav( $tab_slug, $tab_name, $args = array() ) {
			list ( $current_tab, $premium_class, $page, $parent_page ) = yith_plugin_fw_extract( $args, 'current_tab', 'premium_class', 'page', 'parent_page' );

			$active_class = $current_tab === $tab_slug ? ' nav-tab-active' : '';

			if ( 'premium' === $tab_slug ) {
				$active_class .= ' ' . $premium_class;
			}
			$active_class = apply_filters( 'yith_plugin_fw_panel_active_tab_class', $active_class, $current_tab, $tab_slug );

			$first_sub_tab = $this->get_first_sub_tab_key( $tab_slug );
			$sub_tab       = ! ! $first_sub_tab ? $first_sub_tab : '';
			$sub_tabs      = $this->get_sub_tabs( $tab_slug );
			$url           = $this->get_nav_url( $page, $tab_slug, $sub_tab, $parent_page );
			$icon          = ( $current_tab !== $tab_slug && $sub_tabs ) ? '<i class="yith-icon yith-icon-arrow_down"></i>' : '';

			$tab = '<li class="yith-plugin-fw-tab-element">';

			$tab .= '<a class="nav-tab' . esc_attr( $active_class ) . '" href="' . esc_url( $url ) . '">' . wp_kses_post( $tab_name ) . $icon . '</a>';

			if ( $current_tab !== $tab_slug && $sub_tabs ) {
				$tab .= '<div class="nav-subtab-wrap"><ul class="nav-subtab">';
				foreach ( $sub_tabs as $_key => $_tab ) {
					$url = $this->get_nav_url( $page, $tab_slug, $_key );

					$tab .= '<li class="nav-subtab-item"><a href="' . esc_url( $url ) . '">' . wp_kses_post( $_tab['title'] ) . '</a></li>';
				}
				$tab .= '</ul></div>';
			}
			$tab .= '</li>';

			return $tab;
		}

		/**
		 * Retrieve the Nav URL.
		 *
		 * @param string $page        The page.
		 * @param string $tab         The tab.
		 * @param string $sub_tab     The sub-tab.
		 * @param string $parent_page The parent page.
		 *
		 * @return string
		 */
		public function get_nav_url( $page, $tab, $sub_tab = '', $parent_page = '' ) {
			$tab_hierarchy = $this->get_tabs_hierarchy();
			$key           = ! ! $sub_tab ? $sub_tab : $tab;

			if ( isset( $tab_hierarchy[ $key ], $tab_hierarchy[ $key ]['type'], $tab_hierarchy[ $key ]['post_type'] ) && 'post_type' === $tab_hierarchy[ $key ]['type'] ) {
				$url = admin_url( "edit.php?post_type={$tab_hierarchy[$key]['post_type']}" );
			} elseif ( isset( $tab_hierarchy[ $key ], $tab_hierarchy[ $key ]['type'], $tab_hierarchy[ $key ]['taxonomy'] ) && 'taxonomy' === $tab_hierarchy[ $key ]['type'] ) {
				$url = admin_url( "edit-tags.php?taxonomy={$tab_hierarchy[$key]['taxonomy']}" );
			} else {
				$url = ! ! $parent_page ? "?{$parent_page}&" : '?';

				$url .= "page={$page}&tab={$tab}";
				$url .= ! ! $sub_tab ? "&sub_tab={$sub_tab}" : '';

				$url = admin_url( "admin.php{$url}" );
			}

			return apply_filters( 'yith_plugin_fw_panel_url', $url, $page, $tab, $sub_tab, $parent_page );
		}

		/**
		 * Print the Sub-tabs navigation if the current tab has sub-tabs
		 *
		 * @param array $args Sub-tab arguments.
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.4.0
		 */
		public function print_sub_tabs_nav( $args = array() ) {
			$defaults = array(
				'current_tab'     => $this->get_current_tab(),
				'page'            => $this->settings['page'],
				'current_sub_tab' => $this->get_current_sub_tab(),
			);
			$args     = wp_parse_args( $args, $defaults );

			/**
			 * The arguments.
			 *
			 * @var string $current_tab     The current tab.
			 * @var string $page            The page.
			 * @var string $current_sub_tab The current sub-tab.
			 */
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			$sub_tabs = $this->get_sub_tabs( $current_tab );

			if ( $sub_tabs && $current_sub_tab ) {
				include YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/sub-tabs-nav.php';
			}
		}

		/**
		 * Show a tabbed panel to setting page
		 * a callback function called by add_setting_page => add_submenu_page
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function yit_panel() {
			$yit_options = $this->get_main_array_options();
			$wrap_class  = isset( $this->settings['class'] ) ? $this->settings['class'] : '';

			$option_key         = $this->get_current_option_key();
			$custom_tab_options = $this->get_custom_tab_options( $yit_options, $option_key );
			?>
			<div class="wrap <?php echo esc_attr( $wrap_class ); ?>">
				<div id="icon-themes" class="icon32"><br/></div>
				<?php
				do_action( 'yith_plugin_fw_before_yith_panel', $this->settings['page'] );

				$this->print_tabs_nav();
				if ( $this->is_premium_tab() && $this->has_premium_tab() ) {
					$this->print_premium_tab();

					return;
				} elseif ( $custom_tab_options ) {
					$this->print_custom_tab( $custom_tab_options );

					return;
				} elseif ( $this->is_help_tab() ) {
					$this->print_help_tab();

					return;
				}

				$form_method         = apply_filters( 'yit_admin_panel_form_method', 'POST', $option_key );
				$panel_content_class = apply_filters( 'yit_admin_panel_content_class', 'yit-admin-panel-content-wrap', $option_key );
				?>
				<div id="wrap" class="yith-plugin-fw plugin-option yit-admin-panel-container">
					<?php $this->message(); ?>
					<div class="<?php echo esc_attr( $panel_content_class ); ?>">
						<h2><?php echo wp_kses_post( $this->get_tab_title() ); ?></h2>
						<?php if ( $this->is_show_form() ) : ?>
							<form id="yith-plugin-fw-panel" method="<?php echo esc_attr( $form_method ); ?>" action="options.php">
								<?php do_settings_sections( 'yit' ); ?>
								<p>&nbsp;</p>
								<?php settings_fields( 'yit_' . $this->settings['parent'] . '_options' ); ?>
								<input type="hidden" name="<?php echo esc_attr( $this->get_name_field( 'option_key' ) ); ?>"
										value="<?php echo esc_attr( $option_key ); ?>"/>
								<input type="submit" class="button-primary"
										value="<?php esc_attr_e( 'Save Changes', 'yith-plugin-fw' ); ?>"
										style="float:left;margin-right:10px;"/>
								<input type="hidden" name="page" value="<?php echo esc_attr( $this->settings['page'] ); ?>"/>
								<input type="hidden" name="tab" value="<?php echo esc_attr( $this->get_current_tab() ); ?>"/>
								<input type="hidden" name="sub_tab" value="<?php echo esc_attr( $this->get_current_sub_tab() ); ?>"/>
							</form>
							<form method="post">
								<?php
								$reset_warning = __( 'If you continue with this action, you will reset all options in this page.', 'yith-plugin-fw' ) . '\n' . __( 'Are you sure?', 'yith-plugin-fw' );
								?>
								<input type="hidden" name="yit-action" value="reset"/>
								<input type="submit" name="yit-reset" class="button-secondary"
										value="<?php esc_attr_e( 'Reset to default', 'yith-plugin-fw' ); ?>"
										onclick="return confirm('<?php echo esc_attr( $reset_warning ); ?>');"/>
							</form>
							<p>&nbsp;</p>
						<?php endif ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Check if is a custom tab.
		 *
		 * @param array  $options    The tab options.
		 * @param string $option_key The option key.
		 *
		 * @return string|false The action to be fired of false if it's not a custom tab.
		 */
		public function is_custom_tab( $options, $option_key ) {
			$option = $this->get_custom_tab_options( $options, $option_key );

			return ! ! $option && isset( $option['action'] ) ? $option['action'] : false;
		}

		/**
		 * Retrieve the custom tab options.
		 *
		 * @param array  $options    The tab options.
		 * @param string $option_key The option key.
		 *
		 * @return array|false The options of the custom tab; false if it's not a custom tab.
		 */
		public function get_custom_tab_options( $options, $option_key ) {
			$option = ! empty( $options[ $option_key ] ) ? current( $options[ $option_key ] ) : false;

			if ( $option && isset( $option['type'], $option['action'] ) && 'custom_tab' === $option['type'] && ! empty( $option['action'] ) ) {
				return $option;
			} else {
				return false;
			}
		}

		/**
		 * Retrieve the tab type by its options.
		 *
		 * @param array $tab_options The tab options.
		 *
		 * @return string
		 */
		public function get_tab_type_by_options( $tab_options ) {
			$first         = ! ! $tab_options && is_array( $tab_options ) ? current( $tab_options ) : array();
			$type          = isset( $first['type'] ) ? $first['type'] : 'options';
			$special_types = array( 'post_type', 'taxonomy', 'custom_tab', 'multi_tab' );

			return in_array( $type, $special_types, true ) ? $type : 'options';
		}

		/**
		 * Retrieve the tab info by its options.
		 *
		 * @param array $tab_options The tab options.
		 *
		 * @return string[]
		 */
		public function get_tab_info_by_options( $tab_options ) {
			$type  = $this->get_tab_type_by_options( $tab_options );
			$first = ! ! $tab_options && is_array( $tab_options ) ? current( $tab_options ) : array();
			$info  = $first;

			$info['type'] = $type;
			if ( 'post_type' === $type ) {
				$info['post_type'] = isset( $first['post_type'] ) ? $first['post_type'] : '';
			} elseif ( 'taxonomy' === $type ) {
				$info['taxonomy'] = isset( $first['taxonomy'] ) ? $first['taxonomy'] : '';
			}

			return $info;
		}

		/**
		 * Fire the action to print the custom tab.
		 *
		 * @param array $options The options of the custom tab.
		 *
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 */
		public function print_custom_tab( $options ) {
			if ( is_string( $options ) ) {
				// Backward compatibility.
				$options = array( 'action' => $options );
			}
			$current_tab     = $this->get_current_tab();
			$current_sub_tab = $this->get_current_sub_tab();

			include YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/custom-tab.php';
		}

		/**
		 * Check if panel has help tab
		 *
		 * @return bool Whether panel has help tab or no.
		 */
		public function has_help_tab() {
			return apply_filters( 'yith_plugin_fw_panel_has_help_tab', ! empty( $this->settings['help_tab'] ) && ( ! $this->is_free() || ! empty( $this->settings['help_tab']['show_on_free'] ) ), $this );
		}


		/**
		 * Checks whether current tab is special Help Tab
		 *
		 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
		 * @return bool Whether current tab is Help Tab
		 */
		public function is_help_tab() {
			return 'help' === $this->get_current_tab();
		}

		/**
		 * Prints special Help Tab
		 *
		 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
		 * @return void
		 */
		public function print_help_tab() {
			$options      = isset( $this->settings['help_tab'] ) ? $this->settings['help_tab'] : array();
			$plugin_title = isset( $this->settings['plugin_title'] ) ? $this->settings['plugin_title'] : $this->settings['page_title'];
			$is_extended  = $this->is_extended();
			$is_premium   = $this->is_premium() || ! $is_extended;

			if ( 0 !== strpos( $plugin_title, 'YITH' ) ) {
				$plugin_title = "YITH {$plugin_title}";
			}

			// translators: 1. Plugin name.
			$default_title   = $is_premium ? _x( 'Thank you for purchasing %s!', 'Help tab default title', 'yith-plugin-fw' ) : _x( 'Thank you for using %s!', 'Help tab default title', 'yith-plugin-fw' );
			$default_doc_url = $this->get_doc_url();

			// parse options.
			$options = wp_parse_args(
				$options,
				array(
					'title'              => sprintf( $default_title, $plugin_title ),
					'description'        => _x( 'We want to help you enjoy a wonderful experience with all of our products.', 'Help tab default description', 'yith-plugin-fw' ),
					'main_video'         => false,
					'playlists'          => array(),
					'hc_url'             => 'https://support.yithemes.com/hc/',
					'doc_url'            => $default_doc_url,
					'submit_ticket_url'  => 'https://yithemes.com/my-account/support/submit-a-ticket/',
					'show_hc_articles'   => true,
					'show_submit_ticket' => true,
				)
			);

			// add campaign parameters to url.
			if ( isset( $this->settings['plugin_slug'] ) && ! $is_extended ) {
				$utm_medium   = $this->settings['plugin_slug'];
				$utm_source   = yith_plugin_fw_panel_utm_source( $this );
				$utm_campaign = 'help-tab';

				$campaign_urls = array(
					'submit_ticket_url',
					'doc_url',
				);

				foreach ( $campaign_urls as $campaign_url ) {
					if ( empty( $options[ $campaign_url ] ) ) {
						continue;
					}

					$options[ $campaign_url ] = yith_plugin_fw_add_utm_data( $options[ $campaign_url ], $utm_medium, $utm_campaign, $utm_source );
				}
			}

			if ( $is_extended && $options['show_submit_ticket'] ) {
				$options['submit_ticket_url'] = add_query_arg( array( 'page' => 'bluehost' ), admin_url( 'admin.php' ) ) . '#/help';
			}

			// set template variables.
			$current_tab     = $this->get_current_tab();
			$current_sub_tab = $this->get_current_sub_tab();
			$latest_articles = isset( $this->settings['plugin_slug'] ) ? YIT_Help_Desk::get_latest_articles( $this->settings['plugin_slug'] ) : array();

			include YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/help-tab.php';
		}

		/**
		 * Add premium tab in admin-tabs if is set.
		 *
		 * @author Giuseppe Arcifa <giuseppe.arcifa@yithemes.com>
		 * @since  3.9.0
		 */
		protected function maybe_init_premium_tab() {
			if ( isset( $this->settings['premium_tab'] ) ) {
				$this->settings['admin-tabs']['premium'] = _x( 'Get premium', 'Premium tab name', 'yith-plugin-fw' );
			}
		}

		/**
		 * Add help tab in admin-tabs if is set.
		 *
		 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
		 * @since  3.9.0
		 */
		protected function maybe_init_help_tab() {
			if ( $this->has_help_tab() ) {
				$this->settings['admin-tabs']['help'] = _x( 'Help', 'Help tab name', 'yith-plugin-fw' );
			}
		}

		/**
		 * Get the plugin doc URL.
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return string
		 * @since  3.9.14
		 */
		protected function get_doc_url() {
			$plugin_slug = sanitize_title( $this->settings['plugin_slug'] ?? '' );
			if ( $plugin_slug ) {
				return $this->is_extended() ? "https://www.bluehost.com/help/article/{$plugin_slug}/" : "https://docs.yithemes.com/{$plugin_slug}/";
			}

			return '';
		}

		/**
		 * Is in panel?
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return bool
		 * @since  3.9.14
		 */
		protected function is_panel(): bool {
			$is_panel            = yith_plugin_fw_is_panel() && isset( $_GET['page'] ) && $_GET['page'] === $this->settings['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_wp_page_in_panel = isset( self::$panel_tabs_in_wp_pages, self::$panel_tabs_in_wp_pages['page'] ) && self::$panel_tabs_in_wp_pages['page'] === $this->settings['page'];

			return $is_panel || $is_wp_page_in_panel;
		}

		/**
		 * Is this the first panel page?
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return bool
		 * @since  3.9.14
		 */
		protected function is_first_page(): bool {
			$is_first_page = false;
			if ( $this->is_panel() ) {
				$first_tab     = current( array_keys( $this->settings['admin-tabs'] ?? array() ) );
				$sub_tabs      = $this->get_sub_tabs( $first_tab );
				$first_sub_tab = current( array_keys( $sub_tabs ) );

				$tab     = $this->get_current_tab();
				$sub_tab = $this->get_current_sub_tab();

				$is_first_page = $first_tab === $tab;

				if ( $is_first_page && ! ! $first_sub_tab ) {
					$is_first_page = $first_sub_tab === $sub_tab;
				}
			}

			return $is_first_page;
		}

		/**
		 * Add welcome modals.
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since  3.9.14
		 */
		protected function maybe_init_welcome_modals() {
			if ( isset( $this->settings['welcome_modals'] ) ) {
				// Priority needs to be greater than 11, to correctly check if it's first page also for WP pages.
				add_action( 'admin_enqueue_scripts', array( $this, 'maybe_print_welcome_modal' ), 20 );
				add_action( 'admin_init', array( $this, 'handle_welcome_modal_action' ) );
			}
		}

		/**
		 * Handle welcome modal actions.
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since  3.9.14
		 */
		public function handle_welcome_modal_action() {
			if ( isset( $_GET['yith-plugin-fw-welcome-modal-close'], $_GET['yith-panel-page'] ) && $_GET['yith-panel-page'] === $this->settings['page'] ) {
				check_admin_referer( 'welcome-modal-close' );
				$modal    = sanitize_key( wp_unslash( $_GET['yith-plugin-fw-welcome-modal-close'] ) );
				$on_close = $this->settings['welcome_modals']['modals'][ $modal ]['on_close'] ?? $this->settings['welcome_modals']['on_close'] ?? false;

				if ( $on_close && is_callable( $on_close ) ) {
					call_user_func( $on_close );
				}

				wp_safe_redirect( add_query_arg( array( 'page' => $this->settings['page'] ), admin_url( 'admin.php' ) ) );
				exit();
			}
		}

		/**
		 * Print the welcome modal.
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since  3.9.14
		 */
		public function maybe_print_welcome_modal() {
			$settings = $this->settings['welcome_modals'] ?? array();
			$modals   = $settings['modals'] ?? array();
			$show_in  = $settings['show_in'] ?? 'panel';

			if ( ! $this->is_panel() ) {
				return;
			}

			$is_page_to_show = true;

			switch ( $show_in ) {
				case 'panel':
					break;
				case 'first_page':
					$is_page_to_show = $this->is_first_page();
					break;
				default:
					if ( is_callable( $show_in ) ) {
						$context         = array(
							'is_first_page' => $this->is_first_page(),
							'tab'           => $this->get_current_tab(),
							'sub_tab'       => $this->get_current_sub_tab(),
						);
						$is_page_to_show = call_user_func( $show_in, $context );
					}
					break;
			}

			if ( $is_page_to_show ) {
				foreach ( $modals as $key => $modal ) {
					$show            = $modal['show'] ?? null;
					$should_be_shown = ! ! ( is_callable( $show ) ? call_user_func( $show ) : $show );
					$modal['items']  = $modal['items'] ?? array();

					if ( $should_be_shown ) {
						$type      = $modal['type'] ?? $key;
						$template  = YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/welcome-modals/' . $type . '.php';
						$close_url = wp_nonce_url(
							add_query_arg(
								array(
									'yith-plugin-fw-welcome-modal-close' => $key,
									'yith-panel-page'                    => $this->settings['page'],
								),
								admin_url()
							),
							'welcome-modal-close'
						);
						$plugin    = array(
							'name'    => $this->settings['plugin_name'] ?? $this->settings['page_title'] ?? '',
							'version' => $this->settings['plugin_version'] ?? '',
							'slug'    => $this->settings['plugin_slug'] ?? '',
							'icon'    => $this->settings['plugin_icon'] ?? '',
						);

						$plugin['version'] = preg_replace( '/\.0$/', '', $plugin['version'] ); // Make the version nicer.

						if ( 'welcome' === $type ) {
							if ( isset( $modal['items']['documentation'] ) ) {
								$modal['items']['documentation'] = wp_parse_args(
									$modal['items']['documentation'],
									array(
										'title'       => __( 'Read the <mark>plugin documentation</mark>', 'yith-plugin-fw' ),
										'description' => __( 'Learn what you can really do with this powerful tool', 'yith-plugin-fw' ),
										'url'         => $this->get_doc_url(),
									)
								);
							}

							if ( isset( $modal['items']['how-to-video'], $modal['items']['how-to-video']['url'] ) ) {
								$modal['items']['how-to-video'] = wp_parse_args(
									$modal['items']['how-to-video'],
									array(
										'title'       => __( 'Watch our <mark>"First Steps" video</mark>', 'yith-plugin-fw' ),
										'description' => __( 'See how it works before you start using it', 'yith-plugin-fw' ),
									)
								);
							}
						}

						if ( 'update' === $type ) {
							if ( ! isset( $modal['changelog_url'] ) && $this->is_premium() && ! $this->is_extended() ) {
								$doc_url = $this->get_doc_url();
								if ( $doc_url ) {
									$modal['changelog_url'] = trailingslashit( $doc_url ) . 'changelog/changelog-premium-version/';
								}
							}
						}

						if ( file_exists( $template ) ) {
							wp_enqueue_script( 'yith-plugin-fw-welcome-modal' );
							wp_enqueue_style( 'yith-plugin-fw-welcome-modal' );

							// Plugin, modal and close_url variables are required in the modal templates.
							$print_template_cb = function () use ( $template, $plugin, $modal, $close_url ) {
								?>
								<script type="text/html" id="tmpl-yith-plugin-fw-welcome-modal">
									<?php include $template; ?>
								</script>
								<?php
							};

							add_action( 'admin_footer', $print_template_cb );
						}

						break;
					}
				}
			}
		}

		/**
		 * Checks whether current tab is Premium Tab
		 *
		 * @author Giuseppe Arcifa <giuseppe.arcifa@yithemes.com>
		 * @return bool
		 * @since  3.9.0
		 */
		protected function is_premium_tab() {
			return 'premium' === $this->get_current_tab();
		}

		/**
		 * Check if panel has premium tab
		 * (Check for premium Tab through the premium_tab param fully handled by plugin-fw)
		 *
		 * @author Giuseppe Arcifa <giuseppe.arcifa@yithemes.com>
		 * @return bool
		 * @since  3.9.0
		 */
		protected function has_premium_tab() {
			return ! empty( $this->settings['premium_tab'] ) && ( $this->is_free() || $this->is_extended() );
		}

		/**
		 * Prints Premium Tab
		 *
		 * @author Giuseppe Arcifa <giuseppe.arcifa@yithemes.com>
		 * @return void
		 * @since  3.9.0
		 */
		protected function print_premium_tab() {
			$options     = $this->settings['premium_tab'] ?? array();
			$is_extended = $this->is_extended();

			$defaults = array(
				'premium_features'          => array(),
				'main_image_url'            => '',
				'show_free_vs_premium_link' => true,
				'show_premium_landing_link' => $is_extended,
			);
			$options  = wp_parse_args( $options, $defaults );

			$plugin_slug = ! empty( $this->settings['plugin_slug'] ) ? $this->settings['plugin_slug'] : '';
			$premium_url = '';
			if ( $plugin_slug ) {
				$premium_url = ! empty( $options['landing_page_url'] ) ? $options['landing_page_url'] : 'https://yithemes.com/themes/plugins/' . $plugin_slug;
			}

			include YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/premium-tab.php';
		}

		/**
		 * Add sections and fields to setting panel.
		 * Read all options and show sections and fields.
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function add_fields() {
			$yit_options = $this->get_main_array_options();
			$option_key  = $this->get_current_option_key();

			if ( ! $option_key || ! isset( $yit_options[ $option_key ] ) ) {
				return;
			}

			foreach ( $yit_options[ $option_key ] as $section => $data ) {
				add_settings_section( "yit_settings_{$option_key}_{$section}", $this->get_section_title( $section ), $this->get_section_description( $section ), 'yit' );
				foreach ( $data as $option ) {
					if ( isset( $option['id'] ) && isset( $option['type'] ) && isset( $option['name'] ) ) {
						add_settings_field(
							'yit_setting_' . $option['id'],
							$option['name'],
							array( $this, 'render_field' ),
							'yit',
							"yit_settings_{$option_key}_{$section}",
							array(
								'option'    => $option,
								'label_for' => $this->get_id_field( $option['id'] ),
							)
						);
					}
				}
			}
		}

		/**
		 * Add the tabs to admin bar menu.
		 * Set all tabs of settings page on wp admin bar.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function add_admin_bar_menu() {
			global $wp_admin_bar;

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( ! empty( $this->settings['admin_tabs'] ) ) {
				foreach ( $this->settings['admin-tabs'] as $item => $title ) {
					$wp_admin_bar->add_menu(
						array(
							'parent' => $this->settings['parent'],
							'title'  => $title,
							'id'     => $this->settings['parent'] . '-' . $item,
							'href'   => admin_url( 'themes.php' ) . '?page=' . $this->settings['parent_page'] . '&tab=' . $item,
						)
					);
				}
			}
		}

		/**
		 * Get current tab.
		 * Retrieve the id of tab shown, return general is the current tab is not defined.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string|false
		 * @since  3.9.14 Check for WP pages.
		 */
		public function get_current_tab() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$admin_tabs = array_keys( $this->settings['admin-tabs'] );

			global $post_type, $taxonomy;
			$wp_tabs = array();
			if ( $this->is_custom_post_type_page() ) {
				$wp_tabs = $this->get_post_type_tabs( $post_type );
			} elseif ( $this->is_custom_taxonomy_page() ) {
				$wp_tabs = $this->get_taxonomy_tabs( $taxonomy );
			}

			if ( $wp_tabs && isset( $wp_tabs['tab'] ) ) {
				return $wp_tabs['tab'];
			}

			if ( ! isset( $_GET['page'] ) || $_GET['page'] !== $this->settings['page'] ) {
				return false;
			}
			if ( isset( $_REQUEST['yit_tab_options'] ) ) {
				return sanitize_key( wp_unslash( $_REQUEST['yit_tab_options'] ) );
			} elseif ( isset( $_GET['tab'] ) ) {
				return sanitize_key( wp_unslash( $_GET['tab'] ) );
			} elseif ( isset( $admin_tabs[0] ) ) {
				return $admin_tabs[0];
			} else {
				return 'general';
			}
			// phpcs:enable
		}

		/**
		 * Get the current sub-tab.
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return string The key of the sub-tab if exists, empty string otherwise.
		 * @since    3.4.0
		 * @since    3.9.14 Check for WP pages.
		 */
		public function get_current_sub_tab() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended

			global $post_type, $taxonomy;
			$wp_tabs = array();
			if ( $this->is_custom_post_type_page() ) {
				$wp_tabs = $this->get_post_type_tabs( $post_type );
			} elseif ( $this->is_custom_taxonomy_page() ) {
				$wp_tabs = $this->get_taxonomy_tabs( $taxonomy );
			}

			if ( $wp_tabs && isset( $wp_tabs['sub_tab'] ) ) {
				return $wp_tabs['sub_tab'];
			}

			$sub_tabs = $this->get_sub_tabs();
			$sub_tab  = isset( $_REQUEST['sub_tab'] ) ? sanitize_key( wp_unslash( $_REQUEST['sub_tab'] ) ) : '';

			if ( $sub_tabs ) {
				if ( $sub_tab && ! isset( $sub_tabs[ $sub_tab ] ) || ! $sub_tab ) {
					$sub_tab = current( array_keys( $sub_tabs ) );
				}
			} else {
				$sub_tab = '';
			}

			return $sub_tab;
			// phpcs:enable
		}

		/**
		 * Return the option key related to the current page.
		 * for sub-tabbed tabs, it will return the current sub-tab.
		 * fot normal tabs, it will return the current tab.
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return string the current sub-tab, if exists; the current tab otherwise.
		 * @since    3.4.0
		 */
		public function get_current_option_key() {
			$current_tab     = $this->get_current_tab();
			$current_sub_tab = $this->get_current_sub_tab();

			if ( ! $current_tab || $this->is_custom_post_type_page() || $this->is_custom_taxonomy_page() ) {
				return false;
			}

			return ! ! $current_sub_tab ? $current_sub_tab : $current_tab;
		}

		/**
		 * Message
		 * define an array of message and show the content od message if
		 * is find in the query string
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function message() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$message = array(
				'element_exists'   => $this->get_message( '<strong>' . __( 'The element you have entered already exists. Please, enter another name.', 'yith-plugin-fw' ) . '</strong>', 'error', false ),
				'saved'            => $this->get_message( '<strong>' . __( 'Settings saved', 'yith-plugin-fw' ) . '.</strong>', 'updated', false ),
				'reset'            => $this->get_message( '<strong>' . __( 'Settings reset', 'yith-plugin-fw' ) . '.</strong>', 'updated', false ),
				'delete'           => $this->get_message( '<strong>' . __( 'Element deleted correctly.', 'yith-plugin-fw' ) . '</strong>', 'updated', false ),
				'updated'          => $this->get_message( '<strong>' . __( 'Element updated correctly.', 'yith-plugin-fw' ) . '</strong>', 'updated', false ),
				'settings-updated' => $this->get_message( '<strong>' . __( 'Element updated correctly.', 'yith-plugin-fw' ) . '</strong>', 'updated', false ),
				'imported'         => $this->get_message( '<strong>' . __( 'Database imported correctly.', 'yith-plugin-fw' ) . '</strong>', 'updated', false ),
				'no-imported'      => $this->get_message( '<strong>' . __( 'An error has occurred during import. Please try again.', 'yith-plugin-fw' ) . '</strong>', 'error', false ),
				'file-not-valid'   => $this->get_message( '<strong>' . __( 'The added file is not valid.', 'yith-plugin-fw' ) . '</strong>', 'error', false ),
				'cant-import'      => $this->get_message( '<strong>' . __( 'Sorry, import is disabled.', 'yith-plugin-fw' ) . '</strong>', 'error', false ),
				'ord'              => $this->get_message( '<strong>' . __( 'Sorting successful.', 'yith-plugin-fw' ) . '</strong>', 'updated', false ),
			);

			foreach ( $message as $key => $value ) {
				if ( isset( $_GET[ $key ] ) ) {
					echo wp_kses_post( $message[ $key ] );
				}
			}
			// phpcs:enable
		}

		/**
		 * Get Message
		 * return html code of message
		 *
		 * @param string $message The message.
		 * @param string $type    The type of message (can be 'error' or 'updated').
		 * @param bool   $echo    Set to true if you want to print the message.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string
		 */
		public function get_message( $message, $type = 'error', $echo = true ) {
			$message = '<div id="message" class="' . esc_attr( $type ) . ' fade"><p>' . wp_kses_post( $message ) . '</p></div>';
			if ( $echo ) {
				echo wp_kses_post( $message );
			}

			return $message;
		}

		/**
		 * Get Tab Path Files
		 * return an array with file names of tabs
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return array
		 */
		public function get_tabs_path_files() {
			$option_files_path = $this->settings['options-path'] . '/';
			$tabs              = array();

			foreach ( (array) glob( $option_files_path . '*.php' ) as $filename ) {
				preg_match( '/(.*)-options\.(.*)/', basename( $filename ), $filename_parts );

				if ( ! isset( $filename_parts[1] ) ) {
					continue;
				}

				$tab          = $filename_parts[1];
				$tabs[ $tab ] = $filename;
			}

			return $tabs;
		}

		/**
		 * Get main array options
		 * return an array with all options defined on options-files
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return array
		 */
		public function get_main_array_options() {
			$this->maybe_init_vars();

			return $this->main_array_options;
		}

		/**
		 * Get tab hierarchy.
		 *
		 * @return array
		 */
		public function get_tabs_hierarchy() {
			$this->maybe_init_vars();

			return $this->tabs_hierarchy;
		}

		/**
		 * Return the sub-tabs array of a specific tab
		 *
		 * @param array|bool $_tab the tab; if not set it'll be the current tab.
		 *
		 * @since    3.4.0
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return array Sub-tabs array.
		 */
		public function get_sub_tabs( $_tab = false ) {
			if ( false === $_tab ) {
				$_tab = $this->get_current_tab();
			}

			if ( is_string( $_tab ) ) {
				$main_array_options  = $this->get_main_array_options();
				$current_tab_options = $main_array_options[ $_tab ] ?? array();
				if ( $current_tab_options ) {
					$_tab = array( $_tab => $current_tab_options );
				}
			}

			$_tab_options = ! ! $_tab && is_array( $_tab ) ? current( $_tab ) : false;
			$_first       = ! ! $_tab_options && is_array( $_tab_options ) ? current( $_tab_options ) : false;
			if ( $_first && is_array( $_first ) && isset( $_first['type'] ) && 'multi_tab' === $_first['type'] && ! empty( $_first['sub-tabs'] ) ) {
				return $_first['sub-tabs'];
			}

			return array();
		}

		/**
		 * Retrieve the first sub-tab key.
		 *
		 * @param string|false $_tab The tab; if not set it'll be the current tab.
		 *
		 * @return false|mixed
		 */
		public function get_first_sub_tab_key( $_tab = false ) {
			$key = false;
			if ( is_string( $_tab ) ) {
				$main_array_options  = $this->get_main_array_options();
				$current_tab_options = isset( $main_array_options[ $_tab ] ) ? $main_array_options[ $_tab ] : array();
				if ( $current_tab_options ) {
					$_tab = array( $_tab => $current_tab_options );
				}
			}
			$sub_tabs = $this->get_sub_tabs( $_tab );
			if ( $sub_tabs ) {
				$key = current( array_keys( $sub_tabs ) );
			}

			return $key;
		}


		/**
		 * Set an array with all default options
		 * put default options in an array
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return array
		 */
		public function get_default_options() {
			$yit_options     = $this->get_main_array_options();
			$default_options = array();

			foreach ( $yit_options as $tab => $sections ) {
				foreach ( $sections as $section ) {
					foreach ( $section as $id => $value ) {
						if ( isset( $value['std'] ) && isset( $value['id'] ) ) {
							$default_options[ $value['id'] ] = $value['std'];
						}
					}
				}
			}

			unset( $yit_options );

			return $default_options;
		}


		/**
		 * Get the title of the tab
		 * return the title of tab
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string
		 */
		public function get_tab_title() {
			$yit_options = $this->get_main_array_options();
			$option_key  = $this->get_current_option_key();

			foreach ( $yit_options[ $option_key ] as $sections => $data ) {
				foreach ( $data as $option ) {
					if ( isset( $option['type'] ) && 'title' === $option['type'] ) {
						return $option['name'];
					}
				}
			}

			return '';
		}

		/**
		 * Get the title of the section
		 * return the title of section
		 *
		 * @param string $section The section.
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string
		 */
		public function get_section_title( $section ) {
			$yit_options = $this->get_main_array_options();
			$option_key  = $this->get_current_option_key();

			foreach ( $yit_options[ $option_key ][ $section ] as $option ) {
				if ( isset( $option['type'] ) && 'section' === $option['type'] ) {
					return $option['name'];
				}
			}

			return '';
		}

		/**
		 * Get the description of the section
		 * return the description of section if is set
		 *
		 * @param string $section The section.
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string
		 */
		public function get_section_description( $section ) {
			$yit_options = $this->get_main_array_options();
			$option_key  = $this->get_current_option_key();

			foreach ( $yit_options[ $option_key ][ $section ] as $option ) {
				if ( isset( $option['type'] ) && 'section' === $option['type'] && isset( $option['desc'] ) ) {
					return '<p>' . $option['desc'] . '</p>';
				}
			}

			return '';
		}


		/**
		 * Show form when necessary
		 * return true if 'showform' is not defined
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return bool
		 */
		public function is_show_form() {
			$yit_options = $this->get_main_array_options();
			$option_key  = $this->get_current_option_key();

			foreach ( $yit_options[ $option_key ] as $sections => $data ) {
				foreach ( $data as $option ) {
					if ( ! isset( $option['type'] ) || 'title' !== $option['type'] ) {
						continue;
					}
					if ( isset( $option['showform'] ) ) {
						return $option['showform'];
					} else {
						return true;
					}
				}
			}
		}

		/**
		 * Get name field
		 * return a string with the name of the input field
		 *
		 * @param string $name The name.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string
		 */
		public function get_name_field( $name = '' ) {
			return 'yit_' . $this->settings['parent'] . '_options[' . $name . ']';
		}

		/**
		 * Get id field
		 * return a string with the id of the input field
		 *
		 * @param string $id The ID.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return string
		 */
		public function get_id_field( $id ) {
			return 'yit_' . $this->settings['parent'] . '_options_' . $id;
		}


		/**
		 * Render the field showed in the setting page
		 * include the file of the option type, if file do not exists
		 * return a text area
		 *
		 * @param array $param The parameters.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return void
		 */
		public function render_field( $param ) {
			if ( ! empty( $param ) && isset( $param ['option'] ) ) {
				$option     = $param['option'];
				$db_options = $this->get_options();

				$custom_attributes = array();

				if ( ! empty( $option['custom_attributes'] ) && is_array( $option['custom_attributes'] ) ) {
					foreach ( $option['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				$custom_attributes = implode( ' ', $custom_attributes );
				$std               = isset( $option['std'] ) ? $option['std'] : '';
				$db_value          = ( isset( $db_options[ $option['id'] ] ) ) ? $db_options[ $option['id'] ] : $std;

				if ( isset( $option['deps'] ) ) {
					$deps = $option['deps'];
				}

				if ( 'on-off' === $option['type'] ) {
					$option['type'] = 'onoff';
				}

				$field_template_path = yith_plugin_fw_get_field_template_path( $option );
				if ( $field_template_path ) {
					$field_container_path = apply_filters( 'yith_plugin_fw_panel_field_container_template_path', YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/panel-field-container.php', $option );
					file_exists( $field_container_path ) && include $field_container_path;
				} else {
					do_action( "yit_panel_{$option['type']}", $option, $db_value, $custom_attributes );
				}
			}
		}

		/**
		 * Get options from db
		 * return the options from db, if the options aren't defined in the db,
		 * get the default options ad add the options in the db
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
		 * @return array
		 */
		public function get_options() {
			$options = get_option( 'yit_' . $this->settings['parent'] . '_options' );
			if ( false === $options || ( isset( $_REQUEST['yit-action'] ) && 'reset' === sanitize_key( wp_unslash( $_REQUEST['yit-action'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$options = $this->get_default_options();
			}

			return $options;
		}

		/**
		 * Show a box panel with specific content in two columns as a new woocommerce type
		 *
		 * @param array $args The arguments.
		 *
		 * @author   Emanuela Castorina      <emanuela.castorina@yithemes.com>
		 */
		public static function add_infobox( $args = array() ) {
			if ( ! empty( $args ) ) {
				extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				require YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/boxinfo.php';
			}
		}

		/**
		 * Show a box panel with specific content in two columns as a new woocommerce type
		 *
		 * @param array $args Arguments.
		 *
		 * @return   void
		 * @deprecated 3.0.12 Do nothing! Method left to prevent Fatal Error if called directly
		 */
		public static function add_videobox( $args = array() ) {

		}

		/**
		 * Fire the action to print the custom tab
		 *
		 * @return void
		 * @deprecated 3.0.12 Do nothing! Method left to prevent Fatal Error if called directly
		 */
		public function print_video_box() {

		}

		/**
		 * Sort plugins by name in YITH Plugins menu.
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.0.0
		 */
		public static function sort_plugins() {
			global $submenu;
			if ( ! empty( $submenu['yith_plugin_panel'] ) ) {
				$sorted_plugins = $submenu['yith_plugin_panel'];

				usort(
					$sorted_plugins,
					function ( $a, $b ) {
						return strcmp( current( $a ), current( $b ) );
					}
				);

				$submenu['yith_plugin_panel'] = $sorted_plugins; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}

		/**
		 * Add menu class in YITH Plugins menu.
		 *
		 * @param array $menu The menu.
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @return array
		 * @since    3.0.0
		 */
		public static function add_menu_class_in_yith_plugin( $menu ) {
			global $submenu;

			if ( ! empty( $submenu['yith_plugin_panel'] ) ) {
				$item_count = count( $submenu['yith_plugin_panel'] );
				$columns    = absint( $item_count / 20 ) + 1;
				$columns    = max( 1, min( $columns, 3 ) );
				$columns    = apply_filters( 'yith_plugin_fw_yith_plugins_menu_columns', $columns, $item_count );

				if ( $columns > 1 ) {
					$class = "yith-plugin-fw-menu-$columns-columns";
					foreach ( $menu as $order => $top ) {
						if ( 'yith_plugin_panel' === $top[2] ) {
							$c                 = $menu[ $order ][4];
							$menu[ $order ][4] = add_cssclass( $class, $c );
							break;
						}
					}
				}
			}

			return $menu;
		}

		/**
		 * Check if inside the admin tab there's the premium tab to
		 * check if the plugin is a free or not
		 * TODO: remove this from panel, and move to a more generic plugin-registration process; use general plugin data wherever is needed
		 *
		 * @author Emanuela Castorina
		 */
		public function is_free() {
			return ! empty( $this->settings['premium_tab'] ) && ! $this->is_extended() && ! $this->is_premium();
		}

		/**
		 * Checks whether current panel is for extended version of the plugin
		 * TODO: remove this from panel, and move to a more generic plugin-registration process; use general plugin data wherever is needed
		 *
		 * @return bool
		 */
		public function is_extended() {
			return ! empty( $this->settings['is_extended'] );
		}

		/**
		 * Checks whether current panel is for premium version of the plugin
		 * TODO: remove this from panel, and move to a more generic plugin-registration process; use general plugin data wherever is needed
		 *
		 * @return bool
		 */
		public function is_premium() {
			return ! empty( $this->settings['is_premium'] );
		}

		/**
		 * Add plugin banner.
		 *
		 * @param string $page The page.
		 */
		public function add_plugin_banner( $page ) {
			if ( $page !== $this->settings['page'] || ! isset( $this->settings['class'] ) ) {
				return;
			}

			?>
			<?php if ( $this->is_free() && isset( $this->settings['plugin_slug'] ) ) : ?>
				<?php
				$rate_link = apply_filters( 'yith_plugin_fw_rate_url', 'https://wordpress.org/support/plugin/' . $this->settings['plugin_slug'] . '/reviews/#new-post' );
				?>
				<h1 class="notice-container"></h1>
				<div class="yith-plugin-fw-banner">
					<h1 translate="no"><?php echo esc_html( $this->settings['page_title'] ); ?></h1>
				</div>
				<div class="yith-plugin-fw-rate">
					<?php
					printf(
						'<strong>%s</strong> %s <a href="%s" target="_blank"><u>%s</u> <span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></a>  %s',
						esc_html__( 'We need your support', 'yith-plugin-fw' ),
						esc_html__( 'to keep updating and improving the plugin. Please,', 'yith-plugin-fw' ),
						esc_url( $rate_link ),
						esc_html__( 'help us by leaving a good review', 'yith-plugin-fw' ),
						esc_html__( ':) Thanks!', 'yith-plugin-fw' )
					);
					?>
				</div>
			<?php else : ?>
				<h1 class="notice-container"></h1>
				<div class="yith-plugin-fw-banner">
					<h1 translate="no"><?php echo esc_html( $this->settings['page_title'] ); ?></h1>
				</div>
			<?php endif ?>
			<?php
		}

		/**
		 * Add additional element after print the field.
		 *
		 * @param array $field The field.
		 *
		 * @author Emanuela Castorina
		 * @since  3.2
		 */
		public function add_yith_ui( $field ) {
			global $pagenow;

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

			if ( empty( $this->settings['class'] ) || ! isset( $field['type'] ) ) {
				return;
			}
			if ( 'admin.php' === $pagenow && $screen && strpos( $screen->id, $this->settings['page'] ) !== false ) {
				switch ( $field['type'] ) {
					case 'datepicker':
						echo '<span class="yith-icon yith-icon-calendar yith-icon--right-overlay"></span>';
						break;
					default:
						break;
				}
			}
		}

		/**
		 * Get post type tabs.
		 *
		 * @param string $post_type The post type.
		 *
		 * @return array
		 */
		public function get_post_type_tabs( $post_type ) {
			$tabs = array();

			foreach ( $this->get_tabs_hierarchy() as $key => $info ) {
				if ( isset( $info['type'], $info['post_type'] ) && 'post_type' === $info['type'] && $post_type === $info['post_type'] ) {
					if ( ! empty( $info['parent'] ) ) {
						$tabs = array(
							'tab'     => $info['parent'],
							'sub_tab' => $key,
							'options' => $info,
						);
					} else {
						$tabs = array(
							'tab'     => $key,
							'options' => $info,
						);
					}
					break;
				}
			}

			$panel_page = isset( $this->settings['page'] ) ? $this->settings['page'] : 'general';

			return apply_filters( "yith_plugin_fw_panel_{$panel_page}_get_post_type_tabs", $tabs, $post_type );
		}

		/**
		 * Get the taxonomy tabs.
		 *
		 * @param string $taxonomy The taxonomy.
		 *
		 * @return array
		 */
		public function get_taxonomy_tabs( $taxonomy ) {
			$tabs = array();

			foreach ( $this->get_tabs_hierarchy() as $key => $info ) {
				if ( isset( $info['type'], $info['taxonomy'] ) && 'taxonomy' === $info['type'] && $taxonomy === $info['taxonomy'] ) {
					if ( ! empty( $info['parent'] ) ) {
						$tabs = array(
							'tab'     => $info['parent'],
							'sub_tab' => $key,
							'options' => $info,
						);
					} else {
						$tabs = array(
							'tab'     => $key,
							'options' => $info,
						);
					}
					break;
				}
			}

			$panel_page = isset( $this->settings['page'] ) ? $this->settings['page'] : 'general';

			return apply_filters( "yith_plugin_fw_panel_{$panel_page}_get_taxonomy_tabs", $tabs, $taxonomy );
		}


		/**
		 * If the panel page is a WP Page, this will redirect you to the correct page
		 * useful when a Post Type (Taxonomy) is the first tab of your panel, so when you open your panel it'll open the Post Type (Taxonomy) list
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.4.0
		 */
		public function maybe_redirect_to_proper_wp_page() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			global $pagenow;
			if (
				'admin.php' === $pagenow && isset( $_GET['page'] ) && $this->settings['page'] === $_GET['page']
				&&
				! $this->is_custom_taxonomy_page() && ! $this->is_custom_post_type_page()
				&&
				! isset( $_REQUEST['yith-plugin-fw-panel-skip-redirect'] )
			) {
				$url = $this->get_nav_url( $this->settings['page'], $this->get_current_tab(), $this->get_current_sub_tab() );
				if ( strpos( $url, 'edit.php' ) !== false || strpos( $url, 'edit-tags.php' ) !== false ) {
					wp_safe_redirect( add_query_arg( array( 'yith-plugin-fw-panel-skip-redirect' => 1 ), $url ) );
					exit;
				}
			}
			// phpcs:enable
		}

		/**
		 * Print the Panel tabs and sub-tabs navigation in WP pages
		 * Important: this opens a wrapper <div> that will be closed through YIT_Plugin_Panel::print_panel_tabs_in_post_edit_page_end()
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.4.0
		 */
		public function print_panel_tabs_in_wp_pages() {
			if ( self::$panel_tabs_in_wp_pages && 'all_admin_notices' === current_action() ) {
				global $pagenow;

				wp_enqueue_style( 'yit-plugin-style' );
				wp_enqueue_script( 'yit-plugin-panel' );

				$wrap_class = isset( $this->settings['class'] ) ? $this->settings['class'] : '';
				$options    = isset( self::$panel_tabs_in_wp_pages['options'] ) ? self::$panel_tabs_in_wp_pages['options'] : array();

				$options_to_classes   = array( 'type', 'post_type', 'taxonomy' );
				$page_wrapper_classes = array(
					'yith-plugin-fw-wp-page-wrapper',
					isset( $options['wrapper-class'] ) ? $options['wrapper-class'] : '',
				);

				if ( in_array( $pagenow, array( 'edit.php', 'edit-tags.php' ), true ) ) {
					$options_to_classes[]   = 'wp-list-style';
					$page_wrapper_classes[] = 'yith-plugin-ui';
				}

				foreach ( $options_to_classes as $key ) {
					if ( isset( $options[ $key ] ) ) {
						$option                 = $options[ $key ];
						$page_wrapper_classes[] = "yith-plugin-ui--{$option}-{$key}";
					}
				}

				$page_wrapper_classes = implode( ' ', array_filter( $page_wrapper_classes ) );

				echo '<div class="' . esc_attr( $page_wrapper_classes ) . '">';

				echo '<div class="' . esc_attr( $wrap_class ) . '">';

				$this->add_plugin_banner( $this->settings['page'] );
				$this->print_tabs_nav( self::$panel_tabs_in_wp_pages );
				echo '</div>';

				if ( self::$panel_tabs_in_wp_pages['current_sub_tab'] ) {
					echo '<div class="yith-plugin-fw-wp-page__sub-tab-wrap">';
				}
			}
		}

		/**
		 * Close the wrapper opened in YIT_Plugin_Panel::print_panel_tabs_in_wp_pages()
		 *
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since    3.4.0
		 */
		public function print_panel_tabs_in_wp_pages_end() {
			if ( self::$panel_tabs_in_wp_pages ) {

				if ( self::$panel_tabs_in_wp_pages['current_sub_tab'] ) {
					echo '</div><!-- /yith-plugin-fw-wp-page__sub-tab-wrap -->';
				}

				echo '</div><!-- /yith-plugin-fw-wp-page-wrapper -->';
			}
		}

		/**
		 * Set the parent page to handle menu for WP Pages.
		 *
		 * @param string $parent_file The parent file.
		 *
		 * @return string
		 */
		public function set_parent_file_to_handle_menu_for_wp_pages( $parent_file ) {
			if ( self::$panel_tabs_in_wp_pages ) {
				return 'yith_plugin_panel';
			}

			return $parent_file;
		}

		/**
		 * Set the submenu page to handle menu for WP Pages.
		 *
		 * @param string $submenu_file The submenu file.
		 * @param string $parent_file  The parent file.
		 *
		 * @return mixed
		 */
		public function set_submenu_file_to_handle_menu_for_wp_pages( $submenu_file, $parent_file ) {
			if ( self::$panel_tabs_in_wp_pages ) {
				return $this->settings['page'];
			}

			return $submenu_file;
		}

		/**
		 * Save the toggle element options.
		 *
		 * @return bool
		 */
		public function save_toggle_element_options() {
			return true;
		}

		/**
		 * Add UTM data in premium tab
		 *
		 * @param string $url  The url that want to track.
		 * @param string $slug Plugin slug.
		 *
		 * @since 3.8.4
		 */
		public function add_utm_data_on_premium_tab( $url, $slug ) {
			return ! empty( $this->settings['plugin_slug'] ) && $slug === $this->settings['plugin_slug'] && 'premium' === $this->get_current_tab() ? yith_plugin_fw_add_utm_data( $url, $slug, 'button-upgrade', yith_plugin_fw_panel_utm_source( $this ) ) : $url;
		}
	}
}
