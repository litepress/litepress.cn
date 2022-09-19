<?php

namespace LitePress\WAMWR\Inc;

function authcode( string $string, string $operation = 'E' ): string {
	$ssl_public  = file_get_contents( DATA_PATH . "/conf/cert_public.key" );
	$ssl_private = file_get_contents( DATA_PATH . "/conf/cert_private.pem" );
	$pi_key      = openssl_pkey_get_private( $ssl_private );
	$pu_key      = openssl_pkey_get_public( $ssl_public );
	if ( false == ( $pi_key || $pu_key ) ) {
		return '证书错误';
	}
	$data = "";
	if ( $operation == 'D' ) {
		openssl_private_decrypt( base64_decode( $string ), $data, $pi_key );
	} else {
		openssl_public_encrypt( $string, $data, $pu_key );
		$data = base64_encode( $data );
	}

	return $data;
}

function generate_rsa() {
	$config = array(
		"digest_alg"       => "sha512",
		"private_key_bits" => 4096,
		"private_key_type" => OPENSSL_KEYTYPE_RSA,
	);
	$res    = openssl_pkey_new( $config );
	if ( ! $res ) {
		return false;
	}
	openssl_pkey_export( $res, $private_key );

	return array(
		'public_key'  => openssl_pkey_get_details( $res )["key"],
		'private_key' => $private_key,
	);
}

function ras_sign( string $input, string $private_key ): string {
	if ( empty( $input ) || empty( $private_key ) ) {
		return '';
	}

	openssl_sign( $input, $sign, $private_key );

	return base64_encode( $sign );
}

function ras_verify( string $data, string $sign, string $public_key ): bool {
	return (bool) openssl_verify( $data, base64_decode( $sign ), $public_key );
}
