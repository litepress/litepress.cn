<?php

namespace LitePress\API\Inc\Api\Themes;

use LitePress\API\Inc\Api\Base;
use LitePress\API\Inc\Service\Themes_Service;
use LitePress\API\Inc\Service\Translations_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Update_Check
 *
 * 该接口用于检查插件及翻译更新
 *
 * @package LitePress\API\Inc\Api\Themes
 */
class Update_Check extends Base {

	public function __construct() {
		register_rest_route( 'themes/v1', 'update-check', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'update_check' ),
		) );
	}

	public function update_check( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_params( $request->get_params() );

		$themes = json_decode( $params['themes'] ?? '[]', true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$args = array(
				'message' => 'themes 字段格式错误，无法解析为 Json',
			);
			$this->error( $args );
		}
		$themes = $themes['themes'] ?? array();

		$translations = json_decode( $params['translations'] ?? '[]', true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$args = array(
				'message' => 'translations 字段格式错误，无法解析为 Json',
			);
			$this->error( $args );
		}

		$themes_service = new Themes_Service();
		$updated_themes = $themes_service->update_check( $themes );

		$translation_projects = $this->prepare_translation_projects( $themes );
		$translations_service = new Translations_Service();
		$updated_translations = $translations_service->update_check(
			$translation_projects,
			$translations,
			'theme',
			'theme'
		);

		$args = array(
			'themes'       => $updated_themes['update'],
			'no_update'    => $updated_themes['no_update'],
			'translations' => $updated_translations,
		);

		return new WP_REST_Response( $args );
	}

	private function prepare_params( array $params ): array {
		$allowed = array(
			'themes',
			'translations'
		);

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	private function prepare_translation_projects( array $themes ): array {
		$data = array();

		foreach ( $themes as $theme ) {
			if ( ! isset( $theme['Template'] ) || empty( $theme['Template'] ) ) {
				continue;
			}

			$data[ $theme['Template'] ] = $theme['Version'] ?? '';
		}

		return $data;
	}

}
