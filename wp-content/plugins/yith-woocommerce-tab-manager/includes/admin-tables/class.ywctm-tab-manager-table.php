<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class extend the WP_List_Table for tab post post_type
 *
 * @since 1.0.0
 * @package YITH WooCommerce Tab Manager\Admin
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Table' ) ) {

	/**
	 * The Tab manager list table
	 */
	class YITH_Tab_Manager_Table extends WP_List_Table {

		/**
		 * Detected is a post is in Trash
		 *
		 * @var bool
		 */
		private $is_trash;

		/**
		 * The post type name
		 *
		 * @var string
		 */
		private $post_type;

		/**
		 * The construct
		 *
		 * @param array $args the args of the class.
		 * @author YITH
		 * @since 1.0.0
		 */
		public function __construct( $args = array() ) {

			parent::__construct(
				array(
					'singular' => 'yith-tab',
					// singular name of the listed records.
					'plural'   => 'yith-tabs',
					// plural name of the listed records.
					'ajax'     => false,
				// does this table support ajax?.
				)
			);
		}

		/**
		 * Add the column of table
		 *
		 * @return array
		 * @since 1.2.17
		 * @author YITH
		 */
		public function get_columns() {
			$columns = array(
				'cb'           => '<input type="checkbox"/>',
				'title'        => _x( 'Title', 'column name', 'yith-woocommerce-tab-manager' ),
				'is_show'      => _x( 'Is Visible', 'column name', 'yith-woocommerce-tab-manager' ),
				'tab_position' => _x( 'Tab Position', 'column name', 'yith-woocommerce-tab-manager' ),
				'post_date'    => __( 'Date' ),

			);

			return $columns;
		}

		/**
		 * Set the sortable columns in the table
		 *
		 * @return array
		 * @since 1.2.17
		 * @author YITH
		 */
		protected function get_sortable_columns() {
			return array(
				'title'     => array( 'post_title', false ),
				'post_date' => array( 'post_date', true ),
			);
		}

		/**
		 * Show the available views
		 *
		 * @return array
		 * @since 1.2.17
		 * @author YITH
		 */
		protected function get_views() {
			$views = array(
				'all'     => __( 'All', 'yith-woocommerce-tab-manager' ),
				'publish' => __( 'Published', 'yith-woocommerce-tab-manager' ),
				'mine'    => __( 'Mine', 'yith-woocommerce-tab-manager' ),
				'trash'   => __( 'Trash', 'yith-woocommerce-tab-manager' ),
				'draft'   => __( 'Draft', 'yith-woocommerce-tab-manager' ),
			);

			$current_view = $this->get_current_view();

			foreach ( $views as $view_id => $view ) {

				$query_args = array(
					'posts_per_page'  => - 1,
					'post_type'       => 'ywtm_tab',
					'post_status'     => 'publish',
					'suppress_filter' => false,
				);
				$status     = 'status';
				$id         = $view_id;

				if ( 'mine' === $view_id ) {
					$query_args['author'] = get_current_user_id();
					$status               = 'author';
					$id                   = get_current_user_id();

				} elseif ( 'all' !== $view_id ) {
					$query_args['post_status'] = $view_id;
				}

				$href              = esc_url( add_query_arg( $status, $id ) );
				$total_items       = count( get_posts( $query_args ) );
				$class             = $view_id === $current_view ? 'current' : '';
				$views[ $view_id ] = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $total_items );
			}

			return $views;
		}

		/**
		 * Return current view
		 *
		 * @return string
		 * @since 1.2.17
		 * @author YITH
		 */
		public function get_current_view() {

			return empty( $_GET['status'] ) ? 'all' : wp_unslash( $_GET['status'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		/**
		 * Return the bulk actions
		 *
		 * @return array
		 * @since 1.2.7
		 * @author YITH
		 */
		protected function get_bulk_actions() {
			$actions       = array();
			$post_type_obj = get_post_type_object( 'ywtm_tab' );

			if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
				if ( $this->is_trash ) {
					$actions['untrash'] = __( 'Restore' );
				} else {
					$actions['edit'] = __( 'Edit' );
				}
			}

			if ( current_user_can( $post_type_obj->cap->delete_posts ) ) {
				if ( $this->is_trash || ! EMPTY_TRASH_DAYS ) {
					$actions['delete'] = __( 'Delete Permanently' );
				} else {
					$actions['trash'] = __( 'Move to Trash' );
				}
			}

			return $actions;
		}

		/**
		 * The method that return all items
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function prepare_items() {
			$per_page              = 15;
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$current_page = $this->get_pagenum();

			$query_args = array(
				'posts_per_page'  => $per_page,
				'paged'           => $current_page,
				'suppress_filter' => false,
				'post_type'       => 'ywtm_tab',
			);

			$status = isset( $_GET['status'] ) && 'all' !== $_GET['status'] ? wp_unslash( $_GET['status'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$author = isset( $_GET['author'] ) && 'mine' === $_GET['author'] ? wp_unslash( $_GET['author'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( $status ) {
				$query_args['post_status'] = $status;
			}

			if ( $author ) {
				$query_args['author'] = $author;
			}

			$orderby        = isset( $_GET['orderby'] ) ? wp_unslash( $_GET['orderby'] ) : 'post_date'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$order          = isset( $_GET['order'] ) ? wp_unslash( $_GET['order'] ) : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$this->is_trash = $status && 'trash' === $status;

			$query_args['orderby'] = $orderby;
			$query_args['order']   = $order;
			$this->items           = get_posts( $query_args );

			$count_posts = wp_count_posts( 'ywtm_tab' );
			$total_items = $count_posts->publish;
			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,                  // WE have to calculate the total number of items.
					'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
					'total_pages' => ceil( $total_items / $per_page ),   // WE have to calculate the total number of pages.
				)
			);
		}

		/**
		 * Show the checkbox column
		 *
		 * @param WP_Post $item The item.
		 *
		 * @author YITH
		 * @since 1.2.7
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="ywctm_ids[]" value="%s" />',
				$item->ID
			);
		}

		/**
		 * Show the columns
		 *
		 * @param WP_Post $item The item.
		 * @param string  $column_name the column name.
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'title':
					$action_edit_query_args = array(
						'action' => 'edit',
						'post'   => $item->ID,
					);

					$action_edit_url = esc_url( add_query_arg( $action_edit_query_args, admin_url( 'post.php' ) ) );

					$delete = ( isset( $_GET['status'] ) && 'trash' === wp_unslash( $_GET['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					$actions = array();

					if ( $delete ) {

						$post_type        = get_post_type( $item );
						$post_type_object = get_post_type_object( $post_type );

						$actions['untrash'] = "<a title='" . esc_attr__( 'Restore this item from Trash', 'yith-woocommerce-tab-manager' ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $item->ID ) ), 'untrash-post_' . $item->ID ) . "'>" . __( 'Restore', 'yith-woocommerce-tab-manager' ) . '</a>';

						$actions['delete'] = '<a href="' . esc_url(
							get_delete_post_link(
								$item,
								'',
								true
							)
						) . '" class="submitdelete">' . __( 'Delete permanently', 'yith-woocommerce-tab-manager' ) . '</a>';
					} else {
						$actions['edit']  = '<a href="' . $action_edit_url . '">' . __( 'Edit', 'yith-woocommerce-tab-manager' ) . '</a>';
						$actions['trash'] = '<a href="' . esc_url( get_delete_post_link( $item, '', false ) ) . '" class="submitdelete">' . __( 'Trash', 'yith-woocommerce-tab-manager' ) . '</a>';
					}

					$post_title = get_the_title( $item );
					echo sprintf( '<strong><a class="tips" target="_blank" href="%s" data-tip="%s">#%d %s </a></strong> %s', $action_edit_url, __( 'Edit', 'yith-woocommerce-tab-manager' ), $item->ID, $post_title, $this->row_actions( $actions ) ); //phpcs:ignore WordPress.Security.EscapeOutput
					break;
				case 'is_show':
					$show = get_post_meta( $item->ID, '_ywtm_show_tab', true );

					if ( $show ) {
						echo '<mark class="show tips" data-tip="yes">yes</mark>';
					} else {
						echo '<mark class="hide tips" data-tip="no">no</mark>';
					}
					break;

				case 'tab_position':
					$tab_position = get_post_meta( $item->ID, '_ywtm_order_tab', true );
					echo esc_attr( $tab_position );
					break;

			}
		}

		/**
		 * Handles the post date column output.
		 *
		 * @param WP_Post $post The current WP_Post object.
		 *
		 * @global string $mode List table view mode.
		 *
		 * @since 4.3.0
		 */
		public function column_post_date( $post ) {
			global $mode;

			if ( '0000-00-00 00:00:00' === $post->post_date ) {
				$t_time    = __( 'Unpublished' );
				$h_time    = __( 'Unpublished' );
				$time_diff = 0;
			} else {
				$t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
				$m_time = $post->post_date;
				$time   = get_post_time( 'G', true, $post );

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
					/* translators: %s is the time */
					$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
				} else {
					$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
				}
			}

			if ( 'publish' === $post->post_status ) {
				$status = __( 'Published' );
			} elseif ( 'future' === $post->post_status ) {
				if ( $time_diff > 0 ) {
					$status = '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
				} else {
					$status = __( 'Scheduled' );
				}
			} else {
				$status = __( 'Last Modified' );
			}

			/**
			 * Filters the status text of the post.
			 *
			 * @param string $status The status text.
			 * @param WP_Post $post Post object.
			 * @param string $column_name The column name.
			 * @param string $mode The list display mode ('excerpt' or 'list').
			 *
			 * @since 4.8.0
			 */
			$status = apply_filters( 'post_date_column_status', $status, $post, 'date', $mode );

			if ( $status ) {
				echo $status . '<br />'; //phpcs:ignore WordPress.Security.EscapeOutput
			}

			if ( 'excerpt' === $mode ) {
				/**
				 * Filters the published time of the post.
				 *
				 * If `$mode` equals 'excerpt', the published time and date are both displayed.
				 * If `$mode` equals 'list' (default), the publish date is displayed, with the
				 * time and date together available as an abbreviation definition.
				 *
				 * @param string $t_time The published time.
				 * @param WP_Post $post Post object.
				 * @param string $column_name The column name.
				 * @param string $mode The list display mode ('excerpt' or 'list').
				 *
				 * @since 2.5.1
				 */
				echo apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode ); //phpcs:ignore WordPress.Security.EscapeOutput
			} else {

				/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
				echo '<abbr title="' . esc_attr( $t_time ) . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) . '</abbr>'; //phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}
}
