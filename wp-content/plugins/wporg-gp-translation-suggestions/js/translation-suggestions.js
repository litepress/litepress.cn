( function( $ ){
	function fetchSuggestions( $container, apiUrl, originalId, nonce ) {
		var xhr = $.ajax( {
			url: apiUrl,
			data: {
				'original': originalId,
				'nonce': nonce
			},
			dataType: 'json',
			cache: false,
		} );

		xhr.done( function( response ) {
			$container.find( '.suggestions__loading-indicator' ).remove();
			if ( response.success ) {
				$container.append( response.data );
			} else {
				$container.append( $( '<span/>', { 'text': 'Error while loading suggestions.' } ) );
			}
			$container.addClass( 'initialized' );
		} );

		xhr.fail( function() {
			$container.find( '.suggestions__loading-indicator' ).remove();
			$container.append( $( '<span/>', { 'text': 'Error while loading suggestions.' } ) );
			$container.addClass( 'initialized' );
		} );

		xhr.always( function() {
			$container.removeClass( 'fetching' );
		} );
	}

	function maybeFetchTranslationMemorySuggestions() {
		var $container = $gp.editor.current.find( '.suggestions__translation-memory' );
		if ( !$container.length ) {
			return;
		}

		if ( $container.hasClass( 'initialized' ) || $container.hasClass( 'fetching' ) ) {
			return;
		}

		$container.addClass( 'fetching' );

		var originalId = $gp.editor.current.original_id;
		var nonce = $container.data( 'nonce' );

		fetchSuggestions( $container, window.WPORG_TRANSLATION_MEMORY_API_URL, originalId, nonce );
	}

	function maybeFetchOtherLanguageSuggestions() {
		var $container = $gp.editor.current.find( '.suggestions__other-languages' );
		if ( ! $container.length ) {
			return;
		}

		if ( $container.hasClass( 'initialized' ) || $container.hasClass( 'fetching' ) ) {
			return;
		}

		$container.addClass( 'fetching' );

		var originalId = $gp.editor.current.original_id;
		var nonce = $container.data( 'nonce' );

		fetchSuggestions( $container, window.WPORG_OTHER_LANGUAGES_API_URL, originalId , nonce );
	}

	function copySuggestion( event ) {
		if ( 'A' === event.target.tagName ) {
			return;
		}

		var $el = $( this ).closest( '.translation-suggestion' );
		var $translation = $el.find( '.translation-suggestion__translation-raw');
		if ( ! $translation.length ) {
			return;
		}

		var $activeTextarea = $gp.editor.current.find( '.textareas.active textarea' );
		if ( ! $activeTextarea.length ) {
			return;
		}

		$activeTextarea.val( $translation.text() ).focus();

		// Trigger input event for autosize().
		var event = new Event( 'input' );
		$activeTextarea[0].dispatchEvent( event );
	}

	$gp.editor.show = ( function( original ) {
		return function() {
			original.apply( $gp.editor, arguments );

			maybeFetchTranslationMemorySuggestions();
			maybeFetchOtherLanguageSuggestions();
		}
	})( $gp.editor.show );

	$gp.editor.install_hooks = ( function( original ) {
		return function() {
			original();

			$( $gp.editor.table )
				.on( 'click', '.translation-suggestion', copySuggestion );
		}
	})( $gp.editor.install_hooks );

})( jQuery );
