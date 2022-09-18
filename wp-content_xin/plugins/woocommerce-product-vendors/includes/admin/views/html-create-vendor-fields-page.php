<?php
/**
 * Create vendor fields page template ( store admin )
 *
 * @version 2.0.16
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="form-field">
	<a href="#" class="wcpv-term-additional-settings-link"><?php _e( 'Additional Settings', 'woocommerce-product-vendors' ); ?></a>
</div>

<div class="form-fields wcpv-term-additional-settings" style="display:none">
	<div class="form-field term-notes">
		<label><?php esc_html_e( 'Notes', 'woocommerce-product-vendors' ); ?></label>
		<textarea name="vendor_data[notes]"></textarea>

		<p><?php esc_html_e( 'Enter any notes about this vendor.  Only seen by store owners.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-vendor-logo">
		<label for="wcpv-vendor-logo"><?php esc_html_e( 'Vendor Logo', 'woocommerce-product-vendors' ); ?></label>
		<input type="hidden" name="vendor_data[logo]" value="" />
		<a href="#" class="wcpv-upload-logo button"><?php esc_html_e( 'Upload Logo', 'woocommerce-product-vendors' ); ?></a>
		<br />
		<br />

		<img src="" class="wcpv-logo-preview-image hide" />

		<a href="#" class="wcpv-remove-image dashicons dashicons-no" title="<?php esc_attr_e( 'Click to remove image', 'woocommerce-product-vendors' ); ?>" style="display:none;"></a>
	</div>

	<div class="form-field term-profile">
		<label for="wcpv-vendor-profile"><?php esc_html_e( 'Vendor Profile', 'woocommerce-product-vendors' ); ?></label>
		<?php
			$args = array(
				'textarea_name' => 'vendor_data[profile]',
				'textarea_rows' => 5,
			);

			wp_editor( '', 'wcpv_vendor_info', $args );
		?>

		<p><?php esc_html_e( 'Enter the profile information you would like for customer to see.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-email">
		<label for="wcpv-vendor-email"><?php esc_html_e( 'Vendor Email(required)', 'woocommerce-product-vendors' ); ?></label>
		<input type="email" multiple name="vendor_data[email]" value="" required aria-required="true" />

		<p><?php esc_html_e( 'All notifications will be sent to this email address, including new orders and customer inquiries. Add multiple email addresses by separating them with a comma.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-admins">
		<label for="wcpv-vendor-admins"><?php esc_html_e( 'Vendor Admins', 'woocommerce-product-vendors' ); ?> <?php echo wc_help_tip( __( 'Additional access level can be set individually per vendor user.', 'woocommerce-product-vendors' ) ); ?></label>
		<?php if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) { ?>
			<select id="wcpv-vendor-admins" style="width: 50%;" class="wc-customer-search" name="vendor_data[admins][]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for Users', 'woocommerce-product-vendors' ); ?>">
			</select>
		<?php } else { ?>
			<input type="hidden" class="wc-customer-search" id="wcpv-vendor-admins" name="vendor_data[admins]" data-multiple="true" data-placeholder="<?php esc_attr_e( 'Search for Users', 'woocommerce-product-vendors' ); ?>" value="<?php echo esc_attr( $admins ); ?>" data-allow_clear="true" style="max-width: 95%;" />
		<?php } ?>

		<p><?php esc_html_e( 'A list of users who can manage this vendor.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-commission">
		<label for="wcpv-vendor-commission"><?php esc_html_e( 'Commission', 'woocommerce-product-vendors' ); ?> <?php echo wc_help_tip( __( 'This is the commission amount the vendor will receive.  Product level commission can be set which will override this commission.', 'woocommerce-product-vendors' ) ); ?></label>
		<input type="number" id="wcpv-vendor-commission" name="vendor_data[commission]" value="" step="any" min="0" />

		<p><?php esc_html_e( 'Enter a positive number.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-commission-type">
		<label for="wcpv-vendor-commission-type"><?php esc_html_e( 'Commission Type', 'woocommerce-product-vendors' ); ?></label>
		<select id="wcpv-vendor-commission-type" name="vendor_data[commission_type]">
			<option value="percentage"><?php esc_html_e( 'Percentage', 'woocommerce-product-vendors' ); ?></option>
			<option value="fixed"><?php esc_html_e( 'Fixed', 'woocommerce-product-vendors' ); ?></option>
		</select>

		<p><?php esc_html_e( 'Choose whether the commission amount will be a fixed amount or a percentage of the cost.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-pass-shipping">
		<label for="wcpv-pass-shipping">
			<input type="checkbox" id="wcpv-pass-shipping" name="vendor_data[pass_shipping]" checked="checked" />
			<?php esc_html_e( 'Pass Shipping', 'woocommerce-product-vendors' ); ?>
		</label>

		<p><?php esc_html_e( 'Check box to pass the shipping charges to the vendor.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div>
		<label><?php esc_html_e( 'Tax Handling', 'woocommerce-product-vendors' ); ?></label>
		<label>
			<input type="radio" value="keep-tax" name="vendor_data[taxes]" />
			<?php esc_html_e( 'Keep Taxes', 'woocommerce-product-vendors' ); ?>
			<p><?php esc_html_e( 'Calculate commission based on product price only.', 'woocommerce-product-vendors' ); ?></p>
		</label>
		<label>
			<input type="radio" value="pass-tax" name="vendor_data[taxes]" <?php checked( ! wc_prices_include_tax() ); ?> />
			<?php esc_html_e( 'Pass Taxes', 'woocommerce-product-vendors' ); ?>
			<p><?php esc_html_e( 'All tax charges will be included in the vendor\'s commission.', 'woocommerce-product-vendors' ); ?></p>
		</label>
		<label>
			<input type="radio" value="split-tax" name="vendor_data[taxes]" <?php checked( wc_prices_include_tax() ); ?> />
			<?php esc_html_e( 'Split Taxes', 'woocommerce-product-vendors' ); ?>
			<p><?php esc_html_e( 'The full price including taxes will be used to calculate commission.', 'woocommerce-product-vendors' ); ?></p>
		</label>
	</div>

	<div class="form-field term-instant-payout">
		<label for="wcpv-vendor-instant-payout"><input type="checkbox" id="wcpv-vendor-instant-payout" name="vendor_data[instant_payout]" value="" /> <?php esc_html_e( 'Instant Payout', 'woocommerce-product-vendors' ); ?></label>

		<p><?php esc_html_e( 'Pay commission to vendor instantly when order is paid. (Uses PayPal Payouts)', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="form-field term-paypal-email">
		<label for="wcpv-vendor-paypal"><?php esc_html_e( 'PayPal Email', 'woocommerce-product-vendors' ); ?></label>
		<input type="email" id="wcpv-vendor-paypal" name="vendor_data[paypal]" value="" />

		<p><?php esc_html_e( 'Scheduled commission payouts will be using this PayPal email to receive payments.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<div>
		<label for="wcpv-vendor-timezone"><?php esc_html_e( 'Timezone', 'woocommerce-product-vendors' ); ?></label>
		<select id="wcpv-vendor-timezone" name="vendor_data[timezone]" aria-describedby="timezone-description" class="wc-enhanced-select" style="width:50%">
			<?php echo wp_timezone_choice( $tzstring ); ?>
		</select>

		<p><?php esc_html_e( 'Set the local timezone.', 'woocommerce-product-vendors' ); ?></p>
	</div>

	<?php if ( WC_Product_Vendors_Utils::is_wcpv_per_product_shipping_enabled() ) { ?>
		<div class="form-field term-per-product-shipping">
			<label for="wcpv-per-product-shipping"><input type="checkbox" id="wcpv-per-product-shipping" name="vendor_data[per_product_shipping]" /> <?php esc_html_e( 'Show Per Product Shipping Rules', 'woocommerce-product-vendors' ); ?></label>

			<p><?php esc_html_e( 'When enabled, vendor can edit per product shipping rules.', 'woocommerce-product-vendors' ); ?></p>
		</div>
	<?php } ?>

	<div class="form-field term-bookings">
		<label for="wcpv-enable-bookings"><input type="checkbox" id="wcpv-enable-bookings" name="vendor_data[enable_bookings]" /> <?php esc_html_e( 'Enable Bookings Feature', 'woocommerce-product-vendors' ); ?></label>

		<p><?php esc_html_e( 'Enable to allow vendors to create bookable products such as booking classes or lessons. ( WooCommerce Bookings sold seperately )', 'woocommerce-product-vendors' ); ?> <a href="https://www.woothemes.com/products/woocommerce-bookings/" target="_blank">WooCommerce Bookings</a></p>
	</div>
</div>
