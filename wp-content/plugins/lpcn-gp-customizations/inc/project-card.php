<?php

namespace LitePress\GlotPress\Customizations\Inc;

use GP_Project;
use LitePress\I18n\i18n;
use stdClass;

add_action( 'lpcn_gp_project_card', 'LitePress\GlotPress\Customizations\Inc\lpcn_gp_plugin_card', 10, 3 );
add_action( 'lpcn_gp_project_card', 'LitePress\GlotPress\Customizations\Inc\lpcn_gp_theme_card', 10, 3 );
add_action( 'lpcn_gp_project_card', 'LitePress\GlotPress\Customizations\Inc\lpcn_gp_other_card', 10, 3 );

function lpcn_gp_plugin_card( GP_Project $sub_project, stdClass $translation_set_total, int $sub_sub_projects_count ) {
	if ( 1 !== (int) $sub_project->parent_project_id ) {
		return;
	}

	?>
    <div class="card theme-boxshadow">
        <div class="row g-0">
            <div class="project-top row">
                <div class="col-5 center">
                    <img class="plugin-icon"
                         src="https://ps.w.org.ibadboy.net/<?php echo $sub_project->slug ?? '' ?>/assets/icon-128x128.png"
                         onError="this.src='https://cravatar.cn/avatar/<?php echo md5( $sub_project->slug ) ?>?d=identicon&s=133';">
                </div>
                <div class="col-7">
                    <h5 class="card-title"><a href="<?php echo gp_url_project( $sub_project ) ?>"
                                              one-link-mark="yes"><?php echo i18n::get_instance()->translate( 'plugin_' . (string) $sub_project->slug . '_title', (string) $sub_project->name, (string) $sub_project->path . '/readme', true ); ?></a>
                    </h5>
                    <p class="card-text project-description">
                        <small>
							<?php
							echo esc_html( gp_html_excerpt( apply_filters( 'gp_sub_project_description', i18n::get_instance()->translate( 'plugin_' . (string) $sub_project->slug . '_short_description', (string) $sub_project->description, (string) $sub_project->path . '/readme', true ), $sub_project ), 222 ) );
							?>
                        </small>
                    </p>
                </div>
            </div>

            <div class="project-status">
                <div class="project-status-sub-projects col-3">
                    <span class="project-status-title">项目</span>
                    <span class="project-status-value"><?php echo $sub_sub_projects_count ?></span>
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
	<?php
}

function lpcn_gp_theme_card( GP_Project $sub_project, stdClass $translation_set_total, int $sub_sub_projects_count ) {
	if ( 2 !== (int) $sub_project->parent_project_id ) {
		return;
	}

	?>
    <div class="card theme-boxshadow">
        <div class="row g-0">
            <div class="project-top row">
                <div class="col-5 center">
                    <img class="plugin-icon"
                         src="https://i0.wp.com/themes.svn.wordpress.org/<?php echo $sub_project->slug ?? '' ?>/<?php echo gp_get_meta( 'project', $sub_project->id, 'version' ) ?>/screenshot.png"
                         onError="this.src='https://i0.wp.com/themes.svn.wordpress.org/<?php echo $sub_project->slug ?? '' ?>/<?php echo gp_get_meta( 'project', $sub_project->id, 'version' ) ?>/screenshot.jpg';">
                </div>
                <div class="col-7">
                    <h5 class="card-title">
                        <a href="<?php echo gp_url_project( $sub_project ) ?>"
                           one-link-mark="yes"><?php echo i18n::get_instance()->translate( 'theme_' . (string) $sub_project->slug . '_title', (string) $sub_project->name, (string) $sub_project->path . '/' . (string) $sub_project->slug, true ); ?></a>
                    </h5>
                    <p class="card-text project-description">
                        <small>
							<?php
							echo esc_html( gp_html_excerpt( apply_filters( 'gp_sub_project_description', i18n::get_instance()->translate( 'theme_' . (string) $sub_project->slug . '_short_description', (string) $sub_project->description, (string) $sub_project->path . '/' . (string) $sub_project->slug, true ), $sub_project ), 222 ) );
							?>
                        </small>
                    </p>
                </div>
            </div>

            <div class="project-status">
                <div class="project-status-sub-projects col-3">
                    <span class="project-status-title">项目</span>
                    <span class="project-status-value"><?php echo $sub_sub_projects_count ?></span>
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
	<?php
}

function lpcn_gp_other_card( GP_Project $sub_project, stdClass $translation_set_total, int $sub_sub_projects_count ) {
	if ( 5 !== (int) $sub_project->parent_project_id ) {
		return;
	}

	?>
    <div class="card theme-boxshadow">
        <div class="row g-0">
            <div class="project-top row">
                <div class="col-5 center">
					<?php
					$icon = gp_get_meta( 'project', $sub_project->id, 'icon' );

					if ( $icon ) {
						printf( '<img class="plugin-icon" src="%s">', $icon );
					} else {
						printf( '<img class="plugin-icon" src="https://cravatar.cn/avatar/%s?d=identicon&s=133">', md5( $sub_project->slug ) );
					}
					?>
                </div>
                <div class="col-7">
                    <h5 class="card-title">
                        <a href="<?php echo gp_url_project( $sub_project ) ?>"
                           one-link-mark="yes"><?php echo (string) $sub_project->name; ?></a>
                    </h5>
                    <p class="card-text project-description">
                        <small>
							<?php
							echo esc_html( gp_html_excerpt( apply_filters( 'gp_sub_project_description', $sub_project->description ?? '', $sub_project ), 111 ) );
							?>
                        </small>
                    </p>
                </div>
            </div>

            <div class="project-status">
                <div class="project-status-sub-projects col-3">
                    <span class="project-status-title">项目</span>
                    <span class="project-status-value"><?php echo $sub_sub_projects_count ?></span>
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
	<?php
}

/**
 * 这是一个通用的卡片模板，默认情况并不会被使用，请复制一个副本并改成自己的。
 *
 * @param \GP_Project $sub_project
 * @param \stdClass $translation_set_total
 * @param int $sub_sub_projects_count
 */
function lpcn_gp_project_default_card( GP_Project $sub_project, stdClass $translation_set_total, int $sub_sub_projects_count ) {
	?>
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
                    <span class="project-status-value"><?php echo $sub_sub_projects_count ?></span>
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
	<?php
}