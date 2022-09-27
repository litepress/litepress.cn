<?php
/**
 * 该模板为用户输出头像
 */

use JetBrains\PhpStorm\NoReturn;

// 只有在调试模式下才输出错误详情，防止头像格式错误（比如一些警告信息就会破坏头像输出格式）
if ( isset( $_GET['debug'] ) ) {
	ini_set( 'display_errors', 1 );
} else {
	ini_set( 'display_errors', 0 );
}
// 载入配置文件
require '../../../../cravatar-config.php';

// 定义插件根目录产量
const C_ROOT_PATH = ABSPATH . 'wp-content/plugins/cravatar';

// 尝试连接数据库
$db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
if ( mysqli_connect_error() ) {
	c_die( '数据库连接失败！' );
}

/**
 * ===========================================================================================================
 * 定义一些帮助类函数
 * ===========================================================================================================
 */
function write_log( string $level, string $message, array $data = array() ): bool {
	// 统一在日志里面插入当前访问的头像 URL
	$avatar_url = $_SERVER['HTTP_AVATAR_URL'] ?? '';
	$hash       = $_GET['hash'] ?? '';

	// 去掉 hash 参数（这是由网关加入用来标识用户邮箱哈希的，并非用户传递的参数）
	$avatar_url = str_replace( "&hash={$hash}", '', $avatar_url );
	$avatar_url = str_replace( "hash={$hash}", '', $avatar_url );

	$data['avatar_url'] = 'https://cravatar.cn' . $avatar_url;

	$data = json_encode( $data, JSON_UNESCAPED_UNICODE );
	try {
		$datetime = new DateTime();
		$datetime = $datetime->format( 'Y-m-d\TH:i:s.uP' );
	} catch ( Exception $e ) {
		$datetime = date( "Y-m-d H:i:s" );
	}

	$server = json_encode( $_SERVER, JSON_UNESCAPED_UNICODE );
	file_put_contents(
		LOG_FILE,
		"[$datetime] Cravatar.$level: $message $data $server" . PHP_EOL,
		FILE_APPEND
	);

	return true;
}

/**
 * 终止脚本执行并显示错误信息
 *
 * @param string $message
 * @param array $data
 *
 * @return void
 */
#[NoReturn] function c_die( string $message, array $data = array() ): void {
	// 记录到本地的错误日志
	write_log( 'ERROR', $message, $data );

	// 输出错误信息到浏览器
	header( 'Cache-Control: no-cache' );
	http_response_code( 500 );
	echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cravatar 服务出现致命错误</title>
</head>
<body>
<h1>Cravatar 服务出现致命错误</h1>
<p>本次错误已记录日志，我们将尽快解决。你也可以通过直接通知我们的方式来加快处理进度，QQ群：1046115671（入群后艾特任意管理员即可）</p>
<p>错误详情：<span style="color: red;">{$message}</span></p>
</body>
</html>
HTML;
	exit;
}

/**
 * 发送 HTTP GET 请求
 *
 * @param $url
 *
 * @return bool|string
 */
function http_get( $url ): bool|string {
	$oCurl = curl_init();

	if ( stripos( $url, "https://" ) !== false ) {
		curl_setopt( $oCurl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $oCurl, CURLOPT_SSLVERSION, 1 );
	}
	curl_setopt( $oCurl, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $oCurl, CURLOPT_URL, $url );
	curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, 1 );

	$sContent = curl_exec( $oCurl );
	$aStatus  = curl_getinfo( $oCurl );
	curl_close( $oCurl );

	if ( 200 === intval( $aStatus["http_code"] ) ) {
		return $sContent;
	} else {
		// 如果用户指定查询未匹配时返回404状态的头像，则不记录404状态码的日志
		if ( str_contains( $url, 'd=404' ) && 404 === $aStatus["http_code"] ) {
			return false;
		}

		write_log( 'ERROR', '取源失败', array(
			'source_url' => $url,
			'body'       => $sContent,
			'status'     => $aStatus["http_code"],
		) );

		return false;
	}
}

/**
 * 从本地读取远程图片
 *
 * 如果本地存在缓存则直接返回缓存，如果不存在缓存则从远端下载并进行本地缓存
 *
 * @param string $hash
 * @param string $url
 * @param string $type
 * @param bool $force
 *
 * @return string
 */
