<?php

namespace LitePress\Tools;

/**
 * 封装常用的 SVN 操作方法
 *
 * 该类中不包括任何需要 SVN 管理员账号才能调用的功能。
 *
 * @package LitePress\Tools
 */
class SVN {

	/**
	 * 从 URL 获取 SVN 仓库信息
	 *
	 * @static
	 *
	 * @param string $url SVN 仓库 URL。
	 * @param array $options 可选。要传递给 SVN 的选项列表。默认值：空数组。
	 *
	 * @return array {
	 * @type bool|array $result 失败为假。 否则为关联数组。
	 * @type bool|array $errors 是否遇到任何错误或警告。
	 * }
	 */
	public static function info( string $url, array $options = array() ): array {
		$esc_url = escapeshellarg( $url );

		$options[] = 'non-interactive';

		$esc_options = self::parse_esc_parameters( $options );

		$output = self::shell_exec( "svn info $esc_options $esc_url 2>&1" );
		if ( preg_match( '!URL: ' . untrailingslashit( $url ) . '\n!i', $output ) ) {
			$lines  = explode( "\n", $output );
			$result = array_filter( array_reduce(
				$lines,
				function ( $carry, $item ) {
					$pair = explode( ':', $item, 2 );
					if ( isset( $pair[1] ) ) {
						$key           = trim( $pair[0] );
						$carry[ $key ] = trim( $pair[1] );
					} else {
						$carry[] = trim( $pair[0] );
					}

					return $carry;
				},
				array()
			) );
			$errors = false;
		} else {
			$result = false;
			$errors = self::parse_svn_errors( $output );
		}

		return compact( 'result', 'errors' );
	}

	/**
	 * 从 URL 导出 SVN 仓库。
	 *
	 * @static
	 *
	 * @param string $url SVN 仓库 URL。
	 * @param string $destination 要导出到的本地文件夹。
	 * @param array $options 可选。要传递给 SVN 的选项列表。默认值：空数组。
	 *
	 * @return array {
	 * @type bool $result 操作的结果。
	 * @type int $revision 已导出的修订。
	 * @type false|array $errors 是否遇到任何错误或警告。
	 * }
	 */
	public static function export( string $url, string $destination, array $options = array() ): array {
		$options[]   = 'non-interactive';
		$esc_options = self::parse_esc_parameters( $options );

		$esc_url         = escapeshellarg( $url );
		$esc_destination = escapeshellarg( $destination );

		$output = self::shell_exec( "svn export $esc_options $esc_url $esc_destination 2>&1" );
		if ( preg_match( '/Exported revision (?P<revision>\d+)[.]/i', $output, $m ) ) {
			$revision = (int) $m['revision'];
			$result   = true;
			$errors   = false;
		} else {
			$result   = false;
			$revision = false;
			$errors   = self::parse_svn_errors( $output );
		}

		return compact( 'result', 'revision', 'errors' );
	}

	/**
	 * 从 URL 签出 SVN 仓库到本地
	 *
	 * @static
	 *
	 * @param string $url SVN 仓库 URL。
	 * @param string $destination 要签出到的本地文件夹。
	 * @param array $options 可选。要传递给 SVN 的选项列表。默认值：空数组。
	 *
	 * @return array {
	 * @type bool $result 操作的结果。
	 * @type int $revision 已导出的修订。
	 * @type false|array $errors 是否遇到任何错误或警告。
	 * }
	 */
	public static function checkout( string $url, string $destination, array $options = array() ): array {
		$options[]   = 'non-interactive';
		$esc_options = self::parse_esc_parameters( $options );

		$esc_url         = escapeshellarg( $url );
		$esc_destination = escapeshellarg( $destination );

		$output = self::shell_exec( "svn checkout $esc_options $esc_url $esc_destination 2>&1" );
		if ( preg_match( '/Checked out revision (?P<revision>\d+)[.]/i', $output, $m ) ) {
			$revision = (int) $m['revision'];
			$result   = true;
			$errors   = false;
		} else {
			$result   = false;
			$revision = false;
			$errors   = self::parse_svn_errors( $output );
		}

		return compact( 'result', 'revision', 'errors' );
	}

