<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
?>
<script type="text/x-jquery-template" id="wafTmpl_install">
	<div class="wf-modal">
		<div class="wf-modal-header">
			<div class="wf-modal-header-content">
				<div class="wf-modal-title">
					<strong><?php esc_html_e('Optimize Wordfence Firewall', 'wordfence'); ?></strong>
				</div>
			</div>
			<div class="wf-modal-header-action">
				<div><?php esc_html_e('If you cannot complete the setup process, ', 'wordfence'); ?><a target="_blank" rel="noopener noreferrer" href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY); ?>"><?php esc_html_e('click here for help', 'wordfence'); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a></div>
				<div class="wf-padding-add-left-small wf-modal-header-action-close"><a href="#" onclick="WFAD.colorboxClose(); return false" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
			</div>
		</div>
		<div class="wf-modal-content">
			<?php
			$currentAutoPrependFile = ini_get('auto_prepend_file');
			if (empty($currentAutoPrependFile) || WF_IS_WP_ENGINE || WF_IS_PRESSABLE):
			?>
			<p><?php echo wp_kses(__('To make your site as secure as possible, the Wordfence Web Application Firewall is designed to run via a PHP setting called <code>auto_prepend_file</code>, which ensures it runs before any potentially vulnerable code runs.', 'wordfence'), array('code'=>array())); ?></p>
			<?php else: ?>
			<p><?php echo wp_kses(__('To make your site as secure as possible, the Wordfence Web Application Firewall is designed to run via a PHP setting called <code>auto_prepend_file</code>, which ensures it runs before any potentially vulnerable code runs. This PHP setting is currently in use, and is including this file:', 'wordfence'), array('code'=>array())); ?></p>
			<pre class='wf-pre'><?php echo esc_html($currentAutoPrependFile); ?></pre>
			<p><?php echo wp_kses(__('If you don\'t recognize this file, please <a href="https://wordpress.org/support/plugin/wordfence" target="_blank" rel="noopener noreferrer">contact us on the WordPress support forums<span class="screen-reader-text"> (opens in new tab)</span></a> before proceeding.', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()), 'span'=>array('class'=>array()))); ?></p>
			<p><?php echo wp_kses(__('You can proceed with the installation and we will include this from within our <code>wordfence-waf.php</code> file which should maintain compatibility with your site, or you can opt to override the existing PHP setting.', 'wordfence'), array('code'=>array())); ?></p>
			<ul id="wf-waf-include-prepend" class="wf-switch"><li class="wf-active" data-option-value="include"><?php esc_html_e('Include', 'wordfence'); ?></li><li data-option-value="override"><?php esc_html_e('Override', 'wordfence'); ?></li></ul>
			<?php endif; ?>
			<div class="wf-notice"><strong><?php esc_html_e('NOTE:', 'wordfence'); ?></strong> <?php esc_html_e('If you have separate WordPress installations with Wordfence installed within a subdirectory of this site, it is recommended that you perform the Firewall installation procedure on those sites before this one.', 'wordfence'); ?></div>
			<?php
			$serverInfo = wfWebServerInfo::createFromEnvironment();
			$dropdown = array(
				array("apache-mod_php", __('Apache + mod_php', 'wordfence'), $serverInfo->isApacheModPHP(), wfWAFAutoPrependHelper::helper('apache-mod_php')->getFilesNeededForBackup()),
				array("apache-suphp", __('Apache + suPHP', 'wordfence'), $serverInfo->isApacheSuPHP(), wfWAFAutoPrependHelper::helper('apache-suphp')->getFilesNeededForBackup()),
				array("cgi", __('Apache + CGI/FastCGI', 'wordfence'), $serverInfo->isApache() && !$serverInfo->isApacheSuPHP() && ($serverInfo->isCGI() || $serverInfo->isFastCGI()), wfWAFAutoPrependHelper::helper('cgi')->getFilesNeededForBackup()),
				array("litespeed", __('LiteSpeed/lsapi', 'wordfence'), $serverInfo->isLiteSpeed(), wfWAFAutoPrependHelper::helper('litespeed')->getFilesNeededForBackup()),
				array("nginx", __('NGINX', 'wordfence'), $serverInfo->isNGINX(), wfWAFAutoPrependHelper::helper('nginx')->getFilesNeededForBackup()),
				array("iis", __('Windows (IIS)', 'wordfence'), $serverInfo->isIIS(), wfWAFAutoPrependHelper::helper('iis')->getFilesNeededForBackup()),
				array("manual", __('Manual Configuration', 'wordfence'), false, array()),
			);
			
			$hasRecommendedOption = false;
			$wafPrependOptions = '';
			foreach ($dropdown as $option) {
				list($optionValue, $optionText, $selected) = $option;
				$optionValue=esc_attr($optionValue);
				$optionText=esc_html($optionText);
				$wafPrependOptions .= "<option value=\"{$optionValue}\"" . ($selected ? ' selected' : '') . ">{$optionText}" . ($selected ? ' (recommended based on our tests)' : '') . "</option>\n";
				if ($selected) {
					$hasRecommendedOption = true;
				}
			}
			
			if (!$hasRecommendedOption): ?>
				<p><?php esc_html_e('If you know your web server\'s configuration, please select it from the list below.', 'wordfence'); ?></p>
			<?php else: ?>
				<p><?php esc_html_e('We\'ve preselected your server configuration based on our tests, but if you know your web server\'s configuration, please select it now. You can also choose "Manual Configuration" for alternate installation details.', 'wordfence'); ?></p>
			<?php endif; ?>
			<select name='serverConfiguration' id='wf-waf-server-config'>
				<?php echo $wafPrependOptions; ?>
			</select>
			<div class="wf-notice wf-nginx-waf-config" style="display: none;"><?php wp_kses(printf(/* translators: 1. PHP ini setting. 2. Support URL. */ __('Part of the Firewall configuration procedure for NGINX depends on creating a <code>%1$s</code> file in the root of your WordPress installation. This file can contain sensitive information and public access to it should be restricted. We have <a href="%2$s" target="_blank" rel="noreferrer noopener">instructions on our documentation site<span class="screen-reader-text"> (opens in new tab)</span></a> on what directives to put in your nginx.conf to fix this.', 'wordfence'), esc_html(ini_get('user_ini.filename') ? ini_get('user_ini.filename') : '(.user.ini)'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_NGINX)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()), 'span'=>array('class'=>array()))); ?></div>
			<div class="wf-manual-waf-config" style="display: none;">
				<p><?php esc_html_e('If you are using a web server not listed in the dropdown or if file permissions prevent the installer from completing successfully, you will need to perform the change manually. Click Continue below to create the required file and view manual installation instructions.', 'wordfence'); ?></p>
			</div>
			<?php
			$adminURL = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options&action=configureAutoPrepend');
			$wfnonce = wp_create_nonce('wfWAFAutoPrepend');
			foreach ($dropdown as $option):
				list($optionValue, $optionText, $selected) = $option;
				$class = preg_replace('/[^a-z0-9\-]/i', '', $optionValue);
				$helper = new wfWAFAutoPrependHelper($optionValue, null);
				$backups = $helper->getFilesNeededForBackup();
				$filteredBackups = array();
				foreach ($backups as $index => $backup) {
					if (!file_exists($backup)) {
						continue;
					}
					
					$filteredBackups[$index] = $backup;
				}
				$jsonBackups = json_encode(array_map('basename', $filteredBackups));
				?>
				<div class="wf-waf-backups wf-waf-backups-<?php echo $class; ?>" style="display: none;" data-backups="<?php echo esc_attr($jsonBackups); ?>">
					<?php if (count($filteredBackups)): ?><p><?php esc_html_e('Please download a backup of the following files before we make the necessary changes:', 'wordfence'); ?></p><?php endif; ?>
					<ul class="wf-waf-backup-file-list">
						<?php
						foreach ($filteredBackups as $index => $backup) {
							echo '<li><a class="wf-btn wf-btn-default wf-waf-backup-download" data-backup-index="' . $index . '" href="' .
								esc_url(add_query_arg(array(
									'downloadBackup'      => 1,
									'backupIndex'         => $index,
									'serverConfiguration' => $helper->getServerConfig(),
									'wfnonce'             => $wfnonce,
								), $adminURL)) . '">' . esc_html(sprintf(/* translators: File path. */ __('Download %s', 'wordfence'), basename($backup))) . '</a></li>';
						}
						?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="wf-modal-footer">
			<ul class="wf-flex-horizontal wf-flex-full-width">
				<li class="wf-waf-download-instructions"><?php esc_html_e('Once you have downloaded the files, click Continue to complete the setup.', 'wordfence'); ?></li>
				<li class="wf-right"><a href="#" class="wf-btn wf-btn-primary wf-btn-callout-subtle wf-disabled" id="wf-waf-install-continue" role="button"><?php esc_html_e('Continue', 'wordfence'); ?></a></li>
			</ul>
		</div>
	</div>
</script>