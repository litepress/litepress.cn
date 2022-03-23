<!-- Modal -->
<div class="modal fade" id="sign-in" tabindex="-1" data-bs-backdrop="static" aria-labelledby="" aria-hidden="true"
     xmlns="http://www.w3.org/1999/html">
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
                    <ul class="nav lp-nav-tabs nav-tabs mb-3 border-0" id="sign-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sign-up-tab" data-bs-toggle="tab"
                                    data-bs-target="#form-sign-up" type="button" role="tab" aria-controls="profile"
                                    aria-selected="false">免密码登录/注册
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link " id="sign-in-tab" data-bs-toggle="tab"
                                    data-bs-target="#form-sign-in" type="button" role="tab" aria-controls="home"
                                    aria-selected="true">密码登录
                            </button>
                        </li>


                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">

                        <form class="needs-validation tab-pane active" id="form-sign-up" role="tabpanel">
                            <section class="">
                                <div class="form-floating mt-4 mb-3 input-group">
                                    <input type="text" class="form-control" id="mobile"
                                           placeholder="13*******"
                                           pattern="^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$"
                                           required>

                                    <label for="mobile">手机号</label>
                                    <a class="send-sms-code input-group-text" role="button">
                                        发送验证码
                                    </a>
                                    <div class="form-text w-100">未注册手机验证后自动登录</div>
                                    <div class="invalid-feedback">
                                        请输入正确的手机号
                                    </div>
                                </div>

                                <div class="checkbox col">
                                    <input class="form-check-input" name="form-sign-up-agree" type="checkbox"
                                           id="form-sign-up-agree"
                                           required checked>
                                    <label class="form-check-label text-muted" for="form-sign-up-agree">已阅读并同意 用户协议 和
                                        隐私政策</label>
                                    <div class="invalid-feedback">
                                        请勾选同意协议
                                    </div>
                                </div>
                            </section>
                            <section class="hide">
                                <div class="d-flex align-items-center  my-4">
    <span class="fa-stack fa-2x me-2">
<i class="fa-duotone fa-mobile fa-stack-2x"></i>
    <i class="fa-solid fa-message text-primary position-absolute top-0 end-0 m-2"></i>
  </span>
                                    <div class="text-muted">
                                        验证码已发送到您填写的手机号码上<br>有效期5分钟，请注意查收
                                    </div></div>
                                <div style="text-align: center;position: relative;">
                                    <small class="text-muted">直接输入您收到的4位验证码,会自动验证</small>
                                    <ul class="input-smscode mb-2 d-inline-flex">
                                        <li></li>
                                        <li></li>
                                        <li></li>
                                        <li></li>
                                    </ul>
                                    <div>
                                    <!--<a class="send-sms-code" role="button">
                                        重新发送
                                    </a>-->
                                        <a role="button" class="" onclick="$(this).closest('section').hide().siblings().show()"><i class="fa-duotone fa-rotate-left"></i> 上一步</a>

                                    </div>
                                    <input type="text" pattern="^\d{4}$" class=" input-smscode-value p-0" maxlength="4" id="sms-code"
                                            style="outline: none;width:100%;height:0px;border:0;background-color: transparent;color:transparent;     position: absolute;">

                                     </div>


                            </section>
                            <input type="hidden" name="tcaptcha-ticket" class="tcaptcha-ticket" value="">
                            <input type="hidden" name="tcaptcha-randstr" class="tcaptcha-randstr" value="">
                        </form>

                        <form class="needs-validation tab-pane " id="form-sign-in" role="tabpanel">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control username" id="sign-in-username" placeholder="name@example.com"
                                       required>
                                <label for="sign-in-username">手机号/用户名/邮箱</label>
                                <div class="invalid-feedback">
                                    请输入帐号
                                </div>
                            </div>
                            <div class="form-floating mb-3 input-group">
                                <input type="password" class="form-control password" id="sign-in-password" placeholder="Password"
                                       required>
                                <label for="sign-in-password">密码</label>
                                <a class="toggle-password input-group-text"">
                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                </a>
                                <div class="invalid-feedback">
                                    请输入登录密码
                                </div>
                            </div>
                            <div class="row mb-3">

                                <div class=" col text-end">
                                    <a target="_blank" href="/password-reset">忘记密码？</a>
                                </div>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary btn_loading" data-type="submit" type="button">
                                登录
                            </button>
                            <input type="hidden" name="tcaptcha-ticket" class="tcaptcha-ticket" value="">
                            <input type="hidden" name="tcaptcha-randstr" class="tcaptcha-randstr" value="">
                        </form>


                    </div>

                    <div class="row mt-3 member-form-footer">
                        <div class="position-relative">
                            <hr class="bg-300">
                            <div class="divider-content-center"><small class="text-muted">社交账号登录(即将支持)</small></div>
                        </div>

                        <div class=" col  ">
                            <ul class="member-social-list  flex-row navbar-nav justify-content-center">
                                <li class="social-item social-qq">
                                    <a href="/user/oauth/qq"
                                       target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                       aria-label="QQ" data-bs-original-title="QQ登录"><i class="fa-brands fa-qq"
                                                                                        style="color: #4CAFE9"></i></a>
                                </li>
                                <li class="social-item social-wechat">
                                    <a href="" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                       title=""
                                       aria-label="微信" data-bs-original-title="微信登录">
                                        <i class="fa-brands fa-weixin" style="color:#2aae67"></i></a>
                                </li>

                            </ul>
                        </div>

                    </div>


                </main>
            </div>

        </div>
    </div>
</div>

<div class="position-fixed top-15 start-50 translate-middle p-3 toast-box">
    <div id="liveToast" class="toast w-auto" role="alert" aria-live="assertive" aria-atomic="true">
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
                    <a href="/create" <?php if (!is_user_logged_in()) {
                        echo ' data-bs-toggle="modal" data-bs-target="#sign-in" ';
                    } ?> class="btn btn-primary-soft  me-1">
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
