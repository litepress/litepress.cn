<?php
define('WP_USE_THEMES', false);
require_once('../../../../wp-load.php');
$api=XH_Wechat_Payment_WC_Payment_Gateway::instance();
$mchid=$api->get_option('mchid');
$private_key=$api->get_option('private_key');
$order_id= $_GET['out_trade_no'];
if($order_id){
try {
	 $data=array(
            'mchid'     	=> $mchid,
            'out_trade_no'  => $order_id,
            'nonce_str' 	=> str_shuffle(time()),
    	);
	$url = 'https://admin.xunhuweb.com/pay/query';
	$data['sign']	  = $api->generate_xh_hash($data,$private_key);
    $response   	  = $api->http_post($url, json_encode($data));
	$result     	  = $response?json_decode($response,true):null;
    if(isset($result['status'])&&$result['status']=='complete'){
        	var_dump($result); 
	}else{
		echo 'to_be_paid';
	}
	}catch (Exception $e){
		
	}
		return $order_id;
}