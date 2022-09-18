<?php 
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$recent_url=dirname($http_type.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
$api=XH_Wechat_Payment_WC_Payment_Gateway::instance();
$redirect_url=urldecode($_GET['redirect']);
$order_id=$_GET['order_id'];
$pay_url=urldecode($_GET['url']).'&$redirect_url='.$_GET['redirect'];
?>
<html>
<head>
<meta charset="UTF-8">
<title>收银台付款</title>
<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="<?php echo XH_Wechat_Payment_URL?>/images/style.css">
</head>
<body ontouchstart="" class="bggrey">
<div class="xh-title"><img src="https://api.xunhupay.com/content/images/wechat-s.png" alt="" style="vertical-align: middle"> 微信支付收银台</div>

<div class="xhpay ">
   <img class="logo" alt="" src="<?php echo XH_Wechat_Payment_URL?>/images/weixin.png">

	<span class="price"><?php echo $_GET['total_fee'] ?></span>
</div>
<div class="xhpaybt">
	<a href="<?php echo $pay_url?>" class="xunhu-btn xunhu-btn-green" >微信支付</a>
</div>
<div class="xhpaybt">
	<a href="<?php echo $redirect_url;?>" class="xunhu-btn xunhu-btn-border-green" >取消支付</a>
</div>
<div class="xhtext" align="center">支付完成后，如需售后服务请联系客服</div>
<script src="<?php echo XH_Wechat_Payment_URL.'/js/jquery-2.2.4.min.js'; ?>"></script>
<script type="text/javascript">
(function($){
	window.view={
		query:function () {
	        $.ajax({
	            type: "POST",
	            url: "<?php echo XH_Wechat_Payment_URL.'/views/query.php?out_trade_no='.$order_id ?>",
	            timeout:6000,
	            cache:false,
	            dataType:'text',
	            success:function(e){
	            	if (e && e.indexOf('complete')!==-1) {
	                    window.location.href = "<?php echo $redirect_url ?>";
	                    return;
	                }
	                setTimeout(function(){window.view.query();}, 2000);
	            },
	            error:function(){
	            	 setTimeout(function(){window.view.query();}, 2000);
	            }
	        });
	    }
	};
      window.view.query();
})(jQuery);
</script>
</body>
</html>