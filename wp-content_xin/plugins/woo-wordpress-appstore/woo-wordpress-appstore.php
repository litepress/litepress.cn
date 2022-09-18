<?php
/**
 * Plugin Name: Woo WordPress AppStore
 * Description: 使WooCommerce变身为WordPress应用商店，并增加REST API支持
 * Version: 1.1.0
 * Author: LitePress团队
 * Author URI: https://litepress.cn
 * WC requires at least: 4.7.0
 * WC tested up to: 5.8
 * Requires WP: 5.5.4
 * Requires PHP: 8.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace WCWPAS;

add_action( 'init', function () {
    $labels = array(
        'name'              => '产品供应商',
        'singular_name'     => '产品供应商',
        'search_items'      => '搜索产品供应商',
        'all_items'         => '所有产品供应商',
        'parent_item'       => '父项目',
        'parent_item_colon' => __( 'Parent Genre:' ),
        'edit_item'         => __( 'Edit Genre' ),
        'update_item'       => __( 'Update Genre' ),
        'add_new_item'      => __( 'Add New Genre' ),
        'new_item_name'     => __( 'New Genre Name' ),
        'menu_name'         => __( 'Genre' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'product-vendors' ),
        'show_in_rest'          => true,
        'rest_base'             => 'product-vendors',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );



    register_taxonomy( 'wcpv_product_vendors', array( 'product' ), $args );
}, 999 );



//ob_start();
add_action('woocommerce_after_register_post_type', function () {
    //ob_clean();
    //global $_wp_post_type_features;

    remove_post_type_support('product', 'editor');

    //echo json_encode($_wp_post_type_features);
    //exit;
});

add_action('add_meta_boxes', function () {
    // remove_meta_box( 'postexcerpt', 'product', 'normal' );
}, 40);

add_filter('woocommerce_product_data_tabs', function () {
    return array(
        'general' => array(
            'label' => __('General', 'woocommerce'),
            'target' => 'general_product_data',
            'class' => array('hide_if_grouped'),
            'priority' => 10,
        ),
        'inventory' => array(
            'label' => __('Inventory', 'woocommerce'),
            'target' => 'inventory_product_data',
            'class' => array('show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external'),
            'priority' => 20,
        ),
        'shipping' => array(
            'label' => __('Shipping', 'woocommerce'),
            'target' => 'shipping_product_data',
            'class' => array('hide_if_virtual', 'hide_if_grouped', 'hide_if_external'),
            'priority' => 30,
        ),
        'linked_product' => array(
            'label' => __('Linked Products', 'woocommerce'),
            'target' => 'linked_product_data',
            'class' => array(),
            'priority' => 40,
        ),
        'attribute' => array(
            'label' => __('Attributes', 'woocommerce'),
            'target' => 'product_attributes',
            'class' => array(),
            'priority' => 50,
        ),
        'variations' => array(
            'label' => __('Variations', 'woocommerce'),
            'target' => 'variable_product_options',
            'class' => array('variations_tab', 'show_if_variable'),
            'priority' => 60,
        ),
        'advanced' => array(
            'label' => __('Advanced', 'woocommerce'),
            'target' => 'advanced_product_data',
            'class' => array(),
            'priority' => 70,
        ),
    );
});

defined('ABSPATH') || exit;

define('WAS_VERSION', '1.0.0');
define('WAS_ROOT_PATH', plugin_dir_path(__FILE__));

require 'vendor/autoload.php';

use WCWPAS\Controllers\WAS_REST_Orders_Controller;
use WCWPAS\Controllers\WAS_REST_Product_Tags_Controller;
use WCWPAS\Controllers\WAS_REST_Products_Controller;
use WCWPAS\Controllers\WAS_REST_Account_Controller;
use WCWPAS\Controllers\WAS_REST_Product_Reviews_Controller;
use WCWPAS\Src\WAS_Auth;

require_once('include/woo-functions.php');

final class WooWordPressAppStore
{

    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        // 重设支付宝网关的回跳地址为用户指定的地址
        if (key_exists('return_url', $_GET)) {
            add_filter('woo_alipay_gateway_return_url', function () {
                return $_GET['return_url'];
            });
        }

        // 注册Rest API路由
        add_action('rest_api_init', function () {
            $products_controller = new WAS_REST_Products_Controller;
            $products_controller->register_routes();
            $account_controller = new WAS_REST_Account_Controller;
            $account_controller->register_routes();
            $orders_controller = new WAS_REST_Orders_Controller;
            $orders_controller->register_routes();
            $product_reviews_controller = new WAS_REST_Product_Reviews_Controller;
            $product_reviews_controller->register_routes();
            $product_tags_controller = new  WAS_REST_Product_Tags_Controller;
            $product_tags_controller->register_routes();
        }, 10);


        // 注册Web路由
        $was_auth = new WAS_Auth();
        //add_filter('query_vars', [$was_auth, 'add_query_vars'], 0);
        //add_action('init', ['WCWPAS\Src\WAS_Auth', 'add_route'], 0);
        //add_action('parse_request', [$was_auth, 'handle_auth_requests']);

    }

}

/*
add_filter('woocommerce_rest_check_permissions', function ( $permission, $context, $object_id, $post_type ) {
    return true;
}, 10, 4);

*/


function WCWPAS()
{
    return WooWordPressAppStore::instance();
}

WCWPAS();
