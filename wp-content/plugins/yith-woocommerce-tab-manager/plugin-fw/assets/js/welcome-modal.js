/* global jQuery, yith, wp */
jQuery( function ( $ ) {
	"use strict";

	var template = wp.template( 'yith-plugin-fw-welcome-modal' ),
		content  = $( template( {} ) ),
		footer   = content.find( '.yith-plugin-fw-welcome__footer' );

	yith.ui.modal(
		{
			content                   : content,
			footer                    : footer,
			classes                   : {
				wrap: 'yith-plugin-fw-welcome-modal'
			},
			width                     : '600px',
			closeWhenClickingOnOverlay: false,
			onClose                   : function () {
				var location = footer.find( '.yith-plugin-fw-welcome__close' ).attr( 'href' );
				if ( location ) {
					window.location.href = location;
				}
			}
		}
	);

} );