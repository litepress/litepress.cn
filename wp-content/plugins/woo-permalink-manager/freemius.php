<?php

// Create a helper function for easy SDK access.

if ( !function_exists( 'premmerce_wpm_fs' ) ) {
    // Create a helper function for easy SDK access.
    function premmerce_wpm_fs()
    {
        global  $premmerce_wpm_fs ;
        
        if ( !isset( $premmerce_wpm_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $premmerce_wpm_fs = fs_dynamic_init( array(
                'id'             => '1504',
                'slug'           => 'woo-permalink-manager',
                'type'           => 'plugin',
                'public_key'     => 'pk_99e9eb56c52475602e368258aec99',
                'is_premium'     => false,
                'premium_suffix' => '',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => true,
            ),
                'menu'           => array(
                'slug'           => 'premmerce-url-manager-admin',
                'override_exact' => true,
                'support'        => false,
                'parent'         => array(
                'slug' => 'premmerce',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $premmerce_wpm_fs;
    }
    
    // Init Freemius.
    premmerce_wpm_fs();
    // Signal that SDK was initiated.
    do_action( 'premmerce_wpm_fs_loaded' );
    function premmerce_wpm_fs_settings_url()
    {
        return admin_url( 'admin.php?page=premmerce-url-manager-admin' );
    }
    
    premmerce_wpm_fs()->add_filter( 'connect_url', 'premmerce_wpm_fs_settings_url' );
    premmerce_wpm_fs()->add_filter( 'after_skip_url', 'premmerce_wpm_fs_settings_url' );
    premmerce_wpm_fs()->add_filter( 'after_connect_url', 'premmerce_wpm_fs_settings_url' );
    premmerce_wpm_fs()->add_filter( 'after_pending_connect_url', 'premmerce_wpm_fs_settings_url' );
}
