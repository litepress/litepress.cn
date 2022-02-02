<?php
/**
 * Functions.
 *
 * @package YITH\PluginFramework
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yit_plugin_locate_template' ) ) {
	/**
	 * Locate the templates and return the path of the file found
	 *
	 * @param string $plugin_basename The plugin base name.
	 * @param string $path            The path.
	 * @param array  $var             Variable to make visible to the template.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yit_plugin_locate_template( $plugin_basename, $path, $var = null ) {

		$template_path = '/theme/templates/' . $path;

		$located = locate_template( array( $template_path ) );

		if ( ! $located ) {
			$located = $plugin_basename . '/templates/' . $path;
		}

		return $located;
	}
}

if ( ! function_exists( 'yit_plugin_get_template' ) ) {
	/**
	 * Retrieve a template file.
	 *
	 * @param string $plugin_basename The plugin basename.
	 * @param string $path            The path.
	 * @param mixed  $var             Variable that will be extracted to make its items visible to the template.
	 * @param bool   $return          return or print the template.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yit_plugin_get_template( $plugin_basename, $path, $var = null, $return = false ) {

		$located = yit_plugin_locate_template( $plugin_basename, $path, $var );

		if ( $var && is_array( $var ) ) {
			extract( $var ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		if ( $return ) {
			ob_start();
		}

		if ( file_exists( $located ) ) {
			include $located;
		}

		if ( $return ) {
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'yit_plugin_content' ) ) {
	/**
	 * Return post content with read more link (if needed)
	 *
	 * @param string     $what         What do you want to see (content, excerpt or something else).
	 * @param int|string $limit        Limit the content.
	 * @param string     $more_text    The "more" text.
	 * @param string     $split        The split.
	 * @param bool       $in_paragraph Defines if the content is inside a paragraph.
	 *
	 * @return string
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_plugin_content( $what = 'content', $limit = 25, $more_text = '', $split = '[...]', $in_paragraph = true ) {
		if ( 'content' === $what ) {
			$content = get_the_content( $more_text );
		} else {
			if ( 'excerpt' === $what ) {
				$content = get_the_excerpt();
			} else {
				$content = $what;
			}
		}

		if ( ! $limit ) {
			if ( 'excerpt' === $what ) {
				$content = apply_filters( 'the_excerpt', $content );
			} else {
				$content = preg_replace( '/<img[^>]+./', '', $content ); // Remove images.
				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );
			}

			return $content;
		}

		// Remove the "more" tag from the content.
		if ( preg_match( "/<(a)[^>]*class\s*=\s*(['\"])more-link\\2[^>]*>(.*?)<\/\\1>/", $content, $matches ) ) {

			if ( strpos( $matches[0], '[button' ) ) {
				$more_link = str_replace( 'href="#"', 'href="' . get_permalink() . '"', do_shortcode( $matches[3] ) );
			} else {
				$more_link = $matches[0];
			}

			$content = str_replace( $more_link, '', $content );
			$split   = '';
		}

		if ( empty( $content ) ) {
			return '';
		}
		$content = explode( ' ', $content );

		if ( ! empty( $more_text ) && ! isset( $more_link ) ) {
			$more_link = strpos( $more_text, '<a class="btn"' ) ? $more_text : '<a class="read-more' . apply_filters( 'yit_simple_read_more_classes', ' ' ) . '" href="' . get_permalink() . '">' . $more_text . '</a>';
			$split     = '';
		} elseif ( ! isset( $more_link ) ) {
			$more_link = '';
		}

		// Splitting.
		if ( count( $content ) >= $limit ) {
			$split_content = '';
			for ( $i = 0; $i < $limit; $i ++ ) {
				$split_content .= $content[ $i ] . ' ';
			}

			$content = $split_content . $split;
		} else {
			$content = implode( ' ', $content );
		}

		// Handle unclosed tags.
		$tags = array();
		preg_match_all( '/(<([\w]+)[^>]*>)/', $content, $tags_opened, PREG_SET_ORDER ); // Get all opened tags.
		foreach ( $tags_opened as $tag ) {
			$tags[] = $tag[2];
		}

		// Get all closed tags and remove them from the opened tags. Others will be closed at the end of the content.
		preg_match_all( '/(<\/([\w]+)[^>]*>)/', $content, $tags_closed, PREG_SET_ORDER );
		foreach ( $tags_closed as $tag ) {
			unset( $tags[ array_search( $tag[2], $tags, true ) ] );
		}

		// Close the tags.
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				$content .= "</$tag>";
			}
		}

		if ( ! ! $in_paragraph && 'false' !== $in_paragraph ) { // String comparison kept for backward compatibility.
			$content .= $more_link;
		}
		$content = preg_replace( '/<img[^>]+./', '', $content ); // Remove images.
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		if ( ! $in_paragraph || 'false' === $in_paragraph ) { // String comparison kept for backward compatibility.
			$content .= $more_link;
		}

		return $content;
	}
}

if ( ! function_exists( 'yit_plugin_string' ) ) {
	/**
	 * Simple echo a string, with a before and after string, only if the main string is not empty.
	 *
	 * @param string $before What there is before the main string.
	 * @param string $string The main string. If it is empty or null, the functions return null.
	 * @param string $after  What there is after the main string.
	 * @param bool   $echo   If echo or only return it.
	 *
	 * @return string The complete string, if the main string is not empty or null
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_plugin_string( $before = '', $string = '', $after = '', $echo = true ) {
		$html = '';

		if ( ! ! $string ) {
			$html = $before . $string . $after;
		}

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html;
	}
}

if ( ! function_exists( 'yit_plugin_decode_title' ) ) {
	/**
	 * Change some special characters to put easily html into a string
	 * E.G.
	 * string: This is [my title] with | a new line
	 * return: This is <span class="title-highlight">my title</span> with <br /> a new line
	 *
	 * @param string $title The string to convert.
	 *
	 * @return string  The html
	 * @since      1.0
	 * @deprecated 3.5
	 */
	function yit_plugin_decode_title( $title ) {
		$replaces = apply_filters( 'yit_title_special_characters', array() );

		return preg_replace( array_keys( $replaces ), array_values( $replaces ), $title );
	}
}

if ( ! function_exists( 'yit_plugin_get_attachment_id' ) ) {

	/**
	 * Return the ID of an attachment.
	 *
	 * @param string $url The attachment URL.
	 *
	 * @return int
	 * @since 2.0.0
	 */
	function yit_plugin_get_attachment_id( $url ) {
		$upload_dir = wp_upload_dir();
		$dir        = trailingslashit( $upload_dir['baseurl'] );

		if ( false === strpos( $url, $dir ) ) {
			return false;
		}

		$file = basename( $url );

		$query = array(
			'post_type'  => 'attachment',
			'fields'     => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'value'   => $file,
					'compare' => 'LIKE',
				),
			),
		);

		$query['meta_query'][0]['key'] = '_wp_attached_file';
		$ids                           = get_posts( $query );

		foreach ( $ids as $id ) {
			$attachment_image = wp_get_attachment_image_src( $id, 'full' );
			if ( array_shift( $attachment_image ) === $url || str_replace( 'https://', 'http://', array_shift( $attachment_image ) ) === $url ) {
				return $id;
			}
		}
		$query['meta_query'][0]['key'] = '_wp_attachment_metadata';
		$ids                           = get_posts( $query );

		foreach ( $ids as $id ) {
			$meta = wp_get_attachment_metadata( $id );
			if ( ! isset( $meta['sizes'] ) ) {
				continue;
			}

			foreach ( (array) $meta['sizes'] as $size => $values ) {
				$src = wp_get_attachment_image_src( $id, $size );
				if ( $values['file'] === $file && str_replace( 'https://', 'http://', array_shift( $src ) ) === $url ) {
					return $id;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'yit_enqueue_script' ) ) {
	/**
	 * Enqueues script.
	 * Registers the script if src provided (does NOT overwrite) and enqueues.
	 * IMPORTANT: used only in themes, since it needs the YIT_Asset class.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param string[]         $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *
	 * @author     Simone D'Amico <simone.damico@yithemes.com>
	 * @deprecated 3.5
	 */
	function yit_enqueue_script( $handle, $src, $deps = array(), $ver = false, $in_footer = true ) {
		if ( function_exists( 'YIT_Asset' ) && ! is_admin() ) {
			$enqueue = true;
			YIT_Asset()->set( 'script', $handle, compact( 'src', 'deps', 'ver', 'in_footer', 'enqueue' ) );
		} else {
			wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
		}
	}
}

if ( ! function_exists( 'yit_enqueue_style' ) ) {
	/**
	 * Enqueue Styles.
	 * IMPORTANT: used only in themes, since it needs the YIT_Asset class.
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string           $src    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param string[]         $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying stylesheet version number.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *
	 * @deprecated 3.5
	 */
	function yit_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
		if ( function_exists( 'YIT_Asset' ) ) {
			$enqueue = true;
			$who     = YIT_Asset()->get_stylesheet_handle( get_stylesheet_uri(), 'style' );
			$where   = 'before';

			if ( ! $who ) {
				$who = '';
			}

			YIT_Asset()->set( 'style', $handle, compact( 'src', 'deps', 'ver', 'media', 'enqueue' ), $where, $who );
		} else {
			wp_enqueue_style( $handle, $src, $deps, $ver, $media );
		}
	}
}

