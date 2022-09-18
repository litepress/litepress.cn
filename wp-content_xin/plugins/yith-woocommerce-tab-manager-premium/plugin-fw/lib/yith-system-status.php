<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_System_Status' ) ) {
	/**
	 * YITH System Status Panel
	 *
	 * Setting Page to Manage Plugins
	 *
	 * @class      YITH_System_Status
	 * @package    YITH
	 * @since      1.0
	 * @author     Alberto Ruggiero
	 */
	class YITH_System_Status {

		/**
		 * @var array The settings require to add the submenu page "System Status"
		 */
		protected $_settings = array();

		/**
		 * @var string the page slug
		 */
		protected $_page = 'yith_system_info';

		/**
		 * @var array plugins requirements list
		 */
		protected $_plugins_requirements = array();

		/**
		 * @var array requirements labels
		 */
		protected $_requirement_labels = array();

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_System_Status
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main plugin Instance
		 *
		 * @since  1.0.0
		 * @return YITH_System_Status
		 * @author Alberto Ruggiero
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @return void
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

			$system_info  = get_option( 'yith_system_info' );
			$error_notice = ( $system_info['errors'] === true ? ' <span class="yith-system-info-menu update-plugins">!</span>' : '' );

			$this->_settings = array(
				'parent_page' => 'yith_plugin_panel',
				'page_title'  => __( 'System Status', 'yith-plugin-fw' ),
				'menu_title'  => __( 'System Status', 'yith-plugin-fw' ) . $error_notice,
				'capability'  => 'manage_options',
				'page'        => $this->_page,
			);

			$this->_requirement_labels = array(
				'min_wp_version'    => __( 'WordPress Version', 'yith-plugin-fw' ),
				'min_wc_version'    => __( 'WooCommerce Version', 'yith-plugin-fw' ),
				'wp_memory_limit'   => __( 'Available Memory', 'yith-plugin-fw' ),
				'min_php_version'   => __( 'PHP Version', 'yith-plugin-fw' ),
				'min_tls_version'   => __( 'TLS Version', 'yith-plugin-fw' ),
				'wp_cron_enabled'   => __( 'WordPress Cron', 'yith-plugin-fw' ),
				'simplexml_enabled' => __( 'SimpleXML', 'yith-plugin-fw' ),
				'mbstring_enabled'  => __( 'MultiByte String', 'yith-plugin-fw' ),
				'imagick_version'   => __( 'ImageMagick Version', 'yith-plugin-fw' ),
				'gd_enabled'        => __( 'GD Library', 'yith-plugin-fw' ),
				'iconv_enabled'     => __( 'Iconv Module', 'yith-plugin-fw' ),
				'opcache_enabled'   => __( 'OPCache Save Comments', 'yith-plugin-fw' ),
				'url_fopen_enabled' => __( 'URL FOpen', 'yith-plugin-fw' ),
			);

			add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 99 );
			add_action( 'admin_init', array( $this, 'check_system_status' ) );
			add_action( 'admin_notices', array( $this, 'activate_system_notice' ), 15 );
			add_action( 'admin_enqueue_scripts', array( $this, 'dismissable_notice' ), 20 );


		}

		/**
		 * Add "System Information" submenu page under YITH Plugins
		 *
		 * @since  1.0.0
		 * @return void
		 * @author Alberto Ruggiero
		 */
		public function add_submenu_page() {
			add_submenu_page(
				$this->_settings['parent_page'],
				$this->_settings['page_title'],
				$this->_settings['menu_title'],
				$this->_settings['capability'],
				$this->_settings['page'],
				array( $this, 'show_information_panel' )
			);
		}

		/**
		 * Add "System Information" page template under YITH Plugins
		 *
		 * @since  1.0.0
		 * @return void
		 * @author Alberto Ruggiero
		 */
		public function show_information_panel() {

			$path   = defined( 'YIT_CORE_PLUGIN_PATH' ) ? YIT_CORE_PLUGIN_PATH : get_template_directory() . '/core/plugin-fw/';
			$labels = $this->_requirement_labels;

			require_once( $path . '/templates/sysinfo/system-information-panel.php' );

		}

		/**
		 * Perform system status check
		 *
		 * @since  1.0.0
		 * @return void
		 * @author Alberto Ruggiero
		 */
		public function check_system_status() {


			if ( '' == get_option( 'yith_system_info' ) || ( isset( $_GET['page'] ) && $_GET['page'] == $this->_page ) ) {

				$this->add_requirements( __( 'YITH Plugins', 'yith-plugin-fw' ), array( 'min_wp_version' => '4.9', 'min_wc_version' => '3.4', 'min_php_version' => '5.6.20' ) );
				$this->add_requirements( __( 'WooCommerce', 'yith-plugin-fw' ), array( 'wp_memory_limit' => '64M' ) );

				$system_info   = $this->get_system_info();
				$check_results = array();
				$errors        = false;

				foreach ( $system_info as $key => $value ) {
					$check_results[ $key ] = array( 'value' => $value );

					if ( isset( $this->_plugins_requirements[ $key ] ) ) {

						foreach ( $this->_plugins_requirements[ $key ] as $plugin_name => $required_value ) {

							switch ( $key ) {
								case 'wp_cron_enabled'  :
								case 'mbstring_enabled' :
								case 'simplexml_enabled':
								case 'gd_enabled':
								case 'iconv_enabled':
								case 'url_fopen_enabled':
								case 'opcache_enabled'  :

									if ( ! $value ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors                                          = true;
									}
									break;

								case 'wp_memory_limit'  :
									$required_memory = $this->memory_size_to_num( $required_value );

									if ( $required_memory > $value ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors                                          = true;
									}
									break;

								default:
									if ( ! version_compare( $value, $required_value, '>=' ) && $value != 'n/a' ) {
										$check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
										$errors                                          = true;
									}

							}

						}

					}

				}

				update_option( 'yith_system_info', array( 'system_info' => $check_results, 'errors' => $errors ) );

			}

		}

		/**
		 * Handle plugin requirements
		 *
		 * @since  1.0.0
		 *
		 * @param $plugin_name  string
		 * @param $requirements array
		 *
		 * @return void
		 * @author Alberto Ruggiero
		 */
		public function add_requirements( $plugin_name, $requirements ) {

			$allowed_requirements = array_keys( $this->_requirement_labels );

			foreach ( $requirements as $requirement => $value ) {

				if ( in_array( $requirement, $allowed_requirements ) ) {
					$this->_plugins_requirements[ $requirement ][ $plugin_name ] = $value;
				}
			}

		}

		/**
		 * Manages notice dismissing
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function dismissable_notice() {
			$script_path = defined( 'YIT_CORE_PLUGIN_URL' ) ? YIT_CORE_PLUGIN_URL : get_template_directory_uri() . '/core/plugin-fw';
			$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'yith-system-info', $script_path . '/assets/js/yith-system-info' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		}

		/**
		 * Show system notice
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function activate_system_notice() {

			$system_info = get_option( 'yith_system_info', '' );

			if ( ( isset( $_GET['page'] ) && $_GET['page'] == $this->_page ) || ( ! empty( $_COOKIE['hide_yith_system_alert'] ) && 'yes' == $_COOKIE['hide_yith_system_alert'] ) || ( $system_info == '' ) || ( $system_info != '' && $system_info['errors'] === false ) ) {
				return;
			}

			$show_notice = true;

			if ( true === $show_notice ) :
				wp_enqueue_script( 'yith-system-info' );
				?>
                <div id="yith-system-alert" class="notice notice-error is-dismissible" style="position: relative;">
                    <p>
                        <span class="yith-logo"><img src="<?php echo yith_plugin_fw_get_default_logo() ?>" /></span>
                        <b><?php echo __( 'Warning!', 'yith-plugin-fw' ) ?></b><br />
						<?php echo sprintf( __( 'The system check has detected some compatibility issues on your installation. %sClick here%s to know more', 'yith-plugin-fw' ), '<a href="' . esc_url( add_query_arg( array( 'page' => $this->_page ), admin_url( 'admin.php' ) ) ) . '">', '</a>' ) ?>
                    </p>
                    <span class="notice-dismiss"></span>

                </div>
			<?php endif;
		}

		/**
		 * Get system information
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_system_info() {

			$tls = $imagick_version = 'n/a';

			if ( function_exists( 'curl_init' ) && apply_filters( 'yith_system_status_check_ssl', true ) ) {
				//Get TLS version
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, 'https://www.howsmyssl.com/a/check' );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				$data = curl_exec( $ch );
				curl_close( $ch );
				$json = json_decode( $data );
				$tls  = $json != null ? str_replace( 'TLS ', '', $json->tls_version ) : '';
			}

			//Get PHP version
			preg_match( "#^\d+(\.\d+)*#", PHP_VERSION, $match );
			$php_version = $match[0];

			// WP memory limit.
			$wp_memory_limit = $this->memory_size_to_num( WP_MEMORY_LIMIT );
			if ( function_exists( 'memory_get_usage' ) ) {
				$wp_memory_limit = max( $wp_memory_limit, $this->memory_size_to_num( @ini_get( 'memory_limit' ) ) );
			}

			if ( class_exists( 'Imagick' ) && is_callable( array( 'Imagick', 'getVersion' ) ) ) {
				preg_match( "/([0-9]+\.[0-9]+\.[0-9]+)/", Imagick::getVersion()['versionString'], $imatch );
				$imagick_version = $imatch[0];
			}

			return apply_filters( 'yith_system_additional_check', array(
				'min_wp_version'    => get_bloginfo( 'version' ),
				'min_wc_version'    => function_exists( 'WC' ) ? WC()->version : 'n/a',
				'wp_memory_limit'   => $wp_memory_limit,
				'min_php_version'   => $php_version,
				'min_tls_version'   => $tls,
				'imagick_version'   => $imagick_version,
				'wp_cron_enabled'   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
				'mbstring_enabled'  => extension_loaded( 'mbstring' ),
				'simplexml_enabled' => extension_loaded( 'simplexml' ),
				'gd_enabled'        => extension_loaded( 'gd' ) && function_exists( 'gd_info' ),
				'iconv_enabled'     => extension_loaded( 'iconv' ),
				'opcache_enabled'   => ini_get( 'opcache.save_comments' ),
				'url_fopen_enabled' => ini_get( 'allow_url_fopen' ),
			) );

		}

		/**
		 * Convert site into number
		 *
		 * @since   1.0.0
		 *
		 * @param   $memory_size string
		 *
		 * @return  integer
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

	}
}

/**
 * Main instance of plugin
 *
 * @return YITH_System_Status object
 * @since  1.0
 * @author Alberto Ruggiero
 */
if ( ! function_exists( 'YITH_System_Status' ) ) {
	function YITH_System_Status() {
		return YITH_System_Status::instance();
	}
}

YITH_System_Status();
