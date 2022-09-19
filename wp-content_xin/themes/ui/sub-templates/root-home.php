<?php
/**
 * Template name: 首页模板
 * Description: 该模板是首页模板
 */

// 设置页面 SEO 信息
add_filter( 'lpcn_seo_keywords', function () {
	return 'litepress,wp-china-yes,wordpress 中国,wordpress 中文';
} );
add_filter( 'lpcn_seo_description', function () {
	return '该项目旨在解决 WordPress 在中国的一系列特色问题，期望交付一个挣脱枷锁，一身轻的本土 WordPress 发行版。';
} );

get_header();
?>
    <style>
        .deviation i {
            width: 1px;
            height: 10px;
            background: #000;
            opacity: 0.8;
            margin: 0 14px;
            display: inline-block;
        }


        .wp-img-ground .card {
            display: flex;
            padding: 10px;
            justify-content: center;
            padding-top: 33%;
            position: relative;
            margin-bottom: 10px;
        }

        .wp-img-ground .card a {
            display: flex;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            justify-content: center;
            align-items: center;
        }

        .wp-img-ground .card a img {
            object-fit: cover;
            margin: auto;
            width: 75%;
            max-height: 100%;
        }

        .object-fit-img {
            object-fit: cover;
            height: 100%;
        }
        .derivative img,.derivative svg{
            max-height: 180px;
        }
        .bg-gradient-light-white {
            background-image: linear-gradient(180deg, #f9fbfd 0, #fff);
        }

        .bg-gray-200 {
            background-color: #f1f4f8 !important;
        }

        .icon {
            font-size: 30px;
        }

        .text-gray-700 {
            color: #506690 !important;
        }

        .fs-lg {
            font-size: 1.1875rem !important;
        }

        .derivative .card-text {
            color: rgba(0, 0, 0, .65);
            font-size: .875em;
        }

        .sponsor a {
            margin-bottom: 1rem;
        }
    </style>
    <main>
        <section class="py-8 border-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 col-md-5 col-lg-6 order-md-2">

                        <!-- Image -->
                        <img style="box-shadow: 0 1px 8px rgb(0 0 0 / 20%); "
                             src="/wp-content/uploads/2022/01/2022011709101020.png"
                             class="img-fluid  mb-5 mb-sm-0" alt="..."
                        >

                    </div>
                    <div class="col-12 col-md-7 col-lg-6 order-md-1 ">

                        <!-- Heading -->
                        <h1 class="h2 text-center text-md-start">
                            <span class="text-primary">LitePress</span> - 本土的 WordPress 衍生版
                        </h1>

                        <!-- Text -->
                        <p class="lead text-center text-md-start text-muted my-7 mb-lg-8">
                            该项目旨在解决 WordPress 在中国的一系列特色问题<br>期望交付一个挣脱枷锁，一身轻的本土 WordPress 发行版
                        </p>

                        <!-- Buttons -->
                        <div class="text-center text-md-start mt-3 ">
                            <a href="https://a1.wp-china-yes.net/apps/wp-china-yes.zip"
                               class="btn btn-primary me-3 pe-4">
                                <section class="d-flex align-items-center">
                                                                        <span class="fa-stack me-2">  <i
                                                                                    class="far fa-circle fa-stack-2x"></i><i
                                                                                    class="fas fa-long-arrow-down fa-stack-1x"></i></span>
                                    <article class="text-start"><p>获取 WP-China-Yes 插件</p>
                                    </article>
                                </section>
                            </a>
                            <p class="deviation mt-3 text-muted">发行版正在开发中，请先使用插件形式接入本土生态。</p>
                            <!--<p class="deviation mt-3 text-muted">发行日志<i></i>版本号：v 5.8.3</p>-->
                        </div>
                        <!--  <p class="lead text-center text-md-start text-muted my-7 mb-lg-8">当前正在进行发行版重构工作，敬请期待。</p>-->

					  </div>
				  </div> <!-- / .row -->
            </div> <!-- / .container -->
        </section>

        <section class="py-8  border-bottom bg-gradient-light-white">
            <div class="container">
                <h2 class="text-center">为什么使用 LitePress</h2>
                <p class="text-center text-muted mb-5">
                    LitePress 发行的目的是在本土生态层面上改良 WordPress，而非从头构建一个新的 CMS</p>
                <div class="row gy-5">
                    <div class="col-12 col-md-6 ">

                        <!-- Icon -->
                        <div class="icon text-primary mb-3">
                            <i class="fad fa-exchange"></i>
                        </div>

                        <!-- Heading -->
                        <h3>
                            良好的兼容性
                        </h3>

                        <!-- Text -->
                        <p class="text-muted mb-3 mb-md-0">
                            我们严格确保不引入任何破坏性更改，使你可以平滑的在 WordPress 和 LitePress 之间切换，而无需更换正在使用的插件和主题
                        </p>

                    </div>
                    <div class="col-12 col-md-6 ">

                        <!-- Icon -->
                        <div class="icon text-primary mb-3">
                            <i class="fad fa-wifi-2"></i>
                        </div>

                        <!-- Heading -->
                        <h3>
                            无网络卡顿问题
                        </h3>

                        <!-- Text -->
                        <p class="text-muted mb-3 mb-md-0">
                            LitePress 在本地自建 WordPress.org 的所有基础服务，提供稳定快速的对外访问，使你站点不会因为外部 HTTP 请求超时而产生卡顿、报错的情况，同时
                            LitePress 也会智能优化第三方插件主题的缓慢 HTTP 服务的路由线路。
                        </p>

                    </div>
                    <div class="col-12 col-md-6">

                        <!-- Icon -->
                        <div class="icon text-primary mb-3">
                            <i class="fad fa-language"></i>
                        </div>

                        <!-- Heading -->
                        <h3>
                            更好的中文支持
                        </h3>

                        <!-- Text -->
                        <p class="text-muted mb-0">
                            我们构建了本地化的翻译平台，并引入一系列特性帮助加快 WordPress
                            及其生态资源的汉化工作，同时你可以在我们的支持论坛得到志愿者的帮助
                        </p>
                        <div class="text-center text-md-start mt-3">
                            <a href="/translate/" class="btn btn-primary  me-1">
                                翻译平台 <i class="fad fa-language  ms-3"></i>
                            </a>
                            <a href="/forums" class="btn btn-primary-soft  tooltip-show"
                               data-bs-original-title="" title="">
                                支持论坛 <i class="fad fa-user-friends ms-3" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">

                        <!-- Icon -->
                        <div class="icon text-primary mb-3">
                            <i class="fad fa-store"></i>
                        </div>

                        <!-- Heading -->
                        <h3>
                            焕然一新的应用市场（构建中）
                        </h3>

                        <!-- Text -->
                        <p class="text-muted mb-0">
                            LitePress 的应用市场同时对付费和免费的插件、主题提供支持，提供与 Steam
                            一样的一站式安装、更新、鉴权体验，确保你不需要为了寻找一款心仪的主题而四处奔波，并在多个开发商的不同平台间切换账号以查询和更新产品授权。
                        </p>

                    </div>
                </div> <!-- / .row -->


            </div> <!-- / .container -->
        </section>

        <section class="py-8 border-bottom bg-gradient-light-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-10 col-lg-8 text-center">


                        <h2 class="text-center">衍生项目</h2>
                        <p class="text-center text-muted mb-5">
                            我们的面临的是一个复杂的体系化工程，除 LitePress.cn 平台外，我们还构建了一揽子的衍生项目来支持我们实现目的</p>

                    </div>
                </div> <!-- / .row -->
                <div class="row mb-6 row-cols-2 row-cols-lg-4 gy-3 derivative">
                    <div class="col">

                        <a href="https://cravatar.cn/" target="_blank">
                            <div class="card">
                                <img src="/wp-content/uploads/2022/01/2022011712410526.png" class="object-fit-img">

                                <div class="card-body text-center">
                                    <h5 class="card-title ">Cravatar 头像服务</h5>
                                    <p class="card-text">Cravatar 是 Gravatar 在中国大陆的完美替代方案，支持从 MD5 解析并返回 QQ 头像。</p>
                                </div>
                            </div>
                        </a>


                    </div>
                    <div class="col">
                        <div class="card">
                            <svg class="bd-placeholder-img card-img-top" width="100%" height="180"
                                 xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder"
                                 preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title>
                                <rect width="100%" height="100%" fill="#868e96"></rect>
                            </svg>

                            <div class="card-body text-center">
                                <h5 class="card-title ">文风字体服务</h5>
                                <p class="card-text">国外的 WordPress 主题普遍引用谷歌等一系列在中国大陆无法访问的字体资源，此服务意在接替源站提供资源访问。</p>
                            </div>
                        </div>


                    </div>
                    <div class="col">
                        <div class="card">
                            <img src="/wp-content/uploads/hm_bbpui/21819/94xamdz2k131favxztdbqwpbodq2t39i.png"
                                 class="object-fit-img">

                            <div class="card-body text-center">
                                <h5 class="card-title ">LP Translate</h5>
                                <p class="card-text">基于 Loco Translate 开发的翻译插件，对接 LitePress.cn 云平台，提供免费且无限制的机器翻译支持。</p>
                            </div>
                        </div>


                    </div>
                    <div class="col">
                        <div class="card">
                            <img src="/wp-content/uploads/2022/01/2022011714401580.png" class="object-fit-img">

                            <div class="card-body text-center">
                                <h5 class="card-title ">WP-China-Yes</h5>
                                <p class="card-text">对于不想切换 LitePress 的用户，可以使用此插件为自己的 WordPress 对接本土生态体系。</p>
                            </div>
                        </div>


                    </div>
                </div>

            </div> <!-- / .row -->
            </div> <!-- / .container -->
        </section>
        <section class="py-8 border-bottom bg-gradient-light-white">
            <div class="container">
                <div class="row mb-7">
                    <h2 class="text-center">基本准则</h2>
                    <p class="text-center text-muted mb-5">
                        从项目蓝图诞生起，创始团队便期望将此项目发展为国内WordPress生态的基建与公共事业，并为此而确立了四个“基本准则"</p>
                    <ul class="nav nav-pills mb-5 justify-content-around" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                    aria-selected="true">开源
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-profile" type="button" role="tab"
                                    aria-controls="pills-profile" aria-selected="false">开放
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-contact" type="button" role="tab"
                                    aria-controls="pills-contact" aria-selected="false">中立
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-contact" type="button" role="tab"
                                    aria-controls="pills-contact" aria-selected="false">公益
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active card theme-boxshadow px-5 py-4" id="pills-home"
                             role="tabpanel"
                             aria-labelledby="pills-home-tab">
                            <div>
                                <p>
                                    项目核心开发团队及志愿者所产出的所有代码、数据（隐私数据除外）均严格遵守GPL协议开源。我们确保任何人都可以在有想法的时候完整复刻整个项目，以确保贡献者的劳动成果是持久存在的，不会在意外情况来临时因项目闭源而无法采取有效措施。
                                </p>
                                <footer class="text-center"><a href="https://github.com/litepress/"
                                                               class="btn btn-primary shadow lift mt-3">
                                        访问 GitHub <i class="fab fa-github  ms-3"></i>
                                    </a></footer>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel"
                             aria-labelledby="pills-profile-tab">
                            <div>
                                <p>
                                    项目核心开发团队及志愿者所产出的所有代码、数据（隐私数据除外）均严格遵守GPL协议开源。我们确保任何人都可以在有想法的时候完整复刻整个项目，以确保贡献者的劳动成果是持久存在的，不会在意外情况来临时因项目闭源而无法采取有效措施。
                                </p>
                                <footer class="text-center"><a href="https://github.com/litepress/"
                                                               class="btn btn-primary shadow lift mt-3">
                                        访问 GitHub <i class="fab fa-github  ms-3"></i>
                                    </a></footer>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-contact" role="tabpanel"
                             aria-labelledby="pills-contact-tab">
                            <div>
                                <p>
                                    项目核心开发团队及志愿者所产出的所有代码、数据（隐私数据除外）均严格遵守GPL协议开源。我们确保任何人都可以在有想法的时候完整复刻整个项目，以确保贡献者的劳动成果是持久存在的，不会在意外情况来临时因项目闭源而无法采取有效措施。
                                </p>
                                <footer class="text-center"><a href="https://github.com/litepress/"
                                                               class="btn btn-primary shadow lift mt-3">
                                        访问 GitHub <i class="fab fa-github  ms-3"></i>
                                    </a></footer>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- / .container -->
        </section>

        <section class="py-8 border-bottom bg-gradient-light-white">
            <div class="container">
                <div class="row mb-5">
                    <h2 class="text-center">与核心团队交流</h2>
                    <p class="text-center text-muted">
                        项目核心团队，包括一个三人的开发小组和社区理事会成员，你可以通过以下两种方式与我们取得联系</p>
                </div>

                <div class="row mb-6 row-cols-1 row-cols-lg-2 gy-3 m-auto" style="max-width: 960px">
                    <div class="col">


                        <div class="card py-3  theme-boxshadow">

                            <div class="card-body text-center">
                                <h5 class="card-title ">社区发帖（推荐）</h5>
                                <p class="card-text text-muted">
                                    对于系统化的提议，论坛是一个不错的交流渠道，其让双方可以长篇大论的具体叙述想法。同时我们确保回复每一篇帖子。</p>
                                <a class="btn btn-primary mt-3" href="/forums">前往论坛 <i class="fad fa-user-friends"
                                                                                       aria-hidden="true"></i></a>
                            </div>
                        </div>


                    </div>

                    <div class="col">


                        <div class="card py-3 theme-boxshadow">

                            <div class="card-body text-center">
                                <h5 class="card-title ">QQ 群</h5>
                                <p class="card-text text-muted">相较于论坛，QQ 群适合于对时效性要求高的交流，在这里你可以 @
                                    任意项目团队成员。但需要注意的是，这里不受理技术支持。</p>
                                <a class="btn btn-primary mt-3" href="https://jq.qq.com/?_wv=1027&k=AizcubYC">前往 QQ 群 <i
                                            class="fab fa-qq"></i></a>
                            </div>
                        </div>


                    </div>

                </div>


            </div> <!-- / .container -->
        </section>

        <section class="py-8 border-bottom bg-gray-200">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 col-md-5 col-lg-6 order-md-2">

                        <!-- Image -->
                        <img src="https://litepress.cn/wp-content/uploads/2022/01/2022011708014920.png" alt="..."
                             class="img-fluid mb-6 mb-md-0">

                    </div>
                    <div class="col-12 col-md-7 col-lg-6 order-md-1">

                        <!-- Heading -->
                        <h2>

                            <span class="text-primary">LitePress</span> 的未来
                        </h2>

                        <!-- Text -->
                        <p class="fs-lg text-gray-700 mb-6 pe-5">
                            在中国，传统的建站行业的生存空间会越来越小，各种本土 CMS 和论坛系统的消亡很好的印证了这一点，对于 LitePress 来讲，得益于 WordPress 在全球的生态积累，我们希望
                            LitePress 在未来可以逐渐演化成小微企业的业务快速启动平台。


                    </div>
                </div> <!-- / .row -->
            </div> <!-- / .container -->
        </section>
        <section class="py-8 ">
            <div class="container">
                <h2 class=" text-center">赞助者</h2>
                <p class="text-center text-muted mb-5">
                    自项目发起始，有太多人提供了资金和各种资源的支持，我们无法在此一—列出，故只罗列在项<br>目萌芽期给予重要帮助和迄今为止仍持续提供支持的一些个人和组织。</p>
                <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center mb-3">
                    <div class="col">
                        <div class="card">
                            <a href="https://www.wjdun.cn" target="_blank"
                               rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
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
                            <a href=" https://wpscale.cn/?refwps=3" target="_blank" rel="noopener" data-caption=""
                               itemprop="contentUrl" one-link-mark="yes">
                                <img class="uabb-gallery-img card-img"
                                     style="width: 95%;"
                                     src="https://litepress.cn/wp-content/uploads/2022/09/2022091512340525.jpg"
                                     alt="沃朴思WPScale" itemprop="thumbnail" title="沃朴思WPScale">
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
                <div class="btn-card-group justify-content-evenly sponsor">
                    <a href="https://litepress.cn/plugins/lp-plugins/litepress-beta" class="btn btn-primary"
                       one-link-mark="yes"><i class="fas fa-search"></i>完整名单</a>
                    <a href="https://litepress.cn/store/?woo-free-download=251325" class="btn btn-primary"
                       one-link-mark="yes" target="_blank"><i class="fad fa-envelope-open-dollar"></i>提供赞助</a>
                    <a href="https://litepress.cn/store/?woo-free-download=251325" class="btn btn-primary"
                       one-link-mark="yes" target="_blank"><i class="fad fa-money-check-edit-alt"></i>财务支出</a>
                    <a href="https://litepress.cn/store/?woo-free-download=251325" class="btn btn-primary"
                       one-link-mark="yes" target="_blank"><i class="fad fa-money-check-alt"></i>固定资产 </a>
                </div>
            </div>
        </section>

    </main>
<?php
get_footer();