if ( ! function_exists( 'yit_get_post_meta' ) ) {
	/**
	 * Retrieve the value of a metabox.
	 * This function retrieve the value of a metabox attached to a post. It return either a single value or an array.
	 *
	 * @param int    $id   Post ID.
	 * @param string $meta The meta key to retrieve.
	 *
	 * @return mixed Single value or array. Return false is the meta doesn't exists.
	 * @since    2.0.0
	 */
	function yit_get_post_meta( $id, $meta ) {
		if ( ! strpos( $meta, '[' ) ) {
			return metadata_exists( 'post', $id, $meta ) ? get_post_meta( $id, $meta, true ) : false;
		}

		$sub_meta = explode( '[', $meta );

		$meta           = get_post_meta( $id, current( $sub_meta ), true );
		$sub_meta_count = count( $sub_meta );

		for ( $i = 1; $i < $sub_meta_count; $i ++ ) {
			$current_submeta = rtrim( $sub_meta[ $i ], ']' );
			if ( ! isset( $meta[ $current_submeta ] ) ) {
				return false;
			}
			$meta = $meta[ $current_submeta ];
		}

		return $meta;
	}
}

if ( ! function_exists( 'yit_string' ) ) {
	/**
	 * Simple echo a string, with a before and after string, only if the main string is not empty.
	 *
	 * @param string $before What there is before the main string.
	 * @param string $string The main string. If it is empty or null, the functions return null.
	 * @param string $after  What there is after the main string.
	 * @param bool   $echo   If echo or only return it.
	 *
	 * @return string The complete string, if the main string is not empty or null
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_string( $before = '', $string = '', $after = '', $echo = true ) {
		$html = '';

		if ( ! ! $string ) {
			$html = $before . $string . $after;
		}

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html;
	}
}

if ( ! function_exists( 'yit_pagination' ) ) {
	/**
	 * Print pagination
	 *
	 * @param int|string $pages The number of pages.
	 * @param int        $range The range.
	 *
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_pagination( $pages = '', $range = 10 ) {
		$pages     = ! ! $pages ? absint( $pages ) : false;
		$showitems = ( $range * 2 ) + 1;

		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : false;
		if ( false === $paged ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : false;
		}
		if ( false === $paged ) {
			$paged = 1;
		}

		$paged = absint( $paged );
		$html  = '';

		if ( false === $pages ) {
			global $wp_query;

			if ( isset( $wp_query->max_num_pages ) ) {
				$pages = $wp_query->max_num_pages;
			}

			if ( ! $pages ) {
				$pages = 1;
			}
		}

		if ( 1 !== $pages ) {
			$html .= "<div class='general-pagination clearfix'>";
			if ( $paged > 2 ) {
				$html .= sprintf( '<a class="%s" href="%s">&laquo;</a>', 'yit_pagination_first', get_pagenum_link( 1 ) );
			}
			if ( $paged > 1 ) {
				$html .= sprintf( '<a class="%s" href="%s">&lsaquo;</a>', 'yit_pagination_previous', get_pagenum_link( $paged - 1 ) );
			}

			for ( $i = 1; $i <= $pages; $i ++ ) {
				if ( 1 !== $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
					$class = ( $paged === $i ) ? 'selected' : '';

					$html .= '<a href="' . esc_url( get_pagenum_link( $i ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $i ) . '</a>';
				}
			}

			if ( $paged < $pages ) {
				$html .= sprintf( '<a class="%s" href="%s">&rsaquo;</a>', 'yit_pagination_next', esc_url( get_pagenum_link( $paged + 1 ) ) );
			}
			if ( $paged < $pages - 1 ) {
				$html .= sprintf( '<a class="%s" href="%s">&raquo;</a>', 'yit_pagination_last', esc_url( get_pagenum_link( $pages ) ) );
			}

			$html .= "</div>\n";
		}

		echo apply_filters( 'yit_pagination_html', $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'yit_registered_sidebars' ) ) {
	/**
	 * Retrieve all registered sidebars
	 *
	 * @return array
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_registered_sidebars() {
		global $wp_registered_sidebars;

		$return = array();

		if ( empty( $wp_registered_sidebars ) ) {
			$return = array( '' => '' );
		}

		foreach ( (array) $wp_registered_sidebars as $the_ ) {
			$return[ $the_['name'] ] = $the_['name'];
		}

		ksort( $return );

		return $return;
	}
}

if ( ! function_exists( 'yit_layout_option' ) ) {
	/**
	 * Retrieve a layout option
	 * IMPORTANT: used only in themes, since it needs the YIT_Layout_Panel class.
	 *
	 * @param string $key   The key.
	 * @param bool   $id    The ID.
	 * @param string $type  The type.
	 * @param string $model The model.
	 *
	 * @return array
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_layout_option( $key, $id = false, $type = 'post', $model = 'post_type' ) {
		$option = '';

		if ( defined( 'YIT' ) ) {
			$option = YIT_Layout_Panel()->get_option( $key, $id, $type, $model );
		} else {
			if ( ! $id && ( is_single() || is_page() ) ) {
				global $post;
				$id = $post->ID;
			} elseif ( 'all' !== $id ) {
				$option = get_post_meta( $id, $key );
			}
		}

		return $option;
	}
}

if ( ! function_exists( 'yit_curPageURL' ) ) {
	/**
	 * Retrieve the current complete url
	 *
	 * @since 1.0
	 */
	function yit_curPageURL() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput
		$page_url = 'http';
		if ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
			$page_url .= 's';
		}

		$page_url .= '://';

		if ( isset( $_SERVER['SERVER_PORT'] ) && 80 !== absint( $_SERVER['SERVER_PORT'] ) ) {
			$page_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		} else {
			$page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}

		return $page_url;
		// phpcs:enable
	}
}

if ( ! function_exists( 'yit_get_excluded_categories' ) ) {
	/**
	 * Retrieve the excluded categories, set on Theme Options
	 *
	 * @param int $k Key.
	 *
	 * @return string String with all id categories excluded, separated by a comma
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yit_get_excluded_categories( $k = 1 ) {
		global $post;

		if ( ! isset( $post->ID ) ) {
			return '';
		}

		$cf_cats = get_post_meta( $post->ID, 'blog-cats', true );

		if ( ! empty( $cf_cats ) ) {
			return $cf_cats;
		}

		$cats = function_exists( 'yit_get_option' ) ? yit_get_option( 'blog-excluded-cats' ) : '';

		if ( ! is_array( $cats ) || empty( $cats ) || ! isset( $cats[ $k ] ) ) {
			return '';
		}

		$cats = array_map( 'trim', $cats[ $k ] );

		$i     = 0;
		$query = '';
		foreach ( $cats as $cat ) {
			$query .= ",-$cat";

			$i ++;
		}

		ltrim( ',', $query );

		return $query;
	}
}

if ( ! function_exists( 'yit_add_extra_theme_headers' ) ) {
	add_filter( 'extra_theme_headers', 'yit_add_extra_theme_headers' );
	/**
	 * Check the framework core version
	 *
	 * @param array $headers The headers.
	 *
	 * @return array
	 * @since  2.0.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yit_add_extra_theme_headers( $headers ) {
		$headers[] = 'Core Framework Version';

		return $headers;
	}
}

if ( ! function_exists( 'yit_check_plugin_support' ) ) {
	/**
	 * Check the framework core version
	 *
	 * @return bool
	 * @since  2.0.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yit_check_plugin_support() {
		$headers['core']   = wp_get_theme()->get( 'Core Framework Version' );
		$headers['author'] = wp_get_theme()->get( 'Author' );

		if ( ! $headers['core'] && defined( 'YIT_CORE_VERSION' ) ) {
			$headers['core'] = YIT_CORE_VERSION;
		}

		if ( ( ! empty( $headers['core'] ) && version_compare( $headers['core'], '2.0.0', '<=' ) ) || 'Your Inspiration Themes' !== $headers['author'] ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'yit_ie_version' ) ) {
	/**
	 * Retrieve IE version.
	 *
	 * @return int|float
	 * @since  1.0.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>, Andrea Frascaspata<andrea.frascaspata@yithemes.com>
	 */
	function yit_ie_version() {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return - 1;
		}
		preg_match( '/MSIE ([0-9]+\.[0-9])/', $_SERVER['HTTP_USER_AGENT'], $reg );

		// IE 11 fix.
		if ( ! isset( $reg[1] ) ) {
			preg_match( '/rv:([0-9]+\.[0-9])/', $_SERVER['HTTP_USER_AGENT'], $reg );
			if ( ! isset( $reg[1] ) ) {
				return - 1;
			} else {
				return floatval( $reg[1] );
			}
		} else {
			return floatval( $reg[1] );
		}
		// phpcs:enable
	}
}

