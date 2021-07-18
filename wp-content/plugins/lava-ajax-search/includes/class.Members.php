<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Members')):

	class Lava_Ajax_Search_Members extends Lava_Ajax_Search_Type {
		private $type = 'members';

		public static function instance() {
			static $instance = null;
			if (null === $instance) {
				$instance = new Lava_Ajax_Search_Members();

				add_action( 'lava_ajax_search_settings_item_members', array( $instance, 'print_search_options' ) );
			}
			return $instance;
		}

		private function __construct() {}

		public function sql( $search_term, $only_totalrow_count=false ){
			global $wpdb, $bp;
			$query_placeholder = array();
			$items_to_search = Array( 'member_field_user_login' );

			$COLUMNS = " SELECT ";

			if( $only_totalrow_count ){
				$COLUMNS .= " COUNT( DISTINCT u.id ) ";
			} else {
				$COLUMNS .= " DISTINCT u.id, 'members' as type, u.display_name LIKE %s AS relevance, a.date_recorded as entry_date ";
				$query_placeholder[] = '%'. $search_term .'%';
			}

			$FROM = " {$wpdb->users} u JOIN {$bp->members->table_name_last_activity} a ON a.user_id=u.id ";

			$WHERE = array();
			$WHERE[] = "1=1";
			$where_fields = array();

			$user_fields = array();
			foreach( $items_to_search as $item ){
				if( strpos( $item, 'member_field_' )===0 ){
					$user_fields[] = str_replace( 'member_field_', '', $item );
				}
			}

			if( !empty( $user_fields ) ){
				$conditions_wp_user_table = array();
				foreach ( $user_fields as $user_field ) {

					if ( 'user_meta' === $user_field ) {
						$conditions_wp_user_table[] = " ID IN ( SELECT user_id FROM {$wpdb->usermeta} WHERE meta_value LIKE %s ) ";
						$query_placeholder[] = '%'. $search_term .'%';
					} else {
						$conditions_wp_user_table[] = $user_field . " LIKE %s ";
						$query_placeholder[] = '%'. $search_term .'%';
					}

				}


				$clause_wp_user_table = "u.id IN ( SELECT ID FROM {$wpdb->users}  WHERE ( ";
				$clause_wp_user_table .= implode( ' OR ', $conditions_wp_user_table );
				$clause_wp_user_table .= " ) ) ";

				$where_fields[] = $clause_wp_user_table;
			}
			if( function_exists( 'bp_is_active' ) && bp_is_active( 'xprofile' ) ){
				$groups = bp_xprofile_get_groups( array(
					'fetch_fields' => true
				) );

				if ( !empty( $groups ) ){
					$selected_xprofile_fields = array(
						'word_search'   => array(0),
						'char_search'   => array(0),
					);

					$word_search_field_type = array( 'radio', 'checkbox' );

					foreach ( $groups as $group ){
						if ( !empty( $group->fields ) ){
							foreach ( $group->fields as $field ) {
								$item = 'xprofile_field_' . $field->id;
								if( !empty( $items_to_search ) && in_array( $item, $items_to_search ) ) {

									if( in_array( $field->type, $word_search_field_type ) ) {
										$selected_xprofile_fields['word_search'][] = $field->id;
									} else {
										$selected_xprofile_fields['char_search'][] = $field->id;
									}
								}
							}
						}
					}

					if( !empty( $selected_xprofile_fields ) ){
						$clause_xprofile_table = "u.id IN ( SELECT user_id FROM {$bp->profile->table_name_data} WHERE ( value LIKE %s AND field_id IN ( ";
						$clause_xprofile_table .= implode( ',', $selected_xprofile_fields['char_search'] );
						$clause_xprofile_table .= ") ) OR ( value REGEXP '[[:<:]]{$search_term}[[:>:]]' AND field_id IN ( ";
						$clause_xprofile_table .= implode( ',', $selected_xprofile_fields['word_search'] );
						$clause_xprofile_table .= ") ) ) ";

						$where_fields[] = $clause_xprofile_table;
						$query_placeholder[] = '%'. $search_term .'%';
					}
				}
			}

			$split_search_term = explode(' ', $search_term);

			if (count($split_search_term) > 1 ) {

				$clause_search_string_table = "u.id IN ( SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bbgs_search_string' AND ";

				foreach ( $split_search_term as $k => $sterm ) {

					if ( $k == 0 ) {
						$clause_search_string_table .= "meta_value LIKE %s";
						$query_placeholder[] = '%'. $sterm .'%';
					} else {
						$clause_search_string_table .= "AND meta_value LIKE %s";
						$query_placeholder[] = '%'. $sterm .'%';
					}

				}
				$clause_search_string_table .= " ) ";

				$where_fields[] = $clause_search_string_table;

			}

			if( !empty( $where_fields ) )
				$WHERE[] = implode ( ' OR ', $where_fields );

			$WHERE[] = " a.component = 'members' ";
			$WHERE[] = " a.type = 'last_activity' ";

			$sql = $COLUMNS . ' FROM ' . $FROM . ' WHERE ' . implode( ' AND ', $WHERE );
			if( !$only_totalrow_count ){
				$sql .= " GROUP BY u.id ";
			}

			$sql = $wpdb->prepare( $sql, $query_placeholder );
			return apply_filters(
				'Lava_Ajax_Search_Members_sql',
				$sql,
				array(
					'search_term'           => $search_term,
					'only_totalrow_count'   => $only_totalrow_count,
				)
			);
		}

		protected function generate_html( $template_type='' ){
			$group_ids = array();
			foreach( $this->search_results['items'] as $item_id => $item ){
				$group_ids[] = $item_id;
			}

			if( bp_has_members( array( 'search_terms' => '', 'include'=>$group_ids, 'per_page'=>count($group_ids), 'type'=>'alphabetical' ) ) ){
				while ( bp_members() ){
					bp_the_member();
					$result_item = array(
						'id'	=> bp_get_member_user_id(),
						'type'	=> $this->type,
						'title'	=> bp_get_member_name(),
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
								'queried_id' => bp_get_member_user_id(),
								'member_name' => bp_get_member_name(),
							),
						)
					);

					$result_item[ 'html' ] = ob_get_clean();
					$this->search_results['items'][bp_get_member_user_id()] = $result_item;
				}
			}
		}

		function print_search_options( $items_to_search ){
			echo "<div class='wp-user-fields' style='margin: 10px 0 0 30px'>";
			echo "<p class='xprofile-group-name' style='margin: 5px 0'><strong>" . __('Account','lvbp-ajax-search') . "</strong></p>";

			$fields = array(
				'user_login'	=> __( 'Username/Login', 'lvbp-ajax-search' ),
				'display_name'	=> __( 'Display Name', 'lvbp-ajax-search' ),
				'user_email'	=> __( 'Email', 'lvbp-ajax-search' ),
				'user_meta'     => __( 'User Meta', 'lvbp-ajax-search' )
			);
			foreach( $fields as $field=>$label ){
				$item = 'member_field_' . $field;
				$checked = !empty( $items_to_search ) && in_array( $item, $items_to_search ) ? ' checked' : '';
				echo "<label><input type='checkbox' value='{$item}' name='lava_ajax_search_opt[items-to-search][]' {$checked}>{$label}</label>";
			}

			echo "</div><!-- .wp-user-fields -->";

			if( !function_exists( 'bp_is_active' ) || !bp_is_active( 'xprofile' ) )
				return;

			$groups = bp_xprofile_get_groups( array(
				'fetch_fields' => true
			) );

			if ( !empty( $groups ) ){
				echo "<div class='xprofile-fields' style='margin: 0 0 10px 30px'>";
				foreach ( $groups as $group ){
					echo "<p class='xprofile-group-name' style='margin: 5px 0'><strong>" . $group->name . "</strong></p>";

					if ( !empty( $group->fields ) ){
						foreach ( $group->fields as $field ) {
							$item = 'xprofile_field_' . $field->id;
							$checked = !empty( $items_to_search ) && in_array( $item, $items_to_search ) ? ' checked' : '';
							echo "<label><input type='checkbox' value='{$item}' name='lava_ajax_search_opt[items-to-search][]' {$checked}>{$field->name}</label>";
						}
					}
				}
				echo "</div><!-- .xprofile-fields -->";
			}
		}
	}
endif;
