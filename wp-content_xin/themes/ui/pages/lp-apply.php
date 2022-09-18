<?php
/**
 * Template name: 更新LitePress
 * Description: 这是申请更新LitePress的模板
 */

get_header();
?>
<header class="search-banner banner">
    <div class="container">
        <div class="row align-items-center project-row ">

            <h5 class="text-center"><span>即刻申请升级 LitePress</span></h5>
            <p>一个崭新的独属于中国大陆的 WordPress 生态体系等待你的探索为了方便体验
            </p>



        </div>
    </div>
</header>


<main class="wp-body pb-5">
    <div class="container">
        <div class="row">
            <article class="col ">
                <section class="theme-boxshadow wp-banner bg-white p-4">


                    <div class="row">
                    <div class="col-xl-8">
                        <h6 class="mb-3">
                            推荐服务器配置
                        </h6>
                    <div class="row fs--1 fw-semi-bold text-500  gy-3">
                        <div class="col-xl-4 d-flex align-items-center pe-3"><span class="dot bg-primary"></span><span>php 7.4 及以上</span></div>
                        <div class="col-xl-4 d-flex align-items-center pe-3"><span class="dot bg-primary"></span><span>MySQL 5.6 及以上</span></div>
                        <div class="col-12 d-flex align-items-center pe-3"><span class="dot  bg-primary"></span><span>Nginx / Apache</span></div>

                    </div>
                    </div>
                    <div class="col-xl-4 mt-3 mt-xl-0">
                        <h6 class="mb-3">
                            版本信息
                        </h6>
                        <p class="deviation text-muted">发行日志<i></i>版本号：v 5.8.3</p>
                    </div>
                    </div>
                    <hr>
                    <ul class="nav lp-nav-tabs nav-tabs my-3">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#update">申请升级 Litepress</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#Back">回退 Wordpress</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane  active" id="update">
                            <blockquote class="mb-3">
                                <ol>
                                    <li>您可提交此表单来 <b class="text-primary">升级到 LitePress </b> 并接受后续版本更新，在提交前，请确认您的站点 安装 <b class="text-primary">有 WP-China-Yes 插件并切换到 本土市场</b> ，否则无法接收到更新 。
                                    </li>
                                    <li>提交站点需要您完成站点<b class="text-primary"> 所有权验证 </b>，请按页面提示进行操作，如有问题请<a href="/create" target="_blank">发帖咨询</a>。</li>
                                    <li>LitePress 与 WordPress <b class="text-primary">完全兼容 </b>，在使用 LitePress 的过程中，您可以随时到这里<b class="text-primary"> 回退 Wordpress</b>。
                                    </li>
                                </ol>
                            </blockquote>
                            <!--申请表单-->
                            <form class="needs-validation"  novalidate id="lp-apply-form" onsubmit="return false">
                                <div class="form-floating mb-3">
                                    <input type="url" name="site" autocomplete="off" class="form-control" id="lp-apply-site"
                                           placeholder="站点地址" pattern="^[^/](.*[^/])?$" required>
                                    <label for="">站点地址</label>
                                    <div class="invalid-feedback">
                                        不能为空或者结尾不能添加'/'
                                    </div>
                                    <div class="form-text">例如：https://litepress.cn 带协议头，结尾不添加'/'</div>
                                </div>
                                <button type="button"  id="lp-apply-button" class="submit btn btn-primary ms-auto"><i class="fad fa-paper-plane"></i>升级申请</button>
                            </form>

                        </div>
                        <div class="tab-pane fade" id="Back">
                            <blockquote class="mb-3">
                                <ol>
                                    <li>您可提交此表单来 <b class="text-primary">退出</b> LitePress 版本。
                                    </li>
                                    <li>提交站点需要您完成站点<b class="text-primary"> 所有权验证 </b>，请按页面提示进行操作，如有问题请<a href="/create" <?php if ( ! is_user_logged_in()) {echo ' data-bs-toggle="modal" data-bs-target="#sign-in" '; } ?> target="_blank">发帖咨询</a>。</li>
                                    <li>LitePress 与 WordPress <b class="text-primary">完全兼容 </b>，在使用 LitePress 的过程中，您可以随时到这里<b class="text-primary"> 回退 Wordpress</b>。
                                    </li>
                                </ol>
                            </blockquote>
                            <form class="needs-validation"  novalidate id="lp-exit-form" onsubmit="return false">
                                <div class="form-floating mb-3">
                                    <input type="url" name="site" autocomplete="off" class="form-control" id="lp-exit-site"
                                           placeholder="站点地址" pattern="^[^/](.*[^/])?$" required>
                                    <label for="">站点地址</label>
                                    <div class="invalid-feedback">
                                        不能为空或者结尾不能添加'/'
                                    </div>
                                    <div class="form-text">例如：https://litepress.cn 带协议头，结尾不添加'/'</div>
                                </div>
                                <button type="button"  id="lp-exit-button" class="submit btn btn-primary ms-auto"><i class="fad fa-paper-plane"></i> 回退申请</button>
                            </form>

                        </div>
                    </div>
                </section>

                <!-- 模态 -->
                <div class="modal" id="lp-apply-Modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <!-- 模态标题 -->
                            <div class="modal-header">
                                <h6 class="modal-title">站点所有权验证</h6>
                                <button type="button" class="btn-close"
                                        data-bs-dismiss="modal"></button>
                            </div>

                            <!-- 模态主体 -->
                            <div class="modal-body">
                                请在 <code class="lp-apply-site"></code> 根目录下建立<code class="lp-apply-file">lp-check.txt</code>文件，内容为<code class="lp-apply-code"></code>

                                配置完成后可点此测试访问是否正常，访问正常即可点击下面开始验证按钮提交后端验证。
                            </div>

                            <!-- 模态页脚 -->
                            <div class="modal-footer">

                                <button type="button" class="btn btn-primary verify-btn">
                                    <span class="spinner-border spinner-border-sm hide me-2" role="status" aria-hidden="true"></span><a>开始验证</a>
                                </button>
                                <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">取消
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </article>
            <aside class="wp-aside col-xl-3 mt-3 mt-xl-0">
                <section class="theme-boxshadow bg-white wp-accordion">
                    <header class="  d-flex aside-header align-items-center">
                        <div class="me-2 wp-icon">
                            <i class="fas fa-clipboard-list-check fa-fw" style=""></i></div>
                        <span>常见问题</span></header>

                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        <ol>
                            <li class="aside-accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapseOne" aria-expanded="false"
                                            aria-controls="flush-collapseOne">
                                        LitePress 有何特色？
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample"
                                     style="">
                                    <div class="accordion-body">LitePress 当前版本整合了 Cravatar
                                        头像，翻译平台翻译推送等本土特色功能，将在不久后的将来整合新的本土应用市场。
                                    </div>
                                </div>
                            </li>
                            <li class="aside-accordion-item">
                                <h2 class="accordion-header" id="flush-headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapseTwo" aria-expanded="false"
                                            aria-controls="flush-collapseTwo">
                                        LitePress 兼容性怎么样？
                                    </button>
                                </h2>
                                <div id="flush-collapseTwo" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample"
                                     style="">
                                    <div class="accordion-body">LitePress 理论完全兼容 WordPress 的插件/主题，如有兼容性问题，请发帖求助。</div>
                                </div>

                            </li>
                            <li class="aside-accordion-item">
                                <h2 class="accordion-header" id="flush-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapseThree" aria-expanded="false"
                                            aria-controls="flush-collapseTwo">
                                        在此提交站点与安装 Beta 插件有何区别？
                                    </button>
                                </h2>
                                <div id="flush-collapseThree" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample"
                                     style="">
                                    <ul class="accordion-body">
                                        <li>此处提交站点将可接收LitePress稳定版推送，而安装Beta插件将接收LitePress测试版推送。</li>
                                    </ul>
                                </div>

                            </li>
                            <li class="aside-accordion-item">
                                <h2 class="accordion-header" id="flush-heading4">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapse4" aria-expanded="false"
                                            aria-controls="flush-collapseTwo">
                                        为何提交后仍未收到 LitePress 推送？
                                    </button>
                                </h2>
                                <div id="flush-collapse4" class="accordion-collapse collapse"
                                     aria-labelledby="flush-heading4" data-bs-parent="#accordionFlushExample" style="">
                                    <div class="accordion-body">提交站点后请确保 WP-China-Yes 插件设置市场为本土应用市场，然后前往站点后台 -> 仪表盘 ->
                                        更新，点击重新安装按钮覆盖安装即可。
                                    </div>
                                </div>

                            </li>
                            <li class="aside-accordion-item">
                                <h2 class="accordion-header" id="flush-heading5">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapse5" aria-expanded="false"
                                            aria-controls="flush-collapseTwo">
                                        如何换回 WordPress ？
                                    </button>
                                </h2>
                                <div id="flush-collapse5" class="accordion-collapse collapse"
                                     aria-labelledby="flush-heading5" data-bs-parent="#accordionFlushExample" style="">
                                    <div class="accordion-body">退出 LitePress 后请前往站点后台 -> 仪表盘 -> 更新，点击重新安装按钮覆盖安装即可。
                                        您很可能需要在安装完成后重新启用 WP-China-Yes 插件，以优化 WordPress 在国内的体验。
                                    </div>
                                </div>

                            </li>
                        </ol>
                    </div>
                </section>
                <section class="my-3">
                    <a href="/create" target="_blank" class="btn btn-primary d-block" role="button">
                        <i class="fad fa-paper-plane"></i>
                        <span class="uabb-button-text uabb-creative-button-text">发帖咨询</span>
                    </a>
                </section>
            </aside>
        </div>
    </div>
</main>
<script src="https://cdn.staticfile.org/blueimp-md5/2.19.0/js/md5.min.js">
</script>
<?php get_footer(); ?>
