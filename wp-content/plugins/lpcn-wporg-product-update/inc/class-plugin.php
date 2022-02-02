<?php

namespace LitePress\Store\WPOrg_Product_Update;

use DiDom\Document;
use DiDom\Query;
use LitePress\Logger\Logger;
use LitePress\Redis\Redis;
use LitePress\Upyun\Upyun;
use WP_Http;
use function LitePress\Helper\execute_command;
use function LitePress\WP_Http\wp_remote_get;

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

		// 产品成功更新后执行的操作
		add_action( 'lpcn_wp_product_updated', array( self::get_instance(), 'product_updated' ), 10, 2 );

		/**
		 * 如果参数中传入了 ALL-IMPORT 选项，则尝试更新全部数据
		 */
		if ( isset( $_GET['ALL-IMPORT'] ) ) {
			$this->check_all_update();
		}

		$this->create_slug_update_check_task();
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
	 * 批量检查全部更新
	 */
	public function check_all_update() {
		$svn = array(
			'plugin' => "http://svn.wp-plugins.org/",
			'theme'  => "https://themes.svn.wordpress.org/",
		);

		$slugs = array();

		foreach ( $svn as $type => $url ) {
			$r = wp_remote_get( $url );
			if ( is_wp_error( $r ) ) {
				Logger::error( Logger::STORE, '应用市场更新爬虫获取全部 Slug 时失败', array(
					'url'   => $url,
					'error' => $r->get_error_message(),
				) );

				return false;
			}

			$status = wp_remote_retrieve_response_code( $r );
			if ( WP_Http::OK !== $status ) {
				Logger::error( Logger::STORE, '应用市场更新爬虫获取全部 Slug 时失败，接口返回了意料之外的状态码', array(
					'url'    => $url,
					'status' => $status,
				) );

				return false;
			}

			$body = wp_remote_retrieve_body( $r );

			$document = new Document( $body );

			$slugs_tmp = $document->find( '/html/body/ul/li/a/@href', Query::TYPE_XPATH );
			if ( empty( $slugs_tmp ) ) {
				Logger::error( Logger::STORE, '应用市场更新爬虫获取全部 Slug 时失败：DOM 提取的 Slug 列表为空', array(
					'url'   => $url,
					'error' => $r->get_error_message(),
				) );

				return false;
			}

			// 去除 Slug 结尾的斜杠
			$slugs_tmp = array_map( function ( $value ) {
				return str_replace( '/', '', $value );
			}, $slugs_tmp );

			$slugs[ $type ] = $slugs_tmp;
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

	/**
	 * 创建 Slug 更新检查任务
	 *
	 * 根据上次检查时间和当前时间的差值，以 30 分钟为一个间隔安排检查任务（任务可能一次安排多个，比如上次是 24 小时前检查的，则本次会一次性安排 48 个任务）。
	 * 该函数由 Cron 每 30 分钟触发一次。
	 */
	public function create_slug_update_check_task() {
		// 获取上次检查的时间
		$slug_update_check_last = get_option( 'lpcn_slug_update_check_last' );
		//$slug_update_check_last = 1639534224;

		$now_time = time();

		if ( $now_time < ( $slug_update_check_last + 3600 ) ) {
			return;
		}

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
	 * 产品更新后执行的操作
	 */
	public function product_updated( $slug, $type ) {
		/**
		 * 安排翻译更新队列任务
		 */
		switch_to_blog( 4 );
		$args = array(
			'slug' => $slug,
			'type' => $type,
		);
		wp_schedule_single_event( time() + 60, 'gp_import_from_wp_org', $args );
		restore_current_blog();

		/**
		 * 刷新又拍云 CDN 上的缓存
		 */
		$urls  = array(
			"https://d.w.org.ibadboy.net/{$type}s/$slug.zip",
			//"https://download.wp-china-yes.net/{$type}s/$slug.zip", // 已废弃
			// "https://downloads.litepress.cn/{$type}s/$slug.zip", // TODO: 2022年1月4日 此域名暂时未添加到 CDN
		);
		$upyun = new Upyun();
		$upyun->purge( $urls );
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
