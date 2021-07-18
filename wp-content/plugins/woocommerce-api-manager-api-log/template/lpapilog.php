<?php

use LitePress\WAMAL\Inc\Model\Api_Log;
use function LitePress\WAMAL\Inc\pagination;

$api_log = new Api_Log();
$total = $api_log->get_count();
?>
<div class="wrap">
    <h1>API日志</h1>
    <div id="message" class="notice-info notice">
        <p>
            日志默认保留7天
        </p>
    </div>
    <div class="tablenav top">
        <div class="alignleft actions lpapilog_filter">
            <form method="post">
                <label for="bulk-action-selector-top" class="screen-reader-text">选择操作</label><select name="action"
                                                                                                     id="client">
                    <option value="client_domain" selected="selected">客户端域名</option>
                    <option value="client_ip">客户端IP</option>
                </select>
                <input type="text" id="lpapilog_filter_input" class="" value="" required="required">
                <input type="submit" id="lpapilog_filter_btn" class="button" value="应用筛选">
                <a id="clear_filter" class="button">清除筛选</a>
            </form>
        </div>
        <?php pagination($total, ceil($total / 20), $_GET['paged'] ?? 1); ?>
    </div>
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
        <tr>
            <th scope="col" class="manage-column">
                <span class="tips">客户端域名</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">客户端IP</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">请求数据</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">响应数据</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">所属产品</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">日期</span>
            </th>
        </thead>

        <tbody id="the-list">
        <?php foreach ($api_log->get($_GET['paged'] ?? 1) as $value): ?>
            <tr>
                <td><?php echo $value->client_domain; ?></td>
                <td><?php echo $value->client_ip; ?></td>
                <td>
                    <pre><?php echo $value->request; ?></pre>
                </td>
                <td>
                    <pre><?php echo $value->response; ?></pre>
                </td>
                <td>
                    <a href="https://litepress.cn/store/wp-admin/post.php?post=<?php echo $value->product_id; ?>&action=edit"><?php echo get_post($value->product_id)->post_title; ?></a>
                </td>
                <td><?php echo $value->created_at; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="tablenav bottom">
        <?php pagination($total, ceil($total / 20), $_GET['paged'] ?? 1); ?>
    </div>
</div>