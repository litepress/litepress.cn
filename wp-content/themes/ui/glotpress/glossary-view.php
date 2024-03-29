<?php
gp_title( __( '术语表 - LitePress翻译平台', 'glotpress' ) );
gp_breadcrumb(
	array(
		'<a href="/translate/">翻译平台</a>',
		'术语表',
	)
);

$ge_delete_ays    = __( 'Are you sure you want to delete this entry?', 'glotpress' );
$delete_url       = gp_url_join( $url, '-delete' );
$glossary_options = compact( 'can_edit', 'url', 'delete_url', 'ge_delete_ays' );

gp_enqueue_scripts( 'gp-glossary' );
wp_localize_script( 'gp-glossary', '$gp_glossary_options', $glossary_options );

gp_tmpl_header();

/* translators: 1: Locale english name. 2: Project name. */
$glossary_title = __( 'Glossary for %1$s translation of %2$s', 'glotpress' );
if ( 0 === $project->id ) {
	/* translators: %s: Locale english name. */
	$glossary_title = '术语表';
}
?>
<main class="container">
    <?php echo gp_breadcrumb(); ?>

    <section class="toolbar">
        <div class="text-center">
        <ul>
        <?php gp_link_glossary_edit( $glossary, $translation_set, _x( '(edit)', 'glossary', 'glotpress' ) ); ?>
        <?php gp_link_glossary_delete( $glossary, $translation_set, _x( '(delete)', 'glossary', 'glotpress' ) ); ?>
        </ul>
        </div>
        <?php
        /**
         * Filter a glossary description.
         *
         * @since 3.0.0
         *
         * @param string      $description Glossary description.
         * @param GP_Glossary $project     The current glossary.
         */
        $glossary_description = apply_filters( 'gp_glossary_description', $glossary->description, $glossary );

        if ( $glossary_description ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized via filters.
            echo '<div class="glossary-description">' . $glossary_description . '</div>';
        }
        ?>

    </section>

    <style>
        body {
            display: block !important;
        }
    </style>
<table class="glossary translations wp-card " id="glossary">
	<thead>
		<tr>
			<th style="width:20%"><?php _ex( 'Item', 'glossary entry', 'glotpress' ); ?></th>
			<th style="width:20%"><?php _ex( 'Part of speech', 'glossary entry', 'glotpress' ); ?></th>
			<th style="width:20%"><?php _ex( 'Translation', 'glossary entry', 'glotpress' ); ?></th>
			<th style="width:30%"><?php _ex( 'Comments', 'glossary entry', 'glotpress' ); ?></th>
		<?php if ( $can_edit ) : ?>
			<th style="width:10%">&mdash;</th>
		<?php endif; ?>
		</tr>
	</thead>
	<tbody>
<?php
	if ( count( $glossary_entries ) > 0 ) {
		foreach ( $glossary_entries as $entry ) {
			gp_tmpl_load( 'glossary-entry-row', get_defined_vars() );
		}
	} else {
		?>
		<tr>
			<td colspan="5">
				<?php _e( 'No glossary entries yet.', 'glotpress' ); ?>
			</td>
		</tr>
		<?php
	}
?>
		<?php if ( $can_edit ) : ?>
		<tr>
			<td colspan="5">
				<h4><?php _e( 'Create an entry', 'glotpress' ); ?></h4>

				<form action="<?php echo esc_url( gp_url_join( $url, '-new' ) ); ?>" method="post">
					<dl>
						<dt><label for="new_glossary_entry_term"><?php echo esc_html( _x( 'Original term:', 'glossary entry', 'glotpress' ) ); ?></label></dt>
						<dd><input class="form-control" type="text" name="new_glossary_entry[term]" id="new_glossary_entry_term" value=""></dd>
						<dt><label for="new_glossary_entry_post"><?php _ex( 'Part of speech', 'glossary entry', 'glotpress' ); ?></label></dt>
						<dd>
							<select class="form-select" name="new_glossary_entry[part_of_speech]" id="new_glossary_entry_post">
							<?php
								foreach ( GP::$glossary_entry->parts_of_speech as $pos => $name ) {
									echo "\t<option value='" . esc_attr( $pos ) . "'>" . esc_html( $name ) . "</option>\n";
								}
							?>
							</select>
						</dd>
						<dt><label for="new_glossary_entry_translation"><?php _ex( 'Translation', 'glossary entry', 'glotpress' ); ?></label></dt>
						<dd><input class="form-control" type="text" name="new_glossary_entry[translation]" id="new_glossary_entry_translation" value=""></dd>
						<dt><label for="new_glossary_entry_comments"><?php _ex( 'Comments', 'glossary entry', 'glotpress' ); ?></label></dt>
						<dd><textarea class="form-control" type="text" name="new_glossary_entry[comment]" id="new_glossary_entry_comments"></textarea></dd>
					</dl>
					<p>
						<input type="hidden" name="new_glossary_entry[glossary_id]" value="<?php echo esc_attr( $glossary->id ); ?>">
						<input class="btn btn-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Create', 'glotpress' ); ?>" id="submit" />
					</p>
					<?php gp_route_nonce_field( 'add-glossary-entry_' . $project->path . $locale->slug . $translation_set->slug ); ?>
				</form>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<section class="clear actionlist secondary wp-card p-3 mb-4">
	<?php if ( $can_edit ) : ?>
		<?php echo gp_link( gp_url_join( gp_url_project_locale( $project->path, $locale_slug, $translation_set_slug ), array( 'glossary', '-import' ) ), __( 'Import', 'glotpress' ) ); ?>  &bull;&nbsp;
	<?php endif; ?>

	<?php echo gp_link( gp_url_join( gp_url_project_locale( $project->path, $locale_slug, $translation_set_slug ), array( 'glossary', '-export' ) ), __( 'Export as CSV', 'glotpress' ) ); ?>
</section>

</main>
<?php
gp_tmpl_footer();
