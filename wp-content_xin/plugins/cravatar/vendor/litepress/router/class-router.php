<?php

namespace LitePress\Router;

/**
 * 路由框架
 *
 * @package LitePress\Router
 */
class Router {

	/**
	 * 路由
	 *
	 * 存储的是路由和模板路径的关联管理，例如 [ /user => /www/wp-content/user.php ]
	 *
	 * @var array
	 * @access protected
	 */
	private $routes;

	private static $instance = null;

	private function __construct() {
		add_filter( 'template_include', array( $this, 'include_template' ) );
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof Router ) ) {
			self::$instance = new Router();
		}

		return self::$instance;
	}

	/**
	 * 添加一条路由
	 *
	 * @param string $route 路由
	 * @param string $template_path 此路由对应的模板文件路径
	 *
	 * @return void
	 */
	public function add_route( $route, $template_path ) {
		$site_base = parse_url( home_url(), PHP_URL_PATH );
		if ( '/' !== $site_base ) {
			$this->routes[ $site_base . $route ] = $template_path;
		} else {
			$this->routes[ $route ] = $template_path;
		}
	}

	public function include_template( $template_path ) {
		$current_path = parse_url( add_query_arg( array() ), PHP_URL_PATH );

		if ( key_exists( $current_path, $this->routes ) ) {
			header( 'HTTP/1.1 200 OK' );

			return $this->routes[ $current_path ];
		}

		return $template_path;
	}

}

/**
 * 创建路由条目的帮助函数
 *
 * @param string $route 路由
 * @param string $template_path 此路由对应的模板文件路径
 *
 * @return void
 */
function register_route( $route, $template_path ) {
	$router = Router::get_instance();
	$router->add_route( $route, $template_path );
}
