<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_WCTM_Frontend_Premium' ) ) {
	class YITH_WCTM_Frontend_Premium extends YITH_WCTM_Frontend {

		protected static $instance;


		public function __construct() {
			parent::__construct();

			remove_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs_woocommerce' ), 20 );


			//add tabs to woocommerce
			add_filter( 'woocommerce_product_tabs', array( $this, 'show_product_tabs' ), 20 );
			//customize woocommerce tabs
			add_filter( 'woocommerce_product_tabs', array( $this, 'customize_woocommerce_tab' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'include_style_and_script' ), 100 );

			$hide_in_mobile    = get_option( 'ywtm_hide_tab_mobile' );
			$hide_wc_in_mobile = get_option( 'ywtm_hide_wc_tab_mobile' );

			if ( wp_is_mobile() ) {

				if ( 'yes' == $hide_in_mobile ) {
					remove_filter( 'woocommerce_product_tabs', array( $this, 'show_product_tabs' ), 20 );
				}

				if ( 'yes' == $hide_wc_in_mobile ) {
					add_filter( 'woocommerce_product_tabs', '__return_empty_array', 10 );
				}
			}
		}

		/**
		 * @return YITH_WCTM_Frontend_Premium
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * add_global_tabs_woocommerce
		 *
		 * @param $tabs
		 *
		 * @return mixed
		 * @use woocommerce_product_tabs filter
		 * @author YITHEMES
		 * @since 1.0.0
		 */
		public function show_product_tabs( $tabs ) {
			global $product;
			$yith_tabs     = YWTM_Product_Tab()->get_current_product_tabs( $product, true );

			$prefix = 'ywtm';

			foreach ( $yith_tabs as $yith_tab ) {

				$tab_id = $yith_tab->ID;
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$tab_id = apply_filters( 'wpml_object_id', $yith_tab->ID, 'ywtm_tab', false, ICL_LANGUAGE_CODE );

				}

				$show_tab = apply_filters( 'ywtm_show_single_tab', !$this->is_empty( $tab_id ) , $tab_id, $tabs );
				if ( $show_tab ) {
					$tab_key          = $prefix . '_' . $tab_id;
					$tabs[ $tab_key ] = $this->set_single_tab( $tab_id );

					add_filter( 'woocommerce_product_' . $tab_key . '_tab_title', array(
						$this,
						'decode_html_tab'
					), 10, 2 );
				}
			}

			return $tabs;
		}

		/**
		 * check if tab is empty
		 *
		 * @param string $key
		 *
		 * @return boolean
		 * @author Salvatore Strano
		 * @since 1.2.0
		 */
		public function is_empty( $key ) {

			global $product;

			$type_content = get_post_meta( $key, '_ywtm_enable_custom_content', true );
			$type_layout  = get_post_meta( $key, '_ywtm_layout_type', true );

			if ( ! $type_content ) {
				$lang = apply_filters( 'wpml_default_language', false );
				$key  = apply_filters( 'wpml_object_id', $key, 'ywtm_tab', true, $lang );
			}


			switch ( $type_layout ) {

				case 'download' :

					if ( true == $type_content ) {
						$content = get_post_meta( $key, '_ywtm_download', true );
					} else {
						$content = $product->get_meta( $key . '_custom_list_file', true );
					}

					break;

				case 'faq' :

					if ( true == $type_content ) {
						$content = get_post_meta( $key, '_ywtm_faqs', true );
					} else {
						$content = $product->get_meta( $key . '_custom_list_faqs', true );
					}

					break;

				case 'map' :

					if ( true == $type_content ) {
						$content = get_post_meta( $key, '_ywtm_google_map_overlay_address', true );

					} else {

						$content = $product->get_meta( $key . '_custom_map', true );

						$content = isset( $content['addr'] ) ? $content['addr'] : '';
					}


					break;

				case 'contact':

					if ( true == $type_content ) {
						$content = get_post_meta( $key, '_ywtm_form_tab', true );
					} else {
						$content = $product->get_meta( $key . '_custom_form', true );
					}


					break;

				case 'gallery':

					if ( true == $type_content ) {

						$content = get_post_meta( $key, '_ywtm_gallery', true );

					} else {

						$content = $product->get_meta( $key . '_custom_gallery', true );
						$content = isset( $content['images'] ) ? $content['images'] : '';

					}

					break;

				case 'video':

					if ( true == $type_content ) {
						$content = get_post_meta( $key, '_ywtm_video', true );
						$content = isset( $content['video_info'] ) ? $content['video_info'] : '';

					} else {

						$result  = $product->get_meta( $key . '_custom_video', true );
						$content = $result ? 'video' : '';
					}


					break;

				case 'shortcode':
					if ( true == $type_content ) {

						$content = get_post_meta( $key, '_ywtm_shortcode_tab', true );
					} else {
						$content = $product->get_meta( $key . '_custom_shortcode', true );
					};
					break;

				default :

					if ( true == $type_content ) {

						$content = get_post_meta( $key, '_ywtm_text_tab', true );
					} else {

						$content = $product->get_meta( $key . '_default_editor', true );

					}


					break;
			}

			return empty( $content );
		}

		/**
		 * print icon tab
		 *
		 * @param string $title
		 * @param string $key
		 *
		 * @return string
		 * @since 1.1.0
		 * @author Salvatore Strano
		 */
		public function decode_html_tab( $title, $key ) {
			$title = html_entity_decode( $title, ENT_QUOTES );

			return $title;
		}

		/**
		 * set the single tab args
		 *
		 * @param WP_Post $yith_tab
		 *
		 * @return array
		 * @author Salvatore Strano
		 * @since 2.0.0
		 */
		public function set_single_tab( $tab_id ) {

			$tab_post     = get_post( $tab_id );
			$icon_html    = get_html_icon( $tab_id );
			$tab_title    = apply_filters( 'ywtm_get_tab_title', $icon_html . get_the_title( $tab_id ), $tab_post );
			$tab_priority = get_post_meta( $tab_id, '_ywtm_order_tab', true );
			$tab          = array(
				'title'    => $tab_title,
				'priority' => $tab_priority,
				'id'       => $tab_id,
				'callback' => array( $this, 'set_content_tabs' )
			);

			return $tab;

		}

		/**
		 * put_content_tabs
		 * Put the content at the tabs
		 *
		 * @param $key
		 * @param $tab
		 */
		public function set_content_tabs( $key, $tab ) {
			global $product;

			$key = $tab['id'];

			$type_content = get_post_meta( $key, '_ywtm_enable_custom_content', true );
			$type_layout  = get_post_meta( $key, '_ywtm_layout_type', true );
			$args         = array();

			if ( ! $type_content ) {
				$lang = apply_filters( 'wpml_default_language', false );
				$key  = apply_filters( 'wpml_object_id', $key, 'ywtm_tab', true, $lang );
			}

			switch ( $type_layout ) {

				case 'download' :

					if ( true == $type_content ) {
						$args['download'] = get_post_meta( $key, '_ywtm_download', true );
					} else {
						$args['download'] = $product->get_meta( $key . '_custom_list_file', true );
					}

					wc_get_template( 'download.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
					break;

				case 'faq' :

					if ( true == $type_content ) {
						$args['faqs'] = get_post_meta( $key, '_ywtm_faqs', true );
					} else {
						$args['faqs'] = $product->get_meta( $key . '_custom_list_faqs', true );
					}

					wc_get_template( 'faq.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;

				case 'map' :

					if ( true == $type_content ) {
						$address = get_post_meta( $key, '_ywtm_google_map_overlay_address', true );
						$width   = get_post_meta( $key, '_ywtm_google_map_width', true );
						$height  = get_post_meta( $key, '_ywtm_google_map_height', true );
						$zoom    = get_post_meta( $key, '_ywtm_google_map_overlay_zoom', true );
						$show_w  = get_post_meta( $key, '_ywtm_google_map_full_width', true );

						/*addr, heig,wid,zoom*/
						$map_setting = array(
							'addr'       => $address,
							'wid'        => $width,
							'heig'       => $height,
							'zoom'       => $zoom,
							'show_width' => $show_w
						);

						$args['map'] = $map_setting;

					} else {
						$args['map'] = $product->get_meta( $key . '_custom_map', true );

					}

					wc_get_template( 'map.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;

				case 'contact':

					if ( true == $type_content ) {
						$args['form'] = get_post_meta( $key, '_ywtm_form_tab', true );
					} else {
						$args['form'] = $product->get_meta( $key . '_custom_form', true );
					}

					wc_get_template( 'contact_form.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;

				case 'gallery':

					if ( true == $type_content ) {
						$columns        = get_post_meta( $key, '_ywtm_gallery_columns', true );
						$gallery        = get_post_meta( $key, '_ywtm_gallery', true );
						$args['images'] = array( 'columns' => $columns, 'gallery' => $gallery );
					} else {

						$result = $product->get_meta( $key . '_custom_gallery', true );
						if ( isset( $result['settings'] ) ) {
							$columns = $result['settings']['columns'];

							$gallery = '';

							foreach ( $result['images'] as $key => $image ) {
								$gallery .= $image['id'] . ',';
							}

							if ( substr( $gallery, - 1 ) == ',' ) {
								$gallery = substr( $gallery, 0, - 1 );
							}

							$args['images'] = array( 'columns' => $columns, 'gallery' => $gallery );
						}
					}

					$args['tab_id'] = $key;
					wc_get_template( 'image_gallery.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;

				case 'video':

					if ( true == $type_content ) {
						$result = get_post_meta( $key, '_ywtm_video', true );

						$columns        = $result['columns'];
						$video          = $result['video_info'];
						$args['videos'] = array( 'columns' => $columns, 'video' => $video );

					} else {

						$result = $product->get_meta( $key . '_custom_video', true );

						if ( $result ) {
							$columns        = $result['settings']['columns'];
							$video          = $result['video'];
							$args['videos'] = array( 'columns' => $columns, 'video' => $video );
						}

					}

					wc_get_template( 'video_gallery.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;

				case 'shortcode':
					if ( true == $type_content ) {

						$args['shortcode'] = get_post_meta( $key, '_ywtm_shortcode_tab', true );
					} else {
						$args['shortcode'] = $product->get_meta( $key . '_custom_shortcode', true );
					}

					wc_get_template( 'shortcode.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;

				default :

					if ( true == $type_content ) {
						$args['content'] = get_post_meta( $key, '_ywtm_text_tab', true );
					} else {

						$args['content'] = $product->get_meta( $key . '_default_editor', true );
					}

					wc_get_template( 'default.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

					break;
			}

		}

		/** show or hide default tabs
		 *
		 * @param array $tabs
		 *
		 * @return array
		 * @author Salvatore Strano
		 * @since 1.2.0
		 */
		public function customize_woocommerce_tab( $tabs ) {
			global $product;
			$product_id   = $product->get_id();
			$options_name = array(
				'description'            => 'ywtm_hide_wc_desc_tab_in_mobile',
				'reviews'                => 'ywtm_hide_wc_reviews_tab',
				'additional_information' => 'ywtm_hide_wc_addinfo_tab'
			);

			$tab_type = array_keys( ywtm_get_default_tab( $product_id ) );
			global $product;

			foreach ( $tab_type as $type ) {

				$is_hide            = $product->get_meta( '_ywtm_hide_' . $type, true );
				$is_over            = apply_filters( 'ywtm_override_wc_tab', $product->get_meta( '_ywtm_override_' . $type, true ), $type );
				$global_hide_option = 'no';

				if ( isset( $options_name[ $type ] ) ) {
					$global_hide_option = get_option( $options_name[ $type ] );
				}


				if ( $is_hide === 'yes' || 'yes' === $global_hide_option ) {
					unset( $tabs[ $type ] );
				} elseif ( $is_over === 'yes' ) {

					$title = apply_filters( 'ywtm_override_wc_tab', $product->get_meta( '_ywtm_title_tab_' . $type, true ), $type );

					$tabs[ $type ]['priority'] = $product->get_meta( '_ywtm_priority_tab_' . $type, true );
					$tabs[ $type ]['title']    = $type === 'reviews' ? str_replace( '%d', $product->get_review_count(), $title ) : $title;

					if ( $type === 'description' ) {

						$tabs[ $type ]['callback'] = array( $this, 'ywtm_custom_wc_description_content' );
					}
				}

			}

			return $tabs;
		}

		/**
		 * get custom content for description tab
		 * @author YITHEMES
		 * @since 1.1.0
		 */
		public function ywtm_custom_wc_description_content() {

			global $product;
			$content = $product->get_meta( '_ywtm_content_tab_description', true );

			$args = array(
				'content' => $content
			);
			wc_get_template( 'default.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
		}

		/**include style and script in frontend
		 * @author YITHEMES
		 * @since 1.0.0
		 * @use wp_enqueue_scripts
		 */
		public function include_style_and_script() {

			wp_register_style( 'font-retina', YWTM_ASSETS_URL . 'fonts/retinaicon-font/style.css', array(), YWTM_VERSION );

			if ( is_product() ) {

				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_style( 'yit-tabmanager-frontend', YWTM_ASSETS_URL . 'css/yith-tab-manager-frontend.css', true, YWTM_VERSION );

				wp_enqueue_style( 'yit-tabmanager-frontend' );

				$custom_css = get_option( 'ywtm_custom_style', '' );

				wp_add_inline_style( 'yit-tabmanager-frontend', $custom_css );

				if ( ! wp_style_is( 'font-awesome' ) ) {
					wp_enqueue_style( 'font-awesome' );
				}

				wp_enqueue_style( 'font-retina' );

				wp_enqueue_script( 'yit-tabmanager-script', YWTM_ASSETS_URL . 'js/frontend/tab_templates' . $suffix . '.js', array( 'jquery' ), YWTM_VERSION, true );

				$params = array(
					'admin_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'action'    => array(
						'ywtm_sendermail' => 'ywtm_sendermail'
					)
				);


				wp_localize_script( 'yit-tabmanager-script', 'ywtm_params', $params );

				wp_register_script( 'yit-tabmap-script', YWTM_ASSETS_URL . 'js/frontend/gmap3.min.js', array(
					'jquery',
					'ywtm-google-map'
				), '6.0.0', true );


				if ( ! wp_script_is( 'prettyPhoto' ) || version_compare( WC()->version, '3.0.0', '>=' ) ) {
					wp_register_script( 'prettyPhoto', YWTM_ASSETS_URL . 'js/frontend/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.6', true );
					wp_register_script( 'prettyPhoto-init', YWTM_ASSETS_URL . 'js/frontend/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array(
						'jquery',
						'prettyPhoto'
					), '3.1.6', true );
					wp_register_style( 'woocommerce_prettyPhoto_css', YWTM_ASSETS_URL . 'css/prettyPhoto/prettyPhoto.css', array(), '3.1.6' );
				}
			}


		}

	}
}
