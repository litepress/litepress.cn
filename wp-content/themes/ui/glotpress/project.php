<?php

use LitePress\I18n\i18n;

if ( 1 === (int) $project->parent_project_id ) {
	$project->name = i18n::get_instance()->translate( 'plugin_' . (string) $project->slug . '_title', (string) $project->name, (string) $project->path . '/readme', true );
} elseif ( 2 === (int) $project->parent_project_id ) {
	$project->name = i18n::get_instance()->translate( 'theme_' . (string) $project->slug . '_title', (string) $project->name, (string) $project->path . '/' . (string) $project->slug, true );
} else {
	$project->name = $project->name ?? '';
}

gp_title(
	sprintf(
	/* translators: %s: Project name. */
		__( '%s - LitePress翻译平台', 'glotpress' ),
		esc_html( $project->name )
	)
);
gp_breadcrumb( lpcn_gp_project_links_from_root( $project ) );
gp_enqueue_scripts( array( 'gp-editor', 'tablesorter' ) );
gp_enqueue_style( 'tablesorter-theme' );
$edit_link   = gp_link_project_edit_get( $project, _x( '(edit)', 'project', 'glotpress' ) );
$delete_link = gp_link_project_delete_get( $project, _x( '(delete)', 'project', 'glotpress' ) );
if ( $project->active ) {
	add_filter(
		'gp_breadcrumb_items',
		function ( $items ) {
			$items[ count( $items ) - 1 ] .= ' <span class="active bubble">' . __( 'Active', 'glotpress' ) . '</span>';

			return $items;
		}
	);
}
gp_tmpl_header();
?>

    <div class="container">
		<?php echo gp_breadcrumb(); ?>
        <div class="notice" id="help-notice">
            刚开始翻译WordPress？阅读我们的<a
                    href="https://make.wordpress.org/polyglots/handbook/tools/glotpress-translate-wordpress-org/"
                    target="_blank"> 翻译手册 </a>开始。
            <a id="hide-help-notice" class="secondary" style="float: right;" href="/getting-started/hide-notice/">×</a>
        </div>

    </div>
