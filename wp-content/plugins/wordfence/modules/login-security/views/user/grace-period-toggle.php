<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }

?>
<table id="wfls-grace-period-toggle-container" style="display: none">
	<tr>
		<th scope="row"><label for="wfls-grace-period-toggle"><?php esc_html_e('2FA Grace Period', 'wordfence-2fa') ?></label></th>
		<td>
			<input id="wfls-grace-period-toggle" name="wfls-grace-period-toggle" type="checkbox">
			<label for="wfls-grace-period-toggle"><?php esc_html_e('Allow a grace period for this user prior to requiring Wordfence 2FA') ?></label>
		</td>
	</tr>
</table>