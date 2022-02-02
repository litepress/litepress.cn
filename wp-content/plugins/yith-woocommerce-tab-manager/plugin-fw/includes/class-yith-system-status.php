<?php
/**
 * YITH System Status Class
 * handle System Status panel
 *
 * @class   YITH_System_Status
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_System_Status' ) ) {
	/**
	 * YITH_System_Status class.
	 *
	 * @author     Alberto Ruggiero
	 */
	class YITH_System_Status {
		/**
		 * The page slug
		 *
		 * @var string
		 */
		protected $page = 'yith_system_info';

		/**
		 * Plugins requirements list
		 *
		 * @var array
		 */
		protected $plugins_requirements = array();

		/**
		 * Requirements labels
		 *
		 * @var array
		 */
		public $requirement_labels = array();

		/**
		 * Recommended memory amount 134217728 = 128M
		 *
		 * @var integer
		 */
		private $recommended_memory = 134217728;

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_System_Status
		 */
		protected static $instance = null;

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_System_Status
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public function __construct() {

			if ( ! is_admin() ) {
				return;
			}

			/**
			 * Add to prevent trigger admin_init called directly
			 * wp-admin/admin-post.php?page=yith_system_info
			 */
			if ( ! is_user_logged_in() ) {
				return;
			}

			add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 99 );
			add_action( 'admin_init', array( $this, 'check_system_status' ) );
			add_action( 'admin_notices', array( $this, 'activate_system_notice' ), 15 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
			add_action( 'init', array( $this, 'set_requirements_labels' ) );
			add_action( 'wp_ajax_yith_create_log_file', array( $this, 'create_log_file' ) );

		}

		/**
		 * Set requirements labels
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public function set_requirements_labels() {

			$this->requirement_labels = array(
				'min_wp_version'    => esc_html__( 'WordPress Version', 'yith-plugin-fw' ),
				'min_wc_version'    => esc_html__( 'WooCommerce Version', 'yith-plugin-fw' ),
				'wp_memory_limit'   => esc_html__( 'Available Memory', 'yith-plugin-fw' ),
				'min_php_version'   => esc_html__( 'PHP Version', 'yith-plugin-fw' ),
				'min_tls_version'   => esc_html__( 'TLS Version', 'yith-plugin-fw' ),
				'wp_cron_enabled'   => esc_html__( 'WordPress Cron', 'yith-plugin-fw' ),
				'simplexml_enabled' => esc_html__( 'SimpleXML', 'yith-plugin-fw' ),
				'mbstring_enabled'  => esc_html__( 'MultiByte String', 'yith-plugin-fw' ),
				'imagick_version'   => esc_html__( 'ImageMagick Version', 'yith-plugin-fw' ),
				'gd_enabled'        => esc_html__( 'GD Library', 'yith-plugin-fw' ),
				'iconv_enabled'     => esc_html__( 'Iconv Module', 'yith-plugin-fw' ),
				'opcache_enabled'   => esc_html__( 'OPCache Save Comments', 'yith-plugin-fw' ),
				'url_fopen_enabled' => esc_html__( 'URL FOpen', 'yith-plugin-fw' ),
			);

		}

		/**
		 * Add "System Information" submenu page under YITH Plugins
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public function add_submenu_page() {

			$system_info  = get_option( 'yith_system_info', array() );
			$error_notice = ( isset( $system_info['errors'] ) && true === $system_info['errors'] ? ' <span class="yith-system-info-menu update-plugins">!</span>' : '' );
			$settings     = array(
				'parent_page' => 'yith_plugin_panel',
				'page_title'  => esc_html__( 'System Status', 'yith-plugin-fw' ),
				'menu_title'  => esc_html__( 'System Status', 'yith-plugin-fw' ) . $error_notice,
				'capability'  => 'manage_options',
				'page'        => $this->page,
			);

			add_submenu_page(
				$settings['parent_page'],
				$settings['page_title'],
				$settings['menu_title'],
				$settings['capability'],
				$settings['page'],
				array( $this, 'show_information_panel' )
			);
		}

		/**
		 * Add "System Information" page template under YITH Plugins
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public function show_information_panel() {

			$path = defined( 'YIT_CORE_PLUGIN_PATH' ) ? YIT_CORE_PLUGIN_PATH : get_template_directory() . '/core/plugin-fw/';

			require_once $path . '/templates/sysinfo/system-information-panel.php';

		}

		/**
		 * Perform system status check
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public function check_system_status() {

			if ( '' === get_option( 'yith_system_info' ) || ( isset( $_GET['page'] ) && $_GET['page'] === $this->page ) ) { //phpcs:ignore

				$this->add_requirements(
					esc_html__( 'YITH Plugins', 'yith-plugin-fw' ),
					array(
						'min_wp_version'  => '5.6',
						'min_wc_version'  => '5.3',
						'min_php_version' => '7.0',
					)
				);
				$this->add_requirements(
					esc_html__( 'WooCommerce', 'yith-plugin-fw' ),
					array(
						'wp_memory_limit' => '64M',
					)
				);

				$system_info   = $this->get_system_info();
				$check_results = array();
				$errors        = 0;

				foreach ( $system_info as $key => $value ) {
					$check_results[ $key ] = array( 'value' => $value );

					if ( isset( $this->plugins_requirements[ $key ] ) ) {

						foreach ( $this->plugins_requirements[ $key ] as $plugin_name => $required_value ) {

							switch ( $key ) {
								case 'wp_cron_enabled':
								case 'mbstring_enabled':
								case 'simplexml_enabled':
								case 'gd_enabled':
								case 'iconv_enabled':
								case 'url_fopen_enabled':
								case 'opcache_enabled':
									if ( ! $value ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors ++;
									}
									break;

								case 'wp_memory_limit':
									$required_memory = $this->memory_size_to_num( $required_value );

									if ( $required_memory > $value ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors ++;

									} elseif ( $this->recommended_memory > $value && $value > $required_value ) {
										$check_results[ $key ]['warnings'] = 'yes';
									}
									break;

								default:
									if ( 'imagick_version' === $key ) {
										if ( ! version_compare( $value, $required_value, '>=' ) ) {
											$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
											$errors ++;
										}
									} else {
										if ( 'n/a' !== $value ) {
											if ( ! version_compare( $value, $required_value, '>=' ) ) {
												$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
												$errors ++;
											}
										} else {
											if ( 'min_wc_version' !== $key ) {
												$check_results[ $key ]['warnings'][ $plugin_name ] = $required_value;
											}
										}
									}
							}
						}
					}
				}

				update_option(
					'yith_system_info',
					array(
						'system_info' => $check_results,
						'errors'      => $errors > 0,
					)
				);

			}

		}

		/**
		 * Handle plugin requirements
		 *
		 * @param string $plugin_name  The name of the plugin.
		 * @param array  $requirements Array of plugin requirements.
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Alberto Ruggiero
		 */
		public function add_requirements( $plugin_name, $requirements ) {

			$allowed_requirements = array_keys( $this->requirement_labels );

			foreach ( $requirements as $requirement => $value ) {

				if ( in_array( $requirement, $allowed_requirements, true ) ) {
					$this->plugins_requirements[ $requirement ][ $plugin_name ] = $value;
				}
			}

		}

		/**
		 * Manages notice dismissing
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function enqueue_scripts() {
			$script_path = defined( 'YIT_CORE_PLUGIN_URL' ) ? YIT_CORE_PLUGIN_URL : get_template_directory_uri() . '/core/plugin-fw';
			wp_register_script( 'yith-system-info', yit_load_js_file( $script_path . '/assets/js/yith-system-info.js' ), array( 'jquery' ), '1.0.0', true );

			if ( isset( $_GET['page'] ) && 'yith_system_info' === $_GET['page'] ) { //phpcs:ignore
				wp_enqueue_style( 'yit-plugin-style' );
				wp_enqueue_style( 'yith-plugin-fw-fields' );
				wp_enqueue_script( 'yith-system-info' );

				$params = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				);

				wp_localize_script( 'yith-system-info', 'yith_sysinfo', $params );

			}

		}

		/**
		 * Show system notice
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function activate_system_notice() {

			$system_info = get_option( 'yith_system_info', '' );

			if ( ( isset( $_GET['page'] ) && $_GET['page'] === $this->page ) || ( ! empty( $_COOKIE['hide_yith_system_alert'] ) && 'yes' === $_COOKIE['hide_yith_system_alert'] ) || ( '' === $system_info ) || ( '' !== $system_info && false === $system_info['errors'] ) ) { //phpcs:ignore
				return;
			}

			$show_notice = true;

			if ( true === $show_notice ) {
				wp_enqueue_script( 'yith-system-info' );
				?>
				<div id="yith-system-alert" class="notice notice-error is-dismissible" style="position: relative;">
					<p>
						<span class="yith-logo"><img src="<?php echo esc_attr( yith_plugin_fw_get_default_logo() ); ?>" /></span>
						<b>
							<?php esc_html_e( 'Warning!', 'yith-plugin-fw' ); ?>
						</b><br />
						<?php
						/* translators: %1$s open link tag, %2$s open link tag*/
						echo sprintf( esc_html__( 'The system check has detected some compatibility issues on your installation.%1$sClick here%2$s to know more', 'yith-plugin-fw' ), '<a href="' . esc_url( add_query_arg( array( 'page' => $this->page ), admin_url( 'admin.php' ) ) ) . '">', '</a>' );
						?>
					</p>
					<span class="notice-dismiss"></span>

				</div>
				<?php
			}
		}

		/**
		 * Get system information
		 *
		 * @return  array
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function get_system_info() {
			$tls             = $this->get_tls_version();
			$imagick_version = 'n/a';

			// Get PHP version.
			preg_match( '#^\d+(\.\d+)*#', PHP_VERSION, $match );
			$php_version = $match[0];

			// WP memory limit.
			$wp_memory_limit = $this->memory_size_to_num( WP_MEMORY_LIMIT );
			if ( function_exists( 'memory_get_usage' ) ) {
				$wp_memory_limit = max( $wp_memory_limit, $this->memory_size_to_num( @ini_get( 'memory_limit' ) ) ); //phpcs:ignore
			}

			if ( class_exists( 'Imagick' ) && is_callable( array( 'Imagick', 'getVersion' ) ) ) {
				preg_match( '/([0-9]+\.[0-9]+\.[0-9]+)/', Imagick::getVersion()['versionString'], $imatch );
				$imagick_version = $imatch[0];
			}

			return apply_filters(
				'yith_system_additional_check',
				array(
					'min_wp_version'    => get_bloginfo( 'version' ),
					'min_wc_version'    => function_exists( 'WC' ) ? WC()->version : 'n/a',
					'wp_memory_limit'   => $wp_memory_limit,
					'min_php_version'   => $php_version,
					'min_tls_version'   => $tls,
					'imagick_version'   => $imagick_version,
					'wp_cron_enabled'   => ( ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) || apply_filters( 'yith_system_status_server_cron', false ) ),
					'mbstring_enabled'  => extension_loaded( 'mbstring' ),
					'simplexml_enabled' => extension_loaded( 'simplexml' ),
					'gd_enabled'        => extension_loaded( 'gd' ) && function_exists( 'gd_info' ),
					'iconv_enabled'     => extension_loaded( 'iconv' ),
					'opcache_enabled'   => ini_get( 'opcache.save_comments' ),
					'url_fopen_enabled' => ini_get( 'allow_url_fopen' ),
				)
			);

		}

		/**
		 * Get log file
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function create_log_file() {
			try {

				global $wp_filesystem;

				if ( empty( $wp_filesystem ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}

				$download_file  = false;
				$file_content   = '';
				$requested_file = $_POST['file']; //phpcs:ignore

				switch ( $requested_file ) {
					case 'error_log':
						$file_content = $wp_filesystem->get_contents( ABSPATH . 'error_log' );
						break;
					case 'debug.log':
						$file_content = $wp_filesystem->get_contents( WP_CONTENT_DIR . '/debug.log' );
						break;
				}

				if ( '' !== $file_content ) {
					$file          = wp_upload_dir()['basedir'] . '/' . $requested_file . '.txt';
					$download_file = wp_upload_dir()['baseurl'] . '/' . $requested_file . '.txt';
					$wp_filesystem->put_contents( $file, $file_content );
				}

				wp_send_json( array( 'file' => $download_file ) );
			} catch ( Exception $e ) {
				wp_send_json( array( 'file' => false ) );
			}
		}

		/**
		 * Convert size into number
		 *
		 * @param string $memory_size Memory size to convert.
		 *
		 * @return  integer
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function memory_size_to_num( $memory_size ) {
			$unit = strtoupper( substr( $memory_size, - 1 ) );
			$size = substr( $memory_size, 0, - 1 );

			$multiplier = array(
				'P' => 5,
				'T' => 4,
				'G' => 3,
				'M' => 2,
				'K' => 1,
			);

			if ( isset( $multiplier[ $unit ] ) ) {
				for ( $i = 1; $i <= $multiplier[ $unit ]; $i ++ ) {
					$size *= 1024;
				}
			}

			return $size;
		}

		/**
		 * Format requirement value
		 *
		 * @param string $key   Requirement Key.
		 * @param mixed  $value Requirement value.
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function format_requirement_value( $key, $value ) {

			if ( strpos( $key, '_enabled' ) !== false ) {
				echo esc_attr( $value ) ? esc_html__( 'Enabled', 'yith-plugin-fw' ) : esc_html__( 'Disabled', 'yith-plugin-fw' );
			} elseif ( 'wp_memory_limit' === $key ) {
				echo esc_html( size_format( $value ) );
			} else {
				if ( 'n/a' === $value ) {
					echo esc_html__( 'N/A', 'yith-plugin-fw' );
				} else {
					echo esc_attr( $value );
				}
			}

		}

		/**
		 * Print error messages
		 *
		 * @param string $key   Requirement key.
		 * @param array  $item  Requirement item.
		 * @param string $label Requirement label.
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function print_error_messages( $key, $item, $label ) {
			?>
			<ul>
				<?php foreach ( $item['errors'] as $plugin => $requirement ) : ?>
					<li>
						<?php
						if ( strpos( $key, '_enabled' ) !== false ) {
							/* translators: %1$s plugin name, %2$s requirement name */
							echo sprintf( esc_html__( '%1$s needs %2$s enabled', 'yith-plugin-fw' ), '<b>' . esc_attr( $plugin ) . '</b>', '<b>' . esc_attr( $label ) . '</b>' );
						} elseif ( 'wp_memory_limit' === $key ) {
							/* translators: %1$s plugin name, %2$s required memory amount */
							echo sprintf( esc_html__( '%1$s needs at least %2$s of available memory', 'yith-plugin-fw' ), '<b>' . esc_attr( $plugin ) . '</b>', '<span class="error">' . esc_html( size_format( $this->memory_size_to_num( $requirement ) ) ) . '</span>' );
						} else {
							/* translators: %1$s plugin name, %2$s version number */
							echo sprintf( esc_html__( '%1$s needs at least %2$s version', 'yith-plugin-fw' ), '<b>' . esc_attr( $plugin ) . '</b>', '<span class="error">' . esc_attr( $requirement ) . '</span>' );
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
		}

		/**
		 * Print solution suggestions
		 *
		 * @param string $key   Requirement key.
		 * @param array  $item  Requirement item.
		 * @param string $label Requirement label.
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function print_solution_suggestion( $key, $item, $label ) {
			switch ( $key ) {
				case 'min_wp_version':
				case 'min_wc_version':
					esc_html_e( 'Update it to the latest version in order to benefit of all new features and security updates.', 'yith-plugin-fw' );
					break;
				case 'min_php_version':
				case 'min_tls_version':
					esc_html_e( 'Contact your hosting company in order to update it.', 'yith-plugin-fw' );
					break;
				case 'imagick_version':
					if ( 'n/a' === $item['value'] ) {
						esc_html_e( 'Contact your hosting company in order to install it.', 'yith-plugin-fw' );
					} else {
						esc_html_e( 'Contact your hosting company in order to update it.', 'yith-plugin-fw' );
					}
					break;
				case 'wp_cron_enabled':
					/* translators: %1$s code, %2$s file name */
					echo sprintf( esc_html__( 'Remove %1$s from %2$s file', 'yith-plugin-fw' ), '<code>define( \'DISABLE_WP_CRON\', true );</code>', '<b>wp-config.php</b>' );
					break;
				case 'mbstring_enabled':
				case 'simplexml_enabled':
				case 'gd_enabled':
				case 'iconv_enabled':
				case 'opcache_enabled':
				case 'url_fopen_enabled':
					esc_html_e( 'Contact your hosting company in order to enable it.', 'yith-plugin-fw' );
					break;
				case 'wp_memory_limit':
					/* translators: %1$s opening link tag, %2$s closing link tag */
					echo sprintf( esc_html__( 'Read more %1$shere%2$s or contact your hosting company in order to increase it.', 'yith-plugin-fw' ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>' );
					break;
				default:
					echo esc_attr( apply_filters( 'yith_system_generic_message', '', $key, $item, $label ) );
			}
		}

		/**
		 * Print warning messages
		 *
		 * @param string $key Requirement Key.
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function print_warning_messages( $key ) {
			switch ( $key ) {
				case 'wp_memory_limit':
					/* translators: %s recommended memory amount */
					echo sprintf( esc_html__( 'For optimal functioning of our plugins, we suggest setting at least %s of available memory', 'yith-plugin-fw' ), '<span class="warning">' . esc_html( size_format( $this->recommended_memory ) ) . '</span>' );
					echo '<br/>';
					/* translators: %1$s opening link tag, %2$s closing link tag */
					echo sprintf( esc_html__( 'Read more %1$shere%2$s or contact your hosting company in order to increase it.', 'yith-plugin-fw' ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>' );
					break;
				case 'min_tls_version':
					if ( ! function_exists( 'curl_init' ) ) {
						/* translators: %1$s TLS label, %2$s cURL label */
						echo sprintf( esc_html__( 'The system check cannot determine which %1$s version is installed because %2$s module is disabled. Ask your hosting company to enable it.', 'yith-plugin-fw' ), '<b>TLS</b>', '<b>cURL</b>' );
					} else {
						/* translators: %1$s TLS label */
						echo sprintf( esc_html__( 'The system check cannot determine which %1$s version is installed due to a connection issue between your site and our server.', 'yith-plugin-fw' ), '<b>TLS</b>' );
					}
					break;
			}
		}

		/**
		 * Retrieve the TLS Version
		 *
		 * @return string
		 * @since 3.5
		 */
		public function get_tls_version() {
			$tls = get_transient( 'yith-plugin-fw-system-status-tls-version' );

			if ( ! $tls && apply_filters( 'yith_system_status_check_ssl', true ) ) {
				$services = array(
					array(
						'url'              => 'https://www.howsmyssl.com/a/check',
						'string_to_remove' => 'TLS ',
						'prop'             => 'tls_version',
					),
					array(
						'url'              => 'https://ttl-version.yithemes.workers.dev/',
						'string_to_remove' => 'TLSv',
						'prop'             => 'tlsVersion',
					),
				);
				$params   = array(
					'sslverify' => false,
					'timeout'   => 60,
					'headers'   => array( 'Content-Type' => 'application/json' ),
				);

				foreach ( $services as $service ) {
					$url              = $service['url'];
					$string_to_remove = $service['string_to_remove'];
					$prop             = $service['prop'];

					$response = wp_remote_get( $url, $params );

					if ( ! is_wp_error( $response ) && 200 === absint( $response['response']['code'] ) && 'OK' === $response['response']['message'] ) {
						$body    = json_decode( $response['body'] );
						$version = $body && is_object( $body ) && property_exists( $body, $prop ) ? $body->{$prop} : false;
						if ( $version ) {
							$tls = str_replace( $string_to_remove, '', $version );
							break;
						}
					}
				}
				$tls = ! ! $tls ? $tls : 'n/a';

				set_transient( 'yith-plugin-fw-system-status-tls-version', $tls, 300 );
			}

			return ! ! $tls ? $tls : 'n/a';
		}

		/**
		 * Retrieve the output IP Address.
		 *
		 * @return string
		 * @since 3.5
		 */
		public function get_output_ip() {
			$ip = get_transient( 'yith-plugin-fw-system-status-output-ip' );

			if ( ! $ip && apply_filters( 'yith_system_status_check_ip', true ) ) {
				$url    = 'https://ifconfig.co/ip';
				$params = array(
					'sslverify' => false,
					'timeout'   => 60,
				);

				$response = wp_remote_get( $url, $params );

				if ( ! is_wp_error( $response ) && 200 === absint( $response['response']['code'] ) && 'OK' === $response['response']['message'] ) {
					$body = $response['body'];

					// Check for IPv4.
					preg_match( '/((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])/', $body, $matches );
					// Check for IPv6.
					if ( empty( $matches ) ) {
						preg_match( '/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/', $body, $matches );
					}

					$ip = ! empty( $matches ) ? $matches[0] : 'n/a';
				}

				$ip = ! ! $ip ? $ip : 'n/a';

				set_transient( 'yith-plugin-fw-system-status-output-ip', $ip, 300 );
			}

			return ! ! $ip ? $ip : 'n/a';
		}

		/**
		 * Retrieve plugin-fw info, such as version and loaded-by.
		 *
		 * @return array
		 */
		public function get_plugin_fw_info() {
			$version        = yith_plugin_fw_get_version();
			$loaded_by      = basename( dirname( YIT_CORE_PLUGIN_PATH ) );
			$loaded_by_init = trailingslashit( dirname( YIT_CORE_PLUGIN_PATH ) ) . 'init.php';
			if ( file_exists( $loaded_by_init ) ) {
				$plugin_data = get_plugin_data( $loaded_by_init );
				$loaded_by   = $plugin_data['Name'] ?? $loaded_by;
			}

			return compact( 'version', 'loaded_by' );
		}

		/**
		 * Retrieve database info, such as MySQL version and database size.
		 *
		 * @return array
		 */
		public function get_database_info() {

			global $wpdb;

			$database_version = $wpdb->get_row( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				'SELECT
						@@GLOBAL.version_comment AS string,
						@@GLOBAL.version AS number',
				ARRAY_A
			);

			$tables        = array();
			$database_size = array();

			// It is not possible to get the database name from some classes that replace wpdb (e.g., HyperDB)
			// and that is why this if condition is needed.
			if ( defined( 'DB_NAME' ) ) {
				$database_table_information = $wpdb->get_results( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT
					    table_name AS 'name',
						engine AS 'engine',
					    round( ( data_length / 1024 / 1024 ), 2 ) 'data',
					    round( ( index_length / 1024 / 1024 ), 2 ) 'index',
       					round( ( data_free / 1024 / 1024 ), 2 ) 'free'
					FROM information_schema.TABLES
					WHERE table_schema = %s
					ORDER BY name ASC;",
						DB_NAME
					)
				);

				$database_size = array(
					'data'  => 0,
					'index' => 0,
					'free'  => 0,
				);

				$site_tables_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
				$global_tables      = $wpdb->tables( 'global', true );
				foreach ( $database_table_information as $table ) {
					// Only include tables matching the prefix of the current site, this is to prevent displaying all tables on a MS install not relating to the current.
					if ( is_multisite() && 0 !== strpos( $table->name, $site_tables_prefix ) && ! in_array( $table->name, $global_tables, true ) ) {
						continue;
					}

					$tables[ $table->name ] = array(
						'data'   => $table->data,
						'index'  => $table->index,
						'free'   => $table->free,
						'engine' => $table->engine,
					);

					$database_size['data']  += $table->data;
					$database_size['index'] += $table->index;
					$database_size['free']  += $table->free;
				}
			}

			return apply_filters(
				'yith_database_info',
				array(
					'mysql_version'        => $database_version['number'],
					'mysql_version_string' => $database_version['string'],
					'database_tables'      => $tables,
					'database_size'        => $database_size,
				)
			);
		}

	}
}

if ( ! function_exists( 'YITH_System_Status' ) ) {
	/**
	 * Single instance of YITH_System_Status
	 *
	 * @return YITH_System_Status
	 * @since  1.0
	 * @author Alberto Ruggiero
	 */
	function YITH_System_Status() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return YITH_System_Status::instance();
	}
}

YITH_System_Status();
