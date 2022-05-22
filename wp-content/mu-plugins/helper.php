<?php
/**
 * Plugin Name: LitePress.cn的帮助函数
 * Description: 一些有用的函数
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Helper;

use DiDom\Document;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;
use WP_Error;
use WP_Http;

/**
 * 通过解析一组category_ids来分析当前产品的类别
 *
 * 类型包括：插件、主题、小程序、块模板
 *
 * @param array $category_ids
 *
 * @return string
 */
function get_product_type_by_category_ids( array $category_ids ): string {
	$type = '';
	foreach ( $category_ids as $category_id ) {
		if ( 15 === (int) $category_id ) {
			$type = 'plugin';
		}
		if ( 17 === (int) $category_id ) {
			$type = 'theme';
		}
	}

	return $type;
}

/**
 * 通过解析一组categories来分析当前产品的类别
 *
 * 类型包括：插件、主题、小程序、块模板
 *
 * @param array $categories
 *
 * @return string
 */
function get_product_type_by_categories( array $categories ): string {
	$category_ids = array();

	foreach ( $categories as $category ) {
		if ( is_array( $category ) ) {
			$category_ids[] = $category['term_id'];
		} else {
			$category_ids[] = $category->term_id;
		}
	}

	return get_product_type_by_category_ids( $category_ids );
}

/**
 * 检查是否存在某个GlotPress项目
 *
 * @param string $slug 项目Slug
 * @param string $type 项目类型：plugin或theme
 *
 * @return bool 存在返回true，否则返回false
 */
function exist_gp_project( string $slug, string $type ): bool {
	global $wpdb;

	$parent_project_id = match ( $type ) {
		'plugin' => 1,
		'theme' => 2,
		default => 0,
	};

	if ( 0 === $parent_project_id ) {
		return false;
	}

	$sql = $wpdb->prepare( 'SELECT id FROM wp_4_gp_projects WHERE slug = %s AND parent_project_id = %s;', $slug, $parent_project_id );

	return ! empty( $wpdb->get_row( $sql ) );
}

/**
 * 从ES中检索一个产品
 *
 * @param string $slug 产品Slug
 * @param string $type 产品类型
 * @param array $fields 要输出的字段
 */
function get_product_from_es( string $slug, string $type, array $fields = array() ) {
	$body = array(
		'query' => array(
			'bool' => array(
				'must' => array(
					array(
						'term' => array(
							'terms.product_cat.slug' => "{$type}s"
						),
					),
					array(
						'term' => array(
							'slug.keyword' => $slug
						),
					),
				),
			),
		),
		'size'  => 10,
	);
	$body = wp_json_encode( $body );

	$request = wp_remote_post(
		'http://localhost:9200/litepresscnstore-post-3/_search' . ( empty( $fields ) ? '' : ( '?_source_includes=' . join( ',', $fields ) ) ),
		[
			'timeout' => 10,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $body,
		]
	);

	if ( is_wp_error( $request ) ) {
		return $request;
	}

	if ( WP_Http::OK !== wp_remote_retrieve_response_code( $request ) ) {
		return new WP_Error( 'response_code_not_ok' );
	}

	$body   = wp_remote_retrieve_body( $request );
	$result = json_decode( $body, true );

	return $result;
}

/**
 * 从ES中批量检索一组产品
 *
 * @param array $slugs 产品 Slug 数组
 * @param string $type 产品类型
 * @param array $fields 要输出的字段
 */
function get_products_from_es( array $slugs, string $type, array $fields = array() ) {
	$body = array(
		'query' => array(
			'bool' => array(
				'minimum_should_match' => 1,
				'should'               => array(
					array(
						'terms' => array(
							'slug.keyword' => $slugs
						)
					)
				),
				'must'                 => array(
					array(
						'term' => array(
							'terms.product_cat.slug' => "{$type}s"
						)
					)
				)
			)
		),
		'size'  => 500,
	);
	$body = wp_json_encode( $body );

	$request = wp_remote_post(
		'http://localhost:9200/litepresscnstore-post-3/_search' . ( empty( $fields ) ? '' : ( '?_source_includes=' . join( ',', $fields ) ) ),
		[
			'timeout' => 10,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $body,
		]
	);

	if ( is_wp_error( $request ) ) {
		return $request;
	}

	if ( WP_Http::OK !== wp_remote_retrieve_response_code( $request ) ) {
		return new WP_Error( 'response_code_not_ok' );
	}

	$body   = wp_remote_retrieve_body( $request );
	$result = json_decode( $body, true );

	return $result;
}

/**
 * 判断字符串是否是或包含中文
 */
