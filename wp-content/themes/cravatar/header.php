<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/wp-content/themes/cravatar/assets/img/ico.png" type="image/x-icon"/>
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <title><?php wp_title('&#8211;', true, 'right'); ?></title>
    <meta name="keywords" content="cravatar,gravatar,wordpress头像,头像,公共头像"/>
    <meta name="description"
          content="Cravatar 是 Gravatar 在中国的完美替代方案，我们提供独有的三级头像匹配机制，支持在未设置 Cravatar 和 Gravatar 头像时返回 QQ 头像。">
    <?php wp_head(); ?>
    <?php ?>
</head>

<body <?php body_class(); ?>>

<?php
wp_body_open();
?>
<header id="site-header" class="navbar navbar-light bd-navbar navbar-expand-md  w-100">
    <div class="container-fluid container-xxl">
        <a class="navbar-brand" href="/">
            <a class="navbar-brand" href="/" aria-label="Space">
                <img class="navbar-brand-logo mb-2"
                     src="https://dev.litepress.cn/cravatar/wp-content/uploads/sites/9/2022/06/资源-6.png" alt="">
            </a>
            <?php
            /*            $custom_logo_id = get_theme_mod( 'custom_logo' );
                        $logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );
                        if ( has_custom_logo() ) {
                            echo '<img src="' . esc_url( $logo[0] ) . '" alt="' . get_bloginfo( 'name' ) . '" width="' . get_theme_mod( 'lpcn_sections_logo_range', '200' ) . '" >';
                        } else {
                            echo '<h1>' . esc_attr( get_bloginfo( 'name' ) ) . '</h1>';
                        } */ ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse " id="navbarCollapse">
            <section>

                <nav>
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'primary_menu',
                            'container' => false,
                            'items_wrap' => '<ul class="navbar-nav  %2$s">%3$s</ul>',
                            'fallback_cb' => false,
                            'walker' => new Wp_Sub_Menu(),
                        )
                    );
                    ?>


                </nav>



                <?php
                if (is_user_logged_in()) {
                    echo '<ul class="navbar-nav flex-row flex-wrap ms-md-auto">
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://cravatar.cn/avatar/526d5c35b2092765ce9865d807612f33?s=200&amp;test=1&amp;d=mp&amp;r=1656182174" class="avatar avatar-25 me-2" alt="Yulinn">Yulinn
                        </button>
                        <ul class="dropdown-menu" >
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img src="https://cravatar.cn/avatar/526d5c35b2092765ce9865d807612f33?s=200&amp;test=1&amp;d=mp&amp;r=1656182174" class="avatar avatar-40" alt="Yulinn">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"></h5>
                                        <p class="card-text text-body">977869645@qq.com</p>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <li><a class="dropdown-item" href="#">头像管理</a></li>
                            <div class="dropdown-divider"></div>
                            <li><a class="dropdown-item" href="#">注销</a></li>
                        </ul>
                    </li>
                </ul>';
                } else {
                    echo '<nav class="">  
                        <ul class="navbar-nav menu">
                            <li class="btn-sign-in">
                                <a href="https://litepress.cn/user/sso/login" class="nav-link" ><i class="fa-duotone fa-fw fa-right-to-bracket"></i>
                                    登录/注册</a>
                            </li>
                        </ul>
                    </nav>
                    ';
                }
                ?>


                <!--					--><?php
                /*					wp_nav_menu(
                                        array(
                                            'theme_location' => 'register_menu',
                                            'container'      => false,
                                            'items_wrap'     => '<ul class="navbar-nav  %2$s">%3$s</ul>',
                                            'fallback_cb'    => false,
                                            'walker'         => new Wp_Sub_Menu(),
                                        )
                                    );
                                    */ ?>


            </section>
        </div>
    </div>
</header>


<?php echo do_shortcode('[contact-form-7 id="67" title="我要上榜"]') ?>


