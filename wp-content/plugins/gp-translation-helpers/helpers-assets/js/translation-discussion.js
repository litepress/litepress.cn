jQuery( function( $ ) {
	$('.helper-translation-discussion').on( 'click', '.comments-selector a', function( e ){
		e.preventDefault();
		var $comments = jQuery(e.target).parents('h6').next('.discussion-list');
		var selector = $(e.target).data('selector');
		if ( 'all' === selector  ) {
			$comments.children().show();
		} else {
			$comments.children().hide();
			$comments.children( '.comment-locale-' + selector ).show();
		}
		return false;
	} );
	$('.helper-translation-discussion').on( 'submit', '.comment-form', function( e ){
		e.preventDefault();
		var $commentform = $( e.target );
		var formdata = {
			content: $commentform.find('textarea[name=comment]').val(),
			post: $commentform.attr('id').split( '-' )[ 1 ],
			meta: {
				translation_id : $commentform.find('input[name=translation_id]').val(),
				locale         : $commentform.find('input[name=comment_locale]').val()
			}
		}
		jQuery.wpcom_proxy_request( {
				method: 'POST',
				apiNamespace: 'wp/v2',
				path: '/sites/translate.wordpress.com/comments',
				body: formdata
			}
		).done( function( response ){
			if ( 'undefined' !== typeof ( response.data ) ) {
				// There's probably a better way, but response.data is only set for errors.
				// TODO: error handling.
			} else {
				$commentform.find('textarea[name=comment]').val('');
				$gp.translation_helpers.fetch( 'comments' );
			}
		} );

		return false;
	});
});