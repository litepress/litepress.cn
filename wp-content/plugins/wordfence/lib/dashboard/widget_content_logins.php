<?php if (!defined('WORDFENCE_VERSION')) { exit; } ?>
<?php //$data is defined here as an array of login attempts: array('t' => timestamp, 'name' => username, 'ip' => IP address) ?>
<table class="wf-table wf-table-hover">
	<thead>
		<tr>
			<th><?php esc_html_e('Username', 'wordfence') ?></th>
			<th><?php esc_html_e('IP', 'wordfence') ?></th>
			<th><?php esc_html_e('Date', 'wordfence') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($data as $l): ?>
		<tr>
			<td><?php echo esc_html($l['name']); ?></td>
			<td><?php echo esc_html($l['ip']); ?></td>
			<td><?php
				if (time() - $l['t'] < 86400) {
					echo esc_html(wfUtils::makeTimeAgo(time() - $l['t']) . ' ago');
				}
				else {
					echo esc_html(wfUtils::formatLocalTime(get_option('date_format') . ' ' . get_option('time_format'), (int) $l['t']));
				}
				?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>