<?php

namespace Automattic\WooCommerce\Internal\Admin\ProductReviews;

use WP_Comment_Query;

/**
 * Tweaks the WordPress comments page to exclude reviews.
 */
class ReviewsCommentsOverrides {

	const REVIEWS_MOVED_NOTICE_ID = 'product_reviews_moved';

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'admin_notices',
			function() {
				$this->display_notices();
			}
		);

		add_filter(
			'woocommerce_dismiss_admin_notice_capability',
			function( $default_capability, $notice_name ) {
				return $this->get_dismiss_capability( $default_capability, $notice_name );
			},
			10,
			2
		);

		add_filter(
			'comments_list_table_query_args',
			function( $args ) {
				return $this->exclude_reviews_from_comments( $args );
			}
		);
	}

	/**
	 * Renders admin notices.
	 */
	protected function display_notices() : void {
		$screen = get_current_screen();

		if ( empty( $screen ) || 'edit-comments' !== $screen->base ) {
			return;
		}

		$this->maybe_display_reviews_moved_notice();
	}

	/**
	 * May render an admin notice informing the user that reviews were moved to a new page.
	 *
	 * @return void
	 */
	protected function maybe_display_reviews_moved_notice() : void {
		if ( $this->should_display_reviews_moved_notice() ) {
			$this->display_reviews_moved_notice();
		}
	}

	/**
	 * Checks if the admin notice informing the user that reviews were moved to a new page should be displayed.
	 *
	 * @return bool
	 */
	protected function should_display_reviews_moved_notice() : bool {
		// Do not display if the user does not have the capability  to see the new page.
		if ( ! WC()->call_function( 'current_user_can', Reviews::get_capability() ) ) {
			return false;
		}

		// Do not display if the current user has dismissed this notice.
		if ( WC()->call_function( 'get_user_meta', get_current_user_id(), 'dismissed_' . static::REVIEWS_MOVED_NOTICE_ID . '_notice', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Renders an admin notice informing the user that reviews were moved to a new page.
	 *
	 * @return void
	 */
	protected function display_reviews_moved_notice() : void {
		$dismiss_url = wp_nonce_url(
			add_query_arg(
				[
					'wc-hide-notice' => urlencode( static::REVIEWS_MOVED_NOTICE_ID ),
				]
			),
			'woocommerce_hide_notices_nonce',
			'_wc_notice_nonce'
		);
		?>

		<div class="notice notice-info is-dismissible">
			<p><strong><?php esc_html_e( 'Product reviews have moved!', 'woocommerce' ); ?></strong></p>
			<p><?php esc_html_e( 'Product reviews can now be managed from Products > Reviews.', 'woocommerce' ); ?></p>
			<p class="submit">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product-reviews' ) ); ?>" class="button-primary"><?php esc_html_e( 'Visit new location', 'woocommerce' ); ?></a>
			</p>
			<button type="button" class="notice-dismiss" onclick="window.location = '<?php echo esc_url( $dismiss_url ); ?>';"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'woocommerce' ); ?></span></button>
		</div>

		<?php
	}

	/**
	 * Gets the capability required to dismiss the notice.
	 *
	 * This is required so that users who do not have the manage_woocommerce capability (e.g. Editors) can still dismiss
	 * the notice displayed in the Comments page.
	 *
	 * @param string|mixed $default_capability The default required capability.
	 * @param string|mixed $notice_name The notice name.
	 * @return string
	 */
	protected function get_dismiss_capability( $default_capability, $notice_name ) {
		return self::REVIEWS_MOVED_NOTICE_ID === $notice_name ? Reviews::get_capability() : $default_capability;
	}

	/**
	 * Excludes product reviews from showing in the comments page.
	 *
	 * @param array|mixed $args {@see WP_Comment_Query} query args.
	 * @return array
	 */
	protected function exclude_reviews_from_comments( $args ) : array {

		if ( ! empty( $args['post_type'] ) && 'any' !== $args['post_type'] ) {
			$post_types = (array) $args['post_type'];
		} else {
			$post_types = get_post_types();
		}

		$index = array_search( 'product', $post_types );

		if ( false !== $index ) {
			unset( $post_types[ $index ] );
		}

		if ( ! is_array( $args ) ) {
			$args = [];
		}

		$args['post_type'] = $post_types;

		return $args;
	}

}
