<?php
/**
 * API Downloads
 *
 * Shows API downloads on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/api-downloads.php.
 *
 * HOWEVER, on occasion WooCommerce API Manager will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Todd Lahman LLC
 * @package WooCommerce API Manager/Templates/API Downloads
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

wc_print_notices();

if ( empty( $user_id ) ) {
	$user_id = get_current_user_id();
}

if ( ! empty( $user_id ) ) {
	// Set start time for execution.
	//$time_start = microtime( true );

	/**
	 * Every customer must have a Master API Key, and it is missing, so create it now.
	 */
	if ( empty( WC_AM_USER()->get_master_api_key( $user_id ) ) ) {
		WC_AM_USER()->set_registration_master_key_and_status( $user_id );
	}

	$master_api_key_status = WC_AM_USER()->has_api_access( $user_id );

	if ( $master_api_key_status ) {
		$resources = WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_user_id_sort_by_product_title( $user_id );

		if ( $resources ) {
			$dropbox_app_key = get_option( 'woocommerce_api_manager_dropbox_dropins_saver' );
			$columns         = apply_filters( 'wc_api_manager_download_columns', array(
				'api-manager-software-product' => __( 'Product Title', 'woocommerce-api-manager' ),
				'api-manager-version'          => __( 'Version', 'woocommerce-api-manager' ),
				'api-manager-version-date'     => __( 'Release Date', 'woocommerce-api-manager' ),
				'api-manager-documentation'    => __( 'Documentation', 'woocommerce-api-manager' ),
				'api-manager-download'         => __( 'Download', 'woocommerce-api-manager' ),
			) );

			?>
            <table class="woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_api_manager my_account_orders">
                <thead>
                <tr>
					<?php foreach ( $columns as $column_id => $column_name ) { ?>
                        <th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
					<?php } ?>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( $resources as $resource ) {
					// Delete excess API Key activations by activation resource ID.
					WC_AM_API_ACTIVATION_DATA_STORE()->delete_excess_api_key_activations_by_activation_id( $resource->activation_ids, $resource->activations_purchased_total );

					$product_object          = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $resource->product_id );
					$parent_product_id       = $resource->parent_id;
					$is_api                  = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $parent_product_id );
					$product_permalink       = $product_object->get_permalink();
					$is_downloadable         = WC_AM_PRODUCT_DATA_STORE()->is_downloadable_product( $product_object );
					$order_id                = $resource->order_id;
					$order_completed_status  = WC_AM_ORDER_DATA_STORE()->has_status_completed( $order_id );
					$order_processing_status = WC_AM_ORDER_DATA_STORE()->has_status_processing( $order_id );

					if ( $is_api && $is_downloadable && ( $order_completed_status || ( WCAM()->get_grant_access_after_payment() && $order_processing_status ) ) ) {
						$product_id = $resource->product_id;
						$download   = $is_downloadable ? WC_AM_PRODUCT_DATA_STORE()->get_first_download_url( $product_id ) : false;

						/**
						 * Skip duplicate Product IDs.
						 */
						$product_ids[]     = $product_id;
						$total_product_ids = array_count_values( $product_ids );

						if ( is_array( $product_ids ) && in_array( $product_id, $product_ids ) && $total_product_ids[ $product_id ] > 1 ) {
							continue; // Skip duplicates.
						}

						if ( $download ) {
							$product_title         = $resource->product_title;
							$find_amazon_s3_in_url = ! empty( $download ) && WC_AM_URL()->find_amazon_s3_in_url( $download ) === true ? true : false;
							$secure_s3_url         = ! empty( $find_amazon_s3_in_url ) ? WC_AM_URL()->format_secure_s3_v4_url( $download ) : '';

							$remote_url = WC_AM_URL()->is_download_external_url( $product_id );

							if ( WCAM()->get_wc_subs_exist() ) {
								$is_wc_sub = WC_AM_SUBSCRIPTION()->is_wc_subscription( $product_id );
							} else {
								$is_wc_sub = false;
							}

							/**
							 * WC Subscriptions Only
							 */
							if ( WCAM()->get_wc_subs_exist() ) {
								if ( $is_wc_sub ) {
									$sub_id                    = $resource->sub_id;
									$sub_order_key             = $resource->sub_order_key;
									$secure_order_download_url = $remote_url ? WC_AM_ORDER_DATA_STORE()->get_secure_order_download_url( $user_id, $sub_id, $product_id, $remote_url ) : WC_AM_ORDER_DATA_STORE()->get_secure_order_download_url( $user_id, $sub_id, $product_id );

									if ( $sub_id ) {
										?>
                                        <tr class="order">
                                            <td class="api-manager-downloads-product">
												<?php
												if ( ! empty( $product_permalink ) ) {
													?>
                                                    <a href="<?php echo esc_url( $product_permalink ) ?>"
                                                       target="_blank"><?php echo esc_attr( $product_title ) ?></a> <?php echo esc_html( ' &rarr; ID # ' ) . absint( $product_id ) ?>
													<?php
												}
												?>
                                            </td>
                                            <td class="api-manager-version">
												<?php
												$download_version = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_new_version' );
												if ( ! empty( $download_version ) ) {
													echo esc_attr( $download_version );
												}
												?>
                                            </td>
                                            <td class="api-manager-version-date">
												<?php
												$version_date = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_last_updated' );
												if ( ! empty( $version_date ) ) {
													echo esc_attr( date_i18n( $version_date ) );
												} else {
													esc_html_e( 'No Date', 'woocommerce-api-manager' );
												}
												?>
                                            </td>
                                            <td class="api-manager-changelog">
												<?php
												$changelog = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_changelog' );
												if ( ! empty( $changelog ) ) {
													?>
                                                    <a href="<?php echo esc_url( get_permalink( absint( $changelog ) ) ); ?>"
                                                       target="_blank"><?php esc_html_e( 'Changelog', 'woocommerce-api-manager' ); ?></a>
													<?php
												}
												?>
                                                <br>
                                                <hr>
												<?php
												$documentation = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_product_documentation' );
												if ( ! empty( $documentation ) ) {
													?>
                                                    <a href="<?php echo esc_url( get_permalink( absint( $documentation ) ) ); ?>"
                                                       target="_blank"><?php esc_html_e( 'Documentation', 'woocommerce-api-manager' ); ?></a>
													<?php
												}
												?>
                                            </td>
                                            <td class="api-manager-download">
												<?php
												if ( ! empty( $download ) && $find_amazon_s3_in_url ) {
													?>
                                                    <a href="<?php echo esc_url( $secure_s3_url ); ?>"
                                                       target="_blank"><?php esc_html_e( 'Download', 'woocommerce-api-manager' ); ?></a>
												<?php } elseif ( ! empty( $download ) && ! $find_amazon_s3_in_url ) { ?>
                                                    <a href="<?php echo esc_url( $secure_order_download_url ); ?>"
                                                       target="_blank"><?php esc_html_e( 'Download', 'woocommerce-api-manager' ); ?></a>
												<?php } else {
													esc_html_e( 'Disabled', 'woocommerce-api-manager' );
												}
												?>
                                                <br>
                                                <hr>
												<?php
												if ( ! empty( $dropbox_app_key ) && ! empty( $download ) && $find_amazon_s3_in_url ) {
													?>
                                                    <a href="<?php echo esc_url( $secure_s3_url ); ?>"
                                                       class="dropbox-saver nobr"></a>
													<?php
												} elseif ( ! empty( $dropbox_app_key ) && ! empty( $download ) && ! $find_amazon_s3_in_url ) { ?>
                                                    <a href="<?php echo esc_url( $secure_order_download_url ); ?>"
                                                       class="dropbox-saver nobr"></a>
													<?php
												} elseif ( empty( $dropbox_app_key ) ) {
													echo '&nbsp;';
												} else {
													esc_html_e( 'Disabled', 'woocommerce-api-manager' );
												}
												?>
                                            </td>
                                        </tr>
										<?php
									} // end if $sub_id
								}
							}
							/**
							 * Non WC Subscriptions
							 */
							if ( ! $is_wc_sub ) {
								// If the API Key access is not expired.
								$expired = WC_AM_API_RESOURCE_DATA_STORE()->is_access_expired( $resource->access_expires );

								if ( ! $expired ) {
									$order_key                 = WC_AM_ORDER_DATA_STORE()->get_order_key( $order_id );
									$secure_order_download_url = $remote_url ? WC_AM_ORDER_DATA_STORE()->get_secure_order_download_url( $user_id, $order_id, $product_id, $remote_url ) : WC_AM_ORDER_DATA_STORE()->get_secure_order_download_url( $user_id, $order_id, $product_id );

									?>
                                    <tr class="order">
                                        <td class="api-manager-downloads-product">
											<?php
											if ( ! empty( $product_permalink ) ) {
												?>
                                                <a href="<?php echo esc_url( $product_permalink ) ?>"
                                                   target="_blank"><?php echo esc_attr( $product_title ) ?></a><?php echo esc_html( ' &rarr; ID # ' ) . absint( $product_id ) ?>
												<?php
											}
											?>
                                        </td>
                                        <td class="api-manager-version">
											<?php
											$download_version = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_new_version' );
											if ( ! empty( $download_version ) ) {
												echo esc_attr( $download_version );
											}
											?>
                                        </td>
                                        <td class="api-manager-version-date">
											<?php
											$version_date = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_last_updated' );
											if ( ! empty( $version_date ) ) {
												echo esc_attr( date_i18n( $version_date ) );
											} else {
												esc_html_e( 'No Date', 'woocommerce-api-manager' );
											}
											?>
                                        </td>
                                        <td class="api-manager-changelog">
											<?php
											$changelog = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_changelog' );
											if ( ! empty( $changelog ) ) {
												?>
                                                <a href="<?php echo esc_url( get_permalink( absint( $changelog ) ) ); ?>"
                                                   target="_blank"><?php esc_html_e( 'Changelog', 'woocommerce-api-manager' ); ?></a>
												<?php
											}
											?>
                                            <br>
                                            <hr>
											<?php
											$documentation = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_product_documentation' );
											if ( ! empty( $documentation ) ) {
												?>
                                                <a href="<?php echo esc_url( get_permalink( absint( $documentation ) ) ); ?>"
                                                   target="_blank"><?php esc_html_e( 'Documentation', 'woocommerce-api-manager' ); ?></a>
												<?php
											}
											?>
                                        </td>
                                        <td class="api-manager-download">
											<?php
											if ( ! empty( $download ) && $find_amazon_s3_in_url ) {
												?>
                                                <a href="<?php echo esc_url( $secure_s3_url ); ?>"
                                                   target="_blank"><?php esc_html_e( 'Download', 'woocommerce-api-manager' ); ?></a>
												<?php
											} elseif ( ! empty( $download ) && ! $find_amazon_s3_in_url ) { ?>
                                                <a href="<?php echo esc_url( $secure_order_download_url ); ?>"
                                                   target="_blank"><?php esc_html_e( 'Download', 'woocommerce-api-manager' ); ?></a>
												<?php
											} else {
												esc_html_e( 'Disabled', 'woocommerce-api-manager' );
											}
											?>
                                            <br>
                                            <hr>
											<?php
											if ( ! empty( $dropbox_app_key ) && ! empty( $download ) && $find_amazon_s3_in_url ) {
												?>
                                                <a href="<?php echo esc_url( $secure_s3_url ); ?>"
                                                   class="dropbox-saver nobr"></a>
												<?php
											} elseif ( ! empty( $dropbox_app_key ) && ! empty( $download ) && ! $find_amazon_s3_in_url ) { ?>
                                                <a href="<?php echo esc_url( $secure_order_download_url ); ?>"
                                                   class="dropbox-saver nobr"></a>
												<?php
											} elseif ( empty( $dropbox_app_key ) ) {
												echo '&nbsp;';
											} else {
												esc_html_e( 'Disabled', 'woocommerce-api-manager' );
											}
											?>
                                        </td>
                                    </tr>
									<?php
								} // end  if not $expired
							} // end if is WC Sub
						} // end if $download
					} // end if $is_api
				}  // end user_orders
				?>
                </tbody>
            </table>
		<?php } else { ?>
            <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
                <a class="woocommerce-Button button"
                   href="<?php echo esc_url( apply_filters( 'wc_api_manager_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
					<?php esc_html_e( 'Go shop', 'woocommerce-api-manager' ) ?>
                </a>
				<?php esc_html_e( 'No API downloads available yet.', 'woocommerce-api-manager' ); ?>
            </div>
		<?php } // end if user_orders
		?>
	<?php } else { ?>
        <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
			<?php esc_html_e( 'This account has been disabled.', 'woocommerce-api-manager' ); ?>
        </div>
	<?php } // end if master API key not disabled
	//print( 'Executed in ' . round( ( microtime( true ) - $time_start ), 6 ) . ' seconds' );
	?>
<?php } // end if user_id

if ( ! empty( $dropbox_app_key ) ) {
	?>
    <script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs"
            data-app-key="<?php esc_attr_e( $dropbox_app_key ) ?>"></script>
<?php }