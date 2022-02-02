<?php

namespace LitePress\WCAPI\Inc\Api\Order;

use LitePress\WCAPI\Inc\Api\Base;
use LitePress\WCAPI\Inc\Service\Order_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Order_Payment
 *
 * 该接口用于检查订单支付状态
 *
 * @package LitePress\WCAPI\Inc\Api\Order_Payment
 */
class Order_Payment extends Base {

	public function __construct() {
		register_rest_route( 'store/v1', 'order_payment', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'order_payment' ),
		) );
	}

	public function order_payment( WP_REST_Request $request ): WP_REST_Response {
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
			if ( ! isset( $theme['Stylesheet'] ) || empty( $theme['Stylesheet'] ) ) {
				continue;
			}

			$data[ $theme['Stylesheet'] ] = $theme['Version'] ?? '';
		}

		return $data;
	}

}
