<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the persistent banner.
 */
?>
<ul id="wf-onboarding-banner">
	<li><?php esc_html_e('Wordfence installation is incomplete', 'wordfence'); ?></li>
	<li><a href="<?php echo esc_attr(network_admin_url('admin.php?page=WordfenceSupport&onboarding=1')); ?>" class="wf-onboarding-btn wf-onboarding-btn-default" id="wf-onboarding-resume"><?php esc_html_e('Resume Installation', 'wordfence'); ?></a></li>
</ul>