function get_remote_image( string $hash, string $url, string $type = 'gravatar', bool $force = false ): string {
	$file_path = "/www/cravatar_cache/$type/$hash.png";

	/**
	 * 不存在缓存或缓存是15天前创建的就从Gravatar获取数据
	 *
	 * 这里缓存时间15天是因为CDN缓存时间为30天，避免CDN回源时命中本地缓存造成数据被缓存60天
	 */
	if ( ! file_exists( $file_path ) || filemtime( $file_path ) < ( time() - 1313280 ) || $force ) {
		$image_data = http_get( $url );
		if ( empty( $image_data ) ) {
			return '';
		}

		if ( ! file_put_contents( $file_path, $image_data ) ) {
			c_die( '保存远程头像到本地失败，可能是没有权限！请联系管理员解决。' );
		}

		// 记录文件MD5信息方便信息审查
		$avatar_hash = md5_file( $file_path );

		// 有部分图片是禁止使用的，比如 QQ 的小企鹅默认头，这里需要手工排除掉这部分图片
		if ( in_array( $avatar_hash, DENY_IMAGE ) ) {
			return '';
		}
	}

	return $file_path;
}

/**
 * ===========================================================================================================
 * 统一处理 Fatal error
 * ===========================================================================================================
 */
set_exception_handler( function ( $exception ) {
	c_die( $exception->getMessage(), array(
		'file' => $exception->getFile(),
		'line' => $exception->getLine()
	) );
} );

$error_handler = set_error_handler( function ( $errno, $errStr, $errFile, $errLine ) {
	c_die( $errStr, array(
		'file' => $errFile,
		'line' => $errLine
	) );
} );

/**
 * ===========================================================================================================
 * 开始处理头像输出逻辑
 * ===========================================================================================================
 */

/**
 * 解析用户请求的 URL
 *
 * URL 组成：https://cravatar.cn/avatar/邮箱 MD5.图像扩展名?查询参数
 * 此处需要提取 MD5 和头像扩展名
 */
$hash           = $_GET['hash'] ?? ''; // 重写后的头像hash地址
$image_ext      = 'png'; // 请求响应的图片扩展名
$url_path_array = explode( '.', $hash );
$image_ext      = $url_path_array[1] ?? 'png';
$image_ext      = in_array( $image_ext, array( 'jpg', 'jpeg', 'png', 'gif' ) ) ? $image_ext : 'png';
$image_ext      = $image_ext === 'jpg' ? 'jpeg' : $image_ext;
$md5            = $url_path_array[0] ?? '';

/**
 * 从 URL Query 读取头像参数
 *
 * 支持的参数列表：
 *
 * s|size:10-2000[默认:80]
 * d|default:404,mp,mm,mystery,identicon,monsterid,wavatar,retro,robohash,blank,用户自定义 URL[默认:显示平台 LOGO]
 * f|forcedefault:y[默认:n]
 */
$size          = 80; // 图片大小
$default       = ''; // 默认图
$force_default = 'n'; // 强制输出默认图

/**
 * 过滤 size 参数
 */
$size = $_GET['s'] ?? $size;
$size = $_GET['size'] ?? $size;
$size = (int) $size;
$size = max( $size, 10 );
$size = min( $size, 2000 );

/**
 * 过滤 default 参数
 *
 * default 不需要详细的过滤规则，因为只要用户不按要求输入就无法正确的匹配输出默认图
 */
$default = $_GET['d'] ?? $default;
$default = $_GET['default'] ?? $default;

/**
 * 过滤 force_default 参数
 */
$force_default = $_GET['f'] ?? $force_default;
$force_default = $_GET['forcedefault'] ?? $force_default;
$force_default = in_array( $force_default, array( 'y', 'yes' ) ) ? $force_default : false;

/**
 * 开始准备要返回的头像文件
 *
 * 读取顺序：Cravatar->Gravatar->QQ->默认头像
 */
// 头像图片路径（无论通过何种方式获取头像，头像的本地路径都必须保存在这个变量里，后续输出时便只输出此路径的图片），如果此变量始终为空，则会在脚本最后输出默认图
$image_path = '';

// 记录头像获取自哪里
$avatar_from = '';

// 尝试检索 Cravatar 头像
if ( 'y' !== $force_default ) {
	$sql = $db->prepare( 'SELECT image_id FROM wp_9_avatar WHERE md5=?' );
	$sql->bind_param( 's', $md5 );
	$sql->execute();
	$sql->bind_result( $image_id );
	$sql->fetch();
	$sql->close();

	if ( ! empty( $image_id ) ) {
		$sql = $db->prepare( 'SELECT meta_value FROM wp_9_postmeta WHERE post_id=? AND meta_key="_wp_attached_file"' );

		$sql->bind_param( 'd', $image_id );
		$sql->execute();
		$sql->bind_result( $image_path );
		$sql->fetch();
		$sql->close();

		if ( ! empty( $image_path ) ) {
			// 需要拼接出完整路径
			$image_path = sprintf( '%swp-content/uploads/sites/%d/%s', ABSPATH, SITE_ID, $image_path );

			$avatar_from = 'cravatar';
		}
	}
}

