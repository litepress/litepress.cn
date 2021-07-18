<?php
/**
 * 配置文件
 *
 * @package WP_REAL_PERSON_VERIFY
 */

define( 'WPRPV_VERSION', '1.0.0' );

define( 'WPRPV_ROOT_PATH', plugin_dir_path( __FILE__ ) );

define( 'WPRPV_ROOT_URL', plugin_dir_url( __FILE__ ) );

define( 'WPRPV_VERIFY_LIMIT', 3 );

define( 'WPRPV_DATA_DIR', WP_CONTENT_DIR . '/uploads/real-person-verify' );

define( 'WPRPV_DATA_URL', esc_html( content_url( '/uploads/real-person-verify' ) ) );
