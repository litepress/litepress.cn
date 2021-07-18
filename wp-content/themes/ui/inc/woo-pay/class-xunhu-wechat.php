<?php

namespace WCY\Inc\WooPay;

use Exception;
use XH_Wechat_Payment_WC_Payment_Gateway;

if ( class_exists( 'XH_Wechat_Payment_WC_Payment_Gateway' ) ) {
	class Xunhu_Wechat extends XH_Wechat_Payment_WC_Payment_Gateway {

		public function woocommerce_receipt( $order_id ) {
			$wc_order = wc_get_order( $order_id );
			if ( empty( $wc_order ) || $wc_order->is_paid() ) {
				return '';
			}

			$exchange_rate = $this->get_option( 'exchange_rate' );
			if ( $exchange_rate <= 0 ) {
				$exchange_rate = 1;
			}

			try {
				$data = array(
					'mchid'        => $this->get_option( 'mchid' ),
					'out_trade_no' => $order_id . '@' . time(),
					'total_fee'    => round( $wc_order->get_total() * $exchange_rate * 100 ),
					'body'         => $this->get_order_title( $wc_order ),
					'type'         => 'wechat',
					'notify_url'   => XH_Wechat_Payment_URL . '/views/notify.php',
					'nonce_str'    => str_shuffle( time() )
				);

				$private_key = $this->get_option( 'private_key' );
				if ( $this->is_wechat_app() ) {
					$data['redirect_url'] = $this->get_return_url( $wc_order );
					$data['sign']         = $this->generate_xh_hash( $data, $private_key );
					$url                  = $this->get_option( 'tranasction_url' ) . '/pay/cashier';
					$pay_url              = $this->data_link( $url, $data );

					header( "Location:" . htmlspecialchars_decode( $pay_url, ENT_NOQUOTES ) );
					exit;
				}
				$url          = $this->get_option( 'tranasction_url' ) . '/pay/payment';
				$data['sign'] = $this->generate_xh_hash( $data, $private_key );

				$response = $this->http_post( $url, json_encode( $data ) );
				$result   = $response ? json_decode( $response, true ) : null;
				if ( ! $result ) {
					throw new Exception( 'Internal server error', 500 );
				}
				if ( $result['return_code'] != 'SUCCESS' ) {
					throw new Exception( $result['err_msg'] ?? '' );
				}

				$sign = $this->generate_xh_hash( $result, $private_key );
				if ( ! isset( $result['sign'] ) || $sign != $result['sign'] ) {
					throw new Exception( 'Invalid sign!' );
				}

				return $result['code_url'];
			} catch ( Exception $e ) {
				return '';
			}
		}

		private function is_wechat_app() {
			return strripos( $_SERVER['HTTP_USER_AGENT'], 'micromessenger' );
		}

	}
}
