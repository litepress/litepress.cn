<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ();
} // Exit if accessed directly
class XH_Wechat_Payment_WC_Payment_Gateway extends WC_Payment_Gateway {
	private static $_instance;

	public function __construct() {
		$this->id         = XH_Wechat_Payment_ID;
		$this->icon       = XH_Wechat_Payment_URL . '/images/logo/wechat.png';
		$this->has_fields = true;

		$this->method_title       = __( 'Wechat Payment', XH_Wechat_Payment );
		$this->method_description = __( 'Helps to add Wechat payment gateway that supports the features including QR code payment, OA native payment, exchange rate.', XH_Wechat_Payment );

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->enabled      = $this->get_option( 'enabled' );
		$this->instructions = $this->get_option( 'instructions' );

		$this->init_form_fields();
		$this->init_settings();


		add_filter( 'woocommerce_payment_gateways', array( $this, 'woocommerce_add_gateway' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'woocommerce_receipt' ), 10, 1 );
		add_action( "wp_ajax_xh_xunhupay_order_status", array( $this, 'wechat_order_is_paid' ) );
		add_action( "wp_ajax_nopriv_xh_xunhupay_order_status", array( $this, 'wechat_order_is_paid' ) );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'enabled'         => array(
				'title'   => __( 'Enable/Disable', XH_Wechat_Payment ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable/Disable the wechat payment', XH_Wechat_Payment ),
				'default' => 'no',
				'section' => 'default'
			),
			'title'           => array(
				'title'    => __( 'Payment gateway title', XH_Wechat_Payment ),
				'type'     => 'text',
				'default'  => __( 'Wechat Payment', XH_Wechat_Payment ),
				'desc_tip' => true,
				'css'      => 'width:400px',
				'section'  => 'default'
			),
			'description'     => array(
				'title'    => __( 'Payment gateway description', XH_Wechat_Payment ),
				'type'     => 'textarea',
				'default'  => __( 'QR code payment or OA native payment, credit card', XH_Wechat_Payment ),
				'desc_tip' => true,
				'css'      => 'width:400px',
				'section'  => 'default'
			),
			'instructions'    => array(
				'title'       => __( 'Instructions', XH_Wechat_Payment ),
				'type'        => 'textarea',
				'css'         => 'width:400px',
				'description' => __( 'Instructions that will be added to the thank you page.', XH_Wechat_Payment ),
				'default'     => '',
				'section'     => 'default'
			),
			'mchid'           => array(
				'title'       => __( 'MCHID', XH_Wechat_Payment ),
				'type'        => 'text',
				'css'         => 'width:400px',
				'default'     => '2ddfa6b4325542979d55f90ffe0216bd',
				'section'     => 'default',
				'description' => 'Mchid申请地址：https://pay.xunhuweb.com'
			),
			'private_key'     => array(
				'title'       => __( 'Private Key', XH_Wechat_Payment ),
				'type'        => 'text',
				'css'         => 'width:400px',
				'default'     => 'ceb557e114554c56ad665b52f1cb3d8b',
				'section'     => 'default',
				'description' => '签约教程：https://pay.xunhuweb.com/371.html'
			),
			'tranasction_url' => array(
				'title'       => __( 'Transaction_url', XH_Wechat_Payment ),
				'type'        => 'text',
				'css'         => 'width:400px',
				'default'     => 'https://admin.xunhuweb.com',
				'section'     => 'default',
				'description' => ''
			),
			'exchange_rate'   => array(
				'title'       => __( 'Exchange Rate', XH_Wechat_Payment ),
				'type'        => 'text',
				'default'     => '1',
				'description' => __( 'Set the exchange rate to RMB. When it is RMB, the default is 1', XH_Wechat_Payment ),
				'css'         => 'width:400px;',
				'section'     => 'default'
			)
		);
	}

