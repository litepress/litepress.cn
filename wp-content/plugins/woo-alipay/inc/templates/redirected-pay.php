<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="wooalipay-loader">	
	<div class="wooalipay-loader-inner">
		<div class="ant-spin ant-spin-lg ant-spin-spinning">
			<span class="ant-spin-dot ant-spin-dot-spin">
				<i class="ant-spin-dot-item"></i>
				<i class="ant-spin-dot-item"></i>
				<i class="ant-spin-dot-item"></i>
				<i class="ant-spin-dot-item"></i>
			</span>
		</div>
		<div class="wooalipay-loader-description">
			<?php esc_html_e( 'Contacting Alipay...', 'woo-alipay' ); ?>
		</div>
	</div>
</div>
<?php echo $dispatcher_form; // WPCS: XSS OK ?>
