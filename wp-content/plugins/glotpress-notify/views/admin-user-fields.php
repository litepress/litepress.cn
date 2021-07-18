<?php
// user profile fields for GlotPress notifications

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">

	<h2><?php esc_html_e('GlotPress Notify Subscriptions', 'gpnotify'); ?></h2>
	<p><?php esc_html_e('Receive notifications for GlotPress translation projects.', 'gpnotify'); ?></p>

	<?php if ($update_message): ?>
	<div class="updated">
		<p><?php echo $update_message; ?></p>
	</div>
	<?php endif; ?>

	<form action="<?php echo $form_action; ?>" method="post">

		<table class="wp-list-table widefat fixed">

			<thead>
				<tr>
					<th><?php echo esc_html_x('Project name', 'subscription settings', 'glotpress-notify'); ?></th>
					<th><?php echo esc_html_x('Project slug', 'subscription settings', 'glotpress-notify'); ?></th>
					<th><?php echo esc_html_x('Waiting', 'subscription settings', 'glotpress-notify'); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th><?php echo esc_html_x('Project name', 'subscription settings', 'glotpress-notify'); ?></th>
					<th><?php echo esc_html_x('Project slug', 'subscription settings', 'glotpress-notify'); ?></th>
					<th><?php echo esc_html_x('Waiting', 'subscription settings', 'glotpress-notify'); ?></th>
				</tr>
			</tfoot>

			<tbody>
			<?php $i = 0; foreach ($projects as $project) {
				$tr_class = ($i % 2) ? '' : 'class="alternate"';

				$fieldbase = "gpnotify_projects_{$project->id}";
				$waiting = empty($project_options['waiting'][$project->id]) ? 0 : 1;
				?>

				<tr <?php echo $tr_class; ?>>
					<td><?php
						if (empty($project->project_uri)) {
							echo esc_html($project->name);
						}
						else {
							printf('<a href="%s" target="_blank">%s</a>', esc_url($project->project_uri), esc_html($project->name));
						}
					?></td>
					<td><?php echo esc_html($project->slug); ?></td>
					<td><input type="checkbox" name="<?php echo $fieldbase; ?>_waiting" <?php checked($waiting); ?> value="1" /></td>
				</tr>

			<?php $i++; } ?>
			</tbody>

		</table>

		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Save Subscriptions', 'gpnotify'); ?>" />
			<?php wp_nonce_field('subscribe', 'gpnotify_nonce'); ?>
		</p>

	</form>

</div>