	/**
	 * @return XHWepayezAlipayWC
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function woocommerce_add_gateway( $methods ) {
		$methods [] = $this;

		return $methods;
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return array(
				'result'   => 'success',
				'redirect' => wc_get_checkout_url()
			);
		}
		if ( ( method_exists( $order, 'is_paid' ) ? $order->is_paid() : in_array( $order->get_status(), array(
			'processing',
			'completed'
		) ) ) ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		}
		$exchange_rate = $this->get_option( 'exchange_rate' );
		if ( $exchange_rate <= 0 ) {
			$exchange_rate = 1;
		}
		if ( $this->is_app_client() && ! $this->is_wechat_app() ) {
			$data         = array(
				'mchid'        => $this->get_option( 'mchid' ),
				'out_trade_no' => $order_id . '@' . time(),
				'total_fee'    => round( $order->get_total() * $exchange_rate * 100 ),
				'body'         => $this->get_order_title( $order ),
				'type'         => 'wechat',
				'notify_url'   => XH_Wechat_Payment_URL . '/views/notify.php',
				'trade_type'   => 'WAP',
				'wap_url'      => $http_type . $_SERVER['SERVER_NAME'],
				'wap_name'     => '迅虎网络',
				'nonce_str'    => str_shuffle( time() )
			);
			$private_key  = $this->get_option( 'private_key' );
			$url          = $this->get_option( 'tranasction_url' ) . '/pay/payment';
			$redirct_url  = $this->get_return_url( $order );
			$data['sign'] = $this->generate_xh_hash( $data, $private_key );
			$response     = $this->http_post( $url, json_encode( $data ) );
			$result       = $response ? json_decode( $response, true ) : null;
			if ( ! $result ) {
				throw new Exception( 'Internal server error', 500 );
			}
			$sign = $this->generate_xh_hash( $result, $private_key );
			if ( ! isset( $result['sign'] ) || $sign != $result['sign'] ) {
				throw new Exception( __( 'Invalid sign!', XH_Wechat_Payment ), 40029 );
			}
			if ( $result['return_code'] != 'SUCCESS' ) {
				throw new Exception( $result['err_msg'], $result['err_code'] );
			}

			return array(
				'result'   => 'success',
				'redirect' => XH_Wechat_Payment_URL . '/h5.php?url=' . urlencode( $result['mweb_url'] ) . '&redirect=' . urlencode( $redirct_url ) . '&total_fee=' . $order->get_total() . '&order_id=' . $data['out_trade_no']
			);
		}

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);

	}

	public function is_app_client() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$u = strtolower( $_SERVER['HTTP_USER_AGENT'] );
		if ( $u == null || strlen( $u ) == 0 ) {
			return false;
		}

		preg_match( '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/', $u, $res );

		if ( $res && count( $res ) > 0 ) {
			return true;
		}

		if ( strlen( $u ) < 4 ) {
			return false;
		}

		preg_match( '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/', substr( $u, 0, 4 ), $res );
		if ( $res && count( $res ) > 0 ) {
			return true;
		}

		$ipadchar = "/(ipad|ipad2)/i";
		preg_match( $ipadchar, $u, $res );
		if ( $res && count( $res ) > 0 ) {
			return true;
		}

		return false;
	}

	private function is_wechat_app() {
		return strripos( $_SERVER['HTTP_USER_AGENT'], 'micromessenger' );
	}

	public function get_order_title( $order, $limit = 98 ) {
		$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
		$title    = "#{$order_id}";

		$order_items = $order->get_items();
		if ( $order_items ) {
			$qty = count( $order_items );
			foreach ( $order_items as $item_id => $item ) {
				$title .= "|{$item['name']}";
				break;
			}
			if ( $qty > 1 ) {
				$title .= '...';
			}
		}

		$title = mb_strimwidth( $title, 0, $limit, 'utf-8' );

		return apply_filters( 'xh-payment-get-order-title', $title, $order );
	}

	/**
	 * 签名方法
	 *
	 * @param array $datas
	 * @param string $hashkey
	 */
	public static function generate_xh_hash( array $datas, $hashkey ) {
		ksort( $datas );
		reset( $datas );

		$pre = array();
		foreach ( $datas as $key => $data ) {
			if ( is_null( $data ) || $data === '' ) {
				continue;
			}
			if ( $key == 'sign' ) {
				continue;
			}
			$pre[ $key ] = $data;
		}

		$arg   = '';
		$qty   = count( $pre );
		$index = 0;

		foreach ( $pre as $key => $val ) {
			$arg .= "$key=$val";
			if ( $index ++ < ( $qty - 1 ) ) {
				$arg .= "&";
			}
		}

		return strtoupper( md5( $arg . '&key=' . $hashkey ) );
	}

