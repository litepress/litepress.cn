<?php

namespace LitePress\SVN_Browse;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_filter( 'after_setup_theme', array( $this, 'browse' ) );
	}

	public function browse() {
		/**
		 * 首先是路由处理部分
		 */
		$current_url   = add_query_arg( array() );
		$tmp           = explode( '?', $current_url );
		$current_query = '';
		if ( isset( $tmp[1] ) ) {
			$current_query = $tmp[1];
		}
		$current_url = $tmp[0] ?? '';

		$tmp                     = explode( '.', $current_url );
		$current_file_ext = $tmp[1] ?? '';

		// 如果直接访问插件svn列表则重定向到应用市场的插件目录页面
		if ( '/svn/' === $current_url || '/svn' === $current_url || '/svn/plugins' === $current_url ) {
			wp_redirect('/plugins', 301);
			exit;
		} elseif ( '/svn/themes' === $current_url ) {
			wp_redirect('/themes', 301);
			exit;
		}

		// 不是以上需重定向的内容的话就从远程取数据
		$type = stristr( $current_url, '/plugins/' ) ? 'plugin' : 'theme';
		$remote_url = str_replace( "/svn/{$type}s/", "https://{$type}s.svn.wordpress.org/", $current_url );

		$r = wp_remote_get( $remote_url );

		$body = $r['body'];

		if ( ! empty( $current_file_ext ) ) { // 不为空代表是包含后缀名的文件
			if ( ! in_array( $current_file_ext, array( 'php', 'html', 'js', 'css' ) ) ) {
				echo '当前文件暂不支持浏览';
				exit;
			}

			require PLUGIN_DIR . '/templates/file.php';
		} else { // 为空代表是不包含后缀的目录
			require PLUGIN_DIR . '/templates/dir.php';
		}

		exit;
	}

}
