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
<div class="py-3 bg-dark  z-index-1">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center text-white">
              <span class="heading-xxs letter-spacing-xl">
               平台开发中，欢迎参与测试。你可以在<a href="https://jq.qq.com/?_wv=1027&k=AizcubYC"> QQ群:1046115671 </a>中与我们交流，或是直接在<a href="/create">社区发帖</a>。
              </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
wp_body_open();

$wrapper_classes = 'site-header';
$wrapper_classes .= has_custom_logo() ? ' has-logo' : '';
$wrapper_classes .= ( true === get_theme_mod( 'display_title_and_tagline', true ) ) ? ' has-title-and-tagline' : '';
$wrapper_classes .= has_nav_menu( 'primary' ) ? ' has-menu' : '';

switch_to_blog( 1 );
?>
<header id="site-header" class="navbar navbar-expand-md navbar-light bg-white wp-nav  sticky-top" role="banner">
    <div class="container-fluid container-xxl">
        <a class="navbar-brand" href="/">
			<?php
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( has_custom_logo() ) {
				echo '<img src="' . esc_url( $logo[0] ) . '" alt="' . get_bloginfo( 'name' ) . '" width="' . get_theme_mod( 'lpcn_sections_logo_range', '50' ) . '" >';
			} else {
				echo '<h1>' . esc_attr( get_bloginfo( 'name' ) ) . '</h1>';
			} ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <section>
                <nav>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary_menu',
							'container'      => false,
							'items_wrap'     => '<ul class="navbar-nav  %2$s">%3$s</ul>',
							'fallback_cb'    => false,
							'walker'         => new WCY_Sub_Menu(),
						)
					);
					?>
                </nav>
                <div class="header-search">
                    <form style="max-width: 640px" class="m-auto header-search" method="get" action="/search">
                        <div class="position-relative">
                            <input type="text" name="keyword" class="form-control" placeholder="全局搜索……">
                            <button type="submit">
                                <div class="actions"><i class="fad fa-search"></i>

                                </div>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="header-sign">

                    <?php
                    if ( is_user_logged_in() ) {
                        wp_nav_menu(
                            array(
                                'theme_location' => 'register_menu',
                                'container'      => false,
                                'items_wrap'     => '<ul class="navbar-nav  %2$s">%3$s</ul>',
                                'fallback_cb'    => false,
                                'walker'         => new WCY_Sub_Menu(),
                            )
                        );
                    } else {
                        echo '<nav class=""> 
                        <ul class="navbar-nav menu">
                            <li class="">
                                <a href="/login" class="nav-link" data-bs-toggle="modal"
                                   data-bs-target="#sign-in"><i class="fa-duotone fa-right-to-bracket"></i>
                                    登录</a>
                            </li>
                            <li class="">
                                <a href="/register" class="nav-link"><i class="fa-duotone fa-user-plus"></i> 注册</a></li>
                        </ul>
                    </nav>
                    ';
                    }
                    ?>

                </div>
            </section>
        </div>
    </div>
</header>
<?php
restore_current_blog();
lpcn_use_sub_template( 'header' );
?>