	/**
	 * 更新 SVN 签出。
	 *
	 * @static
	 *
	 * @param string $checkout 要更新的 SVN 签出的路径。
	 * @param array $options 可选。要传递给 SVN 的选项列表。默认值：空数组。
	 *
	 * @return array {
	 * @type bool $result 操作的结果。
	 * @type int $revision 已导出的修订。
	 * @type false|array $errors 是否遇到任何错误或警告。
	 * }
	 */
	public static function up( string $checkout, array $options = array() ): array {
		$options[]   = 'non-interactive';
		$esc_options = self::parse_esc_parameters( $options );

		$esc_checkout = escapeshellarg( $checkout );

		$output = self::shell_exec( "svn up $esc_options $esc_checkout 2>&1" );
		if ( preg_match( '/Updated to revision (?P<revision>\d+)[.]/i', $output, $m ) ) {
			$revision = (int) $m['revision'];
			$result   = true;
			$errors   = false;
		} else {
			$result   = false;
			$revision = false;
			$errors   = self::parse_svn_errors( $output );
		}

		return compact( 'result', 'revision', 'errors' );
	}

	/**
	 * 列出远程 SVN 中的文件。
	 *
	 * @static
	 *
	 * @param string $url SVN 仓库 URL。
	 * @param bool $verbose 可选。是否使用额外元数据详细列出文件。默认值：false。
	 *
	 * @return array|bool 如果不详细，则为文件列表；如果详细，则为包含文件名、日期、文件大小、作者和版本的项目数组。如果出错返回 false
	 */
	public static function ls( string $url, bool $verbose = false ): bool|array {
		$options = array(
			'non-interactive',
			'xml',
		);

		$esc_options = self::parse_esc_parameters( $options );
		$esc_url     = escapeshellarg( $url );

		$output = self::shell_exec( "svn ls $esc_options $esc_url 2>&1" );
		$errors = self::parse_svn_errors( $output );
		if ( $errors ) {
			return false;
		}

		$errors = libxml_use_internal_errors( true );
		$xml    = simplexml_load_string( $output );
		libxml_use_internal_errors( $errors );

		$files = [];
		foreach ( $xml->list->children() as $entry ) {
			$files[] = [
				'revision' => (int) $entry->commit['revision'],
				'author'   => (string) $entry->commit->author,
				'filesize' => (int) $entry->size,
				'date'     => gmdate( 'Y-m-d H:i:s', strtotime( (string) $entry->commit->date ) ),
				'filename' => (string) $entry->name,
				'kind'     => (string) $entry['kind'],
			];
		}

		if ( ! $verbose ) {
			return wp_list_pluck( $files, 'filename' );
		}

		return $files;
	}

