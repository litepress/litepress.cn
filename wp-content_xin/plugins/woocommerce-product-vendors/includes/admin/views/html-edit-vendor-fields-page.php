<?php
/**
 * Edit vendor fields page template ( store admin )
 *
 * @version 2.0.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-description"><?php esc_html_e( 'Registration Description', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<textarea name="vendor_data[description]" style="display:none"><?php echo esc_textarea( $description ); ?></textarea>
		<?php echo wp_kses_post( wpautop( $description ) ); ?>

		<p class="description"><?php esc_html_e( 'The description provided by the vendor during registration. Not displayed publicly.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-notes"><?php esc_html_e( 'Notes', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<textarea name="vendor_data[notes]"><?php echo esc_textarea( $notes ); ?></textarea>

		<p><?php esc_html_e( 'Enter any notes about this vendor. Not displayed publicly.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-logo"><?php esc_html_e( 'Vendor Logo', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<input type="hidden" name="vendor_data[logo]" value="<?php echo esc_attr( $logo ); ?>" />
		<a href="#" class="wcpv-upload-logo button"><?php esc_html_e( 'Upload Logo', 'woocommerce-product-vendors' ); ?></a>
		<br />
		<br />
		<?php if ( is_array( $logo_image_url ) && ! empty( $logo_image_url ) ) { ?>
				<img src="<?php echo esc_url( $logo_image_url[0] ); ?>" class="wcpv-logo-preview-image" />

		<?php } else { ?>
				<img src="" class="wcpv-logo-preview-image hide" />

		<?php } ?>

			<a href="#" class="wcpv-remove-image dashicons dashicons-no" style="<?php echo esc_attr( $hide_remove_image_link ); ?>" title="<?php esc_attr_e( 'Click to remove image', 'woocommerce-product-vendors' ); ?>"></a>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-profile"><?php esc_html_e( 'Vendor Profile', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<?php
			$args = array(
				'textarea_name' => 'vendor_data[profile]',
				'textarea_rows' => 5,
			);

			wp_editor( htmlspecialchars_decode( $profile ), 'wcpv_vendor_info', $args );
		?>

		<p><?php esc_html_e( 'Enter the public vendor profile your store\'s customers will see.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-email"><?php esc_html_e( 'Vendor Email(required)', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<input type="email" multiple name="vendor_data[email]" required aria-required="true" value="<?php echo esc_attr( $email ); ?>" />

		<p><?php esc_html_e( 'All notifications will be sent to this email address, including new orders and customer inquiries. Add multiple email addresses by separating them with a comma.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-admins"><?php esc_html_e( 'Vendor Admins', 'woocommerce-product-vendors' ); ?> <?php echo wc_help_tip( __( 'Additional access level can be set individually per vendor user.', 'woocommerce-product-vendors' ) ); ?></label></th>

	<td>
		<?php if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) { ?>
			<select id="wcpv-vendor-admins" style="width: 50%;" class="wc-customer-search" name="vendor_data[admins][]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for Users', 'woocommerce-product-vendors' ); ?>">

				<?php
					foreach ( $selected_admins as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $value ) . '</option>';
					}
				?>
			</select>
		<?php } else {
			$selected_admins = wp_json_encode( $selected_admins );
			$selected_admins = function_exists( 'wc_esc_json' ) ? wc_esc_json( $selected_admins ) : _wp_specialchars( $selected_admins, ENT_QUOTES, 'UTF-8', true );
		?>
			<input type="hidden" class="wc-customer-search" id="wcpv-vendor-admins" name="vendor_data[admins]" data-multiple="true" data-placeholder="<?php esc_attr_e( 'Search for Users', 'woocommerce-product-vendors' ); ?>" value="<?php echo $admins; ?>" data-allow_clear="true" style="max-width: 95%;" data-selected="<?php echo esc_attr( $selected_admins ); ?>" />
		<?php } ?>

		<p><?php esc_html_e( 'A list of users who can manage this vendor.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr>
	<th>
		<h2><?php esc_html_e( 'Commission', 'woocommerce-product-vendors' ); ?></h2>
	</th>
	<td>
		<p><?php esc_html_e( 'Choose how commissions for this vendor are calculated. Product level commissions that have already been set will only be updated for commission (if no specific value was set on the product), commission type, and tax handling. Changing passing shipping on this page will only be applied to products created after updating.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-commission"><?php esc_html_e( 'Commission', 'woocommerce-product-vendors' ); ?> <?php echo wc_help_tip( __( 'Enter a positive number.', 'woocommerce-product-vendors' ) ); ?></label></th>

	<td>
		<input type="number" id="wcpv-vendor-commission" name="vendor_data[commission]" value="<?php echo esc_attr( $commission ); ?>" step="any" min="0" />

		<p><?php esc_html_e( 'This is the commission amount the vendor will receive. Product level commission can be set which will override this commission.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-commission-type"><?php esc_html_e( 'Commission Type', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<select id="wcpv-vendor-commission-type" name="vendor_data[commission_type]">
			<option value="percentage" <?php selected( 'percentage', $commission_type ); ?>><?php esc_html_e( 'Percentage', 'woocommerce-product-vendors' ); ?></option>
			<option value="fixed" <?php selected( 'fixed', $commission_type ); ?>><?php esc_html_e( 'Fixed', 'woocommerce-product-vendors' ); ?></option>
		</select>

		<p><?php esc_html_e( 'Choose whether the commission amount will be a fixed amount or a percentage of the cost.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-pass-shipping"><?php esc_html_e( 'Pass Shipping', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<input type="checkbox" id="wcpv-pass-shipping" name="vendor_data[pass_shipping]" <?php checked( 'yes', $pass_shipping ); ?> />

		<p><?php esc_html_e( 'Check box to pass the shipping charges to the vendor.', 'woocommerce-product-vendors' ); ?></p>
	</td>

</tr>

<tr class="form-field">
	<th scope="row" valign="top"><?php esc_html_e( 'Tax Handling', 'woocommerce-product-vendors' ); ?></th>

	<td>
		<fieldset>
		<label>
			<input type="radio" value="keep-tax" name="vendor_data[taxes]" <?php checked( 'keep-tax', $taxes ); ?> />
			<?php esc_html_e( 'Keep Taxes', 'woocommerce-product-vendors' ); ?>
			<p><?php esc_html_e( 'Calculate commission based on product price only.', 'woocommerce-product-vendors' ); ?></p>
		</label>
		<label>
			<input type="radio" value="pass-tax" name="vendor_data[taxes]" <?php checked( 'pass-tax', $taxes ); ?> />
			<?php esc_html_e( 'Pass Taxes', 'woocommerce-product-vendors' ); ?>
			<p><?php esc_html_e( 'All tax charges will be included in the vendor\'s commission.', 'woocommerce-product-vendors' ); ?></p>
		</label>
		<label>
			<input type="radio" value="split-tax" name="vendor_data[taxes]" <?php checked( 'split-tax', $taxes ); ?> />
			<?php esc_html_e( 'Split Taxes', 'woocommerce-product-vendors' ); ?>
			<p><?php esc_html_e( 'The full price including taxes will be used to calculate commission.', 'woocommerce-product-vendors' ); ?></p>
		</label>
		</fieldset>
	</td>

</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-instant-payout"><?php esc_html_e( 'Instant Payout', 'woocommerce-product-vendors' ); ?>
	<td>
		<input type="checkbox" id="wcpv-vendor-instant-payout" name="vendor_data[instant_payout]" <?php checked( 'yes', $instant_payout ); ?> />

		<p><?php esc_html_e( 'Pay commission to vendor instantly when order is paid. (Uses PayPal Payouts)', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-paypal"><?php esc_html_e( 'PayPal Email', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<input type="email" id="wcpv-vendor-paypal" name="vendor_data[paypal]" value="<?php echo esc_attr( $paypal ); ?>" />

		<p><?php esc_html_e( 'Scheduled commission payouts will be using this PayPal email to receive payments.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<tr>
	<th colspan="2">
		<h2><?php esc_html_e( 'Extra details', 'woocommerce-product-vendors' ); ?></h2>
	</th>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-vendor-timezone"><?php esc_html_e( 'Timezone', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<select id="wcpv-vendor-timezone" name="vendor_data[timezone]" aria-describedby="timezone-description" class="wc-enhanced-select" style="width:20%">
			<?php echo wp_timezone_choice( $tzstring ); ?>
		</select>

		<p><?php esc_html_e( 'Set the local timezone.', 'woocommerce-product-vendors' ); ?></p>
	</td>
</tr>

<?php if ( WC_Product_Vendors_Utils::is_wcpv_per_product_shipping_enabled() ) { ?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="wcpv-per-product-shipping"><?php esc_html_e( 'Show Per Product Shipping Rules', 'woocommerce-product-vendors' ); ?></label></th>

		<td>
			<input type="checkbox" id="wcpv-per-product-shipping" name="vendor_data[per_product_shipping]" <?php checked( 'yes', $per_product_shipping ); ?> />

			<p><?php esc_html_e( 'When enabled, vendor can edit per product shipping rules.', 'woocommerce-product-vendors' ); ?></p>
		</td>

	</tr>
<?php } ?>

<tr class="form-field">
	<th scope="row" valign="top"><label for="wcpv-enable-bookings"><?php esc_html_e( 'Enable Bookings Feature', 'woocommerce-product-vendors' ); ?></label></th>

	<td>
		<input type="checkbox" id="wcpv-enable-bookings" name="vendor_data[enable_bookings]" <?php checked( 'yes', $enable_bookings ); ?> />

		<p><?php esc_html_e( 'Enable to allow vendors to create bookable products such as booking classes or lessons. ( WooCommerce Bookings sold separately )', 'woocommerce-product-vendors' ); ?> <a href="https://woocommerce.com/products/woocommerce-bookings/" target="_blank">WooCommerce Bookings</a></p>
	</td>

</tr>
