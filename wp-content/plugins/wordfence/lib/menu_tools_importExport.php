<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Import/Export Options', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
		});
	})(jQuery);
</script>
<div id="wf-tools-importexport">
	<div class="wf-section-title">
		<h2><?php esc_html_e('Import/Export Options', 'wordfence') ?></h2>
		<span><?php echo wp_kses(sprintf(
			/* translators: URL to support page. */
				__('<a href="%s" target="_blank" rel="noopener noreferrer" class="wf-help-link">Learn more<span class="wf-hidden-xs"> about importing and exporting options</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_TOOLS_IMPORT_EXPORT)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array()), 'span'=>array('class'=>array()))); ?>
			<i class="wf-fa wf-fa-external-link" aria-hidden="true"></i></span>
	</div>
	
	<p><?php esc_html_e("To clone one site's configuration to another, use the import/export tools below.", 'wordfence') ?></p>
	
	<?php
	echo wfView::create('dashboard/options-group-import', array(
		'stateKey' => 'global-options-import',
		'collapseable' => false,
	))->render();
	?>
</div>