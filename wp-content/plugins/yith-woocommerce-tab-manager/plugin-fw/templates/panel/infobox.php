<?php
/**
 * The Template for displaying info-box.
 *
 * @var string $id      The CSS of the box.
 * @var string $name    The name of the box.
 * @var array  $default Array of defaults.
 * @package YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<div id="<?php echo esc_attr( $id ); ?>" class="meta-box-sortables">
	<div id="<?php echo esc_attr( $id ); ?>-content-panel" class="postbox " style="display: block;">
		<h3><?php echo esc_html( $name ); ?></h3>
		<div class="inside">
			<p>Lorem ipsum ... </p>
			<p class="submit"><a href="<?php echo esc_url( $default['buy_url'] ); ?>" class="button-primary">Buy Plugin</a></p>
		</div>
	</div>
</div>
