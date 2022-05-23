<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <?php wp_head(); ?>
</head>
<body>
<main class="center bg-light" id="sign-in">
    <div class="modal-s main w-100">
        <section class="form-sign card  p-4">
            <img class="mb-4 mx-auto" alt="" width="80%"
                 src="https://dev.litepress.cn/wp-content/uploads/2021/05/logo.svg">
            <!-- Nav tabs -->
            <ul class="nav lp-nav-tabs nav-tabs mb-4 border-0" id="sign-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 active" id="sign-up-tab" data-bs-toggle="tab"
                            data-bs-target="#form-sign-up" type="button" role="tab" aria-controls="profile"
                            aria-selected="false">免密码登录
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3" id="sign-in-tab" data-bs-toggle="tab"
                            data-bs-target="#form-sign-in" type="button" role="tab" aria-controls="home"
                            aria-selected="true">密码登录
                    </button>
                </li>


            </ul>

            <!-- Tab panes -->
            <div class="tab-content">

                <form class="needs-validation tab-pane active" id="form-sign-up">
                    <section class="">
                        <div class="form-floating mb-3 input-group">
                            <input type="text" class="form-control" id="mobile"
                                   placeholder="13*******"
                                   pattern="^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$"
                                   required>

                            <label for="mobile">手机号</label>
                            <a class="send-sms-code input-group-text right" role="button">
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
                            </div>
                        </div>
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
                                <a role="button" class="" onclick="$(this).closest('section').hide().siblings().show()"><i
                                            class="fa-duotone fa-rotate-left"></i> 上一步</a>

                            </div>
                            <label for="sms-code"></label><input type="text" pattern="^\d{4}$" class=" input-smscode-value p-0" maxlength="4"
                                                                 id="sms-code"
                                                                 style="outline: none;width:100%;height:0;border:0;background-color: transparent;color:transparent;     position: absolute;">

                        </div>


                    </section>

                </form>

                <form class="needs-validation tab-pane " id="form-sign-in">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control username" id="sign-in-username"
                               placeholder="name@example.com"
                               required>
                        <label for="sign-in-username">手机号/用户名/邮箱</label>
                        <div class="invalid-feedback">
                            请输入帐号
                        </div>
                    </div>
                    <div class="form-floating mb-3 input-group">
                        <input type="password" class="form-control password" id="sign-in-password"
                               placeholder="Password"
                               required>
                        <label for="sign-in-password">密码</label>
                        <a class="toggle-password input-group-text right">
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

                </form>


            </div>

            <div class="row mt-3 member-form-footer">
                <div class="position-relative">
                    <hr class="bg-300">
                    <div class="divider-content-center"><small class="text-muted">社交账号登录(微信即将支持)</small></div>
                </div>

                <div class=" col  ">
                    <ul class="member-social-list  flex-row navbar-nav justify-content-center">
                        <li class="social-item social-qq  py-2 px-0 px-lg-2 ">
                            <a href="/user/oauth/qq"
                               target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                               aria-label="QQ" data-bs-original-title="QQ登录"><i class="fa-brands fa-qq"
                                                                                style="color: #4CAFE9"></i></a>
                        </li>
                        <li class="social-item social-wechat py-2 px-0 px-lg-2">
                            <a href="" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom"
                               title=""
                               aria-label="微信" data-bs-original-title="微信登录">
                                <i class="fa-brands fa-weixin" style="color:#2aae67"></i></a>
                        </li>

                    </ul>
                </div>

            </div>


        </section>
    </div>
</main>


<div class="tncode d-none"></div>

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

<!-- #site-footer -->



<?php wp_footer(); ?>
</body>
</html>
