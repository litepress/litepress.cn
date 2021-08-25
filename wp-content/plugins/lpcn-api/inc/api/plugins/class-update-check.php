<?php

namespace LitePress\API\Inc\Api\Plugins;

use LitePress\API\Inc\Api\Base;
use LitePress\API\Inc\Service\Plugins_Service;
use LitePress\API\Inc\Service\Translations_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Update_Check
 *
 * 该接口用于检查插件及翻译更新
 *
 * @package LitePress\API\Inc\Api\Plugins
 */
class Update_Check extends Base {

	public function __construct() {
		register_rest_route( 'plugins/v1', 'update-check', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'update_check' ),
		) );
	}

	public function update_check( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_params( $request->get_params() );

		$plugins = json_decode( $params['plugins'] ?? '[]', true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$args = array(
				'message' => 'plugins 字段格式错误，无法解析为 Json',
			);
			$this->error( $args );
		}
		$plugins = $plugins['plugins'] ?? array();

		$translations = json_decode( $params['translations'] ?? '[]', true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$args = array(
				'message' => 'translations 字段格式错误，无法解析为 Json',
			);
			$this->error( $args );
		}

		$plugins_service = new Plugins_Service();
		$updated_plugins = $plugins_service->update_check( $plugins );

		$translation_projects = $this->prepare_translation_projects( $plugins );
		$translations_service = new Translations_Service();
		$updated_translations = $translations_service->update_check(
			$translation_projects,
			$translations,
			'plugin',
			'plugin'
		);

		$args = array(
			'plugins'      => $updated_plugins,
			'translations' => $updated_translations,
		);

		return new WP_REST_Response( $args );
	}

	private function prepare_params( array $params ): array {
		$allowed = array(
			'plugins',
			'translations'
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
