<div class="lava-ajax-search-result-page-wrap">

	<h3 class="page-title">
		<?php esc_html_e( "Search Results", 'lvbp-ajax-search' ); ?>
	</h3>

	<div class="lava-ajax-search-form-wrap">
		<?php echo do_shortcode( '[lava_ajax_search_form]' );?>
	</div>
	<div class="lava-ajax-search-results">
		<div class="results-header" role="navigation">
			<ul class="results-tabs">
				<?php lava_ajaxSearch()->core->print_tabs();?>
			</ul>
		</div>
		<div class="results-body">
			<?php lava_ajaxSearch()->core->print_results();?>
		</div>
	</div>
</div>