if ( ! function_exists( 'yit_avoid_duplicate' ) ) {
	/**
	 * Check if something exists. If yes, add a -N to the value where N is a number.
	 *
	 * @param mixed  $value The value to check.
	 * @param array  $array The array to search in.
	 * @param string $check Specifies if the check should be done on values or on keys (default: 'value').
	 *
	 * @return mixed
	 * @since  2.0.0
	 * @author Antonino Scarfì <antonino.scarfi@yithemes.com>
	 */
	function yit_avoid_duplicate( $value, $array, $check = 'value' ) {
		$match = array();

		if ( ! is_array( $array ) ) {
			return $value;
		}

		if ( ( 'value' === $check && ! in_array( $value, $array, true ) ) || ( 'key' === $check && ! isset( $array[ $value ] ) ) ) {
			return $value;
		} else {
			if ( ! preg_match( '/([a-z]+)-([0-9]+)/', $value, $match ) ) {
				$i = 2;
			} else {
				$i     = intval( $match[2] ) + 1;
				$value = $match[1];
			}

			return yit_avoid_duplicate( $value . '-' . $i, $array, $check );
		}
	}
}

if ( ! function_exists( 'yit_title_special_characters' ) ) {
	/**
	 * The chars used in yit_decode_title() and yit_encode_title()
	 * E.G.
	 * string: This is [my title] with | a new line
	 * return: This is <span class="highlight">my title</span> with <br /> a new line
	 *
	 * @param array $chars The chars.
	 *
	 * @return array
	 * @since      1.0
	 * @deprecated 3.5
	 */
	function yit_title_special_characters( $chars ) {
		return array_merge(
			$chars,
			array(
				'/[=\[](.*?)[=\]]/' => '<span class="title-highlight">$1</span>',
				'/\|/'              => '<br />',
			)
		);
	}

	add_filter( 'yit_title_special_characters', 'yit_title_special_characters' );
}

if ( ! function_exists( 'yit_decode_title' ) ) {
	/**
	 * Change some special characters to put easily html into a string
	 * E.G.
	 * string: This is [my title] with | a new line
	 * return: This is <span class="title-highlight">my title</span> with <br /> a new line
	 *
	 * @param string $title The string to convert.
	 *
	 * @return string  The html
	 * @since      1.0
	 * @deprecated 3.5
	 */
	function yit_decode_title( $title ) {
		$replaces = apply_filters( 'yit_title_special_characters', array() );

		return preg_replace( array_keys( $replaces ), array_values( $replaces ), $title );
	}
}

if ( ! function_exists( 'yit_encode_title' ) ) {
	/**
	 * Change some special characters to put easily html into a string
	 * E.G.
	 * string: This is [my title] with | a new line
	 * return: This is <span class="title-highlight">my title</span> with <br /> a new line
	 *
	 * @param string $title The string to convert.
	 *
	 * @return string  The html
	 * @since      1.0
	 * @deprecated 3.5
	 */
	function yit_encode_title( $title ) {
		$replaces = apply_filters( 'yit_title_special_characters', array() );

		return preg_replace( array_values( $replaces ), array_keys( $replaces ), $title );
	}
}

if ( ! function_exists( 'yit_remove_chars_title' ) ) {
	/**
	 * Change some special characters to put easily html into a string
	 * E.G.
	 * string: This is [my title] with | a new line
	 * return: This is <span class="title-highlight">my title</span> with <br /> a new line
	 *
	 * @param string $title The string to convert.
	 *
	 * @return string  The html
	 * @since      1.0
	 * @deprecated 3.5
	 */
	function yit_remove_chars_title( $title ) {
		$replaces = apply_filters( 'yit_title_special_characters', array() );

		return preg_replace( array_keys( $replaces ), '$1', $title );
	}
}

if ( ! function_exists( 'is_shop_installed' ) ) {
	/**
	 * Detect if there is a shop plugin installed
	 *
	 * @return bool
	 * @since  2.0.0
	 * @author Francesco Grasso <francesco.grasso@yithemes.com
	 */
	function is_shop_installed() {
		global $woocommerce;
		if ( isset( $woocommerce ) || defined( 'JIGOSHOP_VERSION' ) ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'yit_load_js_file' ) ) {
	/**
	 * Load .min.js file if WP_Debug is not defined
	 *
	 * @param string $filename The file name.
	 *
	 * @return string The file path
	 * @since  2.0.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yit_load_js_file( $filename ) {

		if ( ! ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || isset( $_GET['yith_script_debug'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filename = str_replace( '.js', '.min.js', $filename );
		}

		return $filename;
	}
}

if ( ! function_exists( 'yit_load_css_file' ) ) {
	/**
	 * Load .min.css file if WP_Debug is not defined
	 *
	 * @param string $filename The file name.
	 *
	 * @return string The file path
	 * @since  2.0.0
	 * @author Alberto Ruggiero
	 */
	function yit_load_css_file( $filename ) {

		if ( ! ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || isset( $_GET['yith_script_debug'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filename = str_replace( '.css', '.min.css', $filename );
		}

		return $filename;
	}
}

if ( ! function_exists( 'yit_wpml_register_string' ) ) {
	/**
	 * Register a string in wpml translation.
	 *
	 * @param string $context The context name.
	 * @param string $name    The name.
	 * @param string $value   The value to translate.
	 *
	 * @since  2.0.0
	 * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
	 */
	function yit_wpml_register_string( $context, $name, $value ) {
		do_action( 'wpml_register_single_string', $context, $name, $value );
	}
}

if ( ! function_exists( 'yit_wpml_string_translate' ) ) {
	/**
	 * Get a string translation
	 *
	 * @param string $context       The context name.
	 * @param string $name          The name.
	 * @param string $default_value Default value.
	 *
	 * @return string the string translated
	 * @since  2.0.0
	 * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
	 */
	function yit_wpml_string_translate( $context, $name, $default_value ) {
		return apply_filters( 'wpml_translate_single_string', $default_value, $context, $name );
	}
}

if ( ! function_exists( 'yit_wpml_object_id' ) ) {
	/**
	 * Get id of post translation in current language
	 *
	 * @param int         $element_id                 The element ID.
	 * @param string      $element_type               The element type.
	 * @param bool        $return_original_if_missing Return original if missing or not.
	 * @param null|string $language_code              The language code.
	 *
	 * @return int the translation id
	 * @since  2.0.0
	 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
	 */
	function yit_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null ) {
		if ( function_exists( 'wpml_object_id_filter' ) ) {
			return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $language_code );
		} elseif ( function_exists( 'icl_object_id' ) ) {
			return icl_object_id( $element_id, $element_type, $return_original_if_missing, $language_code );
		} else {
			return $element_id;
		}
	}
}

if ( ! function_exists( 'yit_get_language_from_locale' ) ) {
	/**
	 * Returns language name from locale code
	 *
	 * @param string $locale      Locale to search for.
	 * @param bool   $show_native Whether to return native language instead of english one.
	 *
	 * @return string Language name for passed locale; if can't find any, local itself is returned.
	 * @since  3.7.1
	 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
	 */
	function yit_get_language_from_locale( $locale, $show_native = false ) {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$translations = wp_get_available_translations();

		if ( in_array( $locale, array( 'en', 'en_US' ), true ) ) {
			/**
			 * English (United States) is not included in translations array
			 * We return fixed, non-localized string, as WordPress does
			 *
			 * @see {wp_dropdown_languages}
			 */
			return 'English';
		}

		if ( empty( $translations ) ) {
			return $locale;
		}

		// perfect match.
		$translation = isset( $translations[ $locale ] ) ? $translations[ $locale ] : false;

		// check for no-dialect.
		if ( 2 === strlen( $locale ) ) {

			// first check for common occurrences.
			if ( 'it' === $locale && isset( $translations['it_IT'] ) ) {
				$translation = $translations['it_IT'];
			} elseif ( 'es' === $locale && isset( $translations['es_ES'] ) ) {
				$translation = $translations['es_ES'];
			} elseif ( 'de' === $locale && isset( $translations['de_DE'] ) ) {
				$translation = $translations['de_DE'];
			} else {
				foreach ( $translations as $translation_locale => $translation_details ) {
					$no_dialect_locale = isset( $translation_details['iso'][1] ) ? $translation_details['iso'][1] : substr( $translation_locale, 0, 2 );

					if ( $locale !== $no_dialect_locale ) {
						continue;
					}

					$translation = $translation_details;

					// remove dialect from names.
					$translation['english_name'] = preg_replace( '/(.+) \(.*\)/', '$1', $translation['english_name'] );

					// we found what we were looking for; break.
					break;
				}
			}
		}

		if ( ! $translation ) {
			return $locale;
		}

		$language_name = $show_native ? $translation['native_name'] : $translation['english_name'];

		return $language_name;
	}
}

if ( ! function_exists( 'yith_get_formatted_price' ) ) {
	/**
	 * Format the price with a currency symbol.
	 *
	 * @param float $price The price.
	 * @param array $args  Arguments.
	 *
	 * @return string
	 */
	function yith_get_formatted_price( $price, $args = array() ) {
		$defaults = array(
			'ex_tax_label'       => false,
			'currency'           => '',
			'decimal_separator'  => wc_get_price_decimal_separator(),
			'thousand_separator' => wc_get_price_thousand_separator(),
			'decimals'           => wc_get_price_decimals(),
			'price_format'       => get_woocommerce_price_format(),
		);
		$args     = wp_parse_args( $args, $defaults );
		$args     = apply_filters( 'wc_price_args', $args );

		list ( $decimals, $decimal_separator, $thousand_separator, $price_format, $currency ) = yith_plugin_fw_extract( $args, 'decimals', 'decimal_separator', 'thousand_separator', 'price_format', 'currency' );

		$negative = $price < 0;
		$price    = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * - 1 : $price ) );
		$price    = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

		if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $decimals > 0 ) {
			$price = wc_trim_zeros( $price );
		}

		$formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, get_woocommerce_currency_symbol( $currency ), $price );
		$return          = $formatted_price;

		return apply_filters( 'wc_price', $return, $price, $args );
	}
}

