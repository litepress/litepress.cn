<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Alipay extends WC_Payment_Gateway {

	const GATEWAY_URL         = 'https://openapi.alipay.com/gateway.do';
	const GATEWAY_SANDBOX_URL = 'https://openapi.alipaydev.com/gateway.do';
	const GATEWAY_ID          = 'alipay';

	protected static $log_enabled = false;
	protected static $log         = false;
	protected static $refund_id;

	protected $current_currency;
	protected $multi_currency_enabled;
	protected $supported_currencies;
	protected $charset;
	protected $pay_notify_result;
	protected $refundable_status;
	protected $is_pay_handler = false;

	public function __construct( $init_hooks = false ) {
		$active_plugins         = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$is_wpml                = in_array( 'woocommerce-multilingual/wpml-woocommerce.php', $active_plugins, true );
		$multi_currency_options = 'yes' === get_option( 'icl_enable_multi_currency' );

		/* translators: In Chinese, can simply be translated as 支付宝. Mentionning "China" in English and other languages to make it clear to international customers this is NOT a crossborder payment method. */
		$this->title                  = __( 'Alipay China', 'woo-alipay' );
		$this->method_title           = __( 'Alipay by Woo Alipay', 'woo-alipay' );
		$this->charset                = strtolower( get_bloginfo( 'charset' ) );
		$this->id                     = self::GATEWAY_ID;
		$this->description            = $this->get_option( 'description' );
		$this->method_description     = __( 'Alipay is a simple, secure and fast online payment method.', 'woo-alipay' );
		$this->exchange_rate          = $this->get_option( 'exchange_rate' );
		$this->current_currency       = get_option( 'woocommerce_currency' );
		$this->multi_currency_enabled = $is_wpml && $multi_currency_options;
		$this->supported_currencies   = array( 'RMB', 'CNY' );
		$this->order_button_text      = __( 'Pay with Alipay', 'woo-alipay' );
		$this->order_title_format     = $this->get_option( 'order_title_format' );
		$this->order_prefix           = $this->get_option( 'order_prefix' );
		$this->has_fields             = false;
		$this->form_submission_method = ( 'yes' === $this->get_option( 'form_submission_method' ) );
		$this->notify_url             = WC()->api_request_url( 'WC_Alipay' );
		$this->supports               = array(
			'products',
			'refunds',
		);

		self::$log_enabled = ( 'yes' === $this->get_option( 'debug', 'no' ) );

		if ( ! in_array( $this->charset, array( 'gbk', 'utf-8' ), true ) ) {
			$this->charset = 'utf-8';
		}

		$this->setup_form_fields();
		$this->init_settings();

		if ( $init_hooks ) {
			// Add save gateway options callback
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ), 10, 0 );
			// Add test connexion ajax callback
			add_action( 'wp_ajax_woo_alipay_test_connection', array( $this, 'test_connection' ), 10, 0 );

			if ( $this->is_wooalipay_enabled() ) {
				$this->description = $this->title;

				// Check alipay response to see if payment is complete
				add_action( 'woocommerce_api_wc_alipay', array( $this, 'check_alipay_response' ), 10, 0 );
				// Remember the refund info at creation for later use
				add_action( 'woocommerce_create_refund', array( $this, 'remember_refund_info' ), 10, 2 );
				// Put the order on hol and add redirection form on receipt page
				add_action( 'woocommerce_receipt_alipay', array( $this, 'receipt_page' ), 10, 1 );

				// Stricter user sanitation
				add_filter( 'sanitize_user', array( $this, 'sanitize_user_strict' ), 10, 3 );
			}

			$this->validate_settings();
		}
	}

	/*******************************************************************
	 * Public methods
	 *******************************************************************/

	public function is_available() {
		$is_available = ( 'yes' === $this->enabled ) ? true : false;

		if ( $this->multi_currency_enabled ) {

			if (
				! in_array( get_woocommerce_currency(), $this->supported_currencies, true ) &&
				! $this->exchange_rate
			) {
				$is_available = false;
			}
		} elseif (
			! in_array( $this->current_currency, $this->supported_currencies, true ) &&
			! $this->exchange_rate
		) {
			$is_available = false;
		}

		return $is_available;
	}

	public function process_admin_options() {
		$saved = parent::process_admin_options();

		if ( 'yes' !== $this->get_option( 'debug', 'no' ) ) {

			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}

			self::$log->clear( self::GATEWAY_ID );
		}

		return $saved;
	}

	public function can_refund_order( $order ) {
		$this->refundable_status = array(
			'refundable' => (bool) $order,
			'code'       => ( (bool) $order ) ? 'ok' : 'invalid_order',
			'reason'     => ( (bool) $order ) ? '' : __( 'Invalid order', 'woo-alipay' ),
		);

		if ( $order ) {
			$alipay_transaction_closed = $order->meta_exists( 'alipay_transaction_closed' );

			if ( $alipay_transaction_closed ) {
				$this->refundable_status['refundable'] = false;
				$this->refundable_status['code']       = 'alipay_transaction_closed';
				$this->refundable_status['reason']     = __( 'Alipay closed the transaction ; the refund needs to be handled by other means.', 'woo-alipay' );
			} elseif ( ! $order->get_transaction_id() ) {
				$this->refundable_status['refundable'] = false;
				$this->refundable_status['code']       = 'transaction_id';
				$this->refundable_status['reason']     = __( 'transaction not found.', 'woo-alipay' );
			}
		}

		return $this->refundable_status['refundable'];
	}

	public function remember_refund_info( $refund, $args ) {
		$prefix = '';
		$suffix = '-' . current_time( 'timestamp' );

		if ( is_multisite() ) {
			$prefix = get_current_blog_id() . '-';
		}

		self::$refund_id = str_pad( $prefix . $refund->get_id() . $suffix, 64, '0', STR_PAD_LEFT );
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = new WC_Order( $order_id );

		if ( ! $this->can_refund_order( $order ) ) {

			return new WP_Error( 'error', __( 'Refund failed', 'woocommerce' ) . ' - ' . $this->refund_status['reason'] );
		}

		Woo_Alipay::require_lib( 'refund' );

		$trade_no = $order->get_transaction_id();
		$total    = $this->maybe_convert_amount( $order->get_total() );
		$amount   = $this->maybe_convert_amount( $amount );

		if ( floatval( $amount ) <= 0 || floatval( $amount ) > floatval( $total ) ) {
			return new WP_Error( 'error', __( 'Refund failed - incorrect refund amount (must be more than 0 and less than the total amount of the order).', 'woo-alipay' ) );
		}

		$out_trade_no  = 'WooA' . $order_id . '-' . current_time( 'timestamp' );
		$refund_result = $this->do_refund( $out_trade_no, $trade_no, $amount, self::$refund_id, $reason, $order_id );

		if ( ! $refund_result instanceof WP_Error ) {
			$result = true;

			$order->add_order_note(
				sprintf(
					/* translators: %1$s: Refund amount, %2$s: Payment method title, %3$s: Refund ID */
					__( 'Refunded %1$s via %2$s - Refund ID: %3$s', 'woo-alipay' ),
					$amount,
					$this->method_title,
					'#' . ltrim( self::$refund_id, '0' )
				)
			);
		} else {
			$result = $refund_result;
		}

		self::$refund_id = null;

		return $result;
	}

	public function sanitize_user_strict( $username, $raw_username, $strict ) {

		if ( ! $strict ) {

			return $username;
		}

		return sanitize_user( stripslashes( $raw_username ), false );
	}

	public function validate_settings() {
		$valid = true;

		if ( $this->requires_exchange_rate() && ! $this->exchange_rate ) {
			add_action( 'admin_notices', array( $this, 'missing_exchange_rate_notice' ), 10, 0 );

			$valid = false;
		}

		return $valid;
	}

	public function requires_exchange_rate() {

		return ( ! in_array( $this->current_currency, $this->supported_currencies, true ) );
	}

	public function missing_exchange_rate_notice() {
		$message = __( 'Aliay is enabled, but the store currency is not set to Chinese Yuan.', 'woo-alipay' );
		// translators: %1$s is the URL of the link and %2$s is the currency name
		$message .= __( ' Please <a href="%1$s">set the %2$s against the Chinese Yuan exchange rate</a>.', 'woo-alipay' );

		$page = 'admin.php?page=wc-settings&tab=checkout&section=wc_alipay#woocommerce_alipay_exchange_rate';
		$url  = admin_url( $page );

		echo '<div class="error"><p>' . sprintf( $message, $url, $this->current_currency . '</p></div>' ); // WPCS: XSS OK
	}

	public function get_icon() {

		return '<span class="alipay"></span>';
	}

	public function receipt_page( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( ! $order || $order->is_paid() ) {

			return;
		}

		Woo_Alipay::require_lib( $this->is_mobile() ? 'payment_mobile' : 'payment_computer' );

		if ( $result instanceof WP_Error ) {
			self::log( __METHOD__ . ' Order #' . $order_id . ': ' . wc_print_r( $result ) );
		}

		$total = $this->maybe_convert_amount( $order->get_total() );

		if ( $this->is_mobile() ) {
			$pay_request_builder = new AlipayTradeWapPayContentBuilder();
		} else {
			$pay_request_builder = new AlipayTradePagePayContentBuilder();
		}

		$pay_request_builder->setBody( $this->get_order_title( $order, true ) );
		$pay_request_builder->setSubject( $this->get_order_title( $order ) );
		$pay_request_builder->setTotalAmount( $total );
		$pay_request_builder->setOutTradeNo( 'WooA' . $order_id . '-' . current_time( 'timestamp' ) );

		if ( $this->is_mobile() ) {
			$pay_request_builder->setTimeExpress( '15m' );
		}

		$config          = $this->get_config( $order_id );
		$aop             = new AlipayTradeService( $config );
		$dispatcher_form = false;

		try {
			ob_start();

			if ( $this->is_mobile() ) {
				$aop->wapPay( $pay_request_builder, $config['return_url'], $config['notify_url'] );
			} else {
				$aop->pagePay( $pay_request_builder, $config['return_url'], $config['notify_url'] );
			}

			set_query_var( 'dispatcher_form', ob_get_clean() );

		} catch ( Exception $e ) {
			ob_end_clean();

			$message = ' Caught an exception when trying to generate the Alipay redirection form: ';

			self::log( __METHOD__ . $message . wc_print_r( $e, true ), 'error' );
			$order->update_status( 'failed', $e->getMessage() );
			WC()->cart->empty_cart();
		}

		ob_start();

		Woo_Alipay::locate_template( 'redirected-pay.php', true, true );

		$html = ob_get_clean();

		echo $html; // WPCS: XSS OK
	}

	public function admin_options() {
		echo '<h3>' . esc_html( __( 'Alipay payment gateway by Woo Alipay', 'woo-alipay' ) ) . '</h3>';
		echo '<p>' . esc_html( __( 'Alipay is a simple, secure and fast online payment method.', 'woo-alipay' ) ) . '</p>';

		$url_data    = wp_parse_url( get_home_url() );
		$scheme      = $url_data['scheme'];
		$url_root    = $scheme . '://' . $url_data['host'];
		$wc_callback = $this->notify_url;

		ob_start();

		require_once WOO_ALIPAY_PLUGIN_PATH . 'inc/templates/admin/config-help.php';

		$html = ob_get_clean();

		echo $html; // WPCS: XSS OK
		echo '<table class="form-table woo-alipay-settings">';

		$this->generate_settings_html();

		ob_start();

		require_once WOO_ALIPAY_PLUGIN_PATH . 'inc/templates/admin/gateway-test.php';

		$html = ob_get_clean();

		echo $html; // WPCS: XSS OK

		echo '</table>';
	}

	public function check_alipay_response() {
		$response_data                   = $_POST; // @codingStandardsIgnoreLine
		$out_trade_no                    = filter_input( INPUT_POST, 'out_trade_no', FILTER_SANITIZE_STRING );
		$response_app_id                 = filter_input( INPUT_POST, 'app_id', FILTER_SANITIZE_STRING );
		$trade_status                    = filter_input( INPUT_POST, 'trade_status', FILTER_SANITIZE_STRING );
		$transaction_id                  = filter_input( INPUT_POST, 'trade_no', FILTER_SANITIZE_STRING );
		$response_total                  = filter_input( INPUT_POST, 'total_amount', FILTER_SANITIZE_STRING );
		$fund_bill_list                  = stripslashes( filter_input( INPUT_POST, 'fund_bill_list', FILTER_SANITIZE_STRING ) );
		$needs_reply                     = false;
		$error                           = false;
		$out_trade_no_parts              = explode( '-', str_replace( 'WooA', '', $out_trade_no ) );
		$order_id                        = absint( array_shift( $out_trade_no_parts ) );
		$order                           = wc_get_order( $order_id );
		$config                          = $this->get_config( $order_id );
		$response_data['fund_bill_list'] = stripslashes( $response_data['fund_bill_list'] );
		$order_total                     = $this->maybe_convert_amount( $order->get_total() );
		$total_amount_check              = ( $order_total === $response_total );
		$response_app_id_check           = ( $response_app_id === $config['app_id'] );
		$order_check                     = ( $order instanceof WC_Order );

		Woo_Alipay::require_lib( 'check_notification' );

		$aop                    = new AlipayTradeService( $config );
		$result_check_signature = $aop->check( $response_data );

		self::log( __METHOD__ . ' Alipay response raw data: ' . wc_print_r( $response_data, true ) );

		if ( $order_check && $result_check_signature && $response_app_id_check && $total_amount_check ) {

			if ( 'TRADE_FINISHED' === $trade_status || 'TRADE_SUCCESS' === $trade_status ) {
				$needs_reply = true;

				add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'valid_order_statuses_for_payment' ), 10, 1 );

				if ( $order->needs_payment() ) {
					self::log( __METHOD__ . ' Found order #' . $order_id );
					$order->payment_complete( wc_clean( $transaction_id ) );
					$order->add_order_note( __( 'Alipay payment completed', 'woo-alipay' ) );
					WC()->cart->empty_cart();
				} else {
					$order->add_order_note( __( 'Alipay notified the payment was successful but the order was already paid for. Please double check that the payment was recorded properly.', 'woo-alipay' ) );
				}

				remove_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'valid_order_statuses_for_payment' ), 10 );

				if ( 'TRADE_FINISHED' === $trade_status ) {
					$order->update_meta_data( 'alipay_transaction_closed', true );
					$order->save_meta_data();
				}
			} elseif ( 'TRADE_CLOSED' === $trade_status ) {
				$needs_reply = true;

				add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'valid_order_statuses_for_payment' ), 10, 1 );

				if ( $order->needs_payment() ) {
					$order->add_order_note( __( 'Alipay closed the transaction and the order is no longer valid for payment.', 'woo-alipay' ) );
					$this->order_cancel( $order );
					self::log( __METHOD__ . ' Found order #' . $order_id . ' and changed status to "cancelled".', 'error' );
				}

				remove_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'valid_order_statuses_for_payment' ), 10 );
				$order->update_meta_data( 'alipay_transaction_closed', true );
				$order->save_meta_data();
			} elseif ( 'WAIT_BUYER_PAY' === $trade_status ) {
				$order->add_order_note( __( 'Alipay notified it is waiting for payment.', 'woo-alipay' ) );
			}
		} else {
			$error = __( 'Invalid Alipay response: ', 'woo-alipay' );

			if ( $order_check ) {

				if ( ! $response_app_id_check ) {
					$error .= 'mismatched_app_id';
				} elseif ( ! $result_check_signature ) {
					$error .= 'invalid_response_signature';
				} elseif ( ! $total_amount_check ) {
					$error .= 'invalid_response_total_amount';
				}

				$order->update_status( 'failed', $error );
				self::log( __METHOD__ . ' Found order #' . $order_id . ' and changed status to "failed".', 'error' );
			} else {
				self::log( __METHOD__ . ' Alipay error - Order not found after payment.', 'error', true );

				if ( $response_app_id_check && $result_check_signature ) {

					if ( 'TRADE_SUCCESS' === $trade_status ) {
						$refund_result = $this->do_refund(
							$out_trade_no,
							$transaction_id,
							$response_total,
							str_pad( 'WooA' . current_time( 'timestamp' ), 64, '0', STR_PAD_LEFT ),
							__( 'Woo Alipay error: WooCommerce Order not found.', 'woo-alipay' )
						);

						if ( ! $refund_result instanceof WP_Error ) {
							$message  = ' Missing order #' . $order_id;
							$message .= ', Alipay transaction #' . $transaction_id . ' successfully refunded.';

							self::log( __METHOD__ . $message, 'info', true );
						} else {
							$message  = ' Missing order #' . $order_id;
							$message .= ', Alipay transaction #' . $transaction_id . ' could not be refunded.';
							$message .= " Reason: \n";
							$message .= 'there was an error while trying to automatically refund the order.';
							$message .= ' Error details: ' . wc_print_r( $refund_result );

							self::log( __METHOD__ . $message, 'error', true );
							do_action(
								'wooalipay_orphan_transaction_notification',
								$order_id,
								$transaction_id,
								WC_Log_Handler_File::get_log_file_path( $this->id ),
								'auto_refund_error'
							);
						}
					} elseif ( 'TRADE_CLOSED' === $trade_status || 'TRADE_FINISHED' === $trade_status ) {
						$message  = ' Missing order #' . $order_id;
						$message .= ', Alipay transaction #' . $transaction_id . ' could not be refunded.';
						$message .= " Reason: \n";
						$message .= 'Alipay already closed the transaction.';
						$message .= ' Alipay response raw data: ' . wc_print_r( $response_data );

						self::log( __METHOD__ . $message, 'error', true );
						do_action(
							'wooalipay_orphan_transaction_notification',
							$order_id,
							$transaction_id,
							WC_Log_Handler_File::get_log_file_path( $this->id ),
							'transaction_closed'
						);
					}
				}
			}
		}

		if ( $needs_reply ) {
			echo ( ! $error ) ? 'success' : 'fail'; // WPCS: XSS OK
		}

		exit();
	}

	public function valid_order_statuses_for_payment( $statuses ) {
		$statuses[] = 'on-hold';

		return $statuses;
	}

	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}

	public function test_connection() {

		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( $_POST['nonce'], '_woo_alipay_test_nonce' )
		) {
			$error = new WP_Error( __METHOD__, __( 'Invalid parameters', 'wp-weixin' ) );

			wp_send_json_error( $error );
		} else {
			$result = $this->execute_dummy_query();

			if ( $result ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( $result );
			}
		}

		wp_die();
	}

	/*******************************************************************
	 * Protected methods
	 *******************************************************************/

	protected function is_wooalipay_enabled() {
		$alipay_options = get_option( 'woocommerce_alipay_settings' );

		return ( 'yes' === $alipay_options['enabled'] );
	}

	protected function order_hold( $order ) {

		if ( 'pending' === $order->get_status() ) {
			$updated = $order->update_status( 'on-hold' );

			if ( ! $updated ) {

				return new WP_Error( __METHOD__, __( 'Update status event failed.', 'woocommerce' ) );
			}
		}

		return true;
	}

	protected function order_cancel( $order ) {

		if ( 'on-hold' === $order->get_status() ) {
			$updated = $order->update_status( 'cancel' );

			if ( ! $updated ) {

				return new WP_Error( __METHOD__, __( 'Update status event failed.', 'woocommerce' ) );
			}
		}

		return true;
	}

	protected function setup_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'woo-alipay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Alipay', 'woo-alipay' ),
				'default' => 'no',
			),
			'title'       => array(
				'title'   => __( 'Checkout page title', 'woo-alipay' ),
				'type'    => 'text',
				'default' => __( 'Alipay', 'woo-alipay' ),
			),
			'description' => array(
				'title'   => __( 'Checkout page description', 'woo-alipay' ),
				'type'    => 'textarea',
				'default' => __( 'Pay via Alipay (Mainland China, incl. Hong Kong and Macau). If you are unable to pay with an Mainland China Alipay account, please select a different payment method.', 'woo-alipay' ),
			),
			'appid'       => array(
				'title'       => __( 'Alipay App ID', 'woo-alipay' ),
				'type'        => 'text',
				'description' => __( 'The App ID found in Alipay Open Platform', 'woo-alipay' ),
			),
			'public_key'  => array(
				'title'       => __( 'Alipay public key', 'woo-alipay' ),
				'type'        => 'textarea',
				'description' => __( 'The Alipay public key generated in the Alipay Open Platform ("支付宝公钥").', 'woo-alipay' ),
			),
			'private_key' => array(
				'title'       => __( 'Alipay Merchant application private key', 'woo-alipay' ),
				'type'        => 'textarea',
				'description' => __( 'The private key generated with the provided Alipay tool application or the <code>openssl</code> command line.<br/>
This key is secret and is not recorded in Alipay Open Platform - <strong>DO NOT SHARE THIS VALUE WITH ANYONE</strong>.', 'woo-alipay' ),
			),
			'sandbox'     => array(
				'title'       => __( 'Sandbox', 'woo-alipay' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable sandbox mode', 'woo-alipay' ),
				'default'     => 'no',
				/* translators: %s: URL */
				'description' => sprintf( __( 'Run Alipay in sandbox mode, with the settings found in %1$s.', 'woo-alipay' ), '<a href="https://openhome.alipay.com/platform/appDaily.htm" target="__blank">https://openhome.alipay.com/platform/appDaily.htm</a>' ),
			),
			'debug'       => array(
				'title'       => __( 'Debug log', 'woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce' ),
				'default'     => 'no',
				/* translators: %s: URL */
				'description' => sprintf( __( 'Log Alipay events inside %s Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'woo-alipay' ), '<code>' . WC_Log_Handler_File::get_log_file_path( $this->id ) . '</code>' ),
			),
		);

		if ( ! in_array( $this->current_currency, $this->supported_currencies, true ) ) {
			$description = sprintf(
				// translators: %1$s is the currency
				__( 'Set the %1$s against Chinese Yuan exchange rate <br/>(1 %1$s = [field value] Chinese Yuan)', 'woo-alipay' ),
				$this->current_currency
			);

			$this->form_fields['exchange_rate'] = array(
				'title'       => __( 'Exchange Rate', 'woo-alipay' ),
				'type'        => 'number',
				'description' => $description,
				'css'         => 'width: 80px;',
				'desc_tip'    => true,
			);
		}
	}

	protected function get_config( $order_id = 0 ) {
		$order  = ( 0 === $order_id ) ? false : new WC_Order( $order_id );
		$config = array(
			'app_id'               => $this->get_option( 'appid' ),
			'merchant_private_key' => $this->get_option( 'private_key' ),
			'notify_url'           => $this->notify_url,
			'return_url'           => apply_filters( 'woo_alipay_gateway_return_url', ( $order ) ? $order->get_checkout_order_received_url() : get_home_url() ),
			'charset'              => $this->charset,
			'sign_type'            => 'RSA2',
			'gatewayUrl'           => ( 'yes' === $this->get_option( 'sandbox' ) ) ? self::GATEWAY_SANDBOX_URL : self::GATEWAY_URL,
			'alipay_public_key'    => $this->get_option( 'public_key' ),
		);

		return $config;
	}

	protected function execute_dummy_query() {
		Woo_Alipay::require_lib( 'dummy_query' );

		$config      = $this->get_config();
		$aop         = new AlipayTradeService( $config );
		$biz_content = '{"out_trade_no":"00000000000000000"}';
		$request     = new AlipayTradeQueryRequest();

		$request->setBizContent( $biz_content );

		$response = $aop->aopclientRequestExecute( $request );
		$response = $response->alipay_trade_query_response;

		if (
			is_object( $response ) &&
			isset( $response->code, $response->sub_code ) &&
			'40004' === $response->code &&
			'ACQ.TRADE_NOT_EXIST' === $response->sub_code
		) {
			self::log( __METHOD__ . ': ' . 'Dummy query to Alipay successful' );
			return true;
		} else {
			self::log( __METHOD__ . ': ' . wc_print_r( $response, true ) );

			return false;
		}
	}

	protected function do_refund( $out_trade_no, $trade_no, $amount, $refund_id, $reason, $order_id = 0 ) {
		$refund_request_builder = new AlipayTradeRefundContentBuilder();

		$refund_request_builder->setOutTradeNo( $out_trade_no );
		$refund_request_builder->setTradeNo( $trade_no );
		$refund_request_builder->setRefundAmount( $amount );
		$refund_request_builder->setOutRequestNo( $refund_id );
		$refund_request_builder->setRefundReason( esc_html( $reason ) );

		$config   = $this->get_config( $order_id );
		$aop      = new AlipayTradeService( $config );
		$response = $aop->Refund( $refund_request_builder );

		if ( 10000 !== absint( $response->code ) ) {
			self::log( __METHOD__ . ' Refund Error: ' . wc_print_r( $response, true ) );

			$result = new WP_Error( 'error', $response->msg . '; ' . $response->sub_msg );
		} else {
			self::log( __METHOD__ . ' Refund Result: ' . wc_print_r( $response, true ) );

			$result = $response;
		}

		return $result;
	}

	protected function get_order_title( $order, $desc = false ) {
		$title       = get_option( 'blogname' );
		$order_items = $order->get_items();

		if ( $order_items && 0 < count( $order_items ) ) {
			$title = '#' . $order->get_id() . ' ';
			$index = 0;
			foreach ( $order_items as $item_id => $item ) {

				if ( $index > 0 && ! $desc ) {
					$title .= '...';

					break;
				} else {

					if ( 0 < $index ) {
						$title .= '; ';
					}

					$title .= $item['name'];
				}

				$index++;
			}
		}

		$title = str_replace( '%', '', $title );

		if ( $desc && 128 < mb_strlen( $title ) ) {
			$title = mb_substr( $title, 0, 125 ) . '...';
		} elseif ( 256 < mb_strlen( $title ) ) {
			$title = mb_substr( $title, 0, 253 ) . '...';
		}

		return $title;
	}

	protected function is_mobile() {
		$ua = strtolower( $_SERVER['HTTP_USER_AGENT'] );

		if ( strpos( $ua, 'ipad' ) || strpos( $ua, 'iphone' ) || strpos( $ua, 'android' ) ) {

			return true;
		}

		return false;
	}

	protected function maybe_convert_amount( $amount ) {
		$exchange_rate    = $this->get_option( 'exchange_rate' );
		$current_currency = get_option( 'woocommerce_currency' );

		if (
			! in_array( $current_currency, $this->supported_currencies, true ) &&
			is_numeric( $exchange_rate )
		) {
			$amount = (int) ( $amount * 100 );
			$amount = round( $amount * $exchange_rate, 2 );
			$amount = round( ( $amount / 100 ), 2 );
		}

		return number_format( $amount, 2, '.', '' );
	}

	protected static function log( $message, $level = 'info', $force = false ) {

		if ( self::$log_enabled || $force ) {

			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}

			self::$log->log( $level, $message, array( 'source' => self::GATEWAY_ID ) );
		}
	}

}
