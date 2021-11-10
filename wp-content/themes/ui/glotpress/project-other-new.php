<?php
gp_title( __( '申请托管第三方项目 - LitePress翻译平台', 'glotpress' ) );
gp_breadcrumb(
	array(
		'<a href="/translate/projects/">项目</a>',
		'<a href="/translate/projects/others/">第三方托管</a>',
		'<a href="/translate/projects/others/-new/">申请</a>',
	)
);
gp_tmpl_header();
?>
    <div class="container">
		<?php echo gp_breadcrumb(); ?>
    </div>
    <div class="container ">
        <div class="setting mb-4">
        <h2><?php _e( '申请托管第三方项目', 'glotpress' ); ?></h2>
        <?php echo do_shortcode('[contact-form-7 id="1766" title="翻译托管申请"]') ?>
        </div>
    </div>
<?php
gp_tmpl_footer();
