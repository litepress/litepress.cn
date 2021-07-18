<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Activities')):

	class Lava_Ajax_Search_Activities extends Lava_Ajax_Search_Type {
		private $type = 'activity';
		public static function instance() {
			static $instance = null;
			if (null === $instance) {
				$instance = new Lava_Ajax_Search_Activities();
			}
			return $instance;
		}

		private function __construct() {}

		function sql( $search_term, $only_totalrow_count=false ){
			global $wpdb, $bp;

			$query_placeholder = array();

			$sql = " SELECT ";

			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT id ) ";
			} else {
				$sql .= " DISTINCT a.id , 'activity' as type, a.content LIKE %s AS relevance, a.date_recorded as entry_date  ";
				$query_placeholder[] = '%'.$wpdb->esc_like( $search_term ).'%';
			}

			$sql .= " FROM
						{$bp->activity->table_name} a
					WHERE
						1=1
						AND is_spam = 0
						AND a.content LIKE %s
						AND a.hide_sitewide = 0
						AND a.type = 'activity_update'
				";
			$query_placeholder[] = '%'.$wpdb->esc_like( $search_term ).'%';
			$sql = $wpdb->prepare( $sql, $query_placeholder );

            return apply_filters(
                'Lava_Ajax_Search_Activities_sql',
                $sql,
                array(
                    'search_term'           => $search_term,
                    'only_totalrow_count'   => $only_totalrow_count,
                )
            );
		}

		protected function generate_html( $template_type='' ){
			$post_ids_arr = array();
			foreach( $this->search_results['items'] as $item_id=>$item_html ){
				$post_ids_arr[] = $item_id;
			}

			$post_ids = implode( ',', $post_ids_arr );

			if( bp_has_activities( array( 'include'=>$post_ids, 'per_page'=>count($post_ids_arr) ) ) ){
				while ( bp_activities() ){
					bp_the_activity();

					$result = array(
						'id'	=> bp_get_activity_id(),
						'type'	=> $this->type,
						'title'	=> $this->search_term,
						'html'	=> '',
					);

					$this->search_results['items'][bp_get_activity_id()] = $result;
				}
			}
		}
	}
endif;