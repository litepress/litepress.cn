<?php
/**
 * 上传翻译词条到记忆库
 */

if (!get_param('key') === 'EpQg4dY7oNLjbnwN') {
    error('接口权限认证错误');
}

header('Content-Type:application/json; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php');

if (!get_param('msgid') || !get_param('msgstr')) {
    error('接口传参错误');
}

$results = $wpdb->get_row($wpdb->prepare("SELECT level FROM wp_4_gp_memory WHERE md5 = %s ",
    md5(strtolower(get_param('msgid')))));
if ($results && $results->level === 2) {
    success('未记录任何数据，因为存在优先级更高的记忆条目');
}

if ($wpdb->replace('wp_4_gp_memory', [
    'md5'     => md5(strtolower(get_param('msgid'))),
    'source'  => get_param('msgid'),
    'target'  => get_param('msgstr'),
    'user_id' => 1
])) {
    success('记录成功');
} else {
    error('出现未知异常');
}

function get_param(string $key) :string {
    if (!key_exists($key, $_POST)) {
        return '';
    }

    return htmlentities(trim($_POST[$key]),ENT_QUOTES,"UTF-8");
}

function success($message = '', $data = []) {
    echo json_encode([
        'code'    => 0,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function error($message = '', $code = - 1) {
    echo json_encode([
        'code'    => $code,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}