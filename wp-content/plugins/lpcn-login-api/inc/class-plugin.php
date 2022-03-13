<?php

namespace LitePress\Login_Api;


use WP_REST_Request;
use WP_REST_Response;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_action( 'rest_api_init', function () {
			register_rest_route( 'lpcn/user', 'login', array(
				'methods'  => 'POST',
				'callback' => array( self::get_instance(), 'login' ),
			) );

			register_rest_route( 'lpcn/user', 'register', array(
				'methods'  => 'POST',
				'callback' => array( self::get_instance(), 'register' ),
			) );
		} );
	}

	private function tcaptcha_check( $Ticket, $Randstr ): bool {
		$CaptchaAppId = CAPTCHA_ID;
		$AppSecretKey = CAPTCHA_KEY;
		$secretId     = Q_CLOUD_ACCESS_Key;
		$secretKey    = Q_CLOUD_SECRET_Key;

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

	public function login( WP_REST_Request $request ): WP_REST_Response {
		$username         = $request->get_param( 'username' );
		$password         = $request->get_param( 'password' );
		$remember         = (int) $request->get_param( 'remember' );
		$tcaptcha_ticket  = $request->get_param( 'tcaptcha-ticket' );
		$tcaptcha_randstr = $request->get_param( 'tcaptcha-randstr' );

		if ( empty( $username ) || empty( $password ) ) {
			return $this->error( '账号密码不能为空' );
		}

		if ( empty( $tcaptcha_ticket ) || empty( $tcaptcha_randstr ) ) {
			return $this->error( '必须完成滑块验证才可登录' );
		}

		if ( ! $this->tcaptcha_check( $tcaptcha_ticket, $tcaptcha_randstr ) ) {
			return $this->error( '验证码错误' );
		}

		$login_data['user_login']    = $username;
		$login_data['user_password'] = $password;
		$login_data['remember']      = (bool) $remember;

		$user_verify = wp_signon( $login_data, false );
		if ( is_wp_error( $user_verify ) ) {
			return $this->error( '用户名或者密码错误！' );
		}

		// 需要在登录成功后设置此 Cookie 以绕过 ols 的缓存
		setcookie( '_lscache_vary', 'abc', time() + ( 365 * 24 * 60 * 60 ), '/' );

		return $this->success( '登录成功' );
	}

	private function success( string $message ): WP_REST_Response {
		$data = array(
			'code'    => 0,
			'message' => $message,
		);

		return new WP_REST_Response( $data );
	}

	private function error( string $error ): WP_REST_Response {
		$data = array(
			'code'  => 0,
			'error' => $error,
		);

		return new WP_REST_Response( $data );
	}

}
