<?php
/**
 * 公共函数库
 *
 * @package WP_REAL_PERSON_VERIFY
 */

/**
 * 该函数用于输出模板
 *
 * @param string $name 要读取的模板的名字
 * @param bool $orphan 是否是孤儿模板（不继承公共父模板）
 */
function wprpv_get_template( string $name, bool $orphan = false ) {
	if ($orphan) {
		require_once WPRPV_ROOT_PATH . "templates/{$name}";

		return;
	}

	add_action( 'wprpv_sub_tpl', function () use ( $name ) {
		require_once WPRPV_ROOT_PATH . "templates/{$name}";
	} );

	require_once WPRPV_ROOT_PATH . 'templates/parent.php';
}

/**
 * 保存用户上传的图片
 *
 * @param array 图片信息，这个信息应来自$_FILES
 *
 * @return string|WP_Error 成功返回图片的URL，失败返回WP_Error
 */
function wprpv_save_img( array $img_info ) {
	if ( ! file_exists( WPRPV_DATA_DIR ) ) {
		mkdir( WPRPV_DATA_DIR, 0775, true );
	}

	$tmp_file = $img_info['tmp_name'];
	if( ! file_exists( $tmp_file ) || filesize( $tmp_file ) <= 0 ) {
		return new WP_Error( 'file_upload_failed', '文件上传失败，可能是服务器出现问题，请联系管理员确认' );
	}

	$filename = '/' . date( 'Y-m-d-H-m-s', time() ) . '-' . md5( rand() ) . '.png';
	if ( move_uploaded_file( $tmp_file, WPRPV_DATA_DIR . $filename ) ) {
		return WPRPV_DATA_URL . $filename;
	}

	return new WP_Error( 'file_upload_failed', '文件上传失败，可能是服务器出现问题，请联系管理员确认' );
}

/**
 * 限制API接口每小时访问频率
 *
 * @param int $limit 每小时最大访问次数
 *
 * @return bool|WP_Error
 */
function wprpv_api_frequency_limit( int $limit ) {
	session_start();

	if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$_SERVER['REMOTE_ADDR'] = $list[0];
	}

	$session_key = 'wprpv_api_frequency_limit_' . $_SERVER['REMOTE_ADDR'];

	if ( isset( $_SESSION[$session_key] ) ) {
		$data = $_SESSION[$session_key];
		if ( time() < $data['expire'] ) {
			if ( $data['limit'] <= 0 ) {
				return new WP_Error( 'too_many_requests', '您的请求过于频繁' );
			}
			$_SESSION[$session_key]['limit'] -= 1;

		}
	} else {
		$_SESSION[$session_key] = array(
			'limit'  => $limit,
			'expire' => time() + 3600,
		);
	}

	return true;
}

/**
 * 检查当前用户是否已经实名认证
 *
 * @return bool 已实名返回true否则返回false
 */
function wprpv_is_user_real(): bool {
	$name = get_user_meta( get_current_user_id(), 'wprpv_real_name', true );
	if ( ! empty( $name ) ) {
		return true;
	}

	return false;
}