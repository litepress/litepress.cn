<?php
/**
 * Plugin Name: LitePress.cn的日志服务
 * Description: 为LitePress.cn提供统一的日志服务
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Logger;

require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger as Logger_Lib;
use Monolog\Handler\StreamHandler;

/**
 * @method static Logger_Lib debug( string $name, string $message, array $context = array() )
 * @method static Logger_Lib info( string $name, string $message, array $context = array() )
 * @method static Logger_Lib notice( string $name, string $message, array $context = array() )
 * @method static Logger_Lib warning( string $name, string $message, array $context = array() )
 * @method static Logger_Lib error( string $name, string $message, array $context = array() )
 * @method static Logger_Lib critical( string $name, string $message, array $context = array() )
 * @method static Logger_Lib alert( string $name, string $message, array $context = array() )
 * @method static Logger_Lib emergency( string $name, string $message, array $context = array() )
 *
 * @see Logger_Lib
 */
class Logger {

	const LEVEL = Logger_Lib::DEBUG;

	/**
	 * @var array
	 */
	private static array $instances = array();

	public static function __callStatic( $func, $args ): void {
		if ( count( $args ) < 2 ) {
			return;
		}

		$name = $args[0];
		unset( $args[0] );

		if ( ! key_exists( $name, self::$instances ) || ! ( self::$instances[ $name ] instanceof Logger_Lib ) ) {
			$log = new Logger_Lib( $name );
			$log->pushHandler( new StreamHandler( WP_CONTENT_DIR . '/run.log', self::LEVEL ) );
			self::$instances[ $name ] = $log;
		}

		call_user_func_array( array( self::$instances[ $name ], $func ), $args );
	}

}
