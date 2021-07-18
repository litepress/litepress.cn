<?php
/**
 * YITH
 * 
 * @package WordPress
 * @subpackage YITH
 * @author YITH <plugins@yithemes.com>
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if( ! class_exists( 'YITH_Dashboard' ) ){
	/**
	 * Wordpress Admin Dashboard Management
	 *
	 * @since 1.0.0
	 */
	class YITH_Dashboard {
		/**
		 * Products URL
		 *
		 * @var string
		 * @access protected
		 * @since 1.0.0
		 */
		static protected $_products_feed = 'https://yithemes.com/latest-updates/feeds/';
		static protected $_blog_feed     = 'https://yithemes.com/feed/';

		/**
		 * Dashboard widget setup
		 *
		 * @return void
		 * @since 1.0.0
		 * @access public
		 */
		public static function dashboard_widget_setup() {
			wp_add_dashboard_widget( 'yith_dashboard_products_news', __( 'YITH Latest Updates' , 'yith-plugin-fw' ), 'YITH_Dashboard::dashboard_products_news' );
			wp_add_dashboard_widget( 'yith_dashboard_blog_news', __( 'Latest news from YITH Blog' , 'yith-plugin-fw' ), 'YITH_Dashboard::dashboard_blog_news' );
		}


		/**
		 * Product news Widget
		 *
		 * @return void
		 * @since 1.0.0
		 * @access public
		 */
		public static function dashboard_products_news() {
			$items = 10;
			$rss = static::$_products_feed;
			if ( is_string( $rss ) ) {
				$rss = fetch_feed( $rss );
			} elseif ( is_array( $rss ) && isset( $rss['url'] ) ) {
				$rss  = fetch_feed( $rss['url'] );
			} elseif ( ! is_object( $rss ) ) {
				return;
			}

			if ( is_wp_error( $rss ) ) {
				if ( is_admin() || current_user_can( 'manage_options' ) ) {
					echo '<p><strong>' . __( 'RSS Error:', 'yith-plugin-fw' ) . '</strong> ' . $rss->get_error_message() . '</p>';
				}
				return;
			}

			if ( ! $rss->get_item_quantity() ) {
				echo '<ul><li>' . __( 'An error has occurred, which probably means the feed is down. Try again later.', 'yith-plugin-fw' ) . '</li></ul>';
				$rss->__destruct();
				unset( $rss );
				return;
			}

			$last_updates = $rss->get_items( 0, $items );
			$html_classes = 'rsswidget yith-update-feeds';
			$output       = '';

			if( count( $last_updates ) > 0 ){
				$output = '<ul class="yith-update-feeds">';
			}

			foreach ( $last_updates as $last_update ) {
				/**
				 * @var $last_update \SimplePie_Item
				 */
				$output    .= '<li class="yith-update-feed">';
				$date      = $last_update->get_date( 'U' );
				$date_i18n = ! empty( $date ) ? date_i18n( get_option( 'date_format' ), $date ) : '';
				$html_date = ! empty( $date_i18n ) ? ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date ) . '</span>' : '';
				$output    .= sprintf( '<a target="_blank" href="%s" class="%s">%s</a> %s', $last_update->get_permalink(), $html_classes, $last_update->get_title(), $html_date );
				$changelog = $last_update->get_description();

				if( ! empty( $changelog ) ){
					//add_thickbox();
					$output .= ' - ';
					$output .= sprintf( '<a class="yith-last-changelog" href="#" data-changelogid="%s" data-plugininfo="%s">%s</a>', $last_update->get_id( true ), $last_update->get_title(), _x( 'View Changelog', 'Plugin FW', 'yith-plugin-fw' ) );
					$output .= sprintf( '<div style="display: none;" id="%s"><div style="display: table;"><img class="yith-feeds-logo" src="%s" /><h3 class="yith-feeds-plugin-name"><span style="font-weight: normal;">%s</span> %s</h3></div><p>%s</p></div>', $last_update->get_id( true ), yith_plugin_fw_get_default_logo(), _x( 'Latest update released on', 'Plugin FW', 'yith-plugin-fw' ), $date_i18n, $changelog );
				}

				$output .= '</li>';
			}

			if( ! empty( $output ) ){
				$output .= '</ul>';
			}

			echo $output;
			$rss->__destruct();
			unset( $rss );
		}

		/**
		 * Blog news Widget
		 *
		 * @return void
		 * @since 1.0.0
		 * @access public
		 */
		public static function dashboard_blog_news() {
			$args = array( 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1, 'items'=> 3 );
			$feed = static::$_blog_feed;
			wp_widget_rss_output( $feed, $args );
		}

		/**
		 * Enqueue Styles and Scripts for View Last Changelog widget
		 *
		 * @return void
		 * @since 1.0.0
		 * @access public
		 */
		public static function enqueue_scripts(){
			if( function_exists( 'get_current_screen' ) && 'dashboard' == get_current_screen()->id ){
				$script_path = defined( 'YIT_CORE_PLUGIN_URL' ) ? YIT_CORE_PLUGIN_URL : get_template_directory_uri() . '/core/plugin-fw';
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				wp_enqueue_script( 'yith-dashboard', $script_path . '/assets/js/yith-dashboard' . $suffix . '.js', array( 'jquery-ui-dialog' ), '1.0.0', true );
				wp_enqueue_style( 'wp-jquery-ui-dialog' );
				$l10n = array(
					'buttons' => array(
						'close' => _x( 'Close', 'Button label', 'yith-plugin-fw' )
					)
				);
				wp_localize_script( 'yith-dashboard', 'yith_dashboard', $l10n );
			}
		}
	}

	if( apply_filters( 'yith_plugin_fw_show_dashboard_widgets', true ) ){
		add_action( 'wp_dashboard_setup', 'YITH_Dashboard::dashboard_widget_setup' );
		add_action( 'admin_enqueue_scripts', 'YITH_Dashboard::enqueue_scripts', 20 );
	}
}

