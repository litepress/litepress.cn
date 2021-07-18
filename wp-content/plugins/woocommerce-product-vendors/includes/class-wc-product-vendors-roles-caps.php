<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Roles and Capability Class.
 *
 * Adds all the default roles and capabilities for store and vendor admins.
 *
 * @category Roles and Capability
 * @package  WooCommerce Product Vendors/Roles and Capability
 * @version  2.0.0
 */
class WC_Product_Vendors_Roles_Caps {
	/**
	 * Init
	 */
	public function __construct() {
		add_filter( 'woocommerce_shop_manager_editable_roles', array( $this, 'shop_manager_vendor_management' ), 10, 1 );
	}

	/**
	 * Allow shop manager to manage vendor profiles.
	 *
	 * @since 2.1.10
	 * @param array $roles Roles that a shop manager can manage.
	 * @return array
	 */
	public function shop_manager_vendor_management( $roles = array() ) {
		$roles[] = 'wc_product_vendors_admin_vendor';
		$roles[] = 'wc_product_vendors_manager_vendor';
		$roles[] = 'wc_product_vendors_pending_vendor';

		return $roles;
	}

	/**
	 * Declares the default admin vendor capabilities
	 *
	 * @access protected
	 * @since 2.0.0
	 * @version 2.0.19
	 * @return array
	 */
	protected function default_admin_vendor_caps() {
		return apply_filters( 'wcpv_default_admin_vendor_role_caps', array(
			'read_product'              => true,
			'manage_product'            => true,
			'edit_products'             => true,
			'edit_product'              => true,
			'edit_published_products'   => true,
			'edit_shop_orders'          => true,
			'assign_product_terms'      => true,
			'upload_files'              => true,
			'read'                      => true,
			'edit_others_products'      => true,
			'view_vendor_sales_widget'  => true,
			'delete_published_products' => true,
			'delete_others_products'    => true,
			'delete_posts'              => true,
			'delete_others_posts'       => true,
			'edit_comment'              => false,
			'edit_comments'             => false,
			'view_woocommerce_reports'  => false,
			'publish_products'          => false,
		) );
	}

	/**
	 * Declares the default manager vendor capabilities
	 *
	 * @access protected
	 * @since 2.0.0
	 * @version 2.0.19
	 * @return array
	 */
	protected function default_manager_vendor_caps() {
		return apply_filters( 'wcpv_default_manager_vendor_role_caps', array(
			'read_product'             => true,
			'manage_product'           => true,
			'edit_products'            => true,
			'edit_product'             => true,
			'edit_published_products'  => true,
			'edit_shop_orders'         => true,
			'assign_product_terms'     => true,
			'upload_files'             => true,
			'read'                     => true,
			'edit_others_products'     => true,
			'delete_posts'             => false,
			'delete_product'           => false,
			'edit_comment'             => false,
			'edit_comments'            => false,
			'view_woocommerce_reports' => false,
			'publish_products'         => false,
		) );
	}

	/**
	 * Declares the default pending vendor capabilities
	 *
	 * @access protected
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array
	 */
	protected function default_pending_vendor_caps() {
		return apply_filters( 'wcpv_default_pending_vendor_role_caps', array(
			'read' => true,
		) );
	}

	/**
	 * Adds the default roles
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_default_roles() {
		// admin.
		remove_role( 'wc_product_vendors_admin_vendor' );
		add_role( 'wc_product_vendors_admin_vendor', __( 'Vendor Admin', 'woocommerce-product-vendors' ), $this->default_admin_vendor_caps() );

		// manager.
		remove_role( 'wc_product_vendors_manager_vendor' );
		add_role( 'wc_product_vendors_manager_vendor', __( 'Vendor Manager', 'woocommerce-product-vendors' ), $this->default_manager_vendor_caps() );

		// pending.
		remove_role( 'wc_product_vendors_pending_vendor' );
		add_role( 'wc_product_vendors_pending_vendor', __( 'Pending Vendor', 'woocommerce-product-vendors' ), $this->default_pending_vendor_caps() );

		return true;
	}

	/**
	 * Adds the necessary caps to shop manager and administrator.
	 *
	 * @since 2.1.15
	 */
	public function add_manager_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Add additional caps to admins and shop managers.
		if ( is_object( $wp_roles ) ) {
			$shop_manager_role_caps = array(
				'manage_vendors',
				'edit_other_vendors_products',
				'delete_other_vendors_products',
				'edit_other_vendors_global_availabilities',
				'delete_other_vendors_global_availabilities',
				'edit_other_vendors_wc_bookings',
				'delete_other_vendors_wc_bookings',
			);
			foreach ( $shop_manager_role_caps as $cap ) {
				$wp_roles->add_cap( 'shop_manager', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}

	/**
	 * Removes the manage_bookings cap since it's deprecated in bookings 1.14.2
	 */
	public function remove_deprecated_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // phpcs:disable WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		}

		if ( is_object( $wp_roles ) ) {
			if ( defined( 'WC_BOOKINGS_VERSION' ) && version_compare( WC_BOOKINGS_VERSION, '1.14.2', '>=' ) ) {
				if ( 'yes' !== get_option( 'wcpv_removed_manage_bookings_cap' ) ) {

					$wp_roles->get_role( 'wc_product_vendors_manager_vendor' )->remove_cap( 'manage_bookings' );
					$wp_roles->get_role( 'wc_product_vendors_admin_vendor' )->remove_cap( 'manage_bookings' );

					update_option( 'wcpv_removed_manage_bookings_cap', 'yes' );
				}
			}
		}
	}

	/**
	 * Adds publish products capability to a user
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_publish_products( $user_id = null ) {
		if ( null === $user_id ) {
			return;
		}

		$user = new WP_User( $user_id );
		$user->add_cap( 'publish_products' );

		return true;
	}

	/**
	 * Remove publish products capability from a user
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function remove_publish_products( $user_id = null ) {
		if ( null === $user_id ) {
			return;
		}

		$user = new WP_User( $user_id );
		$user->remove_cap( 'publish_products' );

		return true;
	}

	/**
	 * Adds manage users capabilities to a user
	 *
	 * @access public
	 * @since 2.1.0
	 * @version 2.1.0
	 * @return bool
	 */
	public function add_manage_users( $user_id = null ) {
		if ( null === $user_id ) {
			return;
		}

		$user = new WP_User( $user_id );

		$user->add_cap( 'list_users' );
		$user->add_cap( 'create_users' );
		$user->add_cap( 'edit_users' );
		$user->add_cap( 'edit_shop_orders' );

		return true;
	}

	/**
	 * Remove manage users capabilities from a user
	 *
	 * @access public
	 * @since 2.1.0
	 * @version 2.1.0
	 * @return bool
	 */
	public function remove_manage_users( $user_id = null ) {
		if ( null === $user_id ) {
			return;
		}

		$user = new WP_User( $user_id );
		$user->remove_cap( 'list_users' );
		$user->remove_cap( 'create_users' );
		$user->remove_cap( 'edit_users' );
		$user->remove_cap( 'edit_shop_orders' );

		return true;
	}
}
