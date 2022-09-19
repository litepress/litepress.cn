<?php
/**
 * WooCommerce API Manager Background Updater Class
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @package     WooCommerce API Manager/Background Updater
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @since       2.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	require_once( 'libraries/wp-async-request.php' );
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	require_once( 'libraries/wp-background-process.php' );
}

class WCAM_Background_API_Resource_Activations_Updater extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wc_am_api_resource_activations_updater';

	/**
	 * Dispatch updater.
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		$dispatched = parent::dispatch();
		$logger     = wc_get_logger();

		if ( is_wp_error( $dispatched ) ) {
			$logger->error( sprintf( 'Unable to dispatch WooCommerce API Manager API Resource Activations updater: %s', $dispatched->get_error_message() ), array( 'source' => 'wc_am_api_resource_activations_update' ) );
		}
	}

	/**
	 * Handle cron healthcheck
	 * Restart the background process if not already running and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();

			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return $this->is_queue_empty() === false;
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		if ( ! is_array( $item ) && ! isset( $item[ 'product_id' ] ) ) {
			return false;
		}

		$product_id = absint( $item[ 'product_id' ] );

		$logger = wc_get_logger();
		$logger->info( 'API Resource Activations update started for product ID# ' . $product_id, array( 'source' => 'wc_am_api_resource_activations_update' ) );

		global $wpdb;

		$current_activations = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_activations' );
		$current_activations = ! empty( $current_activations ) ? $current_activations : 0;

		$data = array(
			'activations_purchased_total' => $current_activations
		);

		$where = array(
			'product_id' => $product_id
		);

		$data_format = array(
			'%d'
		);

		$where_format = array(
			'%d'
		);

		$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$logger = wc_get_logger();
		$logger->info( 'API Resource Activations update completed.', array( 'source' => 'wc_am_api_resource_activations_update' ) );

		parent::complete();
	}
}