// 尝试检索 Gravatar 头像
if ( empty( $image_path ) && 'y' !== $force_default ) {
	$url        = "http://gravatar.litepress.cn/avatar/{$md5}.png?s=400&r=g&d=404";
	$image_path = get_remote_image( $md5, $url );
	if ( ! empty( $image_path ) ) {
		$avatar_from = 'gravatar';
	}
}

// 尝试检索 QQ 头像
if ( empty( $image_path ) && 'y' !== $force_default ) {
	/**
	 * 我们需要先尝试从邮箱 MD5 解析出 QQ 号码，之后才能调取到 QQ 头像
	 */

	// 需要判断下传入的邮箱 MD5 是否是32位，如果不是则不进行查询
	if ( strlen( $md5 ) == 32 ) {
		// 计算出当前的邮箱 MD5 存储在哪个表中
		$table = 'email_hash_' . ( hexdec( substr( $md5, 0, 10 ) ) ) % 5000 + 1;

		$conn = mysqli_connect( LOW_DB_HOST, LOW_DB_USER, LOW_DB_PASSWORD, LOW_DB_NAME );

		$sql   = "select qq from {$table} where md5='{$md5}';";
		$query = mysqli_query( $conn, $sql );
		if ( ! is_bool( $query ) ) {
			$row = mysqli_fetch_array( $query, MYSQLI_ASSOC );

			if ( isset( $row['qq'] ) && ! empty( $row['qq'] ) ) {
				$qq = $row['qq'];
			}
		}
	}

	if ( ! empty( $qq ) ) {
		/**
		 * 有一部分 QQ 头像可能是因为腾讯服务器 BUG 的原因，导致在 100 清晰度下是最佳显示效果，但是在 640 清晰度下则显示出了几十分辨率的屎。
		 *
		 * 比如：
		 * http://q1.qlogo.cn/g?b=qq&nk=1327444568&s=100
		 * http://q1.qlogo.cn/g?b=qq&nk=1327444568&s=640
		 *
		 * 所以这里判断一下，如果通过 640 尺寸获取到的图的实际大小小于 100 则转而获取尺寸为 100 的图
		 *
		 * 2021年11月16日更
		 * 部分 QQ 头没 640 尺寸的图片，这时候尝试获取 100 尺寸的。
		 */
		$url        = "http://q1.qlogo.cn/g?b=qq&nk={$qq}&s=640";
		$image_path = get_remote_image( $md5, $url, 'qq' );

		$width = 0;
		if ( ! empty( $image_path ) ) {
			list( $width, $height, $type, $attr ) = getimagesize( $image_path );
		}

		if ( 100 > (int) $width || empty( $image_path ) ) {
			$url = "http://q1.qlogo.cn/g?b=qq&nk={$qq}&s=100";

			// 强制重新获取 QQ 头
			$image_path = get_remote_image( $md5, $url, 'qq', true );
		}
		if ( ! empty( $image_path ) ) {
			$avatar_from = 'qq';
		}
	}
}

// 检索默认头像
if ( empty( $image_path ) ) {
	$avatar_from = 'default';
	// 如果用户要求直接返回 404 的话就设置 404 状态码并终止执行程序
	if ( '404' === $default ) {
		http_response_code( 404 );
		exit;
	}

	// mp有几个别名，需要特别处理下
	$default = match ( $default ) {
		'mm' => 'mp',
		'mystery' => 'mp',
		default => $default,
	};

	$default_types = array(
		'mp'        => 1,
		'blank'     => 1,
		'identicon' => 1000,
		'monsterid' => 1000,
		'wavatar'   => 1000,
		'retro'     => 1000,
		'robohash'  => 1000,
	);

	$image_path = C_ROOT_PATH . '/assets/image/default-avatar/default.png';

	if ( key_exists( $default, $default_types ) ) {
		$image_path = sprintf( '%s/assets/image/default-avatar/%s/%s.png', C_ROOT_PATH, $default, rand( 1, $default_types[ $default ] ) );
	} elseif ( ! empty( $default ) ) {
		// 只有当用户给定的默认图中包含 .jpg、.jpeg、.gif、.png 时才尝试获取此默认图
		if (
			str_contains( $default, '.jpg' ) ||
			str_contains( $default, '.jpeg' ) ||
			str_contains( $default, '.gif' ) ||
			str_contains( $default, '.png' )
		) {
			$image_path = get_remote_image( md5( $default ), $default, 'custom' );
			if ( empty( $image_path ) ) {
				$image_path = C_ROOT_PATH . '/assets/image/default-avatar/default.png';
				// 需要记录获取远程头像错误的日志
				write_log( 'INFO', '用户指定的默认头像获取失败', array(
					'url' => $default
				) );
			}
		}
	}
}

