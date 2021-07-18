<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Posts_Comments')):

	class Lava_Ajax_Search_Posts_Comments extends Lava_Ajax_Search_Type {
		private $type = 'posts_comments';
		public static function instance() {

			static $instance = null;

			if (null === $instance) {
				$instance = new Lava_Ajax_Search_Posts_Comments();
			}
			return $instance;
		}
		private function __construct() {}

		public function sql( $search_term, $only_totalrow_count=false ){

			global $wpdb;
			$query_placeholder = array();

			$sql = " SELECT ";

			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT comment_ID ) ";
			} else {
				$sql .= " DISTINCT comment_ID AS id, 'posts_comments' as type, comment_content LIKE %s AS relevance, comment_date as entry_date  ";
				$query_placeholder[] = '%'. $search_term .'%';
			}


			$sql .= " FROM {$wpdb->comments} WHERE 1=1 AND comment_content LIKE %s AND comment_approved = 1 ";

			$query_placeholder[] = '%'.$search_term .'%';

			$sql = $wpdb->prepare( $sql, $query_placeholder );

            return apply_filters(
                'Lava_Ajax_Search_Posts_Comments_sql',
                $sql,
                array(
                    'search_term'           => $search_term,
                    'only_totalrow_count'   => $only_totalrow_count,
                )
            );
		}

		protected function generate_html( $template_type='' ) {
			$comment_ids = array();
			foreach( $this->search_results['items'] as $item_id=>$item_html ){
				$comment_ids[] = $item_id;
			}

			$comment_query = new WP_Comment_Query( array( 'comment__in'=> $comment_ids ) );
			$_comments = $comment_query->comments;

			foreach ( $_comments as $_comment ) {
				global $current_comment;
				$current_comment = $_comment;

				$result = array(
					'id'	=> $_comment->comment_ID,
					'type'	=> $this->type,
					'title'	=> $this->search_term,
				);

				ob_start();
				lava_ajaxSearch()->template->load_template(
					Array(
						'file' => 'result-loop',
					),
					Array(
						'lvBpSearchResult' => (object) Array(
							'type' => $this->type,
							'search_type' => $template_type,
							'queried_id' => get_the_ID(),
						),
					)
				);

				$result[ 'html' ] = ob_get_clean();

				$this->search_results['items'][$_comment->comment_ID] = $result;
			}
		}
	}
endif;