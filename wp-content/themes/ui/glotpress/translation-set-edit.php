<?php
gp_title(
	sprintf(
		/* translators: 1: Translation set name. 2: Project name. */
		__( 'Edit Translation Set &lt; %1$s &lt; %2$s - LitePress翻译平台', 'glotpress' ),
		$set->name,
		$project->name
	)
);
gp_breadcrumb(
	array(
		gp_project_links_from_root( $project ),
		gp_link_get( $url, $locale->english_name . 'default' !== $set->slug ? ' ' . $set->name : '' ),
	)
);

// jQuery is required for the 'translation-set-form' template.
gp_enqueue_script( 'jquery' );

gp_tmpl_header();
?>
<h2><?php _e( 'Edit Translation Set', 'glotpress' ); ?></h2>
<form action="" method="post">
<?php gp_tmpl_load( 'translation-set-form', get_defined_vars() ); ?>
	<p>
		<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'glotpress' ); ?>" id="submit" />
		<span class="or-cancel"><?php _e( 'or', 'glotpress' ); ?> <a href="<?php echo esc_url( gp_url_project_locale( $project, $locale->slug, $set->slug ) ); ?>"><?php _e( 'Cancel', 'glotpress' ); ?></a></span>
	</p>
	<?php gp_route_nonce_field( 'edit-translation-set_' . $set->id ); ?>
</form>
<?php
gp_tmpl_footer();
