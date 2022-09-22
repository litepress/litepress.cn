<?php
/**
 * 这个文件被PHP的Preload功能执行以实现在PHP启动时预加载WordPress内核代码
 */

error_reporting( E_ERROR );

$directory = new RecursiveDirectoryIterator( __DIR__ );
$fullTree  = new RecursiveIteratorIterator( $directory );
$phpFiles  = new RegexIterator( $fullTree, '/.+((?<!Test)+\.php$)/i', RecursiveRegexIterator::GET_MATCH );

foreach ( $phpFiles as $key => $file ) {
	if (
		stristr( $file[0], 'wp-admin/includes' ) ||
		stristr( $file[0], 'wp-content/' ) ||
		stristr( $file[0], 'Requests.php' ) // 这货会和WP-Cli里面的类冲突
	) {
		continue;
	}

	opcache_compile_file( $file[0] );
}
