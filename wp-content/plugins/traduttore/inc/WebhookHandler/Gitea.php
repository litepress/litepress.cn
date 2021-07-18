<?php
/**
 * Gitea webhook handler
 *
 * @since 3.2.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\ProjectLocator;
use Required\Traduttore\Repository;
use Required\Traduttore\Updater;
use WP_Error;
use WP_REST_Response;

/**
 * Gitea webhook handler class.
 *
 * @since 3.2.0
 *
 * @see https://docs.gitea.io/en-us/webhooks/
 */
class Gitea extends Base {
	/**
	 * Permission callback for incoming Gitea webhooks.
	 *
	 * @since 3.2.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool {
		$event_name = $this->request->get_header( 'x-gitea-event' );

		if ( 'push' !== $event_name ) {
			return false;
		}

		$token = $this->request->get_header( 'x-gitea-signature' );

		if ( ! $token ) {
			return false;
		}

		$params  = $this->request->get_params();
		$locator = new ProjectLocator( $params['repository']['html_url'] ?? null );
		$project = $locator->get_project();

		$secret = $this->get_secret( $project );

		$payload_signature = hash_hmac( 'sha256', $this->request->get_body(), $secret );

		return hash_equals( $token, $payload_signature );
	}

	/**
	 * Callback for incoming Gitea webhooks.
	 *
	 * @since 3.2.0
	 *
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback() {
		$params     = $this->request->get_params();

		// We only care about the default branch but don't want to send an error still.
		if ( 'refs/heads/' . $params['repository']['default_branch'] !== $params['ref'] ) {
			return new WP_REST_Response( [ 'result' => 'Not the default branch' ] );
		}

		$locator = new ProjectLocator( $params['repository']['html_url'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		$project->set_repository_name( $params['repository']['full_name'] );
		$project->set_repository_url( $params['repository']['html_url'] );
		$project->set_repository_ssh_url( $params['repository']['ssh_url'] );
		$project->set_repository_https_url( $params['repository']['clone_url'] );
		$project->set_repository_visibility( false === $params['repository']['private'] ? 'public' : 'private' );

		if ( ! $project->get_repository_type() ) {
			$project->set_repository_type( Repository::TYPE_GITEA );
		}

		if ( ! $project->get_repository_vcs_type() ) {
			$project->set_repository_vcs_type( Repository::VCS_TYPE_GIT );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
