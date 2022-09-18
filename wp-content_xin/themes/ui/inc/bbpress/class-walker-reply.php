<?php

namespace WCY\Inc\BBPress;

use BBP_Walker_Reply;

if ( class_exists( BBP_Walker_Reply::class ) ) :
	/**
	 * Create hierarchical list of bbPress replies.
	 *
	 * @package bbPress
	 * @subpackage Classes
	 *
	 * @since 2.4.0 bbPress (r4944)
	 */
	class Walker_Reply extends BBP_Walker_Reply {

		/**
		 * @see Walker::start_lvl()
		 *
		 * @since 2.4.0 bbPress (r4944)
		 *
		 * @param string $output Passed by reference. Used to append additional content
		 * @param int $depth Depth of reply
		 * @param array $args Uses 'style' argument for type of HTML list
		 */
		public function start_lvl( &$output = '', $depth = 0, $args = array() ) {
			bbpress()->reply_query->reply_depth = (int) $depth + 1;

			switch ( $args['style'] ) {
				case 'div':
					break;
				case 'ol':
					$output .= "<ol class='bbp-threaded-replies'>\n";
					break;
				case 'ul':
				default:
				    if (0 === $depth) {
					    $output .= "<ul class='bbp-threaded-replies inner-comment-lists'>\n";
                    } else {
					    $output .= "<ul class='bbp-threaded-replies'  >\n";
				    }
					break;
			}
		}

	}

endif; // class_exists check
