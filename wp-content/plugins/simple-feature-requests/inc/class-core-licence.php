<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

if ( class_exists( 'JCK_SFR_Core_Licence' ) ) {
    return;
}
/**
 * JCK_SFR_Core_Licence.
 *
 * @class    JCK_SFR_Core_Licence
 * @version  1.0.0
 * @author   Iconic
 */
class JCK_SFR_Core_Licence
{
    /**
     * Single instance of the JCK_SFR_Core_Licence object.
     *
     * @var JCK_SFR_Core_Licence
     */
    public static  $single_instance = null ;
    /**
     * Class args.
     *
     * @var array
     */
    public static  $args = array() ;
    /**
     * Freemius instance.
     *
     * @var Freemius
     */
    public static  $freemius = null ;
    /**
     * Creates/returns the single instance JCK_SFR_Core_Licence object.
     *
     * @return JCK_SFR_Core_Licence
     */
    public static function run( $args = array() )
    {
        
        if ( null === self::$single_instance ) {
            self::$args = $args;
            self::$single_instance = new self();
        }
        
        return self::$single_instance;
    }
    
    /**
     * Construct.
     */
    private function __construct()
    {
        self::configure_freemius();
        self::run_hooks();
    }
    
    /**
     * Configure Freemius.
     */
    public static function configure_freemius()
    {
        if ( !is_null( self::$freemius ) ) {
            return;
        }
        require_once self::$args['paths']['inc'] . 'vendor/freemius/start.php';
        $menu = array(
            'slug'       => self::get_fs_arg( 'menu/slug', null ),
            'contact'    => self::get_fs_arg( 'menu/contact', false ),
            'support'    => self::get_fs_arg( 'menu/support', false ),
            'account'    => self::get_fs_arg( 'menu/account', false ),
            'pricing'    => self::get_fs_arg( 'menu/pricing', true ),
            'first-path' => self::get_fs_arg( 'menu/first-path', false ),
        );
        $parent = self::get_fs_arg( 'menu/parent', true );
        if ( $parent ) {
            $menu['parent'] = array(
                'slug' => self::get_fs_arg( 'menu/parent/slug', 'woocommerce' ),
            );
        }
        // Requires id, slug, public key, menu slug
        self::$freemius = fs_dynamic_init( array(
            'id'               => self::get_fs_arg( 'id', null ),
            'slug'             => self::get_fs_arg( 'slug', null ),
            'type'             => self::get_fs_arg( 'type', 'plugin' ),
            'public_key'       => self::get_fs_arg( 'public_key', null ),
            'is_premium'       => false,
            'is_premium_only'  => self::get_fs_arg( 'is_premium_only', true ),
            'has_paid_plans'   => self::get_fs_arg( 'has_paid_plans', true ),
            'has_addons'       => self::get_fs_arg( 'has_addons', false ),
            'is_org_compliant' => self::get_fs_arg( 'is_org_compliant', false ),
            'trial'            => array(
            'days'               => self::get_fs_arg( 'trial/days', 14 ),
            'is_require_payment' => self::get_fs_arg( 'trial/is_require_payment', true ),
        ),
            'menu'             => $menu,
            'is_live'          => true,
        ) );
        // Set basename
        if ( isset( self::$args['paths']['file'] ) ) {
            self::$freemius->set_basename( true, self::$args['paths']['file'] );
        }
    }
    
    /**
     * Get FS arg to avoid error when deploying.
     *
     * @param $keys
     * @param $default
     *
     * @return mixed
     */
    public static function get_fs_arg( $keys, $default )
    {
        $base = self::$args['freemius'];
        $keys = explode( '/', $keys );
        $depth = 0;
        $key_count = count( $keys );
        foreach ( $keys as $key ) {
            $depth++;
            if ( !isset( $base[$key] ) ) {
                break;
            }
            $base = $base[$key];
            if ( $depth == $key_count ) {
                return $base;
            }
        }
        return $default;
    }
    
    /**
     * Run hooks.
     */
    public static function run_hooks()
    {
        self::$freemius->add_filter( 'show_trial', '__return_false' );
        self::$freemius->add_filter(
            'templates/account.php',
            array( __CLASS__, 'back_to_settings_link' ),
            10,
            1
        );
        self::$freemius->add_filter(
            'plugin_icon',
            array( __CLASS__, 'plugin_icon' ),
            10,
            1
        );
        self::$freemius->add_filter( 'hide_account_tabs', '__return_true' );
        add_filter( 'plugin_action_links_' . self::$args['basename'], array( __CLASS__, 'add_action_links' ) );
        add_action( 'admin_notices', array( __CLASS__, 'output_back_to_settings_link' ), 200 );
    }
    
    /**
     * Set plugin icon.
     *
     * @return string
     */
    public static function plugin_icon( $icon )
    {
        return self::$args['paths']['plugin'] . '/assets/img/plugin-icon.png';
    }
    
    /**
     * Back to settings link.
     *
     * @todo Move to settings class.
     */
    public static function back_to_settings_link( $html = '' )
    {
        return $html . sprintf( '<a href="%s" class="button button-secondary">&larr; %s</a>', self::$args['urls']['settings'], __( 'Back to Settings', 'simple-feature-requests' ) );
    }
    
    /**
     * Output back to settings link.
     */
    public static function output_back_to_settings_link()
    {
        if ( !JCK_SFR_Core_Settings::is_settings_page( '-account' ) ) {
            return;
        }
        ?>
		<div style="margin: 20px 0 10px;">
			<?php 
        echo  wp_kses_post( self::back_to_settings_link() ) ;
        ?>
		</div>
		<?php 
    }
    
    /**
     * Add action links to "plugins" page.
     *
     * @param $links
     *
     * @return array
     */
    public static function add_action_links( $links )
    {
        $links[] = sprintf( '<a href="%s" target="_blank">%s</a>', self::$args['urls']['product'] . '/changelog/?utm_source=Iconic&utm_medium=Plugin&utm_campaign=simple-feature-requests&utm_content=changelog-link', __( 'Changelog', 'simple-feature-requests' ) );
        return $links;
    }
    
    /**
     * Get admin account link button.
     *
     * @return string
     */
    public static function admin_account_link()
    {
        return sprintf( '<a href="%s" class="button button-secondary">%s</a>', self::$args['urls']['account'], __( 'Manage Licence &amp; Billing', 'simple-feature-requests' ) );
    }
    
    /**
     * Has valid licence?
     *
     * @return bool
     */
    public static function has_valid_licence()
    {
        if ( self::$freemius->can_use_premium_code() ) {
            return true;
        }
        return false;
    }

}