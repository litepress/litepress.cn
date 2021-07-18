<?php
/**
 * Gitea repository implementation
 *
 * @since 3.2.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Repository;

use Required\Traduttore\Repository;

/**
 * Gitea repository class.
 *
 * @since 3.2.0
 */
class Gitea extends Base {
	/**
	 * Gitea API base URL.
	 *
	 * @since 3.2.0
	 */
	public const API_BASE = 'https://api.gitea.com';

	/**
	 * Returns the repository type.
	 *
	 * @since 3.2.0
	 *
	 * @return string Repository type.
	 */
	public function get_type() : string {
		return Repository::TYPE_GITEA;
	}

	/**
	 * Returns the repository host name.
	 *
	 * @since 3.2.0
	 *
	 * @return string Repository host name.
	 */
	public function get_host(): string {
		return 'gitea.com';
	}

	/**
	 * Indicates whether a Gitea repository is publicly accessible or not.
	 *
	 * @since 3.2.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		$visibility = $this->project->get_repository_visibility();

		if ( ! $visibility ) {
			$response = wp_remote_head( self::API_BASE . '/repos/' . $this->get_name() );

			$visibility = 200 === wp_remote_retrieve_response_code( $response ) ? 'public' : 'private';

			$this->project->set_repository_visibility( $visibility );
		}

		return parent::is_public();
	}
}
