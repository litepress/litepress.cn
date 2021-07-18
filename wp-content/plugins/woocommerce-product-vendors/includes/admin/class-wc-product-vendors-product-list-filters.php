<?php
/**
 * Manage the product list filters specific to Product Vendors.
 *
 * @since 2.1.0
 */
class WC_Product_Vendors_Product_List_Filters {

	/**
	 * Constructing, mainly hooking into actions.
	 */
	public function __construct() {
	    /**
         * TODO:WCY
         *
         * 禁止显示供应商筛选器，因为太卡了
         */
		//add_action( 'restrict_manage_posts', array( $this, 'display_vendor_filter' ), 20 );
	}

	/**
	 * Output the select box containing the product vendors.
	 *
	 * @since 2.1.0
	 */
	public function display_vendor_filter() {
		global $wp_query;
		if ( 'product' !== $wp_query->get( 'post_type' ) ) {
			return;
		}

		$vendors = $this->get_vendors();
		if ( empty( $vendors ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$selected = isset( $wp_query->query_vars['wcpv_product_vendors'] ) ? $wp_query->query_vars['wcpv_product_vendors'] : '';

		?>
		<select name="wcpv_product_vendors" id="product_vendor_filter_dropdown" >
			<option value=""> <?php echo __( 'Filter by Vendor', 'woocommerce-product-vendors' ); ?> </option>
			<?php
			foreach ( $vendors as $vendor ) {
				echo '<option ';
				selected( $selected, $vendor->slug, true );
				echo 'value="' . esc_attr( $vendor->slug ) . '" >';
				echo $vendor->name;
				echo '</option>';
			}
			?>
		</select>
		<?php
	}

	/**
	 * Get the vendors. These are all $terms from
	 * wcpv_product_vendors taxonomy;
	 *
	 * @since 2.1.0
	 *
	 * @return array WP_Term
	 */
	protected function get_vendors() {
		/**
		 * TODO:WCY
         *
         * 禁止获取全部供应商数据，要不然网页直接花式卡死……
		 */
	    return array();
/*
		$vendors = get_terms( apply_filters( 'wcpv_get_terms_get_vendors', array(
			'taxonomy' => 'wcpv_product_vendors',
			'hide_empty' => true,
		) ) );

		return $vendors;
*/
	}

}

new WC_Product_Vendors_Product_List_Filters();
