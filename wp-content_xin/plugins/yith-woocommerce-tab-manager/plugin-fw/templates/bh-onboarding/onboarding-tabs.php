<?php
/**
 * The Template for displaying the BH Onboarding tabs.
 *
 * @var array $options The premium tab  options array.
 *
 * @package YITH\PluginFramework\Templates
 * @author  Emanuela Castorina <emanuela.castorina@yithemes.com>
 * @since   3.9.11
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
$tabs = $options['tabs'];
?>
<div class="woocommerce yith-plugin-fw-panel" id="yith-bh-onboarding">
	<header>
		<div class="yith-bh-onboarding-logo">
			<?php if ( isset( $options['logo'] ) ) : ?>
				<div class="logo">
					<img src="<?php echo esc_url( $options['logo'] ); ?>" width="150"/>
				</div>
			<?php endif; ?>
			<?php if ( isset( $options['claim'] ) ) : ?>
				<div class="claim"><?php echo esc_html( $options['claim'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="yith-bh-onboarding-plugin-description">
			<?php if ( isset( $options['plugin-description'] ) ) : ?>
				<div class="plugin-description"><?php echo wp_kses_post( $options['plugin-description'] ); ?></div>
			<?php endif; ?>
		</div>
	</header>
	<div class="yith-bh-onboarding-tabs  yith-plugin-ui">
		<ul class="yith-bh-onboarding-tabs__nav">
			<?php
			$c = 0;
			foreach ( $tabs as $key => $tab ) :
				?>
				<li class="yith-bh-onboarding-tabs__nav__link <?php echo ! ( $c ++ ) ? 'selected' : ''; ?>" data-tab="<?php esc_attr_e( $key ); ?>"><?php echo esc_html( $tab['title'] ); ?></li>
				<?php
			endforeach;
			?>
		</ul>
		<div class="yith-bh-onboarding-tabs__content yith-plugin-fw yit-admin-panel-container">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<?php if ( isset( $tab['options'] ) ) : ?>
					<div class="yith-bh-onboarding-tabs__tab" id="<?php echo esc_attr( $key ); ?>">
						<p class="yith-bh-onboarding-tab-description"><?php echo wp_kses_post( $tab['description'] ); ?></p>
						<form class="yith-bh-onboarding-tabs__form" id="plugin-fw-wc">
							<table class="form-table">
								<?php
								foreach ( $tab['options'] as $name => $option ) {
									YIT_Plugin_Panel_WooCommerce::add_yith_field( $option );
								}
								?>
							</table>
							<?php if ( isset( $tab['show_save_button'] ) && $tab['show_save_button'] ) : ?>
								<input type="hidden" name="yith-plugin" value="<?php echo esc_attr( $options['slug'] ); ?>">
								<input type="hidden" name="action" value="yith_bh_onboarding">
								<input type="hidden" name="tab" value="<?php echo esc_attr( $key ); ?>">
								<?php wp_nonce_field( 'yith-bh-onboarding-save-options' ); ?>
								<div class="submit-area">
									<button id="yith-bh-save-button" class="button button-primary"><?php echo esc_html__( 'Save', 'yith-plugin-fw' ); ?></button>
								</div>
							<?php endif; ?>
						</form>
					</div>
					<?php
				endif;
			endforeach;

			?>
		</div>

	</div>
