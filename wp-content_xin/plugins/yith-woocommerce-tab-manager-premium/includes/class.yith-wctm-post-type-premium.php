<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_WCTM_Post_Type_Premium' ) ) {
	class YITH_WCTM_Post_Type_Premium extends YITH_WCTM_Post_Type {

		protected static $instance;

		public function __construct() {
			parent::__construct();

			add_filter( 'yith_add_column_tab', array( $this, 'add_columns_in_tab_type' ), 10 );
			add_action( 'ywtm_show_custom_columns', array( $this, 'show_custom_columns' ), 10, 2 );
			add_filter( 'yith_wctm_post_type', array( $this, 'add_post_type_args' ), 10, 1 );

			add_filter( 'manage_edit-' . $this->post_type_name . '_sortable_columns', array( $this, 'custom_sortable_columns' ) );


		}

		/**
		 * Returns single instance of the class
		 * @author Salvatore Strano
		 * @return YITH_WCTM_Post_Type_Premium
		 * @since 2.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**Add premium columns in the tab table
		 * @author Salvatore
		 * @since 2.0.0
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		public function add_columns_in_tab_type( $columns ) {

			unset( $columns['date'] );
			$columns['tab_type'] = __( 'Tab type', 'yith-woocommerce-tab-manager' );
			$columns['tab_layout'] = __( 'Tab Layout', 'yith-woocommerce-tab-manager' );
			$columns['date']     = __( 'Date', 'yith-woocommerce-tab-manager' );


			$desc_column = array( 'description' => __( 'Description', 'yith-woocommerce-tab-manager' ) );

			$k = array_search( 'title', array_keys( $columns ) );

			$new_columns = array_slice( $columns, 0, $k+1, true )+$desc_column+array_slice( $columns, $k, count( $columns )-1, true );

			$new_columns['last-modified'] = __( 'Last Modified', 'yith-woocommerce-tab-manager' );

			return $new_columns;
		}

		/**
		 * Print the content columns
		 * @author Salvatore Strano
		 * @since 2.0.0
		 *
		 * @param $column
		 * @param $post_id
		 */
		public function show_custom_columns( $column, $post_id ) {

			switch ( $column ) {
				case 'tab_type' :

					$types = ywtm_get_tab_types();
					$type = get_post_meta( $post_id, '_ywtm_tab_type', true );

					if ( empty( $type ) || $type == 'global' ) {
						echo $types['global'];
					} else {
						echo $types[$type];
					}

					break;
				case 'tab_layout':

					$layouts = ywtm_get_layout_types();
					$type_layout = get_post_meta( $post_id, '_ywtm_layout_type', true );

					if( !empty( $type_layout ) && !empty( $layouts[$type_layout ] ) ){
						echo $layouts[$type_layout ];
					}else{
						echo 'N/A';
					}
					break;
				case 'description':
					$post        = get_post( $post_id );
					$description = $post->post_excerpt;

					if ( empty( $description ) ) {
						$description = __( 'No description', 'yith-woocommerce-tab-manager' );
					}
					$desc = sprintf( '<div class="ywtm_description show_more" data-max_char="%s" data-more_text="%s" data-less_text="%s">%s</div>', 80, __( 'Show more', 'yith-woocommerce-tab-manager' ), __( 'Show less', 'yith-woocommerce-tab-manager' ), $description );
					echo $desc;
					break;

				case 'last-modified' :
					$post          = get_post( $post_id );
					$t_time        = get_the_modified_time( __( 'Y/m/d g:i:s a' ), $post );
					$last_modified = $post->post_modified;
					$h_time        = mysql2date( __( 'Y/m/d' ), $last_modified );
					echo '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
					break;
			}
		}

		/**
		 * add sortable column in tab manager list
		 * @author Salvatore Strano
		 * @param array $sortable_columns
		 *
		 * @return array
		 */
		public function custom_sortable_columns( $sortable_columns ) {
			$sortable_columns['last-modified'] = 'last_modified';

			return $sortable_columns;
		}

		/**
		 * add args into post type
		 * @author Salvatore Strano
		 * @since 1.1.15
		 *
		 * @param $post_type_args
		 *
		 * @return array
		 */
		public function add_post_type_args( $post_type_args ) {

			$post_type_args['supports'][] = 'excerpt';

			return $post_type_args;
		}

		/*public function get_product_tabs( $tab_type = 'global', $show_all = true ) {

			global $wpdb;

			$query = "SELECT ID FROM {$wpdb->posts} JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND 
                    ( {$wpdb->postmeta}.meta_key = '_ywtm_tab_type' AND {$wpdb->postmeta}.meta_value = %s ) AND 
                    {$wpdb->posts}.post_status ='publish' AND
                    {$wpdb->posts}.post_type = 'ywtm_tab';";

			$query  = $wpdb->prepare( $query, array( $tab_type ) );
			$result = $wpdb->get_col( $query );

			$q_tabs = array();

			foreach ( $result as $tab ) {
				$is_visible = get_post_meta( $tab, '_ywtm_show_tab', true );
				if ( $is_visible ) {
					if ( $show_all ) {

						$q_tabs[] = get_post( $tab );
					} else {

						$custom_content = get_post_meta( $tab, '_ywtm_enable_custom_content', true );

						if ( ! $custom_content ) {
							$q_tabs[] = get_post( $tab );
						}
					}
				}
			}

			return $q_tabs;

		}*/

		/**
		 * return the tabs to show in the product metaboxes
		 * @author Salvatore Strano
		 * @since 2.0.0
		 *
		 * @param string $tab_type
		 * @param boolean $show_all
		 *
		 * @return array
		 */
		 public function get_product_tabs( $tab_type = 'global', $show_all = true ){
			  $args = array(
				  'post_type'        => 'ywtm_tab',
				  'post_status'      => 'publish',
				  'posts_per_page'   => - 1,
				  'meta_key'         => '_ywtm_order_tab',
				  'orderby'          => 'meta_value_num',
				  'order'            => 'ASC',
				  'meta_query'       => array(
					  'relation' => 'AND',
					  array(
						  'key'     => '_ywtm_tab_type',
						  'value'   => $tab_type,
						  'compare' => '='
					  )
				  ),
				  'suppress_filters' => false,

			  );

			  if( !$show_all ) {
				  $args['meta_query'][] = array(
					  'key' => '_ywtm_enable_custom_content',
					  'value' => 0,
					  'compare' => '='
				  );
			  }

			 if( apply_filters( 'ywtm_include_only_visible_tab', true ) ){
				 $args['meta_query'][] =   array(
					 'key'     => '_ywtm_show_tab',
					 'value'   => 1,
					 'compare' => '='
				 );
			 }



			 if ( function_exists( 'pll_get_post_language' ) ) {
				  $args = ywtm_get_tab_ppl_language( $args );
			  }

			  if ( isset( $_GET['lang'] ) ) {
				  $args['lang'] = $_GET['lang'];
			  }

			  if ( function_exists( 'wpml_get_language_information' ) ) {
				  $lang_info    = wpml_get_language_information();
				  $args['lang'] = $lang_info['language_code'];
			  }

			  $q_tabs = get_posts( $args );

			  return $q_tabs;
		  }
	}
}