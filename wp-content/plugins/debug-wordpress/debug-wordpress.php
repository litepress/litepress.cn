<?php
/**
 * Plugin Name: 调试WordPress性能
 * Description: 玛尼玛尼哄，妖魔鬼怪快现形！
 * Author: LitePress团队
 * Author URI:https://litepress.cn/
 * Version: 1.0.0
 * Network: True
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

use LitePress\Logger\Logger;

// 判断是不是cli环境，cli下不加载调试
if ( php_sapi_name() != 'cli' ) {
	add_action( 'plugin_loaded', 'enable_debug', 1 );
	add_action( 'shutdown', 'disable_debug', 1000000 );
}

function enable_debug() {
	xhprof_enable( XHPROF_FLAGS_CPU +
	               XHPROF_FLAGS_MEMORY );
}

function disable_debug() {
	$data = xhprof_disable();// 取运行数据
	// 检查主函数是否存在
	if ( isset( $data['main()'] ) ) {
		// 检查主函数的运行时间是否大于10秒，内存是否大于100M
		if ( $data['main()']['wt'] / 1000000 > 30 || $data['main()']['pmu'] / 1048576 > 100 ) {
			include 'xhprof/utils/xhprof_lib.php';
			include 'xhprof/utils/xhprof_runs.php';
			$objXhprofRun = new XHProfRuns_Default();
			// 生成唯一id
			$id = uniqid();
			// 记录请求参数
			Logger::error( Logger::GLOBAL, "检测到脚本运行过慢，已存储log为" . $id, $_SERVER ?? '无SERVER变量' );
			$objXhprofRun->save_run( $data, 'slow', $id );
		}
	}
}
