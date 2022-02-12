<?php
/**
 * Displays the site header.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

$wrapper_classes = 'site-header';
$wrapper_classes .= has_custom_logo() ? ' has-logo' : '';
$wrapper_classes .= ( true === get_theme_mod( 'display_title_and_tagline', true ) ) ? ' has-title-and-tagline' : '';
$wrapper_classes .= has_nav_menu( 'primary' ) ? ' has-menu' : '';
?>

<?php
global $blog_id;
$current_blog_id = $blog_id;

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
                    <div class=" header-sign">

						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'register_menu',
								'container'      => false,
								'items_wrap'     => '<ul class="navbar-nav  %2$s">%3$s</ul>',
								'fallback_cb'    => false,
								'walker'         => new WCY_Sub_Menu(),
							)
						);
						?>
                        <nav class="hide">
                            <ul class="navbar-nav menu">
                                <li class=""><a
                                            href="https://litepress.cn/login" class="nav-link" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal"><i
                                                class="fas fa-sign-in-alt" aria-hidden="true"></i> 登录</a></li>
                                <li class=""><a
                                            href="https://litepress.cn/register" class="nav-link"><i
                                                class="fas fa-user-plus" aria-hidden="true"></i> 注册</a></li>
                            </ul>

                        </nav>
                    </div>
                </section>
            </div>
        </div>
    </header>
<?php switch_to_blog( $current_blog_id ); ?>
<?php
switch ( $blog_id ) {
	case 1:
		// 主站
		if ( str_starts_with( $_SERVER['REQUEST_URI'], '/forums' ) ) {
			get_template_part( 'template-parts/header/sub/forums-header' );
		}

		break;
	case 3:
		// 商城
		get_template_part( 'template-parts/header/site-sub-banner' );
		break;
	case 4:
		// 翻译平台
		if ( '/translate/settings' === $_SERVER['REQUEST_URI'] ) {
			get_template_part( 'template-parts/header/sub/translate-settings-header' );
			break;
		}

		get_template_part( 'template-parts/header/sub/translate-header' );
		break;
	case 11:
	case 6:
		// 文档平台
		get_template_part( 'template-parts/header/sub/docs-header' );
		break;
	case 14:
		// 文档平台
		get_template_part( 'template-parts/header/sub/news-header' );
		break;

	default:
		break;

}
