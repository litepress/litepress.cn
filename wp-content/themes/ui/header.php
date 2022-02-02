<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
	<?php
	$yoast_kw = get_post_meta( get_the_ID(), "_yoast_wpseo_focuskw", true );
	if ( ! empty( $yoast_kw ) ) {
		echo "<meta name='keywords' content='{$yoast_kw}' />\n";
	}
	?>
	<?php /** 标题不在翻译平台输出，因为翻译平台有自己的标题机制 */ ?>

	<?php global $blog_id; ?>

	<?php if ( 4 !== (int) $blog_id ): ?>
        <title><?php wp_title('&#8211;', true, 'right'); ?></title>
	<?php endif; ?>

	<?php wp_head(); ?>
	<?php ?>
</head>

<body <?php body_class(); ?>>
<div class="py-3 bg-dark bg-pattern @@classList">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center text-white">
              <span class="heading-xxs letter-spacing-xl">
               平台开发中，欢迎参与测试。你可以在<a href="https://jq.qq.com/?_wv=1027&k=AizcubYC"> QQ群:1046115671 </a>中与我们交流，或是直接在社区发帖。
              </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
wp_body_open();
?>
<?php get_template_part( 'template-parts/header/site-header' ); ?>
