<?php
/**
 * The Template for displaying the sub-tabs navigation.
 *
 * @var array  $sub_tabs        The sub-tabs.
 * @var string $current_tab     The current tab.
 * @var string $current_sub_tab The current sub-tab.
 * @var string $page            The current page.
 * @package    YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<?php if ( ! empty( $sub_tabs ) ) : ?>
	<div class="yith-plugin-fw-sub-tabs-nav">
		<h3 class="nav-tab-wrapper yith-nav-sub-tab-wrapper">
			<?php foreach ( $sub_tabs as $_key => $_tab ) : ?>
				<?php
				$_defaults = array(
					'title' => '',
					'class' => '',
					'icon'  => '',
					'url'   => $this->get_nav_url( $page, $current_tab, $_key ),
				);
				$_tab      = (object) wp_parse_args( $_tab, $_defaults );

				if ( is_array( $_tab->class ) ) {
					$_tab->class = implode( ' ', $_tab->class );
				}

				if ( $current_sub_tab === $_key ) {
					$_tab->class = 'nav-tab-active ' . $_tab->class;
				}
				?>
				<a href="<?php echo esc_url( $_tab->url ); ?>" class="yith-nav-sub-tab nav-tab <?php echo esc_attr( $_tab->class ); ?>">
					<span class="yith-nav-sub-tab__title"><?php echo esc_html( $_tab->title ); ?></span>
					<?php if ( $_tab->icon ) : ?>
						<span class="yith-nav-sub-tab__icon yith-icon yith-icon-<?php echo esc_attr( $_tab->icon ); ?>"></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</h3>
	</div>
<?php endif; ?>
