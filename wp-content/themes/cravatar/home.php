<?php

use function LitePress\Cravatar\Inc\get_last_day_cdn_analysis;

get_header();

$is_login = is_user_logged_in();
?>
    <main class="flex-fill">
<!--        <section class="index-banner text-white">
            <div class="container-xxl ">

                <h1 class="mb-3 fw-semibold">自由、开放的互联网公共头像服务</h1>
                <div class="row justify-content-center justify-content-lg-between text-center text-lg-left   ">
                    <div class="col-12 col-md-10 col-lg-6 center index-banner-left"><h1
                                class="mb-20 mb-22-md mb-30-xxl 1">Cravatar - 互联网公共头像服务</h1>
                        <p class="subtitle mb-26-xs-md mb-30-md mb-40-xxl w-100 w-75-xxl text-center text-lg-left mx-auto mx-lg-0">

                            Cravatar 是 Gravatar 在中国的完美替代方案,<br>从此你可以自由的上传和分享头像。
                        </p>

                        <div class="d-flex justify-content-center justify-content-lg-start"><a
                                    class="btn btn-lg btn-light mt-4" data-offset="30"
                                    id="hgr-homepage-header-cta-get_started"
                                    href="<?php /*echo $is_login ? '/emails' : '/login' */?>">现在开始</a>

                        </div>
                        <small class="mt-4 g-color-auxiliary">我们昨日共响应了 <span data-countup='{"startVal": 0}'
                                                                             data-to="<?php /*echo get_last_day_cdn_analysis()['req_num'] ?? 0 */?>"
                                                                             id="counter" class="badge bg-primary lh-base">5,000,000</span>
                            次请求。</small>


                    </div>
                    <div class="col-lg-6 col-12  d-flex justify-content-center align-items-center ">
                        <img src="<?php /*echo CA_ROOT_URL; */?>/assets/img/background-header-image-101b1a9e9b.png">
                    </div>
                </div>
            </div>
        </section>-->


        <section class="bd-masthead mb-3" id="content">
            <div class="container-xxl bd-gutter">
                <div class="col-md-8 mx-auto text-center">
                    <a class="d-flex flex-column flex-lg-row justify-content-center align-items-center mb-4 text-dark lh-sm text-decoration-none" href="https://blog.getbootstrap.com/2022/05/13/bootstrap-5-2-0-beta/">
                        <strong class="d-sm-inline-block p-2 me-2 mb-2 mb-lg-0 rounded-3 masthead-notice">UI 风格升级 2.0</strong>
                        <span class="text-muted">前端底层改用 React-Bootstrap 重构</span>
                    </a>
                    <img src="/docs/5.2/assets/brand/bootstrap-logo-shadow.png" width="200" height="165" alt="Bootstrap" class="d-block mx-auto mb-3">

                    <h1 class="mb-3 fw-semibold">自由、开放的互联网公共头像服务</h1>
                    <p class="lead mb-4">
                        Cravatar —— China Recognized Avatar</p>
                    <div class="d-flex flex-column flex-lg-row align-items-md-stretch justify-content-md-center gap-3 mb-4">

                        <a href="/docs/5.2/getting-started/introduction/" class="btn btn-lg bd-btn-lg btn-primary d-flex align-items-center justify-content-center fw-semibold" onclick="ga('send', 'event', 'Jumbotron actions', 'Get started', 'Get started');">
                            <i class="fa-duotone fa-computer-mouse-scrollwheel me-2"></i>
                            开始使用
                        </a>
                    </div>
<!--                    <p class="text-muted mb-0">
                        目前 <strong>v2.0</strong>
                        <span class="px-1">·</span>
                        <a href="/docs/5.2/getting-started/download/" class="link-secondary">Download</a>
                        <span class="px-1">·</span>
                        <a href="https://getbootstrap.com/docs/4.6/getting-started/introduction/" class="link-secondary text-nowrap">v4.6.x docs</a>
                        <span class="px-1">·</span>
                        <a href="/docs/versions/" class="link-secondary text-nowrap">All releases</a>
                    </p>-->

                </div>
            </div>
        </section>

        <section class="section-spaces">

            <div class="container container-2020">
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
                                    <p class="" style="font-size: 14px"><i class="fad fa-arrow-alt-up"></i> 我 要 上 榜</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-spaces">
            <div class="container">
                <h2 class="text-center">赞助商</h2>
                <p class="mb-5 text-center text-muted">服务的运行需要大量的成本投入，还好有一些机构在支持我们</p>
                <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center mb-5">
                    <div class="col">
                        <div class="card">
                            <a href="https://www.wjdun.cn" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/uploads/2022/01/2022012506083569.jpg" alt="稳坚盾"
                                     itemprop="thumbnail" title="稳坚盾">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://console.upyun.com/register/?invite=SyMTvwEi_" target="_blank"
                               rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/uploads/2020/08/又拍云_logo5-300x153.png" alt="又拍云"
                                     itemprop="thumbnail" title="又拍云">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://www.weixiaoduo.com/?ref=sunxiyuan" target="_blank" rel="noopener"
                               data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="/wp-content/uploads/2020/08/weixiaoduo-logo-2020-300x134.png" alt="薇晓朵"
                                     itemprop="thumbnail" title="薇晓朵">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://www.yfxw.cn/" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     src="https://www.yfxw.cn/wp-content/uploads/2021/02/1613564243-bf130567ccd7e68.png"
                                     alt="源分享" itemprop="thumbnail" title="源分享">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://www.vpsor.cn?userCode=rh13788" target="_blank" rel="noopener"
                               data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/logo.png"
                                     alt="硅云" itemprop="thumbnail" title="硅云">
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <a href="https://www.appnode.com/?jzgkdu" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/logo-s.gif"
                                     alt="AppNode" itemprop="thumbnail" title="AppNode">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>



    <body>

<?php
get_footer();