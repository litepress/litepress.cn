<?php
/**
 * Displays the site header.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

$wrapper_classes  = 'site-header';
$wrapper_classes .= has_custom_logo() ? ' has-logo' : '';
$wrapper_classes .= ( true === get_theme_mod( 'display_title_and_tagline', true ) ) ? ' has-title-and-tagline' : '';
$wrapper_classes .= has_nav_menu( 'primary' ) ? ' has-menu' : '';
?>


<header id="site-header" class="navbar navbar-expand-md navbar-light bg-white wp-nav  sticky-top" role="banner">
    <div class="container-fluid container-xxl">
        <a class="navbar-brand" href="#"><img class="wp-logo"
                                              src="https://wp-china-yes.com//wp-content/uploads/2020/08/sxy-2048x410.png"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav  mb-2 mb-md-0 ms-2 mx-5">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="https://wp-china-yes.com">社区</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://wp-china-yes.com/store/app-category/plugins">插件</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://wp-china-yes.com/store/app-category/plugins">主题</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://wp-china-yes.com/store/app-category/plugins">文档</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://wp-china-yes.com/store/app-category/plugins">翻译</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        关于
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="https://wp-china-yes.com/archives/category/finance">财务</a>
                        </li>
                    </ul>
                </li>

            </ul>


            <div class="lava-ajax-search-form-wrap header-search">
                <form method="get" action="https://wp-china-yes.com/"><input type="text" name="s" value=""
                                                                             autocomplete="off" placeholder="搜索……"
                                                                             data-search-input=""
                                                                             class="ui-autocomplete-input">
                    <button type="submit">
                        <div class="actions"><i class="fas fa-search"></i>
                            <div class="loading">
                                <i class="fa fa-spin fa-spinner"></i>
                            </div>
                            <div class="clear hidden">
                                <i class="fa fa-close"></i>
                            </div>
                        </div>
                    </button>
                </form>
            </div>


            <div class="ms-auto header-sign">
                <ul class="navbar-nav  mb-2 mb-md-0 ms-2 mx-5">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <img
                                loading="lazy"
                                src="https://gravatar.wp-china-yes.net/avatar/245467ef31b6f0addc72b039b94122a4?s=400&amp;r=G&amp;d=mystery"
                                class="gravatar avatar avatar-40 um-avatar um-avatar-gravatar fl-node-60015f8de9860-img-2"
                                width="40" height="40" alt="孙锡源"
                                data-default="https://wp-china-yes.com/wp-content/plugins/ultimate-member/assets/img/default_avatar.jpg"
                                onerror="if ( ! this.getAttribute('data-load-error') ){ this.setAttribute('data-load-error', '1');this.setAttribute('src', this.getAttribute('data-default'));}"
                                style="">ibadboy
                        </a>


                        <ul class="dropdown-menu sub-menu" aria-labelledby="navbarDropdown">
                            <li id="menu-item-2048"
                                class="dropdown-item menu-item-type-post_type menu-item-object-page"><a
                                    href="https://wp-china-yes.com/user" one-link-mark="yes"><i
                                        class="fas fa-user-alt" data-icon="fas fa-user-alt"></i> 我的主页</a></li>
                            <li id="menu-item-2032"
                                class="dropdown-item menu-item-type-custom menu-item-object-custom"><a
                                    href="/store/my-account" one-link-mark="yes"><i
                                        class="fa fa-cart-arrow-down" data-icon="fas fa-user-alt"></i> 我的应用</a></li>
                            <li id="menu-item-1376"
                                class="dropdown-item menu-item-type-post_type menu-item-object-page"><a
                                    href="https://wp-china-yes.com/account" one-link-mark="yes"><i
                                        class="fas fa-cog" data-icon="fas fa-user-alt"></i> 账户设置</a></li>
                            <li id="menu-item-2031"
                                class="dropdown-item menu-item-type-custom menu-item-object-custom"><a
                                    href="https://wp-china-yes.com/translate/settings" one-link-mark="yes"><i
                                        class="fa fa-globe" data-icon="fas fa-user-alt"></i> 翻译偏好</a></li>
                            <li id="menu-item-2049"
                                class="dropdown-item menu-item-type-post_type menu-item-object-page"><a
                                    href="https://wp-china-yes.com/logout" one-link-mark="yes"><i
                                        class="fas fa-sign-out-alt" aria-hidden="true"></i> 注销</a></li>
                        </ul>

                    </li>
                </ul>


            </div>
        </div>
    </div>
    <?php get_template_part( 'template-parts/header/site-branding' ); ?>
    <?php get_template_part( 'template-parts/header/site-nav' ); ?>
</header>