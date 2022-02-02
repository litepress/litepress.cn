<?php
/**
 * YITH Plugin Sub-panel Class.
 *
 * @class   YIT_Plugin_SubPanel
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Plugin_SubPanel' ) ) {
	/**
	 * YIT_Plugin_SubPanel class.
	 *
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
	 */
	class YIT_Plugin_SubPanel extends YIT_Plugin_Panel {

		/**
		 * Version of the class.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * List of settings parameters.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * YIT_Plugin_SubPanel constructor.
		 *
		 * @param array $args The panel arguments.
		 */
		public function __construct( $args = array() ) {
			if ( ! empty( $args ) ) {
				$this->settings           = $args;
				$this->settings['parent'] = $this->settings['page'];
				$this->tabs_path_files    = $this->get_tabs_path_files();

				add_action( 'admin_init', array( $this, 'register_settings' ) );
				add_action( 'admin_menu', array( &$this, 'add_setting_page' ) );
				add_action( 'admin_bar_menu', array( &$this, 'add_admin_bar_menu' ), 100 );
				add_action( 'admin_init', array( &$this, 'add_fields' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}
		}

		/**
		 * Register Settings
		 * Generate wp-admin settings pages by registering your settings and using a few callbacks to control the output
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function register_settings() {
			register_setting( 'yit_' . $this->settings['page'] . '_options', 'yit_' . $this->settings['page'] . '_options', array( &$this, 'options_validate' ) );
		}


		/**
		 * Add Setting SubPage
		 * add Setting SubPage to WordPress administrator
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function add_setting_page() {
			global $admin_page_hooks;
			$logo = yith_plugin_fw_get_default_logo();

			$admin_logo = function_exists( 'yit_get_option' ) ? yit_get_option( 'admin-logo-menu' ) : '';

			if ( ! empty( $admin_logo ) ) {
				$logo = $admin_logo;
			}

			if ( ! isset( $admin_page_hooks['yith_plugin_panel'] ) ) {
				$position = apply_filters( 'yit_plugins_menu_item_position', '62.32' );
				add_menu_page( 'yith_plugin_panel', 'YITH', 'nosuchcapability', 'yith_plugin_panel', null, $logo, $position );
				// Prevent issues for backward compatibility.
				$admin_page_hooks['yith_plugin_panel'] = 'yith-plugins'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			add_submenu_page( 'yith_plugin_panel', $this->settings['label'], $this->settings['label'], 'manage_options', $this->settings['page'], array( $this, 'yit_panel' ) );
			remove_submenu_page( 'yith_plugin_panel', 'yith_plugin_panel' );
		}

		/**
		 * Show a tabbed panel to setting page
		 * a callback function called by add_setting_page => add_submenu_page
		 *
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.it>
		 */
		public function yit_panel() {
			$tabs        = '';
			$current_tab = $this->get_current_tab();
			$yit_options = $this->get_main_array_options();

			foreach ( $this->settings['admin-tabs'] as $tab => $tab_value ) {
				$active_class = $current_tab === $tab ? ' nav-tab-active' : '';
				$url          = '?page=' . $this->settings['page'] . '&tab=' . $tab;

				$tabs .= '<a class="nav-tab' . esc_attr( $active_class ) . '" href="' . esc_url( $url ) . '">' . wp_kses_post( $tab_value ) . '</a>';
			}
			?>
			<div id="icon-themes" class="icon32"><br/></div>
			<h2 class="nav-tab-wrapper">
				<?php echo $tabs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</h2>
			<?php
			$custom_tab_options = $this->get_custom_tab_options( $yit_options, $current_tab );
			if ( $custom_tab_options ) {
				$this->print_custom_tab( $custom_tab_options );

				return;
			}

			$panel_content_class = apply_filters( 'yit_admin_panel_content_class', 'yit-admin-panel-content-wrap' );
			?>
			<div id="wrap" class="yith-plugin-fw plugin-option yit-admin-panel-container">
				<?php $this->message(); ?>
				<div class="<?php echo esc_attr( $panel_content_class ); ?>">
					<h2><?php echo wp_kses_post( $this->get_tab_title() ); ?></h2>
					<?php if ( $this->is_show_form() ) : ?>
						<form id="yith-plugin-fw-panel" method="post" action="options.php">
							<?php do_settings_sections( 'yit' ); ?>
							<p>&nbsp;</p>
							<?php settings_fields( 'yit_' . $this->settings['parent'] . '_options' ); ?>
							<input type="hidden" name="<?php echo esc_attr( $this->get_name_field( 'current_tab' ) ); ?>" value="<?php echo esc_attr( $current_tab ); ?>"/>
							<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'yith-plugin-fw' ); ?>" style="float:left;margin-right:10px;"/>
						</form>
						<form method="post">
							<?php
							$reset_warning = __( 'If you continue with this action, you will reset all options in this page.', 'yith-plugin-fw' ) . '\n' . __( 'Are you sure?', 'yith-plugin-fw' );
							?>
							<input type="hidden" name="yit-action" value="reset"/>
							<input type="submit" name="yit-reset" class="button-secondary" value="<?php esc_attr_e( 'Reset to default', 'yith-plugin-fw' ); ?>"
									onclick="return confirm('<?php echo esc_attr( $reset_warning ); ?>');"/>
						</form>
						<p>&nbsp;</p>
					<?php endif ?>
				</div>
			</div>
			<?php
		}
	}
}
