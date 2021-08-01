<?php
/**
 * 此文件用于反代ES的搜索API
 *
 * 为保证速度，其是单独加载的不经过WordPress框架。
 */

$data = file_get_contents( 'php://input' );

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, 'http://localhost:9200/litepresscnstore-post-3/_search' );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_POST, 1 );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Content-Length:' . strlen( $data ),
	)
);

$response_str = curl_exec( $ch );
curl_close( $ch );

echo $response_str;
