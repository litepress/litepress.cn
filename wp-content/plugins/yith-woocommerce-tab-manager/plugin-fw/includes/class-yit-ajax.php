<?php
/**
 * AJAX Class. AJAX Event Handler.
 *
 * @class   YIT_Ajax
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Ajax' ) ) {
	/**
	 * YIT_Ajax class.
	 *
	 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
	 */
	class YIT_Ajax {

		/**
		 * The single instance of the class.
		 *
		 * @var YIT_Ajax
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YIT_Ajax
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YIT_Ajax constructor.
		 */
		private function __construct() {
			$ajax_actions = array(
				'json_search_posts',
				'json_search_products',
				'json_search_orders',
				'json_search_terms',
			);

			foreach ( $ajax_actions as $ajax_action ) {
				add_action( 'wp_ajax_yith_plugin_fw_' . $ajax_action, array( $this, $ajax_action ) );
				add_action( 'wp_ajax_nopriv_yith_plugin_fw_' . $ajax_action, array( $this, $ajax_action ) );
			}
		}

		/**
		 * Post Search
		 *
		 * @param array $request The request.
		 */
		public function json_search_posts( $request = array() ) {
			ob_start();

			// Make sure request is an array.
			$request = is_array( $request ) ? $request : array();

			if ( empty( $request ) ) {
				check_ajax_referer( 'search-posts', 'security' );
			}

			$term = isset( $request['term'] ) ? $request['term'] : ( isset( $_REQUEST['term'] ) ? (string) sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : '' );
			if ( empty( $term ) ) {
				die();
			}

			$found_posts = array();
			$args        = array(
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'numberposts'      => 20,
				'orderby'          => 'title',
				'order'            => 'asc',
				'suppress_filters' => 0,
				'include'          => '',
				'exclude'          => '',
			);

			foreach ( $args as $key => $default_value ) {
				if ( ! empty( $_REQUEST[ $key ] ) ) {
					$args[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) );
				}
			}

			if ( isset( $_REQUEST['post_parent'] ) ) {
				$args['post_parent'] = intval( $_REQUEST['post_parent'] );
			}

			// Merge with passed request data.
			$args    = array_merge( $args, $request );
			$show_id = ! empty( $_REQUEST['show_id'] );

			$args['s']      = $term;
			$args['fields'] = 'ids';

			$posts = get_posts( $args );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post_id ) {
					if ( ! current_user_can( 'read_post', $post_id ) ) {
						continue;
					}

					$the_title = yith_plugin_fw_get_post_formatted_name(
						$post_id,
						array(
							'post-type' => $args['post_type'],
							'show-id'   => $show_id,
						)
					);

					$found_posts[ $post_id ] = apply_filters( 'yith_plugin_fw_json_search_found_post_title', rawurldecode( wp_strip_all_tags( $the_title ) ), $post_id, $request );
				}
			}

			$found_posts = apply_filters( 'yith_plugin_fw_json_search_found_posts', $found_posts, $request );
			wp_send_json( $found_posts );
		}

		/**
		 * Product Search
		 */
		public function json_search_products() {
			check_ajax_referer( 'search-posts', 'security' );

			$term = isset( $_REQUEST['term'] ) ? (string) wc_clean( wp_unslash( $_REQUEST['term'] ) ) : false;
			if ( empty( $term ) ) {
				die();
			}

			$request         = array( 'post_type' => 'product' );
			$request_include = isset( $_REQUEST['include'] ) && ! is_array( $_REQUEST['include'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['include'] ) ) ) : array();

			if ( ! empty( $_REQUEST['product_type'] ) ) {
				$product_type      = sanitize_text_field( wp_unslash( $_REQUEST['product_type'] ) );
				$product_type_term = get_term_by( 'slug', $product_type, 'product_type' );

				if ( $product_type_term ) {
					$posts_in = array_unique( (array) get_objects_in_term( $product_type_term->term_id, 'product_type' ) );
					if ( ! ! $request_include ) {
						$posts_in = array_intersect( $posts_in, $request_include );
					}

					if ( ! ! $posts_in ) {
						$request['include'] = implode( ',', $posts_in );
					} else {
						$request['include'] = '-1';
					}
				}
			}

			$request = apply_filters( 'yith_plugin_fw_json_search_products_request', $request );
			$this->json_search_posts( $request );
		}

		/**
		 * Order Search
		 */
		public function json_search_orders() {
			global $wpdb;
			ob_start();

			check_ajax_referer( 'search-posts', 'security' );

			$term = isset( $_REQUEST['term'] ) ? intval( $_REQUEST['term'] ) : false;

			if ( empty( $term ) ) {
				die();
			}

			$found_orders = array();
			$term         = apply_filters( 'yith_plugin_fw_json_search_order_number', $term );
			$search       = '%' . intval( $term ) . '%';

			$query_orders = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title FROM {$wpdb->posts} AS posts WHERE posts.post_type = 'shop_order' AND posts.ID LIKE %s",
					$search
				)
			);

			if ( $query_orders ) {
				foreach ( $query_orders as $item ) {
					$order_number              = apply_filters( 'yith_plugin_fw_order_number', '#' . $item->ID, $item->ID );
					$found_orders[ $item->ID ] = esc_html( $order_number ) . ' &ndash; ' . esc_html( $item->post_title );
				}
			}

			wp_send_json( $found_orders );
		}

		/**
		 * Order Search
		 */
		public function json_search_terms() {
			ob_start();

			check_ajax_referer( 'search-terms', 'security' );

			$term = isset( $_REQUEST['term'] ) ? (string) sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : false;

			if ( empty( $term ) ) {
				die();
			}

			$args = apply_filters(
				'yith_plugin_fw_json_search_terms_default_args',
				array(
					'taxonomy'     => 'category',
					'hide_empty'   => false,
					'order'        => 'ASC',
					'orderby'      => 'name',
					'include'      => '',
					'exclude'      => '',
					'exclude_tree' => '',
					'number'       => '',
					'hierarchical' => true,
					'child_of'     => 0,
					'parent'       => '',
					'term_field'   => 'id',
				)
			);

			foreach ( $args as $key => $default_value ) {
				if ( ! empty( $_REQUEST[ $key ] ) ) {
					$args[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) );
				}
			}

			$args = apply_filters( 'yith_plugin_fw_json_search_terms_args', $args );

			$args['name__like'] = $term;
			$args['fields']     = 'id=>name';

			if ( ! taxonomy_exists( $args['taxonomy'] ) ) {
				die();
			}

			$terms = yith_get_terms( $args );

			if ( 'id' !== $args['term_field'] ) {
				$temp_terms = $terms;
				$terms      = array();
				foreach ( $temp_terms as $term_id => $term_name ) {
					$current_term_field           = get_term_field( $args['term_field'], $term_id, $args['taxonomy'] );
					$terms[ $current_term_field ] = $term_name;
				}
			}

			wp_send_json( $terms );
		}
	}
}

YIT_Ajax::instance();
