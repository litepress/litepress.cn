/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

jQuery( function ( $ ) {
	// Handle dependencies.
	function dependencies_handler( id, deps, values, type ) {
		var result = true;
		if ( typeof ( deps ) == 'string' ) {
			if ( deps.substr( 0, 6 ) === ':radio' ) {
				deps = deps + ':checked';
			}

			var depsOn     = $( deps ),
				depsOnType = depsOn.attr( 'type' ),
				val        = depsOn.val();

			switch ( depsOnType ) {
				case 'checkbox':
					val = depsOn.is( ':checked' ) ? 'yes' : 'no';
					break;
				case 'radio':
					val = depsOn.find( 'input[type="radio"]' ).filter( ':checked' ).val();
					break;
			}

			values = values.split( ',' );

			for ( var i = 0; i < values.length; i++ ) {
				if ( val != values[ i ] ) {
					result = false;
				} else {
					result = true;
					break;
				}
			}
		}

		var $current_field     = $( id ),
			$current_container = $( id + '-container' ).closest( 'tr' ); // container for YIT Plugin Panel

		if ( $current_container.length < 1 ) {
			// container for YIT Plugin Panel WooCommerce
			$current_container = $current_field.closest( '.yith-plugin-fw-panel-wc-row, .yith-toggle-content-row' );
		}

		var types = type.split( '-' ), j;
		for ( j in types ) {
			var current_type = types[ j ];

			if ( !result ) {
				switch ( current_type ) {
					case 'disable':
						$current_container.addClass( 'yith-disabled' );
						$current_field.attr( 'disabled', true );
						break;
					case 'hide':
					case 'hideNow':
						$current_container.hide();
						break;
					case 'hideme':
						$current_field.hide();
						break;
					case 'fadeInOut':
					case 'fadeOut':
						$current_container.hide( 500 );
						break;
					case 'fadeIn':
					default:
						$current_container.hide();
				}
			} else {
				switch ( current_type ) {
					case 'disable':
						$current_container.removeClass( 'yith-disabled' );
						$current_field.attr( 'disabled', false );
						break;
					case 'hide':
					case 'hideNow':
						$current_container.show();
						break;
					case 'hideme':
						$current_field.show();
						break;
					case 'fadeOut':
						$current_container.show();
						break;
					case 'fadeInOut':
					case 'fadeIn':
					default:
						$current_container.show( 500 );
				}
			}
		}
	}

	function init_dependencies() {
		$( '[data-dep-target]:not( .deps-initialized )' ).each( function () {
			var t = $( this );

			if ( t.closest( '.metaboxes-tab' ).length ) {
				// Let meta-boxes handle their own deps.
				return;
			}

			// init field deps
			t.addClass( 'deps-initialized' );

			var field = '#' + t.data( 'dep-target' ),
				dep   = '#' + t.data( 'dep-id' ),
				value = t.data( 'dep-value' ),
				type  = t.data( 'dep-type' );

			$( dep ).on( 'change', function () {
				dependencies_handler( field, dep, value.toString(), type );
			} ).trigger( 'change' );
		} );
	}

	init_dependencies();
	// re-init deps after an add toggle action
	$( document ).on( 'yith-add-box-button-toggle', init_dependencies );

	//connected list
	$( '.rm_connectedlist' ).each( function () {
		var ul       = $( this ).find( 'ul' );
		var input    = $( this ).find( ':hidden' );
		var sortable = ul.sortable( {
										connectWith: ul,
										update     : function ( event, ui ) {
											var value = {};

											ul.each( function () {
												var options = {};

												$( this ).children().each( function () {
													options[ $( this ).data( 'option' ) ] = $( this ).text();
												} );

												value[ $( this ).data( 'list' ) ] = options;
											} );

											input.val( ( JSON.stringify( value ) ).replace( /[\\"']/g, '\\$&' ).replace( /\u0000/g, '\\0' ) );
										}
									} ).disableSelection();
	} );

	//google analytics generation
	$( function () {
		$( '.google-analytic-generate' ).click( function () {
			var editor   = $( '#' + $( this ).data( 'textarea' ) ).data( 'codemirrorInstance' );
			var gatc     = $( '#' + $( this ).data( 'input' ) ).val();
			var basename = $( this ).data( 'basename' );

			var text = "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){\n";
			text += "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement( o ),\n";
			text += "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)\n";
			text += "})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n\n";
			text += "ga('create', '" + gatc + "', '" + basename + "');\n";
			text += "ga('send', 'pageview');\n";
			editor.replaceRange(
				text,
				editor.getCursor( 'start' ),
				editor.getCursor( 'end' )
			);
		} );
	} );


	// Prevent the WC message for changes when leaving the panel page
	$( '.yith-plugin-fw-panel .woo-nav-tab-wrapper' ).removeClass( 'woo-nav-tab-wrapper' ).addClass( 'yith-nav-tab-wrapper' );

	var wrap    = $( '.wrap.yith-plugin-ui' ).first(),
		notices = $( 'div.updated, div.error, div.notice' );

	// Prevent moving notices into the wrapper
	notices.addClass( 'inline' );
	if ( wrap.length ) {
		wrap.prepend( notices );
	}


	// Additional wrapping just in case 'wrap' div is placed within a sub-tab and it's not already wrapped twice.
	// TODO: Deprecated usage, it'll be removed, since also custom panels should use the automatic-wrapping through 'show_container' param.
	( function () {
		var active_subnav = $( '.yith-nav-sub-tab.nav-tab-active' ),
			subnav_wrap   = $( '.yith-plugin-fw-wp-page__sub-tab-wrap' );

		if ( active_subnav.length && !subnav_wrap.length ) {
			var mainWrapper = $( '.yith-plugin-fw-wp-page-wrapper' );
			if ( !mainWrapper.length ) {
				mainWrapper = $( '#wpbody-content > .yith-plugin-ui' );
			}

			if ( mainWrapper ) {
				var defaultWrap = mainWrapper.find( '.yit-admin-panel-content-wrap' ); // at first, search for default wrap.
				if ( defaultWrap.length ) {
					defaultWrap.addClass( 'has-subnav' );
				} else {
					// try to wrap a generic wrap div in main wrapper
					mainWrapper.find( '.wrap' ).wrap( '<div class="yith-plugin-fw-wp-page__sub-tab-wrap"></div>' );
				}
			}
		}
	} )();

	// Float save button.
	( function () {
		var floatSaveButton = $( '#yith-plugin-fw-float-save-button' ),
			mainForm        = $( '#plugin-fw-wc' ),
			saveButton      = document.querySelector( '#main-save-button' );

		function updateValuesForSpecialEditors() {
			if ( 'tinyMCE' in window && 'triggerSave' in window.tinyMCE ) {
				// Trigger saving to serialize the correct value for WP Editors.
				window.tinyMCE.triggerSave();
			}

			// Trigger saving to serialize the correct value for each Codemirror Editor.
			$( '.codemirror.codemirror--initialized' ).each( function () {
				var editor = $( this ).data( 'codemirrorInstance' ) || false;
				if ( editor && 'codemirror' in editor ) {
					editor.codemirror.save();
				}
			} );
		}

		function checkButtonPosition() {
			if ( isInViewport( saveButton ) ) {
				floatSaveButton.removeClass( 'visible' );
			} else {
				floatSaveButton.addClass( 'visible' );
			}
		}

		function isInViewport( el ) {
			var rect     = el.getBoundingClientRect(),
				viewport = {
					width : window.innerWidth || document.documentElement.clientWidth,
					height: window.innerHeight || document.documentElement.clientHeight
				};
			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.top <= viewport.height &&
				rect.left <= viewport.width
			);
		}

		if ( floatSaveButton.length > 0 && mainForm.length > 0 ) {
			checkButtonPosition();
			document.addEventListener( 'scroll', checkButtonPosition, { passive: true } );

			$( document ).on( 'click', '#yith-plugin-fw-float-save-button', function ( e ) {
				e.preventDefault();

				updateValuesForSpecialEditors();

				floatSaveButton.block(
					{
						message   : null,
						overlayCSS: {
							background: 'transparent',
							opacity   : 0.6
						}
					}
				);
				$.post( document.location.href, mainForm.serialize() )
					.done( function ( response ) {
						floatSaveButton.unblock()
							.addClass( 'green' )
							.fadeOut( 300 )
							.html( '<i class="yith-icon yith-icon-check"></i>' + floatSaveButton.data( 'saved-label' ) )
							.fadeIn( 300 )
							.delay( 2500 )
							.queue(
								function ( next ) {
									floatSaveButton.fadeOut(
										500,
										function () {
											$( this ).removeClass( 'green' );
											$( this ).html( '<i class="yith-icon yith-icon-save"></i>' + $( this ).data( 'default-label' ) ).fadeIn( 500 );
										}
									);
									next();
								} );

						// Prevent WooCommerce warning for changes without saving.
						window.onbeforeunload = null;

						$( document ).trigger( 'yith-plugin-fw-float-save-button-after-saving', [response] );
					} );
			} )
		}
	} )();

} );
