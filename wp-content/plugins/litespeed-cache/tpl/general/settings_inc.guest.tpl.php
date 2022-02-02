<?php
namespace LiteSpeed;
defined( 'WPINC' ) || exit;
?>
	<tr>
		<th>
			<?php $id = Base::O_GUEST; ?>
			<?php $this->title( $id ); ?>
		</th>
		<td>
			<?php $this->build_switch( $id ); ?>
			<div class="litespeed-desc">
				<?php echo __( 'Guest Mode provides an always cacheable landing page for an automated guest\'s first time visit, and then attempts to update cache varies via AJAX.', 'litespeed-cache' ); ?>
				<?php echo __( 'This option can help to correct the cache vary for certain advanced mobile or tablet visitors.', 'litespeed-cache' ); ?>
				<?php Doc::learn_more( 'https://docs.litespeedtech.com/lscache/lscwp/general/#guest-mode' ); ?>
				<br /><?php Doc::notice_htaccess(); ?>
				<br /><?php Doc::crawler_affected(); ?>
			</div>
		</td>
	</tr>

