<?php
if( ! isset( $LAS_PARAM ) ) {
	return false;
}

$strFormCSS = '';
$isStripForm = isset( $LAS_PARAM[ 'strip_form' ] ) ? (boolean) $LAS_PARAM[ 'strip_form' ] === true : false;

if( false !== $LAS_PARAM[ 'height' ] ) {
	$strFormCSS = sprintf( 'style="height:%spx;"', $LAS_PARAM[ 'height' ] );
} ?>

<div class="lava-ajax-search-form-wrap">
	<?php
	if( ! $isStripForm ) {
		printf( '<form method="get" action="%1$s" %2$s>', esc_url( home_url( '/' ) ), $strFormCSS );
	} ?>
		<input type="text" name="<?php echo esc_attr( $LAS_PARAM[ 'field_name' ] ); ?>" value="<?php echo $LAS_PARAM[ 'default_value' ]; ?>" autocomplete="off" placeholder="<?php esc_html_e( "Search", 'lvbp-ajax-search' ); ?>" <?php echo $strFormCSS; ?> data-search-input>
		<?php
		if( $LAS_PARAM[ 'submit_button' ] ) { ?>
			<button type="submit"><?php esc_html_e( "Search", 'lvbp-ajax-search' ); ?></button>
			<?php
		} ?>
	<?php
	if( ! $isStripForm ) {
		printf( '</form>' );
	} ?>
	<div class="actions">
		<div class="loading">
			<i class="fa fa-spin fa-spinner"></i>
		</div>
		<div class="clear hidden">
			<i class="fa fa-close"></i>
		</div>
	</div>
</div>