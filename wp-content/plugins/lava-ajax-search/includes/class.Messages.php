<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Messages')):
	class Lava_Ajax_Search_Messages extends Lava_Ajax_Search_Type {
		private $type = 'messages';
		public static function instance() {
			static $instance = null;
			if (null === $instance) {
				$instance = new Lava_Ajax_Search_Messages();
			}
			return $instance;
		}

		private function __construct() {}

		function sql( $search_term, $only_totalrow_count=false ){
			global $wpdb, $bp;
			$sql = " SELECT ";

			$query_placeholder = array();
			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT m.id ) ";
			} else {
				$sql .= " DISTINCT m.id , 'messages' as type, m.message LIKE %s AS relevance, m.date_sent AS entry_date ";
				$query_placeholder[] = '%'. $search_term .'%';
			}

			$sql .= " FROM
						{$bp->messages->table_name_messages} m LEFT JOIN {$bp->messages->table_name_recipients} r ON m.thread_id = r.thread_id
					WHERE
							r.is_deleted = 0
						AND ( m.subject LIKE %s OR m.message LIKE %s )
						AND (
							( r.user_id = %d AND r.sender_only = 0 )
							OR
							( m.sender_id = %d AND m.sender_id = r.user_id )
						)
				";

			$query_placeholder[] = '%'. $search_term .'%';
			$query_placeholder[] = '%'. $search_term .'%';
			$query_placeholder[] = get_current_user_id();
			$query_placeholder[] = get_current_user_id();

			$sql = $wpdb->prepare( $sql, $query_placeholder );
            return apply_filters(
                'Lava_Ajax_Search_Messages_sql',
                $sql,
                array(
                    'search_term'           => $search_term,
                    'only_totalrow_count'   => $only_totalrow_count,
                )
            );
		}

		protected function generate_html( $template_type='' ){
			$message_ids = array();
			foreach( $this->search_results['items'] as $message_id=>$item_html ){
				$message_ids[] = $message_id;
			}

			global $wpdb, $bp;
			$messages_sql = "SELECT m.* "
					. " FROM {$bp->messages->table_name_messages} m  "
					. " WHERE m.id IN ( " . implode( ',', $message_ids ) . " ) ";
			$messages = $wpdb->get_results( $messages_sql );

			$recepients_sql = "SELECT r.thread_id, GROUP_CONCAT( DISTINCT r.user_id ) AS 'recepient_ids' "
					. " FROM {$bp->messages->table_name_recipients} r JOIN {$bp->messages->table_name_messages} m ON m.thread_id = r.thread_id "
					. " WHERE m.id IN ( " . implode( ',', $message_ids ) . " ) "
					. " GROUP BY r.thread_id ";
			$threads_recepients = $wpdb->get_results( $recepients_sql );

			foreach( $threads_recepients as $thread_recepients ){
				foreach( $messages as $message ){
					if( $message->thread_id==$thread_recepients->thread_id ){
						$message->recepients = explode( ',', $thread_recepients->recepient_ids );
					}
				}
			}

			foreach( $messages as $message ){
				global $current_message;
				$current_message = $message;

				$result = array(
					'id'	=> $message->id,
					'type'	=> $this->type,
					'title'	=> $this->search_term,
					'html'	=> '',
				);

				$this->search_results['items'][$message->id] = $result;
			}
		}
	}
endif;