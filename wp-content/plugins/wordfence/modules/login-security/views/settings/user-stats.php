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
				<th class="wfls-center"><?php esc_html_e('2FA Inactive', 'wordfence-2fa'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$roles = new WP_Roles();
			foreach ($counts['avail_roles'] as $roleTag => $count):
				$superAdmin = ($roleTag === 'super-admin');
				$role = $roles->get_role($roleTag);
				if (!$superAdmin && !$role) { continue; }
				$names = $roles->get_names();
				$roleName = $superAdmin ? __('Super Administrator', 'wordfence-2fa') : $names[$roleTag];
				$activeCount = (isset($counts['active_avail_roles'][$roleTag]) ? $counts['active_avail_roles'][$roleTag] : 0);
				$inactiveCount = $count - $activeCount;
				$requiredAt = \WordfenceLS\Controller_Settings::shared()->get_required_2fa_role_activation_time($roleTag);
				$inactive = $inactiveCount > 0 && $requiredAt !== false;
				$viewUsersBaseUrl = 'admin.php?' . http_build_query(array('page' => 'WFLS', 'role'=> $roleTag));
			?>
				<tr>
					<td><?php echo \WordfenceLS\Text\Model_HTML::esc_html(translate_user_role($roleName)); ?></td>
					<td class="wfls-center"><?php echo number_format($count); ?></td>
					<td class="wfls-center"><?php echo number_format($activeCount); ?></td>
					<td class="wfls-center">
						<?php if ($inactive): ?><a href="<?php echo esc_attr(is_multisite() ? network_admin_url($viewUsersBaseUrl) : admin_url($viewUsersBaseUrl)); ?>"><?php endif ?>
						<?php echo number_format($inactiveCount); ?>
						<?php if ($inactive): ?> (<?php esc_html_e('View users', 'wordfence-2fa') ?>)</a><?php endif ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
			<tr>
				<th><?php esc_html_e('Total', 'wordfence-2fa'); ?></th>
				<th class="wfls-center"><?php echo number_format($counts['total_users']); ?></th>
				<th class="wfls-center"><?php echo number_format($counts['active_total_users']); ?></th>
				<th class="wfls-center"><?php echo number_format($counts['total_users'] - $counts['active_total_users']); ?></th>
			</tr>
			<?php if (is_multisite()): ?>
			<tr>
				<td colspan="4" class="wfls-text-small"><?php esc_html_e('* User counts currently only reflect the main site on multisite installations.', 'wordfence-2fa'); ?></td>
			</tr>
			<?php endif; ?>
			</tfoot>
		</table>
	</div>
</div>