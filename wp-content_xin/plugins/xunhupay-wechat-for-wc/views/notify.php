<?php
define('WP_USE_THEMES', false);
require_once('../../../../wp-load.php');
if(!isset($_POST)){
	 exit('faild!');
}
$data=(array)json_decode(file_get_contents('php://input'));
if(!$data){
	 exit('faild!');
}
// file_put_contents(realpath(dirname(__FILE__)) . "/log.txt",json_encode($data)."\r\n",FILE_APPEND);
$out_trade_no = isset($data['out_trade_no'])?$data['out_trade_no']:null;
$arr = explode('@',$out_trade_no);
$out_trade_no = $arr['0'];
$order_id=isset($data['order_id'])?$data['order_id']:null;
if(!$out_trade_no||!$order_id){
	exit('fail!');
}
$api=XH_Wechat_Payment_WC_Payment_Gateway::instance();
$private_key=$api->get_option('private_key');
$hash =$api->generate_xh_hash($data,$private_key);
if($data['sign']!=$hash){
    //签名验证失败
    echo '签名错误';exit;
}
if($data['status']=='complete'){
	$order = wc_get_order($out_trade_no);
	  if(!$order||$order->is_paid()){
             print 'success';
             exit;
        }
	 $order->payment_complete($out_trade_no);
}else{
		//处理未支付的情况	
}
print 'success';
exit;