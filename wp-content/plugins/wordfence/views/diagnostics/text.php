<?php
if (!defined('WORDFENCE_VERSION')) {
	exit;
}

if (!isset($diagnostic)) {
	$diagnostic = new wfDiagnostic;
}
if (!isset($plugins)) {
	$plugins = get_plugins();
}
if (!isset($activePlugins)) {
	$activePlugins = array_flip(get_option('active_plugins'));
}
if (!isset($activeNetworkPlugins)) {
	$activeNetworkPlugins = is_multisite() ? array_flip(wp_get_active_network_plugins()) : array();
}
if (!isset($muPlugins)) {
	$muPlugins = get_mu_plugins();
}
if (!isset($themes)) {
	$themes = wp_get_themes();
}
if (!isset($currentTheme)) {
	$currentTheme = wp_get_theme();
}

$w = new wfConfig();

?>

<?php
foreach ($diagnostic->getResults() as $title => $tests):
	$table = array(
		array($title, ''),
	);
	foreach ($tests['results'] as $result) {

		$infoOnly = isset($result['infoOnly']) && $result['infoOnly'];
		$message = '';
		if ($infoOnly) {
			$message = '';
		} else if ($result['test'] && $result['message'] !== 'OK') {
			$message = '[OK] ';
		} else if (!$result['test'] && $result['message'] !== 'FAIL') {
			$message = '[FAIL] ';
		}

		$message .= strip_tags($result['message'] . (isset($result['detail']) && !empty($result['detail']) ? "\nAdditional Detail:\n" . $result['detail'] : ''));

		$table[] = array(
			strip_tags((is_array($result['label']) && isset($result['label']['raw']) && $result['label']['raw']) ? $result['label']['value'] : $result['label']),
			$message,
		);
	}
	echo wfHelperString::plainTextTable($table) . "\n\n";
endforeach;
?>

## <?php esc_html_e('IP Detection', 'wordfence') ?>: <?php esc_html_e('Methods of detecting a visitor\'s IP address.', 'wordfence') ?> ##

<?php
$howGet = wfConfig::get('howGetIPs', false);
list($currentIP, $currentServerVarForIP) = wfUtils::getIPAndServerVariable();
$howGetHasErrors = $howGet && (!$currentServerVarForIP || $howGet !== $currentServerVarForIP);

$table = array(
	array(
		__('IPs', 'wordfence'),
		__('Value', 'wordfence'),
		__('Used', 'wordfence'),
	),
);

$serverVariables = array(
	'REMOTE_ADDR'           => 'REMOTE_ADDR',
	'HTTP_CF_CONNECTING_IP' => 'CF-Connecting-IP',
	'HTTP_X_REAL_IP'        => 'X-Real-IP',
	'HTTP_X_FORWARDED_FOR'  => 'X-Forwarded-For',
);
foreach (wfUtils::getAllServerVariableIPs() as $variable => $ip) {

	$ipValue = '';

	if (!$ip) {
		$ipValue = __('(not set)', 'wordfence');
	} elseif (is_array($ip)) {
		$ipValue = array_map('strip_tags', $ip);
		$ipValue = str_replace($currentIP, "**$currentIP**", implode(', ', $ipValue));
	} else {
		$ipValue = strip_tags($ip);
	}

	$used = '';
	if ($currentServerVarForIP && $currentServerVarForIP === $variable) {
		$used = __('In use', 'wordfence');
	} else if ($howGet === $variable) {
		$used = __('Configured but not valid', 'wordfence');
	}
	$table[] = array(
		isset($serverVariables[$variable]) ? $serverVariables[$variable] : $variable,
		$ipValue,
		$used,
	);
}

$table[] = array(
	__('Trusted Proxies', 'wordfence'),
	strip_tags(implode(', ', explode("\n", wfConfig::get('howGetIPs_trusted_proxies', '')))),
	'',
);

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('WordPress Settings', 'wordfence') ?>: <?php esc_html_e('WordPress version and internal settings/constants.', 'wordfence') ?> ##

