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

	// 第一位通常是空的，所以要去掉
	unset( $dir_array[0] );

	$dir_array_count = count( $dir_array );

	if ( empty( $dir_array[ $dir_array_count ] ) ) {
		unset( $dir_array[ $dir_array_count ] );
		$dir_array_count = count( $dir_array );
	}

	$dir_path_html = '<ul>';

	$i = 1;
	foreach ( $dir_array as $dir ) {
		if ( 'file' === $type ) {
			$j = ( $dir_array_count - $i ) - 1;
		} else {
			$j = $dir_array_count - $i;
		}

		if ( $j > 0 ) {
			$previous = str_repeat( '../', $j );
		} elseif ( 0 === $j ) {
			$previous = './';
		}

		if ( ( 'file' === $type ? - 1 : 0 ) === $j ) {
			$dir_path_html .= "<li>$dir</li>";
		} else {
			$dir_path_html .= "<li><a href='$previous' one-link-mark='yes'>$dir</a></li>";
		}

		$i ++;
	}

	$dir_path_html .= '</ul>';

	return $dir_path_html;
}
