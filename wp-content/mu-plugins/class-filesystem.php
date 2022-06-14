<?php

namespace LitePress\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * 封装常用的文件操作方法
 *
 * @package LitePress\Filesystem
 */
class Filesystem {

	/**
	 * 临时目录
	 *
	 * @var string
	 */
	const TMP_DIR = '/tmp/';

	/**
	 * 返回一个唯一的空的临时目录
	 *
	 * 脚本终止时将会将其删除
	 *
	 * @static
	 *
	 * @param string $prefix 可选。目录的前缀例如："avatar"。默认值：空字符串。
	 *
	 * @return string 临时目录的路径
	 */
	public static function temp_directory( string $prefix = '' ): string {
		// 生成唯一文件名
		$tmp_dir = tempnam( self::TMP_DIR, $prefix );

		// 用目录替换该文件
		unlink( $tmp_dir );
		mkdir( $tmp_dir );
		chmod( $tmp_dir, 0777 );

		// 关闭时自动删除此目录
		register_shutdown_function( array( __CLASS__, 'rmdir' ), $tmp_dir );

		return $tmp_dir;
	}

	/**
	 * 解压 Zip 到某个目录
	 *
	 * @static
	 *
	 * @param string $zip_file 要解压的 Zip 文件
	 * @param string $directory 可选。要解压到的目标目录。默认值：临时目录。
	 *
	 * @return string Zip 解压的目录
	 */
	public static function unzip( string $zip_file, string $directory = '' ): string {
		if ( ! $directory ) {
			$directory = self::temp_directory( basename( $zip_file ) );
		}
		$esc_zip_file  = escapeshellarg( $zip_file );
		$esc_directory = escapeshellarg( $directory );

		exec( "unzip -DD {$esc_zip_file} -d {$esc_directory}" );

		// 修复文件的所有权限问题。在目录上设置755，在文件上设置644。
		exec( "chmod -R 755 {$esc_directory}" );
		exec( "find {$esc_directory} -type f -exec chmod 644 {} \;" );

		// 删除不需要的Mac文件。
		exec( "find {$esc_directory} \( -path '*/__MACOSX*' -o -path '*/.DS_Store' \) -delete" );

		return $directory;
	}

	/**
	 * 返回给定目录的所有（可用）文件。
	 *
	 * @static
	 *
	 * @param string $directory 要搜索的目录。
	 * @param bool $recursive 可选。是否递归搜索子目录。默认值: false.
	 * @param string|null $pattern 可选。用于匹配文件的正则表达式。默认值: null.
	 * @param int $depth 可选。递归深度。默认值: -1 (无限).
	 *
	 * @return array 目录中的所有文件（全路径）
	 */
	public static function list_files( string $directory, bool $recursive = false, string $pattern = null, int $depth = - 1 ): array {
		if ( $recursive ) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $directory ),
				RecursiveIteratorIterator::SELF_FIRST
			);

			if ( $depth > - 1 ) {
				$iterator->setMaxDepth( $depth );
			}
		} else {
			$iterator = new \DirectoryIterator( $directory );
		}

		// 按给定的正则表达式过滤文件名
		$filtered = empty( $pattern ) ? $iterator : new RegexIterator( $iterator, $pattern );

		$files = array();
		foreach ( $filtered as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			} elseif ( stristr( $file->getPathname(), '__MACOSX' ) ) {
				continue;
			}

			$files[] = $file->getPathname();
		}

		return $files;
	}

	/**
	 * 递归地强制删除目录。
	 *
	 * @static
	 *
	 * @param string $dir 要删除的目录。
	 *
	 * @return bool 目录是否已删除。
	 */
	public static function rmdir( $dir ): bool {
		if ( trim( $dir, '/' ) ) {
			exec( 'rm -rf ' . escapeshellarg( $dir ) );
		}

		return is_dir( $dir );
	}
}
