<?php
/**
 * Plugin Name: 为升级LitePress页面提供相关API
 * Description: 为升级LitePress页面提供相关API
 * Author: LitePress团队
 * Author URI: https://litepress.cn/
 * Version: 1.0.0
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Apply;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use LitePress\WP_Http\WP_Http;
use WP_Error;
use LitePress\Framework\Framework;

$apply_site_list = get_site_option( 'lp_apply_site', array() );


// 为后台加上站点统计
if ( is_admin() ) {
	$site     = '';
	$lp_count = 0;
	$wp_count = 0;
	foreach ( $apply_site_list as $k => $v ) {

		/**$check = strstr($k, 'haoziwl');
		 * if ($check) {
		 * unset($apply_site_list[$k]);
		 * }*/

		if ( $v == 1 ) {
			$site_type = 'LitePress';
			$lp_count ++;
		} else {
			$site_type = 'WordPress';
			$wp_count ++;
		}
		$site .= $k . ' --> ' . $site_type . '<br>';
	}

	/*update_site_option('lp_apply_site', $apply_site_list);*/
	$site .= '合计：' . count( $apply_site_list ) . '个站点，其中LitePress站点有：' . $lp_count . '个，WordPress站点有：' . $wp_count . '个。';

	// 建立设置
	Framework::createSection( $prefix ?? 'litepress', array(
		'id'     => 'apply',
		'title'  => 'Apply',
		'icon'   => 'fa fa-code',
		'fields' => array(
			array(
				'type'    => 'content',
				'content' => $site,
			),
		)
	) );
}

add_action( 'rest_api_init', '\LitePress\Apply\lp_apply_register_rest_route' );
function lp_apply_register_rest_route() {
	register_rest_route( 'lp/', 'apply', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => '\LitePress\Apply\lp_apply',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'lp/', 'exit', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => '\LitePress\Apply\lp_exit',
		'permission_callback' => '__return_true',
	) );
}

function lp_apply( WP_REST_Request $request ): WP_REST_Response {
	global $apply_site_list;

	$params  = $request->get_params();
	$request = request( $params['site'] );

	if ( is_array( $request ) ) {

		$remote_site = $request['body'];
		$apply_site  = md5( $params['site'] );

		if ( $request['response']['code'] == 404 ) {
			$args = array(
				'code' => 1,
				'msg'  => '未找到验证文件，请检查！',
			);

			return new WP_REST_Response( $args );
		}

		// 去除可能的换行符、空字符
		$remote_site = str_replace( array( ' ', "\r", "\n", "\t" ), '', $remote_site );

		if ( $remote_site == $apply_site ) {

			if ( $apply_site_list[ $params['site'] ] ?? 0 ) {
				$args = array(
					'code' => 1,
					'msg'  => '此站点已提交，请勿重复提交！',
				);

				return new WP_REST_Response( $args );
			}

			$apply_site_list[ $params['site'] ] = 1;
			update_site_option( 'lp_apply_site', $apply_site_list );

			$args = array(
				'code' => 0,
				'msg'  => '验证成功！站点已提交。',
			);

		} else {
			$args = array(
				'code' => 1,
				'msg'  => '文件内容不正确，请检查！',
			);

		}

	} else {
		$args = array(
			'code' => 1,
			'msg'  => '请求出错，请检查站点是否允许本站访问！',
		);

	}

	return new WP_REST_Response( $args );
}

function lp_exit( WP_REST_Request $request ): WP_REST_Response {

	$params  = $request->get_params();
	$request = request( $params['site'] );

	$apply_site_list = get_site_option( 'lp_apply_site', array() );

	if ( is_array( $request ) ) {

		$remote_site = $request['body'];

		if ( $request['response']['code'] == 404 ) {
			$args = array(
				'code' => 1,
				'msg'  => '未找到验证文件，请检查！',
			);

			return new WP_REST_Response( $args );
		}

		if ( $remote_site == 'exit' ) {

			if ( $apply_site_list[ $params['site'] ] ?? 0 ) {

				if ( $apply_site_list[ $params['site'] ] == 'exit' ) {
					$args = array(
						'code' => 1,
						'msg'  => '此站点已退出，无需重复退出！',
					);
				} else {
					$apply_site_list[ $params['site'] ] = 'exit';
					update_site_option( 'lp_apply_site', $apply_site_list );

					$args = array(
						'code' => 0,
						'msg'  => '退出成功！感谢您体验LitePress。',
					);
				}

			} else {
				$args = array(
					'code' => 1,
					'msg'  => '此站点不存在于系统中，无需退出！',
				);

			}


		} else {
			$args = array(
				'code' => 1,
				'msg'  => '文件内容不正确，请检查！',
			);

		}

	} else {
		$args = array(
			'code' => 1,
			'msg'  => '请求出错，请检查站点是否允许本站访问！',
		);

	}

	return new WP_REST_Response( $args );
}

function request( $site ): WP_Error|array {
	$http = new WP_Http();

	return $http->get( $site . '/lp-check.txt' );
}
