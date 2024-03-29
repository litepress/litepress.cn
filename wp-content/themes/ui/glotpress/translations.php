<?php
/**
 * Template for the translations table.
 *
 * @package    GlotPress
 * @subpackage Templates
 */

/** @var GP_Translation_Set $translation_set */

/**
 * 获取父项目
 */

use LitePress\I18n\i18n;

$title          = '';
$parent_project = GP::$project->find_one( array( 'id' => $project->parent_project_id ) );
if ( 1 === (int) $parent_project->parent_project_id ) {
	$parent_project->name = i18n::get_instance()->translate( 'plugin_' . (string) $parent_project->slug . '_title', (string) $parent_project->name, (string) $parent_project->path . '/readme', true );
	$title                = '《' . $parent_project->name . '》的' . $project->name;
} elseif ( 2 === (int) $parent_project->parent_project_id ) {
	$parent_project->name = i18n::get_instance()->translate( 'theme_' . (string) $parent_project->slug . '_title', (string) $parent_project->name, (string) $parent_project->path . '/' . (string) $parent_project->slug, true );
	$title                = "《{$parent_project->name}》";
} elseif ( 5 === (int) $parent_project->parent_project_id ) {
	$parent_project->name = i18n::get_instance()->translate( 'other_' . (string) $parent_project->slug . '_title', (string) $parent_project->name, (string) $parent_project->path . '/' . (string) $parent_project->slug, true );
	$title                = "《{$parent_project->name}》";
} elseif ( 4 === (int) $parent_project->parent_project_id ) {
	$parent_project->name = i18n::get_instance()->translate( 'doc_' . (string) $parent_project->slug . '_title', (string) $parent_project->name, (string) $parent_project->path . '/' . (string) $parent_project->slug, true );
	$title                = "《{$parent_project->name}》";
} else {
	$title = $project->name ?? '';
}

gp_title(
	sprintf(
	/* translators: 1: Translation set name. 2: Project name. */
		__( '翻译%2$s到%1$s - LitePress翻译平台', 'glotpress' ),
		$translation_set->name,
		$title
	)
);
gp_breadcrumb(
	array(
		lpcn_gp_project_links_from_root( $project ),
		gp_link_get( $url, $translation_set->name ),
	)
);
gp_enqueue_scripts( array( 'gp-editor', 'gp-translations-page' ) );
wp_localize_script(
	'gp-translations-page',
	'$gp_translations_options',
	array(
		'sort'   => __( 'Sort', 'glotpress' ),
		'filter' => __( 'Filter', 'glotpress' ),
	)
);

// localizer adds var in front of the variable name, so we can't use $gp.editor.options
$editor_options = compact( 'can_approve', 'can_write', 'url', 'discard_warning_url', 'set_priority_url', 'set_status_url' );

wp_localize_script( 'gp-editor', '$gp_editor_options', $editor_options );

