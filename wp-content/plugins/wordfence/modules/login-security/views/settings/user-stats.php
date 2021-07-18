<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }
/**
 * @var array $counts The counts to display. Required.
 */
?>
<div class="wfls-block wfls-always-active wfls-flex-item-full-width">
	<div class="wfls-block-header wfls-block-header-border-bottom">
		<div class="wfls-block-header-content">
			<div class="wfls-block-title">
				<strong><?php esc_html_e('User Summary', 'wordfence-2fa'); ?></strong>
			</div>
		</div>
		<div class="wfls-block-header-action wfls-block-header-action-text wfls-nowrap wfls-padding-add-right-responsive">
			<a href="users.php"><?php esc_html_e('Manage Users', 'wordfence'); ?></a>
		</div>
	</div>
	<div class="wfls-block-content wfls-padding-no-left wfls-padding-no-right">
		<table class="wfls-table wfls-table-striped wfls-table-header-separators wfls-table-expanded wfls-no-bottom">
			<thead>
			<tr>
				<th><?php esc_html_e('Role', 'wordfence-2fa'); ?></th>
				<th class="wfls-center"><?php esc_html_e('Total Users', 'wordfence-2fa'); ?></th>
				<th class="wfls-center"><?php esc_html_e('2FA Active', 'wordfence-2fa'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$roles = new WP_Roles();
			foreach ($counts['avail_roles'] as $roleTag => $count):
				$role = $roles->get_role($roleTag);
				if (!$role) { continue; }
				$names = $roles->get_names();
				$roleName = $names[$roleTag];
			?>
				<tr>
					<td><?php echo \WordfenceLS\Text\Model_HTML::esc_html(translate_user_role($roleName)); ?></td>
					<td class="wfls-center"><?php echo number_format($count); ?></td>
					<td class="wfls-center"><?php echo (isset($counts['active_avail_roles'][$roleTag]) ? number_format($counts['active_avail_roles'][$roleTag]) : 0); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
			<tr>
				<th><?php esc_html_e('Total', 'wordfence-2fa'); ?></th>
				<th class="wfls-center"><?php echo number_format($counts['total_users']); ?></th>
				<th class="wfls-center"><?php echo number_format($counts['active_total_users']); ?></th>
			</tr>
			<?php if (is_multisite()): ?>
			<tr>
				<td colspan="3" class="wfls-text-small"><?php esc_html_e('* User counts currently only reflect the main site on multisite installations.', 'wordfence-2fa'); ?></td>
			</tr>
			<?php endif; ?>
			</tfoot>
		</table>
	</div>
</div>
