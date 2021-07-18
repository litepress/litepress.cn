<?php
/**
 * 虽说WordPress不建议开放私有入口点，但是查询记忆库这样很小的一个操作如果把整个WordPress框架都加载一遍显然是不值得的……
 */

$query = htmlentities(trim($_GET['query']),ENT_QUOTES,"UTF-8");

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php');

$results = $wpdb->get_row($wpdb->prepare("SELECT target FROM wp_4_gp_memory WHERE source = %s ", $query));

header('Content-Type:application/json; charset=utf-8');

if ($results != null && $results->target != null) {
    echo $results->target;
}
