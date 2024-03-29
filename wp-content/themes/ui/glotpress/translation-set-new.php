<?php
gp_title( __( 'Create New Translation Set - LitePress翻译平台', 'glotpress' ) );
$project ? gp_breadcrumb_project( $project ) : gp_breadcrumb( array( __( 'New Translation Set', 'glotpress' ) ) );

// jQuery is required for the 'translation-set-form' template.
gp_enqueue_script( 'jquery' );

gp_tmpl_header();
?>
<h2><?php _e( 'Create New Translation Set', 'glotpress' ); ?></h2>
<form action="" method="post">
<?php gp_tmpl_load( 'translation-set-form', get_defined_vars() ); ?>
	<p>
		<input type="submit" name="submit" value="<?php esc_attr_e( 'Create', 'glotpress' ); ?>" id="submit" />
		<span class="or-cancel"><?php _e( 'or', 'glotpress' ); ?> <a href="<?php echo esc_url( gp_url_project( $project ) ); ?>"><?php _e( 'Cancel', 'glotpress' ); ?></a></span>
	</p>
	<?php gp_route_nonce_field( 'add-translation-set' ); ?>
</form>
<?php
gp_tmpl_footer();
