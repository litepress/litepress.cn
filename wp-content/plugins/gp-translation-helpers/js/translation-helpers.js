$gp.translation_helpers = (
	function( $ ) {
		return {
			init: function( table ) {
				$gp.translation_helpers.table = table;
				$gp.translation_helpers.install_hooks();
			},
			install_hooks: function() {
				$( $gp.translation_helpers.table )
					.on( 'beforeShow', '.editor', $gp.translation_helpers.hooks.initial_fetch )
					.on( 'click', '.helpers-tabs li', $gp.translation_helpers.hooks.tab_select );
			},
			initial_fetch : function( $element ) {
				var $helpers = $element.find('.translation-helpers');

				if ( $helpers.hasClass('loaded') || $helpers.hasClass('loading') ) {
					return;
				}

				$gp.translation_helpers.fetch( false, $element);
			},
			fetch : function( which, $element ) {
				var $helpers;
				if ( $element ) {
					$helpers = $element.find('.translation-helpers');
				} else {
					$helpers = $( '.editor:visible' ).find('.translation-helpers').first();
				}

				var originalId  = $helpers.parent().attr('row');
				var requestUrl = $gp_translation_helpers_settings.th_url  + originalId + '?nohc';

				if ( which ) {
					requestUrl = requestUrl + '&helper=' + which;
				}

				$helpers.addClass('loading');

				$.getJSON(
					requestUrl,
					function( data ){
						$helpers.addClass('loaded').removeClass('loading');
						$.each( data, function( id, result ){
							jQuery('.helpers-tabs li[data-tab="' + id +'"]').find('.count').text( '(' + result.count + ')' );
							$( '#'  + id ).find('.loading').remove();
							$( '#'  + id ).find('.async-content').html( result.content );
						} );

					}
				);
			},
			tab_select: function( $tab ) {
				var tab_id = $tab.attr('data-tab');

				$tab.siblings().removeClass( 'current');
				$tab.parents('.translation-helpers ').find('.helper').removeClass('current');

				$tab.addClass('current');
				$("#"+tab_id).addClass('current');
			},
			hooks: {
				initial_fetch: function() {
					$gp.translation_helpers.initial_fetch( $( this ) );
					return false;
				},
				tab_select: function() {
					$gp.translation_helpers.tab_select( $( this ) );
					return false;
				}
			}
		}
	}( jQuery )
);

jQuery( function( $ ) {
	$gp.translation_helpers.init( $( '.translations' ) );
	if ( typeof window.newShowFunctionAttached === 'undefined' ) {
		window.newShowFunctionAttached = true;
		var _oldShow = $.fn.show;
		$.fn.show = function( speed, oldCallback ) {
			return $( this ).each( function() {
				var obj = $( this ),
					newCallback = function() {
						if ( $.isFunction( oldCallback ) ) {
							oldCallback.apply( obj );
						}
					};

				obj.trigger( 'beforeShow' );
				_oldShow.apply( obj, [ speed, newCallback ] );
			} );
		}
	}
} );