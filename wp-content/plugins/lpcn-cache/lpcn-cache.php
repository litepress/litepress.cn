<?php
/**
 * Plugin Name: LitePress.cn Cache
 * Description: 为LitePress.cn专门定制的一套缓存规则
 * Version: 1.0.0
 * Author: LitePress团队
 */

global $blog_id;

switch ( (int) $blog_id ) {
	case 3:
		require 'store.php';
}

