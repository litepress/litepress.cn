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

add_action( 'plugin_loaded', 'enable_debug', 1 );
add_action( 'shutdown', 'disable_debug', 1000000 );

function enable_debug() {
    xhprof_enable(XHPROF_FLAGS_CPU +
             XHPROF_FLAGS_MEMORY);
}
function disable_debug() {
    $data = xhprof_disable();   //返回运行数据
    if ( isset( $data['main()'] ) && $data['main()']['wt'] / 1000000 > 30 ) {
        include 'xhprof/utils/xhprof_lib.php';
        include 'xhprof/utils/xhprof_runs.php';
        $objXhprofRun = new XHProfRuns_Default();
        $time = microtime();
        Logger::error( Logger::GLOBAL, "检测到脚本运行过慢，已存储log为" . $time, $_SERVER );
        $objXhprofRun->save_run($data, $time); //test 表示文件后缀
    }
}
