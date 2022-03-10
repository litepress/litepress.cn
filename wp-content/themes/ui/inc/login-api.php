<?php
/**
 * 登录 API
 */

namespace LitePress\api\login;

function tcaptcha_check( $Ticket, $Randstr ) {
	$options = get_site_option( 'litepress' );

	$CaptchaAppId = $options['captcha_appid'];
	$AppSecretKey = $options['app_secretkey'];
	$secretId     = $options['secret_id'];
	$secretKey    = $options['secret_key'];

	if ( ! $CaptchaAppId || ! $AppSecretKey || ! $secretId || ! $secretKey || ! $Ticket || ! $Randstr ) {
		return false;
	}

	$CaptchaAppId = (int) $CaptchaAppId;

	$host      = "captcha.tencentcloudapi.com";
	$service   = "captcha";
	$version   = "2019-07-22";
	$action    = "DescribeCaptchaResult";
	$timestamp = time();

	$payload = array(
		'CaptchaType'  => 9,
		'Ticket'       => $Ticket,
		'Randstr'      => $Randstr,
		'UserIp'       => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ),
		'CaptchaAppId' => $CaptchaAppId,
		'AppSecretKey' => $AppSecretKey,
	);

	$algorithm = "TC3-HMAC-SHA256";

	// step 1: build canonical request string
	$httpRequestMethod    = "POST";
	$canonicalUri         = "/";
	$canonicalQueryString = "";
	$canonicalHeaders     = "content-type:application/json\n" . "host:" . $host . "\n";
	$signedHeaders        = "content-type;host";


	$hashedRequestPayload = hash( "SHA256", json_encode( $payload ) );
	$canonicalRequest     = $httpRequestMethod . "\n"
	                        . $canonicalUri . "\n"
	                        . $canonicalQueryString . "\n"
	                        . $canonicalHeaders . "\n"
	                        . $signedHeaders . "\n"
	                        . $hashedRequestPayload;
	// echo $canonicalRequest.PHP_EOL;

	// step 2: build string to sign
	$date                   = gmdate( "Y-m-d", $timestamp );
	$credentialScope        = $date . "/" . $service . "/tc3_request";
	$hashedCanonicalRequest = hash( "SHA256", $canonicalRequest );
	$stringToSign           = $algorithm . "\n"
	                          . $timestamp . "\n"
	                          . $credentialScope . "\n"
	                          . $hashedCanonicalRequest;
	// echo $stringToSign.PHP_EOL;

	// step 3: sign string
	$secretDate    = hash_hmac( "SHA256", $date, "TC3" . $secretKey, true );
	$secretService = hash_hmac( "SHA256", $service, $secretDate, true );
	$secretSigning = hash_hmac( "SHA256", "tc3_request", $secretService, true );
	$signature     = hash_hmac( "SHA256", $stringToSign, $secretSigning );
	// echo $signature.PHP_EOL;

	// step 4: build authorization
	$authorization = $algorithm
	                 . " Credential=" . $secretId . "/" . $credentialScope
	                 . ", SignedHeaders=content-type;host, Signature=" . $signature;
	// echo $authorization.PHP_EOL;


	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, 'https://' . $host );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $httpRequestMethod );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
		'Authorization: ' . $authorization,
		'Content-Type: application/json',
		'Host: ' . $host,
		'X-TC-Action: ' . $action,
		'X-TC-Version: ' . $version,
		'X-TC-Timestamp: ' . $timestamp,
	) );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );
	$output = curl_exec( $ch );
	curl_close( $ch );

	$output = json_decode( $output );

	// print_r($output);

	// https://cloud.tencent.com/document/product/1110/36926#3.-.E8.BE.93.E5.87.BA.E5.8F.82.E6.95.B0
	if ( isset( $output->Response ) && isset( $output->Response->CaptchaCode ) && $output->Response->CaptchaCode == 1 ) {
		return true;
	}

	return false;
}

//tcaptcha_check( 1, 2 );
header( "Content-type:application/json;charset=utf-8" );

$current_url = $_SERVER['REQUEST_URI'];
if ( '/lpcn/login' === $current_url ) {

	$username         = sanitize_text_field( $_POST['username'] );
	$password         = sanitize_text_field( $_POST['password'] );
	$remember         = (int) sanitize_text_field( $_POST['remember'] );
	$tcaptcha_ticket  = sanitize_textarea_field( $_POST['tcaptcha-ticket'] );
	$tcaptcha_randstr = sanitize_textarea_field( $_POST['tcaptcha-randstr'] );

	if ( empty( $username ) || empty( $password ) ) {
		echo json_encode( array( 'code' => 1, 'error' => '账号密码不能为空' ), JSON_UNESCAPED_UNICODE );
		exit;
	}

	if ( empty( $tcaptcha_ticket ) || empty( $tcaptcha_randstr ) ) {
		echo json_encode( array( 'code' => 1, 'error' => '必须完成滑块验证才可登录' ), JSON_UNESCAPED_UNICODE );
		exit;
	}

	if ( ! tcaptcha_check( $tcaptcha_ticket, $tcaptcha_randstr ) ) {
		echo json_encode( array( 'code' => 1, 'error' => '验证码错误' ), JSON_UNESCAPED_UNICODE );
		exit;
	}

	$login_data['user_login']    = $username;
	$login_data['user_password'] = $password;
	$login_data['remember']      = (bool) $remember;


	$user_verify = wp_signon( $login_data, false );
	if ( is_wp_error( $user_verify ) ) {
		echo json_encode( array( 'code' => 1, 'error' => '用户名或者密码错误！' ), JSON_UNESCAPED_UNICODE );
		exit;
	} else {
		$user     = get_user_by( 'login', $username );
		$user_id  = $user->ID;
		$userinfo = get_userdata( $user_id );

		// 登录成功需要生成一下缓存插件的 Cookie，这样才能标识当前用户已登录从而不对其缓存
		do_action( 'set_logged_in_cookie' );
		do_action( 'wp_login' );
		do_action( 'login_init' );

		echo json_encode( array( 'code' => 0, 'message' => '登陆成功' ), JSON_UNESCAPED_UNICODE );
		exit;
	}
}
