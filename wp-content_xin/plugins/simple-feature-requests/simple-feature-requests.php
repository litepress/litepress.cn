<?php

/**
 * Plugin Name: Simple Feature Requests
 * Plugin URI: https://simplefeaturerequests.com
 * Description: Customer led feature requests with voting.
 * Version: 2.2.4
 * Author: Spellhammer
 * Author URI: https://elijahmills.com
 * Text Domain: simple-feature-requests
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class JCK_Simple_Feature_Requests
{
    /**
     * Version
     *
     * @var string
     */
    public static  $version = "2.2.4" ;
    /**
     * Full name
     *
     * @var string
     */
    public  $name = 'Simple Feature Requests' ;
    /**
     * @var null|JCK_SFR_Core_Settings
     */
    public  $settings = null ;
    /**
     * @var null|Freemius
     */
    public  $freemius = null ;
    /**
     * Class prefix
     *
     * @since  4.5.0
     * @access protected
     * @var string $class_prefix
     */
    protected  $class_prefix = "JCK_SFR_" ;
    /**
     * Pro link.
     *
     * @var string
     */
    public static  $pro_link = 'https://simplefeaturerequests.com/pricing/?utm_source=JCK&utm_medium=Plugin&utm_campaign=free-nudge' ;
    /**
     * Construct
     */
    function __construct()
    {
        $this->define_constants();
        self::load_files();
        $this->load_classes();
        $this->actions();
    }
    
    /**
     * Define Constants.
     */
    private function define_constants()
    {
        $this->define( 'JCK_SFR_PATH', plugin_dir_path( __FILE__ ) );
        $this->define( 'JCK_SFR_URL', plugin_dir_url( __FILE__ ) );
        $this->define( 'JCK_SFR_RELPATH', plugin_basename( JCK_SFR_PATH ) );
        $this->define( 'JCK_SFR_INC_PATH', JCK_SFR_PATH . 'inc/' );
        $this->define( 'JCK_SFR_VENDOR_PATH', JCK_SFR_INC_PATH . 'vendor/' );
        $this->define( 'JCK_SFR_TEMPLATES_PATH', JCK_SFR_PATH . 'templates/' );
        $this->define( 'JCK_SFR_ASSETS_URL', JCK_SFR_URL . 'assets/' );
        $this->define( 'JCK_SFR_BASENAME', plugin_basename( __FILE__ ) );
        $this->define( 'JCK_SFR_VERSION', self::$version );
        $this->define( 'JCK_SFR_BOARD_TAXONOMY_NAME', 'request_board' );
    }
    
    public function actions()
    {
        add_action( 'init', array( $this, 'localization' ) );
        add_filter(
            'jck_sfr_statuses',
            array( $this, 'add_custom_statuses' ),
            10,
            1
        );
        add_filter(
            'jck_sfr_single_request_name',
            array( $this, 'add_custom_labels_single' ),
            10,
            2
        );
        add_filter(
            'jck_sfr_plural_request_name',
            array( $this, 'add_custom_labels_plural' ),
            10,
            2
        );
        add_filter(
            'jck_sfr_status_descriptions',
            array( $this, 'add_custom_status_descriptions' ),
            10,
            1
        );
        add_filter(
            'jck_sfr_status_colors',
            array( $this, 'add_custom_status_colors' ),
            10,
            1
        );
    }
    
    public function localization()
    {
        load_plugin_textdomain( 'simple-feature-requests', false, JCK_SFR_RELPATH . '/languages/' );
    }
    
    public function add_custom_statuses( $statuses = array() )
    {
        $custom_statuses = jck_sfr_get_custom_statuses();
        $custom_statuses = maybe_unserialize( $custom_statuses );
        if ( !empty($custom_statuses) && is_array( $custom_statuses ) ) {
            foreach ( $custom_statuses as $status ) {
                $slug = jck_sfr_get_status_slug( $status['status_title'] );
                if ( !array_key_exists( $slug, $statuses ) ) {
                    $statuses[$slug] = $status['status_title'];
                }
            }
        }
        return $statuses;
    }
    
    public function add_custom_status_descriptions( $descriptions = array() )
    {
        $custom_statuses = jck_sfr_get_custom_statuses();
        $custom_statuses = maybe_unserialize( $custom_statuses );
        if ( !empty($custom_statuses && is_array( $custom_statuses )) ) {
            foreach ( $custom_statuses as $status ) {
                $slug = jck_sfr_get_status_slug( $status['status_title'] );
                if ( !array_key_exists( $slug, $descriptions ) ) {
                    $descriptions[$slug] = $status['status_description'];
                }
            }
        }
        return $descriptions;
    }
    
    public function add_custom_status_colors( $colors = array() )
    {
        $custom_statuses = jck_sfr_get_custom_statuses();
        $custom_statuses = maybe_unserialize( $custom_statuses );
        $default_bg_color = apply_filters( 'jck_sfr_custom_status_default_bg_color', '#d16060' );
        $default_text_color = apply_filters( 'jck_sfr_custom_status_default_text_color', '#FFFFFF' );
        if ( !empty($custom_statuses && is_array( $custom_statuses )) ) {
            foreach ( $custom_statuses as $status ) {
                $slug = jck_sfr_get_status_slug( $status['status_title'] );
                
                if ( !array_key_exists( $slug, $colors ) ) {
                    $colors[$slug]['color'] = ( !empty($status['status_text_color']) ? $status['status_text_color'] : $default_text_color );
                    $colors[$slug]['background'] = ( !empty($status['status_bg_color']) ? $status['status_bg_color'] : $default_bg_color );
                }
            
            }
        }
        return $colors;
    }
    
    /**
     * Custom labels
     */
    public function add_custom_labels_single( $string, $capitalize )
    {
        $custom_label_single = jck_sfr_get_custom_labels( 'single' );
        
        if ( $capitalize ) {
            return ucfirst( $custom_label_single );
        } else {
            return strtolower( $custom_label_single );
        }
    
    }
    
    public function add_custom_labels_plural( $string, $capitalize )
    {
        $custom_label_plural = jck_sfr_get_custom_labels( 'plural' );
        
        if ( $capitalize ) {
            return ucfirst( $custom_label_plural );
        } else {
            return strtolower( $custom_label_plural );
        }
    
    }
    
    /**
     * Define constant if not already set.
     *
     * @param string      $name
     * @param string|bool $value
     */
    private function define( $name, $value )
    {
        if ( !defined( $name ) ) {
            define( $name, $value );
        }
    }
    
    /**
     * Load files.
     */
    private static function load_files()
    {
        require_once JCK_SFR_INC_PATH . 'functions.php';
    }
    
    /**
     * Load classes
     */
    private function load_classes()
    {
        global  $simple_feature_requests_licence ;
        require_once JCK_SFR_INC_PATH . 'class-core-autoloader.php';
        JCK_SFR_Core_Autoloader::run( array(
            'prefix'   => 'JCK_SFR_',
            'inc_path' => JCK_SFR_INC_PATH,
        ) );
        // Activate multisite network integration.
        if ( !defined( 'WP_FS__PRODUCT_1577_MULTISITE' ) ) {
            define( 'WP_FS__PRODUCT_1577_MULTISITE', true );
        }
        $simple_feature_requests_licence = JCK_SFR_Core_Licence::run( array(
            'basename' => JCK_SFR_BASENAME,
            'urls'     => array(
            'product'  => 'https://www.simplefeaturerequests.com/',
            'settings' => admin_url( 'admin.php?page=jck-sfr-settings' ),
            'account'  => admin_url( 'admin.php?page=jck-sfr-settings-account' ),
        ),
            'paths'    => array(
            'inc'    => JCK_SFR_INC_PATH,
            'plugin' => JCK_SFR_PATH,
        ),
            'freemius' => array(
            'id'                  => '1577',
            'slug'                => 'simple-feature-requests',
            'type'                => 'plugin',
            'public_key'          => 'pk_021142a45de2c0bcd8dc427adc8f7',
            'is_premium'          => true,
            'is_premium_only'     => false,
            'has_premium_version' => true,
            'has_addons'          => false,
            'has_paid_plans'      => true,
            'menu'                => array(
            'slug'   => 'jck-sfr-settings',
            'parent' => false,
        ),
        ),
        ) );
        $this->freemius = $simple_feature_requests_licence::$freemius;
        $this->settings = JCK_SFR_Core_Settings::run( array(
            'vendor_path'   => JCK_SFR_VENDOR_PATH,
            'title'         => __( 'Simple Feature Requests', 'simple-feature-requests' ),
            'version'       => self::$version,
            'menu_title'    => JCK_SFR_Post_Types::get_menu_title(),
            'page_title'    => __( 'Simple Feature Requests', 'simple-feature-requests' ),
            'parent_slug'   => false,
            'capability'    => 'manage_options',
            'settings_path' => JCK_SFR_INC_PATH . 'admin/settings.php',
            'option_group'  => 'jck_sfr',
            'docs'          => array(
            'collection'      => '/collection/134-woocommerce-attribute-swatches',
            'troubleshooting' => '',
            'getting-started' => false,
        ),
        ) );
        JCK_SFR_Settings::run();
        JCK_SFR_Assets::run();
        JCK_SFR_Post_Types::run( $this->settings );
        JCK_SFR_Shortcodes::run();
        JCK_SFR_AJAX::run();
        JCK_SFR_User::run();
        JCK_SFR_Submission::run();
        JCK_SFR_Query::run();
        JCK_SFR_Template_Hooks::run();
        JCK_SFR_Factory::run();
        JCK_SFR_Notifications::run();
        JCK_SFR_Compat_Elementor::run();
        JCK_SFR_Compat_Astra::run();
    }
    
    /**
     * Get pro button.
     *
     * @return string
     */
    public static function get_pro_button()
    {
        return '<a href="' . esc_url( self::$pro_link ) . '" target="_blank" class="button" style="margin-top: 5px;">' . __( 'Available in Pro', 'simple-feature-requests' ) . '</a>';
    }

}
$simple_feature_requests_class = new JCK_Simple_Feature_Requests();