<?php
// email template for waiting summary notification

if (!defined('ABSPATH')) {
	exit;
}
?>
<!DOCTYPE>
<html>
<head>
<title><?php echo esc_html($subject); ?></title>
<style>
body { sans-serif; color: #333; }
table { border-collapse: collapse; border-spacing: 0; }
td, th { border: 1px solid #ccc; padding: 2px; }
th { text-align: left; }
.num { text-align: right; }
</style>
</head>

<body>
	<p><strong><?php echo esc_html($title); ?></strong></p>

	<table>
		<thead>
		<tr>
			<th><?php echo esc_html_x('Language', 'translation list heading', 'glotpress-notify'); ?></th>
			<th><?php echo esc_html_x('Locale', 'translation list heading', 'glotpress-notify'); ?></th>
			<th class="num"><?php echo esc_html_x('Current', 'translation list heading', 'glotpress-notify'); ?></th>
			<th class="num"><?php echo esc_html_x('Waiting', 'translation list heading', 'glotpress-notify'); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php foreach ($translations as $translation) { ?>
		<tr>
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
		<?php } ?>
		</tbody>

		<tfoot>
		<tr>
			<th><?php echo esc_html_x('Language', 'translation list heading', 'glotpress-notify'); ?></th>
			<th><?php echo esc_html_x('Locale', 'translation list heading', 'glotpress-notify'); ?></th>
			<th class="num"><?php echo esc_html_x('Current', 'translation list heading', 'glotpress-notify'); ?></th>
			<th class="num"><?php echo esc_html_x('Waiting', 'translation list heading', 'glotpress-notify'); ?></th>
		</tr>
		</tfoot>

	</table>
</body>

</html>
