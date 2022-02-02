<?php
/*
Plugin Name: LitePress.cn 的腾讯云验证码
Plugin URI: https://litepress.cn/
Description: LitePress.cn的腾讯云验证码，不适用于通用环境，插件必须在启用终极会员后方可启用
Version: 1.0.0
Author: LitePress团队
Author URI: https://litepress.cn/
Text Domain: lpcn-um-tcaptcha
Domain Path: /languages
UM version: 2.1.0
*/

namespace LitePress\UMTCaptcha;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LitePress\Framework\Framework;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

// 注册设置项
$options = get_site_option( 'litepress' );
Framework::createSection( 'litepress', array(
	'id'     => 'um_tcaptcha',
	'title'  => 'UM-腾讯云验证码',
	'icon'   => 'fa fa-ban',
	'fields' => array(
		array(
			'id'          => 'captcha_appid',
			'type'        => 'text',
			'title'       => 'CaptchaAppId',
			'placeholder' => '请填写验证码应用ID',
			'desc'        => '填写验证码应用ID，即CaptchaAppId',
		),
		array(
			'id'          => 'app_secretkey',
			'type'        => 'text',
			'title'       => 'AppSecretKey',
			'placeholder' => '请填写验证码应用KEY',
			'desc'        => '填写验证码应用KEY，即AppSecretKey',
		),
		array(
			'id'          => 'secret_id',
			'type'        => 'text',
			'title'       => 'secretId',
			'placeholder' => '请填写账号ID',
			'desc'        => '填写账号ID，即secretId',
		),
		array(
			'id'          => 'secret_key',
			'type'        => 'text',
			'title'       => 'secretKey',
			'placeholder' => '请填写账号KEY',
			'desc'        => '填写账号KEY，即secretKey',
		),
	)
) );

// 添加验证码
function add_captcha( $args ) {
	global $options;
	$t_args = compact( 'args' );
	wp_enqueue_script( 'tcaptcha', 'https://ssl.captcha.qq.com/TCaptcha.js', array() );
	wp_enqueue_script( 'um-tcaptcha', plugin_dir_url( PLUGIN_FILE ) . '/assets/um-tcaptcha.js', array(
		'tcaptcha',
		'jquery'
	) );

	wp_localize_script( 'um-tcaptcha', 'UMTCaptcha', array(
		'captcha_appid' => $options['captcha_appid'],
	) );
}

add_action( 'um_after_register_fields', __NAMESPACE__ . '\add_captcha', 500 );
add_action( 'um_after_login_fields', __NAMESPACE__ . '\add_captcha', 500 );
add_action( 'um_after_password_reset_fields', __NAMESPACE__ . '\add_captcha', 500 );

// 添加二次验证
function validate( $args ) {
	if ( isset( $args['mode'] ) && ! in_array( $args['mode'], array(
			'login',
			'register',
			'password'
		), true ) && ! isset( $args['_social_login_form'] ) ) {
		return;
	}

	if ( empty( $_POST['tcaptcha-ticket'] ) || empty( $_POST['tcaptcha-randstr'] ) ) {
		UM()->form()->add_error( 'tcaptcha', '妖魔鬼怪快离开！' );

		return;
	} else {
		$ticket  = sanitize_textarea_field( $_POST['tcaptcha-ticket'] );
		$randstr = sanitize_textarea_field( $_POST['tcaptcha-randstr'] );
	}

	if ( ! tcaptcha_check( $ticket, $randstr ) ) {
		UM()->form()->add_error( 'tcaptcha', '妖魔鬼怪快离开！' );
	}
}

add_action( 'um_submit_form_errors_hook', __NAMESPACE__ . '\validate', 20 );
add_action( 'um_reset_password_errors_hook', __NAMESPACE__ . '\validate', 20 );

// 请求腾讯云接口做二次验证
function tcaptcha_check( $Ticket, $Randstr ) {
	global $options;
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