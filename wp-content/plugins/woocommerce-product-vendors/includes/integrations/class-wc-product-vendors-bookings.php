<?php
/**
 * Bookings integration for PV.
 *
 * @package WooCommerce Product Vendors/Bookings
 */

/**
 * Class WC_Product_Vendors_Bookings
 */
class WC_Product_Vendors_Bookings {
	/**
	 * Init integration.
	 */
	public function init() {
		if ( version_compare( WC_BOOKINGS_VERSION, '1.13.0.', '<' ) ) {
			require_once 'bookings-global-availability/class-wc-product-vendors-bookings-global-availability-legacy.php';
			require_once 'class-wc-product-vendors-bookings-legacy.php';
			$pv_bookings_global_availability = new WC_Product_Vendors_Bookings_Global_Availability_Legacy();
			$pv_bookings_legacy              = new WC_Product_Vendors_Bookings_Legacy();
			$pv_bookings_legacy->init();
		} else {
			require_once 'bookings-global-availability/class-wc-product-vendors-bookings-global-availability.php';
			$pv_bookings_global_availability = new WC_Product_Vendors_Bookings_Global_Availability();
		}
		$pv_bookings_global_availability->init();
		$this->register_hooks();
	}

	/**
	 * Register all hooks.
	 *
	 * @since 2.1.15
	 */
	protected function register_hooks() {

		// clear bookings query (cache).
		add_action( 'parse_query', array( $this, 'clear_bookings_cache' ) );

		// remove bookings menu if user is not managing any vendors.
		add_action( 'admin_menu', array( $this, 'remove_bookings_menu' ), 99 );

		// filter products for specific vendor.
		add_filter( 'get_booking_products_args', array( $this, 'filter_products' ) );

		// filter resources for specific vendor.
		add_filter( 'get_booking_resources_args', array( $this, 'filter_products' ) );

		// filter products from booking list.
		add_filter( 'pre_get_posts', array( $this, 'filter_products_booking_list' ) );

		// filter products from booking calendar.
		add_filter( 'woocommerce_bookings_in_date_range_query', array( $this, 'filter_bookings_calendar' ) );

		// add vendor email for confirm booking email.
		add_filter( 'woocommerce_email_recipient_new_booking', array( $this, 'filter_booking_emails' ), 10, 2 );

		// add vendor email for cancelled booking email.
		add_filter( 'woocommerce_email_recipient_booking_cancelled', array( $this, 'filter_booking_emails' ), 10, 2 );

		// filters the product type.
		add_filter( 'product_type_selector', array( $this, 'filter_product_type' ), 20 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'maybe_hide_bookings_fields' ) );

		// modify the booking status views.
		add_filter( 'views_edit-wc_booking', array( $this, 'booking_status_views' ) );

		// setup dashboard widget.
		add_action( 'wp_dashboard_setup', array( $this, 'add_vendor_dashboard_widget' ), 99999 );

		// redirect the page after creating bookings.
		add_filter( 'wp_redirect', array( $this, 'create_booking_redirect' ) );

		// clears any cache for the recent bookings on dashboard.
		add_action( 'save_post', array( $this, 'clear_recent_bookings_cache_on_save_post' ), 10, 2 );

		// clears any cache for the recent bookings on dashboard.
		add_action( 'woocommerce_new_booking_order', array( $this, 'clear_recent_bookings_cache_on_create' ) );

		// Dynamically add booking related capabilities if vendor has bookings enabled.
		add_filter( 'user_has_cap', array( $this, 'add_booking_caps' ), 10, 4 );
		// Filter out pages vendors should not have access to.
		add_filter( 'map_meta_cap', array( $this, 'map_vendor_capabilities' ), 10, 4 );

		// Filter view booking order URL.
		add_filter( 'woocommerce_bookings_admin_view_order_url', array( $this, 'admin_view_order_url' ), 10, 3 );

