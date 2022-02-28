<!-- Modal -->
<div class="modal fade" id="sign-in" tabindex="-1"  data-bs-backdrop="static" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <!--<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>-->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
<style>
    .form-sign img {
        width: 60%;
        margin: auto;
        display: block;
    }
</style>
            <div class="modal-body pt-0">
                <main class="form-sign p-3">
                    <img class="mb-4" alt="" width="100%" src="https://dev.litepress.cn/wp-content/uploads/2021/05/logo.svg">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mb-3 border-0" id="sign-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sign-in-tab" data-bs-toggle="tab" data-bs-target="#form-sign-in" type="button" role="tab" aria-controls="home" aria-selected="true">登录</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sign-up-tab" data-bs-toggle="tab" data-bs-target="#form-sign-up" type="button" role="tab" aria-controls="profile" aria-selected="false">注册</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <form class="tab-pane active" id="form-sign-in" role="tabpanel">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                                <label for="floatingInput">用户名/邮箱</label>
                                <div class="invalid-feedback">
                                    请输入帐号
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                                <label for="floatingPassword">密码</label>
                                <div class="invalid-feedback">
                                    请输入登录密码
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="checkbox col">
                                    <input class="form-check-input" type="checkbox" name="rememberme" id="remember-me" value="1">
                                    <label class="form-check-label" for="remember-me">保持登录状态</label>
                                </div>
                                <div class=" col text-end">
                                    忘记密码？
                                </div>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary" type="submit">登录</button>
                        </form>
                        <form class="tab-pane" id="form-sign-up" role="tabpanel"> <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                                <label for="floatingInput">用户名/邮箱</label>
                                <div class="invalid-feedback">
                                    请输入帐号
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                                <label for="floatingPassword">密码</label>
                                <div class="invalid-feedback">
                                    请输入登录密码
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                                <label for="floatingPassword">确认密码</label>
                                <div class="invalid-feedback">
                                    请输入密码
                                </div>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary mb-3" type="submit">注册</button>
                            <p class="text-center">

                                <small>注册即表示同意 用户协议、 隐私协议</small>

                            </p>
                        </form>

                    </div>

                </main>
            </div>

        </div>
    </div>
</div>

<div class="position-fixed bottom-05 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <img src="https://litepress.cn/wp-content/uploads/2021/05/%E8%B5%84%E6%BA%90-5-150x150.png" style="
    width: 20px;
    margin-right: 5px;
">
            <strong class="me-auto">LitePress 通知</strong>
            <small></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">

        </div>
    </div>
</div>

<footer id="site-footer" role="contentinfo" class="header-footer-group wp-footer p-2  ">
    <div class="container">
        <div class="row pt-5 pb-3 ">
            <div class="col">
                <div class="row gy-5">
                    <div class="col-6 col-md-3">
                        <h5 class="text-uppercase text-white opacity-85 mb-3">项目</h5>
                        <ul class="list-unstyled">
                            <li class="mb-1"><a class="link-600" href="http://cravatar.cn">Cravatar</a></li>
                            <li class="mb-1"><a class="link-600" href="/download">WP-China-Yes</a></li>
                            <li class="mb-1"><a class="link-600" href="#!">LP Translate</a></li>

                        </ul>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-uppercase text-white opacity-85 mb-3">新闻</h5>
                        <ul class="list-unstyled">
                            <li class="mb-1"><a class="link-600" href="/news/archives/category/news">动态</a></li>
                            <li class="mb-1"><a class="link-600" href="/news/archives/category/release">版本发布</a></li>
                            <li class="mb-1"><a class="link-600" href="/news/archives/category/maintenance">维护通知</a></li>

                        </ul>
                    </div>
                    <div class="col">
                        <h5 class="text-uppercase text-white opacity-85 mb-3">贡献</h5>
                        <ul class="list-unstyled">
                            <li class="mb-1"><a class="link-600" href="/about/timeline">参与开发</a></li>
                            <li class="mb-1"><a class="link-600" href="/translate/">参与翻译</a></li>
                            <li class="mb-1"><a class="link-600" href="/about/council">捐赠</a></li>

                        </ul>
                    </div>
                    <div class="col">
                        <h5 class="text-uppercase text-white opacity-85 mb-3">关于</h5>
                        <ul class="list-unstyled">
                            <li class="mb-1"><a class="link-600" href="/about/timeline">发展历程</a></li>
                            <li class="mb-1"><a class="link-600" href="/about/open-source-license/">开源许可</a></li>
                            <li class="mb-1"><a class="link-600" href="/about/council">社区理事会</a></li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-5 mt-xl-0">
                <h5 class="text-uppercase text-white opacity-85 mb-3">与我们交流</h5>
<p>
    需要注意的是 QQ 群中不受理非项目相关的技术支持类请求
</p>
                <div class="mt-3">
                    <a href="/create" class="btn btn-primary-soft  me-1">
                        论坛发帖 <i class="fad fa-language  ms-3"></i>
                    </a>
                    <a href="https://jq.qq.com/?_wv=1027&k=AizcubYC" class="btn btn-primary-soft  tooltip-show" data-bs-original-title="" title="">
                        加 QQ 群 <i class="fad fa-user-friends ms-3" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <div class="wp-rich-text text-center">
            <p>
                LitePress 是中国本土的 WordPress 衍生版，我们计划在未来几年内接管 WordPress 在中国的所有用户群并以适合国内的方式促进生态整体的发展。
            </p>
            <p>
                © 2020-2022 <a href="https://litepress.cn">LitePress.cn</a> 版权所有
                <a href="http://beian.miit.gov.cn" class="imprint" title="工信部备案" rel="nofollow" target="_blank">
                    鲁ICP备2021028118号-2
                </a>
            </p>
        </div>
    </div>

</footer><!-- #site-footer -->
<?php wp_footer(); ?>
</body>
</html>
