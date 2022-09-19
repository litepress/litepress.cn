<?php
/**
 * Elementor Widget Skeleton Class.
 *
 * @class   YITH_Elementor_Widget
 * @package YITH\PluginFramework\Classes
 * @since   3.6.0
 */

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Elementor_Widget' ) ) {
	/**
	 * YITH_Elementor_Widget class.
	 *
	 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
	 */
	class YITH_Elementor_Widget extends Widget_Base {

		/**
		 * YITH Data.
		 *
		 * @var array
		 */
		protected $yith_data = array();

		/**
		 * Widget constructor.
		 *
		 * @param array      $data Widget data. Default is an empty array.
		 * @param array|null $args Optional. Widget default arguments. Default is null.
		 *
		 * @throws Exception If arguments are missing when initializing a full widget instance.
		 */
		public function __construct( $data = array(), $args = null ) {
			$this->yith_data = $args['yith_data'];
			$this->init_yith_data();

			parent::__construct( $data, $args );
		}

		/**
		 * Retrieve an YITH prop.
		 *
		 * @param string            $prop    The prop.
		 * @param bool|string|array $default Default value.
		 *
		 * @return mixed|string
		 */
		public function get_yith_prop( $prop, $default = null ) {
			if ( is_null( $default ) ) {
				$defaults = $this->get_yith_data_defaults();
				$default  = array_key_exists( $prop, $defaults ) ? $defaults[ $prop ] : false;
			}

			return array_key_exists( $prop, $this->yith_data ) ? $this->yith_data[ $prop ] : $default;
		}

		/**
		 * Get element name.
		 *
		 * @return string
		 */
		public function get_name() {
			return $this->get_yith_prop( 'name', '' );
		}

		/**
		 * Get the element title.
		 *
		 * @return string
		 */
		public function get_title() {
			return $this->get_yith_prop( 'title', '' );
		}

		/**
		 * Get the element icon.
		 *
		 * @return string
		 */
		public function get_icon() {
			return $this->get_yith_prop( 'icon', 'yith-icon yith-icon-yith' );
		}

		/**
		 * Get widget categories.
		 *
		 * @return array Widget categories.
		 */
		public function get_categories() {
			return array( 'yith' ) + (array) $this->get_yith_prop( 'categories', array() );
		}

		/**
		 * Register the widget controls.
		 */
		public function register_controls() {
			$options     = $this->get_yith_prop( 'options' );
			$description = $this->get_yith_prop( 'description' );

			if ( $options ) {
				$this->start_controls_section(
					'options',
					array(
						'label' => $this->get_yith_prop( 'section_title' ),
						'tab'   => Controls_Manager::TAB_CONTENT,
					)
				);

				if ( $description ) {
					$this->add_control(
						'description',
						array(
							'type'            => Controls_Manager::RAW_HTML,
							'raw'             => $description,
							'content_classes' => 'yith-plugin-fw-elementor-widget-description',
						)
					);
				}

				foreach ( $options as $option ) {
					if ( ! isset( $option['type'] ) ) {
						continue;
					}

					$this->add_control( $option['yith_key'], $option );
				}

				$this->end_controls_section();
			} elseif ( $description ) {
				$this->start_controls_section(
					'options',
					array(
						'label' => $this->get_title(),
						'tab'   => Controls_Manager::TAB_CONTENT,
					)
				);
				$this->add_control(
					'description',
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => $description,
						'content_classes' => 'yith-plugin-fw-elementor-widget-description',
					)
				);

				$this->end_controls_section();
			}
		}

		/**
		 * Render the content of the widget
		 */
		protected function render() {
			$settings         = $this->get_settings_for_display();
			$option_values    = $this->get_yith_option_values();
			$options          = $this->get_yith_prop( 'options' );
			$shortcode_name   = $this->get_yith_prop( 'shortcode_name' );
			$do_shortcode     = ! ! $this->get_yith_prop( 'do_shortcode' );
			$render_cb        = $this->get_yith_prop( 'render_cb' );
			$editor_render_cb = $this->get_yith_prop( 'editor_render_cb' );
			$empty_message    = $this->get_yith_prop( 'empty_message', '' );

			if ( Plugin::$instance->editor->is_edit_mode() && $editor_render_cb && is_callable( $editor_render_cb ) ) {
				echo call_user_func( $editor_render_cb, $option_values ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} elseif ( $render_cb && is_callable( $render_cb ) ) {
				echo call_user_func( $render_cb, $option_values ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				$shortcode_attrs = array();

				foreach ( $options as $option ) {
					$display_key = $option['yith_display_key'];
					$value       = isset( $option_values[ $display_key ] ) ? $option_values[ $display_key ] : null;
					$show        = $this->is_control_visible( $option, $settings );

					if ( $show && ! is_null( $value ) && '' !== $value ) {
						$value             = is_array( $value ) ? implode( ',', $value ) : $value;
						$shortcode_value   = ! empty( $option['remove_quotes'] ) ? $value : ( '"' . $value . '"' );
						$shortcode_attrs[] = $display_key . '=' . $shortcode_value;
					}
				}

				$shortcode_attrs = implode( ' ', $shortcode_attrs );
				$shortcode       = "[{$shortcode_name} {$shortcode_attrs}]";

				if ( Plugin::$instance->editor->is_edit_mode() ) {
					$html = esc_html( $shortcode );
					if ( $do_shortcode ) {
						do_action( 'yith_plugin_fw_elementor_editor_before_do_shortcode', $shortcode, $this );
						$html = do_shortcode( apply_filters( 'yith_plugin_fw_elementor_editor_shortcode', $shortcode, $this ) );
						do_action( 'yith_plugin_fw_elementor_editor_after_do_shortcode', $shortcode, $this );
					}
					$type         = $do_shortcode ? 'html' : 'shortcode';
					$html_to_show = $html;
					$message      = '';

					if ( $do_shortcode && $empty_message && ! $html ) {
						$type         = 'empty-html';
						$html_to_show = esc_html( $shortcode );
						$message      = $empty_message;
					}

					$show_title   = in_array( $type, array( 'shortcode', 'empty-html' ), true );
					$show_content = 'empty-html' !== $type;
					?>
					<div class="yith-plugin-fw-elementor-shortcode-widget yith-plugin-fw-elementor-shortcode-widget--<?php echo esc_attr( $type ); ?>">
						<?php if ( $show_title ) : ?>
							<div class="yith-plugin-fw-elementor-shortcode-widget__title">
								<?php echo esc_html( $this->get_title() ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $message ) : ?>
							<div class="yith-plugin-fw-elementor-shortcode-widget__message">
								<?php echo wp_kses_post( $message ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $show_content ) : ?>
							<div class="yith-plugin-fw-elementor-shortcode-widget__content">
								<?php echo $html_to_show; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>
					</div>
					<?php
				} else {
					do_action( 'yith_plugin_fw_elementor_before_do_shortcode', $shortcode, $this );
					echo do_shortcode( apply_filters( 'yith_plugin_fw_elementor_shortcode', $shortcode, $this ) );
					do_action( 'yith_plugin_fw_elementor_after_do_shortcode', $shortcode, $this );
				}
			}
		}

		/**
		 * Retrieve the YITH Data defaults.
		 *
		 * @return array
		 */
		public function get_yith_data_defaults() {
			return array(
				'map_from_gutenberg' => false,
				'shortcode_name'     => '',
				'do_shortcode'       => true,
				'render_cb'          => false,
				'editor_render_cb'   => false,
				'options'            => array(),
				'section_title'      => sprintf(
				// translators: %s it the Elementor Widget title.
					_x( '%s - Options', 'Elementor Widget - section title', 'yith-plugin-fw' ),
					$this->get_title()
				),
			);
		}

		/**
		 * Retrieve YITH options with their values.
		 */
		protected function get_yith_option_values() {
			$settings           = $this->get_settings_for_display();
			$map_from_gutenberg = $this->get_yith_prop( 'map_from_gutenberg' );
			$options            = $this->get_yith_prop( 'options' );
			$option_values      = array();

			foreach ( $options as $option ) {
				$value       = isset( $option['default'] ) ? $option['default'] : null;
				$key         = $option['yith_key'];
				$display_key = $option['yith_display_key'];

				if ( isset( $settings[ $key ] ) ) {
					$value = $settings[ $key ];
				}

				if ( isset( $option['type'] ) && Controls_Manager::SWITCHER === $option['type'] ) {
					$yes_no_values = isset( $option['yes_no_values'] ) ? $option['yes_no_values'] : ! $map_from_gutenberg;
					if ( false === $yes_no_values ) {
						$yes_no_values = array( 'true', 'false' );
					} elseif ( ! is_array( $yes_no_values ) || 2 !== count( $yes_no_values ) ) {
						$yes_no_values = array( 'yes', 'no' );
					}

					$value = 'yes' === $value ? $yes_no_values[0] : $yes_no_values[1];
				}

				$option_values[ $display_key ] = $value;
			}

			return $option_values;
		}

		/**
		 * YITH Data Initialization.
		 */
		protected function init_yith_data() {
			$data = wp_parse_args( $this->yith_data, $this->get_yith_data_defaults() );

			if ( ! ! $data['map_from_gutenberg'] ) {
				$data = $this->override_elementor_specific_data( $data );

				if ( ! $data['options'] && ! empty( $data['attributes'] ) && is_array( $data['attributes'] ) ) {
					$data['options'] = $data['attributes'];
					unset( $data['attributes'] );
				}

				if ( $data['options'] ) {
					$data['options'] = array_map( array( $this, 'map_option_from_gutenberg' ), $data['options'] );
				}
			}

			$data['options'] = $this->validate_yith_options( $data['options'] );

			$this->yith_data = $data;
		}

		/**
		 * Validate field types
		 *
		 * @param array $options The options.
		 *
		 * @return array|false The validate option array; false if the type is not set.
		 */
		protected function validate_yith_options( $options ) {
			foreach ( $options as $key => &$option ) {
				if ( ! isset( $option['type'] ) ) {
					unset( $options[ $key ] );
					continue;
				}

				// Let's fix the option type.
				if ( in_array( $option['type'], array( 'toggle', 'onoff', 'checkbox' ), true ) ) {
					$option['type'] = Controls_Manager::SWITCHER;

					if ( isset( $option['default'] ) && is_bool( $option['default'] ) ) {
						$option['default'] = yith_plugin_fw_is_true( $option['default'] ) ? 'yes' : 'no';
					}
				} elseif ( in_array( $option['type'], array( 'radio' ), true ) ) {
					$option['type'] = Controls_Manager::SELECT;
				} elseif ( in_array( $option['type'], array( 'color', 'colorpicker', 'color-palette' ), true ) ) {
					$option['type'] = Controls_Manager::COLOR;
				} elseif ( in_array( $option['type'], array( 'select' ), true ) && ! empty( $option['multiple'] ) ) {
					$option['type'] = Controls_Manager::SELECT2;
				}

				// Set the key, used to store the option, and the display_key, used in the render method.
				$option['yith_key'] = $this->maybe_prefix_yith_key( $key );
				if ( ! isset( $option['yith_display_key'] ) ) {
					$option['yith_display_key'] = $key;
				}

				// Auto-set the block_label to display label in a separate line.
				if ( ! isset( $option['label_block'] ) ) {
					$option['label_block'] = true;
				}
			}

			return $options;
		}

		/**
		 * Override Elementor specific data if exists.
		 * This allows setting/overriding specific values for Elementor.
		 *
		 * @param array $data The data array.
		 *
		 * @return array
		 */
		protected function override_elementor_specific_data( $data ) {
			$elementor_data = array_filter(
				$data,
				function ( $key ) {
					return 'elementor_' === substr( $key, 0, 10 );
				},
				ARRAY_FILTER_USE_KEY
			);

			$elementor_data = array_combine(
				array_map(
					function ( $key ) {
						return substr( $key, 10 );
					},
					array_keys( $elementor_data )
				),
				array_values( $elementor_data )
			);

			$data = array_merge( $data, $elementor_data );

			return $data;
		}

		/**
		 * Map an option from Gutenberg
		 *
		 * @param array $option The option array.
		 *
		 * @return array
		 */
		protected function map_option_from_gutenberg( $option ) {
			$option = $this->override_elementor_specific_data( $option );
			$type   = isset( $option['type'] ) ? $option['type'] : false;

			if ( ! empty( $option['deps'] ) && ! isset( $option['condition'] ) ) {
				if ( isset( $option['deps']['id'], $option['deps']['value'] ) ) {
					$deps = array(
						array(
							'id'    => $option['deps']['id'],
							'value' => $option['deps']['value'],
						),
					);
				} else {
					$deps = $option['deps'];
				}

				$option['condition'] = array();

				foreach ( $deps as $dep ) {
					if ( isset( $dep['id'], $dep['value'] ) ) {
						$dep_value = $dep['value'];
						$dep_id    = $dep['id'];
						if ( in_array( $type, array( 'toggle', 'onoff', 'checkbox' ), true ) ) {
							$dep_value = yith_plugin_fw_is_true( $dep_value ) ? 'yes' : 'no';
						}
						$option['condition'][ $dep_id ] = $dep_value;
					}
				}

				unset( $option['deps'] );
			}

			switch ( $type ) {
				case 'color':
				case 'colorpicker':
					if ( ! isset( $option['alpha'] ) ) {
						$option['alpha'] = isset( $option['disableAlpha'] ) ? ! $option['disableAlpha'] : false;
					}

					break;
				case 'color-palette':
					$option['alpha'] = false;
					break;
			}

			if ( isset( $option['help'] ) && ! isset( $option['description'] ) ) {
				$option['description'] = $option['help'];
				unset( $option['help'] );
			}

			return $option;
		}

		/**
		 * Return the YITH key prefix.
		 *
		 * @return string
		 */
		public function get_yith_key_prefix() {
			return '_yith_';
		}

		/**
		 * Add YITH prefix to a specific key.
		 *
		 * @param string $key The key to be prefixed.
		 *
		 * @return string
		 */
		public function maybe_prefix_yith_key( $key ) {
			$reserved_keys = array( 'id', 'elType', 'settings', 'elements', 'isInner' );
			if ( in_array( $key, $reserved_keys, true ) ) {
				$prefix = $this->get_yith_key_prefix();

				$key = substr( $key, 0, strlen( $prefix ) ) === $prefix ? $key : ( $prefix . $key );
			}

			return $key;
		}
	}
}
