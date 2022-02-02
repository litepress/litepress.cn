<?php
/**
 * Post Type Admin
 *
 * @class   YITH_Post_Type_Admin
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Post_Type_Admin' ) ) {
	/**
	 * YITH_Post_Type_Admin class.
	 *
	 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
	 */
	abstract class YITH_Post_Type_Admin {

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = '';

		/**
		 * The object to be shown for each row.
		 *
		 * @var object|null
		 */
		protected $object = null;

		/**
		 * The ID of the Post to be shown for each row.
		 *
		 * @var int|null
		 */
		protected $post_id = null;

		/**
		 * The single instance of the class.
		 *
		 * @var YITH_Post_Type_Admin[]
		 */
		private static $instances = array();

		/**
		 * Singleton implementation.
		 *
		 * @return YITH_Post_Type_Admin
		 */
		public static function instance() {
			$class = get_called_class();

			return ! empty( self::$instances[ $class ] ) ? self::$instances[ $class ] : self::$instances[ $class ] = new $class();
		}

		/**
		 * YITH_Admin_Post_List_Table constructor.
		 */
		protected function __construct() {
			if ( $this->post_type && $this->is_enabled() ) {
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
					// use "admin_init" for AJAX calls, since in case of AJAX, "current_screen" is not fired.
					add_action( 'admin_init', array( $this, 'init_wp_list_handlers' ) );
				} else {
					add_action( 'current_screen', array( $this, 'init_wp_list_handlers' ) );
				}

				add_action( 'edit_form_top', array( $this, 'print_back_to_wp_list_button' ) );
			}
		}

		/**
		 * Return true if it's enabled.
		 *
		 * @return bool
		 */
		protected function is_enabled() {
			return is_admin();
		}

		/**
		 * Return true if you want to use the object when you render columns. False otherwise.
		 * This is useful if you have an object representing your Custom Post Type that handles the CRUD.
		 * Note: if you use the object, you should set it by overriding the YITH_Post_Type_Admin::prepare_row_data method.
		 *
		 * @return bool
		 */
		protected function use_object() {
			return true;
		}

		/**
		 * Return true if the wp-list handlers should be loaded.
		 *
		 * @return bool
		 */
		protected function should_wp_list_handlers_be_loaded() {
			$screen_id = false;

			if ( function_exists( 'get_current_screen' ) ) {
				$screen    = get_current_screen();
				$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
			}

			if ( ! empty( $_REQUEST['screen'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$screen_id = sanitize_text_field( wp_unslash( $_REQUEST['screen'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			return ! ! $screen_id && ( 'edit-' . $this->post_type === $screen_id );
		}

		/**
		 * Initialize the WP List handlers.
		 */
		public function init_wp_list_handlers() {
			if ( $this->should_wp_list_handlers_be_loaded() ) {
				add_action( 'manage_posts_extra_tablenav', array( $this, 'maybe_render_blank_state' ) );

				add_action( 'restrict_manage_posts', array( $this, 'maybe_render_filters' ) );
				add_filter( 'request', array( $this, 'request_query' ) );

				add_filter( 'list_table_primary_column', array( $this, 'list_table_primary_column' ), 10, 2 );
				add_filter( 'post_row_actions', array( $this, 'row_actions' ), 100, 2 );

				add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
				add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', array( $this, 'define_sortable_columns' ) );
				add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'define_columns' ) );
				add_filter( 'bulk_actions-edit-' . $this->post_type, array( $this, 'define_bulk_actions' ) );

				add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
				add_filter( 'handle_bulk_actions-edit-' . $this->post_type, array( $this, 'handle_bulk_actions' ), 10, 3 );

				add_action( 'disable_months_dropdown', array( $this, 'disable_months_dropdown' ), 10, 2 );
			}
		}

		/**
		 * --------------------------------------------------------------------------
		 * Getters and definers methods
		 * --------------------------------------------------------------------------
		 *
		 * Methods for getting data from the objects. Usually you need to override them in your class.
		 */

		/**
		 * Get actions to show in the list table as action-buttons
		 *
		 * @return array
		 */
		protected function get_item_actions() {
			return array();
		}

		/**
		 * Retrieve an array of parameters for blank state.
		 *
		 * @return array{
		 * @type string $icon       The YITH icon. You can use this one (to use an YITH icon) or icon_class or icon_url.
		 * @type string $icon_class The icon class. You can use this one (to use a custom class for your icon) or icon or icon_url.
		 * @type string $icon_url   The icon URL. You can use this one (to specify an icon URL) or icon_icon or icon_class.
		 * @type string $message    The message to be shown.
		 * @type string $cta        {
		 *            The call-to-action button params.
		 * @type string $title      The call-to-action button title.
		 * @type string $icon       The call-to-action button icon.
		 * @type string $url        The call-to-action button URL.
		 * @type string $class      The call-to-action button class.
		 *                            }
		 *              }
		 */
		protected function get_blank_state_params() {
			return array();
		}

		/**
		 * Define primary column.
		 *
		 * @return string
		 */
		protected function get_primary_column() {
			return '';
		}

		/**
		 * Define hidden columns.
		 *
		 * @return array
		 */
		protected function get_default_hidden_columns() {
			return array();
		}

		/**
		 * Define which columns are sortable.
		 *
		 * @param array $columns Existing columns.
		 *
		 * @return array
		 */
		public function define_sortable_columns( $columns ) {
			return $columns;
		}

		/**
		 * Define which columns to show on this screen.
		 *
		 * @param array $columns Existing columns.
		 *
		 * @return array
		 */
		public function define_columns( $columns ) {
			return $columns;
		}

		/**
		 * Define bulk actions.
		 *
		 * @param array $actions Existing actions.
		 *
		 * @return array
		 */
		public function define_bulk_actions( $actions ) {
			return $actions;
		}

		/**
		 * Pre-fetch any data for the row each column has access to it, by loading $this->object.
		 *
		 * @param int $post_id Post ID being shown.
		 */
		protected function prepare_row_data( $post_id ) {
		}

		/**
		 * Render any custom filters and search inputs for the list table.
		 */
		protected function render_filters() {
		}

		/**
		 * Handle any custom filters.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array
		 */
		protected function query_filters( $query_vars ) {
			return $query_vars;
		}

		/**
		 * Handle bulk actions.
		 *
		 * @param string $redirect_to URL to redirect to.
		 * @param string $action      Action name.
		 * @param array  $ids         List of ids.
		 *
		 * @return string
		 */
		public function handle_bulk_actions( $redirect_to, $action, $ids ) {
			return esc_url_raw( $redirect_to );
		}

		/**
		 * Has the months dropdown enabled?
		 *
		 * @return bool
		 */
		protected function has_months_dropdown_enabled() {
			return false;
		}

		/**
		 * Return the text of the "back to WP List" button.
		 * Return empty string if you want to hide the button.
		 *
		 * @return string
		 */
		protected function get_back_to_wp_list_text() {
			$post_type_object = get_post_type_object( $this->post_type );
			$name             = ! ! $post_type_object ? get_post_type_labels( $post_type_object )->name : '';

			// translators: %s is the name of the post type (example Back to "Membership Plans").
			return ! ! $name ? sprintf( __( 'Back to "%s"', 'yith-plugin-fw' ), $name ) : __( 'Back to the list', 'yith-plugin-fw' );
		}

		/**
		 * --------------------------------------------------------------------------
		 * Utils hook handlers
		 * --------------------------------------------------------------------------
		 *
		 * Methods for handling hooks.
		 */

		/**
		 * Adjust which columns are displayed by default.
		 *
		 * @param array  $hidden Current hidden columns.
		 * @param object $screen Current screen.
		 *
		 * @return array
		 */
		public function default_hidden_columns( $hidden, $screen ) {
			if ( isset( $screen->id ) && 'edit-' . $this->post_type === $screen->id ) {
				$hidden = array_merge( $hidden, $this->get_default_hidden_columns() );
			}

			return $hidden;
		}

		/**
		 * Set list table primary column.
		 *
		 * @param string $default   Default value.
		 * @param string $screen_id Current screen ID.
		 *
		 * @return string
		 */
		public function list_table_primary_column( $default, $screen_id ) {
			if ( 'edit-' . $this->post_type === $screen_id && $this->get_primary_column() ) {
				return $this->get_primary_column();
			}

			return $default;
		}

		/**
		 * Show blank slate.
		 *
		 * @param string $which String which table-nav is being shown.
		 */
		public function maybe_render_blank_state( $which ) {
			global $post_type;

			if ( $this->get_blank_state_params() && $post_type === $this->post_type && 'bottom' === $which ) {
				$counts = (array) wp_count_posts( $post_type );
				unset( $counts['auto-draft'] );
				$count = array_sum( $counts );

				if ( 0 < $count ) {
					return;
				}

				$this->render_blank_state();

				echo '<style type="text/css">#posts-filter .wp-list-table, #posts-filter .tablenav.top, .tablenav.bottom > *, .wrap .subsubsub  { display: none; } #posts-filter .tablenav.bottom { height: auto; display: block } </style>';
			}
		}

		/**
		 * Render blank state. Extend to add content.
		 */
		protected function render_blank_state() {
			$component         = $this->get_blank_state_params();
			$component['type'] = 'list-table-blank-state';

			yith_plugin_fw_get_component( $component, true );
		}

		/**
		 * Render individual columns.
		 *
		 * @param string $column  Column ID to render.
		 * @param int    $post_id Post ID being shown.
		 */
		public function render_columns( $column, $post_id ) {
			if ( empty( $this->post_id ) || $this->post_id !== $post_id ) {
				$this->post_id = $post_id;
				$this->prepare_row_data( $post_id );
			}

			if ( $this->use_object() && ! $this->object ) {
				return;
			}

			$render_method = 'render_' . str_replace( '-', '_', $column ) . '_column';

			if ( is_callable( array( $this, $render_method ) ) ) {
				$this->{$render_method}();
			}
		}

		/**
		 * Set row actions: remove row actions, since we show actions through action-buttons.
		 *
		 * @param array   $actions Array of actions.
		 * @param WP_Post $post    Current post object.
		 *
		 * @return array
		 */
		public function row_actions( $actions, $post ) {
			if ( $this->post_type === $post->post_type ) {
				return array();
			}

			return $actions;
		}

		/**
		 * See if we should render search filters or not.
		 */
		public function maybe_render_filters() {
			global $typenow;

			if ( $this->post_type === $typenow ) {
				$this->render_filters();
			}
		}

		/**
		 * Handle any filters.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array
		 */
		public function request_query( $query_vars ) {
			global $typenow;

			if ( $this->post_type === $typenow ) {
				return $this->query_filters( $query_vars );
			}

			return $query_vars;
		}

		/**
		 * Disable Months dropdown for Bookings
		 *
		 * @param bool   $disable   Set true to disable.
		 * @param string $post_type The post type.
		 *
		 * @return bool
		 */
		public function disable_months_dropdown( $disable, $post_type ) {
			if ( $this->post_type === $post_type ) {
				$disable = ! $this->has_months_dropdown_enabled();
			}

			return $disable;
		}

		/**
		 * Print the "Back to WP List" button in Edit Post pages
		 */
		public function print_back_to_wp_list_button() {
			$screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$screen_id = $screen ? $screen->id : false;

			if ( $screen_id === $this->post_type ) {
				$url  = add_query_arg( array( 'post_type' => $this->post_type ), admin_url( 'edit.php' ) );
				$text = $this->get_back_to_wp_list_text();
				if ( $text ) {
					?>
					<div id='yith-plugin-fw__back-to-wp-list__wrapper' class='yith-plugin-fw__back-to-wp-list__wrapper'>
						<a id='yith-plugin-fw__back-to-wp-list' class='yith-plugin-fw__back-to-wp-list' href='<?php echo esc_url( $url ); ?>'><?php echo esc_html( $text ); ?></a>
					</div>
					<script type="text/javascript">
						( function () {
							var wrap   = document.querySelector( '.wrap' ),
								backTo = document.querySelector( '#yith-plugin-fw__back-to-wp-list__wrapper' );

							wrap.insertBefore( backTo, wrap.childNodes[ 0 ] );
						} )();
					</script>
					<?php
				}
			}
		}

	}
}
