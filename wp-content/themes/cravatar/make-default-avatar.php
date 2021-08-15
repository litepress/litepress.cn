<?php
/**
 * 这是一个独立执行的PHP脚本文件，用于从Gravatar上批量抓取随机头像
 */

const STORE_PATH = __DIR__ . '/assets/img/default-avatar';

const GRAVATAR = 'http://avatar.ibadboy.net';

/**
 * Gravatar当前支持的默认头像类型（还有404、blank以及mp未列出，因为这仨是固定的非随机头像）
 */
$default_types = array(
	'identicon',
	'monsterid',
	'wavatar',
	'retro',
	'robohash',
);

foreach ( $default_types as $default_type ) {
	/**
	 * 每种类型均构建1000次随机请求，为防止重复，需将结果以内容md5值命名后再存入本地，这样重复的内容会自动覆盖
	 */
	for ( $i = 0; $i < 1000; $i ++ ) {
		/**
		 * 生成头像URL
		 */
		$url = sprintf( '%s/avatar/%s.png?d=%s&f=y&s=400',
			GRAVATAR,
			md5( $i ),
			$default_type
		);

		/**
		 * 下载头像
		 */
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
		$file = curl_exec( $ch );
		curl_close( $ch );

		/**
		 * 以头像内容md5为名称保存头像到本地
		 */
		$filename = sprintf( '%s/%s/%s.png', STORE_PATH, $default_type, md5( $file ) );
		if ( ! file_exists( $filename ) && false !== $file ) {
			$resource = fopen( $filename, 'w' );
			fwrite( $resource, $file );
			fclose( $resource );
		}

		printf( 'over(%s|%d/1000)%s' . PHP_EOL, $default_type, $i, $url );
	}
}
