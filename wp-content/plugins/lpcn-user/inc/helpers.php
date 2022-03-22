<?php

namespace LitePress\User\Inc;

use Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;
use WP_Error;

function tcaptcha_check( $Ticket, $Randstr ): bool {
	if ( ! CAPTCHA_ID || ! CAPTCHA_KEY || ! Q_CLOUD_ACCESS_Key || ! Q_CLOUD_SECRET_Key || ! $Ticket || ! $Randstr ) {
		return false;
	}

	$CaptchaAppId = (int) CAPTCHA_ID;

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
		'AppSecretKey' => CAPTCHA_KEY,
	);

	$algorithm = "TC3-HMAC-SHA256";

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

	$date                   = gmdate( "Y-m-d", $timestamp );
	$credentialScope        = $date . "/" . $service . "/tc3_request";
	$hashedCanonicalRequest = hash( "SHA256", $canonicalRequest );
	$stringToSign           = $algorithm . "\n"
	                          . $timestamp . "\n"
	                          . $credentialScope . "\n"
	                          . $hashedCanonicalRequest;

	$secretDate    = hash_hmac( "SHA256", $date, "TC3" . Q_CLOUD_SECRET_Key, true );
	$secretService = hash_hmac( "SHA256", $service, $secretDate, true );
	$secretSigning = hash_hmac( "SHA256", "tc3_request", $secretService, true );
	$signature     = hash_hmac( "SHA256", $stringToSign, $secretSigning );

	$authorization = $algorithm
	                 . " Credential=" . Q_CLOUD_ACCESS_Key . "/" . $credentialScope
	                 . ", SignedHeaders=content-type;host, Signature=" . $signature;

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

	// https://cloud.tencent.com/document/product/1110/36926#3.-.E8.BE.93.E5.87.BA.E5.8F.82.E6.95.B0
	if ( isset( $output->Response ) && isset( $output->Response->CaptchaCode ) && $output->Response->CaptchaCode == 1 ) {
		return true;
	}

	return false;
}

/**
 * 发送短信验证码（登录与注册功能通用）
 */
function send_sms_code( string $tel ): bool|WP_Error {
	try {
		// 生成验证码
		$code = rand( 1000, 9999 );

		$cred = new Credential( Q_CLOUD_ACCESS_Key_2, Q_CLOUD_SECRET_Key_2 );
		// 实例化一个http选项，可选的，没有特殊需求可以跳过
		$httpProfile = new HttpProfile();
		// 配置代理
		$httpProfile->setReqMethod( "GET" );  // post请求(默认为post请求)
		$httpProfile->setReqTimeout( 30 );    // 请求超时时间，单位为秒(默认60秒)
		$httpProfile->setEndpoint( "sms.tencentcloudapi.com" );  // 指定接入地域域名(默认就近接入)

		// 实例化一个client选项，可选的，没有特殊需求可以跳过
		$clientProfile = new ClientProfile();
		$clientProfile->setSignMethod( "TC3-HMAC-SHA256" );  // 指定签名算法(默认为HmacSHA256)
		$clientProfile->setHttpProfile( $httpProfile );

		// 实例化要请求产品(以sms为例)的client对象,clientProfile是可选的
		// 第二个参数是地域信息，可以直接填写字符串ap-guangzhou，支持的地域列表参考 https://cloud.tencent.com/document/api/382/52071#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8
		$client = new SmsClient( $cred, "ap-guangzhou", $clientProfile );

		// 实例化一个 sms 发送短信请求对象,每个接口都会对应一个request对象。
		$req = new SendSmsRequest();

		/* 短信应用ID: 短信SdkAppId在 [短信控制台] 添加应用后生成的实际SdkAppId，示例如1400006666 */
		$req->SmsSdkAppId = SMS_APPID;
		/* 短信签名内容: 使用 UTF-8 编码，必须填写已审核通过的签名，签名信息可登录 [短信控制台] 查看 */
		$req->SignName = '驰广信息';
		/* 下发手机号码，采用 E.164 标准，+[国家或地区码][手机号]
		 * 示例如：+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
		$req->PhoneNumberSet = array( "+86$tel" );
		/* 国际/港澳台短信 SenderId: 国内短信填空，默认未开通，如需开通请联系 [sms helper] */
		$req->SenderId = "";
		/* 用户的 session 内容: 可以携带用户侧 ID 等上下文信息，server 会原样返回 */
		$req->SessionContext = "xxx";
		/* 模板 ID: 必须填写已审核通过的模板 ID。模板ID可登录 [短信控制台] 查看 */
		$req->TemplateId = "1334636";
		/* 模板参数: 若无模板参数，则设置为空*/
		$req->TemplateParamSet = array( (string) $code, '5' );

		// 通过client对象调用SendSms方法发起请求。注意请求方法名与请求对象是对应的
		// 返回的resp是一个SendSmsResponse类的实例，与请求对象对应
		$resp = $client->SendSms( $req );

		$status_set = $resp->getSendStatusSet()[0] ?? '';
		if ( empty( $status_set ) ) {
			throw new TencentCloudSDKException( 'return_empty', '接口返回为空' );
		}

		if ( 'Ok' !== $status_set->Code ) {
			throw new TencentCloudSDKException( 'error', $status_set->Message );
		}

		// 录入 WP 的瞬存
		set_transient( 'lpcn_user_sms_code_' . $tel, $code, 300 );

		return true;
	} catch ( TencentCloudSDKException $e ) {
		return new WP_Error( $e->getErrorCode(), $e->getMessage() );
	}
}

/**
 * 验证短信验证码
 *
 * @param string $tel
 * @param string $code
 *
 * @return bool|\WP_Error
 */
function check_sms_code( string $tel, string $code ): bool|WP_Error {
	$db_code = get_transient( 'lpcn_user_sms_code_' . $tel );
	if ( empty( $db_code ) ) {
		return false;
	}

	return (int) $code === (int) $db_code;
}

/**
 * 通过用户 ID 登录用户
 */
function login_by_user_id( $user_id ): bool {
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	// 需要在登录成功后设置此 Cookie 以绕过 ols 的缓存
	setcookie( '_lscache_vary', 'abc', time() + ( 365 * 24 * 60 * 60 ), '/' );

	return true;
}