<?php
require(ABSPATH . 'wp-includes/version.php');
$postRevisions = (defined('WP_POST_REVISIONS') ? WP_POST_REVISIONS : true);
$wordPressValues = array(
	'WordPress Version'              => array('description' => '', 'value' => $wp_version),
	'Multisite'                      => array('description' => __('Return value of is_multisite()', 'wordfence'), 'value' => is_multisite() ? __('Yes', 'wordfence') : __('No', 'wordfence')),
	'ABSPATH'                        => __('WordPress base path', 'wordfence'),
	'WP_DEBUG'                       => array('description' => __('WordPress debug mode', 'wordfence'), 'value' => (defined('WP_DEBUG') && WP_DEBUG ? __('On', 'wordfence') : __('Off', 'wordfence'))),
	'WP_DEBUG_LOG'                   => array('description' => __('WordPress error logging override', 'wordfence'), 'value' => defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'Enabled' : 'Disabled') : __('(not set)', 'wordfence')),
	'WP_DEBUG_DISPLAY'               => array('description' => __('WordPress error display override', 'wordfence'), 'value' => defined('WP_DEBUG_DISPLAY') ? (WP_DEBUG_LOG ? 'Enabled' : 'Disabled') : __('(not set)', 'wordfence')),
	'SCRIPT_DEBUG'                   => array('description' => __('WordPress script debug mode', 'wordfence'), 'value' => (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? __('On', 'wordfence') : __('Off', 'wordfence'))),
	'SAVEQUERIES'                    => array('description' => __('WordPress query debug mode', 'wordfence'), 'value' => (defined('SAVEQUERIES') && SAVEQUERIES ? __('On', 'wordfence') : __('Off', 'wordfence'))),
	'DB_CHARSET'                     => __('Database character set', 'wordfence'),
	'DB_COLLATE'                     => __('Database collation', 'wordfence'),
	'WP_SITEURL'                     => __('Explicitly set site URL', 'wordfence'),
	'WP_HOME'                        => __('Explicitly set blog URL', 'wordfence'),
	'WP_CONTENT_DIR'                 => array('description' => __('"wp-content" folder is in default location', 'wordfence'), 'value' => (realpath(WP_CONTENT_DIR) === realpath(ABSPATH . 'wp-content') ? __('Yes', 'wordfence') : sprintf(__('No: %s', 'wordfence'), WP_CONTENT_DIR))),
	'WP_CONTENT_URL'                 => __('URL to the "wp-content" folder', 'wordfence'),
	'WP_PLUGIN_DIR'                  => array('description' => __('"plugins" folder is in default location', 'wordfence'), 'value' => (realpath(WP_PLUGIN_DIR) === realpath(ABSPATH . 'wp-content/plugins') ? __('Yes', 'wordfence') : sprintf(__('No: %s', 'wordfence'), WP_PLUGIN_DIR))),
	'WP_LANG_DIR'                    => array('description' => __('"languages" folder is in default location', 'wordfence'), 'value' => (realpath(WP_LANG_DIR) === realpath(ABSPATH . 'wp-content/languages') ? __('Yes', 'wordfence') : sprintf(__('No: %s', 'wordfence'), WP_LANG_DIR))),
	'WPLANG'                         => __('Language choice', 'wordfence'),
	'UPLOADS'                        => __('Custom upload folder location', 'wordfence'),
	'TEMPLATEPATH'                   => array('description' => __('Theme template folder override', 'wordfence'), 'value' => (defined('TEMPLATEPATH') && realpath(get_template_directory()) !== realpath(TEMPLATEPATH) ? sprintf(__('Overridden: %s', 'wordfence'), TEMPLATEPATH) : __('(not set)', 'wordfence'))),
	'STYLESHEETPATH'                 => array('description' => __('Theme stylesheet folder override', 'wordfence'), 'value' => (defined('STYLESHEETPATH') && realpath(get_stylesheet_directory()) !== realpath(STYLESHEETPATH) ? sprintf(__('Overridden: %s', 'wordfence'), STYLESHEETPATH) : __('(not set)', 'wordfence'))),
	'AUTOSAVE_INTERVAL'              => __('Post editing automatic saving interval', 'wordfence'),
	'WP_POST_REVISIONS'              => array('description' => __('Post revisions saved by WordPress', 'wordfence'), 'value' => is_numeric($postRevisions) ? $postRevisions : ($postRevisions ? __('Unlimited', 'wordfence') : __('None', 'wordfence'))),
	'COOKIE_DOMAIN'                  => __('WordPress cookie domain', 'wordfence'),
	'COOKIEPATH'                     => __('WordPress cookie path', 'wordfence'),
	'SITECOOKIEPATH'                 => __('WordPress site cookie path', 'wordfence'),
	'ADMIN_COOKIE_PATH'              => __('WordPress admin cookie path', 'wordfence'),
	'PLUGINS_COOKIE_PATH'            => __('WordPress plugins cookie path', 'wordfence'),
	'NOBLOGREDIRECT'                 => __('URL redirected to if the visitor tries to access a nonexistent blog', 'wordfence'),
	'CONCATENATE_SCRIPTS'            => array('description' => __('Concatenate JavaScript files', 'wordfence'), 'value' => (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'WP_MEMORY_LIMIT'                => __('WordPress memory limit', 'wordfence'),
	'WP_MAX_MEMORY_LIMIT'            => __('Administrative memory limit', 'wordfence'),
	'WP_CACHE'                       => array('description' => __('Built-in caching', 'wordfence'), 'value' => (defined('WP_CACHE') && WP_CACHE ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence'))),
	'CUSTOM_USER_TABLE'              => array('description' => __('Custom "users" table', 'wordfence'), 'value' => (defined('CUSTOM_USER_TABLE') ? sprintf(__('Set: %s', 'wordfence'), CUSTOM_USER_TABLE) : __('(not set)', 'wordfence'))),
	'CUSTOM_USER_META_TABLE'         => array('description' => __('Custom "usermeta" table', 'wordfence'), 'value' => (defined('CUSTOM_USER_META_TABLE') ? sprintf(__('Set: %s', 'wordfence'), CUSTOM_USER_META_TABLE) : __('(not set)', 'wordfence'))),
	'FS_CHMOD_DIR'                   => array('description' => __('Overridden permissions for a new folder', 'wordfence'), 'value' => defined('FS_CHMOD_DIR') ? decoct(FS_CHMOD_DIR) : __('(not set)', 'wordfence')),
	'FS_CHMOD_FILE'                  => array('description' => __('Overridden permissions for a new file', 'wordfence'), 'value' => defined('FS_CHMOD_FILE') ? decoct(FS_CHMOD_FILE) : __('(not set)', 'wordfence')),
	'ALTERNATE_WP_CRON'              => array('description' => __('Alternate WP cron', 'wordfence'), 'value' => (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence'))),
	'DISABLE_WP_CRON'                => array('description' => __('WP cron status', 'wordfence'), 'value' => (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? __('Disabled', 'wordfence') : __('Enabled', 'wordfence'))),
	'WP_CRON_LOCK_TIMEOUT'           => __('Cron running frequency lock', 'wordfence'),
	'EMPTY_TRASH_DAYS'               => array('description' => __('Interval the trash is automatically emptied at in days', 'wordfence'), 'value' => (EMPTY_TRASH_DAYS > 0 ? EMPTY_TRASH_DAYS : __('Never', 'wordfence'))),
	'WP_ALLOW_REPAIR'                => array('description' => __('Automatic database repair', 'wordfence'), 'value' => (defined('WP_ALLOW_REPAIR') && WP_ALLOW_REPAIR ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence'))),
	'DO_NOT_UPGRADE_GLOBAL_TABLES'   => array('description' => __('Do not upgrade global tables', 'wordfence'), 'value' => (defined('DO_NOT_UPGRADE_GLOBAL_TABLES') && DO_NOT_UPGRADE_GLOBAL_TABLES ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'DISALLOW_FILE_EDIT'             => array('description' => __('Disallow plugin/theme editing', 'wordfence'), 'value' => (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'DISALLOW_FILE_MODS'             => array('description' => __('Disallow plugin/theme update and installation', 'wordfence'), 'value' => (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'IMAGE_EDIT_OVERWRITE'           => array('description' => __('Overwrite image edits when restoring the original', 'wordfence'), 'value' => (defined('IMAGE_EDIT_OVERWRITE') && IMAGE_EDIT_OVERWRITE ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'FORCE_SSL_ADMIN'                => array('description' => __('Force SSL for administrative logins', 'wordfence'), 'value' => (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'WP_HTTP_BLOCK_EXTERNAL'         => array('description' => __('Block external URL requests', 'wordfence'), 'value' => (defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'WP_ACCESSIBLE_HOSTS'            => __('Allowlisted hosts', 'wordfence'),
	'WP_AUTO_UPDATE_CORE'            => array('description' => __('Automatic WP Core updates', 'wordfence'), 'value' => defined('WP_AUTO_UPDATE_CORE') ? (is_bool(WP_AUTO_UPDATE_CORE) ? (WP_AUTO_UPDATE_CORE ? __('Everything', 'wordfence') : __('None', 'wordfence')) : WP_AUTO_UPDATE_CORE) : __('Default', 'wordfence')),
	'WP_PROXY_HOST'                  => array('description' => __('Hostname for a proxy server', 'wordfence'), 'value' => defined('WP_PROXY_HOST') ? WP_PROXY_HOST : __('(not set)', 'wordfence')),
	'WP_PROXY_PORT'                  => array('description' => __('Port for a proxy server', 'wordfence'), 'value' => defined('WP_PROXY_PORT') ? WP_PROXY_PORT : __('(not set)', 'wordfence')),
	'MULTISITE'                      => array('description' => __('Multisite enabled', 'wordfence'), 'value' => defined('MULTISITE') ? (MULTISITE ? __('Yes', 'wordfence') : __('No', 'wordfence')) : __('(not set)', 'wordfence')),
	'WP_ALLOW_MULTISITE'             => array('description' => __('Multisite/network ability enabled', 'wordfence'), 'value' => (defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
	'SUNRISE'                        => array('description' => __('Multisite enabled, WordPress will load the /wp-content/sunrise.php file', 'wordfence'), 'value' => defined('SUNRISE') ? __('Yes', 'wordfence') : __('(not set)', 'wordfence')),
	'SUBDOMAIN_INSTALL'              => array('description' => __('Multisite enabled, subdomain installation constant', 'wordfence'), 'value' => defined('SUBDOMAIN_INSTALL') ? (SUBDOMAIN_INSTALL ? __('Yes', 'wordfence') : __('No', 'wordfence')) : __('(not set)', 'wordfence')),
	'VHOST'                          => array('description' => __('Multisite enabled, Older subdomain installation constant', 'wordfence'), 'value' => defined('VHOST') ? (VHOST == 'yes' ? __('Yes', 'wordfence') : __('No', 'wordfence')) : __('(not set)', 'wordfence')),
	'DOMAIN_CURRENT_SITE'            => __('Defines the multisite domain for the current site', 'wordfence'),
	'PATH_CURRENT_SITE'              => __('Defines the multisite path for the current site', 'wordfence'),
	'BLOG_ID_CURRENT_SITE'           => __('Defines the multisite database ID for the current site', 'wordfence'),
	'WP_DISABLE_FATAL_ERROR_HANDLER' => array('description' => __('Disable the fatal error handler', 'wordfence'), 'value' => (defined('WP_DISABLE_FATAL_ERROR_HANDLER') && WP_DISABLE_FATAL_ERROR_HANDLER ? __('Yes', 'wordfence') : __('No', 'wordfence'))),
);

$table = array(
	array(
		__('Setting Name', 'wordfence'),
		__('Description', 'wordfence'),
		__('Value', 'wordfence'),
	),
);

foreach ($wordPressValues as $settingName => $settingData) {
	$escapedName = strip_tags($settingName);
	$escapedDescription = '';
	$escapedValue = __('(not set)', 'wordfence');
	if (is_array($settingData)) {
		$escapedDescription = strip_tags($settingData['description']);
		if (isset($settingData['value'])) {
			$escapedValue = strip_tags($settingData['value']);
		}
	} else {
		$escapedDescription = strip_tags($settingData);
		if (defined($settingName)) {
			$escapedValue = strip_tags(constant($settingName));
		}
	}

	$table[] = array(
		$escapedName,
		$escapedDescription,
		$escapedValue,
	);
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('WordPress Plugins', 'wordfence') ?>: <?php esc_html_e('Status of installed plugins.', 'wordfence') ?> ##

<?php

$table = array(
	array(__('Name', 'wordfence'), __('Status', 'wordfence')),
);

foreach ($plugins as $plugin => $pluginData) {
	$slug = $plugin;
	if (preg_match('/^([^\/]+)\//', $plugin, $tableMatches)) {
		$slug = $tableMatches[1];
	} else if (preg_match('/^([^\/.]+)\.php$/', $plugin, $tableMatches)) {
		$slug = $tableMatches[1];
	}

	$name = strip_tags(sprintf('%s (%s)', $pluginData['Name'], $slug));
	if (!empty($pluginData['Version'])) {
		$name .= ' - ' . strip_tags(sprintf(__('Version %s', 'wordfence'), $pluginData['Version']));
	}

	if (array_key_exists(trailingslashit(WP_PLUGIN_DIR) . $plugin, $activeNetworkPlugins)) {
		$status = __('Network Activated', 'wordfence');
	} elseif (array_key_exists($plugin, $activePlugins)) {
		$status = __('Active', 'wordfence');
	} else {
		$status = __('Inactive', 'wordfence');
	}
	$table[] = array(
		$name,
		$status,
	);
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Must-Use WordPress Plugins', 'wordfence') ?>: <?php esc_html_e('WordPress "mu-plugins" that are always active, including those provided by hosts.', 'wordfence') ?> ##

<?php

$table = array(
	array(__('Name', 'wordfence'), __('Status', 'wordfence')),
);

if (!empty($muPlugins)) {
	foreach ($muPlugins as $plugin => $pluginData) {
		$slug = $plugin;
		if (preg_match('/^([^\/]+)\//', $plugin, $tableMatches)) {
			$slug = $tableMatches[1];
		} else if (preg_match('/^([^\/.]+)\.php$/', $plugin, $tableMatches)) {
			$slug = $tableMatches[1];
		}

		$name = strip_tags(sprintf('%s (%s)', $pluginData['Name'], $slug));
		if (!empty($pluginData['Version'])) {
			$name .= ' - ' . strip_tags(sprintf(__('Version %s', 'wordfence'), $pluginData['Version']));
		}

		$table[] = array(
			$name,
			__('Active', 'wordfence'),
		);
	}
} else {
	$table[] = array(
		__('No MU-Plugins', 'wordfence'),
		'',
	);
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Drop-In WordPress Plugins', 'wordfence') ?>: <?php esc_html_e('WordPress "drop-in" plugins that are active.', 'wordfence') ?> ##

<?php

//Taken from plugin.php and modified to always show multisite drop-ins
$dropins = array(
	'advanced-cache.php'      => array(__('Advanced caching plugin'), 'WP_CACHE'), // WP_CACHE
	'db.php'                  => array(__('Custom database class'), true), // auto on load
	'db-error.php'            => array(__('Custom database error message'), true), // auto on error
	'install.php'             => array(__('Custom installation script'), true), // auto on installation
	'maintenance.php'         => array(__('Custom maintenance message'), true), // auto on maintenance
	'object-cache.php'        => array(__('External object cache'), true), // auto on load
	'php-error.php'           => array(__('Custom PHP error message'), true), // auto on error
	'fatal-error-handler.php' => array(__('Custom PHP fatal error handler'), true), // auto on error
);
$dropins['sunrise.php'] = array(__('Executed before Multisite is loaded'), is_multisite() && 'SUNRISE'); // SUNRISE
$dropins['blog-deleted.php'] = array(__('Custom site deleted message'), is_multisite()); // auto on deleted blog
$dropins['blog-inactive.php'] = array(__('Custom site inactive message'), is_multisite()); // auto on inactive blog
$dropins['blog-suspended.php'] = array(__('Custom site suspended message'), is_multisite()); // auto on archived or spammed blog

$table = array(
	array(__('Name', 'wordfence'), __('Status', 'wordfence')),
);

foreach ($dropins as $file => $data) {
	$active = file_exists(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file) && is_readable(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file) && $data[1];
	$table[] = array(
		sprintf('%s (%s)', $data[0], $file),
		$active ? __('Active', 'wordfence') : __('Inactive', 'wordfence'),
	);
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Themes', 'wordfence') ?>: <?php esc_html_e('Status of installed themes.', 'wordfence') ?> ##

<?php

$table = array(
	array(__('Name', 'wordfence'), __('Status', 'wordfence')),
);

if (!empty($themes)) {
	foreach ($themes as $theme => $themeData) {
		$slug = $theme;
		if (preg_match('/^([^\/]+)\//', $theme, $tableMatches)) {
			$slug = $tableMatches[1];
		} else if (preg_match('/^([^\/.]+)\.php$/', $theme, $tableMatches)) {
			$slug = $tableMatches[1];
		}

		$name = strip_tags(sprintf('%s (%s)', $themeData['Name'], $slug));
		if (!empty($themeData['Version'])) {
			$name .= ' - ' . strip_tags(sprintf(__('Version %s', 'wordfence'), $themeData['Version']));
		}

		if ($currentTheme instanceof WP_Theme && $theme === $currentTheme->get_stylesheet()) {
			$status = __('Active', 'wordfence');
		} else {
			$status = __('Inactive', 'wordfence');
		}
		$table[] = array(
			$name,
			$status,
		);
	}
} else {
	$table[] = array(
		__('No Themes', 'wordfence'),
		''
	);
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Cron Jobs', 'wordfence') ?>: <?php esc_html_e('List of WordPress cron jobs scheduled by WordPress, plugins, or themes.', 'wordfence') ?> ##

<?php
$cron = _get_cron_array();

$table = array(
	array(__('Run Time', 'wordfence'), __('Job', 'wordfence')),
);
foreach ($cron as $timestamp => $values) {
	if (is_array($values)) {
		foreach ($values as $cron_job => $v) {
			if (is_numeric($timestamp)) {
				$overdue = ((time() - 1800) > $timestamp);

				$table[] = array(
					strip_tags(date('r', $timestamp)) . ($overdue ? ' **(' . __('Overdue', 'wordfence') . ')**' : ''),
					strip_tags($cron_job),
				);
			}
		}
	}
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Database Tables', 'wordfence') ?>: <?php esc_html_e('Database table names, sizes, timestamps, and other metadata.', 'wordfence') ?> ##

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

if ($q) {
	$databaseCols = count($q[0]);

	if ($total > 250) {
		_e('Unable to verify - table count too high', 'wordfence');
	} else {
		$hasAll = true;
		$schemaTables = wfSchema::tableList();
		$existingTables = wfUtils::array_column($q, 'Name');
		if (WFWAF_IS_WINDOWS) {
			$existingTables = wfUtils::array_strtolower($existingTables);
		} //Windows MySQL installations are case-insensitive
		$missingTables = array();
		foreach ($schemaTables as $t) {
			$table = wfDB::networkTable($t);
			if (WFWAF_IS_WINDOWS) {
				$table = strtolower($table);
			}
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

		if ($hasAll) {
			_e('All Tables Exist', 'wordfence');
		} else {
			printf(/* translators: 1. WordPress table prefix. 2. Wordfence tables. */ __('Tables missing (prefix %1$s, %2$s): %s', 'wordfence'), wfDB::networkPrefix(), wfSchema::usingLowercase() ? __('lowercase', 'wordfence') : __('regular case', 'wordfence'), implode(', ', $missingTables));
		}
		echo "\n";
	}

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

	$table = array(
		$displayKeyOrder,
	);

	$count = 0;
	foreach ($q as $val) {
		$tableRow = array();
		foreach ($displayKeyOrder as $tkey) {
			$tableRow[] = isset($val[$tkey]) ? $val[$tkey] : '';
		}
		$table[] = $tableRow;

		$count++;
		if ($count >= 250 && $total > $count) {
			$tableRow = array_fill(0, $databaseCols, '');
			$tableRow[0] = sprintf(__('and %d more', 'wordfence'), $total - $count);
			$table[] = $tableRow;
			break;
		}
	}
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Log Files', 'wordfence') ?>: <?php esc_html_e('PHP error logs generated by your site, if enabled by your host.', 'wordfence') ?> ##

<?php

$table = array(
	array(
		__('File', 'wordfence'),
	),
);

$errorLogs = wfErrorLogHandler::getErrorLogs();
if (count($errorLogs) < 1) {
	$table[] = array(
		__('No log files found.', 'wordfence'),
	);
} else {
	foreach ($errorLogs as $log => $readable) {
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

		$logData = strip_tags($shortLog);
		if (!empty($metadata)) {
			$logData .= ' (' . implode(', ', $metadata) . ')';
		}
		$table[] = array($logData);
	}
}

echo wfHelperString::plainTextTable($table) . "\n\n";

?>

## <?php esc_html_e('Scan Issues', 'wordfence') ?> ##

<?php

$issues = wfIssues::shared()->getIssues(0, 50, 0, 50);
$issueCounts = array_merge(array('new' => 0, 'ignoreP' => 0, 'ignoreC' => 0), wfIssues::shared()->getIssueCounts());
$issueTypes = wfIssues::validIssueTypes();

printf(__('New Issues (%d total)', 'wordfence'), $issueCounts['new']);
echo "\n\n";

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
			echo strip_tags($viewContent) . "\n\n";
		}
	}
}
else {
	_e('No New Issues', 'wordfence');
	echo "\n";
}

?>

## PHPInfo ##

<?php
ob_start();
phpinfo();
$phpinfo = ob_get_clean();

if (preg_match_all('#(?:<h2>(.*?)</h2>\s*)?<table[^>]*>(.*?)</table>#is', $phpinfo, $tableMatches)) {
	foreach ($tableMatches[2] as $countIndex => $tableContents) {
		$table = array();
		if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $tableContents, $rowMatches)) {
			foreach ($rowMatches[1] as $rowContents) {
				if (preg_match_all('#<t[hd][^>]*>(.*?)</t[hd]>#is', $rowContents, $colMatches)) {
					$row = array();
					foreach ($colMatches[1] as $colContents) {
						$row[] = trim(strip_tags(html_entity_decode($colContents)));
					}
					$table[] = $row;
				}
			}
		}
		if (array_key_exists($countIndex, $tableMatches[1]) && $tableMatches[1][$countIndex]) {
			echo "## " . strip_tags($tableMatches[1][$countIndex]) . " ##\n\n";
		}

		$tableString = wfHelperString::plainTextTable($table);
		if ($tableString) {
			echo $tableString . "\n\n";
		}
	}
}


?>