gp_tmpl_header();
$i = 0;
?>

    <style>
        body {
            display: block !important;
        }
    </style>


    <div class="container">
		<?php echo gp_breadcrumb(); ?>
        <div class="notice" id="help-notice">
            你可以通过安装 <a href="https://litepress.cn/store/?woo-free-download=273479" target="_blank">WP-China-Yes</a>
            插件，并切换应用市场为“LitePress 应用市场”来接收翻译推送（只推送翻译率大于 80% 的包）。
        </div>
    </div>

    <div class="container ">

        <div class="translate">

            <div class="filter-toolbar">
                <form id="upper-filters-toolbar" class="filters-toolbar" action="" method="get" accept-charset="utf-8">
                    <div class="filter-menu">

						<?php
						$current_filter = '';
						$filter_links   = array();

						// Use array_filter() to remove empty values, store them for use later if a custom filter has been applied.
						$filters_values_only = array_filter( $filters );
						$sort_values_only    = array_filter( $sort );
						$filters_and_sort    = array_merge( $filters_values_only, $sort_values_only );

						/**
						 * Check to see if a term or user login has been added to the filter or one of the other filter options, if so,
						 * we don't want to match the standard filter links.
						 *
						 * Note: Don't check for the warnings filter here otherwise we won't be able to use this value during the check
						 * to see if the warnings filter link entry is the currently selected filter.
						 */
						$additional_filters = array_key_exists( 'term', $filters_and_sort ) ||
						                      array_key_exists( 'user_login', $filters_and_sort ) ||
						                      array_key_exists( 'with_comment', $filters_and_sort ) ||
						                      array_key_exists( 'case_sensitive', $filters_and_sort ) ||
						                      array_key_exists( 'with_plural', $filters_and_sort ) ||
						                      array_key_exists( 'with_context', $filters_and_sort );

						// Because 'warnings' is not a translation status we need to know if we're filtering on it before we check
						// for what filter links to add.
						$warnings_filter = array_key_exists( 'warnings', $filters_and_sort );

						$all_filters = array(
							'status' => 'current_or_waiting_or_fuzzy_or_untranslated',
						);

						$current_filter_class = array(
							'class' => 'filter-current',
						);

						$is_current_filter = ( array() === array_diff( $all_filters, $filters_and_sort ) || array() === $filters_and_sort ) && ! $additional_filters && ! $warnings_filter;
						$current_filter    = $is_current_filter ? 'all' : $current_filter;

						$filter_links[] = gp_link_get(
							$url,
							// Translators: %s is the total strings count for the current translation set.
							sprintf( __( '<b>%s</b><span>全部</span>', 'glotpress' ), number_format_i18n( $translation_set->all_count() ) ),
							$is_current_filter ? $current_filter_class : array()
						);

						$translated_filters = array(
							'filters[translated]' => 'yes',
							'filters[status]'     => 'current',
						);

						$is_current_filter = array() === array_diff( $translated_filters, $filters_and_sort ) && false === $additional_filters && ! $warnings_filter;
						$current_filter    = $is_current_filter ? 'translated' : $current_filter;

						$filter_links[] = gp_link_get(
							add_query_arg( $translated_filters, $url ),
							// Translators: %s is the translated strings count for the current translation set.
							sprintf( __( '<b>%s</b><span>已翻译</span>', 'glotpress' ), number_format_i18n( $translation_set->current_count() ) ),
							$is_current_filter ? $current_filter_class : array()
						);

						$untranslated_filters = array(
							'filters[status]' => 'untranslated',
						);

						$is_current_filter = array() === array_diff( $untranslated_filters, $filters_and_sort ) && false === $additional_filters && ! $warnings_filter;
						$current_filter    = $is_current_filter ? 'untranslated' : $current_filter;

						$filter_links[] = gp_link_get(
							add_query_arg( $untranslated_filters, $url ),
							// Translators: %s is the untranslated strings count for the current translation set.
							sprintf( __( '<b>%s</b><span>未翻译</span>', 'glotpress' ), number_format_i18n( $translation_set->untranslated_count() ) ),
							$is_current_filter ? $current_filter_class : array()
						);

						$waiting_filters = array(
							'filters[translated]' => 'yes',
							'filters[status]'     => 'waiting',
						);

						$is_current_filter = array() === array_diff( $waiting_filters, $filters_and_sort ) && ! $additional_filters && ! $warnings_filter;
						$current_filter    = $is_current_filter ? 'waiting' : $current_filter;

						$filter_links[] = gp_link_get(
							add_query_arg( $waiting_filters, $url ),
							// Translators: %s is the waiting strings count for the current translation set.
							sprintf( __( '<b>%s</b><span>等待</span>', 'glotpress' ), number_format_i18n( $translation_set->waiting_count() ) ),
							$is_current_filter ? $current_filter_class : array()
						);

						$fuzzy_filters = array(
							'filters[translated]' => 'yes',
							'filters[status]'     => 'fuzzy',
						);

						$is_current_filter = array() === array_diff( $fuzzy_filters, $filters_and_sort ) && ! $additional_filters && ! $warnings_filter;
						$current_filter    = $is_current_filter ? 'fuzzy' : $current_filter;

						$filter_links[] = gp_link_get(
							add_query_arg( $fuzzy_filters, $url ),
							// Translators: %s is the fuzzy strings count for the current translation set.
							sprintf( __( '<b>%s</b><span>模糊</span>', 'glotpress' ), number_format_i18n( $translation_set->fuzzy_count() ) ),
							$is_current_filter ? $current_filter_class : array()
						);

						$warning_filters = array(
							'filters[warnings]' => 'yes',
						);

						$is_current_filter = array() === array_diff( $warning_filters, $filters_and_sort ) && ! $additional_filters && ! array_key_exists( 'status', $filters_and_sort );
						$current_filter    = $is_current_filter ? 'warning' : $current_filter;

						$filter_links[] = gp_link_get(
							add_query_arg( $warning_filters, $url ),
							// Translators: %s is the strings with warnings count for the current translation set.
							sprintf( __( '<b>%s</b><span>警告</span>', 'glotpress' ), number_format_i18n( $translation_set->warnings_count() ) ),
							$is_current_filter ? $current_filter_class : array()
						);

						// If no filter has been selected yet, then add the current filter count to the end of the filter links array.
						if ( '' === $current_filter ) {
							// Build an array or query args to add to the link using the current sort/filter options.
							$custom_filter = array();

							foreach ( $filters_values_only as $key => $value ) {
								$custom_filter[ 'filters[' . $key . ']' ] = $value;
							}

							foreach ( $sort_values_only as $key => $value ) {
								$custom_filter[ 'sort[' . $key . ']' ] = $value;
							}

							$filter_links[] = gp_link_get(
								add_query_arg( $custom_filter, $url ),
								// Translators: %s is the strings with the current filter count for the current translation set.
								sprintf( __( '<b>%s</b><span>当前过滤</span>', 'glotpress' ), number_format_i18n( $total_translations_count ) ),
								$current_filter_class
							);
						}

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo implode( '  ', $filter_links );
						?>


                    </div>
                    <div class="groupedfields">
                        <a href="#"
                           class="btn btn-outline-primary filter far fa-filter"><?php _e( '过滤', 'glotpress' ); ?></a>
						<?php if ( $can_approve ): ?>
                            <a href="#"
                               class="btn btn-outline-primary replace far fa-bomb"><?php _e( '替换', 'glotpress' ); ?></a>
						<?php endif; ?>
                        <a href="#"
                           class="btn btn-outline-primary sort far fa-sort-alpha-down"><?php _e( '排序', 'glotpress' ); ?></a>
                    </div>

                    <div class="filters-expanded filters hidden">
                        <div class="filters-expanded-section">
                            <label for="filters[term]" class="filter-title"><?php _e( 'Term:', 'glotpress' ); ?></label><br/>
                            <input class="form-control" type="text"
                                   value="<?php echo gp_esc_attr_with_entities( gp_array_get( $filters, 'term' ) ); ?>"
                                   name="filters[term]" id="filters[term]"/><br/>

                            <fieldset>
                                <legend class="filter-title"><?php _e( 'Term Scope:', 'glotpress' ); ?></legend>
								<?php
								echo gp_radio_buttons(
									'filters[term_scope]',
									array(
										'scope_originals'    => __( 'Originals only', 'glotpress' ),
										'scope_translations' => __( 'Translations only', 'glotpress' ),
										'scope_context'      => __( 'Context only', 'glotpress' ),
										'scope_references'   => __( 'References only', 'glotpress' ),
										'scope_both'         => __( 'Both Originals and Translations', 'glotpress' ),
										'scope_any'          => __( 'Any', 'glotpress' ),
									),
									gp_array_get( $filters, 'term_scope', 'scope_any' )
								);
								?>
                            </fieldset>
                        </div>

                        <div class="filters-expanded-section">
                            <fieldset>
                                <legend class="filter-title"><?php _e( 'Status:', 'glotpress' ); ?></legend>
								<?php
								echo gp_radio_buttons(
									'filters[status]', // TODO: show only these, which user is allowed to see afterwards.
									array(
										'current_or_waiting_or_fuzzy_or_untranslated' => __( 'Current/waiting/fuzzy + untranslated (All)', 'glotpress' ),
										'current'                                     => __( 'Current only', 'glotpress' ),
										'old'                                         => __( 'Approved, but obsoleted by another translation', 'glotpress' ),
										'waiting'                                     => __( 'Waiting approval', 'glotpress' ),
										'rejected'                                    => __( 'Rejected', 'glotpress' ),
										'untranslated'                                => __( 'Without current translation', 'glotpress' ),
										'either'                                      => __( 'Any', 'glotpress' ),
									),
									gp_array_get( $filters, 'status', 'current_or_waiting_or_fuzzy_or_untranslated' )
								);
								?>
                            </fieldset>
                        </div>

                        <div class="filters-expanded-section">
                            <fieldset>
                                <legend class="filter-title"><?php _e( 'Options:', 'glotpress' ); ?></legend>
                                <input type="checkbox" name="filters[with_comment]" value="yes"
                                       id="filters[with_comment][yes]" <?php gp_checked( 'yes' === gp_array_get( $filters, 'with_comment' ) ); ?>>&nbsp;<label
                                        for='filters[with_comment][yes]'><?php _e( 'With comment', 'glotpress' ); ?></label><br/>
                                <input type="checkbox" name="filters[with_context]" value="yes"
                                       id="filters[with_context][yes]" <?php gp_checked( 'yes' === gp_array_get( $filters, 'with_context' ) ); ?>>&nbsp;<label
                                        for='filters[with_context][yes]'><?php _e( 'With context', 'glotpress' ); ?></label><br/>
                                <input type="checkbox" name="filters[warnings]" value="yes"
                                       id="filters[warnings][yes]" <?php gp_checked( 'yes' === gp_array_get( $filters, 'warnings' ) ); ?>>&nbsp;<label
                                        for='filters[warnings][yes]'><?php _e( 'With warnings', 'glotpress' ); ?></label><br/>
                                <input type="checkbox" name="filters[with_plural]" value="yes"
                                       id="filters[with_plural][yes]" <?php gp_checked( 'yes' === gp_array_get( $filters, 'with_plural' ) ); ?>>&nbsp;<label
                                        for='filters[with_plural][yes]'><?php _e( 'With plural', 'glotpress' ); ?></label><br/>
                                <input type="checkbox" name="filters[case_sensitive]" value="yes"
                                       id="filters[case_sensitive][yes]" <?php gp_checked( 'yes' === gp_array_get( $filters, 'case_sensitive' ) ); ?>>&nbsp;<label
                                        for='filters[case_sensitive][yes]'><?php _e( 'Case sensitive', 'glotpress' ); ?></label>
                            </fieldset>
                        </div>

                        <div class="filters-expanded-section">
                            <label for="filters[user_login]"
                                   class="filter-title"><?php _e( 'User:', 'glotpress' ); ?></label><br/>
                            <input class="form-control" type="text"
                                   value="<?php echo gp_esc_attr_with_entities( gp_array_get( $filters, 'user_login' ) ); ?>"
                                   name="filters[user_login]" id="filters[user_login]"/><br/>
                        </div>

						<?php
						/**
						 * Fires after the translation set filters options.
						 *
						 * This action is inside a DL element.
						 *
						 * @since 2.1.0
						 */
						do_action( 'gp_translation_set_filters_form' );
						?>

                        <div class="filters-expanded-actions">
                            <input type="submit" class="button btn btn-primary"
                                   value="<?php esc_attr_e( 'Apply Filters', 'glotpress' ); ?>" name="filter"/>
                        </div>
                    </div>
                    <div class="filters-expanded replace hidden">
                        <div class="filters-expanded-section">
                            <label for="filters[term_by_replace]" class="filter-title">搜索关键字：</label><br/>
                            <input class="form-control" type="text"
                                   value="<?php echo gp_esc_attr_with_entities( gp_array_get( $filters, 'term_by_replace' ) ); ?>"
                                   name="filters[term_by_replace]" id="filters[term_by_replace]"/>
                            <fieldset>
                                <legend class="filter-title">搜索范围：</legend>
								<?php
								echo gp_radio_buttons(
									'filters[term_by_replace_scope]',
									array(
										'scope_translations' => __( 'Translations only', 'glotpress' ),
									),
									gp_array_get( $filters, 'term_by_replace_scope', 'scope_translations' )
								);
								?>
                            </fieldset>
                            <label for="filters[term]" class="filter-title">替换关键字：</label><br/>
                            <input class="form-control" type="text"
                                   value="<?php echo gp_esc_attr_with_entities( gp_array_get( $filters, 'replace' ) ); ?>"
                                   name="filters[replace]" id="filters[replace]"/><br/>
                        </div>

                        <div class="filters-expanded-actions">
                            <input type="submit" class="button btn btn-primary"
                                   value="应用搜索" name="filter"/>
                            <input type="submit" class="button btn btn-primary"
                                   value="应用替换" name="filter"/>
                        </div>
                    </div>
                    <div class="filters-expanded sort hidden">
                        <div class="filters-expanded-section">
                            <fieldset>
                                <legend class="filter-title"><?php _ex( 'By:', 'sort by', 'glotpress' ); ?></legend>
								<?php
								$default_sort = get_user_option( 'gp_default_sort' );
								if ( ! is_array( $default_sort ) ) {
									$default_sort = array(
										'by'  => 'priority',
										'how' => 'desc',
									);
								}

								$sort_bys = wp_list_pluck( gp_get_sort_by_fields(), 'title' );
								echo gp_radio_buttons( 'sort[by]', $sort_bys, gp_array_get( $sort, 'by', $default_sort['by'] ) );
								?>
                            </fieldset>
                        </div>

                        <div class="filters-expanded-section">
                            <fieldset>
                                <legend class="filter-title"><?php _e( 'Order:', 'glotpress' ); ?></legend>
								<?php
								echo gp_radio_buttons(
									'sort[how]',
									array(
										'asc'  => __( 'Ascending', 'glotpress' ),
										'desc' => __( 'Descending', 'glotpress' ),
									),
									gp_array_get( $sort, 'how', $default_sort['how'] )
								);
								?>
                            </fieldset>
                        </div>

						<?php
						/**
						 * Fires after the translation set sort options.
						 *
						 * This action is inside a DL element.
						 *
						 * @deprecated 2.1.0 Call gp_translation_set_sort_form instead
						 * @since 1.0.0
						 */
						do_action( 'gp_translation_set_filters' );

						/**
						 * Fires after the translation set sort options.
						 *
						 * This action is inside a DL element.
						 *
						 * @since 2.1.0
						 */
						do_action( 'gp_translation_set_sort_form' );
						?>

                        <div class="filters-expanded-actions">
                            <input type="submit" class="button btn btn-primary"
                                   value="<?php esc_attr_e( 'Apply Sorting', 'glotpress' ); ?>" name="sorts"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="translate-body">
                <div class="row filter-bulk">
                    <div class="col-6">
						<?php
						if ( $can_approve ) {
							wcy_gp_translations_bulk_actions_toolbar( $bulk_action, $can_write, $translation_set, 'top' );
						}
						$class_rtl = 'rtl' === $locale->text_direction ? ' translation-sets-rtl' : '';
						?>
                    </div>
                    <div class="col-6">
                        <nav aria-label="Page navigation  ">
                            <ul class="pagination justify-content-end">
								<?php echo gp_pagination( $page, $per_page, $total_translations_count ); ?>
                            </ul>
                        </nav>
                    </div>
                </div>
				<?php
				if ( ! $can_approve ) {
					echo '<style>
	.translate{position: relative;}
	.filter-bulk {
    padding: 10px 0;
    position: absolute;
    right: 12px;
    top: 77px;
}
	</style>';
				}
				?>

                <table id="translations" class="translations clear<?php echo esc_attr( $class_rtl ); ?>">
                    <thead>
                    <tr class="table-row">
						<?php
						if ( $can_approve ) :
							?>
                            <th class="checkbox"><input type="checkbox"/></th>
						<?php
						endif;
						?>
                        <th class="priority"><?php /* Translators: Priority */
							_e( 'Prio', 'glotpress' ); ?></th>
                        <th class="original"><?php _e( 'Original string', 'glotpress' ); ?></th>
                        <th class="translation"><?php _e( 'Translation', 'glotpress' ); ?></th>
                        <th class="actions">&mdash;</th>
                    </tr>
                    </thead>
					<?php
					if ( $glossary ) {
						$glossary_entries       = $glossary->get_entries();
						$glossary_entries_terms = gp_glossary_add_suffixes( $glossary_entries );
					}

					$root_locale          = null;
					$root_translation_set = null;
					$has_root             = null;

					if ( null !== $locale->variant_root ) {
						$root_locale          = GP_Locales::by_slug( $locale->variant_root );
						$root_translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $translation_set->slug, $locale->variant_root );

						// Only set the root translation flag if we have a valid root translation set, otherwise there's no point in querying it later.
						if ( null !== $root_translation_set ) {
							$has_root = true;
						}
					}

					foreach ( $translations as $translation ) {
						if ( ! $translation->translation_set_id ) {
							$translation->translation_set_id = $translation_set->id;
						}

						$can_approve_translation = GP::$permission->current_user_can( 'approve', 'translation', $translation->id, array( 'translation' => $translation ) );
						gp_tmpl_load( 'translation-row', get_defined_vars() );
					}
					?>
					<?php
					if ( ! $translations ) :
						?>
                        <tr>
                            <td colspan="<?php echo $can_approve ? 5 : 4; ?>"><?php _e( 'No translations were found!', 'glotpress' ); ?></td>
                        </tr>
					<?php
					endif;
					?>
                </table>
                <section class="d-flex align-items-center mb-3 flex-wrap">
					<?php
					if ( $can_approve ) {
						wcy_gp_translations_bulk_actions_toolbar( $bulk_action, $can_write, $translation_set, 'bottom' );
					}
					?>
                    <nav aria-label="Page navigation example" class="ms-auto order-1">
                        <ul class="pagination justify-content-end">
							<?php echo gp_pagination( $page, $per_page, $total_translations_count ); ?>
                        </ul>
                    </nav>
                    <div id="legend" class="secondary clearfix">
                        <div><strong><?php _e( 'Legend:', 'glotpress' ); ?></strong></div>
						<?php
						foreach ( GP::$translation->get_static( 'statuses' ) as $legend_status ) :
							?>
                            <div class="box status-<?php echo esc_attr( $legend_status ); ?>"></div>
                            <div>
							<?php
							switch ( $legend_status ) {
								case 'current':
									_e( 'Current', 'glotpress' );
									break;
								case 'waiting':
									_e( 'Waiting', 'glotpress' );
									break;
								case 'fuzzy':
									_e( 'Fuzzy', 'glotpress' );
									break;
								case 'old':
									_e( 'Old', 'glotpress' );
									break;
								case 'rejected':
									_e( 'Rejected', 'glotpress' );
									break;
								default:
									echo esc_html( $legend_status );
							}
							?>
                            </div><?php endforeach; ?>
                        <div class="box has-warnings"></div>
                        <div><?php _e( 'With warnings', 'glotpress' ); ?></div>
						<?php
						if ( $locale->variant_root ) :
							?>
                            <div class="box root-translation"></div>
                            <div><?php _e( 'Root translation', 'glotpress' ); // phpcs:ignore WordPress.Security.EscapeOutput.
								?></div>
						<?php
						endif
						?>
                    </div>
                </section>
                <p class="clear actionlist secondary">
					<?php
					$footer_links = array();
					if ( ( isset( $can_import_current ) && $can_import_current ) || ( isset( $can_import_waiting ) && $can_import_waiting ) ) {
						$footer_links[] = gp_link_get( gp_url_project( $project, array(
							$locale->slug,
							$translation_set->slug,
							'import-translations'
						) ), __( 'Import Translations', 'glotpress' ) );
					}

					/**
					 * The 'default' filter is 'Current/waiting/fuzzy + untranslated (All)', however that is not
					 * the default action when exporting so make sure to set it on the export link if no filter
					 * has been activated by the user.
					 */
					if ( ! array_key_exists( 'status', $filters ) ) {
						$filters['status'] = 'current_or_waiting_or_fuzzy_or_untranslated';
					}

					$export_url     = gp_url_project( $project, array(
						$locale->slug,
						$translation_set->slug,
						'export-translations'
					) );
					$export_link    = gp_link_get(
						$export_url,
						__( 'Export', 'glotpress' ),
						array(
							'id'      => 'export',
							'filters' => add_query_arg( array( 'filters' => $filters ), $export_url ),
						)
					);
					$format_options = array();
					foreach ( GP::$formats as $slug => $format ) {
						$format_options[ $slug ] = $format->name;
					}
					$what_dropdown   = gp_select(
						'what-to-export',
						array(
							'all'      => _x( 'all current', 'export choice', 'glotpress' ),
							'filtered' => _x( 'only matching the filter', 'export choice', 'glotpress' ),
						),
						'all'
					);
					$format_dropdown = gp_select( 'export-format', $format_options, 'po' );
					/* translators: 1: export 2: what to export dropdown (all/filtered) 3: export format */
					$footer_links[] = sprintf( __( '%1$s %2$s as %3$s', 'glotpress' ), $export_link, $what_dropdown, $format_dropdown );

					/**
					 * Filter footer links in translations.
					 *
					 * @param array $footer_links Default links.
					 * @param GP_Project $project The current project.
					 * @param GP_Locale $locale The current locale.
					 * @param GP_Translation_Set $translation_set The current translation set.
					 *
					 * @since 1.0.0
					 *
					 */
					$footer_links = apply_filters( 'gp_translations_footer_links', $footer_links, $project, $locale, $translation_set );

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo implode( ' &bull; ', $footer_links );
					?>
                </p>

            </div>
        </div>
    </div>

<?php
gp_tmpl_footer();
