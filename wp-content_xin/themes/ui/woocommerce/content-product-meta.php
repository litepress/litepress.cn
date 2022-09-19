<?php
/**
 * 产品的Meta区域
 */

use function LitePress\Helper\get_woo_download_url;

global $product;

$product_price = $product->get_price();
?>
<div class="project-status">
                <div class="plugin-meta">
     <span class="plugin-author">
			<i class="fas fa-user-edit" style=""></i> <?php do_action( 'wcy_product_vendor' ); ?></span>
<span class="plugin-time">
			<i class="fas fa-calendar-alt"
			   style=""></i> 更新于<?php echo human_time_diff( strtotime( $product->get_date_modified() ), time() ); ?>前</span>
<span class="tested-with">
                        <?php $api_version_required = $product->get_meta( '_api_version_required' ); ?>
	<?php if ( empty( $api_version_required ) ): ?>
		<i class="fab fa-wordpress"></i> 兼容性未知
	<?php else: ?>
		<i class="fab fa-wordpress"></i> 兼容<?php echo $api_version_required; ?>及以上版本
	<?php endif; ?>
                    </span>
<span class="tested-with">
				<i class="fas fa-chart-bar"
				   style=""></i> <?php echo wcy_prepare_installed_num( $product->get_total_sales() ); ?>个有效安装</span>
</div>
<div class="card-body btn-card-group">
	<a href="<?php echo $product->get_permalink(); ?>" class="btn btn-primary" one-link-mark="yes"><i
			class="fas fa-search"></i>查看详情</a>
	<?php if ( 0 === (int) $product_price ): ?>
		<a href="<?php echo $product->get_meta( '_download_url' ) ?: get_woo_download_url( $product->get_id() ); ?>" class="btn btn-primary"
		   one-link-mark="yes" target="_blank"><i class="fas fa-download"></i>立即下载</a>
	<?php else: ?>
		<a href="<?php echo $product->add_to_cart_url(); ?>" class="btn btn-primary" one-link-mark="yes">
            <i class="fad fa-shopping-cart"></i>立即购买
        </a>
	<?php endif; ?>
</div>
</div>