/**
 * 处理违规图
 *
 * 系统维护了一个数据表，其中按每张原始图片的 MD5 来做为主键索引。每个经过本系统输出的图片都要在表里检索一遍是否是违规图。
 * 对于表中不存在图，则在检查的同时录入数据。系统会有一个 Cron 进程，每天定时检查新图是否违规并更新状态。
 */
// 不对 QQ 头像检查违规图，成本顶不住，而且考虑到腾讯也有实名认证
if ( 'qq' !== $avatar_from && 'default' !== $avatar_from && ! empty( $image_path ) ) {
	$image_hash = md5_file( $image_path );
	$sql        = $db->prepare( 'SELECT status FROM wp_9_avatar_verify WHERE image_md5=?' );
	$sql->bind_param( 's', $image_hash );
	$sql->execute();
	$sql->bind_result( $status );
	$sql->fetch();
	$sql->close();

	if ( is_null( $status ) ) {
		$avatar_url = "https://cravatar.cn/avatar/$md5";
		$sql        = $db->prepare( 'INSERT INTO wp_9_avatar_verify( image_md5, url, type ) VALUES ( ?, ?, ? )' );
		$sql->bind_param( 'sss', $image_hash, $avatar_url, $avatar_from );
		$sql->execute();
		$sql->close();

		// 将违规图检测任务压入任务队列
		$args    = serialize( array(
			'url'        => $avatar_url,
			'image_md5'  => $image_hash,
			'email_hash' => $md5,
		) );
		$start   = time();
		$nextrun = $start + 10;
		// 将时间戳转化成datetime格式
		$start   = date( 'Y-m-d H:i:s', $start );
		$nextrun = date( 'Y-m-d H:i:s', $nextrun );
		$sql     = $db->prepare( 'INSERT INTO wp_cavalcade_jobs( site, hook, args, start, nextrun ) VALUES ( 9, "lpcn_sensitive_content_recognition", ?, ?, ? )' );
		$sql->bind_param( 'sss', $args, $start, $nextrun );

		$sql->execute();
		$sql->close();
	}

	if ( - 1 === (int) $status ) {
		$image_path = C_ROOT_PATH . '/assets/image/default-avatar/ban/1.png';
	}
}

/**
 * 按用户指定的参数生成头像数据
 */
if ( ! file_exists( $image_path ) ) {
	c_die( '原始图片找不到：' . $image_path, array(
		'image_path' => $image_path,
	) );
}
$info = getimagesize( $image_path );

$cache_img_ext = image_type_to_extension( $info[2], false );
if ( empty( $cache_img_ext ) || empty( $info ) ) {
	c_die( '获取图片类型数据失败，可能图片格式有误。', array(
		'image_path' => $image_path,
	) );
}

// 删除 PNG 文件中无效的 iCCP 块，防止 imagecreatefrompng 函数抛出异常
if ( 'png' === $cache_img_ext ) {
	system( "mogrify $image_path" );
}
$fun      = "imagecreatefrom{$cache_img_ext}";
$img_info = $fun( $image_path );

$image_p = imagecreatetruecolor( $size, $size );
imageAlphaBlending( $image_p, false );
imageSaveAlpha( $image_p, true );

$fun = "image{$image_ext}";

/**
 * 为了防止裁剪后出现白边，所以取最短边
 */
$src_img_size = min( $info[0], $info[1] );
imagecopyresampled( $image_p, $img_info, 0, 0, 0, 0, $size, $size, $src_img_size, $src_img_size );

// 图片输出时先输出到本地临时文件，再从临时文件读取并输出到浏览器，直接输出的话会卡的一批
$temp_file = tempnam( sys_get_temp_dir(), 'cravatar' );
$fun( $image_p, $temp_file );

$img_type = match ( $image_ext ) {
	'jpg', 'jpeg' => IMAGETYPE_JPEG,
	'gif' => IMAGETYPE_GIF,
	'webp' => IMAGETYPE_WEBP,
	default => IMAGETYPE_PNG,
};
$mime     = image_type_to_mime_type( $img_type );

header( 'Content-Type:' . $mime );
header( 'Content-Length:' . filesize( $temp_file ) );
header( 'Last-Modified:' . gmdate( 'D, d M Y H:i:s', filemtime( $image_path ) ) . ' GMT' );
header( 'By:' . 'cravatar.cn' );
header( 'Avatar-From:' . $avatar_from );

readfile( $temp_file );

unlink( $temp_file );