function is_chinese( string $str ): bool {
	if ( preg_match( '/[\x{4e00}-\x{9fa5}]/u', $str ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * 压缩 HTML
 *
 * @param $string
 *
 * @return string
 */
function compress_html( $string ): string {
	$string  = str_replace( "\r\n", '', $string ); //清除换行符
	$string  = str_replace( "\n", '', $string ); //清除换行符
	$string  = str_replace( "\t", '', $string ); //清除制表符
	$pattern = array(
		"/> *([^ ]*) *</", //去掉注释标记
		"/[\s]+/", //多个空白字符 -- 置为1个空格
		"/<!--[\\w\\W\r\\n]*?-->/", //<!-- -->注释之间的空白字符 -- 置空
	);
	$replace = array(
		">\\1<",
		" ",
		"",
	);

	return preg_replace( $pattern, $replace, $string );
}

/**
 * 为 HTML 文本切片
 *
 * 切片的依据是 标题、li标签、p标签，他们中包含的元素都会成为一个单独的片
 *
 * @param string $html
 *
 * @return array
 * @throws \Exception
 */
function html_split( string $html ): array {
	$section_strings = array();

	$dom = new Document( $html );

	$body = $dom->find( 'body' );

	foreach ( $body[0]->children() as $node ) {
		// 有一些用作列表的 HTML 标签，对于它们，需要取子元素
		$list_tag = array(
			'ol',
			'ul',
		);
		if ( in_array( $node->getNode()->tagName, $list_tag ) ) {
			foreach ( $node->children() as $child_node ) {
				if ( stristr( $child_node->html(), 'If a database relating to WordPress does not already' ) ) {
					var_dump( $child_node->html() );
					exit;
				}
				$section_strings[] = $child_node->html();
			}
		} else {
			$section_strings[] = $node->html();
		}
	}

	// 进行一次预处理，去掉所有字符串最外侧的 HTML 标签
	foreach ( $section_strings as &$section_string ) {
		if ( preg_match( '|^<\w+[^>]*>([\s\S]*?)</\w+>$|', $section_string, $matches ) ) {
			if ( ! empty( $matches[1] ) ) {
				if ( stristr( $section_string, 'If a database relating to WordPress does not already' ) ) {
					var_dump( $section_string );
					exit;
				}
				$section_string = compress_html( $matches[1] );
			}
		}
	}
	unset( $section_string );

	return $section_strings;
}

/**
 * 对所有来自 w.org 的字符串数据进行预处理（主要是替换各种关键字）
 *
 * @param string $str
 *
 * @return string
 */
function prepare_w_org_string( string $str ): string {
	$items = array(
		'translate.wordpress.org'         => 'litepress.cn/translate',
		'developer.wordpress.org'         => 'litepress.cn/developer',
		'wordpress.org/support/article/'  => 'litepress.cn/support/article/',
		'wordpress.org/support/category/' => 'litepress.cn/support/category/',
		'WordPress'                       => 'LitePress',
	);

	$search  = array();
	$replace = array();

	foreach ( $items as $k => $v ) {
		$search[]  = $k;
		$replace[] = $v;
	}

	return str_replace( $search, $replace, $str );
}

/**
 * 获取 Woo 商品的下载地址
 *
 * @param int $product_id
 *
 * @return string
 */
function get_woo_download_url( int $product_id ): string {
	return home_url( '?woo-free-download=' . $product_id );
}

/**
 * 按照目录结构递归创建目录
 *
 * @param $directory
 */
function create_directory( string $directory ) {
	execute_command( sprintf(
		'mkdir --parents %s 2>/dev/null',
		escapeshellarg( $directory )
	) );
}

/**
 * 执行一个 Shell 命令
 *
 * @param $command
 * @param bool $get_return
 *
 * @return \WP_Error|string|bool
 */
function execute_command( string $command, bool $get_return = false ): WP_Error|string|bool {
	exec( $command, $output, $return_var );

	if ( $return_var ) {
		return new WP_Error( $return_var, '执行命令时出错。', $output );
	}

	return $get_return ? join( "\n", $output ) : true;
}

/**
 * 验证当前用户是否已经通过滑块验证
 */
function check_tncode() {
	if ( ! isset( $_SESSION ) ) {
		session_start();
	}

	return $_SESSION['tncode_check'] ?? false;
}

/**
 * 发送邮件验证码（全局通用）
 */
function send_email_code( string $email ): bool|WP_Error {
	// 生成验证码
	$code = rand( 1000, 9999 );

	$subject = 'LitePress.cn 平台验证码';
	$message = <<<html
你的验证码：{$code}
<br/>
此验证码有效期 5 分钟
html;

	$headers[] = 'From: Cravatar <noreplay@litepress.cn>';
	$headers[] = 'Content-Type: text/html; charset=UTF-8';

	if ( wp_mail( $email, $subject, $message, $headers ) ) {
		// 录入 WP 的瞬存
		set_transient( 'lpcn_user_email_code_' . $email, $code, 300 );

		return true;
	} else {
		return new WP_Error( 'send_email_error', '发送邮件验证码失败' );
	}

}

/**
 * 验证邮箱验证码
 *
 * @param string $email
 * @param string $code
 *
 * @return bool|\WP_Error
 */
function check_email_code( string $email, string $code ): bool|WP_Error {
	$db_code = get_transient( 'lpcn_user_email_code_' . $email );
	if ( empty( $db_code ) ) {
		return false;
	}

	return (int) $code === (int) $db_code;
}

/**
 * 发送短信验证码（登录与注册功能通用）
 */
function send_sms_code( string $tel ): bool|WP_Error {
	try {
		// 生成验证码
		$code = rand( 1000, 9999 );

		$cred = new Credential( Q_CLOUD_ACCESS_Key_2, Q_CLOUD_SECRET_Key_2 );
		// 实例化一个http选项，可选的，没有特殊需求可以跳过
		$httpProfile = new HttpProfile();
		// 配置代理
		$httpProfile->setReqMethod( "GET" );  // post请求(默认为post请求)
		$httpProfile->setReqTimeout( 30 );    // 请求超时时间，单位为秒(默认60秒)
		$httpProfile->setEndpoint( "sms.tencentcloudapi.com" );  // 指定接入地域域名(默认就近接入)

		// 实例化一个client选项，可选的，没有特殊需求可以跳过
		$clientProfile = new ClientProfile();
		$clientProfile->setSignMethod( "TC3-HMAC-SHA256" );  // 指定签名算法(默认为HmacSHA256)
		$clientProfile->setHttpProfile( $httpProfile );

		// 实例化要请求产品(以sms为例)的client对象,clientProfile是可选的
		// 第二个参数是地域信息，可以直接填写字符串ap-guangzhou，支持的地域列表参考 https://cloud.tencent.com/document/api/382/52071#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8
		$client = new SmsClient( $cred, "ap-guangzhou", $clientProfile );

		// 实例化一个 sms 发送短信请求对象,每个接口都会对应一个request对象。
		$req = new SendSmsRequest();

		/* 短信应用ID: 短信SdkAppId在 [短信控制台] 添加应用后生成的实际SdkAppId，示例如1400006666 */
		$req->SmsSdkAppId = SMS_APPID;
		/* 短信签名内容: 使用 UTF-8 编码，必须填写已审核通过的签名，签名信息可登录 [短信控制台] 查看 */
		$req->SignName = '驰广信息';
		/* 下发手机号码，采用 E.164 标准，+[国家或地区码][手机号]
		 * 示例如：+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
		$req->PhoneNumberSet = array( "+86$tel" );
		/* 国际/港澳台短信 SenderId: 国内短信填空，默认未开通，如需开通请联系 [sms helper] */
		$req->SenderId = "";
		/* 用户的 session 内容: 可以携带用户侧 ID 等上下文信息，server 会原样返回 */
		$req->SessionContext = "xxx";
		/* 模板 ID: 必须填写已审核通过的模板 ID。模板ID可登录 [短信控制台] 查看 */
		$req->TemplateId = "1334636";
		/* 模板参数: 若无模板参数，则设置为空*/
		$req->TemplateParamSet = array( (string) $code, '5' );

		// 通过client对象调用SendSms方法发起请求。注意请求方法名与请求对象是对应的
		// 返回的resp是一个SendSmsResponse类的实例，与请求对象对应
		$resp = $client->SendSms( $req );

		$status_set = $resp->getSendStatusSet()[0] ?? '';
		if ( empty( $status_set ) ) {
			throw new TencentCloudSDKException( 'return_empty', '接口返回为空' );
		}

		if ( 'Ok' !== $status_set->Code ) {
			throw new TencentCloudSDKException( 'error', $status_set->Message );
		}

		// 录入 WP 的瞬存
		set_transient( 'lpcn_user_sms_code_' . $tel, $code, 300 );

		return true;
	} catch ( TencentCloudSDKException $e ) {
		return new WP_Error( $e->getErrorCode(), $e->getMessage() );
	}
}

/**
 * 验证短信验证码
 *
 * @param string $tel
 * @param string $code
 *
 * @return bool|\WP_Error
 */
function check_sms_code( string $tel, string $code ): bool|WP_Error {
	$db_code = get_transient( 'lpcn_user_sms_code_' . $tel );
	if ( empty( $db_code ) ) {
		return false;
	}

	return (int) $code === (int) $db_code;
}
