<?php
/**
 * Template for the editor part of a single translation row in a translation set display
 *
 * @package    GlotPress
 * @subpackage Templates
 */

/**
 * Filter to update colspan of editor. Decrease to add an extra column
 * with action 'gp_translation_row_editor_columns'.
 *
 * @param int $colspan The colspan of editor column.
 * @since 3.0.0
 *
 */
$colspan = apply_filters('gp_translation_row_editor_colspan', $can_approve ? 5 : 4);

$singular = sprintf(
/* translators: %s: Original singular form of the text */
    __('Singular: %s', 'glotpress'),
    '<span class="original">' . $translation_singular . '</span>'
);
$plural = sprintf(
/* translators: %s: Original plural form of the text */
    __('Plural: %s', 'glotpress'),
    '<span class="original">' . (isset($translation->plural_glossary_markup) ? $translation->plural_glossary_markup : esc_translation($translation->plural)) . '</span>'
);

?>
<tr class="editor <?php gp_translation_row_classes($translation); ?>"
    id="editor-<?php echo esc_attr($translation->row_id); ?>" row="<?php echo esc_attr($translation->row_id); ?>">
    <td colspan="<?php echo esc_attr($colspan); ?>">
        <div class="editor-panel">
            <div class="editor-panel__left">
                <div class="panel-content">
                    <div class="source-string strings">
                        <?php if (!$translation->plural) : ?>
                            <p class="original"><?php echo prepare_original($translation_singular); ?></p>
                            <p class="original_raw"><?php echo esc_translation($translation->singular); ?></p>
                    </div>

                            <?php textareas($translation, array($can_edit, $can_approve_translation)); ?>
                        <?php else : ?>
                            <?php if (absint($locale->nplurals) === 2 && 'n != 1' === $locale->plural_expression) : ?>
                                <p class="original_raw"><?php echo esc_translation($translation->singular); ?></p>
                                <p style="display: none" class="original_plural_raw"></p>
                                <!-- Nav tabs -->
                                <ul class="nav lp-nav-tabs nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#s-<?php echo esc_attr($translation->row_id); ?>">单数</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#p-<?php echo esc_attr($translation->row_id); ?>">复数</a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div class="tab-pane container active" id="s-<?php echo esc_attr($translation->row_id); ?>">
                                        <p>
		                                    <?php
		                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		                                    echo $singular;
		                                    ?>
                                            <span class="original_raw"><?php echo esc_translation($translation->singular); ?></span>
                                        </p>
	                                    <?php textareas($translation, array($can_edit, $can_approve), 0); ?>
                                    </div>
                                    <div class="tab-pane container fade" id="p-<?php echo esc_attr($translation->row_id); ?>">
                                        <p class="clear">
		                                    <?php
		                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		                                    echo $plural;
		                                    ?>
                                            <span class="original_raw"><?php echo esc_translation($translation->plural); ?></span>
                                        </p>

	                                    <?php textareas($translation, array($can_edit, $can_approve), 1); ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!--
                                TODO: labels for each plural textarea and a sample number
                                -->
                                <p>
                                    <?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    echo $singular;
                                    ?>
                                </p>
                                <p class="clear">
                                    <?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    echo $plural;
                                    ?>
                                </p>
                                <?php foreach (range(0, $locale->nplurals - 1) as $plural_index) : ?>
                                    <?php if ($locale->nplurals > 1) : ?>
                                        <p class="plural-numbers">
                                            <?php
                                            printf(
                                            /* translators: %s: Numbers */
                                                __('This plural form is used for numbers like: %s', 'glotpress'),
                                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                '<span class="numbers">' . implode(', ', $locale->numbers_for_index($plural_index)) . '</span>'
                                            );
                                            ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php textareas($translation, array($can_edit, $can_approve), $plural_index); ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php gp_tmpl_load('translation-row-editor-actions', get_defined_vars()); ?>

                        <div class="suggestions-wrapper">
                            <?php do_action('wporg_translate_suggestions', $translation); ?>
                        </div>

                </div>
            </div>
        </div>
        <?php gp_tmpl_load('translation-row-editor-meta', get_defined_vars()); ?>
    </td>
    <?php
    /**
     * Fires after editor column.
     *
     * @param GP_Translation $translation The current translation.
     * @param GP_Translation_Set $translation_set The current translation set.
     * @since 3.0.0
     *
     */
    do_action('gp_translation_row_editor_columns', $translation, $translation_set);
    ?>
</tr>


