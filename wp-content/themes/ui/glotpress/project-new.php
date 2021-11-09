<?php
gp_title( __( '创建项目 - LitePress翻译平台', 'glotpress' ) );
gp_breadcrumb(
	array(
		'创建项目',
	)
);
gp_tmpl_header();
?>

    <div class="container ">
        <div class="setting">
        <h2><?php _e( 'Create New Project', 'glotpress' ); ?></h2>
        <form action="" method="post" enctype="multipart/form-data">
			<?php gp_tmpl_load( 'project-form', get_defined_vars() ); ?>
            <p>
                <input class="btn-primary btn" type="submit" name="submit"
                       value="<?php esc_attr_e( 'Create', 'glotpress' ); ?>" id="submit"/>
                <span class="or-cancel"><?php _e( 'or', 'glotpress' ); ?> <a class="btn btn-outline-primary"
                                                                             href="<?php echo esc_url( gp_url_public_root() ); ?>"><?php _e( 'Cancel', 'glotpress' ); ?></a></span>
            </p>
			<?php gp_route_nonce_field( 'add-project' ); ?>
        </form>
        </div>
    </div>
<?php
gp_tmpl_footer();
