<?php
/*
Plugin Name: GlotPress Notify
Plugin URI: http://shop.webaware.com.au/downloads/glotpress-notify/
Description: notify WordPress users when new GlotPress translations strings are awaiting review
Version: 1.0.1
Author: WebAware
Author URI: http://webaware.com.au/
Text Domain: glotpress-notify
Domain Path: /languages/
*/

/*
copyright (c) 2014-2015 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('ABSPATH')) {
	exit;
}

define('GPNOTIFY_PLUGIN_FILE', __FILE__);
define('GPNOTIFY_PLUGIN_ROOT', dirname(__FILE__) . '/');
define('GPNOTIFY_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
define('GPNOTIFY_OPTIONS', 'gpnotify');
define('GPNOTIFY_PLUGIN_VERSION', '1.0.1');

// scheduled tasks
define('GPNOTIFY_TASK_NOTIFY_WAITING', 'gpnotify_waiting');

// custom exceptions
class GPNotifyException extends Exception {}

require GPNOTIFY_PLUGIN_ROOT . 'includes/class.GPNotifyPlugin.php';

GPNotifyPlugin::getInstance();
