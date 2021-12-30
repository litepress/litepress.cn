<?php if (!defined('WORDFENCE_VERSION')) { exit; } ?>
<?php
if (!wfUtils::isAdmin()) {
	exit();
}
/**
 * @var array $results
 */
?>
<table border="0" cellpadding="2" cellspacing="0" class="wf-recent-traffic-table">
	<?php foreach ($results as $key => $v) { ?>
		<tr>
			<th><?php esc_html_e('Time:', 'wordfence') ?></th>
			<td><?php esc_html_e(sprintf(
				/* translators: 1. Time ago, example: 2 hours, 40 seconds. 2. Localized date. 3. Unix timestamp.  */
					__('%1$s ago -- %2$s -- %3$s in Unixtime', 'wordfence'), $v['timeAgo'], date(DATE_RFC822, $v['ctime']), $v['ctime'])) ?></td>
		</tr>
		<?php if ($v['timeSinceLastHit']) {
			echo '<th>' . esc_html__('Seconds since last hit:', 'wordfence') . '</th><td>' . $v['timeSinceLastHit'] . '</td></tr>';
		} ?>
		<tr>
			<th><?php esc_html_e('URL:', 'wordfence') ?></th>
			<td>
				<a href="<?php echo esc_url($v['URL']) ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($v['URL']); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a>
			</td>
		</tr>
		<tr>
			<th>Type:</th>
			<td><?php
				if ($v['statusCode'] == '404') {
					echo '<span style="color: #F00;">' . esc_html('Page not found', 'wordfence') . '</span>';
				}
				else if ($v['type'] == 'hit') {
					esc_html_e('Normal request', 'wordfence');
				} ?></td>
		</tr>
		<?php if ($v['referer']) { ?>
			<tr>
			<th><?php esc_html_e('Referrer:', 'wordfence') ?></th>
			<td>
				<a href="<?php echo esc_url($v['referer']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($v['referer']); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a>
			</td></tr><?php } ?>
		<tr>
			<th><?php esc_html_e('Full Browser ID:', 'wordfence') ?></th>
			<td><?php echo esc_html($v['UA']); ?></td>
		</tr>
		<?php if ($v['user']) { ?>
			<tr>
				<th><?php esc_html_e('User:', 'wordfence') ?></th>
				<td>
					<a href="<?php echo esc_url($v['user']['editLink']); ?>" target="_blank" rel="noopener noreferrer"><span data-userid="<?php echo esc_attr($v['user']['ID']); ?>" class="wfAvatar"></span><?php echo esc_html($v['user']['display_name']); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a>
				</td>
			</tr>
		<?php } ?>
		<?php if ($v['loc']) { ?>
			<tr>
				<th><?php esc_html_e('Location:', 'wordfence') ?></th>
				<td>
					<span class="wf-flag <?php echo esc_attr('wf-flag-' . strtolower($v['loc']['countryCode'])); ?>" title="<?php echo esc_attr($v['loc']['countryName']); ?>"></span>
					<?php if ($v['loc']['city']) {
						echo esc_html($v['loc']['city']) . ', ';
					} ?>
					<?php 
					if ($v['loc']['region'] && wfUtils::shouldDisplayRegion($v['loc']['countryName'])) {
						echo esc_html($v['loc']['region']) . ', ';
					} ?>
					<?php echo esc_html($v['loc']['countryName']); ?>
				</td>
			</tr>
		<?php } ?>
		<tr class="wf-recent-traffic-table-row-border">
			<td colspan="2"><div></div></td>
		</tr>
	<?php } ?>

</table>