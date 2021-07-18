<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

/** @var boolean $inEmail */

$diagnostic = new wfDiagnostic;
$plugins = get_plugins();
$activePlugins = array_flip(get_option('active_plugins'));
$activeNetworkPlugins = is_multisite() ? array_flip(wp_get_active_network_plugins()) : array();
$muPlugins = get_mu_plugins();
$themes = wp_get_themes();
$currentTheme = wp_get_theme();
$cols = 3;

$w = new wfConfig();
if (!isset($sendingDiagnosticEmail)) {
	$sendingDiagnosticEmail = false;
}
?>
<?php if (!$sendingDiagnosticEmail): ?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Diagnostics', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
		});
	})(jQuery);
</script>
<?php endif; ?>
<div id="wf-diagnostics">
	<?php if (!$sendingDiagnosticEmail): ?>
		<div class="wf-diagnostics-wrapper">
			<div class="wf-flex-row">
				<div class="wf-flex-row-1">
					<?php esc_html_e('This page shows information that can be used for troubleshooting conflicts, configuration issues, or compatibility with other plugins, themes, or a host\'s environment.', 'wordfence') ?>
				</div>
				<div class="wf-flex-row-0 wf-padding-add-left">
					<div id="sendByEmailThanks" class="hidden">
						<h3><?php esc_html_e('Thanks for sending your diagnostic page over email', 'wordfence'); ?></h3>
					</div>
					<div id="sendByEmailDiv" class="wf-add-bottom">
						<span class="wf-nowrap">
							<input class="wf-btn wf-btn-primary wf-btn-sm" type="submit" id="exportDiagnostics" value="Export"/>
							<input class="wf-btn wf-btn-primary wf-btn-sm" type="submit" id="sendByEmail" value="Send Report by Email"/>
							<input class="wf-btn wf-btn-default wf-btn-sm" type="button" id="expandAllDiagnostics" value="Expand All Diagnostics"/>
						</span>
					</div>
				</div>
			</div>
			<div id="sendByEmailForm" class="wf-block wf-active hidden">
				<div class="wf-block-header">
					<div class="wf-block-header-content">
						<div class="wf-block-title">
							<strong><?php esc_html_e('Send Report by Email', 'wordfence') ?></strong>
						</div>
					</div>
				</div>
				<div class="wf-block-content wf-clearfix">
					<ul class="wf-block-list">
						<li>
							<div><?php esc_html_e('Email address:', 'wordfence'); ?></div>
							<div style="width: 40%">
								<p><input class="wf-input-text" type="email" id="_email" value="wftest@wordfence.com"/>
								</p>
							</div>
						</li>
						<li>
							<div><?php esc_html_e('Ticket Number/Forum Username:', 'wordfence'); ?></div>
							<div style="width: 40%">
								<p><input class="wf-input-text" type="text" id="_ticketnumber" required/></p>
							</div>
						</li>
						<li>
							<p>
								<input class="wf-btn wf-btn-primary" type="button" id="doSendEmail" value="Send"/>
							</p>
						</li>
					</ul>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class="wf-diagnostics-wrapper">
		<?php foreach ($diagnostic->getResults() as $title => $tests):
			$key = sanitize_key('wf-diagnostics-' . $title);
			$hasFailingTest = false;
			foreach ($tests['results'] as $result) {
				$infoOnly = isset($result['infoOnly']) && $result['infoOnly'];
				if (!$result['test'] && !$infoOnly) {
					$hasFailingTest = true;
					break;
				}
			}

			if ($inEmail): ?>
				<table>
					<thead>
					<tr>
						<th colspan="2"><?php echo esc_html($title) ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($tests['results'] as $result): ?>
						<?php
						$infoOnly = isset($result['infoOnly']) && $result['infoOnly'];
						?>
						<tr>
							<td style="width: 75%; min-width: 300px"><?php echo (is_array($result['label']) && isset($result['label']['raw']) && $result['label']['raw'] ? $result['label']['value'] : wp_kses($result['label'], array(
									'code'   => array(),
									'strong' => array(),
									'em'     => array(),
									'a'      => array('href' => true),
								))) ?></td>
							<td>
								<?php if ($infoOnly): ?>
									<div class="wf-result-info"><?php echo nl2br(esc_html($result['message'])); ?></div>
								<?php elseif ($result['test']): ?>
									<div class="wf-result-success"><?php echo nl2br(esc_html($result['message'])); ?></div>
								<?php else: ?>
									<div class="wf-result-error"><?php echo nl2br(esc_html($result['message'])); ?></div>
								<?php endif ?>
								<?php if (isset($result['detail']) && !empty($result['detail'])): ?>
									<p><strong><?php esc_html_e('Additional Detail', 'wordfence'); ?></strong><br><?php echo nl2br(esc_html($result['detail'])); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			<?php else: ?>
				<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive($key) ? ' wf-active' : '') .
					($hasFailingTest ? ' wf-diagnostic-fail' : '') ?>" data-persistence-key="<?php echo esc_attr($key) ?>">
					<div class="wf-block-header">
						<div class="wf-block-header-content">
							<div class="wf-block-title">
								<strong><?php echo esc_html($title) ?></strong>
								<span class="wf-text-small"><?php echo esc_html($tests['description']) ?></span>
							</div>
							<div class="wf-block-header-action">
								<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive($key) ? 'true' : 'false'); ?>" tabindex="0"></div>
							</div>
						</div>
					</div>
					<div class="wf-block-content wf-clearfix">
						<ul class="wf-block-list">
							<?php foreach ($tests['results'] as $key => $result): ?>
								<?php
								$infoOnly = isset($result['infoOnly']) && $result['infoOnly'];
								?>
								<li>
									<div style="width: 75%; min-width: 300px;"><?php echo (is_array($result['label']) && isset($result['label']['raw']) && $result['label']['raw'] ? $result['label']['value'] : wp_kses($result['label'], array(
											'code'   => array(),
											'strong' => array(),
											'em'     => array(),
											'a'      => array('href' => true),
										))) ?></div>
									<div class="wf-right">
									<?php if ($infoOnly): ?>
										<div class="wf-result-info"><?php echo nl2br(esc_html($result['message'])); ?></div>
									<?php elseif ($result['test']): ?>
										<div class="wf-result-success"><?php echo nl2br(esc_html($result['message'])); ?></div>
									<?php else: ?>
										<div class="wf-result-error"><?php echo nl2br(esc_html($result['message'])); ?></div>
									<?php endif ?>
									<?php if (isset($result['detail']) && !empty($result['detail'])): ?>
											<p><a href="#" onclick="jQuery('#wf-diagnostics-detail-<?php echo esc_attr($key); ?>').show(); jQuery(this).hide(); return false;"><?php esc_html_e('View Additional Detail', 'wordfence'); ?></a></p>
											<pre class="wf-pre wf-split-word" id="wf-diagnostics-detail-<?php echo esc_attr($key); ?>" style="max-width: 600px; display: none;"><?php echo esc_html($result['detail']); ?></pre>
									<?php endif; ?>
										</div>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>
			<?php endif ?>

		<?php endforeach ?>
		<?php
		$howGet = wfConfig::get('howGetIPs', false);
		list($currentIP, $currentServerVarForIP) = wfUtils::getIPAndServerVariable();
		$howGetHasErrors = $howGet && (! $currentServerVarForIP || $howGet !== $currentServerVarForIP);
		?>
		<div class="wf-block<?php echo ($howGetHasErrors ? ' wf-diagnostic-fail' : '') . (wfPersistenceController::shared()->isActive('wf-diagnostics-client-ip') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-client-ip') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('IP Detection', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('Methods of detecting a visitor\'s IP address.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-client-ip') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">

				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<tbody class="thead">
					<tr>
						<th><?php esc_html_e('IPs', 'wordfence'); ?></th>
						<th><?php esc_html_e('Value', 'wordfence'); ?></th>
						<th><?php esc_html_e('Used', 'wordfence'); ?></th>
					</tr>
					</tbody>
					<tbody>
					<?php
					$serverVariables = array(
						'REMOTE_ADDR'           => 'REMOTE_ADDR',
						'HTTP_CF_CONNECTING_IP' => 'CF-Connecting-IP',
						'HTTP_X_REAL_IP'        => 'X-Real-IP',
						'HTTP_X_FORWARDED_FOR'  => 'X-Forwarded-For',
					);
					foreach (wfUtils::getAllServerVariableIPs() as $variable => $ip): ?>
						<tr>
							<td><?php echo isset($serverVariables[$variable]) ? $serverVariables[$variable] : $variable ?></td>
							<td><?php
								if (! $ip) {
									_e('(not set)', 'wordfence');
								} elseif (is_array($ip)) {
									$output = array_map('esc_html', $ip);
									echo str_replace($currentIP, "<strong>{$currentIP}</strong>", implode(', ', $output));
								} else {
									echo esc_html($ip);
								}
							?></td>
							<?php if ($currentServerVarForIP && $currentServerVarForIP === $variable): ?>
								<td class="wf-result-success"><?php esc_html_e('In use', 'wordfence'); ?></td>
							<?php elseif ($howGet === $variable): ?>
								<td class="wf-result-error"><?php esc_html_e('Configured but not valid', 'wordfence'); ?></td>
							<?php else: ?>
								<td></td>
							<?php endif ?>
						</tr>
					<?php endforeach ?>
					<tr>
						<td><?php esc_html_e('Trusted Proxies', 'wordfence'); ?></td>
						<td><?php echo esc_html(implode(', ', explode("\n", wfConfig::get('howGetIPs_trusted_proxies', '')))); ?></td>
						<td></td>
					</tr>
					</tbody>
				</table>

			</div>
		</div>

		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-constants') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-wordpress-constants') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('WordPress Settings', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('WordPress version and internal settings/constants.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-constants') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<tbody>
					<?php
					require(ABSPATH . 'wp-includes/version.php');
					$postRevisions = (defined('WP_POST_REVISIONS') ? WP_POST_REVISIONS : true);
					$wordPressValues = array(
						'WordPress Version'            => array('description' => '', 'value' => $wp_version),
						'Multisite'					   => array('description' => __('Return value of is_multisite()', 'wordfence'), 'value' => is_multisite() ? __('Yes', 'wordfence') : __('No', 'wordfence')),
						'ABSPATH'					   => __('WordPress base path', 'wordfence'), 
						'WP_DEBUG'                     => array('description' => __('WordPress debug mode', 'wordfence'), 'value' => (defined('WP_DEBUG') && WP_DEBUG ? __('On', 'wordfence') : __('Off', 'wordfence'))),
						'WP_DEBUG_LOG'                 => array('description' => __('WordPress error logging override', 'wordfence'), 'value' => defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'Enabled' : 'Disabled') : __('(not set)', 'wordfence')),
						'WP_DEBUG_DISPLAY'             => array('description' => __('WordPress error display override', 'wordfence'), 'value' => defined('WP_DEBUG_DISPLAY') ? (WP_DEBUG_LOG ? 'Enabled' : 'Disabled') : __('(not set)', 'wordfence')),
						'SCRIPT_DEBUG'                 => array('description' => __('WordPress script debug mode', 'wordfence'), 'value' => (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? __('On', 'wordfence') : __('Off', 'wordfence'))),
						'SAVEQUERIES'                  => array('description' => __('WordPress query debug mode', 'wordfence'), 'value' => (defined('SAVEQUERIES') && SAVEQUERIES ? __('On', 'wordfence') : __('Off', 'wordfence'))),
						'DB_CHARSET'                   => __('Database character set', 'wordfence'),
						'DB_COLLATE'                   => __('Database collation', 'wordfence'),
						'WP_SITEURL'                   => __('Explicitly set site URL', 'wordfence'),
						'WP_HOME'                      => __('Explicitly set blog URL', 'wordfence'),
						'WP_CONTENT_DIR'               => array('description' => __('"wp-content" folder is in default location', 'wordfence'), 'value' => (realpath(WP_CONTENT_DIR) === realpath(ABSPATH . 'wp-content') ? __('Yes', 'wordfence') : sprintf(/* translators: WordPress content directory. */ __('No: %s', 'wordfence'), WP_CONTENT_DIR))),
						'WP_CONTENT_URL'               => __('URL to the "wp-content" folder', 'wordfence'),
						'WP_PLUGIN_DIR'                => array('description' => __('"plugins" folder is in default location', 'wordfence'), 'value' => (realpath(WP_PLUGIN_DIR) === realpath(ABSPATH . 'wp-content/plugins') ? __('Yes', 'wordfence') : sprintf(/* translators: WordPress plugins directory. */ __('No: %s', 'wordfence'), WP_PLUGIN_DIR))),
						'WP_LANG_DIR'                  => array('description' => __('"languages" folder is in default location', 'wordfence'), 'value' => (realpath(WP_LANG_DIR) === realpath(ABSPATH . 'wp-content/languages') ? __('Yes', 'wordfence') : sprintf(/* translators: WordPress languages directory. */ __('No: %s', 'wordfence'), WP_LANG_DIR))),
						'WPLANG'                       => __('Language choice', 'wordfence'),
						'UPLOADS'                      => __('Custom upload folder location', 'wordfence'),
						'TEMPLATEPATH'                 => array('description' => __('Theme template folder override', 'wordfence'), 'value' => (defined('TEMPLATEPATH') && realpath(get_template_directory()) !== realpath(TEMPLATEPATH) ? sprintf(/* translators: WordPress theme template directory. */ __('Overridden: %s', 'wordfence'), TEMPLATEPATH) : __('(not set)', 'wordfence'))),
						'STYLESHEETPATH'               => array('description' => __('Theme stylesheet folder override', 'wordfence'), 'value' => (defined('STYLESHEETPATH') && realpath(get_stylesheet_directory()) !== realpath(STYLESHEETPATH) ? sprintf(/* translators: WordPress theme stylesheet directory. */ __('Overridden: %s', 'wordfence'), STYLESHEETPATH) : __('(not set)', 'wordfence'))),
						'AUTOSAVE_INTERVAL'            => __('Post editing automatic saving interval', 'wordfence'),
						'WP_POST_REVISIONS'            => array('description' => __('Post revisions saved by WordPress', 'wordfence'), 'value' => is_numeric($postRevisions) ? $postRevisions : ($postRevisions ? __('Unlimited', 'wordfence') : __('None', 'wordfence'))),
						'COOKIE_DOMAIN'                => __('WordPress cookie domain', 'wordfence'),
						'COOKIEPATH'                   => __('WordPress cookie path', 'wordfence'),
						'SITECOOKIEPATH'               => __('WordPress site cookie path', 'wordfence'),
						'ADMIN_COOKIE_PATH'            => __('WordPress admin cookie path', 'wordfence'),
						'PLUGINS_COOKIE_PATH'          => __('WordPress plugins cookie path', 'wordfence'),
						'NOBLOGREDIRECT'               => __('URL redirected to if the visitor tries to access a nonexistent blog', 'wordfence'),
						'CONCATENATE_SCRIPTS'          => array('description' => __('Concatenate JavaScript files', 'wordfence'), 'value' => (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'WP_MEMORY_LIMIT'              => __('WordPress memory limit', 'wordfence'),
						'WP_MAX_MEMORY_LIMIT'          => __('Administrative memory limit', 'wordfence'),
						'WP_CACHE'                     => array('description' => __('Built-in caching', 'wordfence'), 'value' => (defined('WP_CACHE') && WP_CACHE ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence'))),
						'CUSTOM_USER_TABLE'            => array('description' => __('Custom "users" table', 'wordfence'), 'value' => (defined('CUSTOM_USER_TABLE') ? sprintf(/* translators: WordPress custom user table. */ __('Set: %s', 'wordfence'), CUSTOM_USER_TABLE) : __('(not set)', 'wordfence'))),
						'CUSTOM_USER_META_TABLE'       => array('description' => __('Custom "usermeta" table', 'wordfence'), 'value' => (defined('CUSTOM_USER_META_TABLE') ? sprintf(/* translators: WordPress custom user meta table. */ __('Set: %s', 'wordfence'), CUSTOM_USER_META_TABLE) : __('(not set)', 'wordfence'))),
						'FS_CHMOD_DIR'                 => array('description' => __('Overridden permissions for a new folder', 'wordfence'), 'value' => defined('FS_CHMOD_DIR') ? decoct(FS_CHMOD_DIR) : __('(not set)', 'wordfence')),
						'FS_CHMOD_FILE'                => array('description' => __('Overridden permissions for a new file', 'wordfence'), 'value' => defined('FS_CHMOD_FILE') ? decoct(FS_CHMOD_FILE) : __('(not set)', 'wordfence')),
						'ALTERNATE_WP_CRON'            => array('description' => __('Alternate WP cron', 'wordfence'), 'value' => (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence'))),
						'DISABLE_WP_CRON'              => array('description' => __('WP cron status', 'wordfence'), 'value' => (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? __('Disabled', 'wordfence') : __('Enabled', 'wordfence'))),
						'WP_CRON_LOCK_TIMEOUT'         => __('Cron running frequency lock', 'wordfence'),
						'EMPTY_TRASH_DAYS'             => array('description' => __('Interval the trash is automatically emptied at in days', 'wordfence'), 'value' => (EMPTY_TRASH_DAYS > 0 ? EMPTY_TRASH_DAYS : __('Never', 'wordfence'))),
						'WP_ALLOW_REPAIR'              => array('description' => __('Automatic database repair', 'wordfence'), 'value' => (defined('WP_ALLOW_REPAIR') && WP_ALLOW_REPAIR ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence'))),
						'DO_NOT_UPGRADE_GLOBAL_TABLES' => array('description' => __('Do not upgrade global tables', 'wordfence'), 'value' => (defined('DO_NOT_UPGRADE_GLOBAL_TABLES') && DO_NOT_UPGRADE_GLOBAL_TABLES ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'DISALLOW_FILE_EDIT'           => array('description' => __('Disallow plugin/theme editing', 'wordfence'), 'value' => (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'DISALLOW_FILE_MODS'           => array('description' => __('Disallow plugin/theme update and installation', 'wordfence'), 'value' => (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'IMAGE_EDIT_OVERWRITE'         => array('description' => __('Overwrite image edits when restoring the original', 'wordfence'), 'value' => (defined('IMAGE_EDIT_OVERWRITE') && IMAGE_EDIT_OVERWRITE ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'FORCE_SSL_ADMIN'              => array('description' => __('Force SSL for administrative logins', 'wordfence'), 'value' => (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'WP_HTTP_BLOCK_EXTERNAL'       => array('description' => __('Block external URL requests', 'wordfence'), 'value' => (defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'WP_ACCESSIBLE_HOSTS'          => __('Allowlisted hosts', 'wordfence'),
						'WP_AUTO_UPDATE_CORE'          => array('description' => __('Automatic WP Core updates', 'wordfence'), 'value' => defined('WP_AUTO_UPDATE_CORE') ? (is_bool(WP_AUTO_UPDATE_CORE) ? (WP_AUTO_UPDATE_CORE ? __('Everything', 'wordfence') : __('None', 'wordfence')) : WP_AUTO_UPDATE_CORE) : __('Default', 'wordfence')),
						'WP_PROXY_HOST'                => array('description' => __('Hostname for a proxy server', 'wordfence'), 'value' => defined('WP_PROXY_HOST') ? WP_PROXY_HOST : __('(not set)', 'wordfence')),
						'WP_PROXY_PORT'                => array('description' => __('Port for a proxy server', 'wordfence'), 'value' => defined('WP_PROXY_PORT') ? WP_PROXY_PORT : __('(not set)', 'wordfence')),
						'MULTISITE'               	   => array('description' => __('Multisite enabled', 'wordfence'), 'value' => defined('MULTISITE') ? (MULTISITE ? __('Yes', 'wordfence') : __('No', 'wordfence')) : __('(not set)', 'wordfence')),
						'WP_ALLOW_MULTISITE'           => array('description' => __('Multisite/network ability enabled', 'wordfence'), 'value' => (defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
						'SUNRISE'					   => array('description' => __('Multisite enabled, WordPress will load the /wp-content/sunrise.php file', 'wordfence'), 'value' => defined('SUNRISE') ? __('Yes', 'wordfence') : __('(not set)', 'wordfence')),
						'SUBDOMAIN_INSTALL'			   => array('description' => __('Multisite enabled, subdomain installation constant', 'wordfence'), 'value' => defined('SUBDOMAIN_INSTALL') ? (SUBDOMAIN_INSTALL ? __('Yes', 'wordfence') : __('No', 'wordfence')) : __('(not set)', 'wordfence')),
						'VHOST'						   => array('description' => __('Multisite enabled, Older subdomain installation constant', 'wordfence'), 'value' => defined('VHOST') ? (VHOST == 'yes' ? __('Yes', 'wordfence') : __('No', 'wordfence')) : __('(not set)', 'wordfence')),
						'DOMAIN_CURRENT_SITE'		   => __('Defines the multisite domain for the current site', 'wordfence'),
						'PATH_CURRENT_SITE'			   => __('Defines the multisite path for the current site', 'wordfence'),
						'BLOG_ID_CURRENT_SITE'		   => __('Defines the multisite database ID for the current site', 'wordfence'),
						'WP_DISABLE_FATAL_ERROR_HANDLER' => array('description' => __('Disable the fatal error handler', 'wordfence'), 'value' => (defined('WP_DISABLE_FATAL_ERROR_HANDLER') && WP_DISABLE_FATAL_ERROR_HANDLER ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
					);

					foreach ($wordPressValues as $settingName => $settingData):
						$escapedName = esc_html($settingName);
						$escapedDescription = '';
						$escapedValue = __('(not set)', 'wordfence');
						if (is_array($settingData)) {
							$escapedDescription = esc_html($settingData['description']);
							if (isset($settingData['value'])) {
								$escapedValue = esc_html($settingData['value']);
							}
						} else {
							$escapedDescription = esc_html($settingData);
							if (defined($settingName)) {
								$escapedValue = esc_html(constant($settingName));
							}
						}
						?>
						<tr>
							<td><strong><?php echo $escapedName ?></strong></td>
							<td><?php echo $escapedDescription ?></td>
							<td><?php echo $escapedValue ?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-plugins') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-wordpress-plugins') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('WordPress Plugins', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('Status of installed plugins.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-plugins') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<tbody>
					<?php foreach ($plugins as $plugin => $pluginData): ?>
						<?php
						$slug = $plugin;
						if (preg_match('/^([^\/]+)\//', $plugin, $matches)) {
							$slug = $matches[1];
						}
						else if (preg_match('/^([^\/.]+)\.php$/', $plugin, $matches)) {
							$slug = $matches[1];
						}
						?>
						<tr>
							<td>
								<strong><?php echo esc_html($pluginData['Name']); ?> (<?php echo esc_html($slug); ?>)</strong>
								<?php if (!empty($pluginData['Version'])): ?>
									- <?php echo esc_html(sprintf(__('Version %s', 'wordfence'), $pluginData['Version'])); ?>
								<?php endif ?>
							</td>
							<?php if (array_key_exists(trailingslashit(WP_PLUGIN_DIR) . $plugin, $activeNetworkPlugins)): ?>
								<td class="wf-result-success"><?php esc_html_e('Network Activated', 'wordfence'); ?></td>
							<?php elseif (array_key_exists($plugin, $activePlugins)): ?>
								<td class="wf-result-success"><?php esc_html_e('Active', 'wordfence'); ?></td>
							<?php else: ?>
								<td class="wf-result-inactive"><?php esc_html_e('Inactive', 'wordfence'); ?></td>
							<?php endif ?>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-mu-wordpress-plugins') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-mu-wordpress-plugins') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Must-Use WordPress Plugins', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('WordPress "mu-plugins" that are always active, including those provided by hosts.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-mu-wordpress-plugins') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<?php if (!empty($muPlugins)): ?>
						<tbody>
						<?php foreach ($muPlugins as $plugin => $pluginData): ?>
							<?php
							$slug = $plugin;
							if (preg_match('/^([^\/]+)\//', $plugin, $matches)) {
								$slug = $matches[1];
							}
							else if (preg_match('/^([^\/.]+)\.php$/', $plugin, $matches)) {
								$slug = $matches[1];
							}
							?>
							<tr>
								<td>
									<strong><?php echo esc_html($pluginData['Name']) ?> (<?php echo esc_html($slug); ?>)</strong>
									<?php if (!empty($pluginData['Version'])): ?>
										- <?php echo esc_html(sprintf(/* translators: Plugin version. */ __('Version %s', 'wordfence'), $pluginData['Version'])); ?>
									<?php endif ?>
								</td>
								<td class="wf-result-success"><?php esc_html_e('Active', 'wordfence'); ?></td>
							</tr>
						<?php endforeach ?>
						</tbody>
					<?php else: ?>
						<tbody>
						<tr>
							<td><?php esc_html_e('No MU-Plugins', 'wordfence'); ?></td>
						</tr>
						</tbody>

					<?php endif ?>
				</table>
			</div>
		</div>
		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-dropin-wordpress-plugins') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-dropin-wordpress-plugins') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Drop-In WordPress Plugins', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('WordPress "drop-in" plugins that are active.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-dropin-wordpress-plugins') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<tbody>
					<?php
					//Taken from plugin.php and modified to always show multisite drop-ins
					$dropins = array(
						'advanced-cache.php'	 => array( __( 'Advanced caching plugin'       ), 'WP_CACHE' ), // WP_CACHE
						'db.php'            	 => array( __( 'Custom database class'         ), true ), // auto on load
						'db-error.php'      	 => array( __( 'Custom database error message' ), true ), // auto on error
						'install.php'       	 => array( __( 'Custom installation script'    ), true ), // auto on installation
						'maintenance.php'   	 => array( __( 'Custom maintenance message'    ), true ), // auto on maintenance
						'object-cache.php'  	 => array( __( 'External object cache'         ), true ), // auto on load
						'php-error.php'          => array( __( 'Custom PHP error message'	   ), true ), // auto on error
						'fatal-error-handler.php'=> array( __( 'Custom PHP fatal error handler' ), true ), // auto on error
					);
					$dropins['sunrise.php'       ] = array( __( 'Executed before Multisite is loaded' ), is_multisite() && 'SUNRISE' ); // SUNRISE
					$dropins['blog-deleted.php'  ] = array( __( 'Custom site deleted message'   ), is_multisite() ); // auto on deleted blog
					$dropins['blog-inactive.php' ] = array( __( 'Custom site inactive message'  ), is_multisite() ); // auto on inactive blog
					$dropins['blog-suspended.php'] = array( __( 'Custom site suspended message' ), is_multisite() ); // auto on archived or spammed blog
					?>
					<?php foreach ($dropins as $file => $data): ?>
						<?php
						$active = file_exists(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file) && is_readable(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file) && $data[1];
						?>
						<tr>
							<td>
								<strong><?php echo esc_html($data[0]) ?> (<?php echo esc_html($file); ?>)</strong>
							</td>
							<?php if ($active): ?>
								<td class="wf-result-success"><?php esc_html_e('Active', 'wordfence'); ?></td>
							<?php else: ?>
								<td class="wf-result-inactive"><?php esc_html_e('Inactive', 'wordfence'); ?></td>
							<?php endif; ?>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-themes') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-wordpress-themes') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Themes', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('Status of installed themes.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-themes') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<?php if (!empty($themes)): ?>
						<tbody>
						<?php foreach ($themes as $theme => $themeData): ?>
							<?php
							$slug = $theme;
							if (preg_match('/^([^\/]+)\//', $theme, $matches)) {
								$slug = $matches[1];
							}
							else if (preg_match('/^([^\/.]+)\.php$/', $theme, $matches)) {
								$slug = $matches[1];
							}
							?>
							<tr>
								<td>
									<strong><?php echo esc_html($themeData['Name']) ?> (<?php echo esc_html($slug); ?>)</strong>
									<?php if (!empty($themeData['Version'])): ?>
										- <?php echo esc_html(sprintf(/* translators: Theme version. */ __('Version %s', 'wordfence'), $themeData['Version'])); ?>
									<?php endif ?>
								<?php if ($currentTheme instanceof WP_Theme && $theme === $currentTheme->get_stylesheet()): ?>
									<td class="wf-result-success"><?php esc_html_e('Active', 'wordfence'); ?></td>
								<?php else: ?>
									<td class="wf-result-inactive"><?php esc_html_e('Inactive', 'wordfence'); ?></td>
								<?php endif ?>
							</tr>
						<?php endforeach ?>
						</tbody>
					<?php else: ?>
						<tbody>
						<tr>
							<td><?php esc_html_e('No Themes', 'wordfence'); ?></td>
						</tr>
						</tbody>

					<?php endif ?>
				</table>
			</div>
		</div>
		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-cron-jobs') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-wordpress-cron-jobs') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Cron Jobs', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('List of WordPress cron jobs scheduled by WordPress, plugins, or themes.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-wordpress-cron-jobs') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
					<tbody>
					<?php
					$cron = _get_cron_array();

					foreach ($cron as $timestamp => $values) {
						if (is_array($values)) {
							foreach ($values as $cron_job => $v) {
								if (is_numeric($timestamp)) {
									$overdue = ((time() - 1800) > $timestamp);
									?>
									<tr<?php echo $overdue ? ' class="wf-overdue-cron"' : ''; ?>>
										<td><?php echo esc_html(date('r', $timestamp)) . ($overdue ? ' <strong>(' . esc_html__('Overdue', 'wordfence') . ')</strong>' : '') ?></td>
										<td><?php echo esc_html($cron_job) ?></td>
									</tr>
									<?php
								}
							}
						}
					}
					?>
					</tbody>
				</table>
			</div>
		</div>

		<?php
		global $wpdb;
		$wfdb = new wfDB();
		//This must be done this way because MySQL with InnoDB tables does a full regeneration of all metadata if we don't. That takes a long time with a large table count.
		$tables = $wfdb->querySelect('SELECT SQL_CALC_FOUND_ROWS TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME ASC LIMIT 250');
		$total = $wfdb->querySingle('SELECT FOUND_ROWS()');
		foreach ($tables as &$t) {
			$t = "'" . esc_sql($t['TABLE_NAME']) . "'";
		}
		unset($t);
		$q = $wfdb->querySelect("SHOW TABLE STATUS WHERE Name IN (" . implode(',', $tables) . ')');
		if ($q):
			$databaseCols = count($q[0]);
			?>
			<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-database-tables') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-database-tables') ?>">
				<div class="wf-block-header">
					<div class="wf-block-header-content">
						<div class="wf-block-title">
							<strong><?php esc_html_e('Database Tables', 'wordfence') ?></strong>
							<span class="wf-text-small"><?php esc_html_e('Database table names, sizes, timestamps, and other metadata.', 'wordfence') ?></span>
						</div>
						<div class="wf-block-header-action">
							<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-database-tables') ? 'true' : 'false'); ?>" tabindex="0"></div>
						</div>
					</div>
				</div>
				<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
					<ul class="wf-block-list wf-padding-add-left-large wf-padding-add-right-large">
						<li style="border-bottom: 1px solid #e2e2e2;">
							<div style="width: 75%; min-width: 300px;"><?php esc_html_e('Wordfence Table Check', 'wordfence'); ?></div>
							<div class="wf-right">
								<?php if ($total > 250): ?>
									<div class="wf-result-info"><?php esc_html_e('Unable to verify - table count too high', 'wordfence'); ?></div>
								<?php else:
									$hasAll = true;
									$schemaTables = wfSchema::tableList();
									$existingTables = wfUtils::array_column($q, 'Name');
									if (WFWAF_IS_WINDOWS) { $existingTables = wfUtils::array_strtolower($existingTables); } //Windows MySQL installations are case-insensitive
									$missingTables = array();
									foreach ($schemaTables as $t) {
										$table = wfDB::networkTable($t);
										if (WFWAF_IS_WINDOWS) { $table = strtolower($table); }
										if (!in_array($table, $existingTables)) {
											$hasAll = false;
											$missingTables[] = $t;
										}
									}

									foreach (
										array(
											\WordfenceLS\Controller_DB::TABLE_2FA_SECRETS,
											\WordfenceLS\Controller_DB::TABLE_SETTINGS,
										) as $t) {
										$table = \WordfenceLS\Controller_DB::network_table($t);
										if (!in_array($table, $existingTables)) {
											$hasAll = false;
											$missingTables[] = $t;
										}
									}

									if ($hasAll): ?>
									<div class="wf-result-success"><?php esc_html_e('All Tables Exist', 'wordfence'); ?></div>
									<?php else: ?>
									<div class="wf-result-error"><?php echo esc_html(sprintf(
											/* translators: 1. WordPress table prefix. 2. Wordfence table case. 3. List of database tables. */
											__('Tables missing (prefix %1$s, %2$s): %3$s', 'wordfence'), wfDB::networkPrefix(), wfSchema::usingLowercase() ? __('lowercase', 'wordfence') : __('regular case', 'wordfence'), implode(', ', $missingTables))); ?></div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</li>
					</ul>
					<div class="wf-add-top-large" style="max-width: 100%; overflow: auto; padding: 1px;">
						<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
							<tbody class="thead thead-subhead" style="font-size: 85%">
							<?php
							$val = wfUtils::array_first($q);
							$actualKeyOrder = array_keys($val);
							$preferredKeyOrder = array('Name', 'Comment', 'Engine', 'Rows', 'Avg_row_length', 'Data_length', 'Index_length', 'Auto_increment', 'Create_time', 'Row_format', 'Collation', 'Version', 'Max_data_length', 'Data_free', 'Update_time', 'Check_time', 'Checksum', 'Create_options');
							$leftoverKeys = array();
							$displayKeyOrder = array();
							foreach ($preferredKeyOrder as $k) {
								if (in_array($k, $actualKeyOrder)) {
									$displayKeyOrder[] = $k;
								}
							}
							
							$diff = array_diff($actualKeyOrder, $preferredKeyOrder);
							$displayKeyOrder = array_merge($displayKeyOrder, $diff);
							
							?>
							<tr>
								<?php foreach ($displayKeyOrder as $tkey): ?>
									<th><?php echo esc_html($tkey) ?></th>
								<?php endforeach; ?>
							</tr>
							</tbody>
							<tbody style="font-size: 85%">
							<?php
							$count = 0;
							foreach ($q as $val) {
								?>
								<tr>
								<?php foreach ($displayKeyOrder as $tkey): ?>
									<td><?php if (isset($val[$tkey])) { echo esc_html($val[$tkey]); } ?></td>
								<?php endforeach; ?>
								</tr>
								<?php
								$count++;
								if ($count >= 250 && $total > $count) {
									?>
									<tr>
										<td colspan="<?php echo $databaseCols; ?>"><?php echo esc_html(sprintf(/* translators: Row/record count. */ __('and %d more', 'wordfence'), $total - $count)); ?></td>
									</tr>
									<?php
									break;
								}
							}
							?>
							</tbody>

						</table>
					</div>

				</div>
			</div>
		<?php endif ?>
		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-log-files') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-log-files') ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Log Files', 'wordfence') ?></strong>
						<span class="wf-text-small"><?php esc_html_e('PHP error logs generated by your site, if enabled by your host.', 'wordfence') ?></span>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-log-files') ? 'true' : 'false'); ?>" tabindex="0"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix wf-padding-no-left wf-padding-no-right">
				<div style="max-width: 100%; overflow: auto; padding: 1px;">
					<table class="wf-striped-table"<?php echo !empty($inEmail) ? ' border=1' : '' ?>>
						<tbody class="thead thead-subhead" style="font-size: 85%">
						<tr>
							<th><?php esc_html_e('File', 'wordfence'); ?></th>
							<th><?php esc_html_e('Download', 'wordfence'); ?></th>
						</tr>
						</tbody>
						<tbody style="font-size: 85%">
						<?php
						$errorLogs = wfErrorLogHandler::getErrorLogs();
						if (count($errorLogs) < 1): ?>
							<tr>
								<td colspan="2"><em><?php esc_html_e('No log files found.', 'wordfence'); ?></em></td>
							</tr>
						<?php else:
							foreach ($errorLogs as $log => $readable): ?>
								<?php
								$metadata = array();
								if (is_callable('filesize')) {
									$rawSize = @filesize($log);
									if ($rawSize !== false) {
										$metadata[] = wfUtils::formatBytes(filesize($log));
									}
								}
								
								if (is_callable('lstat')) {
									$rawStat = @lstat($log);
									if (is_array($rawStat) && isset($rawStat['mtime'])) {
										$ts = $rawStat['mtime'];
										$utc = new DateTimeZone('UTC');
										$dtStr = gmdate("c", (int) $ts); //Have to do it this way because of PHP 5.2
										$dt = new DateTime($dtStr, $utc);
										$metadata[] = $dt->format('M j, Y G:i:s') . ' ' . __('UTC', 'wordfence');
									}
								}
								
								$shortLog = $log;
								if (strpos($shortLog, ABSPATH) === 0) {
									$shortLog = '~/' . substr($shortLog, strlen(ABSPATH));
								}
								?>
								<tr>
									<td style="width: 100%"><?php echo esc_html($shortLog); if (!empty($metadata)) { echo ' (' . esc_html(implode(', ', $metadata)) . ')'; } ?></td>
									<td style="white-space: nowrap; text-align: right;"><?php echo($readable ? '<a href="#" data-logfile="' . esc_attr($log) . '" class="downloadLogFile" target="_blank" rel="noopener noreferrer">' . esc_html__('Download', 'wordfence') . '</a>' : '<em>' . esc_html__('Requires downloading from the server directly', 'wordfence') . '</em>'); ?></td>
								</tr>
							<?php endforeach;
						endif; ?>
						</tbody>

					</table>
				</div>
			</div>
		</div>
	</div>
	
	<?php
	if (!empty($inEmail)) {
		echo '<h1>' . esc_html__('Scan Issues', 'wordfence') . "</h1>\n";
		$issues = wfIssues::shared()->getIssues(0, 50, 0, 50);
		$issueCounts = array_merge(array('new' => 0, 'ignoreP' => 0, 'ignoreC' => 0), wfIssues::shared()->getIssueCounts());
		$issueTypes = wfIssues::validIssueTypes();
		
		echo '<h2>' . esc_html(sprintf(/* translators: Number of scan issues. */ __('New Issues (%d total)', 'wordfence'), $issueCounts['new'])) . "</h2>\n";
		if (isset($issues['new']) && count($issues['new'])) {
			foreach ($issues['new'] as $i) {
				if (!in_array($i['type'], $issueTypes)) {
					continue;
				}
				
				$viewContent = '';
				try {
					$viewContent = wfView::create('scanner/issue-' . $i['type'], array('textOutput' => $i))->render();
				}
				catch (wfViewNotFoundException $e) {
					//Ignore -- should never happen since we validate the type
				}
				
				if (!empty($viewContent)) {
					echo nl2br($viewContent) . "<br><br>\n";
				}
			}
		}
		else {
			echo '<h1>' . esc_html__('No New Issues', 'wordfence') . "</h1>\n";
		}
	}
	?>

	<?php if (!empty($inEmail)): ?>
		<?php phpinfo(); ?>
	<?php endif ?>

	<?php if (!empty($emailForm)): ?>
		<div class="wf-diagnostics-wrapper">
			<div id="wf-diagnostics-other-tests" class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-other-tests') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-other-tests') ?>">
				<div class="wf-block-header">
					<div class="wf-block-header-content">
						<div class="wf-block-title">
							<strong><?php esc_html_e('Other Tests', 'wordfence') ?></strong>
							<span class="wf-text-small"><?php esc_html_e('System configuration, memory test, send test email from this server.', 'wordfence') ?></span>
						</div>
						<div class="wf-block-header-action">
							<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-other-tests') ? 'true' : 'false'); ?>" tabindex="0"></div>
						</div>
					</div>
				</div>
				<div class="wf-block-content wf-clearfix">
					<ul class="wf-block-list">
						<li>
							<span>
								<a href="<?php echo wfUtils::siteURLRelative(); ?>?_wfsf=sysinfo&nonce=<?php echo wp_create_nonce('wp-ajax'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Click to view your system\'s configuration in a new window', 'wordfence'); ?></a>
								<a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DIAGNOSTICS_SYSTEM_CONFIGURATION); ?>" target="_blank" rel="noopener noreferrer" class="wfhelp wf-inline-help"></a>
							</span>
						</li>
						<li>
							<span>
								<a href="<?php echo wfUtils::siteURLRelative(); ?>?_wfsf=testmem&nonce=<?php echo wp_create_nonce('wp-ajax'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Test your WordPress host\'s available memory', 'wordfence'); ?></a>
							<a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DIAGNOSTICS_TEST_MEMORY); ?>" target="_blank" rel="noopener noreferrer" class="wfhelp wf-inline-help"></a>
							</span>
						</li>
						<li>
							<span>
								<?php esc_html_e('Send a test email from this WordPress server to an email address:', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DIAGNOSTICS_TEST_EMAIL); ?>" target="_blank" rel="noopener noreferrer" class="wfhelp wf-inline-help"></a>
								<input type="text" id="testEmailDest" value="" size="20" maxlength="255" class="wfConfigElem"/>
								<input class="wf-btn wf-btn-default wf-btn-sm" type="button" value="<?php esc_attr_e('Send Test Email', 'wordfence'); ?>" onclick="WFAD.sendTestEmail(jQuery('#testEmailDest').val());"/>
							</span>
						</li>
						<li>
							<span>
								<?php esc_html_e('Send a test activity report email:', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DIAGNOSTICS_TEST_ACTIVITY_REPORT); ?>" target="_blank" rel="noopener noreferrer" class="wfhelp wf-inline-help"></a>
								<input type="email" id="email_summary_email_address_debug" value="" size="20" maxlength="255" class="wfConfigElem"/>
								<input class="wf-btn wf-btn-default wf-btn-sm" type="button" value="<?php esc_attr_e('Send Test Activity Report', 'wordfence'); ?>" onclick="WFAD.sendTestActivityReport(jQuery('#email_summary_email_address_debug').val());"/>
							</span>
						</li>
						<li>
							<span>
								<?php esc_html_e('Clear all Wordfence Central connection data', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DIAGNOSTICS_REMOVE_CENTRAL_DATA); ?>" target="_blank" rel="noopener noreferrer" class="wfhelp wf-inline-help"></a>
								<input class="wf-btn wf-btn-default wf-btn-sm" type="button" value="<?php esc_attr_e('Clear Connection Data', 'wordfence'); ?>" onclick="WFAD.ajax('wordfence_wfcentral_disconnect', {}, function() { WFAD.colorboxModal((self.isSmallScreen ? '300px' : '400px'), 'Successfully removed data', 'All associated Wordfence Central data has been removed from the database.'); });"/>
							</span>
						</li>
					</ul>

				</div>
			</div>

			<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('wf-diagnostics-debugging-options') ? ' wf-active' : '') ?>" data-persistence-key="<?php echo esc_attr('wf-diagnostics-debugging-options') ?>">
				<div class="wf-block-header">
					<div class="wf-block-header-content">
						<div class="wf-block-title">
							<strong><?php esc_html_e('Debugging Options', 'wordfence') ?></strong>
						</div>
						<div class="wf-block-header-action">
							<div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('wf-diagnostics-debugging-options') ? 'true' : 'false'); ?>" tabindex="0"></div>
						</div>
					</div>
				</div>
				<div class="wf-block-content wf-clearfix">
					<form action="#" id="wfDebuggingConfigForm">
						<ul class="wf-block-list">
							<li>
								<?php
								echo wfView::create('options/option-toggled', array(
									'optionName'    => 'debugOn',
									'enabledValue'  => 1,
									'disabledValue' => 0,
									'value'         => $w->get('debugOn') ? 1 : 0,
									'title'         => __('Enable debugging mode (increases database load)', 'wordfence'),
									'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_DIAGNOSTICS_OPTION_DEBUGGING_MODE),
								))->render();
								?>
							</li>
							<li>
								<?php
								echo wfView::create('options/option-toggled', array(
									'optionName'    => 'startScansRemotely',
									'enabledValue'  => 1,
									'disabledValue' => 0,
									'value'         => $w->get('startScansRemotely') ? 1 : 0,
									'title'         => __('Start all scans remotely (Try this if your scans aren\'t starting and your site is publicly accessible)', 'wordfence'),
									'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_DIAGNOSTICS_OPTION_REMOTE_SCANS),
								))->render();
								?>
							</li>
							<li>
								<?php
								echo wfView::create('options/option-toggled', array(
									'optionName'    => 'ssl_verify',
									'enabledValue'  => 1,
									'disabledValue' => 0,
									'value'         => $w->get('ssl_verify') ? 1 : 0,
									'title'         => __('Enable SSL Verification (Disable this if you are consistently unable to connect to the Wordfence servers.)', 'wordfence'),
									'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_DIAGNOSTICS_OPTION_SSL_VERIFICATION),
								))->render();
								?>
							</li>
							<li>
								<?php
								echo wfView::create('options/option-toggled', array(
									'optionName'    => 'avoid_php_input',
									'enabledValue'  => 1,
									'disabledValue' => 0,
									'value'         => wfWAF::getInstance()->getStorageEngine()->getConfig('avoid_php_input', false) ? 1 : 0,
									'title'         => __('Disable reading of php://input', 'wordfence'),
									'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_DIAGNOSTICS_OPTION_DISABLE_PHP_INPUT),
								))->render();
								?>
							</li>
							<li>
								<?php
								echo wfView::create('options/option-toggled', array(
									'optionName'    => 'betaThreatDefenseFeed',
									'enabledValue'  => 1,
									'disabledValue' => 0,
									'value'         => $w->get('betaThreatDefenseFeed') ? 1 : 0,
									'title'         => __('Enable beta threat defense feed', 'wordfence'),
									'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_DIAGNOSTICS_OPTION_BETA_TDF),
								))->render();
								?>
							</li>
							<li>
								<?php
								echo wfView::create('options/option-toggled', array(
									'optionName'    => 'wordfenceI18n',
									'enabledValue'  => 1,
									'disabledValue' => 0,
									'value'         => $w->get('wordfenceI18n') ? 1 : 0,
									'title'         => 'Enable Wordfence translations',
									'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_DIAGNOSTICS_OPTION_WORDFENCE_TRANSLATIONS),
								))->render();
								?>
							</li>
							<li>
								<p>
									<a id="wf-restore-defaults" class="wf-btn wf-btn-default wf-btn-callout-subtle" href="#" data-restore-defaults-section="<?php echo esc_attr(wfConfig::OPTIONS_TYPE_DIAGNOSTICS); ?>"><?php esc_html_e('Restore Defaults', 'wordfence'); ?></a>
									<a id="wf-cancel-changes" class="wf-btn wf-btn-default wf-btn-callout-subtle wf-disabled" href="#"><?php esc_html_e('Cancel Changes', 'wordfence'); ?></a>
									<a id="wf-save-changes" class="wf-btn wf-btn-primary wf-btn-callout-subtle wf-disabled" href="#"><?php esc_html_e('Save Changes', 'wordfence'); ?></a>
								</p>
							</li>
						</ul>
					</form>
				</div>
			</div>
		</div>

	<?php endif ?>
</div>
<div class="wf-scrollTop">
	<a href="javascript:void(0);"><i class="wf-ionicons wf-ion-chevron-up"></i></a>
</div>
<script type="text/x-jquery-template" id="wfTmpl_restoreDefaultsPrompt">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Confirm Restore Defaults', 'wordfence'),
		'message' => __('Are you sure you want to restore the default Diagnostics settings? This will undo any custom changes you have made to the options on this page.', 'wordfence'),
		'primaryButton' => array('id' => 'wf-restore-defaults-prompt-cancel', 'label' => __('Cancel', 'wordfence'), 'link' => '#'),
		'secondaryButtons' => array(array('id' => 'wf-restore-defaults-prompt-confirm', 'labelHTML' => wp_kses(__('Restore<span class="wf-hidden-xs"> Defaults</span>', 'wordfence'), array('span'=>array('class'=>array()))), 'link' => '#')),
	))->render();
	?>
</script>