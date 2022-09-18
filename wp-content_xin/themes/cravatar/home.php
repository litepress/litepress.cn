<?php

use function LitePress\Cravatar\Inc\get_last_day_cdn_analysis;

get_header();

$is_login = is_user_logged_in();
?>
    <main class="flex-fill">

    <div class="d-lg-flex position-relative">
        <div class="container d-lg-flex align-items-lg-center content-space-t-3 content-space-lg-0 min-vh-lg-93">
            <!-- Heading -->
            <div class="w-100">
                <div class="row">
                    <div class="col-lg-12">

                        <a class="d-flex flex-column flex-lg-row justify-content-center  align-items-center mb-6 text-dark lh-sm text-decoration-none"
                           href="https://blog.getbootstrap.com/2022/05/13/bootstrap-5-2-0-beta/">
                            <strong class="d-sm-inline-block p-2 me-2 mb-2 mb-lg-0 rounded-3 masthead-notice">UI 风格升级
                                2.0</strong>
                            <span class="text-muted">前端底层改用 React-Bootstrap 重构</span>
                        </a>

                        <h1 class="mb-3 fw-semibold text-center">                  <span
                                    class="text-primary text-highlight-warning">
                    <span class="typed" data-typer-targets="自由, 开放"></span><span
                                        class="typed-cursor typed-cursor--blink" aria-hidden="true">|</span>
                  </span>的互联网公共头像服务</h1>

                        <p class="lead mb-6 text-center ">
                            Cravatar —— China Recognized Avatar</p>
                        <div class="d-flex flex-column flex-lg-row align-items-md-stretch justify-content-center  gap-3 mb-6">

                            <a href="<?php echo $is_login ? '/emails' : '/login' ?>"
                               class="btn btn-lg bd-btn-lg btn-primary d-flex align-items-center justify-content-center fw-semibold"
                               onclick="ga('send', 'event', 'Jumbotron actions', 'Get started', 'Get started');">
                                <i class="fa-duotone fa-computer-mouse-scrollwheel me-2"></i>
                                开始使用
                            </a>
                        </div>
                        <p class="mt-6   text-center">
                            <small>我们昨日共响应了 <span data-countup='{"startVal": 0}'
                                                  data-to="<?php echo get_last_day_cdn_analysis()['req_num'] ?? 0 ?>"
                                                  id="counter"
                                                  class="badge bg-primary lh-base">5,000,000</span>
                                次请求。</small>
                        </p>
                        <div class="m-mouse-icon">
                            <div class="m-wheel"></div>
                        </div>
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Row -->
            </div>
            <!-- End Title & Description -->

            <!-- SVG Shape -->
            <div class="col-lg-7 col-xl-6 d-none d-lg-block position-absolute top-0 end-0 pe-0"
                 style="margin-top: 6.75rem;    z-index: -1;">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1137.5 979.2">
                    <path fill="#F9FBFF" d="M565.5,957.4c81.1-7.4,155.5-49.3,202.4-115.7C840,739.8,857,570,510.7,348.3C-35.5-1.5-4.2,340.3,2.7,389
              c0.7,4.7,1.2,9.5,1.7,14.2l29.3,321c14,154.2,150.6,267.8,304.9,253.8L565.5,957.4z"></path>
                    <defs>
                        <path id="mainHeroSVG1"
                              d="M1137.5,0H450.4l-278,279.7C22.4,430.6,24.3,675,176.8,823.5l0,0C316.9,960,537.7,968.7,688.2,843.6l449.3-373.4V0z"></path>
                    </defs>
                    <clipPath id="mainHeroSVG2">
                        <use xlink:href="#mainHeroSVG1"></use>
                    </clipPath>
                    <g transform="matrix(1 0 0 1 0 0)" clip-path="url(#mainHeroSVG2)">

                    </g>
                </svg>
            </div>
            <!-- End SVG Shape -->
        </div>
    </div>


    <div class="container content-space-2 content-space-t-xl-3 content-space-b-lg-3">
        <!-- Heading -->
        <div class="w-md-75 w-lg-50 text-center mx-md-auto mb-5">
            <h2>主要优势</h2>
        </div>
        <!-- End Heading -->


        <div class="row mb-4">
            <div class="col-12 col-md-6 col-lg-4 mb-5 mb-lg-0 text-center">



                <div class="icon icon-shape icon-shape-primary rounded-circle"><i class="fa-duotone fa-users-gear fa-2x"></i></div>

                <h5 class="mt-4 mb-3">三级头像匹配</h5>
                <p class="px-0 px-sm-4 px-lg-0 h7">除自有头像源外，我们还整合了 QQ 和 Gravatar 头像，当用户请求头像时，我们会按以下顺序分三级返回准确的头像</p></div>
            <div class="col-12 col-md-6 col-lg-4 mb-5 mb-lg-0 text-center">
                <div class="icon icon-shape icon-shape-primary rounded-circle">                        <i class="fa-duotone fa-browser fa-2x"></i>
                </div>
                <h5 class="mt-4 mb-3">WEBP 支持</h5>
                <p class="px-0 px-sm-4 px-lg-0 h7">我们会为适合的客户端返回 WEBP 格式图片，这将提供夸张的高达 70% 的压缩率，确保可以更快的加载头像。</p></div>
            <div class="col-12 col-md-6 col-lg-4 mb-5 mb-lg-0 text-center">
                <div class="icon icon-shape icon-shape-primary rounded-circle"> <i class="fa-duotone fa-gauge-max  fa-2x"></i></div>
                <h5 class="mt-4 mb-3">更快的速度</h5>
                <p class="px-0 px-sm-4 px-lg-0 h7">相较于传统的反代模式，我们只会对每个第三方头像缓存一份原始文件，此后的每次不同尺寸的请求都在此基础上在本地作图，减少第三方回源次数。</p></div>
        </div>


        <!-- End Row -->
    </div>


    <section class="bg-light rounded-2 mx-3 mx-lg-10">
        <div class="container content-space-2">
            <h2 class="text-center">赞助商</h2>
            <p class="mb-5 text-center text-muted">服务的运行需要大量的成本投入，还好有一些机构在支持我们</p>
            <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center mb-5">
                <div class="col">
                    <div class="card">
                        <a href="https://www.wjdun.cn" target="_blank" rel="noopener" data-caption=""
                           itemprop="contentUrl">
                            <img class="uabb-gallery-img card-img"
                                 src="/wp-content/uploads/2022/01/2022012506083569.jpg" alt="稳坚盾"
                                 itemprop="thumbnail" title="稳坚盾">
                        </a>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <a href="https://console.upyun.com/register/?invite=SyMTvwEi_" target="_blank"
                           rel="noopener" data-caption="" itemprop="contentUrl">
                            <img class="uabb-gallery-img card-img"
                                 src="/wp-content/uploads/2020/08/又拍云_logo5-300x153.png" alt="又拍云"
                                 itemprop="thumbnail" title="又拍云">
                        </a>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <a href="https://www.weixiaoduo.com/?ref=sunxiyuan" target="_blank" rel="noopener"
                           data-caption="" itemprop="contentUrl">
                            <img class="uabb-gallery-img card-img"
                                 src="/wp-content/uploads/2020/08/weixiaoduo-logo-2020-300x134.png" alt="薇晓朵"
                                 itemprop="thumbnail" title="薇晓朵">
                        </a>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <a href="https://www.yfxw.cn/" target="_blank" rel="noopener" data-caption=""
                           itemprop="contentUrl">
                            <img class="uabb-gallery-img card-img"
                                 src="https://litepress.cn/wp-content/uploads/2022/09/2022091512340525.jpg"
                                 alt="沃朴思WPScale" itemprop="thumbnail" title="沃朴思WPScale">
                        </a>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <a href="https://www.vpsor.cn?userCode=rh13788" target="_blank" rel="noopener"
                           data-caption="" itemprop="contentUrl">
                            <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/logo.png"
                                 alt="硅云" itemprop="thumbnail" title="硅云">
                        </a>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <a href="https://www.appnode.com/?jzgkdu" target="_blank" rel="noopener" data-caption=""
                           itemprop="contentUrl">
                            <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/logo-s.gif"
                                 alt="AppNode" itemprop="thumbnail" title="AppNode">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-spaces">

        <div class="container content-space-2">
            <div id="trust-signals">
                <h2 class=" text-center">名家信任</h2>
                <p class="mb-5 text-center text-muted">一些你所熟悉的人正在使用我们的服务，不妨也试一下？</p>
                <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center">
                    <div class="col">
                        <div class="card">
                            <a href="https://www.wpdaxue.com/cravatar.html" target="_blank" rel="noopener"
                               data-caption="" itemprop="contentUrl">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/wpdaxue.png" alt="WordPress大学"
                                     itemprop="thumbnail" title="WordPress大学">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://zmingcx.com/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/zmingcx.png" alt="知更鸟"
                                     itemprop="thumbnail" title="知更鸟">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://wpcom.cn/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/wpcom.png" alt="WPCOM"
                                     itemprop="thumbnail" title="WPCOM">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://www.nicetheme.cn/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/nicetheme.png" alt="nicetheme"
                                     itemprop="thumbnail" title="nicetheme">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://iro.tw/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/iro.png" alt="iro"
                                     itemprop="thumbnail" title="iro">
                            </a>
                        </div>
                    </div>


                </div>
                <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center">
                    <div class="col">
                        <div class="card">
                            <a href="https://www.lovestu.com/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/lovestu.png" alt="lovestu"
                                     itemprop="thumbnail" title="lovestu">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://www.ilxtx.com/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/themes/cravatar/assets/img/ilxtx.png" alt="ilxtx"
                                     itemprop="thumbnail" title="龙笑天下">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="javascript:" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes" style="" data-bs-toggle="modal"
                               data-bs-target="#Cooperation">
                                <p class="m-0" style="font-size: 14px"><i class="fad fa-arrow-alt-up"></i> 我 要 上 榜</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <body>

<?php
get_footer();