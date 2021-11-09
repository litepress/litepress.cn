<?php

add_action( 'wpcf7_admin_menu', 'wpcf7_admin_init_bulk_cv', 10, 0 );

function wpcf7_admin_init_bulk_cv() {
	if ( ! wpcf7_validate_configuration()
	or ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
		return;
	}

	$result = WPCF7::get_option( 'bulk_validate' );
	$last_important_update = '5.1.5';

	if ( ! empty( $result['version'] )
	and version_compare( $last_important_update, $result['version'], '<=' ) ) {
		return;
	}

	add_filter( 'wpcf7_admin_menu_change_notice',
		'wpcf7_admin_menu_change_notice_bulk_cv', 10, 1 );

	add_action( 'wpcf7_admin_warnings',
		'wpcf7_admin_warnings_bulk_cv', 5, 3 );
}

function wpcf7_admin_menu_change_notice_bulk_cv( $counts ) {
	$counts['wpcf7'] += 1;
	return $counts;
}

function wpcf7_admin_warnings_bulk_cv( $page, $action, $object ) {
	if ( 'wpcf7' === $page and 'validate' === $action ) {
		return;
	}

	$link = wpcf7_link(
		add_query_arg(
			array( 'action' => 'validate' ),
			menu_page_url( 'wpcf7', false )
		),
		__( 'Validate Contact Form 7 Configuration', 'contact-form-7' )
	);

	$message = __( "Misconfiguration leads to mail delivery failure or other troubles. Validate your contact forms now.", 'contact-form-7' );

	echo sprintf(
		'<div class="notice notice-warning"><p>%1$s &raquo; %2$s</p></div>',
		esc_html( $message ),
		$link
	);
}

add_action( 'wpcf7_admin_load', 'wpcf7_load_bulk_validate_page', 10, 2 );

function wpcf7_load_bulk_validate_page( $page, $action ) {
	if ( 'wpcf7' != $page
	or 'validate' != $action
	or ! wpcf7_validate_configuration()
	or 'POST' != $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	check_admin_referer( 'wpcf7-bulk-validate' );

	if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
		wp_die( __( "You are not allowed to validate configuration.", 'contact-form-7' ) );
	}

	$contact_forms = WPCF7_ContactForm::find();

	$result = array(
		'timestamp' => time(),
		'version' => WPCF7_VERSION,
		'count_valid' => 0,
		'count_invalid' => 0,
	);

	foreach ( $contact_forms as $contact_form ) {
		$config_validator = new WPCF7_ConfigValidator( $contact_form );
		$config_validator->validate();
		$config_validator->save();

		if ( $config_validator->is_valid() ) {
			$result['count_valid'] += 1;
		} else {
			$result['count_invalid'] += 1;
		}
	}

	WPCF7::update_option( 'bulk_validate', $result );

	$redirect_to = add_query_arg(
		array(
			'message' => 'validated',
		),
		menu_page_url( 'wpcf7', false )
	);

	wp_safe_redirect( $redirect_to );
	exit();
}

function wpcf7_admin_bulk_validate_page() {
	$contact_forms = WPCF7_ContactForm::find();
	$count = WPCF7_ContactForm::count();

	$submit_text = sprintf(
		_n(
			/* translators: %s: number of contact forms */
			"Validate %s contact form now",
			"Validate %s contact forms now",
			$count, 'contact-form-7'
		),
		number_format_i18n( $count )
	);

?>
<div class="wrap">

<h1><?php echo esc_html( __( 'Validate Configuration', 'contact-form-7' ) ); ?></h1>

<form method="post" action="">
	<input type="hidden" name="action" value="validate" />
	<?php wp_nonce_field( 'wpcf7-bulk-validate' ); ?>
	<p><input type="submit" class="button" value="<?php echo esc_attr( $submit_text ); ?>" /></p>
</form>

<?php
	echo wpcf7_link(
		__( 'https://contactform7.com/configuration-validator-faq/', 'contact-form-7' ),
		__( 'FAQ about Configuration Validator', 'contact-form-7' )
	);
?>

</div>
<?php
}