		return true;
	}

	/**
	 * Checks if we need to require additional capabilities for editing other vendor's stuff.
	 *
	 * @param array  $caps Currently required capabilities.
	 * @param string $cap Original requested capabality.
	 * @param int    $user_id Current User id.
	 * @param array  $args object id in use.
	 *
	 * @return array
	 */
	public function map_vendor_capabilities( $caps, $cap, $user_id, $args ) {

		if ( empty( $args[0] ) ) {
			return $caps;
		}

		if ( in_array( 'edit_global_availability', (array) $caps, true ) ) {

			$availability = new WC_Global_Availability( $args[0] );

			$vendor_id = $availability->get_meta( 'vendor_id' );

			if ( (int) WC_Product_Vendors_Utils::get_user_active_vendor( $user_id ) !== (int) $vendor_id ) {
				$caps[] = 'edit_other_vendors_global_availabilities';
			}
		}

		if ( in_array( 'delete_global_availability', (array) $caps, true ) ) {

			$availability = new WC_Global_Availability( $args[0] );

			$vendor_id = $availability->get_meta( 'vendor_id' );

			if ( (int) WC_Product_Vendors_Utils::get_user_active_vendor( $user_id ) !== (int) $vendor_id ) {
				$caps[] = 'delete_other_vendors_global_availabilities';
			}
		}

		if ( in_array( 'edit_others_wc_bookings', (array) $caps, true ) ) {

			$vendor_id = WC_Product_Vendors_Utils::get_user_active_vendor( $user_id );

			// Get the product from the booking.
			$product_id = get_post_meta( $args[0], '_booking_product_id', true );

			if ( ! WC_Product_Vendors_Utils::can_user_manage_product( $vendor_id, $product_id ) ) {
				$caps[] = 'edit_other_vendors_wc_bookings';
			}
		}

		if ( in_array( 'delete_others_wc_bookings', (array) $caps, true ) ) {

			$vendor_id = WC_Product_Vendors_Utils::get_user_active_vendor( $user_id );

			// Get the product from the booking.
			$product_id = get_post_meta( $args[0], '_booking_product_id', true );

			if ( ! WC_Product_Vendors_Utils::can_user_manage_product( $vendor_id, $product_id ) ) {
				$caps[] = 'delete_other_vendors_wc_bookings';
			}
		}

		return $caps;
	}

	/**
	 *
	 * @param array        $allcaps An array of all the user's capabilities.
	 * @param array|string $caps    Actual capabilities for meta capability.
	 * @param array        $args    Optional parameters passed to has_cap(), typically object ID.
	 * @param WP_User      $user    The user object.
	 *
	 * @return array
	 */
	public function add_booking_caps( array $allcaps, $caps, array $args, WP_User $user ) {
		// Remove and re-add this filter later, to avoid potential infinite
		// loop with other plugins that hook onto `get_terms` and use `has_cap`
		remove_filter( 'user_has_cap', array( $this, 'add_booking_caps' ), 10, 4 );

		if ( WC_Product_Vendors_Utils::is_vendor( $user->ID ) ) {
			if ( WC_Product_Vendors_Utils::is_bookings_enabled( $user->ID ) ) {

				$allcaps['manage_bookings_settings']     = true;
				$allcaps['edit_global_availability']     = true;
				$allcaps['read_global_availability']     = true;
				$allcaps['delete_global_availability']   = true;
				$allcaps['edit_global_availabilities']   = true;
				$allcaps['delete_global_availabilities'] = true;

				$capability_types = array( 'bookable_person', 'wc_booking' );

				foreach ( $capability_types as $capability_type ) {
					$allcaps = array_merge(
						$allcaps,
						array(
							"edit_{$capability_type}"     => true,
							"read_{$capability_type}"     => true,
							"delete_{$capability_type}"   => true,
							"edit_{$capability_type}s"    => true,
							"edit_others_{$capability_type}s" => true,
							"publish_{$capability_type}s" => true,
							"read_private_{$capability_type}s" => true,
							"delete_{$capability_type}s"  => true,
							"delete_private_{$capability_type}s" => true,
							"delete_published_{$capability_type}s" => true,
							"delete_others_{$capability_type}s" => true,
							"edit_private_{$capability_type}s" => true,
							"edit_published_{$capability_type}s" => true,
						)
					);
				}
			}
		}

		add_filter( 'user_has_cap', array( $this, 'add_booking_caps' ), 10, 4 );

		return $allcaps;
	}

	/**
	 * Add dashboard widgets for vendors
	 *
	 * @since 2.1.0
	 * @version 2.1.0
	 * @return bool
	 */
	public function add_vendor_dashboard_widget() {
		if ( WC_Product_Vendors_Utils::is_bookings_enabled() ) {
			wp_add_dashboard_widget(
				'wcpv_vendor_bookings_dashboard_widget',
				__( 'Recent Bookings', 'woocommerce-product-vendors' ),
				array( $this, 'render_bookings_dashboard_widget' )
			);
		}

		return true;
	}

	/**
	 * Renders the bookings dashboard widgets for vendors
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 */
	public function render_bookings_dashboard_widget() {
		if ( false === ( $bookings = get_transient( 'wcpv_reports_bookings_wg_' . WC_Product_Vendors_Utils::get_logged_in_vendor() ) ) ) {
			$args = array(
				'post_type'      => 'wc_booking',
				'posts_per_page' => 20,
				'post_status'    => get_wc_booking_statuses(),
			);

			$bookings = get_posts( apply_filters( 'wcpv_bookings_list_widget_args', $args ) );

			if ( ! empty( $bookings ) ) {
				// filter out only bookings with products of the vendor.
				$bookings = array_filter( $bookings, array( $this, 'filter_booking_products' ) );
			}

			set_transient( 'wcpv_reports_bookings_wg_' . WC_Product_Vendors_Utils::get_logged_in_vendor(), $bookings, DAY_IN_SECONDS );
		}

		if ( empty( $bookings ) ) {
			echo '<p>' . __( 'There are no bookings available.', 'woocommerce-product-vendors' ) . '</p>';

			return;
		}
		?>

		<table class="wcpv-vendor-bookings-widget wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Booking ID', 'woocommerce-product-vendors' ); ?></th>
					<th><?php esc_html_e( 'Booked Product', 'woocommerce-product-vendors' ); ?></th>
					<th><?php esc_html_e( '# of Persons', 'woocommerce-product-vendors' ); ?></th>
					<th><?php esc_html_e( 'Booked By', 'woocommerce-product-vendors' ); ?></th>
					<th><?php esc_html_e( 'Order', 'woocommerce-product-vendors' ); ?></th>
					<th><?php esc_html_e( 'Start Date', 'woocommerce-product-vendors' ); ?></th>
					<th><?php esc_html_e( 'End Date', 'woocommerce-product-vendors' ); ?></th>
				</tr>
			</thead>

			<tbody id="the-list">
				<?php
				foreach ( $bookings as $booking ) {
					$booking_item = get_wc_booking( $booking->ID );
					?>
					<tr>
						<td><a href="<?php echo get_edit_post_link( $booking->ID ); ?>" title="<?php esc_attr_e( 'Edit Booking', 'woocommerce-product-vendors' ); ?>"><?php printf( __( 'Booking #%d', 'woocommerce-product-vendors' ), $booking->ID ); ?></a></td>

						<td><a href="<?php echo get_edit_post_link( $booking_item->get_product()->get_id() ); ?>" title="<?php esc_attr_e( 'Edit Product', 'woocommerce-product-vendors' ); ?>"><?php echo $booking_item->get_product()->get_name(); ?></a></td>

						<td>
							<?php
							if ( $booking_item->has_persons() ) {
								echo $booking_item->get_persons_total();
							} else {
								esc_html_e( 'N/A', 'woocommerce-product-vendors' );
							}
							?>
						</td>

						<td>
							<?php
							if ( $booking_item->get_customer() ) {
							?>
								<a href="mailto:<?php echo esc_attr( $booking_item->get_customer()->email ); ?>"><?php echo $booking_item->get_customer()->name; ?></a>
							<?php
							} else {
								esc_html_e( 'N/A', 'woocommerce-product-vendors' );
							}
							?>
						</td>

						<td>
							<?php
							if ( $booking_item->get_order() ) {
							?>
								<a href="<?php echo admin_url( 'admin.php?page=wcpv-vendor-order&id=' . $booking_item->order_id ); ?>" title="<?php esc_attr_e( 'Order Detail', 'woocommerce-product-vendors' ); ?>"><?php printf( __( '#%d', 'woocommerce-product-vendors' ), $booking_item->order_id ); ?></a> &mdash; <?php echo WC_Product_Vendors_Utils::format_order_status( $booking_item->get_order()->get_status() ); ?>
							<?php
							} else {
								esc_html_e( 'N/A', 'woocommerce-product-vendors' );
							}
							?>
						</td>

						<td><?php echo $booking_item->get_start_date(); ?></td>
						<td><?php echo $booking_item->get_end_date(); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Filters the product ids for logged in vendor
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $term the slug of the term
	 * @return array $ids product ids
	 */
	public function filter_booking_products( $item ) {
		$product_ids = WC_Product_Vendors_Utils::get_vendor_product_ids();

		$booking_item = get_wc_booking( $item->ID );

		if ( is_object( $booking_item ) && is_object( $booking_item->get_product() ) && $booking_item->get_product()->get_id() && in_array( $booking_item->get_product()->get_id(), $product_ids ) ) {
			return $item;
		}
	}

	/**
	 * Filters the product type
	 *
	 * @since 2.0.9
	 * @param array $types
	 * @return array $post_type_args
	 */
	public function filter_product_type( $types ) {
		if ( WC_Product_Vendors_Utils::auth_vendor_user() && ! WC_Product_Vendors_Utils::is_bookings_enabled() ) {
			unset( $types['booking'] );
		}

		return $types;
	}

	/**
	 * This hack is needed because in $this->filter_product_type function we unset bookings.
	 * WooCommerce doesn't know how to handle this and simply shows the output if the bookable
	 * product is no longer available in the type.
	 *
	 * @since 2.1.0
	 */
	public function maybe_hide_bookings_fields() {
		if ( ! WC_Product_Vendors_Utils::auth_vendor_user()
			|| WC_Product_Vendors_Utils::is_bookings_enabled() ) {
			return;
		}
		?>
		<script>
			jQuery( ".show_if_booking" ).hide();
		</script>
		<?php
	}

	/**
	 * Modifies the booking status views
	 *
	 * @since 2.0.9
	 * @version 2.0.9
	 * @param array $views
	 * @return array $post_type_args
	 */
	public function booking_status_views( $views ) {
		global $typenow;

		if ( WC_Product_Vendors_Utils::auth_vendor_user() && 'wc_booking' === $typenow ) {
			$new_views = array();

			// remove the count from each status
			foreach ( $views as $k => $v ) {
				$new_views[ $k ] = preg_replace( '/\(\d+\)/', '', $v );
			}

			$views = $new_views;
		}

		return $views;
	}

	/**
	 * Remove bookings menu item when user has no access
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function remove_bookings_menu() {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			if ( ! WC_Product_Vendors_Utils::is_bookings_enabled() ) {
				remove_menu_page( 'edit.php?post_type=wc_booking' );

				// remove create bookings menu page.
				remove_submenu_page( 'edit.php?post_type=wc_booking', 'create_booking' );

				return;
			}
		}

		return true;
	}

	/**
	 * Filter products for specific vendor
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $query_args
	 * @return array $products
	 */
	public function filter_products( $query_args ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			$product_ids = WC_Product_Vendors_Utils::get_vendor_product_ids();

			$product_ids = ! empty( $product_ids ) ? $product_ids : array( '0' );

			$query_args['post__in'] = $product_ids;
		}

		return $query_args;
	}

	/**
	 * Filter products booking list to specific vendor
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $query
	 * @return bool
	 */
	public function filter_products_booking_list( $query ) {
		global $typenow, $current_screen;

		if ( ! $query->is_main_query() ) {
			return;
		}

		remove_filter( 'pre_get_posts', array( $this, 'filter_products_booking_list' ) );

		if ( 'wc_booking' === $typenow && WC_Product_Vendors_Utils::auth_vendor_user() && is_admin() && 'edit-wc_booking' === $current_screen->id ) {
			$product_ids = WC_Product_Vendors_Utils::get_vendor_product_ids();

			$product_ids = ! empty( $product_ids ) ? $product_ids : array( '0' );
			$query->set( 'meta_key', '_booking_product_id' );
			$query->set( 'meta_compare', 'IN' );
			$query->set( 'meta_value', $product_ids );
		}

		return true;
	}

	/**
	 * Filter products booking calendar to specific vendor
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $booking_ids booking ids
	 * @return array
	 */
	public function filter_bookings_calendar( $booking_ids ) {
		$filtered_ids = array();

		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			$product_ids = WC_Product_Vendors_Utils::get_vendor_product_ids();

			if ( ! empty( $product_ids ) ) {
				foreach ( $booking_ids as $id ) {
					$booking = get_wc_booking( $id );

					if ( in_array( $booking->product_id, $product_ids ) ) {
						$filtered_ids[] = $id;
					}
				}

				$filtered_ids = array_unique( $filtered_ids );

				return $filtered_ids;
			} else {
				return array();
			}
		}

		return $booking_ids;
	}

	/**
	 * Add vendor email to bookings admin emails
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array  $recipients
	 * @param object $this_email
	 * @return array $recipients
	 */
	public function filter_booking_emails( $recipients, $this_email ) {
		if ( ! empty( $this_email ) ) {
			$vendor_id   = WC_Product_Vendors_Utils::get_vendor_id_from_product( $this_email->product_id );
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );

			if ( ! empty( $vendor_id ) && ! empty( $vendor_data ) ) {
				if ( isset( $recipients ) ) {
					$recipients .= ',' . $vendor_data['email'];
				} else {
					$recipients = $vendor_data['email'];
				}
			}
		}

		return $recipients;
	}

	public function create_booking_redirect( $location ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() ) {
			return $location;
		}

		if ( ! is_admin() ) {
			return $location;
		}

		// most likely an admin, no need to redirect.
		if ( current_user_can( 'manage_options' ) ) {
			return $location;
		}

		if ( ! WC_Product_Vendors_Utils::is_bookings_enabled() ) {
			return $location;
		}

		if ( preg_match( '/\bpost=(\d+)/', $location, $matches ) ) {
			// check the post type.
			$post = get_post( $matches[1] );

			if ( 'shop_order' === $post->post_type ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wcpv-vendor-order&id=' . $post->ID ) );
				exit;
			}
		}

		return $location;
	}

	/**
	 * Clears the recent bookings cache on dashboard
	 *
	 * @since 2.0.21
	 * @version 2.0.21
	 * @return bool
	 */
	public function clear_recent_bookings_cache_on_create() {
		global $wpdb;

		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wcpv_reports_bookings_wg%'" );

		return true;
	}

	/**
	 * Clears the recent bookings cache on dashboard
	 *
	 * @since 2.0.21
	 * @version 2.0.21
	 * @return bool
	 */
	public function clear_recent_bookings_cache_on_save_post( $post_id, $post ) {
		global $wpdb;

		if ( 'wc_booking' !== $post->post_type ) {
			return;
		}

		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wcpv_reports_bookings_wg%'" );

		return true;
	}

	/**
	 * Clears the bookings query cache on page load
	 *
	 * @since 2.0.1
	 * @version 2.0.1
	 * @return bool
	 */
	public function clear_bookings_cache() {
		global $wpdb, $typenow, $current_screen;

		if ( 'wc_booking' === $typenow && is_admin() && ( 'edit-wc_booking' === $current_screen->id || 'wc_booking_page_booking_calendar' === $current_screen->id ) ) {

			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%book_dr%'" );
		}

		return true;
	}

	/**
	 * Filter the view order URL when viewing a booking.
	 *
	 * @param string   $url      URL where we can view booking details.
	 * @param int      $order_id Order ID.
	 * @param WC_Order $order    Order object.
	 * @return string  Filtered URL pointing to vendor page.
	 */
	public function admin_view_order_url( $url, $order_id, $order ) {

		// Check if vendor has access to order page.
		$vendors_from_order = WC_Product_Vendors_Utils::get_vendors_from_order( $order );
		if ( in_array( WC_Product_Vendors_Utils::get_user_active_vendor(), array_keys( $vendors_from_order ), true ) ) {
			$url = admin_url( 'admin.php?page=wcpv-vendor-order&id=' . absint( $order_id ) );
		}

		return $url;
	}
}

