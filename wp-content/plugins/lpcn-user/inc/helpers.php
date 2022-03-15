<?php

namespace LitePress\User\Inc;

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
