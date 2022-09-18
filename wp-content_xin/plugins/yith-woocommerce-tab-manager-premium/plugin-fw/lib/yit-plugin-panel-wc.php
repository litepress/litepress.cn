<?php
/**
 * This file belongs to the YIT Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
    /**
     * YIT Plugin Panel for WooCommerce
     * Setting Page to Manage Plugins
     *
     * @class      YIT_Plugin_Panel
     * @package    YITH
     * @since      1.0
     * @author     Andrea Grillo      <andrea.grillo@yithemes.com>
     * @author     Antonio La Rocca   <antonio.larocca@yithemes.com>
     */
    class YIT_Plugin_Panel_WooCommerce extends YIT_Plugin_Panel {

        /**
         * @var string version of class
         */
        public $version = '1.0.0';

        /**
         * @var array a setting list of parameters
         */
        public $settings = array();

        /**
         * @var array a setting list of parameters
         */
        public static $wc_type = array( 'checkbox', 'textarea', 'multiselect', 'multi_select_countries', 'image_width' );

        /**
         * @var array a setting list of parameters
         */
        public static $body_class = ' yith-plugin-fw-panel ';


        /**
         * @var array
         */
        protected $_tabs_path_files;

        /**
         * @var bool
         */
        protected static $_actions_initialized = false;

        /**
         * Constructor
         *
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         */
        public function __construct( $args = array() ) {

            $args = apply_filters( 'yit_plugin_fw_wc_panel_option_args', $args );
            if ( !empty( $args ) ) {
                if ( isset( $args[ 'parent_page' ] ) && 'yit_plugin_panel' === $args[ 'parent_page' ] )
                    $args[ 'parent_page' ] = 'yith_plugin_panel';

                $this->settings         = $args;
                $this->_tabs_path_files = $this->get_tabs_path_files();

                if ( isset( $this->settings[ 'create_menu_page' ] ) && $this->settings[ 'create_menu_page' ] ) {
                    $this->add_menu_page();
                }

                if ( !empty( $this->settings[ 'links' ] ) ) {
                    $this->links = $this->settings[ 'links' ];
                }

                add_action( 'admin_init', array( $this, 'set_default_options' ) );
                add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
                add_action( 'admin_menu', array( $this, 'add_premium_version_upgrade_to_menu' ), 100 );
                add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
                add_action( 'admin_init', array( $this, 'woocommerce_update_options' ) );
                add_filter( 'woocommerce_screen_ids', array( $this, 'add_allowed_screen_id' ) );

                add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unserialize_panel_data' ), 10, 3 );

                //yith-plugin-ui
                add_action( 'yith_plugin_fw_get_field_after', array( $this, 'add_yith_ui' ) );
                add_action( 'yith_plugin_fw_before_woocommerce_panel', array( $this, 'add_plugin_banner' ), 10, 1 );
                add_action( 'admin_action_yith_plugin_fw_save_toggle_element', array( $this, 'save_toggle_element_options' ) );

                add_action( 'admin_enqueue_scripts', array( $this, 'init_wp_with_tabs' ), 11 );

                // init actions once to prevent multiple actions
                static::_init_actions();
            }
        }

        protected static function _init_actions() {
            if ( !static::$_actions_initialized ) {
                /* Add VideoBox and InfoBox */
                add_action( 'woocommerce_admin_field_boxinfo', array( __CLASS__, 'add_infobox' ), 10, 1 );

                /* Add YITH Fields */
                add_action( 'woocommerce_admin_field_yith-field', array( __CLASS__, 'add_yith_field' ), 10, 1 );

                /* WooCommerce 2.4 Support */
                add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );

                add_filter( 'woocommerce_admin_settings_sanitize_option', array( __CLASS__, 'sanitize_option' ), 10, 3 );

                // sort plugins by name in YITH Plugins menu
                add_action( 'admin_menu', array( __CLASS__, 'sort_plugins' ), 90 );

                add_filter( 'add_menu_classes', array( __CLASS__, 'add_menu_class_in_yith_plugin' ) );


                static::$_actions_initialized = true;
            }
        }


        /**
         * Show a tabbed panel to setting page
         * a callback function called by add_setting_page => add_submenu_page
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         */
        public function yit_panel() {
            $this->maybe_redirect_to_proper_wp_page();
            $additional_info = array(
                'current_tab'     => $this->get_current_tab(),
                'current_sub_tab' => $this->get_current_sub_tab(),
                'available_tabs'  => $this->settings[ 'admin-tabs' ],
                'default_tab'     => $this->get_available_tabs( true ), //get default tabs
                'page'            => $this->settings[ 'page' ],
                'wrap_class'      => isset( $this->settings[ 'class' ] ) ? $this->settings[ 'class' ] : '',
            );


            $additional_info                      = apply_filters( 'yith_admin_tab_params', $additional_info );
            $additional_info[ 'additional_info' ] = $additional_info;

            extract( $additional_info );
            require_once( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-panel.php' );
        }

        /**
         * Show a input fields to upload images
         *
         * @return   string
         * @since    1.0
         * @author   Emanuela Castorina      <emanuela.castorina@yithemes.com>
         */
        public function yit_upload_update( $option_value ) {
            return $option_value;
        }

        /**
         * Show a input fields to upload images
         *
         * @param array $args
         * @since    1.0
         * @author   Emanuela Castorina      <emanuela.castorina@yithemes.com>
         */
        public function yit_upload( $args = array() ) {
            if ( !empty( $args ) ) {
                $args[ 'value' ] = ( get_option( $args[ 'id' ] ) ) ? get_option( $args[ 'id' ] ) : $args[ 'default' ];
                extract( $args );

                include( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-upload.php' );
            }
        }

        /**
         * Add the plugin woocommerce page settings in the screen ids of woocommerce
         *
         * @param $screen_ids
         * @return mixed
         * @since    1.0.0
         * @author   Antonino Scarfì      <antonino.scarfi@yithemes.com>
         */
        public function add_allowed_screen_id( $screen_ids ) {
            global $admin_page_hooks;

            if ( !isset( $admin_page_hooks[ $this->settings[ 'parent_page' ] ] ) ) {
                return $screen_ids;
            }

            $screen_ids[] = $admin_page_hooks[ $this->settings[ 'parent_page' ] ] . '_page_' . $this->settings[ 'page' ];

            return $screen_ids;
        }

        /**
         * Returns current active tab slug
         *
         * @return string
         * @since    2.0.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         */
        public function get_current_tab() {
            global $pagenow;
            $tabs = $this->get_available_tabs();

            if ( $pagenow == 'admin.php' && isset( $_REQUEST[ 'tab' ] ) && in_array( $_REQUEST[ 'tab' ], $tabs ) ) {
                return $_REQUEST[ 'tab' ];
            } else {
                return $tabs[ 0 ];
            }
        }

        /**
         * Return available tabs
         * read all options and show sections and fields
         *
         * @param bool false for all tabs slug, true for current tab
         * @return mixed Array tabs | String current tab
         * @since    1.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         */
        public function get_available_tabs( $default = false ) {
            $tabs = array_keys( $this->settings[ 'admin-tabs' ] );

            return $default ? $tabs[ 0 ] : $tabs;
        }


        /**
         * Add sections and fields to setting panel
         * read all options and show sections and fields
         *
         * @return void
         * @since    1.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         */
        public function add_fields() {
            $yit_options = $this->get_main_array_options();
            $option_key  = $this->get_current_option_key();

            if ( !$option_key ) {
                return;
            }

            woocommerce_admin_fields( $yit_options[ $option_key ] );
        }

        /**
         * Print the panel content
         * check if the tab is a wc options tab or custom tab and print the content
         *
         * @return void
         * @since    1.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         * @author   Leanza Francesco   <leanzafrancesco@gmail.com>
         */
        public function print_panel_content() {
            $yit_options       = $this->get_main_array_options();
            $page              = $this->settings[ 'page' ];
            $option_key        = $this->get_current_option_key();
            $custom_tab_action = $this->is_custom_tab( $yit_options, $option_key );

            $this->print_tabs_nav();

            if ( $custom_tab_action ) {
                $this->print_custom_tab( $custom_tab_action );

                return;
            } else {
                require_once( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-form.php' );
            }
        }

        /**
         * Update options
         *
         * @return void
         * @since    1.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         * @see      woocommerce_update_options function
         * @internal fire two action (before and after update): yit_panel_wc_before_update and yit_panel_wc_after_update
         */
        public function woocommerce_update_options() {

            if ( isset( $_POST[ 'yit_panel_wc_options_nonce' ] ) && wp_verify_nonce( $_POST[ 'yit_panel_wc_options_nonce' ], 'yit_panel_wc_options_' . $this->settings[ 'page' ] ) ) {

                do_action( 'yit_panel_wc_before_update' );

                $yit_options = $this->get_main_array_options();
                $option_key  = $this->get_current_option_key();

                if ( version_compare( WC()->version, '2.4.0', '>=' ) ) {
                    if ( !empty( $yit_options[ $option_key ] ) ) {
                        foreach ( $yit_options[ $option_key ] as $option ) {
                            if ( isset( $option[ 'id' ] ) && isset( $_POST[ $option[ 'id' ] ] ) && isset( $option[ 'type' ] ) && !in_array( $option[ 'type' ], self::$wc_type ) ) {
                                $_POST[ $option[ 'id' ] ] = maybe_serialize( $_POST[ $option[ 'id' ] ] );
                            }
                        }
                    }
                }

                foreach ( $_POST as $name => $value ) {

                    //  Check if current POST var name ends with a specific needle and make some stuff here
                    $attachment_id_needle = "-yith-attachment-id";
                    $is_hidden_input      = ( ( $temp = strlen( $name ) - strlen( $attachment_id_needle ) ) >= 0 && strpos( $name, $attachment_id_needle, $temp ) !== false );
                    if ( $is_hidden_input ) {
                        //  Is an input element of type "hidden" coupled with an input element for selecting an element from the media gallery
                        $yit_options[ $option_key ][ $name ] = array(
                            "type" => "text",
                            "id"   => $name
                        );
                    }
                }

                woocommerce_update_options( $yit_options[ $option_key ] );

                do_action( 'yit_panel_wc_after_update' );

            } elseif ( isset( $_REQUEST[ 'yit-action' ] ) && $_REQUEST[ 'yit-action' ] == 'wc-options-reset'
                       && isset( $_POST[ 'yith_wc_reset_options_nonce' ] ) && wp_verify_nonce( $_POST[ 'yith_wc_reset_options_nonce' ], 'yith_wc_reset_options_' . $this->settings[ 'page' ] )
            ) {

                do_action( 'yit_panel_wc_before_reset' );

                $yit_options = $this->get_main_array_options();
                $option_key  = $this->get_current_option_key();
                foreach ( $yit_options[ $option_key ] as $id => $option ) {
                    if ( isset( $option[ 'yith-type' ] ) && $option[ 'yith-type' ] == 'multi-colorpicker' && !empty( $option[ 'colorpickers' ] ) ) {
                        $default = [];
                        foreach ( $option[ 'colorpickers' ] as $colorpicker ) {
                            $default[ $colorpicker[ 'id' ] ] = isset( $colorpicker[ 'default' ] ) ? $colorpicker[ 'default' ] : '';
                        }
                        update_option( $option[ 'id' ], $default );
                    } elseif ( isset( $option[ 'default' ] ) ) {
                        update_option( $option[ 'id' ], $option[ 'default' ] );
                    }
                }

                do_action( 'yit_panel_wc_after_reset' );
            }
        }

        /**
         * Add Admin WC Style and Scripts
         *
         * @return void
         * @since    1.0
         * @author   Andrea Grillo      <andrea.grillo@yithemes.com>
         * @author   Antonio La Rocca   <antonio.larocca@yithemes.com>
         * @author   Leanza Francesco   <leanzafrancesco@gmail.com>
         */
        public function admin_enqueue_scripts() {
            global $woocommerce, $pagenow;

            if ( 'customize.php' != $pagenow ) {
                wp_enqueue_style( 'wp-jquery-ui-dialog' );
            }

            $screen            = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
            $assets_screen_ids = (array) apply_filters( 'yith_plugin_fw_wc_panel_screen_ids_for_assets', array() );

            // enqueue styles only in the current panel page
            if ( $screen && ( 'admin.php' === $pagenow && strpos( $screen->id, $this->settings[ 'page' ] ) !== false ) || in_array( $screen->id, $assets_screen_ids ) ) {
                $woocommerce_version       = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
                $woocommerce_settings_deps = array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris' );

                if ( version_compare( '2.5', $woocommerce_version, '<=' ) ) {
                    // WooCommerce > 2.6
                    $woocommerce_settings_deps[] = 'select2';
                } else {
                    // WooCommerce < 2.6
                    $woocommerce_settings_deps[] = 'jquery-ui-dialog';
                    $woocommerce_settings_deps[] = 'chosen';
                }

                wp_enqueue_media();

                wp_enqueue_style( 'yith-plugin-fw-fields' );
                wp_enqueue_style( 'woocommerce_admin_styles' );
                wp_enqueue_style( 'raleway-font' );

                wp_enqueue_script( 'woocommerce_settings', $woocommerce->plugin_url() . '/assets/js/admin/settings.min.js', $woocommerce_settings_deps, $woocommerce_version, true );
                wp_localize_script( 'woocommerce_settings', 'woocommerce_settings_params', array(
                    'i18n_nav_warning' => __( 'The changes you have made will be lost if you leave this page.', 'yith-plugin-fw' )
                ) );
                wp_enqueue_script( 'yith-plugin-fw-fields' );
            }

            if ( $screen && ( 'admin.php' === $pagenow && yith_plugin_fw_is_panel() ) || in_array( $screen->id, $assets_screen_ids ) ) {
                wp_enqueue_media();
                wp_enqueue_style( 'yit-plugin-style' );
                wp_enqueue_script( 'yit-plugin-panel' );
            }

            if ( $screen && 'admin.php' === $pagenow && strpos( $screen->id, 'yith_upgrade_premium_version' ) !== false ) {
                wp_enqueue_style( 'yit-upgrade-to-pro' );
                wp_enqueue_script( 'colorbox' );
            }
        }

        /**
         * Default options
         * Sets up the default options used on the settings page
         *
         * @access public
         * @return void
         * @since  1.0.0
         */
        public function set_default_options() {
            // check if the default options for this panel are already set
            $page                = $this->settings[ 'page' ];
            $default_options_set = get_option( 'yit_plugin_fw_panel_wc_default_options_set', array() );
            if ( isset( $default_options_set[ $page ] ) && $default_options_set[ $page ] )
                return;

            $default_options = $this->get_main_array_options();

            foreach ( $default_options as $section ) {
                foreach ( $section as $value ) {
                    if ( ( isset( $value[ 'std' ] ) || isset( $value[ 'default' ] ) ) && isset( $value[ 'id' ] ) ) {
                        $default_value = ( isset( $value[ 'default' ] ) ) ? $value[ 'default' ] : $value[ 'std' ];

                        if ( $value[ 'type' ] == 'image_width' ) {
                            add_option( $value[ 'id' ] . '_width', $default_value );
                            add_option( $value[ 'id' ] . '_height', $default_value );
                        } else {
                            add_option( $value[ 'id' ], $default_value );
                        }
                    }

                }
            }

            // set the flag for the default options of this panel
            $default_options_set[ $page ] = true;
            update_option( 'yit_plugin_fw_panel_wc_default_options_set', $default_options_set );
        }

        /**
         * Delete the "default options added" option
         *
         * @author   Leanza Francesco   <leanzafrancesco@gmail.com>
         */
        public static function delete_default_options_set_option() {
            delete_option( 'yit_plugin_fw_panel_wc_default_options_set' );
        }

        /**
         * Add the WooCommerce body class in plugin panel page
         *
         * @param array $admin_body_classes The body classes
         * @return array Filtered body classes
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  2.0
         */
        public static function admin_body_class( $admin_body_classes ) {
            global $pagenow;

	        $assets_screen_ids = (array) apply_filters( 'yith_plugin_fw_wc_panel_screen_ids_for_assets', array() );


	        if ( ( 'admin.php' == $pagenow && ( strpos( get_current_screen()->id, 'yith-plugins_page' ) !== false || in_array( get_current_screen()->id, $assets_screen_ids ) ) ) )
                $admin_body_classes = substr_count( $admin_body_classes, self::$body_class ) == 0 ? $admin_body_classes . self::$body_class : $admin_body_classes;

            return 'admin.php' == $pagenow && substr_count( $admin_body_classes, 'woocommerce' ) == 0 ? $admin_body_classes .= ' woocommerce ' : $admin_body_classes;
        }

        /**
         * Maybe unserialize panel data
         *
         * @param $value     mixed  Option value
         * @param $option    mixed  Option settings array
         * @param $raw_value string Raw option value
         * @return mixed Filtered return value
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         * @since  2.0
         */
        public function maybe_unserialize_panel_data( $value, $option, $raw_value ) {
            if ( !version_compare( WC()->version, '2.4.0', '>=' ) || !isset( $option[ 'type' ] ) || in_array( $option[ 'type' ], self::$wc_type ) ) {
                return $value;
            }

            $yit_options = $this->get_main_array_options();
            $option_key  = $this->get_current_option_key();

            if ( !empty( $yit_options[ $option_key ] ) ) {
                foreach ( $yit_options[ $option_key ] as $option_array ) {
                    if ( isset( $option_array[ 'id' ] ) && isset( $option[ 'id' ] ) && $option_array[ 'id' ] == $option[ 'id' ] ) {
                        return maybe_unserialize( $value );
                    }
                }
            }

            return $value;
        }

        /**
         * Sanitize Option
         *
         * @param $value     mixed  Option value
         * @param $option    mixed  Option settings array
         * @param $raw_value string Raw option value
         * @return mixed Filtered return value
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         * @since  3.0.0
         */
        public static function sanitize_option( $value, $option, $raw_value ) {

            if ( isset( $option[ 'type' ] ) && 'yith-field' === $option[ 'type' ] ) {
                // set empty array if is multiple
                if ( !empty( $option[ 'multiple' ] ) && is_null( $value ) ) {
                    $value = array();
                }

                // sanitize the option for the checkbox field: 'yes' or 'no'
                if ( isset( $option[ 'yith-type' ] ) && in_array( $option[ 'yith-type' ], array( 'checkbox', 'onoff' ) ) ) {
                    $value = yith_plugin_fw_is_true( $raw_value ) ? 'yes' : 'no';
                }

                if ( isset( $option[ 'yith-type' ] ) && 'checkbox-array' === $option[ 'yith-type' ] ) {
                    $value = maybe_unserialize( $raw_value );
                    $value = !!$value && is_array( $value ) ? $value : array();
                }

                if ( isset( $option[ 'yith-type' ] ) && in_array( $option[ 'yith-type' ], array( 'textarea', 'textarea-editor', 'textarea-codemirror' ) ) ) {
                    $value = $raw_value;
                }

                // sanitize the option date-format when the user choose the custom option
                if ( isset( $option[ 'yith-type' ] ) && in_array( $option[ 'yith-type' ], array( 'date-format' ) ) && '\c\u\s\t\o\m' == $raw_value ) {
                    $custom = isset( $_REQUEST[ $option[ 'id' ] . '_text' ] ) ? $_REQUEST[ $option[ 'id' ] . '_text' ] : $option[ 'default' ];
                    $value  = $custom;
                }

                if ( isset( $option[ 'yith-type' ] ) && in_array( $option[ 'yith-type' ], array( 'toggle-element' ) ) ) {

                    //  error_log( print_r( $raw_value, true ) );

                    if ( $value && isset( $option[ 'elements' ] ) && !empty( $option[ 'elements' ] ) ) {

                        $value = maybe_unserialize( $value );

                        if ( isset( $value[ 'box_id' ] ) ) {
                            unset( $value[ 'box_id' ] );
                        }

                        foreach ( $value as $index => $single_toggle ) {

                            if ( $value && isset( $option[ 'onoff_field' ] ) && !empty( $option[ 'onoff_field' ] ) ) {
                                $onoff                = $option[ 'onoff_field' ];
                                $onoff[ 'type' ]      = 'yith-field';
                                $onoff[ 'yith-type' ] = 'onoff';
                                $onoff_id             = $onoff[ 'id' ];

                                $value[ $index ][ $onoff_id ] = isset( $single_toggle[ $onoff_id ] ) ? self::sanitize_option( $single_toggle[ $onoff_id ], $onoff, $single_toggle[ $onoff_id ] ) : 'no';
                            }

                            foreach ( $option[ 'elements' ] as $element ) {
                                $element_value                       = isset( $value[ $index ][ $element[ 'id' ] ] ) ? $value[ $index ][ $element[ 'id' ] ] : false;
                                $value[ $index ][ $element[ 'id' ] ] = self::sanitize_option( $element_value, $element, $element_value );
                            }
                        }
                    }


                    // error_log('sanitizes value');
                    // error_log( print_r( $value, true ) );
                }


                if ( !empty( $option[ 'yith-sanitize-callback' ] ) && is_callable( $option[ 'yith-sanitize-callback' ] ) ) {
                    $value = call_user_func( $option[ 'yith-sanitize-callback' ], $value );
                }
            }

            return $value;
        }

        /**
         * Add YITH Fields
         *
         * @param array $field
         * @return   void
         * @since    3.0.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public static function add_yith_field( $field = array() ) {
            if ( !empty( $field ) && isset( $field[ 'yith-type' ] ) ) {
                $field[ 'type' ] = $field[ 'yith-type' ];
                unset( $field[ 'yith-type' ] );

                $field[ 'id' ]      = isset( $field[ 'id' ] ) ? $field[ 'id' ] : '';
                $field[ 'name' ]    = $field[ 'id' ];
                $field[ 'default' ] = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';

                $value = apply_filters( 'yith_plugin_fw_wc_panel_pre_field_value', null, $field );
                if ( is_null( $value ) ) {
                    if ( 'toggle-element' === $field[ 'type' ] || 'toggle-element-fixed' === $field[ 'type' ] ) {
                        $value = get_option( $field[ 'id' ], $field[ 'default' ] );
                    } else {
                        $value = WC_Admin_Settings::get_option( $field[ 'id' ], $field[ 'default' ] );
                    }
                }
                $field[ 'value' ] = $value;

                require( YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-option-row.php' );
            }
        }

        /**
         *  Save the content of the toggle element present inside the panel.
         *  Called by the action 'admin_action_yith_plugin_fw_save_toggle_element'
         *  via Ajax
         *
         * @author Emanuela Castorina
         */
        public function save_toggle_element_options() {

            check_ajax_referer( 'save-toggle-element', 'security' );

            if ( !current_user_can( $this->settings[ 'capability' ] ) ) {
                wp_die( -1 );
            }

            $posted      = $_POST;
            $tabs        = $this->get_available_tabs();
            $yit_options = $this->get_main_array_options();
            $current_tab = isset( $_REQUEST[ 'tab' ] ) && in_array( $_REQUEST[ 'tab' ], $tabs ) ? $_REQUEST[ 'tab' ] : $tabs[ 0 ];
            $option_id   = isset( $_REQUEST[ 'toggle_id' ] ) ? $_REQUEST[ 'toggle_id' ] : '';
            $updated     = false;

            if ( !empty( $yit_options[ $current_tab ] ) && !empty( $option_id ) ) {

                $tab_options = $yit_options[ $current_tab ];
                foreach ( $tab_options as $key => $item ) {
                    if ( !isset( $item[ 'id' ] ) ) {
                        unset( $tab_options[ $key ] );
                    }
                }

                $option_array = array_combine( wp_list_pluck( $tab_options, 'id' ), $tab_options );
                if ( isset( $option_array[ $option_id ] ) ) {
                    $value = isset( $posted[ $option_id ] ) ? $posted[ $option_id ] : '';

                    //drag and drop
                    $order_elements = isset( $posted[ 'yith_toggle_elements_order_keys' ] ) ? explode( ',', $posted[ 'yith_toggle_elements_order_keys' ] ) : false;
                    if ( $order_elements ) {
                        $i         = 0;
                        $new_value = array();
                        foreach ( $order_elements as $key ) {
                            $index               = apply_filters( 'yith_toggle_elements_index', $i++, $key );
                            $new_value[ $index ] = $value[ $key ];
                        }

                        $value = $new_value;
                    }
                    $value   = self::sanitize_option( $value, $option_array[ $option_id ], $value );
                    $updated = update_option( $option_id, $value );
                }
            }

            return $updated;
        }

        public function print_tabs_nav( $args = array() ) {
            $defaults = array(
                'premium_class' => 'yith-premium',
                'parent_page'   => '',
                'wrapper_class' => 'nav-tab-wrapper woo-nav-tab-wrapper'
            );
            $args     = wp_parse_args( $args, $defaults );

            parent::print_tabs_nav( $args );
        }
    }
}
