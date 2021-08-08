<?php

namespace LitePress\SVN_Browse\Inc;

/**
 * 通过给定的类型和路径输出目录路径html
 *
 * @param string $type
 * @param string $path
 *
 * @return string
 */
function get_current_dir_path_html( string $type, string $path ): string {
	$dir_array = explode( '/', $path );

	// 第一位通常是空的，最后一位是空的或文件名，所以要去掉
	unset( $dir_array[0] );

	$dir_array_count = count( $dir_array );

	$dir_path_html = '<ul>';

	$i = 1;
	foreach ( $dir_array as $dir ) {
		if ( empty( $dir ) ) {
			break;
		}

		if ( $dir_array_count === $i ) {
			break;
		}

		$j = ( $dir_array_count - $i ) - 1;

		if ( 0 !== $j ) {
			$previous = str_repeat( '../', $j );
		} else {
			$previous = './';
		}

		$dir_path_html .= "<li><a href='$previous' one-link-mark='yes'>$dir</a></li>";

		$i ++;
	}

	$dir_path_html .= '</ul>';

	return $dir_path_html;
}
