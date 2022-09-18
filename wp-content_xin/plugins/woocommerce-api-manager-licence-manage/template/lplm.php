<?php

use LitePress\LM\Inc\Model\Licence;
use function LitePress\LM\Inc\pagination;

$licence = new Licence();

$total = $licence->get_count();

?>
<div class="wrap">
    <h1>授权管理</h1>
    <div id="message" class="notice-info notice">
        <p>
            这个列表显示了所有已激活的客户端，你可以针对某些激活进行封禁操作。
        </p>
    </div>
    <div class="tablenav top">
        <div class="alignleft actions lpapilog_filter">
            <form method="post">
                <label for="bulk-action-selector-top" class="screen-reader-text">选择操作</label>
                <select name="action" id="client">
                    <option value="object" selected="selected">客户端域名</option>
                    <option value="ip_address">客户端IP</option>
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
                <span class="tips">用户邮箱</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">所属订单</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">所属产品</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">产品版本</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">激活日期</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">状态</span>
            </th>
            <th scope="col" class="manage-column">
                <span class="tips">操作</span>
            </th>
        </thead>

        <tbody id="the-list">
		<?php foreach ( $licence->get( $_GET['paged'] ?? 1 ) as $value ): ?>
			<?php $is_disabled = Licence::is_disabled( $value->api_key ) ?>
            <tr>
                <td><?php echo $value->object; ?></td>
                <td><?php echo $value->ip_address; ?></td>
                <td>
		            <?php
		            $order = wc_get_order( $value->order_id );

		            echo $order->get_user()->user_email;
		            ?>
                </td>
                <td>
                    <a id="order_id" href="/store/wp-admin/admin.php?page=wcpv-vendor-order&id=<?php echo $value->order_id; ?>" data="<?php echo $value->order_id; ?>">
                        #<?php echo $value->order_id; ?>
                    </a>
                </td>
                <td>
                    <a href="/store/wp-admin/post.php?post=<?php echo $value->product_id; ?>&action=edit"><?php echo get_post( $value->product_id )->post_title; ?></a>
                </td>
                <td>
	                <?php echo $value->version; ?>
                </td>
                <td><?php echo date( 'Y-m-d H:i:s', $value->activation_time ); ?></td>
                <td>
                    <?php if ( false !== $is_disabled ): ?>
                    <span style="color: red;">已封禁：<?php echo $is_disabled; ?></span>
	                <?php else: ?>
                    正常
                    <?php endif; ?>
                </td>
                <td>
	                <?php if ( false !== $is_disabled ): ?>
                        <a href="#modal_enable" class="button modal_button" rel="modal:open">启用</a>
	                <?php else: ?>
                        <a href="#modal_ban" class="button modal_button" rel="modal:open">封禁</a>
	                <?php endif; ?>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
    <div class="tablenav bottom">
	<?php pagination( $total, ceil( $total / 20 ), $_GET['paged'] ?? 1 ); ?>
    </div>
</div>

<div class="modal_ban modal" id="modal_ban">
    <form method="post">
        <header>
            <h3>封禁操作</h3>
        </header>
        <main>
        <div class="input-text-wrap" id="title-wrap">
            <label for="title">
                所属订单
            </label>
            <article><div class="order_id_show"></div></article>
            <label for="order_id" class="none"><input hidden name="order_id" value=""/></label>
            <label for="method" class="none"><input hidden name="method" value="disable" /></label>
        </div>
        <div class="textarea-wrap" id="description-wrap">
            <label for="content">备注</label>
            <textarea name="comment" id="content" placeholder="禁用理由" class="mceEditor" rows="3"  autocomplete="off"></textarea>
        </div>
        </main>
        <footer>
            <button data-remodal-action="cancel" class="button">取消</button>
            <button type="submit" class="button button-primary">封禁</button>
        </footer>
    </form>
</div>
<div class="modal_enable modal" id="modal_enable">
    <form method="post">
        <header>
            <h3>启用操作</h3>
        </header>
        <main>
            <div class="input-text-wrap" id="title-wrap">
                <label for="title">
                    所属订单
                </label>
                <article><div class="order_id_show"></div></article>
                <label for="order_id" class="none"><input hidden name="order_id" value=""/></label>
                <label for="method" class="none"><input hidden name="method" value="disable" /></label>
            </div>

        </main>
        <footer>
            <button data-remodal-action="cancel" class="button">取消</button>
            <button type="submit" class="button button-primary">启用</button>
        </footer>
    </form>
</div>


