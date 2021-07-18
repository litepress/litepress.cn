<?php
// settings form

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">

	<h2><?php esc_html_e('GlotPress Notify', 'glotpress-notify'); ?></h2>

	<?php settings_errors(); ?>

	<form action="<?php echo admin_url('options.php'); ?>" method="POST">
		<?php settings_fields(GPNOTIFY_OPTIONS); ?>

		<table class="form-table">
		<tr>
			<th scope="row"><label><?php esc_html_e('Email sender', 'glotpress-notify'); ?></label></th>
			<td>
				<input name="gpnotify[email_from]" type="text" class="regular-text" value="<?php echo esc_attr($options['email_from']); ?>"
					placeholder="<?php esc_attr_e('Some Name <name@example.com>', 'glotpress-notify'); ?>" />
				<br /><?php esc_html_e('(full From: address)', 'glotpress-notify'); ?>
			</td>
		</tr>

		<tr>
			<th scope="row"><label><?php esc_html_e('GlotPress table prefix', 'glotpress-notify'); ?></label></th>
			<td>
				<input name="gpnotify[gp_prefix]" type="text" class="regular-text" value="<?php echo esc_attr($options['gp_prefix']); ?>" />
			</td>
		</td>

		</table>

		<?php submit_button(); ?>
	</form>

</div>

