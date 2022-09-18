<?php
/**
 *   加密工具类
 *
 * User: Alexandre Froger
 * Date: 20/01/20
 * Time: 下午9:06
 */

function AESEncrypt($message, $encodingAesKey = '', $appId = '') {
	$key = base64_decode($encodingAesKey . '=');
	$text = AESRandom(16) . pack("N", strlen($message)) . $message . $appid;
	$iv = substr($key, 0, 16);
	$blockSize = 32;
	$textLength = strlen($text);
	$amountToPad = $blockSize - ($textLength % $blockSize);
	
	if (0 === $amountToPad) {
		$amountToPad = $blockSize;
	}

	$padChr = chr($amountToPad);
	$tmp = '';

	for ($i = 0; $i < $amountToPad; $i++) {
		$tmp .= $padChr;
	}

	$text = $text . $tmp; 

	$encrypted = openssl_encrypt(
		$text,
		'AES-256-CBC',
		$key,
		OPENSSL_RAW_DATA |  OPENSSL_ZERO_PADDING,
		$iv
	);

	$encryptMsg = base64_encode($encrypted);

	return $encryptMsg;
}

function AESDecrypt($message, $encodingAesKey = '', $appId = '') {
	$key = base64_decode($encodingAesKey);
	$ciphertext = base64_decode($message);
	$iv = substr($key, 0, 16);

	$decrypted = openssl_decrypt(
		$ciphertext,
		'AES-256-CBC',
		$key,
		OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
		$iv
	);

	$pad = ord(substr($decrypted, -1));

	if ($pad < 1 || $pad > 32) {
		$pad = 0;
	}

	$result = substr($decrypted, 0, (strlen($decrypted) - $pad));

	if (strlen($result) < 16) {

		return false;
	}

	$content = substr($result, 16);
	$lenList = unpack("N", substr($content, 0, 4));
	$len = $lenList[1];
	$content = substr($content, 4, $len);
	$fromAppId  = substr($content, $len + 4);

	if ($fromAppId != $appId) {

		return false;
	}

	return $content;
}

function AESRandom($length = 16) {
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
	$str = "";

	for ($i = 0; $i < $length; $i++)  {  
		$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
	} 

	return $str;
}