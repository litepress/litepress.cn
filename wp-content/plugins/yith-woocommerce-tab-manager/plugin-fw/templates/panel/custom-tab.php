<?php
/**
 * The Template for displaying the custom tab.
 *
 * @var array  $options         Array of options.
 * @var string $current_tab     The current tab.
 * @var string $current_sub_tab The current sub-tab.
 *
 * @package YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$defaults       = array(
	'action'         => '',
	'show_container' => false,
	'title'          => '',
);
$is_sub_tab     = ! ! $current_sub_tab;
$options        = wp_parse_args( $options, $defaults );
$the_action     = $options['action'];
$show_container = $options['show_container'];
$the_title      = $options['title'];
$tab_id         = sanitize_key( implode( '-', array_filter( array( 'yith-plugin-fw-panel-custom-tab', $current_tab, $current_sub_tab ) ) ) );
?>
<?php if ( $show_container ) : ?>
	<div id='<?php echo esc_attr( $tab_id ); ?>' class='yith-plugin-fw-panel-custom-tab-container'>
	<?php if ( $is_sub_tab ) : ?>
		<div class='yith-plugin-fw-panel-custom-sub-tab-container'>
	<?php endif; ?>
<?php endif; ?>

<?php if ( $the_title ) : ?>
	<h2 class="yith-plugin-fw-panel-custom-tab-title"><?php echo wp_kses_post( $the_title ); ?></h2>
<?php endif; ?>

<?php do_action( $the_action ); ?>

<?php if ( $show_container ) : ?>
	<?php if ( $is_sub_tab ) : ?>
		</div><!-- /.yith-plugin-fw-panel-custom-sub-tab-container -->
	<?php endif; ?>
	</div><!-- /.yith-plugin-fw-panel-custom-tab-container -->
<?php endif; // phpcs:ignore Generic.Files.EndFileNewline.NotFound ?>