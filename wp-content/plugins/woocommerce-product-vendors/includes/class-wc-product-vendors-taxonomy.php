<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Taxonomy Class
 *
 * Add custom taxonomy to WordPress.
 *
 * @package  WooCommerce Product Vendors/Taxonomy
 * @version  2.0.0
 */
class WC_Product_Vendors_Taxonomy {
	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function __construct() {
		// Registers vendor taxonomy.
		add_action( 'init', array( $this, 'register_vendor_taxonomy' ), 9 );

		// Registers custom updated term messages.
		add_filter( 'term_updated_messages', array( $this, 'updated_term_messages' ) );

		return true;
	}

	/**
	 * Register vendor taxonomy
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_vendor_taxonomy() {
		$labels = array(
			'name'              => _x( 'Vendors', 'taxonomy general name', 'woocommerce-product-vendors' ),
			'singular_name'     => _x( 'Vendor', 'taxonomy singular name', 'woocommerce-product-vendors' ),
			'search_items'      => __( 'Search Vendors', 'woocommerce-product-vendors' ),
			'all_items'         => __( 'All Vendors', 'woocommerce-product-vendors' ),
			'popular_items'     => __( 'Popular Vendors', 'woocommerce-product-vendors' ),
			'parent_item'       => __( 'Parent Vendor', 'woocommerce-product-vendors' ),
			'parent_item_colon' => __( 'Parent Vendor:', 'woocommerce-product-vendors' ),
			'edit_item'         => __( 'Edit Vendor', 'woocommerce-product-vendors' ),
			'view_item'         => __( 'View Vendor Page', 'woocommerce-product-vendors' ),
			'update_item'       => __( 'Update Vendor', 'woocommerce-product-vendors' ),
			'add_new_item'      => __( 'Add New Vendor', 'woocommerce-product-vendors' ),
			'new_item_name'     => __( 'New Vendor Name', 'woocommerce-product-vendors' ),
			'menu_name'         => __( 'Vendors', 'woocommerce-product-vendors' ),
			'not_found'         => __( 'No Vendors Found', 'woocommerce-product-vendors' ),
			'back_to_items'     => __( 'Back to Vendors', 'woocommerce-product-vendors' ),
		);

		$args = array(
			'hierarchical'       => false,
			'labels'             => $labels,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'query_var'          => true,
			'show_in_rest'       => true,
			'capabilities'       => array(
				'manage_terms' => 'manage_woocommerce',
				'edit_terms'   => 'manage_woocommerce',
				'delete_terms' => 'manage_woocommerce',
				'assign_terms' => 'manage_woocommerce',
			),
			'rewrite'            => (array) apply_filters( 'wcpv_vendor_rewrite_rules', array( 'with_front' => true, 'slug' => apply_filters( 'wcpv_vendor_slug', 'vendor' ) ) ),
			'show_in_quick_edit' => false,
		);

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$args['meta_box_cb'] = array( $this, 'add_meta_box' );
		}

		register_taxonomy( WC_PRODUCT_VENDORS_TAXONOMY, array( 'product' ), apply_filters( 'wcpv_vendor_taxonomy_args', $args ) );
	}

	/**
	 * Customize vendor taxonomy updated messages.
	 *
	 * @since 2.1.34
	 *
	 * @param array $messages The list of available messages.
	 * @return array
	 */
	public function updated_term_messages( $messages ) {

		$messages['wcpv_product_vendors'] = array(
			0 => '',
			1 => __( 'Vendor added.', 'woocommerce-product-vendors' ),
			2 => __( 'Vendor deleted.', 'woocommerce-product-vendors' ),
			3 => __( 'Vendor updated.', 'woocommerce-product-vendors' ),
			4 => __( 'Vendor not added.', 'woocommerce-product-vendors' ),
			5 => __( 'Vendor not updated.', 'woocommerce-product-vendors' ),
			6 => __( 'Vendor deleted.', 'woocommerce-product-vendors' ),
		);

		return $messages;
	}

	/**
	 * Adds the taxonomy meta box
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function add_meta_box() {
		global $post;

		$args = array(
			'hide_empty'   => false,
			'hierarchical' => false,
		);

		/**
		 * TODO:WCY
		 * 因为供应商数据太多了，所以这里直接不查询了，否则这个sql能执行上10秒
		 */
		//$terms = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, $args );
		$terms = [];

		if ( ! empty( $terms ) ) {
			$post_term = wp_get_post_terms( $post->ID, WC_PRODUCT_VENDORS_TAXONOMY );

			$post_term = ! empty( $post_term ) ? $post_term[0]->term_id : '';

			$output = '<select class="wcpv-product-vendor-terms-dropdown" name="wcpv_product_term">';

			$output .= '<option value="">' . esc_html__( 'Select a Vendor', 'woocommerce-product-vendors' ) . '</option>';

			foreach ( $terms as $term ) {
				$output .= '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $post_term, $term->term_id, false ) . '>' . esc_html( $term->name ) . '</option>';
			}

			$output .= '</select>';

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			/* Translators: %1$s: Anchor tag to vendors list. %2$s: Closing anchor tag. */
			printf( wp_kses_post( __( 'Please create vendors by going %1$sHere%2$s', 'woocommerce-product-vendors' ) ), '<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=wcpv_product_vendors&post_type=product' ) ) . '" title="' . esc_attr__( 'Vendors', 'woocommerce-product-vendors' ) . '">', '</a>' );
		}
	}
}

new WC_Product_Vendors_Taxonomy();
