<?php

use Premmerce\UrlManager\UrlManagerPlugin;

/**
 * Premmerce Permalink Manager for WooCommerce
 *
 * @package           Premmerce\UrlManager
 *
 * @wordpress-plugin
 * Plugin Name:       Premmerce Permalink Manager for WooCommerce
 * Plugin URI:        https://premmerce.com/woocommerce-permalink-manager-remove-shop-product-product-category-url/
 * Description:       Premmerce Permalink Manager for WooCommerce allows you to change WooCommerce permalink and remove product and product_category slugs from the URL.
 * Version:           2.3.2
 * Author:            premmerce
 * Author URI:        https://premmerce.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       premmerce-url-manager
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 5.4.1
 */

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

if ( ! function_exists('premmerce_wpm_fs')) {

    call_user_func(function () {

        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

        #premmerce_clear
        require_once plugin_dir_path(__FILE__) . '/freemius.php';
        #/premmerce_clear

        $main = new UrlManagerPlugin(__FILE__);

        register_activation_hook(__FILE__, [$main, 'activate']);

        register_deactivation_hook(__FILE__, [$main, 'deactivate']);

        register_uninstall_hook(__FILE__, [UrlManagerPlugin::class, 'uninstall']);

        $main->run();

    });
}
