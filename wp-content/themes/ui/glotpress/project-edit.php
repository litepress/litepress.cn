<?php
gp_title(
	sprintf(
		/* translators: %s: project name */
		__( 'Edit project "%s" - LitePress翻译平台', 'glotpress' ),
		$project->name
	)
);
gp_breadcrumb_project( $project );
gp_tmpl_header();
?>
    <div class="container setting">

<form action="" method="post" enctype="multipart/form-data">
	<?php gp_tmpl_load( 'project-form', get_defined_vars() ); ?>
	<p>
		<input class="btn btn-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'glotpress' ); ?>" id="submit" />
		<span class="or-cancel"><?php _e( 'or', 'glotpress' ); ?>　<a class="btn btn-outline-primary" href="<?php echo esc_url( gp_url_project( $project ) ); ?>"><?php _e( 'Cancel', 'glotpress' ); ?></a></span>
	</p>
	<?php gp_route_nonce_field( 'edit-project_' . $project->id ); ?>
</form>
    </div>
<?php
gp_tmpl_footer();
