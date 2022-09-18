<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that manage the post type
 *
 * @package YITH WooCommerce Tab Manager\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_WCTM_Post_Type' ) ) {
	/**
	 * The class for tab manager post type
	 */
	class YITH_WCTM_Post_Type {
		/**
		 * The unique instance of the class
		 *
		 * @var YITH_WCTM_Post_Type
		 */
		protected static $instance;
		/**
		 * Post type name
		 *
		 * @var string
		 */
		public $post_type_name = 'ywtm_tab';

		/**
		 * The construct of the class
		 *
		 * @author YITH
		 * @since 2.0.0
		 */
		public function __construct() {

			// Add action register post type.
			add_action( 'init', array( $this, 'tabs_post_type' ), 10 );
			add_action( 'edit_form_top', array( $this, 'add_return_to_list_button' ) );

			// Custom Tab Message.
			add_filter( 'post_updated_messages', array( $this, 'custom_tab_messages' ) );

		}

		/**
		 * Returns single instance of the class
		 *
		 * @author YITH
		 * @return YITH_WCTM_Post_Type
		 * @since 2.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register a Global Tab post type
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function tabs_post_type() {
			$args = apply_filters(
				'yith_wctm_post_type',
				array(
					'label'               => __( 'ywtm_tab', 'yith-woocommerce-tab-manager' ),
					'description'         => __( 'Yith Tab Manager Description', 'yith-woocommerce-tab-manager' ),
					'labels'              => $this->get_tab_taxonomy_label(),
					'supports'            => array( 'title' ),
					'hierarchical'        => true,
					'public'              => false,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'can_export'          => false,
					'has_archive'         => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'capability_type'     => 'post',
				)
			);

			register_post_type( $this->post_type_name, $args );

		}

		/**
		 * Get the tab taxonomy label
		 *
		 * @param string $arg The string to return. Defaul empty. If is empty return all taxonomy labels.
		 *
		 * @author YITH
		 * @since  1.0.0
		 *
		 * @return string|array taxonomy label
		 * @fire yith_tab_manager_taxonomy_label hooks
		 */
		protected function get_tab_taxonomy_label( $arg = '' ) {

			$label = apply_filters(
				'yith_tab_manager_taxonomy_label',
				array(
					'name'               => _x( 'YITH WooCommerce Tab Manager', 'Post Type General Name', 'yith-woocommerce-tab-manager' ),
					'singular_name'      => _x( 'Tab', 'Post Type Singular Name', 'yith-woocommerce-tab-manager' ),
					'menu_name'          => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
					'parent_item_colon'  => __( 'Parent Item:', 'yith-woocommerce-tab-manager' ),
					'all_items'          => __( 'All Tabs', 'yith-woocommerce-tab-manager' ),
					'view_item'          => __( 'View Tabs', 'yith-woocommerce-tab-manager' ),
					'add_new_item'       => __( 'Add New Tab', 'yith-woocommerce-tab-manager' ),
					'add_new'            => __( 'Add New Tab', 'yith-woocommerce-tab-manager' ),
					'edit_item'          => __( 'Edit Tab', 'yith-woocommerce-tab-manager' ),
					'update_item'        => __( 'Update Tab', 'yith-woocommerce-tab-manager' ),
					'search_items'       => __( 'Search Tab', 'yith-woocommerce-tab-manager' ),
					'not_found'          => __( 'Not found', 'yith-woocommerce-tab-manager' ),
					'not_found_in_trash' => __( 'Not found in Trash', 'yith-woocommerce-tab-manager' ),
				)
			);
			return ! empty( $arg ) ? $label[ $arg ] : $label;
		}

		/**
		 * Customize the messages for Tabs
		 *
		 * @param array $messages The post type message.
		 * @author YITH
		 * @since 1.0.0
		 * @return array
		 * @fire post_updated_messages filter
		 */
		public function custom_tab_messages( $messages ) {

			$singular_name                     = $this->get_tab_taxonomy_label( 'singular_name' );
			$messages[ $this->post_type_name ] = array(

				0  => '',
				/* translators: %s is the name of post type */
				1  => sprintf( __( '%s updated', 'yith-woocommerce-tab-manager' ), $singular_name ),
				2  => __( 'Custom field updated', 'yith-woocommerce-tab-manager' ),
				3  => __( 'Custom field deleted', 'yith-woocommerce-tab-manager' ),
				/* translators: %s is the name of post type */
				4  => sprintf( __( '%s updated', 'yith-woocommerce-tab-manager' ), $singular_name ),
				/* translators: %s is the revision number */
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Tab restored to version %s', 'yith-woocommerce-tab-manager' ), wp_post_revision_title( (int) wp_unslash( $_GET['revision'] ), false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				/* translators: %s is the name of post type */
				6  => sprintf( __( '%s published', 'yith-woocommerce-tab-manager' ), $singular_name ),
				/* translators: %s is the name of post type */
				7  => sprintf( __( '%s saved', 'yith-woocommerce-tab-manager' ), $singular_name ),
				/* translators: %s is the name of post type */
				8  => sprintf( __( '%s submitted', 'yith-woocommerce-tab-manager' ), $singular_name ),
				9  => $singular_name,
				/* translators: %s is the name of post type */
				10 => sprintf( __( '%s draft updated', 'yith-woocommerce-tab-manager' ), $singular_name ),
			);

			return $messages;
		}




		/**
		 * Edit Columns Table
		 *
		 * @param array $columns The table columns.
		 * @author YITH
		 * @since 1.0.0
		 * @return array
		 */
		public function edit_columns( $columns ) {

			$columns = apply_filters(
				'yith_add_column_tab',
				array(
					'cb'           => '<input type="checkbox" />',
					'title'        => __( 'Title', 'yith-woocommerce-tab-manager' ),
					'is_show'      => __( 'Is Visible', 'yith-woocommerce-tab-manager' ),
					'tab_position' => __( 'Tab Position', 'yith-woocommerce-tab-manager' ),
					'date'         => __( 'Date', 'yith-woocommerce-tab-manager' ),
				)
			);

			return $columns;
		}

		/**
		 * Print the content columns
		 *
		 * @param string $column The column name.
		 * @param int    $post_id The post id.
		 * @author YITH
		 * @since 1.0.0
		 */
		public function custom_columns( $column, $post_id ) {
			switch ( $column ) {
				case 'is_show':
					$show = get_post_meta( $post_id, '_ywtm_show_tab', true );

					if ( $show ) {
						echo '<mark class="show tips" data-tip="yes">yes</mark>';
					} else {
						echo '<mark class="hide tips" data-tip="no">no</mark>';
					}
					break;

				case 'tab_position':
					$tab_position = get_post_meta( $post_id, '_ywtm_order_tab', true );
					echo esc_attr( $tab_position );
					break;
			}

			do_action( 'ywtm_show_custom_columns', $column, $post_id );

		}
		/**
		 * Get all custom tab.
		 *
		 * @author YITH
		 * @since 1.0.0
		 * @return array
		 */
		public function get_tabs() {

			/*Custom query for gets all post 'Tab'*/

			$args = array(
				'post_type'        => 'ywtm_tab',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'suppress_filters' => false,
				'meta_query'       => array(
					array(
						'key'   => '_ywtm_show_tab',
						'value' => true,
					),
				),

			);

			if ( function_exists( 'pll_current_language' ) ) {
				$args['lang'] = pll_current_language();
			}
			$q_tabs = get_posts( $args );
			$tabs   = array();

			foreach ( $q_tabs as $tab ) {

				$attr_tab                                      = array();
					$attr_tab['title']                         = $tab->post_title;
					$attr_tab['priority']                      = get_post_meta( $tab->ID, '_ywtm_order_tab', true );
					$attr_tab['id']                            = $tab->ID;
					$tabs[ $tab->post_title . '_' . $tab->ID ] = $attr_tab;

			}
			return $tabs;

		}

		/**
		 * Show a button to go back in the list.
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function add_return_to_list_button() {

			global $post;

			if ( isset( $post ) && 'ywtm_tab' === $post->post_type ) {
				$admin_url = admin_url( 'admin.php' );
				$params    = array(
					'page' => 'yith_wc_tab_manager_panel',
					'tab'  => 'settings',
				);

				$list_url = apply_filters( 'ywctm_back_link', esc_url( add_query_arg( $params, $admin_url ) ) );
				$button   = sprintf(
					'<a href="%1$s" title="%2$s" class="ywctm_back_to">%2$s</a>',
					$list_url,
					__(
						'Back to Tabs',
						'yith-woocommerce-tab-manager'
					)
				);
				echo $button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

	}

}
/**
 * Return the unique access of this class
 *
 * @author YITH
 * @since 1.0.0
 * @return YITH_WCTM_Post_Type|YITH_WCTM_Post_Type_Premium
 */
function YITH_WCTM_Post_Type() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

	if ( defined( 'YWTM_PREMIUM' ) && class_exists( 'YITH_WCTM_Post_Type_Premium' ) ) {
		return YITH_WCTM_Post_Type_Premium::get_instance();
	} else {
		return YITH_WCTM_Post_Type::get_instance();
	}

}
