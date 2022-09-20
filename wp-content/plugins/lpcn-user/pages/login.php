<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SSO 单点登录</title>
    <?php wp_head(); ?>
</head>
<body>

<header class="position-sticky p-2 p-lg-0">
    <div class="d-flex d-lg-none justify-content-center">
        <a href="/">
            <img class="img-fluid" src="/wp-content/themes/ui/assets/img/logo.svg" alt="litepress"  style="width: 150px">
        </a>


    </div>
</header>
<main role="main" class="main pt-0" id="sign-in">
    <!-- Content -->
    <div class="container-fluid px-3">
        <div class="row">
            <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center min-vh-lg-100 position-relative bg-light px-0">
                <!-- Logo & Language -->
                <div class="position-absolute top-0 start-0 end-0 mt-3 mx-3">
                    <div class="d-none d-lg-flex justify-content-between">
                        <a href="/">
                            <img class="w-100" src="/wp-content/themes/ui/assets/img/logo.svg" alt="litepress"
                                 style="max-width: 250px">
                            <!--<img class="w-100" src="./assets/svg/logos-light/logo.svg" alt="Image Description" data-hs-theme-appearance="dark" style="min-width: 7rem; max-width: 7rem;">
                        --></a>


                    </div>
                </div>
                <!-- End Logo & Language -->

                <div style="max-width: 23rem;">
                    <div class="text-center mb-5">
                        <img class="img-fluid"
                             src="/wp-content/plugins/lpcn-user/pages/assets/img/oc-chatting.svg"
                             alt="litepress" style="width: 12rem;" >
                        <!--<img class="img-fluid" src="./assets/svg/illustrations-light/oc-chatting.svg" alt="Image Description" style="width: 12rem;" data-hs-theme-appearance="dark">
                    --></div>

                    <div class="mb-5">
                        <h2 class="display-6">登录你的 LitePress.cn 账号</h2>
                    </div>

                    <!-- List Checked -->
                    <ul class="list-checked list-checked-lg list-checked-primary list-py-2">
                        <li class="list-checked-item">

                            只需要一次登录，你就可以在我们的所有平台中使用你的账号权限。
                        </li>

                    </ul>
                    <!-- End List Checked -->

                    <!-- End Row -->
                </div>
            </div>
            <!-- End Col -->

            <div class="col-lg-6 d-flex justify-content-center align-items-center  min-vh-lg-100 ">



                <div class="w-100 content-space-t-4 content-space-t-lg-2 content-space-b-1" style="max-width: 25rem;">
                    <!-- Form -->

                    <div class="text-center ">

                        <div class="mb-5 d-block d-lg-none pt-4">
                            <h2 class="display-6">登录你的 LitePress.cn 账号</h2>
                        </div>

                        <div class="d-grid mb-4">
                            <a class="btn btn-white " href="/user/oauth/qq"
                               target="_blank">
                    <span class="d-flex justify-content-center align-items-center">
                      <i class="fa-brands fa-fw
                                fa-qq me-2" style="color: #4CAFE9"></i>
                      QQ 登录
                    </span>
                            </a>
                        </div>
                        <div class="d-grid mb-4">
                            <a class="btn btn-white " href="#">
                    <span class="d-flex justify-content-center align-items-center">
                      <i class="fa-brands fa-weixin fa-fw me-2" style="color:#2aae67"></i>
                      微信登录
                    </span>
                            </a>
                        </div>

                        <span class="divider-center text-muted mb-4">或</span>
                    </div>

                    <!-- Form -->
                    <section class="form-sign pb-5 pb-lg-0">

                        <!-- Nav tabs -->

                        <div class="px-4">
                            <ul class="nav lp-nav-tabs nav-tabs mb-4 border-0 justify-content-center" id="sign-tab"
                                role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3 active" id="sign-up-tab" data-bs-toggle="tab"
                                            data-bs-target="#form-sign-up" type="button" role="tab"
                                            aria-controls="profile"
                                            aria-selected="false">免密码登录
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3" id="sign-in-tab" data-bs-toggle="tab"
                                            data-bs-target="#form-sign-in" type="button" role="tab"
                                            aria-controls="home"
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
                                            <input class="form-check-input" name="form-sign-up-agree"
                                                   type="checkbox"
                                                   id="form-sign-up-agree"
                                                   required checked>
                                            <label class="form-check-label text-muted" for="form-sign-up-agree">已阅读并同意
                                                用户协议 和
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
                                                <a role="button" class=""
                                                   onclick="$(this).closest('section').hide().siblings().show()"><i
                                                            class="fa-duotone fa-rotate-left"></i> 上一步</a>

                                            </div>
                                            <label for="sms-code"></label><input type="text" pattern="^\d{4}$"
                                                                                 class=" input-smscode-value p-0"
                                                                                 maxlength="4"
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
                                    <button class="w-100 btn btn-lg btn-primary btn_loading" data-type="submit"
                                            type="button">
                                        登录
                                    </button>

                                </form>


                            </div>
                        </div>


                    </section>
                    <!-- End Form -->
                </div>
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Content -->
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
