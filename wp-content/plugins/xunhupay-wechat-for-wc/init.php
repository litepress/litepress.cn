<?php
/*
 * Plugin Name: Xh Wechat Payment For WooCommerce New
 * Plugin URI: http://www.xunhuweb.com
 * Description: (迅虎支付)微信扫码支付、微信H5支付、jsapi支付
 * Author: 重庆迅虎网络有限公司
 * Version: 1.0.7
 * Author URI:  http://www.xunhuweb.com
 * Text Domain: Wechat payment for woocommerce
 * WC tested up to: 9.9.9
 */

if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

if (! defined ( 'XH_Wechat_Payment' )) {define ( 'XH_Wechat_Payment', 'XH_Wechat_Payment' );} else {return;}
define ( 'XH_Wechat_Payment_VERSION', '1.0.7');
define ( 'XH_Wechat_Payment_ID', 'xh-wechat-payment-wc');
define ( 'XH_Wechat_Payment_FILE', __FILE__);
define ( 'XH_Wechat_Payment_DIR', rtrim ( plugin_dir_path ( XH_Wechat_Payment_FILE ), '/' ) );
define ( 'XH_Wechat_Payment_URL', rtrim ( plugin_dir_url ( XH_Wechat_Payment_FILE ), '/' ) );
load_plugin_textdomain( XH_Wechat_Payment, false,dirname( plugin_basename( __FILE__ ) ) . '/lang/'  );

add_filter ( 'plugin_action_links_'.plugin_basename( XH_Wechat_Payment_FILE ),'xh_wechat_payment_plugin_action_links_new',10,1 );
function xh_wechat_payment_plugin_action_links_new($links) {
    return array_merge ( array (
        'settings' => '<a href="' . admin_url ( 'admin.php?page=wc-settings&tab=checkout&section='.XH_Wechat_Payment_ID ) . '">'.__('Settings',XH_Wechat_Payment).'</a>'
    ), $links );
}

if(!class_exists('WC_Payment_Gateway')){
    return;
}

require_once XH_Wechat_Payment_DIR.'/class-wechat-wc-payment-gateway.php';
global $XH_Wechat_Payment_WC_Payment_Gateway;
$XH_Wechat_Payment_WC_Payment_Gateway= new XH_Wechat_Payment_WC_Payment_Gateway();

add_action('init', function(){
    $request = shortcode_atts(array(
        'action'=>null,
        'order_id'=>null,
        'time'=>null,
        'notice_str'=>null,
        'hash'=>null
    ), stripslashes_deep($_REQUEST));

    if(empty($request['action'])||$request['action']!='-hpj-wechat-do-pay'){
        return;
    }

});

