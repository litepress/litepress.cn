<!-- Modal -->
<div class="modal fade" id="sign-in" tabindex="-1" data-bs-backdrop="static" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered ">
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
                <main class="form-sign p-3 pt-0">
                    <img class="mb-4" alt="" width="100%"
                         src="https://dev.litepress.cn/wp-content/uploads/2021/05/logo.svg">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mb-3 border-0" id="sign-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sign-in-tab" data-bs-toggle="tab"
                                    data-bs-target="#form-sign-in" type="button" role="tab" aria-controls="home"
                                    aria-selected="true">登录
                            </button>
                        </li>
                        <li class="nav-item hide" role="presentation">
                            <button class="nav-link" id="sign-up-tab" data-bs-toggle="tab"
                                    data-bs-target="#form-sign-up" type="button" role="tab" aria-controls="profile"
                                    aria-selected="false">注册
                            </button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">


                        <form class="needs-validation tab-pane active" id="form-sign-in" role="tabpanel">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" placeholder="name@example.com"
                                       required>
                                <label for="username">用户名/邮箱</label>
                                <div class="invalid-feedback">
                                    请输入帐号
                                </div>
                            </div>
                            <div class="form-floating mb-3 input-group">
                                <input type="password" class="form-control" id="password" placeholder="Password"
                                       required>
                                <label for="password">密码</label>
                                <a class="toggle-password input-group-text"">
                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                </a>
                                <div class="invalid-feedback">
                                    请输入登录密码
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="checkbox col">
                                    <input class="form-check-input" name="rememberme" type="checkbox" id="rememberme"
                                           value="0">
                                    <label class="form-check-label" for="rememberme">保持登录状态</label>
                                </div>
                                <div class=" col text-end">
                                    <a target="_blank" href="/password-reset">忘记密码？</a>
                                </div>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary" data-type="submit" type="button">
                                <span class="spinner-border spinner-border-sm hide me-2" role="status"
                                      aria-hidden="true"></span><a>登录</a>
                            </button>
                            <input type="hidden" name="tcaptcha-ticket" id="tcaptcha-ticket" value="">
                            <input type="hidden" name="tcaptcha-randstr" id="tcaptcha-randstr" value="">
                        </form>

                        <!--注册-->
                        <form class="needs-validation tab-pane" id="form-sign-up" role="tabpanel">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email"
                                       placeholder="name@example.com" required>
                                <label for="email">邮箱</label>
                                <div class="invalid-feedback">
                                    请输入邮箱
                                </div>
                            </div>
                            <div class="form-floating mb-3 input-group">
                                <input type="password" class="form-control" id="sign-up-password" placeholder="Password"
                                       required>
                                <label for="password">密码</label>
                                <a class="toggle-password input-group-text"">
                                    <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                </a>
                                <div class="invalid-feedback">
                                    请输入密码
                                </div>
                            </div>
                            <div class="form-floating mb-3 input-group">
                                <input type="password" class="form-control" id="sign-up-password2" placeholder="Password"
                                       required>
                                <label for="password2">确认密码</label>
                                <a class="toggle-password input-group-text"">
                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                </a>
                                <div class="invalid-feedback">
                                    密码不相同，请重新输入
                                </div>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary" data-type="submit" type="button">
                                <span class="spinner-border spinner-border-sm hide me-2" role="status"
                                      aria-hidden="true"></span><a>注册</a>
                            </button>

                        </form>

                    </div>

                    <div class="row mt-3 member-form-footer">
                        <p class="text-center mb-2">
                        <div class="position-relative">
                            <hr class="bg-300">
                            <div class="divider-content-center"><small class="text-muted">注册即表示同意 用户协议、 隐私协议</small></div>
                        </div>


                        </p>
                        <div class="col">
                            <small class="text-muted">社交账号登录(即将支持)</small>
                        </div>
                        <div class=" col  ">
                            <ul class="member-social-list  flex-row navbar-nav justify-content-end">
                                <li class="social-item social-qq">
                                    <a  href="/wp-content/plugins/lpcn-user/oauth/qq/example/oauth/index.php" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                       aria-label="QQ" data-bs-original-title="QQ登录" ><i class="fa-brands fa-qq" style="color: #4CAFE9"></i></a>
                                </li>
                               <li class="social-item social-wechat">
                                    <a href="" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                       aria-label="微信" data-bs-original-title="微信登录">
                                        <i class="fa-brands fa-weixin" style="color:#2aae67"></i></a>
                                </li>
<!--                                <li class="social-item social-weibo">
                                    <a href="" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                       aria-label="微博" data-bs-original-title="微博登录">
                                        <i class="fa-brands fa-weibo" style="color: #da2733"></i>
                                    </a>
                                </li>
                                <li class="social-item social-github">
                                    <a href="" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                       aria-label="github" data-bs-original-title="Github登录">
                                        <i class="fa-brands fa-github" style="color: #24292f"></i>
                                    </a>
                                </li>-->
                            </ul>
                        </div>

                    </div>


                </main>
            </div>

        </div>
    </div>
</div>

<div class="position-fixed bottom-05 end-0 p-3 toast-box">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
        <div class="toast-body">
            <section class="success hide"><i class="fad fa-check-circle  me-2"></i><span></span></section>
            <section class="danger hide"><i class="fad fa-exclamation-circle  me-2"></i><span></span></section>
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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
                            <li class="mb-1"><a class="link-600" href="/news/archives/category/maintenance">维护通知</a>
                            </li>

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
                        论坛发帖 <i class="fa-duotone fa-file-circle-plus"></i>
                    </a>
                    <a href="https://jq.qq.com/?_wv=1027&k=AizcubYC" class="btn btn-primary-soft  tooltip-show"
                       data-bs-original-title="" title="">
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
