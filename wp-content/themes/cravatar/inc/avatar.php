<?php

namespace LitePress\Cravatar\Inc;

use LitePress\Cravatar\Inc\DataObject\Avatar_Status;

function handle_avatar() {
	/**
	 * 输出头像时关闭错误显示，否则可能造成图像格式错误
	 */
	ini_set( 'display_errors', 0 );

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

	// 如果客户端明确表示支持webp则强制返回webp
	//if ( stristr( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) ) {
	//	$current_request_img_ext = 'webp';
	//}

	$current_url = $tmp[0] ?? '';

	$url_area       = explode( '/', $current_url );
	$url_area_count = count( $url_area );

	if ( 'avatar' === ( $url_area[ $url_area_count - 2 ] ?? '' ) ) {
		/**
		 * 处理用户传入的图像参数
		 */
		if ( isset( $_GET['d'] ) ) {
			$default = $_GET['d'];
		} elseif ( isset( $_GET['default'] ) ) {
			$default = $_GET['default'];
		} else {
			$default = '';
		}

		if ( isset( $_GET['s'] ) ) {
			$size = $_GET['s'];
		} elseif ( isset( $_GET['size'] ) ) {
			$size = $_GET['size'];
		} else {
			$size = '';
		}

		if ( isset( $_GET['f'] ) ) {
			$forcedefault = $_GET['f'];
		} elseif ( isset( $_GET['forcedefault'] ) ) {
			$forcedefault = $_GET['forcedefault'];
		} else {
			$forcedefault = '';
		}

		$user_email_hash = $url_area[ $url_area_count - 1 ] ?? '';

		/**
		 * 如果URL未拼接邮箱哈希，则返回默认头像
		 */
		if ( count( $url_area ) < 3 || empty( $user_email_hash ) ) {
			$forcedefault = 'y';
		}

		$user_id = get_user_id_by_hash( $user_email_hash ?? '' );
		$user    = get_user_by( 'ID', $user_id );

		$avatar_filename = '';

		// 当前头像服务的提供者，目前有三个可能的值：cravatar、gravatar、qq，该值会在名为avatar-from的header字段中返回
		$avatar_from = 'cravatar';

		if (
			! empty( $user->user_email ) && empty( $avatar_filename ) && 'y' !== $forcedefault
		) {
			$avatar_filename = um_get_user_avatar_url( $user->ID ?? 0, 400 );
			$avatar_filename = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $avatar_filename );
			$avatar_filename = explode( '?', $avatar_filename )[0] ?? '';
			$avatar_filename = str_replace( '-400x400.', '.', $avatar_filename );

			/**
			 * 如果终极会员返回默认头像，则清空
			 */
			if ( stristr( $avatar_filename, 'default_avatar.jpg' ) ) {
				$avatar_filename = '';
			}
		}

		/**
		 * 在从本地读取头像失败时尝试返回Gravatar头像
		 */
		if (
			(
				empty( $user->user_email ) ||
				empty( $avatar_filename )
			) && 'y' !== $forcedefault
		) {
			$avatar_filename = get_gravatar_to_file( $user_email_hash, $current_query );
			if ( ! empty( $avatar_filename ) ) {
				$avatar_from = 'gravatar';
			}
		}

		/**
		 * 在本地无法读取以及无法从Gravatar读取的情况下尝试读取QQ头像
		 */
		if ( empty( $avatar_filename ) && 'y' !== $forcedefault ) {
			$qq = get_qq_for_hash( $user_email_hash );
			if ( ! empty( $qq ) ) {
				$avatar_filename = get_qqavatar_to_file( $user_email_hash, $qq );
				if ( ! empty( $avatar_filename ) ) {
					$avatar_from = 'qq';
				}
			}
		}


		/**
		 * 不对 QQ 头像检查违规图，成本顶不住，而且考虑到腾讯也有实名认证
		 */
		if ( 'qq' !== $avatar_from ) {
			if ( is_status_for_avatar( $user_email_hash, $avatar_filename, Avatar_Status::BAN, $avatar_from ) ) { // 如果当前图片处于黑名单则返回空字符串
				$avatar_filename = '';
				$default         = 'ban';
			}
		}

		/**
		 * 如果经过上述一串操作后还是没有图像的话就按404处理
		 */
		if ( empty( $avatar_filename ) || false == $avatar_filename ) {
			/**
			 * 如果用户指定返回404的话就终止后续操作直接返回404状态码
			 */
			if ( '404' === $default ) {
				status_header( 404 );
				exit( 0 );
			}

			$avatar_filename = get_default_avatar_filename( $default );
		}

		// 此替换将保证若https配置不当时，不至于返回500
		$avatar_filename = str_replace( 'http://litepress.cn/cravatar/', '', $avatar_filename );
		$info            = getimagesize( $avatar_filename );

		$cache_img_ext   = image_type_to_extension( $info[2], false );
		$fun             = "imagecreatefrom{$cache_img_ext}";
		$img_info        = $fun( $avatar_filename );

		$img_size = $size ?: 80;
		$img_size = (int) ( $img_size > 2000 ? 2000 : $img_size );

		$image_p = imagecreatetruecolor( $img_size, $img_size );
		imageAlphaBlending( $image_p, false );
		imageSaveAlpha( $image_p, true );

		$fun = "image{$current_request_img_ext}";

		/**
		 * 为了防止裁剪后出现白边，所以取最短边
		 */
		$src_img_size = $info[0] < $info[1] ? $info[0] : $info[1];
		imagecopyresampled( $image_p, $img_info, 0, 0, 0, 0, $img_size, $img_size, $src_img_size, $src_img_size );

		// 图片输出时先输出到本地临时文件，再从临时文件读取并输出到浏览器，直接输出的话会卡的一批
		$temp_file = tempnam( sys_get_temp_dir(), 'cravatar' );
		$fun( $image_p, $temp_file );

		$img_type = match ( $current_request_img_ext ) {
			'jpg', 'jpeg' => IMAGETYPE_JPEG,
			'gif' => IMAGETYPE_GIF,
			'webp' => IMAGETYPE_WEBP,
			default => IMAGETYPE_PNG,
		};
		$mime     = image_type_to_mime_type( $img_type );

		header( 'Content-Type:' . $mime );
		header( 'Content-Length:' . filesize( $temp_file ) );
		header( 'Last-Modified:' . gmdate( 'D, d M Y H:i:s', filemtime( $avatar_filename ) ) . ' GMT' );
		header( 'By:' . 'cravatar.cn' );
		header( 'Avatar-From:' . $avatar_from );

		readfile( $temp_file );

		unlink( $temp_file );
		imagedestroy( $image_p );
		imagedestroy( $img_info );

		/**
		 * 如果头像文件名中包含404_avatar_的话意味着是临时文件，需要在最后删除
		 */
		if ( stristr( $avatar_filename, '404_avatar_' ) ) {
			unlink( $avatar_filename );
		}

		exit( 0 );
	}
}

add_action( 'parse_request', 'LitePress\Cravatar\Inc\handle_avatar' );
