<?php
/**
 * YITH Meta-box Class.
 *
 * @class   YIT_Metabox
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Metabox' ) ) {
	/**
	 * YIT_Metabox class.
	 *
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 * @author Leanza Francesco <leanzafrancesco@gmail.com>
	 */
	class YIT_Metabox {

		/**
		 * The ID of meta-box.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Meta-box options.
		 *
		 * @var array
		 */
		private $options = array();

		/**
		 * Meta-box tabs.
		 *
		 * @var array
		 */
		private $tabs = array();

		/**
		 * Array of instances of the class.
		 *
		 * @var array
		 */
		private static $instance = array();

		/**
		 * Retrieve a specific instance of the class
		 *
		 * @param string $id The ID of the instance.
		 *
		 * @return YIT_Metabox
		 * @author Antonino Scarfi' <antonino.scarfi@yithemes.com>
		 */
		public static function instance( $id ) {
			if ( ! isset( self::$instance[ $id ] ) ) {
				self::$instance[ $id ] = new self( $id );
			}

			return self::$instance[ $id ];
		}

		/**
		 * YIT_Metabox constructor.
		 *
		 * @param string $id the ID of the meta-box.
		 */
		public function __construct( $id = '' ) {
			$this->id = $id;
		}

		/**
		 * Set options and tabs, add actions to register metabox, scripts and save data.
		 *
		 * @param array $options The meta-box options.
		 */
		public function init( $options = array() ) {
			$this->set_options( $options );
			$this->set_tabs();

			add_action( 'add_meta_boxes', array( $this, 'register_metabox' ), 99 );
			add_action( 'save_post', array( $this, 'save_postdata' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 15 );

			add_filter( 'yit_icons_screen_ids', array( $this, 'add_screen_ids_for_icons' ) );

			add_action( 'wp_ajax_yith_plugin_fw_save_toggle_element_metabox', array( $this, 'save_toggle_element' ) );
		}

		/**
		 * Add Screen ids to include icons
		 *
		 * @param array $screen_ids The screen IDs array.
		 *
		 * @return array
		 */
		public function add_screen_ids_for_icons( $screen_ids ) {
			return array_unique( array_merge( $screen_ids, (array) $this->options['pages'] ) );
		}

		/**
		 * Enqueue script and styles in admin side.
		 */
		public function enqueue() {
			$enqueue = function_exists( 'get_current_screen' ) && get_current_screen() && in_array( get_current_screen()->id, (array) $this->options['pages'], true );
			$enqueue = apply_filters( 'yith_plugin_fw_metabox_enqueue_styles_and_scripts', $enqueue, $this );

			if ( $enqueue ) {
				wp_enqueue_media();

				wp_enqueue_style( 'woocommerce_admin_styles' );

				wp_enqueue_style( 'yith-plugin-fw-fields' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'yit-plugin-metaboxes' );
				wp_enqueue_style( 'jquery-ui-style' );

				wp_enqueue_script( 'yit-metabox' );
				wp_enqueue_script( 'yith-plugin-fw-fields' );
			}
		}

		/**
		 * Set the meta-box options.
		 *
		 * @param array $options The options.
		 */
		public function set_options( $options = array() ) {
			$this->options = $options;
		}

		/**
		 * Set the tabs.
		 */
		public function set_tabs() {
			if ( ! isset( $this->options['tabs'] ) ) {
				return;
			}
			$this->tabs = $this->options['tabs'];
			if ( isset( $this->tabs['settings']['fields'] ) ) {
				$this->tabs['settings']['fields'] = array_filter( $this->tabs['settings']['fields'] );
			}
		}


		/**
		 * Add tab to the meta-box
		 *
		 * @param array  $tab   The new tab to be added add to the meta-box.
		 * @param string $where Where to insert the tab: after or before the $refer.
		 * @param null   $refer An existent tab of the  meta-box.
		 */
		public function add_tab( $tab, $where = 'after', $refer = null ) {
			if ( ! is_null( $refer ) ) {
				$ref_pos = array_search( $refer, array_keys( $this->tabs ), true );
				if ( false !== $ref_pos ) {
					if ( 'after' === $where ) {
						$this->tabs = array_slice( $this->tabs, 0, $ref_pos + 1, true ) + $tab + array_slice( $this->tabs, $ref_pos + 1, count( $this->tabs ) - 1, true );
					} else {
						$this->tabs = array_slice( $this->tabs, 0, $ref_pos, true ) + $tab + array_slice( $this->tabs, $ref_pos, count( $this->tabs ), true );
					}
				}
			} else {
				$this->tabs = array_merge( $tab, $this->tabs );
			}

		}

		/**
		 * Remove a tab from the tabs of meta-box.
		 *
		 * @param string $tab_id The tab ID.
		 */
		public function remove_tab( $tab_id ) {
			if ( isset( $this->tabs[ $tab_id ] ) ) {
				unset( $this->tabs[ $tab_id ] );
			}
		}

		/**
		 * Add a field inside a tab of meta-box
		 *
		 * @param string $tab_id The id of the tabs where add the field.
		 * @param array  $args   The field to add.
		 * @param string $where  Where to insert the field: after or before the $refer.
		 * @param null   $refer  An existent field inside tab.
		 */
		public function add_field( $tab_id, $args, $where = 'after', $refer = null ) {
			if ( isset( $this->tabs[ $tab_id ] ) ) {

				$cf = $this->tabs[ $tab_id ]['fields'];
				if ( ! is_null( $refer ) ) {
					$ref_pos = array_search( $refer, array_keys( $cf ), true );
					if ( false !== $ref_pos ) {
						if ( 'after' === $where ) {
							$this->tabs[ $tab_id ]['fields'] = array_slice( $cf, 0, $ref_pos + 1, true ) + $args + array_slice( $cf, $ref_pos, count( $cf ) - 1, true );

						} elseif ( 'before' === $where ) {
							$this->tabs[ $tab_id ]['fields'] = array_slice( $cf, 0, $ref_pos, true ) + $args + array_slice( $cf, $ref_pos, count( $cf ), true );
						}
					}
				} else {
					if ( 'first' === $where ) {
						$this->tabs[ $tab_id ]['fields'] = $args + $cf;
					} else {
						$this->tabs[ $tab_id ]['fields'] = array_merge( $this->tabs[ $tab_id ]['fields'], $args );
					}
				}
			}
		}

		/**
		 * Remove a field from the meta-box, search inside the tabs and remove it if exists.
		 *
		 * @param string $field_id The field ID.
		 */
		public function remove_field( $field_id ) {
			foreach ( $this->tabs as $tab_name => $tab ) {
				if ( isset( $tab['fields'][ $field_id ] ) ) {
					unset( $this->tabs[ $tab_name ]['fields'][ $field_id ] );
				}
			}
		}

		/**
		 * Order tabs and fields and set id and name for each field.
		 */
		public function reorder_tabs() {
			foreach ( $this->tabs as $tab_name => $tab ) {
				foreach ( $tab['fields'] as $id_field => $field ) {
					$this->tabs[ $tab_name ]['fields'][ $id_field ]['private'] = ( isset( $field['private'] ) ) ? $field['private'] : true;
					if ( empty( $this->tabs[ $tab_name ]['fields'][ $id_field ]['id'] ) ) {
						$this->tabs[ $tab_name ]['fields'][ $id_field ]['id'] = $this->get_option_metabox_id( $id_field, $this->tabs[ $tab_name ]['fields'][ $id_field ]['private'] );
					}
					if ( empty( $this->tabs[ $tab_name ]['fields'][ $id_field ]['name'] ) ) {
						$this->tabs[ $tab_name ]['fields'][ $id_field ]['name'] = $this->get_option_metabox_name( $this->tabs[ $tab_name ]['fields'][ $id_field ]['id'] );
					}
				}
			}

		}

		/**
		 * Get the option key for a specific field
		 *
		 * @param string $field_id The field ID.
		 * @param bool   $private  If true, add an underscore before the ID.
		 *
		 * @return string
		 */
		public function get_option_metabox_id( $field_id, $private = true ) {
			if ( $private ) {
				return '_' . $field_id;
			} else {
				return $field_id;
			}
		}

		/**
		 * Get meta-box field name
		 * Return the name of the field, this name will be used as attribute name of the input field
		 *
		 * @param string $field_id The field ID.
		 * @param bool   $private  If true, add an underscore before the ID.
		 *
		 * @return string
		 */
		public function get_option_metabox_name( $field_id, $private = true ) {
			$db_name = apply_filters( 'yit_metaboxes_option_main_name', 'yit_metaboxes' );
			$return  = $db_name . '[';

			if ( ! strpos( $field_id, '[' ) ) {
				return $return . $field_id . ']';
			}
			$return .= substr( $field_id, 0, strpos( $field_id, '[' ) );
			$return .= ']';
			$return .= substr( $field_id, strpos( $field_id, '[' ) );

			return $return;
		}

		/**
		 * Register the meta-box
		 *
		 * @param string $post_type The post-type.
		 */
		public function register_metabox( $post_type ) {
			if ( in_array( $post_type, (array) $this->options['pages'], true ) ) {
				add_meta_box( $this->id, $this->options['label'], array( $this, 'show' ), $post_type, $this->options['context'], $this->options['priority'] );
			}
		}

		/**
		 * Show the meta-box
		 *
		 * @param WP_Post $post     The post.
		 * @param array   $meta_box The meta-box info array.
		 */
		public function show( $post, $meta_box ) {
			$this->reorder_tabs();

			$args = array(
				'tabs'        => $this->tabs,
				'class'       => isset( $this->options['class'] ) ? $this->options['class'] : '',
				'meta_box_id' => $this->id,
			);

			if ( isset( $meta_box, $meta_box['id'] ) ) {
				do_action( "yith_plugin_fw_metabox_before_render_{$meta_box['id']}", $post, $meta_box );
			}

			yit_plugin_get_template( YIT_CORE_PLUGIN_PATH, 'metaboxes/tab.php', $args );
		}

		/**
		 * Save the post data in the database when saving the post
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return int
		 */
		public function save_postdata( $post_id ) {
			if ( ! isset( $_POST['yit_metaboxes_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['yit_metaboxes_nonce'] ), 'metaboxes-fields-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return $post_id;
			}

			$allow_ajax = isset( $_REQUEST['yith_metabox_allow_ajax_saving'] ) && sanitize_key( wp_unslash( $_REQUEST['yith_metabox_allow_ajax_saving'] ) ) === $this->id;
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! $allow_ajax ) ) {
				return $post_id;
			}

			if ( isset( $_POST['post_type'] ) ) {
				$post_type = sanitize_key( wp_unslash( $_POST['post_type'] ) );
			} else {
				return $post_id;
			}

			if ( 'page' === $post_type ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return $post_id;
				}
			}

			if ( ! in_array( $post_type, (array) $this->options['pages'], true ) ) {
				return $post_id;
			}

			if ( isset( $_POST['yit_metaboxes'] ) ) {
				$yit_metabox_data = wp_unslash( $_POST['yit_metaboxes'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				if ( is_array( $yit_metabox_data ) ) {
					foreach ( $yit_metabox_data as $field_name => $field_value ) {
						if ( ! add_post_meta( $post_id, $field_name, $field_value, true ) ) {
							update_post_meta( $post_id, $field_name, $field_value );
						}
					}
				}
			}

			$this->sanitize_and_save_fields( $post_id );

			return $post_id;
		}

		/**
		 * Sanitize fields
		 *
		 * @param int $post_id The post ID.
		 *
		 * @since      3.2.1
		 * @deprecated since 3.4.8
		 */
		public function sanitize_fields( $post_id ) {
			$this->sanitize_and_save_fields( $post_id );
		}

		/**
		 * Sanitize and save fields of the Meta-box.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @since 3.4.8
		 */
		public function sanitize_and_save_fields( $post_id ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$this->reorder_tabs();
			$tabs_to_sanitize        = $this->tabs;
			$allow_ajax              = isset( $_REQUEST['yith_metabox_allow_ajax_saving'] ) && sanitize_key( wp_unslash( $_REQUEST['yith_metabox_allow_ajax_saving'] ) ) === $this->id;
			$ajax_partial_saving_tab = isset( $_REQUEST['yith_metabox_allow_ajax_partial_saving_tab'] ) ? sanitize_key( wp_unslash( $_REQUEST['yith_metabox_allow_ajax_partial_saving_tab'] ) ) : false;

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! $allow_ajax ) {
				return;
			} elseif ( $ajax_partial_saving_tab ) {
				if ( array_key_exists( $ajax_partial_saving_tab, $tabs_to_sanitize ) ) {
					$tabs_to_sanitize = array( $ajax_partial_saving_tab => $tabs_to_sanitize[ $ajax_partial_saving_tab ] );
				} else {
					return;
				}
			}

			foreach ( $tabs_to_sanitize as $tab ) {
				foreach ( $tab['fields'] as $field ) {
					$this->sanitize_and_save_field( $field, $post_id );
				}
			}
			// phpcs:enable
		}

		/**
		 * Sanitize and save a single field
		 *
		 * @param array $field   The field.
		 * @param int   $post_id The post ID.
		 *
		 * @since 3.4.8
		 */
		public function sanitize_and_save_field( $field, $post_id ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( in_array( $field['type'], array( 'title' ), true ) ) {
				return;
			}

			$meta_box_data = isset( $_POST['yit_metaboxes'] ) ? wp_unslash( $_POST['yit_metaboxes'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( isset( $meta_box_data[ $field['id'] ] ) ) {
				if ( in_array( $field['type'], array( 'onoff', 'checkbox' ), true ) ) {
					update_post_meta( $post_id, $field['id'], '1' );
				} elseif ( in_array( $field['type'], array( 'toggle-element' ), true ) ) {
					if ( isset( $field['elements'] ) && $field['elements'] ) {
						$elements_value = $meta_box_data[ $field['id'] ];
						if ( $elements_value ) {
							if ( isset( $elements_value['box_id'] ) ) {
								unset( $elements_value['box_id'] );
							}

							foreach ( $field['elements'] as $element ) {
								foreach ( $elements_value as $key => $element_value ) {
									if ( isset( $field['onoff_field'] ) ) {
										$elements_value[ $key ][ $field['onoff_field']['id'] ] = ! isset( $element_value[ $field['onoff_field']['id'] ] ) ? 0 : $element_value[ $field['onoff_field']['id'] ];
									}
									if ( in_array( $element['type'], array( 'onoff', 'checkbox' ), true ) ) {
										$elements_value[ $key ][ $element['id'] ] = ! isset( $element_value[ $element['id'] ] ) ? 0 : 1;
									}

									if ( ! empty( $element['yith-sanitize-callback'] ) && is_callable( $element['yith-sanitize-callback'] ) ) {
										$elements_value[ $key ][ $element['id'] ] = call_user_func( $element['yith-sanitize-callback'], $elements_value[ $key ][ $element['id'] ] );
									}
								}
							}
						}

						update_post_meta( $post_id, $field['id'], maybe_serialize( $elements_value ) );
					}
				} else {
					$value = $meta_box_data[ $field['id'] ];
					if ( ! empty( $field['yith-sanitize-callback'] ) && is_callable( $field['yith-sanitize-callback'] ) ) {
						$value = call_user_func( $field['yith-sanitize-callback'], $value );
					}
					add_post_meta( $post_id, $field['id'], $value, true ) || update_post_meta( $post_id, $field['id'], $value );
				}
			} elseif ( in_array( $field['type'], array( 'onoff', 'checkbox' ), true ) ) {
				update_post_meta( $post_id, $field['id'], '0' );
			} elseif ( in_array( $field['type'], array( 'checkbox-array' ), true ) ) {
				update_post_meta( $post_id, $field['id'], array() );
			} else {
				delete_post_meta( $post_id, $field['id'] );
			}
			// phpcs:enable
		}

		/**
		 * Remove a list of fields from the meta-box, search inside the tabs and remove it if exists
		 *
		 * @param array $fields Fields.
		 *
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function remove_fields( $fields ) {
			foreach ( $fields as $k => $field ) {
				$this->remove_field( $field );
			}
		}

		/**
		 * Save the element toggle via AJAX.
		 *
		 * @since  3.2.1
		 * @author Emanuela Castorina
		 */
		public function save_toggle_element() {
			if ( ! isset( $_REQUEST['post_ID'] ) ) {
				return;
			}

			if ( ! isset( $_REQUEST['yit_metaboxes_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['yit_metaboxes_nonce'] ), 'metaboxes-fields-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			$post_id = isset( $_REQUEST['post_ID'] ) ? absint( $_REQUEST['post_ID'] ) : false;
			if ( ! $post_id ) {
				return;
			}

			if ( isset( $_REQUEST['yit_metaboxes'], $_REQUEST['toggle_id'], $_REQUEST['metabox_tab'], $_REQUEST['yit_metaboxes'][ $_REQUEST['toggle_id'] ] ) ) {
				$meta_box_data = isset( $_REQUEST['yit_metaboxes'] ) ? wp_unslash( $_REQUEST['yit_metaboxes'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$metabox_tab   = sanitize_key( wp_unslash( $_REQUEST['metabox_tab'] ) );
				$field_id      = sanitize_key( wp_unslash( $_REQUEST['toggle_id'] ) );
				if ( strpos( $field_id, '_' ) === 0 ) {
					$field_id = substr( $field_id, 1 );
				}

				if ( is_array( $meta_box_data ) ) {
					$this->reorder_tabs();
					$tabs = $this->tabs;

					if ( isset( $tabs[ $metabox_tab ], $tabs[ $metabox_tab ]['fields'] ) && isset( $tabs[ $metabox_tab ]['fields'][ $field_id ] ) ) {
						$field = $tabs[ $metabox_tab ]['fields'][ $field_id ];
						if ( $field ) {
							$this->sanitize_and_save_field( $field, $post_id );
						}
					}
				}
			} elseif ( isset( $_REQUEST['toggle_id'] ) ) {
				$field_id = sanitize_key( wp_unslash( $_REQUEST['toggle_id'] ) );
				delete_post_meta( $post_id, $field_id );
			}
		}
	}
}

if ( ! function_exists( 'yit_metabox' ) ) {
	/**
	 * Return the meta-box instance.
	 *
	 * @param string $id The meta-box id.
	 *
	 * @return YIT_Metabox
	 */
	function yit_metabox( $id ) {
		return YIT_Metabox::instance( $id );
	}
}
