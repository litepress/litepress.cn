<?php

namespace LitePress\Cravatar\Inc;

use LitePress\Cravatar\Inc\DataObject\Avatar_Status;

function handle_avatar() {
	$current_url   = add_query_arg( array() );
	$tmp           = explode( '?', $current_url );
	$current_query = '';
	if ( isset( $tmp[1] ) ) {
		$current_query = $tmp[1];
	}
	$current_url = $tmp[0] ?? '';

	$tmp                     = explode( '.', $current_url );
	$current_request_img_ext = $tmp[1] ?? 'png';
	if ( ! in_array( $current_request_img_ext, array( 'png', 'jpg', 'jpeg', 'git', 'webp' ) ) ) {
		$current_request_img_ext = 'png';
	}
	if ( 'jpg' === $current_request_img_ext ) {
		$current_request_img_ext = 'jpeg';
	}
	$current_url = $tmp[0] ?? '';

	$url_area       = explode( '/', $current_url );
	$url_area_count = count( $url_area );

	if ( 'avatar' === ( $url_area[ $url_area_count - 2 ] ?? '' ) ) {
		$user_email_hash = $url_area[ $url_area_count - 1 ] ?? '';

		$user_id = get_user_id_by_hash( $user_email_hash ?? '' );
		$user    = get_user_by( 'ID', $user_id );

		$avatar_filename = '';
		if ( ! empty( $user->user_email ) ) {
			$avatar_filename = um_get_user_avatar_url( $user->ID ?? 0, 400 );
			$avatar_filename = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $avatar_filename );
			$avatar_filename = explode( '?', $avatar_filename )[0] ?? '';
		}

		/**
		 * 在从本地读取头像失败，或用户主动设置f及forcedefault参数时强制返回默认图的情况下返回默认图
		 */
		if (
			empty( $user->user_email ) ||
			empty( $avatar_filename ) ||
			stristr( $avatar_filename, 'default_avatar.jpg' ) ||
			isset( $_GET['f'] ) ||
			isset( $_GET['forcedefault'] )
		) {
			$avatar_filename = get_gravatar_to_file( $user_email_hash, $current_query );
		}

		if ( is_status_for_avatar( $avatar_filename, Avatar_Status::BAN ) ) { // 如果当前图片处于黑名单则返回空字符串
			$avatar_filename = get_default_avatar_filename();
		}

		$info          = getimagesize( $avatar_filename );
		$cache_img_ext = image_type_to_extension( $info[2], false );
		$fun           = "imagecreatefrom{$cache_img_ext}";
		$img_info      = $fun( $avatar_filename );

		$img_type = match ( $current_request_img_ext ) {
			'jpg', 'jpeg' => IMAGETYPE_JPEG,
			'gif' => IMAGETYPE_GIF,
			default => IMAGETYPE_PNG,
		};
		$mime     = image_type_to_mime_type( $img_type );

		header( 'Content-Type:' . $mime );

		$img_size = $_GET['s'] ?? 80;
		$img_size = (int) ( $img_size > 2000 ? 2000 : $img_size );

		$image_p = imagecreatetruecolor( $img_size, $img_size );
		imageAlphaBlending( $image_p, false );
		imageSaveAlpha( $image_p, true );

		$fun = "image{$current_request_img_ext}";
		imagecopyresampled( $image_p, $img_info, 0, 0, 0, 0, $img_size, $img_size, $info[0], $info[0] );

		// 图片输出时先输出到本地临时文件，再从临时文件读取并输出到浏览器，直接输出的话会卡的一批
		$temp_file = tempnam( sys_get_temp_dir(), 'lavatar' );
		$fun( $image_p, $temp_file );
		readfile( $temp_file );

		unlink( $temp_file );
		imagedestroy( $image_p );
		imagedestroy( $img_info );

		exit( 0 );
	}
}

add_action( 'parse_request', 'LitePress\Cravatar\Inc\handle_avatar' );
