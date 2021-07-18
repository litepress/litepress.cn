<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Groups')):

	class Lava_Ajax_Search_Groups extends Lava_Ajax_Search_Type {
		private $type = 'groups';

		public static function instance() {
			static $instance = null;

			if (null === $instance) {
				$instance = new Lava_Ajax_Search_Groups();
			}

			return $instance;
		}

		private function __construct() {}

		public function sql( $search_term, $only_totalrow_count=false ){
			global $wpdb, $bp;
			$query_placeholder = array();

			$sql = " SELECT ";

			if ( $only_totalrow_count ) {
				$sql .= " COUNT( DISTINCT g.id ) ";
			} else {
				$sql .= " DISTINCT g.id, 'groups' as type, g.name LIKE %s AS relevance, gm2.meta_value as entry_date ";
				$query_placeholder[] = '%'.$wpdb->esc_like( $search_term ).'%';
			}

			$sql .= " FROM
						{$bp->groups->table_name_groupmeta} gm1, {$bp->groups->table_name_groupmeta} gm2, {$bp->groups->table_name} g
					WHERE
						1=1
						AND g.id = gm1.group_id
						AND g.id = gm2.group_id
						AND gm2.meta_key = 'last_activity'
						AND gm1.meta_key = 'total_member_count'
						AND ( g.name LIKE %s OR g.description LIKE %s )
				";
			$query_placeholder[] = '%'.$wpdb->esc_like( $search_term ).'%';
			$query_placeholder[] = '%'.$wpdb->esc_like( $search_term ).'%';

			if (function_exists('bp_bpla') && 'yes' == bp_bpla()->option('enable-for-groups') ) {

					$split_search_term = explode(' ', $search_term );

					$sql .= "OR g.id IN ( SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'bbgs_group_search_string' AND ";

					foreach ( $split_search_term as $k => $sterm ) {

						if ( $k == 0 ) {
							$sql .= "meta_value LIKE %s";
							$query_placeholder[] = '%'.$wpdb->esc_like( $sterm ) . '%';
						} else {
							$sql .= "AND meta_value LIKE %s";
							$query_placeholder[] = '%'.$wpdb->esc_like( $sterm ) .'%';
						}

					}
					$sql .= " ) ";

			}

			if( is_user_logged_in() ){
				if( !current_user_can( 'level_10' ) ){
					$hidden_groups_sql = $wpdb->prepare( "SELECT DISTINCT gm.group_id FROM {$bp->groups->table_name_members} gm JOIN {$bp->groups->table_name} g ON gm.group_id = g.id WHERE gm.user_id = %d AND gm.is_confirmed = 1 AND gm.is_banned = 0 AND g.status='hidden' ", bp_loggedin_user_id() );
					$hidden_groups_ids = $wpdb->get_col( $hidden_groups_sql );
					if( empty( $hidden_groups_ids ) ){
						$hidden_groups_ids = array( 99999999 );//arbitrarily large number
					}

					$hidden_groups_ids_csv = implode( ',', $hidden_groups_ids );

					$sql .= " AND ( g.status != 'hidden' OR g.id IN ( {$hidden_groups_ids_csv} ) ) ";
				}
			} else {
				$sql .= " AND g.status != 'hidden' ";
			}

			$sql = $wpdb->prepare( $sql, $query_placeholder );

            return apply_filters(
                'Lava_Ajax_Search_Groups_sql',
                $sql,
                array(
                    'search_term'           => $search_term,
                    'only_totalrow_count'   => $only_totalrow_count,
                )
            );
		}

		protected function generate_html( $template_type='' ){
			$group_ids = array();
			foreach( $this->search_results['items'] as $item_id=>$item_html ){
				$group_ids[] = $item_id;
			}

			$args = array( 'include'=>$group_ids, 'per_page'=>count($group_ids), 'search_terms' => false );
			if( is_user_logged_in() ){
				$args['show_hidden'] = true;
			}

			if (function_exists('bp_bpla') ) {
				$args['search_terms'] = ' ';
			}

			if( bp_has_groups( $args ) ){
				while ( bp_groups() ){
					bp_the_group();

					$result = array(
						'id'	=> bp_get_group_id(),
						'type'	=> $this->type,
						'title'	=> bp_get_group_name(),
					);

					ob_start();
					lava_ajaxSearch()->template->load_template(
						Array(
							'file' => 'result-loop',
							'once' => false,
						),
						Array(
							'lvBpSearchResult' => (object) Array(
								'type' => $this->type,
								'search_type' => $template_type,
								'queried_id' => bp_get_group_id(),
								'group_name' => bp_get_group_name(),
							),
						)
					);

					$result[ 'html' ] = ob_get_clean();

					$this->search_results['items'][bp_get_group_id()] = $result;
				}
			}
		}
	}
endif;