	/**
	 * 获取给定修订或修订范围的 SVN 修订。
	 *
	 * @static
	 *
	 * @param string $url SVN 仓库 URL。
	 * @param array|string $revision 可选。要获取有关信息的修订。默认值：HEAD。
	 * @param array $options 可选。传递给 SVN 的选项列表。默认值：空数组。
	 *
	 * @return array {
	 * @type array|false $errors 是否遇到任何错误或警告。
	 * @type array $log SVN 日志数据结构。
	 * }
	 */
	public static function log( string $url, array|string $revision = 'HEAD', array $options = array() ): array {
		$options[]           = 'non-interactive';
		$options[]           = 'verbose';
		$options[]           = 'xml';
		$options['revision'] = is_array( $revision ) ? "{$revision[0]}:{$revision[1]}" : $revision;
		$esc_options         = self::parse_esc_parameters( $options );

		$esc_url = escapeshellarg( $url );

		$output = self::shell_exec( "svn log $esc_options $esc_url 2>&1" );
		$errors = self::parse_svn_errors( $output );

		$log = array();

		/*
		 * 我们在这里使用时髦的字符串修饰来提取 XML，因为它可能已被 SVN 错误截断，或以 SVN 警告为后缀。
		 */
		$xml = substr( $output, $start = stripos( $output, '<?xml' ), $end = ( strripos( $output, '</log>' ) - $start + 6 ) );
		if ( $xml && false !== $start && false !== $end ) {

			$user_errors = libxml_use_internal_errors( true );
			$simple_xml  = simplexml_load_string( $xml );
			libxml_use_internal_errors( $user_errors );

			if ( ! $simple_xml ) {
				$errors[] = "SimpleXML failed to parse input";
			} else {
				foreach ( $simple_xml->logentry as $entry ) {
					$revision = (int) $entry->attributes()['revision'];
					$paths    = array();

					foreach ( $entry->paths->children() as $child_path ) {
						$paths[] = (string) $child_path;
					}

					$log[ $revision ] = array(
						'revision' => $revision,
						'author'   => (string) $entry->author,
						'date'     => strtotime( (string) $entry->date ),
						'paths'    => $paths,
						'message'  => (string) $entry->msg,
					);
				}
			}
		}

		return compact( 'log', 'errors' );
	}

	/**
	 * 解析和转义提供的 SVN 参数以在 CLI 上使用。
	 *
	 * 参数可以作为 [ param ] 或 [ param = value ] 传递，如果参数没有以 - 为前缀，它将根据需要以 1 或 2 个 - 作为前缀。
	 *
	 * @static
	 * @access protected
	 *
	 * @param array $params 提供的参数数组。
	 *
	 * @return string 为 CLI 使用而格式化和转义的参数。
	 */
	protected static function parse_esc_parameters( array $params ): string {
		$result = array();

		foreach ( $params as $key => $value ) {
			$no_parameters = is_numeric( $key );
			if ( $no_parameters ) {
				$key = $value;
			}

			// 如果选项长度超过2个字符，需要在其前面加“-”或“--”。
			if ( ! str_starts_with( $key, '-' ) ) {
				$key = '-' . ( strlen( $key ) > 2 ? '-' : '' ) . $key;
			}

			$result[] = escapeshellarg( $key ) . ( $no_parameters ? '' : ' ' . escapeshellarg( $value ) );
		}

		return implode( ' ', $result );
	}

	/**
	 * 分析SVN输出以检测 SVN 错误并引发异常。
	 *
	 * @static
	 * @access protected
	 *
	 * @param string $output SVN 的输出。
	 *
	 * @return false|array 如果在输出中未检测到错误/警告，则为False；如果检测到，则为包含警告/错误代码/错误消息的数组。
	 */
	protected static function parse_svn_errors( string $output ): bool|array {
		if ( preg_match_all( '!^svn: (?P<warning>warning:)?\s*(?<error_code>[EW]\d+):\s*(?P<error_message>.+)$!im', $output, $messages, PREG_SET_ORDER ) ) {

			$messages = array_map( function ( $item ) {
				return array_filter( $item, 'is_string', ARRAY_FILTER_USE_KEY );
			}, $messages );

			return $messages;
		}

		return false;
	}

	/**
	 * 执行具有“正确”语言环境/语言设置的命令，以便正确处理utf8字符串。
	 *
	 * WordPress.org 使用 en_US.UTF-8 环境。
	 *
	 * @static
	 * @access protected
	 *
	 * @param string $command 要执行的命令。
	 *
	 * @return false|string|null 已执行命令的输出，如果发生错误或命令不产生输出，则为 NULL。
	 */
	protected static function shell_exec( string $command ): bool|string|null {
		return shell_exec( 'export LC_CTYPE="en_US.UTF-8" LANG="en_US.UTF-8"; ' . $command );
	}

}