if ( ! function_exists( 'yith_get_terms' ) ) {
	/**
	 * Get terms.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array|int|WP_Error
	 * @deprecated 3.5 | use get_terms instead
	 */
	function yith_get_terms( $args ) {
		global $wp_version;
		if ( version_compare( $wp_version, '4.5', '>=' ) ) {
			$terms = get_terms( $args );
		} else {
			$terms = get_terms( $args['taxonomy'], $args );
		}

		return $terms;
	}
}

if ( ! function_exists( 'yith_field_deps_data' ) ) {
	/**
	 * Retrieve the field deps HTML data.
	 *
	 * @param array $field The field.
	 *
	 * @return string
	 */
	function yith_field_deps_data( $field ) {
		$deps_data = '';
		if ( isset( $field['deps'] ) && ( isset( $field['deps']['ids'] ) || isset( $field['deps']['id'] ) ) && ( isset( $field['deps']['values'] ) || isset( $field['deps']['value'] ) ) ) {
			$deps       = $field['deps'];
			$id         = isset( $deps['target-id'] ) ? $deps['target-id'] : $field['id'];
			$dep_id     = isset( $deps['id'] ) ? $deps['id'] : $deps['ids'];
			$dep_values = isset( $deps['value'] ) ? $deps['value'] : $deps['values'];
			$dep_type   = isset( $deps['type'] ) ? $deps['type'] : 'fadeIn';

			$deps_data = 'data-dep-target="' . esc_attr( $id ) . '" data-dep-id="' . esc_attr( $dep_id ) . '" data-dep-value="' . esc_attr( $dep_values ) . '" data-dep-type="' . esc_attr( $dep_type ) . '"';
		}

		return $deps_data;
	}
}

