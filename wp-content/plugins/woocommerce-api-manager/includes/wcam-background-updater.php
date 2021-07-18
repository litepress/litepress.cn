<?php
/**
 * WooCommerce API Manager Background Updater Class
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB updates in the background.
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

class WCAM_Background_Updater extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wc_am_updater';

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		$dispatched = parent::dispatch();
		$logger     = wc_get_logger();

		if ( is_wp_error( $dispatched ) ) {
			$logger->error( sprintf( 'Unable to dispatch WooCommerce API Manager updater: %s', $dispatched->get_error_message() ), array( 'source' => 'wc_am_db_updates' ) );
		}
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
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
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function
	 *
	 * @return mixed
	 */
	protected function task( $callback ) {
		WCAM()->maybe_define_constant( 'WC_AM_UPDATING', true );

		$logger = wc_get_logger();

		include_once( dirname( __FILE__ ) . '/wcam-update-functions.php' );

		if ( is_callable( $callback ) ) {
			$logger->info( sprintf( 'Running %s callback', $callback ), array( 'source' => 'wc_am_db_updates' ) );
			call_user_func( $callback );
			$logger->info( sprintf( 'Finished %s callback', $callback ), array( 'source' => 'wc_am_db_updates' ) );
		} else {
			$logger->notice( sprintf( 'Could not find %s callback', $callback ), array( 'source' => 'wc_am_db_updates' ) );
		}

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
		$logger->info( 'Data update complete', array( 'source' => 'wc_am_db_updates' ) );
		WC_AM_INSTALL()->update_db_version();
		parent::complete();
	}
}