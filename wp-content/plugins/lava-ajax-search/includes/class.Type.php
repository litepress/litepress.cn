<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_Type')):

	abstract class Lava_Ajax_Search_Type {

		protected $search_term = '';
		protected $search_results = array( 'total_match_count'=>false, 'items'=> array(), 'items_title'=> array(), 'html_generated' => false );
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'lvbp-ajax-search'), '1.7');
		}

		public function __wakeup() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'lvbp-ajax-search'), '1.7');
		}

		public function union_sql( $search_term ){
			$this->search_term = $search_term;//save it for future reference may be.

			return $this->sql($search_term);
		}

		public function add_search_item( $item_id ){
			if( !in_array( $item_id, $this->search_results['items'] ) )
				$this->search_results['items'][$item_id] = '';
		}

		public function get_title( $item_id ){
			if( !$this->search_results['html_generated'] ){
				$this->generate_html();
				$this->search_results['html_generated'] = true;//do once only
			}

			return isset( $this->search_results['items'][$item_id]['title'] ) ? $this->search_results['items'][$item_id]['title'] : $this->search_term;
		}

		public function get_total_match_count( $search_term ){
			$this->search_term = $search_term;//save it for future reference may be.

			global $wpdb;
			$sql = $this->sql( $search_term, true );
			return $wpdb->get_var( $sql );
		}

		abstract function sql( $search_term, $only_totalrow_count=false );

		public function get_html( $itemid, $template_type='' ){
			if( !$this->search_results['html_generated'] ){
				$this->generate_html( $template_type );
				$this->search_results['html_generated'] = true;//do once only
			}

			return isset( $this->search_results['items'][$itemid] ) ? ($this->search_results['items'][$itemid]['html'] ?? '') : '';
		}
	}
endif;