<?php if ( $can_write ) : ?>
    <div class="container  ">
        <div class="toolbar">
            <a href="#" class="project-actions"
               id="project-actions-toggle"><?php echo __( 'Project actions', 'glotpress' ) . ' &darr;'; ?></a>
            <div class="project-actions hide-if-js">
				<?php gp_project_actions( $project, $translation_sets ); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

    <div class="container">
        <!--        --><?php
		//        /**
		//         * Filter a project description.
		//         *
		//         * @param string $description Project description.
		//         * @param GP_Project $project The current project.
		//         *
		//         * @since 1.0.0
		//         *
		//         */
		//        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		//        $project_description = apply_filters('gp_project_description', $project->description, $project);
		//
		//        if ($project_description) {
		//            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized via filters.
		//            echo '<div class="project-description">' . $project_description . '</div>';
		//        }
		//        ?>


		<?php
		$project_class = $sub_projects ? 'with-sub-projects' : '';
		?>
        <div id="project" class="<?php echo esc_attr( $project_class ); ?>">

			<?php if ( $translation_sets ) : ?>
                <style>
                    .fl-builder-content.fl-builder-content-1386 {
                        position: fixed;
                        bottom: 0;
                        right: 0;
                        left: 0;
                    }
                </style>
                <div id="translation-sets">
                    <!--	<h3><?php _e( 'Translations', 'glotpress' ); ?></h3>-->
                    <table class="translation-sets tablesorter tablesorter-glotpress">
                        <thead>
                        <tr class="tablesorter-headerRow">
                            <th class="header tablesorter-header tablesorter-headerUnSorted"><?php _e( 'Locale', 'glotpress' ); ?></th>
                            <th class="header tablesorter-header tablesorter-headerUnSorted"><?php _ex( '%', 'locale translation percent header', 'glotpress' ); ?></th>
                            <th class="header tablesorter-header tablesorter-headerDesc"><?php _e( 'Translated', 'glotpress' ); ?></th>
                            <th class="header tablesorter-header tablesorter-headerUnSorted"><?php _e( 'Fuzzy', 'glotpress' ); ?></th>
                            <th class="header tablesorter-header tablesorter-headerUnSorted"><?php _e( 'Untranslated', 'glotpress' ); ?></th>
                            <th class="header tablesorter-header tablesorter-headerUnSorted"><?php _e( 'Waiting', 'glotpress' ); ?></th>
							<?php if ( has_action( 'gp_project_template_translation_set_extra' ) ) : ?>
                                <th class="header tablesorter-header tablesorter-headerUnSorted extra"><?php _e( 'Extra', 'glotpress' ); ?></th>
							<?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						$class = '';

						foreach ( $translation_sets as $set ) :
							$class = ( 'odd' === $class ) ? 'even' : 'odd';

							?>
                            <tr class="<?php echo esc_attr( $class ); ?>">
                                <td>
                                    <strong><?php gp_link( gp_url_project( $project, gp_url_join( $set->locale, $set->slug ) ), $set->name_with_locale() ); ?></strong>
									<?php
									if ( $set->current_count && $set->current_count >= $set->all_count * 0.9 ) :
										$percent = floor( $set->current_count / $set->all_count * 100 );
										?>
                                        <span class="bubble morethan90"><?php echo number_format_i18n( $percent ); ?>%</span>
									<?php endif; ?>
                                </td>
                                <td class="stats percent"><?php echo number_format_i18n( $set->percent_translated ); ?>
                                    %
                                </td>
                                <td class="stats translated" title="translated">
									<?php
									gp_link(
										gp_url_project(
											$project,
											gp_url_join( $set->locale, $set->slug ),
											array(
												'filters[translated]' => 'yes',
												'filters[status]'     => 'current',
											)
										),
										number_format_i18n( $set->current_count )
									);
									?>
                                </td>
                                <td class="stats fuzzy" title="fuzzy">
									<?php
									gp_link(
										gp_url_project(
											$project,
											gp_url_join( $set->locale, $set->slug ),
											array(
												'filters[translated]' => 'yes',
												'filters[status]'     => 'fuzzy',
											)
										),
										number_format_i18n( $set->fuzzy_count )
									);
									?>
                                </td>
                                <td class="stats untranslated" title="untranslated">
									<?php
									gp_link(
										gp_url_project(
											$project,
											gp_url_join( $set->locale, $set->slug ),
											array(
												'filters[status]' => 'untranslated',
											)
										),
										number_format_i18n( $set->untranslated_count )
									);
									?>
                                </td>
                                <td class="stats waiting">
									<?php
									gp_link(
										gp_url_project(
											$project,
											gp_url_join( $set->locale, $set->slug ),
											array(
												'filters[translated]' => 'yes',
												'filters[status]'     => 'waiting',
											)
										),
										number_format_i18n( $set->waiting_count )
									);
									?>
                                </td>
								<?php if ( has_action( 'gp_project_template_translation_set_extra' ) ) : ?>
                                    <td class="extra">
										<?php
										/**
										 * Fires in an extra information column of a translation set.
										 *
										 * @param GP_Translation_Set $set The translation set.
										 * @param GP_Project $project The current project.
										 *
										 * @since 1.0.0
										 *
										 */
										do_action( 'gp_project_template_translation_set_extra', $set, $project );
										?>
                                    </td>
								<?php endif; ?>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
			<?php elseif ( ! $sub_projects ) : ?>
                <p><?php _e( 'There are no translations of this project.', 'glotpress' ); ?></p>
			<?php endif; ?>

        </div>

		<?php if ( isset( $sub_projects ) && ! empty( $sub_projects ) ) : ?>


            <div id="sub-projects">
				<?php
				/**
				 * 判断一下如果父项目ID小于10（也就是非最顶级的插件、主题、核心）则按次级项目泪飙显示，这样方便展示更多信息
				 */
				$first_sub_project    = $sub_projects[0];
				$parent_project       = GP::$project->find_one( array( 'id' => $first_sub_project->parent_project_id ) );
				$sub_sub_project_slug = $sub_sub_project->slug ?? '';
				if ( $first_sub_project->parent_project_id > 10 ) :?>
                    <div class="locale-project">
                        <section class="wp-card">
                            <table class="locale-sub-projects">
                                <thead>
                                <tr>
                                    <th class="header"><font style="vertical-align: inherit;"><font
                                                    style="vertical-align: inherit;">项目</font></font></th>
                                    <th><font style="vertical-align: inherit;"><font
                                                    style="vertical-align: inherit;">已翻译</font></font></th>
                                    <th><font style="vertical-align: inherit;"><font
                                                    style="vertical-align: inherit;">模糊</font></font></th>
                                    <th><font style="vertical-align: inherit;"><font
                                                    style="vertical-align: inherit;">未翻译</font></font></th>
                                    <th><font style="vertical-align: inherit;"><font
                                                    style="vertical-align: inherit;">等待</font></font></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php foreach ( $sub_projects as $sub_project ): ?>
									<?php
									$translation_set = GP::$translation_set->by_project_id( $sub_project->id )[0] ?? null;
									if ( ! empty( $translation_set ) ) :
										?>
                                        <tr>
                                            <td class="set-name">
                                                <strong><a href="<?php echo gp_url_project( $sub_project ) ?>zh-cn/default/"><font
                                                                style="vertical-align: inherit;"><font
                                                                    style="vertical-align: inherit;"><?php echo $sub_project->name ?></font></font></a></strong>
                                                <span class="sub-project-status"><font
                                                            style="vertical-align: inherit;"><font
                                                                style="vertical-align: inherit;"> (<?php echo round( ( $translation_set->current_count() / ( $translation_set->all_count() == 0 ? 1 : $translation_set->all_count() ) ) * 100 ); ?>%)</font></font></span>
                                            </td>
                                            <td class="stats translated">
                                                <a href="<?php echo gp_url_project( $sub_project ) ?>zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=current"><font
                                                            style="vertical-align: inherit;"><font
                                                                style="vertical-align: inherit;"><?php echo $translation_set->current_count() ?></font></font></a>
                                            </td>
                                            <td class="stats fuzzy">
                                                <a href="<?php echo gp_url_project( $sub_project ) ?>zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=fuzzy"><font
                                                            style="vertical-align: inherit;"><font
                                                                style="vertical-align: inherit;"><?php echo $translation_set->fuzzy_count() ?></font></font></a>
                                            </td>
                                            <td class="stats untranslated">
                                                <a href="<?php echo gp_url_project( $sub_project ) ?>zh-cn/default/?filters%5Bstatus%5D=untranslated"><font
                                                            style="vertical-align: inherit;"><font
                                                                style="vertical-align: inherit;"><?php echo $translation_set->untranslated_count() ?></font></font></a>
                                            </td>
                                            <td class="stats waiting">
                                                <a href="<?php echo gp_url_project( $sub_project ) ?>zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=waiting"><font
                                                            style="vertical-align: inherit;"><font
                                                                style="vertical-align: inherit;"><?php echo $translation_set->waiting_count() ?></font></font></a>
                                            </td>
                                        </tr>
									<?php
									endif;
								endforeach;
								?>
                                </tbody>
                            </table>
                        </section>

                        <div class="row">
                            <div class="col-xl-9">
                                <section class="wp-card">
                                    <header class="d-flex aside-header align-items-center">
                                        <div class="me-2 wp-icon">
                                            <i class="fas fa-clipboard-list-check fa-fw"></i></div>
                                        <span>翻译贡献者</span></header>
                                    <ul class="p-2">
                                        <table class="locale-project-contributors-table table">
                                            <thead>
                                            <tr>
                                                <th class="contributor-name">贡献者</th>
                                                <th class="contributor-stats">贡献情况</th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php
											$local               = new \LitePress\GlotPress\Inc\Locale();
											$locale_contributors = $local->get_locale_contributors( $parent_project, 'zh-cn', 'default' );
											$contributors        = $locale_contributors['contributors'] ?? array();
											foreach ( $contributors as $contributor ) :
												?>
                                                <tr id="contributor-<?php echo $contributor->login; ?>">
                                                    <td class="contributor-name">
                                                        <a href="/user/<?php echo $contributor->login; ?>"><?php echo get_avatar( $contributor->email, 32 ); ?><?php echo $contributor->display_name; ?></a>
                                                        <span>上次翻译提交: <?php echo human_time_diff( strtotime( $contributor->last_update ), time() ); ?>前</span>
                                                    </td>
                                                    <td class="contributor-stats">
                                                        <div class="total">
                                                            <span>总计</span>
                                                            <p><?php echo $contributor->total_count; ?></p>
                                                        </div>
                                                        <div class="current">
                                                            <span>已翻译</span>
                                                            <p><?php echo $contributor->current_count; ?></p>
                                                        </div>
                                                        <div class="waiting">
                                                            <span>等待</span>
                                                            <p><?php echo $contributor->waiting_count; ?></p>
                                                        </div>
                                                        <div class="fuzzy">
                                                            <span>模糊</span>
                                                            <p><?php echo $contributor->fuzzy_count; ?></p>
                                                        </div>
                                                        <div class="detailed">
                                                            <details>
                                                                <summary>详细</summary>
																<?php foreach ( $contributor->detailed as $detailed ): ?>
                                                                    <strong class="detailed__project-name"><?php echo $detailed->project->name; ?></strong>
                                                                    <div class="total">
                                                                        <p>
                                                                            <a href="<?php echo gp_url_project( $detailed->project ); ?>/zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=current_or_waiting_or_fuzzy_or_untranslated&amp;filters%5Buser_login%5D=<?php echo $contributor->login; ?>"><?php echo $detailed->total_count; ?></a>
                                                                        </p>
                                                                    </div>
                                                                    <div class="current">
                                                                        <p>
                                                                            <a href="<?php echo gp_url_project( $detailed->project ); ?>/zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=current&amp;filters%5Buser_login%5D=<?php echo $contributor->login; ?>"><?php echo $detailed->current_count; ?></a>
                                                                        </p>
                                                                    </div>
                                                                    <div class="waiting">
                                                                        <p>
                                                                            <a href="<?php echo gp_url_project( $detailed->project ); ?>/zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=waiting&amp;filters%5Buser_login%5D=<?php echo $contributor->login; ?>"><?php echo $detailed->waiting_count; ?></a>
                                                                        </p>
                                                                    </div>
                                                                    <div class="fuzzy">
                                                                        <p>
                                                                            <a href="<?php echo gp_url_project( $detailed->project ); ?>/zh-cn/default/?filters%5Btranslated%5D=yes&amp;filters%5Bstatus%5D=fuzzy&amp;filters%5Buser_login%5D=<?php echo $contributor->login; ?>"><?php echo $detailed->fuzzy_count; ?></a>
                                                                        </p>
                                                                    </div>
																<?php endforeach; ?>
                                                            </details>
                                                        </div>
                                                    </td>
                                                </tr>
											<?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </ul>
                                </section>
                            </div>

                            <div class="col-xl-3 mt-3 mt-xl-0 ">
                                <section class="wp-card">
                                    <header class="d-flex aside-header align-items-center">
                                        <div class="me-2 wp-icon">
                                            <i class="fas fa-clipboard-list-check fa-fw"></i></div>
                                        <span>项目翻译编辑者</span></header>

                                    <ul class="p-2">
										<?php $project_permissions = GP::$permission->find( array(
											'action'    => 'approve',
											'object_id' => $parent_project->id . '|zh-cn|default'
										) ) ?>
										<?php foreach ( $project_permissions as $project_permission ): ?>
											<?php $user = get_user_by( 'ID', $project_permission->user_id ); ?>
											<?php if ( ! empty( $user ) ): ?>
                                                <li>
                                                    <a href="/user/<?php echo $user->user_login ?>"><?php echo get_avatar( $user->id, 32 ) . $user->display_name ?></a>
                                                </li>
											<?php endif; ?>
										<?php endforeach; ?>
                                    </ul>
                                </section>

                                <section class="wp-card mt-3">
                                    <header class="d-flex aside-header align-items-center">
                                        <div class="me-2 wp-icon">
                                            <i class="fas fa-clipboard-list-check fa-fw"></i></div>
                                        <span>全局翻译编辑者</span></header>
									<?php $global_permissions = GP::$permission->find( array( 'action' => 'admin' ) ) ?>
                                    <ul class="compressed p-2">
										<?php foreach ( $global_permissions as $global_permission ): ?>
											<?php $user = get_user_by( 'ID', $global_permission->user_id ); ?>
											<?php if ( ! empty( $user ) ): ?>
                                                <li>
                                                    <a href="/user/<?php echo $user->user_login ?>"><?php echo get_avatar( $user->id, 32 ) . $user->display_name ?></a>
                                                </li>
											<?php endif; ?>
										<?php endforeach; ?>
                                    </ul>
                                </section>
                            </div>
                        </div>


                    </div>
				<?php else: ?>
                    <ul class="row projects gx-5 gy-4">
						<?php foreach ( $sub_projects as $sub_project ) :
							$translation_set = GP::$translation_set->by_project_id( $sub_project->id )[0] ?? null;
							$sub_sub_projects = GP::$project->find_many( array(
								'parent_project_id' => $sub_project->id,
							) );

							$translation_set_total                     = new stdClass();
							$translation_set_total->current_count      = 0;
							$translation_set_total->untranslated_count = 0;
							$translation_set_total->waiting_count      = 0;
							$translation_set_total->fuzzy_count        = 0;
							$translation_set_total->percent_translated = 0;
							$translation_set_total->all_count          = 0;

							foreach ( $sub_sub_projects as $sub_sub_project ) {
								$sub_translation_set = GP::$translation_set->by_project_id( $sub_sub_project->id )[0] ?? new stdClass();

								$translation_set_total->current_count      += $sub_translation_set->current_count();
								$translation_set_total->untranslated_count += $sub_translation_set->untranslated_count();
								$translation_set_total->waiting_count      += $sub_translation_set->waiting_count();
								$translation_set_total->fuzzy_count        += $sub_translation_set->fuzzy_count();
								$translation_set_total->percent_translated += $sub_translation_set->percent_translated();
								$translation_set_total->all_count          += $sub_translation_set->all_count();
							}
							$translation_set_total->progress = round( ( $translation_set_total->current_count / ( $translation_set_total->all_count == 0 ? 1 : $translation_set_total->all_count ) ) * 100 );
							?>
                            <li class="col-xl-4">
                                <div class="card theme-boxshadow">
                                    <div class="row g-0">
                                        <div class="project-top row">
                                            <div class="col-5 center">
												<?php if ( 2 === (int) $sub_project->parent_project_id ): ?>
                                                    <img class="plugin-icon"
                                                         src="https://i0.wp.com/themes.svn.wordpress.org/<?php echo $sub_project->slug ?? '' ?>/<?php echo gp_get_meta( 'project', $sub_project->id, 'version' ) ?>/screenshot.png"
                                                         onError="this.src='https://i0.wp.com/themes.svn.wordpress.org/<?php echo $sub_project->slug ?? '' ?>/<?php echo gp_get_meta( 'project', $sub_project->id, 'version' ) ?>/screenshot.jpg';">
												<?php else: ?>
                                                    <img class="plugin-icon"
                                                         src="https://ps.w.org.ibadboy.net/<?php echo $sub_project->slug ?? '' ?>/assets/icon-128x128.png"
                                                         onError="this.src='https://avatar.ibadboy.net/avatar/<?php echo md5( $sub_project->slug ) ?>?d=identicon&s=133';">
												<?php endif; ?>
                                            </div>
                                            <div class="col-7">
                                                <h5 class="card-title">
													<?php if ( 1 === (int) $sub_project->parent_project_id ): ?>
                                                        <a href="<?php echo gp_url_project( $sub_project ) ?>"
                                                           one-link-mark="yes"><?php echo i18n::get_instance()->translate( 'plugin_' . (string) $sub_project->slug . '_title', (string) $sub_project->name, (string) $sub_project->path . '/readme', true ); ?></a>
													<?php elseif ( 2 === (int) $sub_project->parent_project_id ): ?>
                                                        <a href="<?php echo gp_url_project( $sub_project ) ?>"
                                                           one-link-mark="yes"><?php echo i18n::get_instance()->translate( 'theme_' . (string) $sub_project->slug . '_title', (string) $sub_project->name, (string) $sub_project->path . '/' . (string) $sub_project->slug, true ); ?></a>
													<?php else: ?>
                                                        <a href="<?php echo gp_url_project( $sub_project ) ?>"
                                                           one-link-mark="yes"><?php echo (string) $sub_project->name; ?></a>
													<?php endif; ?>
                                                </h5>
                                                <p class="card-text project-description"><small><?php
														/**
														 * Filter a sub-project description.
														 *
														 * @param string $description Sub-project description.
														 * @param GP_Project $project The sub-project.
														 *
														 * @since 1.0.0
														 *
														 */
														if ( 1 === (int) $sub_project->parent_project_id ) {
															echo esc_html( gp_html_excerpt( apply_filters( 'gp_sub_project_description', i18n::get_instance()->translate( 'plugin_' . (string) $sub_project->slug . '_short_description', (string) $sub_project->description, (string) $sub_project->path . '/readme', true ), $sub_project ), 222 ) );
														} elseif ( 2 === (int) $sub_project->parent_project_id ) {
															echo esc_html( gp_html_excerpt( apply_filters( 'gp_sub_project_description', i18n::get_instance()->translate( 'theme_' . (string) $sub_project->slug . '_short_description', (string) $sub_project->description, (string) $sub_project->path . '/' . (string) $sub_project->slug, true ), $sub_project ), 222 ) );
														} else {
															echo esc_html( gp_html_excerpt( apply_filters( 'gp_sub_project_description', $sub_project->description ?? '', $sub_project ), 111 ) );
														}
														?></small>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="project-status">
                                            <div class="project-status-sub-projects col-3">
                                                <span class="project-status-title">项目</span>
                                                <span class="project-status-value"><?php echo count( $sub_sub_projects ) ?></span>
                                            </div>
                                            <div class="project-status-waiting col-3">
                                                <span class="project-status-title">等待/模糊</span>
                                                <span class="project-status-value"><?php echo (int) $translation_set_total->fuzzy_count + (int) $translation_set_total->waiting_count; ?></span>
                                            </div>
                                            <div class="project-status-remaining col-3">
                                                <span class="project-status-title">总词条</span>
                                                <span class="project-status-value"><?php echo (int) $translation_set_total->all_count; ?></span>
                                            </div>
                                            <div class="project-status-progress col-3">
                                                <span class="project-status-title">进度</span>
                                                <span class="project-status-value"><?php echo (int) $translation_set_total->progress; ?>%</span>
                                            </div>
                                            <div class="progress col-12">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: <?php echo (int) $translation_set_total->progress; ?>%;"
                                                     aria-valuenow="<?php echo (int) $translation_set_total->progress; ?>"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="card-body d-grid"><a
                                                        href="<?php echo gp_url_project( $sub_project ) ?>"
                                                        class="btn btn-primary" one-link-mark="yes"><i
                                                            class=" fad fa-users-medical"></i>参与翻译</a>
                                                <div class="col-12">
													<?php gp_link_project_edit( $sub_project, null, array( 'class' => 'bubble' ) ); ?>
													<?php gp_link_project_delete( $sub_project, null, array( 'class' => 'bubble' ) ); ?>
                                                    <!--<?php
													if ( $sub_project->active ) {
														echo "<span class='active bubble'>" . __( 'Active', 'glotpress' ) . '</span>';
													}
													?>--></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
						<?php endforeach; ?>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-end">
								<?php
								if ( isset( $page ) ) {
									echo gp_pagination( $page, 16, $sub_projects_num );
								}
								?>
                            </ul>
                        </nav>
                    </ul>
				<?php endif; ?>

            </div>
		<?php endif; ?>

    </div>


    <div class="clear"></div>


    <script type="text/javascript" charset="utf-8">
        $gp.showhide('a.personal-options', 'div.personal-options', {
            show_text: '<?php echo __( 'Personal project options', 'glotpress' ) . ' &darr;'; ?>',
            hide_text: '<?php echo __( 'Personal project options', 'glotpress' ) . ' &uarr;'; ?>',
            focus: '#source-url-template',
            group: 'personal'
        });
        jQuery('div.personal-options').hide();
        $gp.showhide('a.project-actions', 'div.project-actions', {
            show_text: '<?php echo __( 'Project actions', 'glotpress' ) . ' &darr;'; ?>',
            hide_text: '<?php echo __( 'Project actions', 'glotpress' ) . ' &uarr;'; ?>',
            focus: '#source-url-template',
            group: 'project'
        });
        jQuery(document).ready(function ($) {
            $(".translation-sets").tablesorter({
                theme: 'glotpress',
                sortList: [[2, 1]],
                headers: {
                    0: {
                        sorter: 'text'
                    }
                },
                widgets: ['zebra']
            });
        });
    </script>
<?php
gp_tmpl_footer();
