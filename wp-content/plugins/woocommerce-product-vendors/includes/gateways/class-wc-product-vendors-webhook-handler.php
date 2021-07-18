<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use \PayPal\Api\VerifyWebhookSignature;
use \PayPal\Api\WebhookEvent;
require __DIR__  . '/paypal-php-sdk/autoload.php';

/**
 * Class WC_Product_Vendors_Webhook_Handler.
 * Mass payment is sent via batch payments and they're asyncronous.
 * Therefore notification webhooks are needed to obtain payment
 * information such as if they payment was successful or not.
 *
 * Handles the PayPal Masspay webhooks.
 * @since 2.0.35
 */
class WC_Product_Vendors_Webhook_Handler {
	private $clientID;
	private $clientSecret;
	private $apiContext;
	private $environment;
	private $events;
	private $webhook_id;

	/**
	 * Number of minutes to wait before trying to create a Webhook handler.
	 */
	const WEBHOOK_DEFAULT_WAIT_TIME = 1;

	/**
	 * Name for the option when a Webhook failure occurs.
	 */
	const WEBHOOK_FAILURE_STATE = 'wcpv_webhook_failure_state';

	/**
	 * Constructor.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
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

		$this->webhook_id = get_option( 'wcpv_webhook_id', '' );

		/**
		 * The event types we want to listen for.
		 */
		$this->events = array(
			'PAYMENT.PAYOUTSBATCH.DENIED',
			'PAYMENT.PAYOUTS-ITEM.SUCCEEDED',
		);

		$this->clientID     = $clientID;
		$this->clientSecret = $clientSecret;

		$this->set_api_context();

		add_action( 'woocommerce_api_wc_product_vendors_paypal', array( $this, 'check_for_webhook' ) );

