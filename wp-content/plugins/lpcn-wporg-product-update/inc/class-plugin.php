<?php

namespace LitePress\Store\WPOrg_Product_Update;

use LitePress\Logger\Logger;
use LitePress\Redis\Redis;
use function LitePress\Helper\execute_command;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		// 加载命令行
		if ( class_exists( 'WP_CLI' ) ) {
			require __DIR__ . '/class-wporg-product-update-command.php';
		}

		add_action( 'lpcn_job_slug_update_check_task', array(
			self::get_instance(),
			'job_slug_update_check_task'
		), 10, 2 );

		if ( isset( $_GET['test'] ) ) {
			$this->create_slug_update_check_task();
		}
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
	 * 创建 Slug 更新检查任务
	 *
	 * 根据上次检查时间和当前时间的差值，以 30 分钟为一个间隔安排检查任务（任务可能一次安排多个，比如上次是 24 小时前检查的，则本次会一次性安排 48 个任务）。
	 * 该函数由 Cron 每 30 分钟触发一次。
	 */
	public function create_slug_update_check_task() {
		// 获取上次检查的时间
		$slug_update_check_last = get_option( 'lpcn_slug_update_check_last' );
		$slug_update_check_last = 1635724800;

		$now_time = time();

		for ( ; $now_time > $slug_update_check_last; $slug_update_check_last += 3600 ) {
			$start_time = date( DATE_ISO8601, $slug_update_check_last );
			$end_time   = date( DATE_ISO8601, $slug_update_check_last + 3599 );

			$args = array(
				'start_time' => $start_time,
				'end_time'   => $end_time,
			);
			wp_schedule_single_event( time() + 1, 'lpcn_job_slug_update_check_task', $args );
			update_option( 'lpcn_slug_update_check_last', $slug_update_check_last + 3600 );
		}
	}

	/**
	 * 执行 Slug 更新检查任务
	 */
	public function job_slug_update_check_task( string $start_time, string $end_time ): bool {
		$svn = array(
			'plugin' => "http://svn.wp-plugins.org/",
			'theme'  => "https://themes.svn.wordpress.org/",
		);

		$slugs = array();

		foreach ( $svn as $type => $url ) {
			$command = "svn log -v $url -r \{$start_time\}:\{$end_time\}";
			$output  = execute_command( $command, true );
			if ( is_wp_error( $output ) ) {
				Logger::error( Logger::STORE, '执行产品更新监控任务失败', array(
					'command'       => $command,
					'error_message' => $output->get_error_message(),
					'error_data'    => $output->get_all_error_data(),
				) );
			}

			$output_array = explode( "\n", $output );

			$i = 0;

			foreach ( $output_array as $item ) {
				preg_match( '|\s[A-Z]\s/([^/]+)|', $item, $matches );

				if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
					// 为了防止重复项，这里使用键来存储
					$slugs[ $type ][ $matches[1] ] = $i;
					$i ++;
				}
			}

			$slugs[ $type ] = array_flip( $slugs[ $type ] );
		}

		// 录入队列
		if ( Redis::get_instance() ) {
			foreach ( $slugs as $type => $items ) {
				if ( ! is_array( $items ) ) {
					$items = array();
				}
				foreach ( $items as $slug ) {
					Redis::get_instance()->xAdd( 'slug_update_check', '*', array( $type => $slug ) );
				}
			}

			return true;
		}

		return false;
	}

}
