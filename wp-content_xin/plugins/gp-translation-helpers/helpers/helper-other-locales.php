<?php

class Helper_Other_Locales extends GP_Translation_Helper {

	public $priority = 3;
	public $title = 'Other locales';
	public $has_async_content = true;

	function get_async_content() {
		$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $this->data['project_id'], $this->data['set_slug'], $this->data['locale_slug'] );
		if ( ! $translation_set ) {
			return;
		}

		$translations  = GP::$translation->find_many_no_map( array( 'status' => 'current', 'original_id' => $this->data['original_id'] ) );
		$translations_by_locale = array();
		foreach ( $translations as $translation ) {
			$_set = GP::$translation_set->get( $translation->translation_set_id );
			if ( $translation->translation_set_id === $translation_set->id ) {
				continue;
			}
			$translations_by_locale[ $_set->locale ] = $translation;
		}

		ksort( $translations_by_locale );

		return $translations_by_locale;
	}

	function async_output_callback( $translations ) {
		$output = '<ul class="other-locales">';
		foreach ( $translations as $locale => $translation ) {
			$output .= sprintf( '<li><span class="locale">%s</span>%s</li>', $locale, esc_translation( $translation->translation_0 ) );
		}
		$output .= '</ul>';
		return $output;
	}

	function empty_content() {
		return 'No other locales have translated this string yet.';
	}

	function get_css() {
		return <<<CSS
	.other-locales {
		list-style: none;
	}
	.other-locales li {
		clear:both;
	}
	.other-locales .locale {
		display: inline-block;
		padding: 1px 6px 0 0;
		margin: 1px 6px 1px 0;
		background: #00DA12;
		width: 44px;
		text-align: right;
		float: left;
		color: #fff;
	}
CSS;
	}
}
