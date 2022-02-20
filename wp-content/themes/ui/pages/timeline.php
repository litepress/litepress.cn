<?php
/**
 * Template name: 时间线
 * Description: 该模板是关于我们-发展历程的时间线模板
 */
get_header();
?>
    <style>
        /*===============================
		  TIMELINE
	=================================*/
        .process-circle {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #fff;
            font-size: 1.125rem
        }

        .process-circle:empty:after {
            content: '';
            width: .5rem;
            height: .5rem;
            border-radius: 50%
        }

        .process-circle:empty.bg-primary {
            background: rgba(55, 85, 190, .05) !important
        }

        .process-circle:empty.bg-primary:after {
            background-color: #3755be;
            z-index: 999;
        }

        .process-circle:empty.bg-primary-2 {
            background: rgba(255, 142, 136, .05) !important
        }

        .process-circle:empty.bg-primary-2:after {
            background-color: #ff8e88
        }

        .process-circle:empty.bg-primary-3 {
            background: rgba(27, 31, 59, .05) !important
        }

        .process-circle:empty.bg-primary-3:after {
            background-color: #1b1f3b
        }

        .process-vertical {
            padding: 0;
            width: 100%;
            list-style: none;
            display: flex;
            flex-direction: column
        }

        .process-vertical li {
            display: flex;
            align-items: center
        }

        .process-vertical li .process-circle {
            margin-right: 1.5rem
        }

        .process-vertical li:not(:last-child) {
            position: relative;
            margin-bottom: 1.5rem
        }

        @media (min-width: 768px) {
            .process-vertical li {
                width: 50%;
                margin-left: 50%
            }

            .process-vertical li .process-circle {
                margin-left: -1.5rem
            }

            .process-vertical li:nth-child(even) {
                flex-direction: row-reverse;
                text-align: right;
                margin-left: 1px;
                margin-right: 50%
            }

            .process-vertical li:nth-child(even) .process-circle {
                margin-right: -1.5rem;
                margin-left: 1.5rem
            }

            .process-vertical li:not(:last-child) {
                padding-bottom: 4.5rem;
                margin-bottom: 0
            }

            .process-vertical li:not(:last-child):after {
                content: '';
                display: block;
                width: 1px;
                height: 100%;
                background: #dee2e6;
                position: absolute;
                top: 2.125rem;
            }

            .text-light .process-vertical li:not(:last-child):after {
                background: rgba(255, 255, 255, .25)
            }
        }
        .all-text-white *, .text-all-white * {
            color: #ffffff;
        }
        .py-7 {
            padding-top: 7rem !important;
            padding-bottom: 7rem !important;
        }
        .shape {
            pointer-events: none;
            position: absolute;
        }
        .shape-bottom {
            bottom: 0;
            left: 0;
            right: 0;
        }
        .shape:not([class*=shape-blur]) {
            overflow: hidden;
        }
        .shape:not([class*=shape-blur])>* {
            transform: scale(2);
        }
        .shape-fluid-x>* {
            height: auto;
            width: 100%;
        }
        .shape-bottom>* {
            transform-origin: top center;
        }
    </style>
    <div class="text-center bg-overlay-dark-7 py-7 " style="background:url(/wp-content/uploads/2022/02/2022020906361956.jpg) no-repeat; background-size:cover; background-position: center center;">
        <div class="container">
            <div class="row all-text-white">
                <div class="col-md-12 align-self-center">
