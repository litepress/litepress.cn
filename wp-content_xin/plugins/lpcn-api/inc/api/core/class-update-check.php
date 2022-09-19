<?php

namespace LitePress\API\Inc\Api\Core;

use LitePress\API\Inc\Api\Base;
use LitePress\API\Inc\Service\Core_Service;
use LitePress\API\Inc\Service\Translations_Service;
use function LitePress\API\request_wporg;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Update_Check
 *
 * 该接口用于检查核心更新
 *
 * @package LitePress\API\Inc\Api\Plugins
 */
class Update_Check extends Base {

	public function __construct() {
		register_rest_route( 'core/v1', 'version-check', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'version_check' ),
		) );
	}

	public function version_check( WP_REST_Request $request ): WP_REST_Response {
		// 首先对输入数据消毒
		$params = $this->prepare_params( $request->get_params() );

		// 然后排除WP的UA
		$wp_ua_check = ! substr_count( $request->get_header( 'user-agent' ), 'LitePress' );

		// 检查并录入站点
		$apply_site_list = get_site_option( 'lp_apply_site', array() );
		preg_match_all( '/[a-zA-z]+:\/\/[^\s]*/', $request->get_header( 'user-agent' ), $site );

		$site = $site[0][0] ?? '/';

		// 去掉域名结尾斜杠
		$site = rtrim( $site, '/' );

		$lp_check = false;
		$wp_check = true;
		if ( $site ) {
			if ( isset( $apply_site_list[ $site ] ) ) {
				if ( (int) $apply_site_list[ $site ] === 0 ) {
					$wp_check = true;
					$lp_check = false;
				} elseif ( $apply_site_list[ $site ] == 'exit' ) {
					$wp_check = true;
					$lp_check = false;
				} elseif ( (int) $apply_site_list[ $site ] === 1 ) {
					$wp_check = false;
					$lp_check = true;
				}
			} else {
				if ( $wp_ua_check ) {
					$wp_check = true;
					$lp_check = false;

					//$apply_site_list[ $site ] = 0;
					//update_site_option( 'lp_apply_site', $apply_site_list );
				} else {
					$wp_check = false;
					$lp_check = true;

					//$apply_site_list[ $site ] = 1;
					//update_site_option( 'lp_apply_site', $apply_site_list );
				}
			}
		} else if ( ! $wp_ua_check ) {
			$wp_check = false;
			$lp_check = true;
		}

		if ( $wp_check && ! $lp_check ) {
			$request = request_wporg( add_query_arg( $params, '/core/version-check/1.7/' ) );
			if ( is_array( $request ) ) {
				$return = json_decode( $request['body'], true );

				return new WP_REST_Response( $return );
			} else {
				$args = array(
					'message' => '请求出错',
				);
				$this->error( $args );
			}
		}

		// 初始化核心服务
		$core_service = new Core_Service();
		$updated_core = $core_service->update_check( $params );

		// 检查核心的翻译更新
		$translations = json_decode( $params['translations'] ?? '[]', true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$args = array(
				'message' => 'translations 字段格式错误，无法解析为 Json',
			);
			$this->error( $args );
		}

		$translations_service = new Translations_Service();
		$updated_translations = $translations_service->update_check(
			array(
				$params['version'] ?? '',
			),
			$translations,
			'core',
			'core'
		);

		//构建返回参数
		$args = array(
			'offers'       => $updated_core,
			'translations' => $updated_translations,
		);

		return new WP_REST_Response( $args );
	}

	private function prepare_params( array $params ): array {
		$allowed = array(
			'version',
			'php',
			'locale',
			'mysql',
			'local_package',
			'blogs',
			'users',
			'multisite_enabled',
			'initial_db_version',
			'translations',
		);

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	private function prepare_translation_projects( array $plugins ): array {
		$data = array();

		foreach ( $plugins as $plugin ) {
			if ( ! isset( $plugin['TextDomain'] ) || empty( $plugin['TextDomain'] ) ) {
				continue;
			}

			$data[ $plugin['TextDomain'] ] = $plugin['Version'] ?? '';
		}

		return $data;
	}

}
