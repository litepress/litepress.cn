<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Terms')):

	class Lava_Ajax_Search_Terms extends Lava_Ajax_Search_Type {
		private $pt_name;
		private $search_type;

		public function __construct( $search_type ) {
			$this->pt_name = NULL; // $pt_name;
			$this->search_type = $search_type;

			add_action( "lava_ajax_search_settings_item_{$this->search_type}", array( $this, 'print_search_options' ) );
		}

		public function sql( $search_term, $only_totalrow_count=false ){
			global $wpdb;
			$query_placeholder = array();

			$item_limit = 0 === intVal( lava_ajaxSearch()->admin->get_settings( 'search_limit', 0 ) ) ? NULL : ' limit ' .intVal( lava_ajaxSearch()->admin->get_settings( 'search_limit' ) ) . ' ';

			$sql = " SELECT ";

			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT t.term_id ) ";
			} else {
				$sql .= " DISTINCT t.term_id as id , %s as type, t.name LIKE %s AS relevance, tt.count as entry_date  ";
				$query_placeholder[] = $this->search_type;
				$query_placeholder[] = '%'. $search_term .'%';
			}

			$sql .= " FROM {$wpdb->term_taxonomy} tt ";
			$sql .= " LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id ";

			$sql .= ' WHERE 1=1 AND ( t.name LIKE %s AND tt.taxonomy=%s ';
			$query_placeholder[] = '%'. $search_term .'%';
			$query_placeholder[] = $this->search_type;

			$sql .= $only_totalrow_count ? ") " : ") {$item_limit} ";
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
			ob_start();
			foreach( $this->search_results['items'] as $item_id => $item_html ){
				$result = array(
					'id'	=> $item_id,
					'type'	=> $this->search_type,
					'title'	=> get_term( $item_id )->name,
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
							'queried_id' => $item_id,
							'count' => $GLOBALS[ 'wpdb' ]->get_var( $this->sql( $this->search_type, true ) ),
						),
					)
				);
				$result[ 'html' ] = ob_get_clean();
				$this->search_results['items'][$item_id] = $result;
			}
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