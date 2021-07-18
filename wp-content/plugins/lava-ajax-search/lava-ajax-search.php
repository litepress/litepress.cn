<?php
/**
 * Plugin Name: Lava Ajax Search
 * Plugin URI: http://lava-code.com/ajax-search/
 * Description: Lava Ajax Search
 * Version: 1.1.8

 * Author: Lavacode
 * Author URI: http://lava-code.com/
 * Text Domain: lvbp-ajax-search
 * Domain Path: /languages/
 */
/*
    Copyright Automattic and many other contributors.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if( ! defined( 'ABSPATH' ) )
	die();

if( ! class_exists( 'Lava_Ajax_Search' ) ) :

	class Lava_Ajax_Search {

		public static $instance;

		private $version = '1.1.8';
		public  $path = false;

		public function __construct( $file ) {
			$this->file = $file;
			$this->folder = basename( dirname( $this->file ) );
			$this->path = dirname( $this->file );
			$this->template_path = trailingslashit( $this->path ) . 'templates';
			$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/dist/', $this->file ) ) );
			$this->image_url = esc_url( trailingslashit( $this->assets_url . 'images/' ) );

			register_activation_hook( $this->file, Array( $this, 'register' ) );
			register_deactivation_hook( $this->file, Array( $this, 'unregister' ) );

			$this->load_files();
			$this->register_hooks();
		}

		public function getHookName( $suffix='' ) {
			$suffix = !empty( $suffix ) ? '_' . $suffix : $suffix;
			return sprintf( '%1$s%2$s', $this->getName(), $suffix );
		}

		public function register() {
			flush_rewrite_rules();
			do_action( $this->getHookName( 'Register' ) );
		}

		public function unregister(){ do_action( $this->getHookName( 'Unregister' ) ); }
		public function getVersion() { return $this->version; }
		public function getName(){ return get_class( $this ); }
		public function getPluginDir() { return trailingslashit( dirname( dirname( __FILE__ ) ) ); }

		public function load_files() {
			require_once( 'includes/class-admin.php' );
			require_once( 'includes/class-core.php' );
			require_once( 'includes/class-template.php' );
			require_once( 'includes/class-shortcode.php' );
		}

		public function register_hooks() {
			add_action( 'init', Array( $this, 'initialize' ) );

			load_plugin_textdomain( 'lvbp-ajax-search', false, $this->folder . '/languages/' );
		}

		public function initialize() {
			add_rewrite_tag( '%edit%', '([^&]+)' );

			$this->admin = new Lava_Ajax_Search_Admin;
			$this->core = new Lava_Ajax_Search_Core;
			$this->shortcode = new Lava_Ajax_Search_Shortcode;
			$this->template = new Lava_Ajax_Search_Template;
		}

		public static function get_instance( $file ) {
			if( ! self::$instance )
				self::$instance = new self( $file );
			return self::$instance;
		}

	}
endif;

if( !function_exists( 'lava_ajaxSearch' ) ) :
	function lava_ajaxSearch() {
		return Lava_Ajax_Search::get_instance( __FILE__ );
	}
	lava_ajaxSearch();
endif;
