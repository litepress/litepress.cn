<?php
/**
 * Plugin Name: Woo Data Entry
 * Description: 【此插件已被更优方案取代，不久后移除】 Woo的数据录入插件，该插件改造了Woo的Rest Api，使其适合爬虫录入数据
 * Version: 1.0.0
 * Author: WP中国本土化社区
 * Author URI: https://wp-china.org
 * WC requires at least: 4.7.0
 * WC tested up to: 4.7.0
 * Requires WP: 5.5.4
 * Requires PHP: 7.4
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace WCY\WDE;

require_once 'vendor/autoload.php';
require 'src/scheduled-tasks.php';
require 'src/class-api-project.php';

use WCY\WDE\Src\Controllers\WDE_REST_Products_Controller;

final class WCY_Woo_Data_Entry
{

	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct()
	{
		// 注册Rest API路由
		add_action('rest_api_init', function () {
			$products_controller = new WDE_REST_Products_Controller;
			$products_controller->register_routes();
		}, 10);
	}

}

WCY_Woo_Data_Entry::instance();
