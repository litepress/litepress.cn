<?php

class Lava_Ajax_Search_Core {

	public $instances = Array();

	public function __construct() {
		$this->load_files();
		$this->register_hooks();
		$this->ajax_Hook();
	}

	public function load_files() {
		$arrEngines = Array(

			'Type' => array(),
			'bbPress' => array(),

			'Activities' => array(),
			'Activity_Comments' => array(),

			'bbPress_Forums' => array(),
			'bbPress_Replies' => array(),
			'bbPress_Topics' => array(),

			'Members' => array(),
			'Messages' => array(),
			'Groups' => array(),

			'Posts' => array(),
			'Posts_Comments' => array(),
			'Terms' => array(),
		);

		if( !empty( $arrEngines ) ) {
			foreach( $arrEngines as $engine => $engineMeta ) {
				$strFileName = sprintf( '%s/includes/class.%s.php', lava_ajaxSearch()->path, $engine );
				if( file_exists( $strFileName ) ) {
					require_once( $strFileName );
				}
			}
		}
	}

	public function register_hooks() {
		add_action( 'init', array( $this, 'register_engines' ), 15 );
		add_filter( 'lava/ajax-search/type/label', array( $this, 'labelFilter' ) );
	}

	public function register_engines() {

		$this->instances = Array(
			'posts' => new Lava_Ajax_Search_Posts( 'post', 'posts' ),
			'pages' => new Lava_Ajax_Search_Posts( 'page', 'pages' ),
			'listings' => new Lava_Ajax_Search_Posts( 'lv_listing', 'listings' ),
			'products' => new Lava_Ajax_Search_Posts( 'product', 'products' ),
			'posts_comments' => Lava_Ajax_Search_Posts_Comments::instance(),
			'forum' => Lava_Ajax_Search_bbPress_Forums::instance(),
			'topic' => Lava_Ajax_Search_bbPress_Topics::instance(),
			'reply' => Lava_Ajax_Search_bbPress_Replies::instance(),
			'members' => Lava_Ajax_Search_Members::instance(),
			'groups' => Lava_Ajax_Search_Groups::instance(),
			'activity' => Lava_Ajax_Search_Activities::instance(),
			'activity_comment' => Lava_Ajax_Search_Activity_Comment::instance(),
			'messages' => Lava_Ajax_Search_Messages::instance(),
		);
		$this->search_helpers = apply_filters( 'lava_ajax_search_instances', $this->instances, $this );
	}

	public function labelFilter( $label='' ) {
		$replaces = apply_filters('lava/ajax-search/type/label/replace', Array(
			'posts' => esc_html__( "Posts", 'lvbp-ajax-search' ),
			'pages' => esc_html__( "Pages", 'lvbp-ajax-search' ),
			'listings' => esc_html__( "Listings", 'lvbp-ajax-search' ),
			'products' => esc_html__( "Products", 'lvbp-ajax-search' ),
			'posts_comments' => esc_html__( "Posts Comments", 'lvbp-ajax-search' ),
			'forum' => esc_html__( "Forum", 'lvbp-ajax-search' ),
			'topic' => esc_html__( "Topic", 'lvbp-ajax-search' ),
			'reply' => esc_html__( "Reply", 'lvbp-ajax-search' ),
			'members' => esc_html__( "Members", 'lvbp-ajax-search' ),
			'groups' => esc_html__( "Groups", 'lvbp-ajax-search' ),
			'activity' => esc_html__( "Activity", 'lvbp-ajax-search' ),
			'activity_comment' => esc_html__( "Activity Comments", 'lvbp-ajax-search' ),
			'messages' => esc_html__( "Messages", 'lvbp-ajax-search' ),
		));
		return str_replace( array_keys( $replaces ), array_values($replaces), $label );
	}

	public function ajax_Hook() {
		add_action( 'wp_ajax_lava_ajax_search_query', array( $this, 'searchResponse' ) );
		add_action( 'wp_ajax_nopriv_lava_ajax_search_query', array( $this, 'searchResponse' ) );
	}

