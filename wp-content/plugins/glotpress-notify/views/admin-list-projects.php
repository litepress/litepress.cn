<?php
// email template for waiting summary notification

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">

	<h2><?php esc_html_e('GlotPress Notify Projects', 'gpnotify'); ?></h2>

	<?php if (count($waiting) === 0): ?>

		<p><?php esc_html_e('There are no GlotPress projects with translations waiting for approval.', 'gpnotify'); ?></p>

	<?php else: ?>

		<?php foreach ($waiting as $project_id => $translations) { ?>

		<h3><?php echo esc_html($projects[$project_id]->name); ?></h3>

		<table class="wp-list-table widefat fixed">
			<thead>
			<tr>
				<th><?php echo esc_html_x('Language', 'translation list heading', 'glotpress-notify'); ?></th>
				<th><?php echo esc_html_x('Locale', 'translation list heading', 'glotpress-notify'); ?></th>
				<th class="num"><?php echo esc_html_x('Current', 'translation list heading', 'glotpress-notify'); ?></th>
				<th class="num"><?php echo esc_html_x('Waiting', 'translation list heading', 'glotpress-notify'); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php $i = 0; foreach ($translations as $translation) {
				$tr_class = ($i % 2) ? '' : 'class="alternate"';
				?>
				<tr <?php echo $tr_class; ?>>
					<td><?php
						if (empty($translation->translation_uri)) {
							echo esc_html($translation->locale_name);
						}
						else {
							printf('<a href="%s" target="_blank">%s</a>', esc_url($translation->translation_uri), esc_html($translation->locale_name));
						}
					?></td>
					<td><?php echo esc_html($translation->locale); ?></td>
					<td class="num"><?php echo esc_html($translation->current); ?></td>
					<td class="num"><?php echo esc_html($translation->waiting); ?></td>
				</tr>
			<?php $i++; } ?>
			</tbody>

		</table>

		<?php } ?>

	<?php endif; ?>

</div>

