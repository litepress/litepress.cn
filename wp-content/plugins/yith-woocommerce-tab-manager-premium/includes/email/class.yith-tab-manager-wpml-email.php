<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements the YITH_YWRAQ_Multilingual_Email class.
 *
 * @class   YITH_YWRAQ_Multilingual_Email
 * @package YITH
 * @since   1.0.0
 * @author  YITH
 */
if ( !class_exists( 'YITH_YTM_Multilingual_Email' ) && class_exists('WCML_Emails') ) {

	/**
	 * YITH_YTM_Multilingual_Email
	 *
	 * @since 1.0.0
	 */
	class YITH_YTM_Multilingual_Email extends WCML_Emails {

		/**
		 * YITH_YWRAQ_Multilingual_Email constructor.
		 */
		function __construct(  ) {

			global $woocommerce_wpml, $sitepress, $woocommerce, $wpdb;

			if( !is_null( $sitepress ) ) {
				if ( version_compare( WCML_VERSION, '4.2.10', '<' ) ) {
					parent::__construct( $woocommerce_wpml, $sitepress, $woocommerce );
				} else {
					parent::__construct( $woocommerce_wpml, $sitepress, $woocommerce, $wpdb );
				}
			}
			// Call parent constructor


			add_action( 'send_tab_manager_email_notification', array( $this, 'refresh_email_lang'), 10, 1 );
		}

		/**
		 * @param $order_id
		 */
		function refresh_email_lang( $args ){
			global $sitepress;

			if ( ! empty( $args['language'] ) ) {
				$lang = $args['language'];

				$sitepress->switch_lang($lang,true);
			}

		}
	}

	// returns instance of the mail on file include
	return new YITH_YTM_Multilingual_Email();
}