		$this->maybe_create_webhook();
	}

	/**
	 * Sets the API context.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public function set_api_context() {
		$this->apiContext = new \PayPal\Rest\ApiContext( new \PayPal\Auth\OAuthTokenCredential( $this->clientID, $this->clientSecret ) );
		$this->apiContext->setConfig( array( 'mode' => $this->environment ) );
	}

	/**
	 * Gets a list of all registered webhooks.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @return mixed
	 */
	public function get_all_webhooks() {
		return \PayPal\Api\Webhook::getAll( $this->apiContext );
	}

	/**
	 * Delete a specific webhook.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @param string $webhook_id
	 */
	public function delete_webhook( $webhook_id ) {
		$webhook = new \PayPal\Api\Webhook();
		$webhook->setUrl( WC_Product_Vendors_Utils::get_paypal_webhook_notification_url() );
		$webhook_event_types = array();

		// Build the event types.
		foreach ( $this->events as $event ) {
			$webhook_event_types[] = new \PayPal\Api\WebhookEventType( wp_json_encode( array( 'name' => $event ) ) );
		}

		$webhook->setEventTypes( $webhook_event_types );
		$webhook->setId( $webhook_id );

		try {
			$webhook->delete( $this->apiContext );
			WC_Product_Vendors_Logger::log( 'Webhook deleted!' );
		} catch ( Exception $e ) {
			WC_Product_Vendors_Logger::log( 'Webhook could not be deleted!' );
		}
	}

	/**
	 * Maybe create webhook. Only if we don't already have webhook ID.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public function maybe_create_webhook() {
		if ( ! empty( $this->webhook_id ) || false !== get_transient( self::WEBHOOK_FAILURE_STATE ) ) {
			// Transient still exists and it is in the failure state.
			return;
		}

		$this->create_webhook();
	}

	/**
	 * Saves the newly created webhook id.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @param string $id
	 */
	public function save_webhook_id( $id ) {
		update_option( 'wcpv_webhook_id', $id );
		$this->webhook_id = $id;
	}

	/**
	 * Creates webhook. From the docs, site may need to be under SSL to work.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public function create_webhook() {
		$webhook = new \PayPal\Api\Webhook();

		$webhook->setUrl( WC_Product_Vendors_Utils::get_paypal_webhook_notification_url() );

		$webhook_event_types = array();

		// Build the event types.
		foreach ( $this->events as $event ) {
			$webhook_event_types[] = new \PayPal\Api\WebhookEventType( wp_json_encode( array( 'name' => $event ) ) );
		}

		$webhook->setEventTypes( $webhook_event_types );

		// If webhook already exists, it returns error 400.
		try {
			$created_webhook = $webhook->create( $this->apiContext );
			WC_Product_Vendors_Logger::log( 'Webhook Created! ' . $created_webhook->id );
			$this->save_webhook_id( $created_webhook->id );

			// Remove transient from the failure state.
			delete_transient( self::WEBHOOK_FAILURE_STATE );
		} catch ( Exception $e ) {
			// Set transient to the failure state.
			set_transient( self::WEBHOOK_FAILURE_STATE, true, self::WEBHOOK_DEFAULT_WAIT_TIME * MINUTE_IN_SECONDS );

			WC_Product_Vendors_Logger::log( $e->getMessage() );
		}
	}

	/**
	 * Verify the incoming webhook notification to make sure it is legit.
	 * Note that when testing, you cannot use the webhook simulator in the
	 * PayPal developer's account as that does not work due to a bug on their
	 * end.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @param string $request_headers The request headers from PayPal.
	 * @param string $request_body The request body from PayPal.
	 * @return bool
	 */
	public function is_valid_request( $request_headers = null, $request_body = null ) {
		if ( null === $request_headers || null === $request_body ) {
			return false;
		}

		$signatureVerification = new VerifyWebhookSignature();
		$signatureVerification->setAuthAlgo( $request_headers['PAYPAL-AUTH-ALGO'] );
		$signatureVerification->setTransmissionId( $request_headers['PAYPAL-TRANSMISSION-ID'] );
		$signatureVerification->setCertUrl( $request_headers['PAYPAL-CERT-URL'] );
		$signatureVerification->setWebhookId( $this->webhook_id );
		$signatureVerification->setTransmissionSig( $request_headers['PAYPAL-TRANSMISSION-SIG'] );
		$signatureVerification->setTransmissionTime( $request_headers['PAYPAL-TRANSMISSION-TIME'] );

		$webhookEvent = new WebhookEvent();
		$webhookEvent->fromJson( $request_body );
		$signatureVerification->setWebhookEvent( $webhookEvent );

		try {
			$results = $signatureVerification->post( $this->apiContext );

			if ( 'SUCCESS' === $results->getVerificationStatus() ) {
				return true;
			}

			WC_Product_Vendors_Logger::log( 'Webhook Verification: ' . $results->getVerificationStatus() );
			return false;
		} catch ( Exception $e ) {
			WC_Product_Vendors_Logger::log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Check incoming requests for PayPal Webhook data and process them.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public function check_for_webhook() {
		if ( ( 'POST' !== $_SERVER['REQUEST_METHOD'] )
			|| ! isset( $_GET['wc-api'] )
			|| ( 'wc_product_vendors_paypal' !== $_GET['wc-api'] )
		) {
			return;
		}

		$request_body    = file_get_contents( 'php://input' );
		$request_headers = array_change_key_case( $this->get_request_headers(), CASE_UPPER );

		// Validate it to make sure it is legit.
		if ( $this->is_valid_request( $request_headers, $request_body ) ) {
			$this->process_webhook( $request_body );
		} else {
			status_header( 400 );
			exit;
		}
	}

	/**
	 * Gets the incoming request headers. Some servers are not using
	 * Apache and "getallheaders()" will not work so we may need to
	 * build our own headers.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public function get_request_headers() {
		if ( ! function_exists( 'getallheaders' ) ) {
			$headers = array();
			foreach ( $_SERVER as $name => $value ) {
				if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
					$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
				}
			}

			return $headers;
		} else {
			return getallheaders();
		}
	}

	/**
	 * Processes the incoming webhook.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @param string $request_body
	 */
	public function process_webhook( $request_body = null ) {
		WC_Product_Vendors_Logger::log( 'Received webhook from PayPal. ');		
		if ( null === $request_body ) {
			WC_Product_Vendors_Logger::log( 'PayPal Masspay Payout error: received empty response. ');		
			status_header( 400 );
			exit;
		}

		$notification = json_decode( $request_body );
		WC_Product_Vendors_Logger::log( 'Received message: ' . print_r ( $notification, true ) );	

		if ( 'PAYMENT.PAYOUTSBATCH.DENIED' === $notification->event_type ) {
			WC_Product_Vendors_Logger::log( 'PayPal Masspay Batch Payouts Denied: ' . $notification->summary );
			status_header( 400 );
			exit;
		}

		if ( 'PAYMENT.PAYOUTS-ITEM.SUCCEEDED' !== $notification->event_type ) {
			WC_Product_Vendors_Logger::log( 'PayPal Masspay Item Payout unsuccessful: ' . $notification->summary );
			status_header( 400 );
			exit;
		}

		do_action( 'woocommerce_product_vendors_paypal_webhook_trigger', $notification );

		status_header( 200 );
		exit;
	}
}

new WC_Product_Vendors_Webhook_Handler();
