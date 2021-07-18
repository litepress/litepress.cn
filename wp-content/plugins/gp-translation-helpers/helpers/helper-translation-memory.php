<?php

class Helper_Translation_Memory extends GP_Translation_Helper {

	public $priority = 1;
	public $title = 'Translation Memory';
	public $has_async_content = true;


	function activate() {
		return class_exists( 'GP_Translation_Memory' );
	}

	function get_async_content() {
		$original = GP::$original->get( $this->data['original_id'] );
		if ( ! $original ) {
			return false;
		}

		$suggestions = GP_Translation_Memory::get_suggestions( $original->id,  $original->singular, $this->data['locale_slug'] );
		return $suggestions;
	}

	function async_output_callback( $items ) {
		$output = '<ul class="suggestions">';
		foreach ( $items as $suggestion ) {
			$output .= '<li>';
			if ( $suggestion['diff'] ) {
				$output .= '<span class="score has-diff">';
				$output .= '<span class="original-diff">' . wp_kses_post( $suggestion['diff'] ) . '</span>';
			} else {
				$output .= '<span class="score">';
			}
			$output .= esc_html( number_format( 100 * $suggestion['similarity_score'] ) ) . '%</span>';
			$output .= '<span class="translation">' . esc_html( $suggestion['translation']['translation_0'] ) . '</span>';
			$output .= '<a class="copy-suggestion" href="#">copy this</a>';
			$output .= '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	function empty_content() {
		return 'No suggestions found!';
	}

	function get_css() {
		return GP_Translation_Memory::get_css();
	}

	function get_js() {
		return <<<JS
jQuery( function( $ ) {
	$( '#translations').on( 'click', 'a.copy-suggestion', function() {
		var original_text = $(this).prev( '.translation' ).text();
		original_text = original_text.replace( /<span class=.invisibles.*?<\/span>/g, '' );
		$(this).parents('.editor').find( 'textarea' ).val( original_text ).focus();
		new Image().src = document.location.protocol+'//pixel.wp.com/g.gif?v=wpcom-no-pv&x_gp-translation-memory=copy&baba='+Math.random();
	} );
} );
JS;
	}
}
