<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Posts')):

	class Lava_Ajax_Search_Posts extends Lava_Ajax_Search_Type {
		private $pt_name;
		private $search_type;

		public function __construct( $pt_name, $search_type ) {
			$this->pt_name = $pt_name;
			$this->search_type =$search_type;

			add_action( "lava_ajax_search_settings_item_{$this->search_type}", array( $this, 'print_search_options' ) );
		}

		public function sql( $search_term, $only_totalrow_count=false ){
			global $wpdb;
			$query_placeholder = array();

			$sql = " SELECT ";

			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT id ) ";
			} else {
				$sql .= " DISTINCT id , %s as type, post_title LIKE %s AS relevance, post_date as entry_date  ";
				$query_placeholder[] = $this->search_type;
				$query_placeholder[] = '%'. $search_term .'%';
			}

			$sql .= " FROM {$wpdb->posts} p ";
			$items_to_search = lava_ajaxSearch()->admin->get_settings( 'search_filter' );
			$item_limit = 0 === intVal( lava_ajaxSearch()->admin->get_settings( 'search_limit', 0 ) ) ? NULL : ' limit ' .intVal( lava_ajaxSearch()->admin->get_settings( 'search_limit' ) ) . ' ';
			$post_fields 	 = array();
			$tax 			 = array();
			foreach( $items_to_search as $item ) {

				if( strpos( $item, 'post_field_' )===0 ){
					$post_field = str_replace( 'post_field_', '', $item );
					$post_fields[$post_field] = true;
				}

				if ( strpos( $item, '-tax-' ) ) {
					$tax[] = str_replace( $this->search_type.'-tax-', '', $item );
				}
			}

			if ( ! empty( $tax ) ) {
				$sql .= " LEFT JOIN {$wpdb->term_relationships} r ON p.ID = r.object_id ";
			}

			$sql .= ' WHERE 1=1 AND ( p.post_title LIKE %s OR p.post_content LIKE %s ';
			$query_placeholder[] = '%'. $search_term .'%';
			$query_placeholder[] = '%'. $search_term .'%';

			//Tax query
			if ( ! empty( $tax ) ) {

				$tax_in_arr = array_map( function( $t_name ) {
					return "'" . $t_name . "'";
				}, $tax );

				$tax_in = implode( ', ', $tax_in_arr );

				$sql .= " OR  r.term_taxonomy_id IN (SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt INNER JOIN {$wpdb->terms} t ON
					  t.term_id = tt.term_id WHERE ( t.slug LIKE %s OR t.name LIKE %s ) AND  tt.taxonomy IN ({$tax_in}) )";
					$query_placeholder[] = '%'. $search_term .'%';
					$query_placeholder[] = '%'. $search_term .'%';
			}

			if ( ! empty( $post_fields[$this->pt_name.'_meta'] ) ) {
				$sql .= " OR p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE %s )";
				$query_placeholder[] = '%'. $search_term .'%';
			}

			$sql .= ") AND p.post_type = '{$this->pt_name}' AND p.post_status = 'publish' {$item_limit} ";

			$sql = $wpdb->prepare( $sql, $query_placeholder );

            return apply_filters(
                'Lava_Ajax_Search_Posts_sql',
                $sql,
                array(
                    'search_term'           => $search_term,
                    'only_totalrow_count'   => $only_totalrow_count,
                )
            );
		}

		protected function generate_html( $template_type='' ){
			$post_ids = array();
			foreach( $this->search_results['items'] as $item_id=>$item_html ){
				$post_ids[] = $item_id;
			}

			$qry = new WP_Query( Array(
				'post_type' => $this->pt_name,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'post__in'=>$post_ids,
			) );

			if( $qry->have_posts() ){
				while( $qry->have_posts() ){
					$qry->the_post();
					$result = array(
						'id'	=> get_the_ID(),
						'type'	=> $this->search_type,
						'title'	=> get_the_title(),
					);

					ob_start();
					lava_ajaxSearch()->template->load_template(
						Array(
							'file' => 'result-loop',
							'once' => false,
						),
						Array(
							'lvBpSearchResult' => (object) Array(
								'type' => $this->search_type,
								'search_type' => $template_type,
								'queried_id' => get_the_ID(),
							),
						)
					);

					$result[ 'html' ] = ob_get_clean();


					$this->search_results['items'][get_the_ID()] = $result;
				}
			}
			wp_reset_postdata();
		}


		function print_search_options( $items_to_search ){
			global $wp_taxonomies;
			echo "<div class='wp-posts-fields' style='margin: 10px 0 10px 30px'>";

			$label 		= sprintf( __('%s Meta', 'lvbp-ajax-search' ), ucfirst( $this->pt_name ) );
			$item 		= 'post_field_' . $this->pt_name.'_meta';
			$checked 	= ! empty( $items_to_search ) && in_array( $item, $items_to_search ) ? ' checked' : '';

			echo "<label><input type='checkbox' value='{$item}' name='lava_ajax_search_opt[items-to-search][]' {$checked}>{$label}</label>";

			$pt_taxonomy = get_object_taxonomies( $this->pt_name ) ;

			foreach ( $pt_taxonomy as $tax ) {

				$label 		= ucwords( str_replace( '_', ' ', $tax ) );
				$value 		= $this->search_type.'-tax-'.$tax;
				$checked 	= !empty( $items_to_search ) && in_array( $value, $items_to_search ) ? ' checked' : '';

				echo "<label><input type='checkbox' value='{$value}' name='lava_ajax_search_opt[items-to-search][]' {$checked}>{$label}</label>";
			}

			echo "</div><!-- .wp-user-fields -->";
		}

	}

endif;