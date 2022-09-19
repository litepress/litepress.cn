<?php
/**
 * 插件装载文件
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration;

/** 载入Composer的自动加载程序 */
require_once 'vendor/autoload.php';

/** 载入配置文件 */
require_once 'config.php';

/** 载入公共函数 */
require_once 'src/functions.php';

/** 注册静态脚本 */
require_once 'src/enqueue-scripts.php';

/** 载入路由 */
require_once 'route.php';
