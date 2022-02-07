<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">

	<?php
	/** 标题不在翻译平台输出，因为翻译平台有自己的标题机制 */
	global $blog_id;

	if ( 4 !== (int) $blog_id ): ?>
        <title><?php wp_title( '&#8211;', true, 'right' ); ?></title>
	<?php endif; ?>

	<?php
	/** 输出页面关键字 */
	$keywords = apply_filters( 'lpcn_seo_keywords', false );
	if ( ! empty( $keywords ) ): ?>
        <meta name="keywords" content="<?php echo $keywords; ?>">
	<?php endif; ?>

	<?php
	/** 输出页面描述 */
	$description = apply_filters( 'lpcn_seo_description', false );
	if ( ! empty( $description ) ): ?>
        <meta name="description" content="<?php echo $description; ?>">
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
