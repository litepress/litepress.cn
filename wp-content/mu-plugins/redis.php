<?php
/**
 * Plugin Name: LitePress.cn 的 Redis 类
 * Description: 该类提供一系列方法帮助方便的使用 Redis
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Redis;

use LitePress\Logger\Logger;
use Redis as Redis_Lib;

class Redis {

	/**
	 * @var Redis|null The singleton instance.
	 */
	private static ?Redis $instance = null;

	private $redis = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return \Redis
	 */
	public static function get_instance(): Redis_Lib {
		if ( ! ( self::$instance instanceof Redis ) ) {
			self::$instance = new Redis();
		}

		return self::$instance->redis();
	}

	private function redis(): Redis_Lib|bool {
		if ( empty( $this->redis ) ) {
			$this->redis = new Redis_Lib();
			if ( ! $this->redis->connect( '192.168.0.12', 6379 ) ) {
				Logger::error( Logger::GLOBAL, 'Redis 连接失败：' . $this->redis->getLastError() );

				return false;
			}
		}

		return $this->redis;
	}

}
