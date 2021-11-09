<?php

function wpcf7_autop( $pee, $br = 1 ) {
	if ( trim( $pee ) === '' ) {
		return '';
	}

	$pee = $pee . "\n"; // just to make things a little easier, pad the end
	$pee = preg_replace( '|<br />\s*<br />|', "\n\n", $pee );
	// Space things out a little
	/* wpcf7: remove select and input */
	$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
	$pee = preg_replace( '!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee );
	$pee = preg_replace( '!(</' . $allblocks . '>)!', "$1\n\n", $pee );

	/* wpcf7: take care of [response], [recaptcha], and [hidden] tags */
	$form_tags_manager = WPCF7_FormTagsManager::get_instance();
	$block_hidden_form_tags = $form_tags_manager->collect_tag_types(
		array( 'display-block', 'display-hidden' ) );
	$block_hidden_form_tags = sprintf( '(?:%s)',
		implode( '|', $block_hidden_form_tags ) );

	$pee = preg_replace( '!(\[' . $block_hidden_form_tags . '[^]]*\])!',
		"\n$1\n\n", $pee );

	$pee = str_replace( array( "\r\n", "\r" ), "\n", $pee ); // cross-platform newlines

	if ( strpos( $pee, '<object' ) !== false ) {
		$pee = preg_replace( '|\s*<param([^>]*)>\s*|', "<param$1>", $pee ); // no pee inside object/embed
		$pee = preg_replace( '|\s*</embed>\s*|', '</embed>', $pee );
	}

	$pee = preg_replace( "/\n\n+/", "\n\n", $pee ); // take care of duplicates
	// make paragraphs, including one at the end
	$pees = preg_split( '/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY );
	$pee = '';

	foreach ( $pees as $tinkle ) {
		$pee .= '<p>' . trim( $tinkle, "\n" ) . "</p>\n";
	}

	$pee = preg_replace( '!<p>([^<]+)</(div|address|form|fieldset)>!', "<p>$1</p></$2>", $pee );

	$pee = preg_replace( '|<p>\s*</p>|', '', $pee ); // under certain strange conditions it could create a P of entirely whitespace

	$pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee ); // don't pee all over a tag
	$pee = preg_replace( "|<p>(<li.+?)</p>|", "$1", $pee ); // problem with nested lists
	$pee = preg_replace( '|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee );
	$pee = str_replace( '</blockquote></p>', '</p></blockquote>', $pee );
	$pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee );
	$pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee );

	/* wpcf7: take care of [response], [recaptcha], and [hidden] tag */
	$pee = preg_replace( '!<p>\s*(\[' . $block_hidden_form_tags . '[^]]*\])!',
		"$1", $pee );
	$pee = preg_replace( '!(\[' . $block_hidden_form_tags . '[^]]*\])\s*</p>!',
		"$1", $pee );

	if ( $br ) {
		/* wpcf7: add textarea */
		$pee = preg_replace_callback(
			'/<(script|style|textarea).*?<\/\\1>/s',
			'wpcf7_autop_preserve_newline_callback', $pee );
		$pee = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $pee ); // optionally make line breaks
		$pee = str_replace( '<WPPreserveNewline />', "\n", $pee );

		/* wpcf7: remove extra <br /> just added before [response], [recaptcha], and [hidden] tags */
		$pee = preg_replace( '!<br />\n(\[' . $block_hidden_form_tags . '[^]]*\])!',
			"\n$1", $pee );
	}

	$pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee );
	$pee = preg_replace( '!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee );

	if ( strpos( $pee, '<pre' ) !== false ) {
		$pee = preg_replace_callback( '!(<pre[^>]*>)(.*?)</pre>!is',
			'clean_pre', $pee );
	}

	$pee = preg_replace( "|\n</p>$|", '</p>', $pee );

	return $pee;
}

function wpcf7_autop_preserve_newline_callback( $matches ) {
	return str_replace( "\n", '<WPPreserveNewline />', $matches[0] );
}

function wpcf7_sanitize_query_var( $text ) {
	$text = wp_unslash( $text );
	$text = wp_check_invalid_utf8( $text );

	if ( false !== strpos( $text, '<' ) ) {
		$text = wp_pre_kses_less_than( $text );
		$text = wp_strip_all_tags( $text );
	}

	$text = preg_replace( '/%[a-f0-9]{2}/i', '', $text );
	$text = preg_replace( '/ +/', ' ', $text );
	$text = trim( $text, ' ' );

	return $text;
}

