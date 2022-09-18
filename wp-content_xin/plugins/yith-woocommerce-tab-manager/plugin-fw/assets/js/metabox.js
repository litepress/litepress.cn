( function ( $ ) {

	// TODO: check if someone is directly using it, otherwise it could be removed, since it's not used by the fw.
	var actPageOptionContainer = $( '#_active_page_options-container' ),
		actPageOption          = actPageOptionContainer.parent().html();
	actPageOptionContainer.parent().remove();
	$( actPageOption ).insertAfter( '#yit-post-setting .handlediv' );
	$( actPageOption ).insertAfter( '#yit-page-setting .handlediv' );

	actPageOptionContainer.on( 'click', function () {
		if ( $( '#_active_page_options' ).is( ":checked" ) ) {
			$( '#yit-page-setting .inside .metaboxes-tab, #yit-post-setting .inside .metaboxes-tab' ).css( {
																											   'opacity'       : 1,
																											   'pointer-events': 'auto'
																										   } );
		} else {
			$( '#yit-page-setting .inside .metaboxes-tab, #yit-post-setting .inside .metaboxes-tab' ).css( {
																											   'opacity'       : 0.5,
																											   'pointer-events': 'none'
																										   } );
		}
	} ).trigger( 'click' );


	//dependencies handler
	$( document.body ).on( 'yith-plugin-fw-metabox-init-deps', function () {
		$( document.body ).trigger( 'yith-plugin-fw-init-radio' );
		$( '.metaboxes-tab [data-dep-target]:not(.yith-plugin-fw-metabox-deps-initialized)' ).each( function () {
			var t = $( this );

			var field = '#' + t.data( 'dep-target' ),
				dep   = '#' + t.data( 'dep-id' ),
				value = t.data( 'dep-value' ),
				type  = t.data( 'dep-type' );


			dependencies_handler( field, dep, value.toString(), type );

			$( dep ).on( 'change', function () {
				dependencies_handler( field, dep, value.toString(), type );
			} ).change();

			t.addClass( 'yith-plugin-fw-metabox-deps-initialized' );
		} );
	} ).trigger( 'yith-plugin-fw-metabox-init-deps' );

	//Handle dependencies.
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
			$current_container = $( id + '-container' ).parent();

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

} )( jQuery );