<?php

namespace LitePress\GlotPress\Memory_Edit;

use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * 管理界面
 */
class Manage extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Customer', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Customers', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( '无数据', 'sp' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$id = $item['id'];

		return match ( $column_name ) {
			'id', 'source', 'target', 'priority' => esc_html( $item[ $column_name ] ),
			'action' => <<<HTML
<form method="post">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="method" value="high">
<input type="submit" class="button-primary" value="优先">
</form>
<form method="post">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="method" value="low">
<input type="submit" class="button" value="排除">
</form>
HTML,
			default => print_r( $item, true ),
		};
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'id'       => __( 'ID', 'sp' ),
			'source'   => __( '原文', 'sp' ),
			'target'   => __( '译文', 'sp' ),
			'priority' => __( '优先级', 'sp' ),
			'action'   => __( '操作', 'sp' ),
		];
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		//$per_page     = $this->get_items_per_page( 'customers_per_page', 20 );
		$per_page     = 1;
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_customers( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				self::delete_customer( absint( $_GET['customer'] ) );

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				wp_redirect( esc_url_raw( add_query_arg() ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_customer( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url
			wp_redirect( esc_url_raw( add_query_arg() ) );
			exit;
		}
	}

	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}gp_memory",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = <<<SQL
select count(*) as count
from (
         select count(m1.source) as count
         from {$wpdb->prefix}gp_memory as m1
         where source not in (
             select source
             from wp_4_gp_memory as m2
             where m2.priority = 100
         )
         and m1.priority > 0
         group by m1.source
         HAVING count > 1
     ) as t;
SQL;


		return $wpdb->get_var( $sql );
	}

	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_customers( $per_page = 20, $page_number = 1 ) {
		global $wpdb;

		$sql = <<<SQL
select GROUP_CONCAT(id SEPARATOR '<ohh>') as id,
       source,
       GROUP_CONCAT(target SEPARATOR '<ohh>') as target,
       priority,
       count(m1.source)                       as count
from {$wpdb->prefix}gp_memory as m1
where source not in (
    select source
    from {$wpdb->prefix}gp_memory as m2
    where m2.priority = 100
) and
      priority>0
group by m1.source
HAVING count > 1
order by count desc
 limit %d, %d;
SQL;

		$sql = $wpdb->prepare( $sql, ( $page_number - 1 ) * $per_page, $per_page );
		$r   = $wpdb->get_results( $sql, ARRAY_A ) ?: array();

		$data = array();
		foreach ( $r as $item ) {
			$tmp  = explode( '<ohh>', $item['target'] );
			$tmp2 = explode( '<ohh>', $item['id'] );
			foreach ( $tmp as $k => $v ) {
				$data[] = array(
					'id'       => $tmp2[ $k ],
					'source'   => $item['source'],
					'target'   => $v,
					'priority' => $item['priority'],
				);
			}
		}

		return $data;
	}

}