	/**
	 * http_post传输
	 *
	 * @param array $url
	 * @param string $jsonStr
	 */
	public function http_post( $url, $jsonStr ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonStr );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen( $jsonStr )
			)
		);
		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		return $response;
	}

	public function woocommerce_receipt( $order_id ) {
		$http_type = ( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) ) ? 'https://' : 'http://';
		$wc_order  = wc_get_order( $order_id );
		if ( ! $wc_order ) {
			?>
            <script type="text/javascript">
                location.href = '<?php echo wc_get_checkout_url();?>';
            </script>
			<?php
			return;
		}
		if ( $wc_order->is_paid() ) {
			?>
            <script type="text/javascript">
                location.href = '<?php echo $this->get_return_url( $wc_order );?>';
            </script>
			<?php
			return;
		}
		$exchange_rate = $this->get_option( 'exchange_rate' );
		if ( $exchange_rate <= 0 ) {
			$exchange_rate = 1;
		}
		try {
			$data        = array(
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
				throw new Exception( $result['msg'] );
			}
			$sign = $this->generate_xh_hash( $result, $private_key );
			if ( ! isset( $result['sign'] ) || $sign != $result['sign'] ) {
				throw new Exception( 'Invalid sign!' );
			}
			$url = $result['code_url'];
			?>
            <script src="<?php echo XH_Wechat_Payment_URL ?>/js/qrcode.js"></script>
            <style type="text/css">
                .pay-weixin-design {
                    display: block;
                    background: #fff; /*padding:100px;*/
                    overflow: hidden;
                }

                .page-wrap {
                    padding: 50px 0;
                    min-height: auto !important;
                }

                .pay-weixin-design #WxQRCode {
                    width: 196px;
                    height: auto
                }

                .pay-weixin-design .p-w-center {
                    display: block;
                    overflow: hidden;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #eee;
                }

                .pay-weixin-design .p-w-center h3 {
                    font-family: Arial, 微软雅黑;
                    margin: 0 auto 10px;
                    display: block;
                    overflow: hidden;
                }

                .pay-weixin-design .p-w-center h3 font {
                    display: block;
                    font-size: 14px;
                    font-weight: bold;
                    float: left;
                    margin: 10px 10px 0 0;
                }

                .pay-weixin-design .p-w-center h3 strong {
                    position: relative;
                    text-align: center;
                    line-height: 40px;
                    border: 2px solid #3879d1;
                    display: block;
                    font-weight: normal;
                    width: 130px;
                    height: 44px;
                    float: left;
                }

                .pay-weixin-design .p-w-center h3 strong span {
                    display: inline-block;
                    font-size: 14px;
                    vertical-align: top;
                }

                .pay-weixin-design .p-w-center h3 strong #img2 {
                    position: absolute;
                    right: 0;
                    bottom: 0;
                }

                .pay-weixin-design .p-w-center h4 {
                    font-family: Arial, 微软雅黑;
                    margin: 0;
                    font-size: 14px;
                    color: #666;
                }

                .pay-weixin-design .p-w-left {
                    display: block;
                    overflow: hidden;
                    float: left;
                }

                .pay-weixin-design .p-w-left p {
                    display: block;
                    width: 196px;
                    background: #00c800;
                    color: #fff;
                    text-align: center;
                    line-height: 2.4em;
                    font-size: 12px;
                }

                .pay-weixin-design .p-w-left img {
                    margin-bottom: 10px;
                }

                .pay-weixin-design .p-w-right {
                    margin-left: 50px;
                    display: block;
                    float: left;
                }
            </style>
            <div class="pay-weixin-design">
                <div class="p-w-center">
                    <h3>
                        <font>支付方式已选择微信支付</font>
                        <strong>
                            <img id="img1" src="<?php echo XH_Wechat_Payment_URL ?>/images/weixin.png">
                            <span>微信支付</span>
                            <img id="img2" src="<?php echo XH_Wechat_Payment_URL ?>/images/ep_new_sprites1.png">
                        </strong>
                    </h3>
                    <h4>通过微信首页右上角扫一扫，或者在“发现-扫一扫”扫描二维码支付。本页面将在支付完成后自动刷新。</h4>

                </div>

                <div class="p-w-left">
                    <div id="wechat_qrcode" style="width: 200px;height: 200px;margin-bottom: 10px;"></div>
                    <p>使用微信扫描二维码进行支付</p>

                </div>

                <div class="p-w-right">

                    <img src="<?php echo XH_Wechat_Payment_URL ?>/images/ep_sys_wx_tip.jpg">
                </div>

            </div>
            <script type="text/javascript">
                (function ($) {
                    function queryOrderStatus() {
                        $.ajax({
                            type: "GET",
                            url: wc_checkout_params.ajax_url,
                            data: {id: <?php print $order_id?>, action: 'xh_xunhupay_order_status'},
                            timeout: 6000,
                            cache: false,
                            dataType: 'json',
                            success: function (data) {
                                if (data && data.status === "paid") {
                                    location.href = '<?php echo $this->get_return_url( $wc_order )?>';
                                    return;
                                }

                                setTimeout(queryOrderStatus, 2000);
                            },
                            error: function () {
                                setTimeout(queryOrderStatus, 2000);
                            }
                        });
                    }

                    setTimeout(function () {
                        queryOrderStatus();
                    }, 3000);
                    var qrcode = new QRCode(document.getElementById("wechat_qrcode"), {
                        width: 200,
                        height: 200
                    });

					<?php if(! empty( $url )){
					?>
                    qrcode.makeCode("<?php print $url?>");
                    queryOrderStatus();
					<?php
					}?>
                })(jQuery);
            </script>
			<?php
		} catch ( Exception $e ) {
			?>
            <ul class="woocommerce-error">
            <li><?php echo $e->getMessage(); ?></li>
            </ul><?php
		}
	}

	/**
	 * url拼接
	 *
	 * @param array $url
	 * @param string $datas
	 */
	public function data_link( $url, $datas ) {
		ksort( $datas );
		reset( $datas );
		$pre = array();
		foreach ( $datas as $key => $data ) {
			if ( is_null( $data ) || $data === '' ) {
				continue;
			}

			$pre[ $key ] = $data;
		}

		$arg   = '';
		$qty   = count( $pre );
		$index = 0;
		foreach ( $pre as $key => $val ) {
			$val = urlencode( $val );
			$arg .= "$key=$val";
			if ( $index ++ < ( $qty - 1 ) ) {
				$arg .= "&amp;";
			}
		}

		return $url . '?' . $arg;
	}

	public function wechat_order_is_paid() {
		$order_id = isset( $_GET['id'] ) ? $_GET['id'] : 0;
		if ( ! $order_id ) {
			echo json_encode( array(
				'status' => 'unpaid'
			) );
			exit;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			echo json_encode( array(
				'status' => 'unpaid'
			) );
			exit;
		}

		if ( ( method_exists( $order, 'is_paid' ) ? $order->is_paid() : in_array( $order->get_status(), array(
			'processing',
			'completed'
		) ) ) ) {
			echo json_encode( array(
				'status' => 'paid'
			) );
			exit;
		}

		echo json_encode( array(
			'status' => 'unpaid'
		) );
		exit;
	}

	public function get_order_id_from_out_trade_no( $out_trade_no ) {
		return substr( $out_trade_no, strlen( $this->get_option( 'prefix' ) ) + 15 );
	}

	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wpautop( wptexturize( $this->instructions ) );
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 *
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		$method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;
		if ( $this->instructions && ! $sent_to_admin && $this->id === $method ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}

}

?>
