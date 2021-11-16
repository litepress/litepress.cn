<?php

use LitePress\I18n\i18n;

$parent_project = GP::$project->find_one( array( 'id' => $project->parent_project_id ) );
if ( 1 === (int) $parent_project->parent_project_id ) {
	$parent_project->name = i18n::get_instance()->translate( 'plugin_' . (string) $parent_project->slug . '_title', (string) $parent_project->name, (string) $parent_project->path . '/readme', true );
	$title                = '《' . $parent_project->name . '》的' . $project->name;
} elseif ( 2 === (int) $parent_project->parent_project_id ) {
	$parent_project->name = i18n::get_instance()->translate( 'theme_' . (string) $parent_project->slug . '_title', (string) $parent_project->name, (string) $parent_project->path . '/' . (string) $parent_project->slug, true );
	$title                = $parent_project->name;
} else {
	$title = $project->name ?? '';
}


if ( 'originals' === $kind ) {
	$gp_title    = sprintf(
	/* translators: %s: Project name. */
		__( '导入原文到%s - LitePress翻译平台', 'glotpress' ),
		esc_html( $title )
	);
	$return_link = gp_url_project( $project );
	gp_breadcrumb_project( $project );
} else {
	$gp_title    = sprintf(
	/* translators: %s: Project name. */
		__( '导入翻译到%s - LitePress翻译平台', 'glotpress' ),
		esc_html( $title )
	);
	$return_link = gp_url_project_locale( $project, $locale->slug, $translation_set->slug );
	gp_breadcrumb(
		array(
			gp_project_links_from_root( $project ),
			gp_link_get( $return_link, $translation_set->name ),
		)
	);
}

gp_title( $gp_title );
gp_tmpl_header();
?>

    <!--<h2><?php /*echo 'originals' == $kind ? __( 'Import Originals', 'glotpress' ) : __( 'Import Translations', 'glotpress' ); */ ?></h2>-->
    <div class="container ">
	    <?php echo gp_breadcrumb(); ?>
        <div class="setting">
        <form action="" method="post" enctype="multipart/form-data" style="max-width: 400px">
            <dl>
                <dt>
                    <label for="import-file">从文件导入<?php if ( 'originals' === $kind ): ?>（你可以上传翻译文件或插件、主题安装包）<?php endif; ?></label>
                </dt>
                <dd><input type="file" class="form-control" name="import-file" id="import-file"/></dd>
				<?php
				/**
				 * 选择要导入到的子项目
				 */
				if ( 'originals' === $kind ) {
					/**
					 * @var \GP_Project $sub_projects
					 */
					$sub_projects = GP::$project->find_many( array(
						'parent_project_id' => $project->id,
						'active'            => 1
					) );

					$data = array();
					foreach ( $sub_projects as $sub_project ) {
						$data[ $sub_project->path ] = $sub_project->name;
					}

					echo '<dt><label for="format">子项目</label></dt>';
					echo '<dd>';
					echo gp_select( 'sub_project_path', $data, 'auto' );
					echo '</dd>';

					echo '<dt><label for="format">项目版本号（请务必正确填写）</label></dt>';
					echo '<dd>';
					echo '<input type="text" name="version" placeholder="1.0.0">';
					echo '</dd>';
				}

				$format_options         = array();
				$format_options['auto'] = __( 'Auto Detect', 'glotpress' );
				if ( 'originals' === $kind ) {
					$format_options['zip'] = '插件、主题安装包 (.zip)';
				}
				foreach ( GP::$formats as $slug => $format ) {
					$format_options[ $slug ] = $format->name;
				}

				$status_options = array();
				if ( isset( $can_import_current ) && $can_import_current ) {
					$status_options['current'] = __( 'Current', 'glotpress' );
				}
				if ( isset( $can_import_waiting ) && $can_import_waiting ) {
					$status_options['waiting'] = __( 'Waiting', 'glotpress' );
				}
				?>
                <dt><label for="format"><?php _e( 'Format:', 'glotpress' ); ?></label></dt>
                <dd>
					<?php echo gp_select( 'format', $format_options, 'auto' ); ?>
                </dd>
				<?php if ( ! empty( $status_options ) ) : ?>
                    <dt><label for="status"><?php _e( 'Status:', 'glotpress' ); ?></label></dt>
                    <dd>
						<?php if ( count( $status_options ) === 1 ) : ?>
                            <input type="hidden" name="status"
                                   value="<?php echo esc_attr( key( $status_options ) ); ?>"/>
							<?php echo esc_html( reset( $status_options ) ); ?>
						<?php elseif ( count( $status_options ) > 1 ) : ?>
							<?php echo gp_select( 'status', $status_options, 'current' ); ?>
						<?php endif; ?>
                    </dd>
				<?php endif; ?>
                <dt>
                    <p>
                        <input type="submit" class="btn btn-primary" name="submit"
                               value="<?php esc_attr_e( 'Import', 'glotpress' ); ?>"/>
                        <span class="or-cancel"><?php _e( 'or', 'glotpress' ); ?> <a
                                    href="<?php echo esc_url( $return_link ); ?>"><?php _e( 'Cancel', 'glotpress' ); ?></a></span>
                    </p>
                </dt>
            </dl>
			<?php gp_route_nonce_field( ( 'originals' === $kind ? 'import-originals_' : 'import-translations_' ) . $project->id ); ?>
        </form>
        </div>
    </div>
<?php
gp_tmpl_footer();
