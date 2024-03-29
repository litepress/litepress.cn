<?php
/**
 * Template for the meta section of the editor row in a translation set display
 *
 * @package    GlotPress
 * @subpackage Templates
 */

$more_links = array();
if ( $translation->translation_status ) {
	$translation_permalink = gp_url_project_locale(
		$project,
		$locale->slug,
		$translation_set->slug,
		array(
			'filters[status]'         => 'either',
			'filters[original_id]'    => $translation->original_id,
			'filters[translation_id]' => $translation->id,
		)
	);

	$more_links['translation-permalink'] = '<a tabindex="-1" href="' . esc_url( $translation_permalink ) . '">' . __( 'Permalink to this translation', 'glotpress' ) . '</a>';
} else {
	$original_permalink = gp_url_project_locale( $project, $locale->slug, $translation_set->slug, array( 'filters[original_id]' => $translation->original_id ) );

	$more_links['original-permalink'] = '<a tabindex="-1" href="' . esc_url( $original_permalink ) . '">' . __( 'Permalink to this original', 'glotpress' ) . '</a>';
}

$original_history = gp_url_project_locale(
	$project,
	$locale->slug,
	$translation_set->slug,
	array(
		'filters[status]'      => 'either',
		'filters[original_id]' => $translation->original_id,
		'sort[by]'             => 'translation_date_added',
		'sort[how]'            => 'asc',
	)
);

$more_links['history'] = '<a tabindex="-1" href="' . esc_url( $original_history ) . '">' . __( 'All translations of this original', 'glotpress' ) . '</a>';

/**
 * Allows to modify the more links in the translation editor.
 *
 * @since 2.3.0
 *
 * @param array $more_links The links to be output.
 * @param GP_Project $project Project object.
 * @param GP_Locale $locale Locale object.
 * @param GP_Translation_Set $translation_set Translation Set object.
 * @param GP_Translation $translation Translation object.
 */
$more_links = apply_filters( 'gp_translation_row_template_more_links', $more_links, $project, $locale, $translation_set, $translation );

?>
<div class="meta">
	<h3><?php _e( 'Meta', 'glotpress' ); ?></h3>

	<?php gp_tmpl_load( 'translation-row-editor-meta-status', get_defined_vars() ); ?>

	<?php if ( property_exists( $translation, 'root_translation_set_id' ) ) : ?>
	<dl>
		<dt><?php _e( 'Root Translation:', 'glotpress' ); ?></dt>
	<?php if ( $translation->translation_set_id === $translation->root_translation_set_id ) : ?>
		<dd>
<?php
			gp_link(
				gp_url_project_locale(
					$project,
					$root_locale->slug,
					$root_translation_set->slug,
					array(
						'filters[status]'         => 'either',
						'filters[original_id]'    => $translation->original_id,
						'filters[translation_id]' => $translation->id,
					)
				),
				$root_translation_set->name_with_locale()
			);
?>
		</dd>
	<?php else : ?>
		<dd><?php _e( 'False', 'glotpress' ); ?></dd>
	<?php endif; ?>
	</dl>
	<?php endif; ?>
	<?php if ( $translation->context ) : ?>
		<dl>
			<dt><?php _e( 'Context:', 'glotpress' ); ?></dt>
			<dd><span class="btn btn-secondary"><?php echo esc_translation( $translation->context ); ?></span></dd>
		</dl>
	<?php endif; ?>
	<?php if ( $translation->extracted_comments ) : ?>
		<dl>
			<dt><?php _e( 'Comment:', 'glotpress' ); ?></dt>
			<dd>
				<?php
				/**
				 * Filters the extracted comments of an original.
				 *
				 * @param string         $extracted_comments Extracted comments of an original.
				 * @param GP_Translation $translation        Translation object.
				 */
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'gp_original_extracted_comments', $translation->extracted_comments, $translation );
				?>
			</dd>
		</dl>
	<?php endif; ?>
	<?php if ( $translation->translation_added && '0000-00-00 00:00:00' !== $translation->translation_added ) : ?>
		<dl>
			<dt><?php _e( 'Date added (GMT):', 'glotpress' ); ?></dt>
			<dd id="gmt-date-added-<?php echo esc_attr( $translation->row_id ); ?>"><?php echo esc_html( $translation->translation_added ); ?></dd>
		</dl>
		<dl>
			<dt><?php _e( 'Date added (local):', 'glotpress' ); ?></dt>
			<dd id="local-date-added-<?php echo esc_attr( $translation->row_id ); ?>"><?php _e( 'Calculating...', 'glotpress' ); ?></dd>
		</dl>
	<?php endif; ?>
	<?php if ( $translation->user ) : ?>
		<dl>
			<dt><?php _e( 'Translated by:', 'glotpress' ); ?></dt>
			<dd><?php gp_link_user( $translation->user ); ?></dd>
		</dl>
	<?php endif; ?>
	<?php if ( $translation->user_last_modified && ( ! $translation->user || $translation->user->ID !== $translation->user_last_modified->ID ) ) : ?>
		<dl>
			<dt>
			<?php
			if ( 'current' === $translation->translation_status ) {
				_e( 'Approved by:', 'glotpress' );
			} elseif ( 'rejected' === $translation->translation_status ) {
				_e( 'Rejected by:', 'glotpress' );
			} else {
				_e( 'Last updated by:', 'glotpress' );
			}
			?>
			</dt>
			<dd><?php gp_link_user( $translation->user_last_modified ); ?></dd>
		</dl>
	<?php endif; ?>
	<?php references( $project, $translation ); ?>



	<dl>
		<dt><?php _e( 'More links:', 'glotpress' ); ?>
			<ul>
				<?php foreach ( $more_links as $more_link ) : ?>
					<li>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $more_link;
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</dt>
	</dl>
</div>