if ( ! function_exists( 'yith_panel_field_deps_data' ) ) {
	/**
	 * Retrieve the panel field deps HTML data.
	 *
	 * @param array                                         $field The field.
	 * @param YIT_Plugin_Panel|YIT_Plugin_Panel_WooCommerce $panel The panel object.
	 *
	 * @return string
	 */
	function yith_panel_field_deps_data( $field, $panel ) {
		$deps_data = '';
		if ( isset( $field['deps'] ) && ( isset( $field['deps']['ids'] ) || isset( $field['deps']['id'] ) ) && isset( $field['deps']['values'] ) ) {
			$dep_id               = isset( $field['deps']['id'] ) ? $field['deps']['id'] : $field['deps']['ids'];
			$field['deps']['ids'] = $panel->get_id_field( $dep_id );
			$field['deps']['id']  = $panel->get_id_field( $dep_id );
			$field['id']          = $panel->get_id_field( $field['id'] );

			$deps_data = yith_field_deps_data( $field );
		}

		return $deps_data;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_field' ) ) {
	/**
	 * Retrieve a field.
	 *
	 * @param array $field          The field.
	 * @param false $echo           Set to true to print the field directly; false otherwise.
	 * @param bool  $show_container Set to true to show the container; false otherwise.
	 *
	 * @return false|string
	 */
	function yith_plugin_fw_get_field( $field, $echo = false, $show_container = true ) {
		if ( empty( $field['type'] ) ) {
			return '';
		}

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = '';
		}

		if ( ! isset( $field['name'] ) ) {
			$field['name'] = '';
		}

		if ( ! isset( $field['custom_attributes'] ) ) {
			$field['custom_attributes'] = array();
		}

		if ( is_array( $field['custom_attributes'] ) ) {
			/**
			 * Convert custom_attributes to string to prevent issues in plugins using them as string in their templates.
			 * todo: remove after checking plugins using custom_attributes as "string" in custom fields templates and as "array" in custom fields options.
			 */
			$field['custom_attributes'] = yith_plugin_fw_html_attributes_to_string( $field['custom_attributes'] );
		}

		if ( ! isset( $field['default'] ) && isset( $field['std'] ) ) {
			$field['default'] = $field['std'];
		}

		$field_template = yith_plugin_fw_get_field_template_path( $field );

		if ( ! isset( $field['id'] ) ) {
			static $field_number = 1;

			$field['id'] = "yith-plugin-fw-field__{$field_number}";
			$field_number ++;
		}

		if ( $field_template ) {
			if ( ! $echo ) {
				ob_start();
			}

			if ( $show_container ) {
				echo '<div class="yith-plugin-fw-field-wrapper yith-plugin-fw-' . esc_attr( $field['type'] ) . '-field-wrapper">';
			}

			do_action( 'yith_plugin_fw_get_field_before', $field );
			do_action( 'yith_plugin_fw_get_field_' . $field['type'] . '_before', $field );

			include $field_template;

			do_action( 'yith_plugin_fw_get_field_after', $field );
			do_action( 'yith_plugin_fw_get_field_' . $field['type'] . '_after', $field );

			if ( $show_container ) {
				echo '</div>';
			}

			if ( ! $echo ) {
				return ob_get_clean();
			}
		}

		return '';
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_field_template_path' ) ) {
	/**
	 * Retrieve the field template path.
	 *
	 * @param array $field The field.
	 *
	 * @return false|string
	 */
	function yith_plugin_fw_get_field_template_path( $field ) {
		if ( empty( $field['type'] ) ) {
			return false;
		}

		$field_template = YIT_CORE_PLUGIN_TEMPLATE_PATH . '/fields/' . sanitize_title( $field['type'] ) . '.php';

		$field_template = apply_filters( 'yith_plugin_fw_get_field_template_path', $field_template, $field );

		return file_exists( $field_template ) ? $field_template : false;
	}
}

if ( ! function_exists( 'yith_plugin_fw_html_data_to_string' ) ) {
	/**
	 * Transform data array to HTML data.
	 *
	 * @param array $data The array of data.
	 * @param false $echo Set to true to print it directly; false otherwise.
	 *
	 * @return string
	 */
	function yith_plugin_fw_html_data_to_string( $data = array(), $echo = false ) {
		$html_data = '';

		if ( ! ! $data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data_attribute = "data-{$key}";
					$data_value     = ! is_array( $value ) ? $value : implode( ',', $value );

					$html_data .= ' ' . esc_attr( $data_attribute ) . '="' . esc_attr( $data_value ) . '"';
				}
				$html_data .= ' ';
			}
		}

		if ( $echo ) {
			echo $html_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html_data;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_icon' ) ) {
	/**
	 * Retrieve an icon.
	 *
	 * @param string $icon The icon.
	 * @param array  $args Array of arguments (such as html_tag, class, style, filter_suffix).
	 *
	 * @return string
	 */
	function yith_plugin_fw_get_icon( $icon = '', $args = array() ) {
		return YIT_Icons()->get_icon( $icon, $args );
	}
}

if ( ! function_exists( 'yith_plugin_fw_is_true' ) ) {
	/**
	 * Is something true?
	 *
	 * @param string|bool|int $value The value to check for.
	 *
	 * @return bool
	 */
	function yith_plugin_fw_is_true( $value ) {
		return true === $value || 1 === $value || '1' === $value || 'yes' === $value || 'true' === $value;
	}
}

if ( ! function_exists( 'yith_plugin_fw_enqueue_enhanced_select' ) ) {
	/**
	 * Enqueue the enhanced select style and script.
	 */
	function yith_plugin_fw_enqueue_enhanced_select() {
		wp_enqueue_script( 'yith-enhanced-select' );
		$select2_style_to_enqueue = function_exists( 'WC' ) ? 'woocommerce_admin_styles' : 'yith-select2-no-wc';
		wp_enqueue_style( $select2_style_to_enqueue );
	}
}

if ( ! function_exists( 'yit_add_select2_fields' ) ) {
	/**
	 * Add select 2.
	 *
	 * @param array $args The arguments.
	 */
	function yit_add_select2_fields( $args = array() ) {
		$default = array(
			'type'              => 'hidden',
			'class'             => '',
			'id'                => '',
			'name'              => '',
			'data-placeholder'  => '',
			'data-allow_clear'  => false,
			'data-selected'     => '',
			'data-multiple'     => false,
			'data-action'       => '',
			'value'             => '',
			'style'             => '',
			'custom-attributes' => array(),
		);

		$args = wp_parse_args( $args, $default );

		$custom_attributes = array();
		foreach ( $args['custom-attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
		$custom_attributes = implode( ' ', $custom_attributes );

		if ( ! function_exists( 'WC' ) || version_compare( WC()->version, '2.7.0', '>=' ) ) {
			if ( true === $args['data-multiple'] && substr( $args['name'], - 2 ) !== '[]' ) {
				$args['name'] = $args['name'] . '[]';
			}
			$select2_template_name = 'select2.php';

		} else {
			if ( false === $args['data-multiple'] && is_array( $args['data-selected'] ) ) {
				$args['data-selected'] = current( $args['data-selected'] );
			}
			$select2_template_name = 'select2-wc-2.6.php';
		}

		$template = YIT_CORE_PLUGIN_TEMPLATE_PATH . '/fields/resources/' . $select2_template_name;
		if ( file_exists( $template ) ) {
			include $template;
		}
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_version' ) ) {
	/**
	 * Retrieve the Plugin Framework version.
	 *
	 * @return string
	 */
	function yith_plugin_fw_get_version() {
		$plugin_fw_data = get_file_data( trailingslashit( YIT_CORE_PLUGIN_PATH ) . 'init.php', array( 'Version' => 'Version' ) );

		return $plugin_fw_data['Version'];
	}
}

if ( ! function_exists( 'yith_get_premium_support_url' ) ) {
	/**
	 * Return the url for My Account > Support dashboard
	 *
	 * @return string The complete string, if the main string is not empty or null
	 * @since      2.0.0
	 * @deprecated 3.5
	 */
	function yith_get_premium_support_url() {
		return 'https://yithemes.com/my-account/support/dashboard/';
	}
}

if ( ! function_exists( 'yith_plugin_fw_is_panel' ) ) {
	/**
	 * Is this a Plugin Framework panel?
	 *
	 * @return bool
	 */
	function yith_plugin_fw_is_panel() {
		$panel_screen_id = 'yith-plugins_page';
		$screen          = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		return $screen instanceof WP_Screen && strpos( $screen->id, $panel_screen_id ) !== false;
	}
}

if ( ! function_exists( 'yith_plugin_fw_force_regenerate_plugin_update_transient' ) ) {
	/**
	 * Delete the update plugins transient
	 *
	 * @return void
	 * @since  1.0
	 * @see    update_plugins transient and pre_set_site_transient_update_plugins hooks
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yith_plugin_fw_force_regenerate_plugin_update_transient() {
		delete_site_transient( 'update_plugins' );
	}
}

if ( ! function_exists( 'yith_plugin_fw_is_gutenberg_enabled' ) ) {
	/**
	 * Is Gutenberg enabled?
	 *
	 * @return bool
	 */
	function yith_plugin_fw_is_gutenberg_enabled() {
		return function_exists( 'YITH_Gutenberg' );
	}
}

if ( ! function_exists( 'yith_plugin_fw_gutenberg_add_blocks' ) ) {
	/**
	 * Add new blocks to Gutenberg
	 *
	 * @param string|array $blocks Blocks to add.
	 *
	 * @return bool true if add a new blocks, false otherwise
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yith_plugin_fw_gutenberg_add_blocks( $blocks ) {
		$added = false;
		if ( yith_plugin_fw_is_gutenberg_enabled() ) {
			// Add blocks.
			$added = YITH_Gutenberg()->add_blocks( $blocks );

			// Add blocks arguments.
			if ( $added ) {
				YITH_Gutenberg()->set_block_args( $blocks );
			}
		}

		return $added;
	}
}

if ( ! function_exists( 'yith_plugin_fw_gutenberg_get_registered_blocks' ) ) {
	/**
	 * Return an array with the registered blocks
	 *
	 * @return array
	 */
	function yith_plugin_fw_gutenberg_get_registered_blocks() {
		return yith_plugin_fw_is_gutenberg_enabled() ? YITH_Gutenberg()->get_registered_blocks() : array();
	}
}

if ( ! function_exists( 'yith_plugin_fw_gutenberg_get_to_register_blocks' ) ) {
	/**
	 * Return an array with the blocks to register
	 *
	 * @return array
	 */
	function yith_plugin_fw_gutenberg_get_to_register_blocks() {
		return yith_plugin_fw_is_gutenberg_enabled() ? YITH_Gutenberg()->get_to_register_blocks() : array();
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_default_logo' ) ) {
	/**
	 * Get the default SVG logo
	 *
	 * @return string default logo image url
	 */
	function yith_plugin_fw_get_default_logo() {
		return YIT_CORE_PLUGIN_URL . '/assets/images/yith-icon.svg';
	}
}

if ( ! function_exists( 'yith_get_wrapper_class' ) ) {
	/**
	 * Return the wrapper class for the UI style.
	 *
	 * @return string
	 * @since 3.7.0
	 */
	function yith_get_wrapper_class() {
		return 'yith-plugin-ui';
	}
}

if ( ! function_exists( 'yith_set_wrapper_class' ) ) {
	/**
	 * Return the wrapper class for the UI style, by setting any additional class passed through the $class parameter.
	 *
	 * @param array|string $class List of additional classes to add to the UI style class.
	 *
	 * @return string
	 * @author Emanuela Castorina
	 */
	function yith_set_wrapper_class( $class = '' ) {
		$new_class = yith_get_wrapper_class();
		$class     = ( ! empty( $class ) && is_array( $class ) ) ? implode( ' ', $class ) : $class;

		return $new_class . ' ' . $class;
	}
}

if ( ! function_exists( 'yith_get_date_formats' ) ) {
	/**
	 * Get all available date format.
	 *
	 * @param bool $js JS date format or not.
	 *
	 * @return array
	 * @author     Salvatore Strano
	 * @since      3.1
	 * @deprecated 3.5 | use yith_get_date_formats() instead
	 */
	function yith_get_date_format( $js = true ) {
		return yith_get_date_formats( $js );
	}
}

if ( ! function_exists( 'yith_get_date_formats' ) ) {
	/**
	 * Get all available date formats.
	 *
	 * @param bool $js JS date format or not.
	 *
	 * @return array
	 * @since  3.5
	 */
	function yith_get_date_formats( $js = true ) {
		$date_formats = array(
			'F j, Y' => 'F j, Y',
			'Y-m-d'  => 'Y-m-d',
			'm/d/Y'  => 'm/d/Y',
			'd/m/Y'  => 'd/m/Y',
		);

		if ( $js ) {
			$date_formats = array(
				'MM d, yy' => 'F j, Y',
				'yy-mm-dd' => 'Y-m-d',
				'mm/dd/yy' => 'm/d/Y',
				'dd/mm/yy' => 'd/m/Y',
			);
		}

		return apply_filters( 'yith_plugin_fw_date_formats', $date_formats, $js );
	}
}

if ( ! function_exists( 'yith_get_time_formats' ) ) {
	/**
	 * Get all available time format.
	 *
	 * @return array
	 * @author Emanuela Castorina
	 * @since  3.5
	 */
	function yith_get_time_formats() {

		$time_formats = array(
			'h:i:s' => 'h:i:s',
			'g:i a' => 'g:i a',
			'g:i A' => 'g:i A',
			'H:i'   => 'H:i',
		);

		return apply_filters( 'yith_plugin_fw_time_formats', $time_formats );
	}
}


if ( ! function_exists( 'yith_format_toggle_title' ) ) {
	/**
	 * Replace the placeholders with the values of the element id for toggle element field.
	 *
	 * @param string $title  The title.
	 * @param array  $values The values.
	 *
	 * @return array
	 * @author Salvatore Strano
	 * @since  3.1
	 */
	function yith_format_toggle_title( $title, $values ) {
		preg_match_all( '/(?<=\%%).+?(?=\%%)/', $title, $matches );
		if ( isset( $matches[0] ) ) {
			foreach ( $matches[0] as $element_id ) {
				if ( isset( $values[ $element_id ] ) ) {
					$title = str_replace( '%%' . $element_id . '%%', $values[ $element_id ], $title );
				}
			}
		}

		return $title;
	}
}

if ( ! function_exists( 'yith_plugin_fw_load_update_and_licence_files' ) ) {
	/**
	 * Load premium file for license and update system
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yith_plugin_fw_load_update_and_licence_files() {
		global $plugin_upgrade_fw_data;

		/**
		 * If the init.php was load by old plugin-fw version
		 * load the upgrade and license key from local folder
		 */
		if ( empty( $plugin_upgrade_fw_data ) ) {
			$plugin_upgrade_path = plugin_dir_path( __DIR__ ) . 'plugin-upgrade';
			if ( file_exists( $plugin_upgrade_path ) ) {
				$required_files = array(
					$plugin_upgrade_path . '/lib/yit-licence.php',
					$plugin_upgrade_path . '/lib/yit-plugin-licence.php',
					$plugin_upgrade_path . '/lib/yit-theme-licence.php',
					$plugin_upgrade_path . '/lib/yit-plugin-upgrade.php',
				);

				$plugin_upgrade_fw_data = array( '1.0' => $required_files );
			}
		}

		if ( ! empty( $plugin_upgrade_fw_data ) && is_array( $plugin_upgrade_fw_data ) ) {
			foreach ( $plugin_upgrade_fw_data as $fw_version => $core_files ) {
				foreach ( $core_files as $core_file ) {
					if ( file_exists( $core_file ) ) {
						include_once $core_file;
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'yith_plugin_fw_remove_duplicate_classes' ) ) {
	/**
	 * Remove the duplicate classes from a string.
	 *
	 * @param string $classes The classes.
	 *
	 * @return string
	 * @since  3.2.2
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	function yith_plugin_fw_remove_duplicate_classes( $classes ) {
		$class_array  = explode( ' ', $classes );
		$class_unique = array_unique( array_filter( $class_array ) );
		if ( $class_unique ) {
			$classes = implode( ' ', $class_unique );
		}

		return $classes;
	}
}

if ( ! function_exists( 'yith_plugin_fw_add_requirements' ) ) {
	/**
	 * Add plugin requirements
	 *
	 * @param string $plugin_name  The name of the plugin.
	 * @param array  $requirements Array of plugin requirements.
	 */
	function yith_plugin_fw_add_requirements( $plugin_name, $requirements ) {
		if ( ! empty( $requirements ) ) {
			YITH_System_Status()->add_requirements( $plugin_name, $requirements );
		}
	}
}

if ( ! function_exists( 'yith_plugin_fw_parse_dimensions' ) ) {
	/**
	 * Parse dimensions stored through a "dimensions" field to a key-value array
	 * where the key will be equal to the dimension key
	 * and the value will be equal to the value of the dimension suffixed with the unit
	 *
	 * @param array $values The values.
	 *
	 * @return array
	 */
	function yith_plugin_fw_parse_dimensions( $values ) {
		$dimensions = array();
		if ( is_array( $values ) && isset( $values['dimensions'], $values['unit'] ) && is_array( $values['dimensions'] ) ) {
			$raw_unit = $values['unit'];
			$unit     = 'percentage' === $raw_unit ? '%' : $raw_unit;
			foreach ( $values['dimensions'] as $key => $value ) {
				$dimensions[ $key ] = $value . $unit;
			}
		}

		return $dimensions;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_dimensions_by_option' ) ) {
	/**
	 * Retrieve a parsed array of dimensions by an option
	 *
	 * @param string     $option  The option.
	 * @param bool|array $default Default value.
	 *
	 * @return array|bool
	 */
	function yith_plugin_fw_get_dimensions_by_option( $option, $default = false ) {
		$dimensions = get_option( $option, false );

		return ! ! $dimensions ? yith_plugin_fw_parse_dimensions( $dimensions ) : $default;
	}
}

if ( ! function_exists( 'yith_plugin_fw_extract' ) ) {
	/**
	 * Extract array variables
	 *
	 * Usage example:
	 * ```
	 * list ( $type, $class, $value ) = yith_plugin_fw_extract( $field, 'type', 'class', 'value' );
	 * ```
	 *
	 * @param array  $array   The array.
	 * @param string ...$keys The keys.
	 *
	 * @return array
	 * @since 3.5
	 */
	function yith_plugin_fw_extract( $array, ...$keys ) {
		return array_map(
			function ( $key ) use ( $array ) {
				return isset( $array[ $key ] ) ? $array[ $key ] : null;
			},
			$keys
		);
	}
}


if ( ! function_exists( 'yith_plugin_fw_register_elementor_widget' ) ) {
	/**
	 * Register Elementor widget
	 *
	 * @param string $widget_name    The widget name.
	 * @param array  $widget_options The widget options.
	 *
	 * @since 3.6.0
	 */
	function yith_plugin_fw_register_elementor_widget( $widget_name, $widget_options ) {
		YITH_Elementor::instance()->register_widget( $widget_name, $widget_options );
	}
}

if ( ! function_exists( 'yith_plugin_fw_register_elementor_widgets' ) ) {
	/**
	 * Register Elementor widgets
	 *
	 * @param array $widgets            The widgets.
	 * @param bool  $map_from_gutenberg Set to true if you need to map options from Gutenberg blocks array.
	 *
	 * @since 3.6.0
	 */
	function yith_plugin_fw_register_elementor_widgets( $widgets, $map_from_gutenberg = false ) {
		foreach ( $widgets as $widget_name => $widget_options ) {
			if ( $map_from_gutenberg ) {
				$widget_options = array_merge( array( 'map_from_gutenberg' => true ), $widget_options );
			}
			yith_plugin_fw_register_elementor_widget( $widget_name, $widget_options );
		}
	}
}

if ( ! function_exists( 'yith_plugin_fw_copy_to_clipboard' ) ) {
	/**
	 * Print a field with a button to copy its content to clipboard
	 *
	 * @param string $value The text to be shown.
	 * @param array  $field The field attributes.
	 *
	 * @since 3.6.2
	 */
	function yith_plugin_fw_copy_to_clipboard( $value, $field = array() ) {
		$defaults      = array(
			'id'    => '',
			'value' => $value,
		);
		$field         = wp_parse_args( $field, $defaults );
		$field['type'] = 'copy-to-clipboard';

		// Enqueue style and script if not enqueued.
		wp_enqueue_style( 'yith-plugin-fw-fields' );
		wp_enqueue_script( 'yith-plugin-fw-fields' );

		yith_plugin_fw_get_field( $field, true, false );
	}
}

if ( ! function_exists( 'yith_plugin_fw_add_utm_data' ) ) {
	/**
	 * Add UTM data in backend url
	 *
	 * @param string $url      The url that want to track.
	 * @param string $slug     Plugin slug.
	 * @param string $campaign Campaign to track. Default: plugin-version-author-uri.
	 * @param string $source   Where the link came from. Default: wp-dashboard.
	 *
	 * @since 3.6.10
	 */
	function yith_plugin_fw_add_utm_data( $url, $slug, $campaign = 'plugin-version-author-uri', $source = 'wp-dashboard' ) {
		$url = trailingslashit( $url );
		if ( ! empty( $slug ) ) {
			$utm_track_data = array(
				'utm_source'   => $source,
				'utm_medium'   => $slug,
				'utm_campaign' => $campaign,
			);

			$url = add_query_arg( $utm_track_data, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'yith_plugin_fw_include_fw_template' ) ) {
	/**
	 * Include a FW template
	 *
	 * @param string $template The template.
	 * @param array  $args     Arguments.
	 *
	 * @since 3.7.0
	 */
	function yith_plugin_fw_include_fw_template( $template, $args = array() ) {
		$_template_path = trailingslashit( YIT_CORE_PLUGIN_TEMPLATE_PATH ) . $template;

		if ( file_exists( $_template_path ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			include $_template_path;
		}
	}
}

if ( ! function_exists( 'yith_plugin_fw_html_attributes_to_string' ) ) {
	/**
	 * Transform attributes array to HTML attributes string.
	 * If using a string, the attributes will be escaped.
	 * Prefer using arrays.
	 *
	 * @param array|string $attributes The attributes.
	 * @param bool         $echo       Set to true to print it directly; false otherwise.
	 *
	 * @return string
	 * @since 3.7.0
	 * @since 3.8.0 Escaping attributes when using strings; allow value-less attributes by setting value to null.
	 */
	function yith_plugin_fw_html_attributes_to_string( $attributes = array(), $echo = false ) {
		$html_attributes = '';

		if ( ! ! $attributes ) {
			if ( is_string( $attributes ) ) {
				$parsed_attrs = wp_kses_hair( $attributes, wp_allowed_protocols() );
				$attributes   = array();
				foreach ( $parsed_attrs as $attr ) {
					$attributes[ $attr['name'] ] = 'n' === $attr['vless'] ? $attr['value'] : null;
				}
			}

			if ( is_array( $attributes ) ) {
				$html_attributes = array();
				foreach ( $attributes as $key => $value ) {
					if ( ! is_null( $value ) ) {
						$html_attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
					} else {
						$html_attributes[] = esc_attr( $key );
					}
				}
				$html_attributes = implode( ' ', $html_attributes );
			}
		}

		if ( $echo ) {
			// Already escaped above.
			echo $html_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html_attributes;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_component' ) ) {
	/**
	 * Retrieve a component.
	 *
	 * @param array $component The component.
	 * @param bool  $echo      Set to true to print the component directly; false otherwise.
	 *
	 * @return false|string
	 * @since 3.7.0
	 */
	function yith_plugin_fw_get_component( $component, $echo = true ) {
		if ( ! empty( $component['type'] ) ) {
			$type     = sanitize_title( $component['type'] );
			$defaults = array(
				'id'         => '',
				'class'      => '',
				'attributes' => array(),
				'data'       => array(),
			);

			$component = wp_parse_args( $component, $defaults );

			$component_template = '/components/' . $type . '.php';

			if ( ! $echo ) {
				ob_start();
			}

			yith_plugin_fw_include_fw_template( $component_template, compact( 'component' ) );

			if ( ! $echo ) {
				return ob_get_clean();
			}
		}

		return '';
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_default_post_actions' ) ) {
	/**
	 * Retrieve the default post actions to be used in WP List tables to show action buttons.
	 *
	 * @param WP_Post|int $post                   The post.
	 * @param array       $args                   {
	 *                                            Optional. Arguments to retrieve actions.
	 *
	 * @type array        $more-menu              Array of more-menu items.
	 * @type array|bool   $more-menu-in-trash     false: the menu will be not shown in trash | true: the menu will be shown in trash | array: set specific menu for trash.
	 * @type string|false $duplicate-url          The Duplicate URL. Default: false (the duplicate action will be not shown).
	 * @type string|false $confirm-trash-message  The 'confirm trash' message. Set to false to not ask for trash confirmation.
	 * @type string|false $confirm-delete-message The 'confirm delete' message. Set to false to not ask for delete confirmation.
	 * }
	 *
	 * @return array
	 * @since 3.7.0
	 */
	function yith_plugin_fw_get_default_post_actions( $post, $args = array() ) {
		$post    = get_post( $post );
		$actions = array();
		if ( $post ) {
			$title            = _draft_or_post_title( $post );
			$post_type_object = get_post_type_object( $post->post_type );
			$can_edit_post    = current_user_can( 'edit_post', $post->ID );

			$defaults = array(
				'more-menu'              => array(),
				'more-menu-in-trash'     => false,
				'duplicate-url'          => false,
				// translators: %s is the title of the post object.
				'confirm-trash-message'  => sprintf( __( 'Are you sure you want to move "%s" to trash?', 'yith-plugin-fw' ), '<strong>' . $title . '</strong>' ),
				// translators: %s is the title of the post object.
				'confirm-delete-message' => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-plugin-fw' ), '<strong>' . $title . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-plugin-fw' ),
			);

			$args = wp_parse_args( $args, $defaults );

			if ( is_post_type_viewable( $post_type_object ) ) {
				if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ), true ) ) {
					if ( $can_edit_post ) {
						$preview_link = get_preview_post_link( $post );

						$actions['view'] = array(
							'type'   => 'action-button',
							'title'  => _x( 'Preview', 'Post action', 'yith-plugin-fw' ),
							'action' => 'view',
							'icon'   => 'eye',
							'url'    => $preview_link,
						);
					}
				} elseif ( 'trash' !== $post->post_status ) {
					$actions['view'] = array(
						'type'   => 'action-button',
						'title'  => _x( 'View', 'Post action', 'yith-plugin-fw' ),
						'action' => 'view',
						'icon'   => 'eye',
						'url'    => get_permalink( $post->ID ),
					);
				}
			}

			if ( $can_edit_post && 'trash' !== $post->post_status ) {
				$actions['edit'] = array(
					'type'   => 'action-button',
					'title'  => _x( 'Edit', 'Post action', 'yith-plugin-fw' ),
					'action' => 'edit',
					'url'    => get_edit_post_link( $post->ID ),
				);

				if ( $args['duplicate-url'] ) {
					$actions['duplicate'] = array(
						'type'   => 'action-button',
						'title'  => _x( 'Duplicate', 'Post action', 'yith-plugin-fw' ),
						'action' => 'duplicate',
						'icon'   => 'clone',
						'url'    => $args['duplicate-url'],
					);
				}
			}

			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( 'trash' === $post->post_status ) {
					$actions['untrash'] = array(
						'type'   => 'action-button',
						'title'  => _x( 'Restore', 'Post action', 'yith-plugin-fw' ),
						'action' => 'untrash',
						'icon'   => 'reply',
						'url'    => wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
					);
				} elseif ( EMPTY_TRASH_DAYS ) {
					$actions['trash'] = array(
						'type'   => 'action-button',
						'title'  => _x( 'Trash', 'Post action', 'yith-plugin-fw' ),
						'action' => 'trash',
						'url'    => get_delete_post_link( $post->ID ),
					);
					if ( $args['confirm-trash-message'] ) {
						$actions['trash']['confirm_data'] = array(
							'title'               => __( 'Confirm trash', 'yith-plugin-fw' ),
							'message'             => $args['confirm-trash-message'],
							'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
							'confirm-button'      => _x( 'Yes, move to trash', 'Trash confirmation action', 'yith-plugin-fw' ),
							'confirm-button-type' => 'delete',
						);
					}
				}
				if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
					$actions['delete'] = array(
						'type'   => 'action-button',
						'title'  => _x( 'Delete Permanently', 'Post action', 'yith-plugin-fw' ),
						'action' => 'delete',
						'icon'   => 'trash',
						'url'    => get_delete_post_link( $post->ID, '', true ),
					);
					if ( $args['confirm-delete-message'] ) {
						$actions['delete']['confirm_data'] = array(
							'title'               => __( 'Confirm delete', 'yith-plugin-fw' ),
							'message'             => $args['confirm-delete-message'],
							'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
							'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-plugin-fw' ),
							'confirm-button-type' => 'delete',
						);
					}
				}
			}

			if ( $args['more-menu'] ) {
				if ( 'trash' !== $post->post_status || true === $args['more-menu-in-trash'] ) {
					$actions['more'] = array(
						'type'   => 'action-button',
						'title'  => __( 'Further actions', 'yith-plugin-fw' ),
						'action' => 'more',
						'url'    => '#',
						'menu'   => $args['more-menu'],
					);
				} elseif ( ! ! $args['more-menu-in-trash'] ) {
					$actions['more'] = array(
						'type'   => 'action-button',
						'title'  => __( 'Further actions', 'yith-plugin-fw' ),
						'action' => 'more',
						'url'    => '#',
						'menu'   => $args['more-menu-in-trash'],
					);
				}
			}
		}

		return $actions;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_default_term_actions' ) ) {
	/**
	 * Retrieve the default term actions to be used in WP List tables to show action buttons.
	 *
	 * @param WP_Term|int $term                   The term.
	 * @param array       $args                   {
	 *                                            Optional. Arguments to retrieve actions.
	 *
	 * @type string       $taxonomy               The taxonomy. If not set, the taxonomy will be retrieved by $_REQUEST.
	 * @type string       $object-type            The object type the term is assigned to (ex: the post-type).
	 * @type array        $more-menu              Array of more-menu items.
	 * @type string|false $duplicate-url          The Duplicate URL. Default: false (the duplicate action will be not shown).
	 * @type string|false $confirm-delete-message The 'confirm delete' message. Set to false to not ask for delete confirmation.
	 * }
	 *
	 * @return array
	 * @since 3.7.0
	 */
	function yith_plugin_fw_get_default_term_actions( $term, $args = array() ) {
		if ( isset( $args['taxonomy'] ) ) {
			$taxonomy = $args['taxonomy'];
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_title( wp_unslash( $_REQUEST['taxonomy'] ) ) : false;
		}

		if ( is_numeric( $term ) ) {
			$term_id = absint( $term );
			$term    = get_term_by( 'id', $term_id, $taxonomy );
		}

		$actions = array();
		$tax     = get_taxonomy( $taxonomy );
		if ( ! empty( $term->term_id ) && $tax ) {
			$title    = $term->name;
			$uri      = wp_doing_ajax() ? wp_get_referer() : esc_url_raw( wp_unslash( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' ) );
			$defaults = array(
				'object-type'            => '',
				'more-menu'              => array(),
				'duplicate-url'          => false,
				// translators: %s is the title of the post object.
				'confirm-delete-message' => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-plugin-fw' ), '<strong>' . $title . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-plugin-fw' ),
			);

			$args = wp_parse_args( $args, $defaults );

			$edit_link = add_query_arg(
				'wp_http_referer',
				$uri,
				get_edit_term_link( $term->term_id, $taxonomy, $args['object-type'] )
			);

			if ( is_taxonomy_viewable( $tax ) ) {
				$actions['view'] = array(
					'type'   => 'action-button',
					'title'  => _x( 'View', 'Term action', 'yith-plugin-fw' ),
					'action' => 'view',
					'icon'   => 'eye',
					'url'    => get_term_link( $term ),
				);
			}

			if ( current_user_can( 'edit_term', $term->term_id ) ) {
				$actions['edit'] = array(
					'type'   => 'action-button',
					'title'  => _x( 'Edit', 'Term action', 'yith-plugin-fw' ),
					'action' => 'edit',
					'url'    => $edit_link,
				);

				if ( $args['duplicate-url'] ) {
					$actions['duplicate'] = array(
						'type'   => 'action-button',
						'title'  => _x( 'Duplicate', 'Term action', 'yith-plugin-fw' ),
						'action' => 'duplicate',
						'icon'   => 'clone',
						'url'    => $args['duplicate-url'],
					);
				}
			}

			if ( current_user_can( 'delete_term', $term->term_id ) ) {
				$delete_url = wp_nonce_url( "edit-tags.php?action=delete&amp;taxonomy=$taxonomy&amp;tag_ID=$term->term_id", 'delete-tag_' . $term->term_id );

				$actions['delete'] = array(
					'type'   => 'action-button',
					'title'  => _x( 'Delete', 'Term action', 'yith-plugin-fw' ),
					'action' => 'delete',
					'icon'   => 'trash',
					'url'    => $delete_url,
				);
				if ( $args['confirm-delete-message'] ) {
					$actions['delete']['confirm_data'] = array(
						'title'               => __( 'Confirm delete', 'yith-plugin-fw' ),
						'message'             => $args['confirm-delete-message'],
						'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-plugin-fw' ),
						'confirm-button-type' => 'delete',
					);
				}
			}

			if ( $args['more-menu'] ) {
				$actions['more'] = array(
					'type'   => 'action-button',
					'title'  => __( 'Further actions', 'yith-plugin-fw' ),
					'action' => 'more',
					'url'    => '#',
					'menu'   => $args['more-menu'],
				);
			}
		}

		return $actions;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_action_buttons' ) ) {
	/**
	 * Retrieve action buttons.
	 *
	 * @param array $actions The actions.
	 * @param bool  $echo    Set to true to print the field directly; false otherwise.
	 *
	 * @return string
	 * @since 3.7.0
	 */
	function yith_plugin_fw_get_action_buttons( $actions, $echo = true ) {
		$actions_html = '';

		foreach ( $actions as $action ) {
			$action['type'] = 'action-button';

			$actions_html .= yith_plugin_fw_get_component( $action, $echo );
		}

		return $actions_html;
	}
}

if ( ! function_exists( 'yith_plugin_fw_get_post_formatted_name' ) ) {
	/**
	 * Get the formatted name for posts/products
	 *
	 * @param int|WP_Post|WC_Product $post The post ID, the post object, or the product object.
	 * @param array                  $args Arguments.
	 *
	 * @return string
	 * @since 3.7.2
	 */
	function yith_plugin_fw_get_post_formatted_name( $post, $args = array() ) {
		$defaults  = array(
			'show-id'   => false,
			'post-type' => false,
		);
		$args      = wp_parse_args( $args, $defaults );
		$post_type = $args['post-type'];
		$show_id   = $args['show-id'];

		if ( is_a( $post, 'WP_Post' ) ) {
			$post_id = $post->ID;
		} elseif ( class_exists( 'WC_Product' ) && is_a( $post, 'WC_Product' ) ) {
			$post_id = $post->get_id();
			if ( false === $post_type ) {
				$post_type = is_a( $post, 'WC_Product_Variation' ) ? 'product_variation' : 'product';
			}
		} else {
			$post_id = absint( $post );
		}

		if ( ! $post_type ) {
			$post_type = get_post_type( $post_id );
		}

		$name = null;

		switch ( $post_type ) {
			case 'product':
			case 'product_variation':
				$product = wc_get_product( $post );
				if ( $product ) {
					$name = $product->get_formatted_name();

					if ( ! $show_id ) {

						if ( $product->get_sku() ) {
							$identifier = $product->get_sku();
						} else {
							$identifier = '#' . $product->get_id();
						}

						// Use normal replacing instead of regex since the identifier could be also the product SKU.
						$name = str_replace( "({$identifier})", '', $name );
					}
				}
		}

		if ( is_null( $name ) ) {
			$name = get_the_title( $post_id );
			if ( $show_id ) {
				$name .= " (#{$post_id})";
			}
		}

		return $name;
	}
}

if ( ! function_exists( 'yith_plugin_fw_add_kses_global_attributes' ) ) {

	/**
	 * Add global attributes to a tag in the allowed HTML list.
	 *
	 * @param array $attributes An array of attributes.
	 *
	 * @return array The array of attributes with global attributes added.
	 *
	 * @since  3.8.0
	 */
	function yith_plugin_fw_add_kses_global_attributes( $attributes ) {
		$global_attributes = array(
			'aria-describedby' => true,
			'aria-details'     => true,
			'aria-label'       => true,
			'aria-labelledby'  => true,
			'aria-hidden'      => true,
			'class'            => true,
			'id'               => true,
			'style'            => true,
			'title'            => true,
			'role'             => true,
			'data-*'           => true,
		);

		if ( true === $attributes ) {
			$attributes = array();
		}

		if ( is_array( $attributes ) ) {
			return array_merge( $attributes, $global_attributes );
		}

		return $attributes;
	}
}
