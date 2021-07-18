<?php

/**
 * WooCommerce API Manager Admin Settings Class
 *
 * @since       1.3
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Admin/Admin Settings
 */

defined( 'ABSPATH' ) || exit;

class WC_AM_Settings_Admin {

	/**
	 * The WooCommerce settings tab name
	 *
	 * @since 1.3
	 */
	public $tab_name = 'api_manager';

	/**
	 * The prefix for API Manager settings
	 *
	 * @since 1.0
	 */
	public $option_prefix = 'woocommerce_api_manager';

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Settings_Admin
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_api_manager_settings_tab' ), 60 );
		add_action( 'woocommerce_settings_' . $this->tab_name, array( $this, 'api_manager_settings_page' ) );
		add_action( 'woocommerce_update_options_' . $this->tab_name, array( $this, 'update_api_manager_settings' ) );
		// Custom Amazon S3 Secret Access Key form field that is encrypted and decrypted
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_api_manager_amazon_s3_secret_access_key', array( $this, 'encrypt_secret_key' ), 10, 2 ); // 2 out of 3 used
		add_action( 'woocommerce_admin_field_wc_am_s3_secret_key', array( $this, 'secret_key_field' ) );
	}

	/**
	 * Add the API Manager settings tab to the WooCommerce settings tabs array.
	 *
	 * @since 1.3
	 *
	 * @param array $settings_tabs Array of WooCommerce setting tabs and their labels, excluding the API Manager tab.
	 *
	 * @return array $settings_tabs Array of WooCommerce setting tabs and their labels, including the API Manager tab.
	 */
	public function add_api_manager_settings_tab( $settings_tabs ) {
		$settings_tabs[ $this->tab_name ] = __( 'API Manager', 'woocommerce-api-manager' );

		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @since 1.3
	 * @uses  $this->get_settings()
	 * @uses  woocommerce_admin_fields()
	 */
	public function api_manager_settings_page() {
		global $current_section;

		woocommerce_admin_fields( $this->get_settings( $current_section ) );
	}

	/**
	 * Get all the settings for the API Manager extension in the format required by the @see woocommerce_admin_fields() function.
	 *
	 * @since 1.0
	 *
	 * @param string $current_section
	 *
	 * @return array Array of settings in the format required by the @see woocommerce_admin_fields() function.
	 */
	public function get_settings( $current_section ) {
		$aws_s3_regions  = array(
			'ap-east-1'      => 'ap-east-1',
			'ap-northeast-1' => 'ap-northeast-1',
			'ap-northeast-2' => 'ap-northeast-2',
			'ap-northeast-3' => 'ap-northeast-3',
			'ap-south-1'     => 'ap-south-1',
			'ap-southeast-1' => 'ap-southeast-1',
			'ap-southeast-2' => 'ap-southeast-2',
			'ca-central-1'   => 'ca-central-1',
			'cn-north-1'     => 'cn-north-1',
			'cn-northwest-1' => 'cn-northwest-1',
			'eu-central-1'   => 'eu-central-1',
			'eu-north-1'     => 'eu-north-1',
			'eu-west-1'      => 'eu-west-1',
			'eu-west-2'      => 'eu-west-2',
			'eu-west-3'      => 'eu-west-3',
			'sa-east-1'      => 'sa-east-1',
			'us-east-1'      => 'us-east-1',
			'us-east-2'      => 'us-east-2',
			'us-west-1'      => 'us-west-1',
			'us-west-2'      => 'us-west-2'
		);
		$current_section = 'api_manager';

		$amazon_s3_title = array(
			'name' => __( 'Amazon S3', 'woocommerce-api-manager' ),
			'type' => 'title',
			'desc' => sprintf( __( 'For better security add the following to wp-config.php, so your AWS Keys are not stored in the database:%s%s%s%s%s%s%s%s', 'woocommerce-api-manager' ), '<br>', '<code>', "define('WC_AM_AWS3_ACCESS_KEY_ID', 'your_access_key_here');", '</code>', '<br>', '<code>', "define('WC_AM_AWS3_SECRET_ACCESS_KEY', 'your_secret_key_here');", '</code>' ),
			'id'   => $this->option_prefix . '_amazon_s3'
		);

		$access_key_id = array(
			'name'     => __( 'Access Key ID', 'woocommerce-api-manager' ),
			'desc'     => __( 'The Amazon Web Services Access Key ID.', 'woocommerce-api-manager' ),
			'tip'      => '',
			'id'       => $this->option_prefix . '_amazon_s3_access_key_id',
			'css'      => 'min-width:250px;',
			'default'  => '',
			'type'     => 'text',
			'desc_tip' => false
		);

		$secret_access_key = array(
			'name'     => __( 'Secret Access Key', 'woocommerce-api-manager' ),
			'desc'     => __( 'The Amazon Web Services Secret Access Key is securely encrypted in the database.', 'woocommerce-api-manager' ),
			'tip'      => '',
			'id'       => $this->option_prefix . '_amazon_s3_secret_access_key',
			'css'      => 'min-width:250px;',
			'default'  => '',
			'type'     => 'wc_am_s3_secret_key',
			'desc_tip' => __( 'The Amazon Web Services Secret Access Key.', 'woocommerce-api-manager' )
		);

		if ( defined( 'WC_AM_AWS3_ACCESS_KEY_ID' ) && defined( 'WC_AM_AWS3_SECRET_ACCESS_KEY' ) ) {
			$amazon_s3_title = array(
				'name' => __( 'Amazon S3', 'woocommerce-api-manager' ),
				'type' => 'title',
				'desc' => __( 'Values defined in wp-config.php.', 'woocommerce-api-manager' ),
				'id'   => $this->option_prefix . '_amazon_s3'
			);
		}

		if ( defined( 'WC_AM_AWS3_ACCESS_KEY_ID' ) ) {
			$access_key_id = array();
		}
		if ( defined( 'WC_AM_AWS3_SECRET_ACCESS_KEY' ) ) {
			$secret_access_key = array();
		}

		if ( $current_section ) {
			return apply_filters( 'wc_api_manager_settings', array(
				$amazon_s3_title,

				$access_key_id,

				$secret_access_key,

				array(
					'name'     => __( 'Amazon S3 Region', 'woocommerce-api-manager' ),
					'desc'     => __( 'The Amazon S3 Region where files are stored.', 'woocommerce-api-manager' ),
					'id'       => $this->option_prefix . '_aws_s3_region',
					'default'  => 1,
					'default'  => 'us-east-1',
					'type'     => 'select',
					'options'  => $aws_s3_regions,
					'desc_tip' => __( 'The region is required.', 'woocommerce-api-manager' )
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_amazon_s3_sec' ),

				array(
					'name' => __( 'Download Links', 'woocommerce-api-manager' ),
					'type' => 'title',
					'desc' => '',
					'id'   => $this->option_prefix . '_download_links'
				),

				array(
					'name'     => __( 'URL Expire Time', 'woocommerce-api-manager' ),
					'desc'     => __( 'Expiration time in days, for Amazon S3 and local server WooCommerce URLs.', 'woocommerce-api-manager' ),
					'id'       => $this->option_prefix . '_url_expire',
					'default'  => 1,
					'type'     => 'select',
					'options'  => apply_filters( 'wc_api_manager_url_expire_time', array_combine( range( 1, 7, 1 ), range( 1, 7, 1 ) ) ),
					'desc_tip' => __( 'Sets the time limit in days before a secure URL will expire. If a download begins before the expiration time limit is reached, the download will continue until complete.', 'woocommerce-api-manager' )
				),

				array(
					'name'     => __( 'Save to Dropbox App Key', 'woocommerce-api-manager' ),
					'desc'     => sprintf( __( 'This creates a Save to Dropbox link in the My Account > My API Downloads section. Create an App Key %shere%s.', 'woocommerce-api-manager' ), '<a href="' . esc_url( 'https://www.dropbox.com/developers/apps/create' ) . '" target="blank">', '</a>' ),
					'tip'      => '',
					'id'       => $this->option_prefix . '_dropbox_dropins_saver',
					'css'      => 'min-width:250px;',
					'default'  => '',
					'type'     => 'text',
					'desc_tip' => false
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_download_links_sec' ),

				array(
					'name' => __( 'API Keys', 'woocommerce-api-manager' ),
					'type' => 'title',
					'desc' => '',
					'id'   => $this->option_prefix . '_api_keys'
				),

				array(
					'name'            => __( 'Product Order API Keys', 'woocommerce-api-manager' ),
					'desc'            => __( 'Hide Product Order API Keys on My Account > API Keys tab screen. Hide if customers should only use Master API Key.', 'woocommerce-api-manager' ),
					'id'              => $this->option_prefix . '_hide_product_order_api_keys',
					'default'         => 'no',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option'
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_api_doc_tabs' ),

				array(
					'name' => __( 'API Doc Tabs', 'woocommerce-api-manager' ),
					'desc' => sprintf( __( 'Choose which tabs will display on the WordPress plugin information screen. Can also be used for non-WordPress software. A changelog is required. %sScreenshot example%s.', 'woocommerce-api-manager' ), '<a href="' . esc_url( 'https://docs.woocommerce.com/wp-content/uploads/2013/09/api-manager-view-version-details-2.png' ) . '" target="blank">', '</a>' ),
					'type' => 'title',
					'id'   => $this->option_prefix . '_apidoctabs'
				),

				array(
					'name'            => __( 'Description', 'woocommerce-api-manager' ),
					'id'              => $this->option_prefix . '_description',
					'default'         => 'no',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option'
				),

				array(
					'name'            => __( 'Installation', 'woocommerce-api-manager' ),
					'id'              => $this->option_prefix . '_installation',
					'default'         => 'no',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option'
				),

				array(
					'name'            => __( 'FAQ', 'woocommerce-api-manager' ),
					'id'              => $this->option_prefix . '_faq',
					'default'         => 'no',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option'
				),

				array(
					'name'            => __( 'Screenshots', 'woocommerce-api-manager' ),
					'id'              => $this->option_prefix . '_screenshots',
					'default'         => 'no',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option'
				),

				array(
					'name'            => __( 'Other Notes', 'woocommerce-api-manager' ),
					'id'              => $this->option_prefix . '_other_notes',
					'default'         => 'no',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option'
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_api_response_sec' ),

				array(
					'name' => __( 'API Response', 'woocommerce-api-manager' ),
					'type' => 'title',
					'desc' => '',
					'id'   => $this->option_prefix . '_debug'
				),

				array(
					'name'     => __( 'Send API Resource Data', 'woocommerce-api-manager' ),
					'desc'     => __( '<br>Sending extended resource data in API responses is not required, and will slow down response time.<br>Recommended Off.', 'woocommerce-api-manager' ),
					'id'       => $this->option_prefix . '_api_response_data',
					'type'     => 'radio',
					'desc_tip' => '',
					'default'  => 'no',
					'options'  => array( 'yes' => 'On', 'no' => 'Off' )
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_api_debug_sec' ),

				array(
					'name' => __( 'Debug', 'woocommerce-api-manager' ),
					'type' => 'title',
					'desc' => sprintf( __( '%sPostman%s is recommended for API testing.', 'woocommerce-api-manager' ), '<a href="' . esc_url( 'https://www.postman.com/' ) . '" target="_blank">', '</a>' ),
					'id'   => $this->option_prefix . '_debug'
				),

				array(
					'name'     => __( 'API Request Log', 'woocommerce-api-manager' ),
					'desc'     => sprintf( __( '<br>Logs query events inside <code>%s</code><br> Log file size %s <a href="%s">View Log</a>', 'woocommerce-api-manager' ), basename( wc_get_log_file_path( 'wc-am-api-request-log' ) ), esc_attr( $this->human_readable_filesize( wc_get_log_file_path( 'wc-am-api-query-log' ) ) ), esc_url( self_admin_url() . 'admin.php?page=wc-status&tab=logs' ) ),
					'id'       => $this->option_prefix . '_api_debug_log',
					'type'     => 'radio',
					'desc_tip' => '',
					'default'  => 'no',
					'options'  => array( 'yes' => 'On', 'no' => 'Off' )
				),

				array(
					'name'     => __( 'API Error Log', 'woocommerce-api-manager' ),
					'desc'     => sprintf( __( '<br>Logs error events inside <code>%s</code><br> Log file size %s <a href="%s">View Log</a>', 'woocommerce-api-manager' ), basename( wc_get_log_file_path( 'wc-am-api-error-log' ) ), esc_attr( $this->human_readable_filesize( wc_get_log_file_path( 'wc-am-api-error-log' ) ) ), esc_url( self_admin_url() . 'admin.php?page=wc-status&tab=logs' ) ),
					'id'       => $this->option_prefix . '_api_error_log',
					'type'     => 'radio',
					'desc_tip' => '',
					'default'  => 'no',
					'options'  => array( 'yes' => 'On', 'no' => 'Off' )
				),

				array(
					'name'     => __( 'API Response Log', 'woocommerce-api-manager' ),
					'desc'     => sprintf( __( '<br>Logs response events inside <code>%s</code><br> Log file size %s <a href="%s">View Log</a>', 'woocommerce-api-manager' ), basename( wc_get_log_file_path( 'wc-am-api-response-log' ) ), esc_attr( $this->human_readable_filesize( wc_get_log_file_path( 'wc-am-api-response-log' ) ) ), esc_url( self_admin_url() . 'admin.php?page=wc-status&tab=logs' ) ),
					'id'       => $this->option_prefix . '_api_response_log',
					'type'     => 'radio',
					'desc_tip' => '',
					'default'  => 'no',
					'options'  => array( 'yes' => 'On', 'no' => 'Off' )
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_api_extensions' ),

				array(
					'name' => __( 'API Manager Extensions', 'woocommerce-api-manager' ),
					'desc' => sprintf( __( '%s%sIntegrate plugins and themes easily with %sWooCommerce API Manager PHP Library for Plugins and Themes%s.%s%sDisplay the Product API tab settings data automatically on the frontend product page with the %sWooCommerce API Manager Product Tabs%s extension is implemented.%s%s', 'woocommerce-api-manager' ), '<ul style="list-style-type:disc;padding-left:5em">','<li>', '<a href="' . esc_url( 'https://www.toddlahman.com/shop/woocommerce-api-manager-php-library-for-plugins-and-themes/' ) . '" target="blank">', '</a>', '</li>', '<li>', '<a href="' . esc_url( 'https://www.toddlahman.com/shop/woocommerce-api-manager-product-tabs/' ) . '" target="blank">', '</a>', '</li>', '</ul>' ),
					'type' => 'title',
					'id'   => $this->option_prefix . '_api_extensions_info'
				),

				array( 'type' => 'sectionend', 'id' => $this->option_prefix . '_api_extensions' ),

			) );
		}

		return array();
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @since 1.3
	 * @uses  $this->get_settings()
	 * @uses  woocommerce_update_options()
	 */
	public function update_api_manager_settings() {
		global $current_section;

		woocommerce_update_options( $this->get_settings( $current_section ) );
	}

	/**
	 * Updates and encrypts the Amazon S3 secret key.
	 *
	 * @since 2.0
	 *
	 * @param string $value
	 * @param array  $option
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function encrypt_secret_key( $value, $option ) {
		if ( ! empty( $option[ 'id' ] ) ) {
			$value = stripslashes( WC_AM_ENCRYPTION()->encrypt( $value ) );
		}

		return $value;
	}

	/**
	 * Displays the Amazon S3 secret key
	 *
	 * @since 1.3.2
	 *
	 * @param string $field encrypted secret key
	 *
	 * @throws \Exception
	 */
	public function secret_key_field( $field ) {
		if ( isset( $field[ 'id' ] ) && isset( $field[ 'name' ] ) ) :
			$field_val = get_option( $field[ 'id' ] );
			?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo wp_kses_post( $field[ 'id' ] ); ?>"><?php echo esc_attr( $field[ 'name' ] ); ?></label>
                </th>
                <td class="forminp forminp-password">
                    <input name="<?php echo esc_attr( $field[ 'id' ] ); ?>" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" type="password"
                           style="<?php echo esc_attr( isset( $field[ 'css' ] ) ? $field[ 'css' ] : '' ); ?>"
                           value="<?php echo esc_attr( WC_AM_ENCRYPTION()->decrypt( $field_val ) ); ?>"
                           class="<?php echo esc_attr( isset( $field[ 'class' ] ) ? $field[ 'class' ] : '' ); ?>">
                    <span class="description"><?php echo esc_attr( $field[ 'desc' ] ); ?></span>
                </td>
            </tr>
		<?php

		endif;
	}

	/**
	 * Returns a human readable file size.
	 *
	 * @since 2.0
	 *
	 * @param string $filepath Full path to the file.
	 * @param int    $decimal_places
	 *
	 * @return string
	 */
	public function human_readable_filesize( $filepath, $decimal_places = 2 ) {
		$filesize = ! empty( $filepath ) && is_writable( $filepath ) ? filesize( $filepath ) : false;

		if ( ! empty( $filesize ) ) {
			$size               = array( 'Bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
			$factor             = floor( ( strlen( $filesize ) - 1 ) / 3 );
			$exponential_result = $filesize / pow( 1024, $factor );

			/**
			 * Arrays and objects can not be used as array keys. Doing so will result in a warning: Illegal offset type.
			 * $factor is an illegal key as a float type, so it is cast as a string to make it legal. The string is then cast as an integer by PHP.
			 */
			return sprintf( "%.{$decimal_places}f", $exponential_result ) . ' ' . $size[ (string) $factor ];
		}

		return '0.00';
	}

} // end of class