<!--                    <h1 class="fw-bold">Timeline</h1>
                    <h6 class="mb-5">We transform your perception into an excellent website</h6>-->
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item active"><a href="/"><i class="ti-home"></i> 首页</a></li>
                            <li class="breadcrumb-item">发展历程</li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="position-relative">
        <div class="shape shape-bottom shape-fluid-x text-light">
            <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 48h2880V0h-720C1442.5 52 720 0 720 0H0v48z" fill="#f0f2f5"></path></svg>      </div>
    </div>

    <section class="bg-primary-alt o-hidden wp-body">
        <div class="container bg-white theme-boxshadow py-5">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="text-center">发展历程</h2>
                    <p class="text-center text-muted mb-5">
                        我们现阶段的目标在于接管 WordPress 在中国的所有用户群，当前所有工作都围绕这一目标而进行。<br>
                        路虽远，行则将至；事虽难，做则必成。
                    </p>
                </div>
            </div>
            <div class="row o-hidden o-lg-visible">
                <div class="col-11 m-auto d-flex flex-column align-items-center">
                    <ol class="process-vertical">
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2022 年 1 月 30 日</span>
                                <p class="mb-0">作为分支项目的 ArkPress 开发完成，该项目定位为无古腾堡的 WordPress 发行版，与 LitePress 一样对接本土生态体系。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2022 年 1 月 16 日</span>
                                <p class="mb-0"><a href="/search" target="_blank">平台搜索引擎</a> 第一版上线，提供跨越多个子站点的聚合式搜索体验，支持分词与全文索引。</p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 12 月 6 日</span>
                                <p class="mb-0">应用市场爬虫第二版重构完成，自此我们可以做到每 30 分钟与 wordpress.org 增量同步一次应用市场资源。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 10 月 20 日</span>
                                <p class="mb-0">LP Translate 第一版发布，此插件旨在允许用户直接在自己的网站后台编辑并向平台贡献翻译。插件完整接入 <a href="https://litepress.cn/translate/" target="_blank">LitePress 翻译平台</a> 的机器辅助翻译特性。</p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 10 月 12 日</span>
                                <p class="mb-0">文档平台第一版上线，与翻译平台深度整合，提供基于“段落”的文档翻译支持，同时支持直接在文档页面点击翻译某段文字。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 9 月 14 日</span>
                                <p class="mb-0">机器翻译引擎第二版上线，提供批量翻译、术语表支持，同时提供更稳定的调用体验。</p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 7 月 25 日</span>
                                <p class="mb-0"><a href="https://cravatar.cn" target="_blank">Cravatar 公共头像服务</a> 发布，旨在接替 Gravatar 为国内用户提供头像托管服务。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 6 月 13 日</span>
                                <p class="mb-0"><a href="/user/haozi" target="_blank">赵皓（耗子）</a>加入项目团队，负责 LitePress 发行版的开发和部分 LitePress.cn 平台后端的开发、维护工作。同时 LitePress 发布第一个测试版。</p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 5 月 7 日</span>
                                <p class="mb-0">重构后的 wp-china.org 上线，更改域名为 litepress.cn，同时项目发展着力点更改为在发展本土生态的同时推本土发行版——LitePress，寓意为挣脱枷锁，一身轻的 WordPress。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2021 年 1 月 1 日</span>
                                <p class="mb-0">WP-China-Yes 4.0.0 Beta1 开发完成，其中接入了处于雏形状态的本土应用市场（此版本仅小范围测试，未公开发布）。</p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2020 年 12 月 13 日</span>
                                <p class="mb-0"><a href="/user/linn" target="_blank">李钰林（Yulinn）</a>加入项目团队，负责前端开发与设计工作，wp-china.org 开始重构。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2020 年 8 月 15 日</span>
                                <p class="mb-0">wp-china.org 网站及翻译平台第一版上线；同时发布 WP-China-Yes 的 3.0.0 版，此版本对接本土翻译平台的自动化翻译能力，尝试为用户推送 WordPress 插件及主题的全量翻译包。</p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2020 年 6 月 23 日</span>
                                <p class="mb-0">项目得到薇晓朵网络工作室共计 50500 元赞助，这极大的分摊了平台开发成本，使整个计划变得可能。</p>
                            </div>
                        </li>
                        <li data-aos="fade-right" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2020 年 6 月 3 日</span>
                                <p class="mb-0">《<a href="https://www.ibadboy.net/archives/3864.html" target="_blank">WP 中国本土化社区</a>》 构想诞生，目的是整合国内 WordPress 生态现有资源，并构建允许闭源和付费应用的统一的应用市场，以及本地化的翻译平台，以此促进 WordPress 在中国的发展。
                                </p>
                            </div>
                        </li>
                        <li data-aos="fade-left" class="aos-init aos-animate">
                            <div class="process-circle bg-primary"></div>
                            <div>
                                <span class="text-small text-muted">2020 年 2 月 29 日</span>
                                <p class="mb-0">为应对 wordpress.org 返回429报错的问题，WP-China-Yes 插件发布，该项目最初作为 <a href="/user/ibadboy" target="_blank">孙锡源（绝世坏蛋）</a>的个人项目，旨在建立中国的 WordPress
                                    仓库镜像。</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

<?php
get_footer();