/* global WooAlipay */
jQuery( document ).ready( function( $ ) {

	$( '#woo_alipay_test_connection' ).on( 'click', function( e ) {
		e.preventDefault();

		var spinner      = $('.woo-alipay-settings .spinner'),
			failure      = $('.woo-alipay-settings .test-status .failure, .woo-alipay-settings .test-status-message.failure'),
			error        = $('.woo-alipay-settings .test-status .error, .woo-alipay-settings .test-status-message.error'),
			success      = $('.woo-alipay-settings .test-status .success, .woo-alipay-settings .test-status-message.success'),
			help         = $('.woo-alipay-settings .description.help'),
			data         = {
			nonce : $('#woo_alipay_nonce').val(),
			action: 'woo_alipay_test_connection'
		};

		spinner.addClass('is-active');
		failure.removeClass('is-active');
		error.removeClass('is-active');
		success.removeClass('is-active');

		$.ajax( {
			url: WooAlipay.ajax_url,
			type: 'POST',
			data: data
		} ).done( function( response ) {
			spinner.removeClass('is-active');
			window.console.log( response );

			if ( response.success ) {
				success.addClass('is-active');
				help.removeClass('is-active');
			} else {

				if ( response.data ) {
					error.addClass('is-active');
					help.removeClass('is-active');
				} else {
					failure.addClass('is-active');
					help.removeClass('is-active');
				}
			}
		} ).fail( function( qXHR, textStatus ) {
			WooAlipay.debug && window.console.log( textStatus );
			spinner.removeClass('is-active');
			success.removeClass('is-active');
			help.removeClass('is-active');
			failure.addClass('is-active');
		} );
	} );

	$('.woo-alipay-config-help .handlediv, .woo-alipay-config-help .handle').on('click', function(e){
		e.preventDefault();

		$('.woo-alipay-config-help').toggleClass('closed');
	});

} );