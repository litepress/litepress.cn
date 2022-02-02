<?php
/**
 * The Template for displaying PHP Information.
 *
 * @package YITH\PluginFramework\Templates\SysInfo
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

ob_start();
phpinfo( 61 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_phpinfo
$php_info = ob_get_contents();
ob_end_clean();

$php_info = preg_replace( '%^.*<div class="center">(.*)</div>.*$%ms', '$1', $php_info );
$php_info = preg_replace( '%^<h1>(.*)</h1>$%ms', '', $php_info );
$php_info = preg_replace( '%(^.*)<a name=\".*\">(.*)</a>(.*$)%m', '$1$2$3', $php_info );
$php_info = preg_replace( '%^<h2>((\w*-*\w*)|(\w*\s*\w*))</h2>$%m', '</div><div id="$1"><h2>$1</h2>', $php_info );
$php_info = str_replace( '<table>', '<table class="form-table" role="presentation">', $php_info );
$php_info = str_replace( '<td class="e">', '<th class="e">', $php_info );
$php_info = str_replace( '<hr />', '', $php_info );

?>
<div class="yith-phpinfo-wrap">
	<?php
	echo '<div id="main">' . $php_info; //phpcs:ignore
	?>
</div>
