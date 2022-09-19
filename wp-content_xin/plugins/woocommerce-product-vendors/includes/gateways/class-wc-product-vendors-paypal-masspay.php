<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require __DIR__  . '/paypal-php-sdk/autoload.php';

/**
 * PayPal Mass Payments Class.
 *
 * Mass Payments by PayPal to mass pay vendor commission.
 *
 * @category Payout
 * @package  WooCommerce Product Vendors/PayPal Masspay
 * @version  2.0.35
 * @since 2.0.0
 */
class WC_Product_Vendors_PayPal_MassPay implements WC_Product_Vendors_Vendor_Payout_Interface {
	private $clientID;
	private $clientSecret;
	private $apiContext;
	private $environment;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array of objects $commissions
	 * @return bool
	 */
	public function __construct() {
		$this->environment = get_option( 'wcpv_vendor_settings_paypal_masspay_environment' );

		if ( 'sandbox' === $this->environment ) {
			$clientID     = get_option( 'wcpv_vendor_settings_paypal_masspay_client_id_sandbox' );
			$clientSecret = get_option( 'wcpv_vendor_settings_paypal_masspay_client_secret_sandbox' );
		} else {
			$clientID     = get_option( 'wcpv_vendor_settings_paypal_masspay_client_id_live' );
			$clientSecret = get_option( 'wcpv_vendor_settings_paypal_masspay_client_secret_live' );
		}

		if ( empty( $clientID ) || empty( $clientSecret ) ) {
			return;
		}

		$this->clientID     = $clientID;
		$this->clientSecret = $clientSecret;

		$this->set_api_context();

		return true;
	}

	/**
	 * Sets the API context
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function set_api_context() {
		$this->apiContext = new \PayPal\Rest\ApiContext( new \PayPal\Auth\OAuthTokenCredential( $this->clientID, $this->clientSecret ) );
		$this->apiContext->setConfig( array( 'mode' => $this->environment ) );

		return true;
	}

	/**
	 * Sends payment 
	 *
	 * @since 2.0.0
	 * @version 2.0.35
	 * @return string $batch_id
	 */
	public function do_payment( $commissions ) {
		if ( empty( $commissions ) ) {
			//throw new Exception( __( 'No commission to pay', 'woocommerce-product-vendors' ) );

			return;
		}

		$payouts = new \PayPal\Api\Payout();

		$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();

		$senderBatchHeader->setSenderBatchId( uniqid() )->setEmailSubject( __( 'You have earned a commission', 'woocommerce-product-vendors ' ) );

		// add each commission item
		foreach ( $commissions as $commission ) {
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $commission->vendor_id );

			if ( empty( $vendor_data ) || empty( $vendor_data['paypal'] ) ) {
				continue;
			}

			$senderItem = new \PayPal\Api\PayoutItem();

			$note = '';
			$note .= sprintf( __( 'You have earned a commission from %s for order #%s.', 'woocommerce-product-vendors' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $commission->order_id );

			$note = apply_filters( 'wcpv_commission_paypal_masspay_vendor_note', $note, $commission );

			// setSenderItemId can only have max 30 characters. oid = order_id ven = vendor_id
			$senderItem->setRecipientType( 'Email' )
			    ->setNote( $note )
			    ->setReceiver( $vendor_data['paypal'] )
			    ->setSenderItemId( 'oid_' . $commission->order_id . '_ven_' . $commission->vendor_id )
			    ->setAmount( new \PayPal\Api\Currency( '{
			    	"value":"' . $commission->total_commission_amount . '",
					"currency":"' . get_woocommerce_currency() . '"
				}' ) );

			$payouts->setSenderBatchHeader( $senderBatchHeader )->addItem( $senderItem );
			WC_Product_Vendors_Logger::log( 'Sending payout to ' . $senderItem->getReceiver() . '. Payout amount: ' . $senderItem->getAmount() );				
		}

		$results = json_decode( $payouts->create( null, $this->apiContext ) );

		if ( is_wp_error( $results ) ) {
			throw new Exception( $results->get_error_message() );
		}

		return $results->batch_header->payout_batch_id;
	}

	/**
	 * Gets the batch status from PayPal
	 *
	 * @access public
	 * @since 2.0.6
	 * @version 2.0.6
	 * @return object $result
	 */
	public function get_batch_status( $batch_id ) {
		return \PayPal\Api\Payout::get( $batch_id, $this->apiContext );
	}
}
