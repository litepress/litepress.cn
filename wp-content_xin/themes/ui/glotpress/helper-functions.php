<?php
/**
 * 函数库文件
 *
 * 这个文件由主题引入，而不是glotpree。glotpree插件内单独引用了另一个同名文件
 *
 * @package GlotPress
 * @since 1.0.0
 */

use LitePress\I18n\i18n;

/**
 * Output the bulk actions toolbar in the translations page.
 *
 * @param string $bulk_action The URL to submit the form to.
 * @param string $can_write Can the current user write translations to the database.
 * @param string $translation_set The current translation set.
 * @param string $location The location of this toolbar, used to make id's unique for each instance on a page.
 */
function wcy_gp_translations_bulk_actions_toolbar( $bulk_action, $can_write, $translation_set, $location = 'top' ) {
	?>

    <form id="bulk-actions-toolbar-<?php echo esc_attr( $location ); ?>" class="filters-toolbar bulk-actions"
          action="<?php echo esc_attr( $bulk_action ); ?>" method="post">
        <div class="d-flex">
            <select name="bulk[action]" id="bulk-action-<?php echo esc_attr( $location ); ?>" class="bulk-action form-select me-1">
                <option value="" selected="selected"><?php _e( 'Bulk Actions', 'glotpress' ); ?></option>
                <option value="approve"><?php _ex( 'Approve', 'Action', 'glotpress' ); ?></option>
                <option value="reject"><?php _ex( 'Reject', 'Action', 'glotpress' ); ?></option>
                <option value="fuzzy"><?php _ex( 'Fuzzy', 'Action', 'glotpress' ); ?></option>
				<?php if ( $can_write ) : ?>
                    <option value="set-priority"
                            class="hide-if-no-js"><?php _e( 'Set Priority', 'glotpress' ); ?></option>
				<?php endif; ?>
				<?php

				/**
				 * Fires inside the bulk action menu for translation sets.
				 *
				 * Printing out option elements here will add those to the translation
				 * bulk options drop down menu.
				 *
				 * @param GP_Translation_Set $set The translation set.
				 *
				 * @since 1.0.0
				 *
				 */
				do_action( 'gp_translation_set_bulk_action', $translation_set );
				?>
            </select>
			<?php if ( $can_write ) : ?>
                <select name="bulk[priority]" id="bulk-priority-<?php echo esc_attr( $location ); ?>"
                        class="bulk-priority hidden">
					<?php
					$labels = array(
						'hidden' => _x( 'hidden', 'Priority', 'glotpress' ),
						'low'    => _x( 'low', 'Priority', 'glotpress' ),
						'normal' => _x( 'normal', 'Priority', 'glotpress' ),
						'high'   => _x( 'high', 'Priority', 'glotpress' ),
					);

					foreach ( GP::$original->get_static( 'priorities' ) as $value => $label ) {
						if ( isset( $labels[ $label ] ) ) {
							$label = $labels[ $label ];
						}

						echo "\t<option value='" . esc_attr( $value ) . "' " . selected( 'normal', $value, false ) . '>' . esc_html( $label ) . "</option>\n";
					}
					?>
                </select>
			<?php endif; ?>
            <input type="hidden" name="bulk[redirect_to]" value="<?php echo esc_attr( gp_url_current() ); ?>"
                   id="bulk-redirect_to-<?php echo esc_attr( $location ); ?>"/>
            <input type="hidden" name="bulk[row-ids]" value="" id="bulk-row-ids-<?php echo esc_attr( $location ); ?>"/>
            <input type="submit" class="button btn btn-primary" value="<?php esc_attr_e( 'Apply', 'glotpress' ); ?>"/>
            <a class="button btn btn-danger" href="/translate/gp-mt/<?php echo $translation_set->project_id; ?>">预翻译(这需要一点时间，勿反复点击)</a>
        </div>
		<?php
		$nonce = gp_route_nonce_field( 'bulk-actions', false );
		$nonce = str_replace( 'id="_gp_route_nonce"', 'id="_gp_route_nonce_' . esc_attr( $location ) . '"', $nonce );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $nonce;
		?>
    </form>
	<?php
}

function lpcn_gp_project_links_from_root( $leaf_project ) {
	if ( 0 === $leaf_project->id ) {
		return array();
	}
	$links          = array();
	$path_from_root = array_reverse( $leaf_project->path_to_root() );
	$links[]        = empty( $path_from_root ) ? __( 'Projects', 'glotpress' ) : gp_link_get( gp_url( '/projects' ), __( 'Projects', 'glotpress' ) );
	foreach ( $path_from_root as $project ) {
		if ( 1 === (int) $project->parent_project_id ) {
			$project->name = i18n::get_instance()->translate( 'plugin_' . (string) $project->slug . '_title', (string) $project->name, (string) $project->path . '/readme', true );
		} elseif ( 2 === (int) $project->parent_project_id ) {
			$project->name = i18n::get_instance()->translate( 'theme_' . (string) $project->slug . '_title', (string) $project->name, (string) $project->path . '/' . (string) $project->slug, true );
		} else {
			$project->name = $project->name ?? '';
		}

		$links[] = gp_link_project_get( $project, esc_html( $project->name ) );
	}

	return $links;
}