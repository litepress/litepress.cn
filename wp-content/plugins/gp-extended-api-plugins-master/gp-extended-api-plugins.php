<?php
/*
Plugin Name: Extended Api Plugins for GlotPress as a WordPress plugin
Plugin URI: https://github.com/david-binda/gp-extended-api-plugins
Description: Expands the GP API by adding extended Translation endpoints
Version: 0.1.0
Author: david-binda, hewsut
Author URI: https://github.com/david-binda/gp-extended-api-plugins
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

define( 'GP_EXTENDED_API_PLUGINS_DIR', plugin_dir_path( __FILE__ ) );

require_once( GP_EXTENDED_API_PLUGINS_DIR . 'gp-translation-extended-api/gp-translation-extended-api.php' );
