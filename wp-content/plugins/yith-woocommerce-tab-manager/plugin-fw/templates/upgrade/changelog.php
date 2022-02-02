<?php
/**
 * The Template for displaying the plugin changelog.
 *
 * @var string $plugin_name The plugin name.
 * @var string $changelog   The changelog.
 * @package YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width">
	<meta name="robots" content="noindex,follow">
	<title><?php echo esc_html( $plugin_name ); ?> - Changelog</title>
	<style type="text/css">
		body {
			background  : #ffffff;
			color       : #444;
			font-family : -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			font-size   : 13px;
			line-height : 1.4em;
			padding     : 10px;
		}

		h2.yith-plugin-changelog-title {
			text-transform : uppercase;
			font-size      : 17px;
		}

		ul {
			list-style : none;
			padding    : 0;
		}

		li {
			display       : list-item;
			margin-bottom : 6px;
		}
	</style>
</head>
<body>
	<h2 class='yith-plugin-changelog-title'><?php echo esc_html( $plugin_name ); ?> - Changelog</h2>
	<div class='yith-plugin-changelog'><?php echo wp_kses_post( $changelog ); ?></div>
</body>
</html>