	public function prepare_search_page(){
		$args = array();
		if( isset( $_GET['subset'] ) && !empty( $_GET['subset'] ) ){
			$args['search_subset'] = $_GET['subset'];
			$args['per_page'] = 0;
		}

		if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ){
			$args['search_term'] = $_GET['s'];
		}

		if( isset( $_GET['list'] ) && !empty( $_GET['list'] ) ){
			$current_page = (int)$_GET['list'];
			if( $current_page > 0 ){
				$args['current_page'] = $current_page;
			}
		}
		$this->do_search( $args );
	}

	public function getExcludeSearchable() {
		return apply_filters('lava/ajax-search/search/print_tab/exclude', Array(
			'listing_keyword'
		), $this->search_args);
	}

	public function print_tabs(){
		$search_url = $this->search_page_search_url();

		$class = 'all'==$this->search_args['search_subset'] ? 'active current' : '';
		$all_label = esc_html__( "All", 'lvbp-ajax-search' );

		$label = apply_filters( 'lava/ajax-search/type/label', $all_label );

		if( $this->search_args['count_total'] && isset( $this->search_results['all'] ) )    {
			$label .= "<span class='count'>" . $this->search_results['all']['total_match_count'] . "</span>";
		}

		$tab_url = $search_url;
		echo "<li class='{$class}'><a href='" . esc_url($tab_url) . "'>{$label}</a></li>";

		//then other tabs
		$search_items = lava_ajaxSearch()->admin->get_settings( 'search_filter' );
		foreach( (array) $this->search_args['searchable_items'] as $item ){

			if(in_array($item, $this->getExcludeSearchable())) {
				continue;
			}

			$class = $item==$this->search_args['search_subset'] ? 'active current' : '';
			//this filter can be used to change display of 'posts' to 'Blog Posts' etc..

			$label = isset ( $search_items[$item] ) ? $search_items[$item] : $item;

			$label = apply_filters( 'lava/ajax-search/type/label', $label);

			if(empty($this->search_results[$item]['total_match_count'])) {
				continue; //skip tab
			}

			if( $this->search_args['count_total'] ){
				$label .= "<span class='count'>" . (int)$this->search_results[$item]['total_match_count'] . "</span>";
			}

			$tab_url = esc_url(add_query_arg( 'subset', $item, $search_url ));
			echo "<li class='{$class} {$item}' data-item='{$item}'><a href='" . esc_url($tab_url) . "'>{$label}</a></li>";
		}
	}

	public function print_results(){
		$current_tab = $this->search_args['search_subset'];
		if( isset( $this->search_results[$current_tab]['items'] ) && !empty( $this->search_results[$current_tab]['items'] ) ){
			foreach( $this->search_results[$current_tab]['items'] as $item_id=>$item ){
				echo $item['html'];
			}
		} else {
			lava_ajaxSearch()->template->load_template(
				Array(
					'file' => 'no-result',
					'once' => false,
				),
				Array(
					'lvBpSearchResult' => (object) Array(
						'tab' => $current_tab,
						'not_found' => esc_html__( "Not found results", 'lvbp-ajax-search' ),
					),
				)
			);
		}
	}

	public function searchResponse() {
		$args = array(
			'search_term'	=> isset($_REQUEST['search_term'])?$_REQUEST['search_term']:'',
			'per_page'		=>  isset($_REQUEST['per_page'])?$_REQUEST['per_page']:false,
			'count_total'	=> false,
			'template_type'	=> 'ajax',
		);

		if( isset( $_REQUEST['forum_search_term']) ) {
			$args['forum_search'] = true;
		}

		$this->do_search( $args );

		$search_results = array();
		if( isset( $this->search_results['all']['items'] ) && !empty( $this->search_results['all']['items'] ) ){
			$types = array();
			foreach( $this->search_results['all']['items'] as $item_id=>$item ){
				$type = $item['type'];
				if( empty( $types ) || !in_array( $type, $types ) ){
					$types[] = $type;
				}
			}

			$types = apply_filters( 'lava_ajax_search_type_orders', $types );

			$new_items = array();
			foreach( $types as $type ){
				$first_html_changed = false;
				foreach( $this->search_results['all']['items'] as $item_id=>$item ){
					if( $item['type']!= $type ) {
						continue;
					}
					$new_items[$item_id] = $item;
				}
			}

			$this->search_results['all']['items'] = $new_items;
			$url = $this->search_page_search_url();
			$url = esc_url(add_query_arg( array( 'no_frame' => '1' ), $url ));
			$type_mem = "";
			foreach( $this->search_results['all']['items'] as $item_id=>$item ){
				$new_row = array( 'value'=>$item['html'] );
				$type_label = apply_filters( 'lava/ajax-search/type/label', $item['type'] );
				$new_row['type'] = $item['type'];
				$new_row['type_label'] = "";
				$new_row['value'] = $item['html'];
				if( isset( $item['id'] ) ) {
					$new_row['object_id'] = $item['id'];
				}
				if( isset( $item['title'] ) ){
					$new_row['label'] = $item['title'];
				}

				if($type_mem != $new_row['type']) {
					$type_mem = $new_row['type'];
					$cat_row = $new_row;
					$cat_row["type"] = $item['type'];
					$cat_row['type_label'] = $type_label;
					$category_search_url = esc_url(add_query_arg( array( 'subset' => $item['type'] ), $url ));
					$html = "<span class='type-label'><a href='" . esc_url( $category_search_url ) . "'>" . $type_label . "</a></span>";
					$cat_row["value"] = apply_filters('buddypress_gs_autocomplete_category', $html, $item['type'], $url, $type_label);
					$search_results[] = $cat_row;
				}

				$search_results[] = $new_row;
			}

			$all_results_row = array(
				"value" => "<div class='lava_ajax_search_item allresults'><a href='" . esc_url( $url ) . "'>" . sprintf( __( "View all results for '%s'", 'lvbp-ajax-search' ), stripslashes( $this->search_args['search_term'] ) ) . "</a></div>",
				"type"	=> 'view_all_type',
				"type_label"	=> ''
			);
			$search_results[] = $all_results_row;
		} else {
			$search_results[] = array(
				'value' => '<div class="lava_ajax_search_item noresult">' . sprintf( __( "Nothing found for '%s'", 'lvbp-ajax-search' ), stripslashes( $this->search_args['search_term'] ) ) . '</div>',
				'label'	=> $this->search_args['search_term']
			);
		}

		wp_send_json( $search_results );
	}

	public function do_search( $args='' ){
		global $wpdb;

		$args = $this->sanitize_args( $args );
		$defaults = array(
			'search_term'		=> '',
			'search_subset'		=> 'all',
			'searchable_items' => lava_ajaxSearch()->admin->get_settings( 'search_filter' ),
			'per_page'			=> 5,
			'current_page'		=> 1,
			'count_total'		=> true,
			'template_type'		=> '',
			'forum_search' => false,
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'lava_ajax_search_prepare_search', $args, $this );

		if ( true === $args[ 'forum_search' ] ) {
			$args[ 'searchable_items' ] = array( 'forum', 'topic', 'reply' );
		}

		/*
		if(
			!in_array('listing_category', $defaults['searchable_items']) &&
			isset($this->search_helpers['listing_category'])
		) {
			unset($this->search_helpers['listing_category']);
		} */

		$this->search_args = $args;

		if( !$args['search_term'] ) {
			return;
		}

		$search_url = $this->search_page_search_url();

		$sqlItemsLimit = '';
		if( $args['per_page']> 0 ){
			$offset = ( $args['current_page'] * $args['per_page'] ) - $args['per_page'];
			$sqlItemsLimit = " LIMIT {$offset}, {$args['per_page']} ";
		}

		if( 'all' == $args['search_subset'] ){

			$sql_queries = array();
			foreach( $args['searchable_items'] as $search_type ){
				if( !isset($this->search_helpers[$search_type]))
					continue;
				$obj = $this->search_helpers[$search_type];

				$sqlLimit =
				$sql_queries[] = "( " . $obj->union_sql( $args['search_term'] ) . ' ' . $sqlItemsLimit . " ) ";
			}

			if( empty( $sql_queries ) ){
				return;
			}

			$pre_search_query = implode( ' UNION ', $sql_queries) . " ORDER BY relevance, type DESC, entry_date DESC ";

			/*
			if( $args['per_page']> 0 ){
				$offset = ( $args['current_page'] * $args['per_page'] ) - $args['per_page'];
				$pre_search_query .= " LIMIT {$offset}, {$args['per_page']} ";
			} */

			$results = $wpdb->get_results( $pre_search_query );

			if( !empty( $results ) ){
				$this->search_results['all'] = array( 'total_match_count' => 0, 'items' => array(), 'items_title'=> array() );

				foreach( $results as $item ){
					$obj = $this->search_helpers[$item->type];
					$obj->add_search_item( $item->id );
				}

				foreach( $results as $item ){
					$obj = $this->search_helpers[$item->type];
					$result = array(
						'id'	=> $item->id,
						'type'	=> $item->type,
						'html'	=> $obj->get_html( $item->id, $args['template_type'] ),
						'title'	=> $obj->get_title( $item->id )
					);
					$this->search_results['all']['items'][$item->id] = $result;
				}

				if( !empty( $this->search_results['all']['items'] ) && $args['template_type']!='ajax' ){
					$ordered_items_group = array();
					foreach( $this->search_results['all']['items'] as $item_id=>$item ){
						$type = $item['type'];
						if( !isset( $ordered_items_group[$type] ) ){
							$ordered_items_group[$type] = array();
						}

						$ordered_items_group[$type][$item_id] = $item;
					}

					$search_items = lava_ajaxSearch()->admin->get_settings( 'search_filter' );
					foreach( $ordered_items_group as $type=>&$items ){
						$label = isset( $search_items[$type] ) ? $search_items[$type] : $type;
						$first_item = reset($items);
						$start_html = "<div class='results-group results-group-{$type} results-wrap'>"
								.	"<h2 class='results-group-title'><span>" . $label . "</span></h2>"
								.	"<ul id='{$type}-stream' class='item-list {$type}-list results-lists'>";

						$group_start_html = $start_html;

						$first_item['html'] = $group_start_html . $first_item['html'];
						$items[$first_item['id']] = $first_item;

						//and append html (closing tags) to last item of each type
						$last_item = end($items);
						$end_html = '</ul>';

						$tab_url = esc_url(add_query_arg( 'subset', $type, $search_url ));
						$end_html .= sprintf(
							'<a href="%1$s" class="btn btn-primary btn-sm btn-see-more btn-type-%2$s">' .
							'%3$s <i class="fas fa-arrow-right"></i></a>', $tab_url, $type,
							esc_html__("See More", 'lvbp-ajax-search')
						);

						$end_html .= '</div>';

						$group_end_html = $end_html;

						$last_item['html'] = $last_item['html'] . $group_end_html;
						$items[$last_item['id']] = $last_item;
					}

					//replace orginal items with this new, grouped set of items
					$this->search_results['all']['items'] = array();
					foreach( $ordered_items_group as $type=>$grouped_items ){
						foreach( $grouped_items as $item_id=>$item ){
							$this->search_results['all']['items'][$item_id] = $item;
						}
					}
				}
			}
		} else {
			if( !in_array( $args['search_subset'], $args['searchable_items'] ) )
				return;

			if( !isset($this->search_helpers[$args['search_subset']]))
				return;

			$args['per_page'] 	= 0; // get_option('posts_per_page');
			$obj = $this->search_helpers[$args['search_subset']];
			$pre_search_query 	= $obj->union_sql( $args['search_term'] ) . " ORDER BY relevance DESC, entry_date DESC ";

			if( $args['per_page']> 0 ){
				$offset = ( $args['current_page'] * $args['per_page'] ) - $args['per_page'];
				$pre_search_query .= " LIMIT {$offset}, {$args['per_page']} ";
			}

			$results = $wpdb->get_results( $pre_search_query );

			if( !empty( $results ) ){
				$obj = $this->search_helpers[$args['search_subset']];
				$this->search_results[$args['search_subset']] = array( 'total_match_count' => 0, 'items' => array() );
				foreach( $results as $item ){
					$obj->add_search_item( $item->id );
				}

				foreach( $results as $item ){
					$html = $obj->get_html( $item->id, $args['template_type'] );

					$result = array(
						'id'	=> $item->id,
						'type'	=> $args['search_subset'],
						'html'	=> $obj->get_html( $item->id, $args['template_type'] ),
						'title'	=> $obj->get_title( $item->id ),
					);

					$this->search_results[$args['search_subset']]['items'][$item->id] = $result;
				}

				$first_item = reset($this->search_results[$args['search_subset']]['items']);
				$start_html = "<div class='results-group results-group-{$args['search_subset']} results-wrap'>"
						.	"<ul id='{$args['search_subset']}-stream' class='item-list {$args['search_subset']}-list results-lists'>";

				$group_start_html = $start_html;

				$first_item['html'] = $group_start_html . $first_item['html'];
				$this->search_results[$args['search_subset']]['items'][$first_item['id']] = $first_item;

				$last_item = end($this->search_results[$args['search_subset']]['items']);
				$end_html = "</ul></div>";

				$group_end_html = $end_html;

				$last_item['html'] = $last_item['html'] . $group_end_html;
				$this->search_results[$args['search_subset']]['items'][$last_item['id']] = $last_item;
			}
		}

		if( $args['count_total'] ){
			$all_items_count = 0;
			foreach( $args['searchable_items'] as $search_type ){
				if( !isset($this->search_helpers[$search_type]))
					continue;

				$obj = $this->search_helpers[$search_type];
				$total_match_count = $obj->get_total_match_count( $this->search_args['search_term'] );
				$this->search_results[$search_type]['total_match_count'] = $total_match_count;

				$all_items_count += $total_match_count;
			}

			$this->search_results['all']['total_match_count'] = $all_items_count;
		}
	}

	public function search_page_url($value=""){
		$url = home_url( '/' );

		if(!empty($value)){
			$url = esc_url(add_query_arg( 's',urlencode($value), $url ));
		}

		return $url;
	}

	private function search_page_search_url(){

		if ( true == $this->search_args['forum_search'] ) {
			$base_url = bbp_get_search_url();
			$full_url = esc_url( add_query_arg( 'bbp_search' , urlencode( $this->search_args['search_term'] ), $base_url ) );
		} else {
			$base_url = $this->search_page_url();
			$full_url = esc_url(add_query_arg( 's', urlencode( stripslashes($this->search_args['search_term'] ) ), $base_url ));
		}

		return $full_url;
	}

	public function sanitize_args( $args='' ){
		$args = wp_parse_args( $args, array() );

		if( isset( $args['search_term'] ) ){
			$args['search_term'] = sanitize_text_field( $args['search_term'] );
		}

		if( isset( $args['search_subset'] ) ){
			$args['search_subset'] = sanitize_text_field( $args['search_subset'] );
		}

		if( isset( $args['per_page'] ) ){
			$args['per_page'] = absint( $args['per_page'] );
		}

		if( isset( $args['current_page'] ) ){
			$args['current_page'] = absint( $args['current_page'] );
		}

		return $args;
	}

}