function wpcf7_strip_quote( $text ) {
	$text = trim( $text );

	if ( preg_match( '/^"(.*)"$/s', $text, $matches ) ) {
		$text = $matches[1];
	} elseif ( preg_match( "/^'(.*)'$/s", $text, $matches ) ) {
		$text = $matches[1];
	}

	return $text;
}

function wpcf7_strip_quote_deep( $arr ) {
	if ( is_string( $arr ) ) {
		return wpcf7_strip_quote( $arr );
	}

	if ( is_array( $arr ) ) {
		$result = array();

		foreach ( $arr as $key => $text ) {
			$result[$key] = wpcf7_strip_quote_deep( $text );
		}

		return $result;
	}
}

function wpcf7_normalize_newline( $text, $to = "\n" ) {
	if ( ! is_string( $text ) ) {
		return $text;
	}

	$nls = array( "\r\n", "\r", "\n" );

	if ( ! in_array( $to, $nls ) ) {
		return $text;
	}

	return str_replace( $nls, $to, $text );
}

function wpcf7_normalize_newline_deep( $arr, $to = "\n" ) {
	if ( is_array( $arr ) ) {
		$result = array();

		foreach ( $arr as $key => $text ) {
			$result[$key] = wpcf7_normalize_newline_deep( $text, $to );
		}

		return $result;
	}

	return wpcf7_normalize_newline( $arr, $to );
}

function wpcf7_strip_newline( $str ) {
	$str = (string) $str;
	$str = str_replace( array( "\r", "\n" ), '', $str );
	return trim( $str );
}

function wpcf7_canonicalize( $text, $strto = 'lower' ) {
	if ( function_exists( 'mb_convert_kana' )
	and 'UTF-8' == get_option( 'blog_charset' ) ) {
		$text = mb_convert_kana( $text, 'asKV', 'UTF-8' );
	}

	if ( 'lower' == $strto ) {
		$text = strtolower( $text );
	} elseif ( 'upper' == $strto ) {
		$text = strtoupper( $text );
	}

	$text = trim( $text );
	return $text;
}

function wpcf7_sanitize_unit_tag( $tag ) {
	$tag = preg_replace( '/[^A-Za-z0-9_-]/', '', $tag );
	return $tag;
}

function wpcf7_antiscript_file_name( $filename ) {
	$filename = wp_basename( $filename );

	$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
	$filename = preg_replace( '/[\pC\pZ]+/iu', '', $filename );

	$parts = explode( '.', $filename );

	if ( count( $parts ) < 2 ) {
		return $filename;
	}

	$script_pattern = '/^(php|phtml|pl|py|rb|cgi|asp|aspx)\d?$/i';

	$filename = array_shift( $parts );
	$extension = array_pop( $parts );

	foreach ( (array) $parts as $part ) {
		if ( preg_match( $script_pattern, $part ) ) {
			$filename .= '.' . $part . '_';
		} else {
			$filename .= '.' . $part;
		}
	}

	if ( preg_match( $script_pattern, $extension ) ) {
		$filename .= '.' . $extension . '_.txt';
	} else {
		$filename .= '.' . $extension;
	}

	return $filename;
}


/**
 * Masks a password with asterisks (*).
 *
 * @param int $right Length of right-hand unmasked text. Default 0.
 * @param int $left Length of left-hand unmasked text. Default 0.
 * @return string Text of masked password.
 */
function wpcf7_mask_password( $text, $right = 0, $left = 0 ) {
	$length = strlen( $text );

	$right = absint( $right );
	$left = absint( $left );

	if ( $length < $right + $left ) {
		$right = $left = 0;
	}

	if ( $length <= 48 ) {
		$masked = str_repeat( '*', $length - ( $right + $left ) );
	} elseif ( $right + $left < 48 ) {
		$masked = str_repeat( '*', 48 - ( $right + $left ) );
	} else {
		$masked = '****';
	}

	$left_unmasked = $left ? substr( $text, 0, $left ) : '';
	$right_unmasked = $right ? substr( $text, -1 * $right ) : '';

	$text = $left_unmasked . $masked . $right_unmasked;

	return $text;
}
