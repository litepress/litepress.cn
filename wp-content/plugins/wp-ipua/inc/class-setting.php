<?php

namespace WePublish\IPUA\Inc;

/**
 * 设置API
 * @author 耗子
 * @package WePublish\IPUA\Inc\Setting
 */

if ( ! class_exists( 'Setting' ) ):
	class Setting {

		/**
		 * 设置子页面数组
		 *
		 * @var array
		 */
		protected array $settings_sections = array();

		/**
		 * 设置字段数组
		 *
		 * @var array
		 */
		protected array $settings_fields = array();

		public function __construct() {
			// 判断站点网络是否开启
			if ( ! defined( 'WPSA_MULTIPLE_NETWORK' ) ) {
				define( 'WPSA_MULTIPLE_NETWORK', is_multisite() );
			}

			if ( WPSA_MULTIPLE_NETWORK ) {
				add_action( 'network_admin_edit_wsa-multiple-network-options', [ $this, 'multiple_network_options' ] );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * 加载设置页面的样式和脚本
		 */
		function admin_enqueue_scripts(): void {
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_media();
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );
		}

		/**
		 * 设置设置子页面
		 *
		 * @param array $sections setting sections array
		 */
		function set_sections( $sections ) {
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * 添加一个设置子页面
		 *
		 * @param array $section
		 */
		function add_section( $section ): static {
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * 设置设置字段
		 *
		 * @param array $fields
		 *
		 * @return $this
		 */
		function set_fields( array $fields ): static {
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * 添加一个设置字段
		 *
		 * @param $section
		 * @param $field
		 *
		 * @return $this
		 */
		function add_field( $section, $field ): static {
			$defaults = array(
				'name'  => '',
				'label' => '',
				'desc'  => '',
				'type'  => 'text'
			);

			$arg                                 = wp_parse_args( $field, $defaults );
			$this->settings_fields[ $section ][] = $arg;

			return $this;
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
		 *
		 * Usually this should be called at `admin_init` hook.
		 *
		 * This function gets the initiated settings sections and fields. Then
		 * registers them to WordPress and ready for use.
		 */
		function admin_init() {
			//register settings sections
			foreach ( $this->settings_sections as $section ) {
				if ( ! get_option( $section['id'] ) ) {
					add_option( $section['id'] );
				}

				if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
					$callback        = function () use ( $section ) {
						echo str_replace( '"', '\"', $section['desc'] );
					};
				} elseif ( isset( $section['callback'] ) ) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
			}

			//register settings fields
			foreach ( $this->settings_fields as $section => $field ) {
				foreach ( $field as $option ) {

					$name     = $option['name'];
					$type     = $option['type'] ?? 'text';
					$label    = $option['label'] ?? '';
					$callback = $option['callback'] ?? array(
						$this,
						'callback_' . $type
					);

					$args = array(
						'id'                => $name,
						'class'             => $option['class'] ?? $name,
						'label_for'         => "{$section}[{$name}]",
						'desc'              => $option['desc'] ?? '',
						'value'             => $option['value'] ?? '',
						'name'              => $label,
						'section'           => $section,
						'size'              => $option['size'] ?? null,
						'options'           => $option['options'] ?? '',
						'std'               => $option['default'] ?? '',
						'sanitize_callback' => $option['sanitize_callback'] ?? '',
						'type'              => $type,
						'placeholder'       => $option['placeholder'] ?? '',
						'min'               => $option['min'] ?? '',
						'max'               => $option['max'] ?? '',
						'step'              => $option['step'] ?? '',
						'html'              => $option['html'] ?? '',
					);

					add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
				}
			}

			// creates our settings in the options table
			foreach ( $this->settings_sections as $section ) {
				register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
			}
		}

		/**
		 * Get field description for display
		 *
		 * @param array $args settings field args
		 */
		public function get_field_description( array $args ) {
			if ( $args['type'] == 'html' ) {
				return $args['html'];
			}
			if ( ! empty( $args['desc'] ) ) {
				$desc = __( sprintf( '<p class="description">%s</p>', $args['desc'] ), 'wp-settings-api' );
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * 文本框回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_text( array $args ): void {

			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = $args['type'] ?? 'text';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';

			$html = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>',
				$type, $size, $args['section'], $args['id'], $value, $placeholder );
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 链接回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_url( array $args ): void {
			$this->callback_text( $args );
		}

		/**
		 * 数字选择回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_number( array $args ): void {
			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = $args['type'] ?? 'number';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min         = ( $args['min'] == '' ) ? '' : ' min="' . $args['min'] . '"';
			$max         = ( $args['max'] == '' ) ? '' : ' max="' . $args['max'] . '"';
			$step        = ( $args['step'] == '' ) ? '' : ' step="' . $args['step'] . '"';

			$html = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>',
				$type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 单选框回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_checkbox( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

			$html = '<fieldset>';
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id'] );
			$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
			$html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />',
				$args['section'], $args['id'], checked( $value, 'on', false ) );
			$html .= sprintf( '%1$s<p class="description">%2$s</p></label>', $args['value'], $args['desc'] );
			$html .= '</fieldset>';

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 多选框回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_multicheck( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$html  = '<fieldset>';
			$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
			foreach ( $args['options'] as $key => $label ) {
				$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
				$html    .= sprintf( '<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
				$html    .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />',
					$args['section'], $args['id'], $key, checked( $checked, $key, false ) );
				$html    .= sprintf( '%1$s</label><br>', $label );
			}

			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * Displays a radio button for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_radio( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$html  = '<fieldset>';

			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
				$html .= sprintf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
					$args['section'], $args['id'], $key, checked( $value, $key, false ) );
				$html .= sprintf( '%1$s</label><br>', $label );
			}

			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 下拉框回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_select( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'],
				$args['id'] );

			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
			}

			$html .= sprintf( '</select>' );
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 文本域回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_textarea( array $args ): void {

			$value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';

			$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>',
				$size, $args['section'], $args['id'], $placeholder, $value );
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * HTML源码回调函数
		 *
		 * @param array $args settings field args
		 *
		 * @return void
		 */
		function callback_html( array $args ): void {
			_e( $this->get_field_description( $args ), 'wp-settings-api' );
		}

		/**
		 * 富文本编辑器回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_wysiwyg( array $args ): void {

			$value = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

			echo '<div style="max-width: ' . esc_attr( $size ) . ';">';

			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10
			);

			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}

			wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

			echo '</div>';

			_e( $this->get_field_description( $args ), 'wp-settings-api' );
		}

		/**
		 * 文件上传回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_file( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$id    = $args['section'] . '[' . $args['id'] . ']';
			$label = $args['options']['button_label'] ?? __( 'Choose File' );

			$html = sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
				$size, $args['section'], $args['id'], $value );
			$html .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 密码文本框回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_password( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
				$size, $args['section'], $args['id'], $value );
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * 颜色选择器回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_color( array $args ): void {

			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />',
				$size, $args['section'], $args['id'], $value, $args['std'] );
			$html .= $this->get_field_description( $args );

			_e( $html, 'wp-settings-api' );
		}


		/**
		 * WordPress页面下拉框回调函数
		 *
		 * @param array $args settings field args
		 */
		function callback_pages( array $args ): void {

			$dropdown_args = array(
				'selected' => esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) ),
				'name'     => $args['section'] . '[' . $args['id'] . ']',
				'id'       => $args['section'] . '[' . $args['id'] . ']',
				'echo'     => 0
			);
			_e( wp_dropdown_pages( $dropdown_args ), 'wp-settings-api' );
		}

		/**
		 * 消毒选项值
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		function sanitize_options( $options ): mixed {

			if ( ! $options ) {
				return $options;
			}

			foreach ( $options as $option_slug => $option_value ) {
				$sanitize_callback = $this->get_sanitize_callback( $option_slug );

				// If callback is set, call it
				if ( $sanitize_callback ) {
					$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
					continue;
				}
			}

			return $options;
		}

		/**
		 * 通过选项slug返回消毒后的值
		 *
		 * @param string $slug option slug
		 *
		 * @return callable|false string or bool false
		 */
		function get_sanitize_callback( string $slug = '' ): callable|bool {
			if ( empty( $slug ) ) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback
			foreach ( $this->settings_fields as $section => $options ) {
				foreach ( $options as $option ) {
					if ( $option['name'] != $slug ) {
						continue;
					}

					// Return the callback name
					return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * 获取设置字段的值
		 *
		 * @param string $option settings field name
		 * @param string $section the section name this field belongs to
		 * @param string $default default text if it's not found
		 *
		 * @return string
		 */
		function get_option( $option, $section, $default = '' ) {

			$options = WPSA_MULTIPLE_NETWORK ? get_site_option( $section ) : get_option( $section );

			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default;
		}

		/**
		 * 将导航显示为标签
		 */
		function show_navigation() {
			$html = '<h2 class="nav-tab-wrapper">';

			$count = count( $this->settings_sections );

			// don't show the navigation if only one section exists
			if ( $count === 1 ) {
				return;
			}

			foreach ( $this->settings_sections as $tab ) {
				$html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
			}

			$html .= '</h2>';

			_e( $html, 'wp-settings-api' );
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		function show_forms() {
			?>
            <div class="metabox-holder">
				<?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo esc_attr( $form['id'] ); ?>" class="group" style="display: none;">
                        <form method="post"
                              action="<?php echo WPSA_MULTIPLE_NETWORK ? 'edit.php?action=wsa-multiple-network-options' : 'options.php' ?>">
							<?php
							do_action( 'wsa_form_top_' . $form['id'], $form );
							settings_fields( $form['id'] );
							do_settings_sections( $form['id'] );
							do_action( 'wsa_form_bottom_' . $form['id'], $form );
							if ( isset( $this->settings_fields[ $form['id'] ] ) && ! ( isset( $form['show_submit'] ) && ! $form['show_submit'] ) ):
								?>
                                <div style="padding-left: 10px">
									<?php submit_button(); ?>
                                </div>
							<?php endif; ?>
                        </form>
                    </div>
				<?php } ?>
            </div>
			<?php
			$this->script();
		}

		/**
		 * 保存多站点模式下的选项
		 */
		function multiple_network_options() {
			// 检查权限
			if ( ! current_user_can( 'manage_network_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			// 消毒数据

			$option_page = sanitize_textarea_field( $_POST['option_page'] );

			check_admin_referer( $option_page . '-options' );

			global $new_whitelist_options;
			$options = $new_whitelist_options[ $option_page ];

			foreach ( $options as $option ) {
				if ( isset( $_POST[ $option ] ) ) {
					update_site_option( $option, sanitize_textarea_field( $_POST[ $option ] ) );
				} else {
					delete_site_option( $option );
				}
			}

			wp_redirect( add_query_arg( 'multiple-network-settings-updated', 'true', esc_url_raw( $_POST['_wp_http_referer'] ) ) );
			exit;
		}

		/**
		 * Tabbable JavaScript codes & Initiate Color Picker
		 *
		 * This code uses localstorage for displaying active tabs
		 */
		function script() {
			?>
            <script>
                jQuery(document).ready(function ($) {
                    //Initiate Color Picker
                    $('.wp-color-picker-field').wpColorPicker();

                    // Switches option sections
                    $('.group').hide();
                    var activetab = '';
                    if (typeof (localStorage) != 'undefined') {
                        activetab = localStorage.getItem("activetab");
                    }

                    //if url has section id as hash then set it as active or override the current local storage value
                    if (window.location.hash) {
                        activetab = window.location.hash;
                        if (typeof (localStorage) != 'undefined') {
                            localStorage.setItem("activetab", activetab);
                        }
                    }

                    if (activetab != '' && $(activetab).length) {
                        $(activetab).fadeIn();
                    } else {
                        $('.group:first').fadeIn();
                    }
                    $('.group .collapsed').each(function () {
                        $(this).find('input:checked').parent().parent().parent().nextAll().each(
                            function () {
                                if ($(this).hasClass('last')) {
                                    $(this).removeClass('hidden');
                                    return false;
                                }
                                $(this).filter('.hidden').removeClass('hidden');
                            });
                    });

                    if (activetab != '' && $(activetab + '-tab').length) {
                        $(activetab + '-tab').addClass('nav-tab-active');
                    } else {
                        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                    }
                    $('.nav-tab-wrapper a').click(function (evt) {
                        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        $(this).addClass('nav-tab-active').blur();
                        var clicked_group = $(this).attr('href');
                        if (typeof (localStorage) != 'undefined') {
                            localStorage.setItem("activetab", $(this).attr('href'));
                        }
                        $('.group').hide();
                        $(clicked_group).fadeIn();
                        evt.preventDefault();
                    });

                    $('.wpsa-browse').on('click', function (event) {
                        event.preventDefault();

                        var self = $(this);

                        // Create the media frame.
                        var file_frame = wp.media.frames.file_frame = wp.media({
                            title: self.data('uploader_title'),
                            button: {
                                text: self.data('uploader_button_text'),
                            },
                            multiple: false
                        });

                        file_frame.on('select', function () {
                            attachment = file_frame.state().get('selection').first().toJSON();
                            self.prev('.wpsa-url').val(attachment.url).change();
                        });

                        // Finally, open the modal
                        file_frame.open();
                    });
                });
            </script>
			<?php
		}
	}
endif;
