<?php
add_shortcode('lpcn-user-center', function () {
    return <<<HTML
<main class="wp-body">
    <section class="container ">
        <div class="row">
            <div class="col-lg-3">
                <div class="navbar-expand-lg navbar-light">
                    <div id="sidebarNav" class="navbar-collapse navbar-vertical collapse" style="">
                        <div class="card flex-grow-1 mb-5 theme-boxshadow">
                            <div class="card-body">
                                <div class="text-center  p-4 pt-2">
                                    <div class="avatar lp-avatar overflow-hidden avatar-xxl m-auto mb-3  position-relative"
                                         data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                         data-bs-original-title="点击更改头像">
                                        <img class="img-fluid"
                                             src="https://cravatar.cn/avatar/526d5c35b2092765ce9865d807612f33?s=200&d=mp&test=1"
                                             alt="图片描述">
                                        <span class="avatar-tooltip"><i class="fad fa-camera-retro"></i></span>

                                    </div>

                                    <h4 class="card-title mb-0">Yulinn</h4>
                                    <p class="card-text text-muted small">i@litepress.cn</p>
                                </div>

                                <span class="text-cap ps-3">账号</span>

                                <ul class="nav nav-sm nav-tabs nav-tabs-user nav-vertical  mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-target="#lp-user-data" data-bs-toggle="tab"
                                           type="button">
                                            <i class="fa-duotone fa-fw me-2 fa-user-pen"></i> 个人信息
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link " data-bs-target="#lp-user-security" data-bs-toggle="tab"
                                           type="button">
                                            <i class="fa-duotone fa-fw me-2 fa-shield"></i> 安全
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link " type="button">
                                            <i class="fa-duotone fa-fw me-2 fa-bells"></i> 系统通知
                                            <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">1</span>
                                        </a>
                                    </li>
                                </ul>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">

                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="lp-user-data" role="tabpanel"
                         aria-labelledby="v-pills-home-tab">
                        <div class="card theme-boxshadow ">
                            <div class="card-header p-4 bg-white">
                                <h5 class="card-header-title">基础信息</h5>
                            </div>
                            <div class="card-body p-4">
                                <form class="info-form">
                                    <div class="row mb-4">
                                        <label for="firstNameLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw  fa-user"></i> 昵称<i
                                                class="fa-regular fa-circle-question text-muted ms-1"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                                data-bs-original-title="昵称将在全站显示" aria-label="昵称将在全站显示"></i></label>

                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="display_name"
                                                       id="firstNameLabel" placeholder="昵称" aria-label="昵称" value="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="firstNameLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw  fa-id-badge"></i> 专属铭牌 <i
                                                class="fa-regular fa-circle-question text-muted ms-1"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                                data-bs-original-title="你的专属铭牌将显示在网站的各处，你只需要替换默认值中的标题和网址即可哦~"
                                                aria-label="你的专属铭牌将显示在网站的各处，你只需要替换默认值中的标题和网址即可哦~"></i><small
                                                class="text-muted">（可选）</small></label>

                                        <div class="col-sm-9">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="text" class="form-control" placeholder="标题"
                                                           id="nameplate_text" aria-label="First name">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control" placeholder="链接"
                                                           id="nameplate_url" aria-label="Last name">
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-venus-mars"></i> 性别</label>

                                        <div class="col-sm-9 gender">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="exampleRadios"
                                                       id="exampleRadios1" value="男">
                                                <label class="form-check-label" for="exampleRadios1">
                                                    男
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="exampleRadios"
                                                       id="exampleRadios2" value="女">
                                                <label class="form-check-label" for="exampleRadios2">
                                                    女
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-comment-text"></i> 简介<small
                                                class="text-muted">（可选 限200字）</small></label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control d-none" id="brief"
                                                      maxlength="65525"></textarea>
                                            <section class="wang-editor">
                                                <div id="bbp-editor-toolbar" class="editor-toolbar"></div>
                                                <div id="bbp-editor-container" class="editor-container heti"></div>
                                            </section>
                                        </div>
                                    </div>

                                    <div class="card-footer pt-0 bg-white border-0 p-0">
                                        <div class="d-flex justify-content-end gap-3">

                                            <a class="btn btn-primary" role="button"><i
                                                    class="fa-duotone fa-floppy-disk fa-fw me-1"></i>保存更改</a>
                                        </div>
                                    </div> 

                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="lp-user-security" role="tabpanel">

                        <div class="card theme-boxshadow ">
                            <div class="card-header p-4 bg-white">
                                <div class="card-header-title"><span class="h5">账号绑定</span><small class="text-muted">（仅自己可见）</small>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <form >

                                    <div class="row mb-4">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-envelope"></i> 绑定邮箱</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            未绑定 <a class="ms-2 bind-a" data-tab="#tab-bind-email" role="button"
                                                   data-bs-toggle="modal" data-bs-target="#bind-modal">立即绑定</a>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-mobile"></i> 绑定手机</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            未绑定 <a class="mx-2 bind-a" data-tab="#tab-bind-mobile" role="button"
                                                   data-bs-toggle="modal" data-bs-target="#bind-modal">立即绑定</a>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label">
                                            <svg t="1648106706276" class="icon" viewBox="0 0 1024 1024" version="1.1"
                                                 xmlns="http://www.w3.org/2000/svg" p-id="3495" width="20" height="16">
                                                <path d="M672 1024c-64 0-115.2-12.8-160-40.533333l-2.133333 0C469.333333 1011.2 413.866667 1024 347.733333 1024c-61.866667 0-115.2-14.933333-157.866667-40.533333-51.2-29.866667-78.933333-76.8-78.933333-130.133333 0-10.666667 2.133333-23.466667 4.266667-34.133333-36.266667-21.333333-61.866667-61.866667-68.266667-113.066667-6.4-46.933333 0-100.266667 19.2-160C76.8 512 93.866667 469.333333 128 435.2c2.133333-27.733333 8.533333-55.466667 17.066667-78.933333C147.2 264.533333 183.466667 181.333333 251.733333 106.666667c72.533333-72.533333 160-106.666667 260.266667-106.666667 102.4 0 189.866667 36.266667 260.266667 106.666667 68.266667 68.266667 104.533333 153.6 106.666667 251.733333 10.666667 27.733333 14.933333 49.066667 17.066667 74.666667 23.466667 29.866667 49.066667 68.266667 64 115.2 19.2 59.733333 25.6 113.066667 19.2 160-6.4 55.466667-29.866667 93.866667-68.266667 113.066667 2.133333 10.666667 4.266667 21.333333 4.266667 32 0 51.2-25.6 96-78.933333 130.133333C793.6 1009.066667 738.133333 1024 672 1024zM480 898.133333l57.6 0 10.666667 8.533333c32 23.466667 70.4 34.133333 123.733333 34.133333 51.2 0 89.6-8.533333 119.466667-27.733333 36.266667-23.466667 40.533333-42.666667 40.533333-57.6 0-14.933333-2.133333-23.466667-10.666667-34.133333l-44.8-66.133333L853.333333 755.2c0 0 0 0 0 0l0 0 8.533333-2.133333c8.533333-2.133333 25.6-8.533333 29.866667-53.333333 4.266667-34.133333 0-74.666667-14.933333-121.6l0-2.133333c-8.533333-29.866667-25.6-59.733333-57.6-96l-10.666667-12.8L808.533333 448c0-21.333333-4.266667-40.533333-14.933333-66.133333l-2.133333-8.533333 0-8.533333c0-78.933333-27.733333-142.933333-81.066667-198.4C657.066667 113.066667 590.933333 85.333333 512 85.333333c-78.933333 0-145.066667 27.733333-200.533333 81.066667-53.333333 59.733333-81.066667 125.866667-81.066667 198.4l0 10.666667L226.133333 384c-8.533333 17.066667-12.8 38.4-12.8 61.866667l0 25.6-14.933333 12.8c-27.733333 23.466667-42.666667 57.6-51.2 85.333333-14.933333 46.933333-21.333333 87.466667-14.933333 121.6 4.266667 25.6 12.8 44.8 29.866667 53.333333 4.266667 0 8.533333 2.133333 12.8 4.266667 0 0 2.133333 0 2.133333 0l76.8 0-44.8 66.133333c-8.533333 10.666667-10.666667 21.333333-10.666667 34.133333 0 23.466667 12.8 42.666667 38.4 57.6l2.133333 2.133333c27.733333 19.2 66.133333 27.733333 113.066667 27.733333 53.333333 0 96-10.666667 121.6-32L480 898.133333z"
                                                      p-id="3496"></path>
                                            </svg>
                                            绑定 QQ</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            未绑定
                                            <div class="social-item"><a class="" href="/user/oauth/qq">立即绑定</a></div>
                                        </div>
                                    </div>


                                </form>
                            </div>
                        </div>

                        <div class="card theme-boxshadow mt-4">
                            <div class="card-header p-4 bg-white">
                                <div class="card-header-title"><span class="h5">修改密码</span></div>
                            </div>
                            <div class="card-body p-4">
                                <form>

                                    <div class="row mb-4">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-unlock-keyhole"></i> 旧密码</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="sign-up-password"
                                                       placeholder="旧密码"
                                                       required>
                                                <a class="toggle-password input-group-text"">
                                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                                </a>
                                                <div class="invalid-feedback">
                                                    请输入密码
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-unlock-keyhole"></i> 新密码</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="sign-up-password"
                                                       placeholder="新密码"
                                                       required>
                                                <a class="toggle-password input-group-text"">
                                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                                </a>
                                                <div class="invalid-feedback">
                                                    请输入密码
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label">
                                            <i class="fa-regular fa-fw fa-unlock-keyhole "></i> 确认密码</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="sign-up-password2"
                                                       placeholder="确认密码"
                                                       required>
                                                <a class="toggle-password input-group-text"">
                                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                                </a>
                                                <div class="invalid-feedback">
                                                    密码不相同，请重新输入
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="card-footer pt-0 bg-white border-0 p-0">
                                        <div class="d-flex justify-content-end gap-3">

                                            <a class="btn btn-primary" href="javascript:;"><i
                                                    class="fa-duotone fa-floppy-disk fa-fw me-1"></i>保存更改</a>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>

                        <div class="card theme-boxshadow mt-4">
                            <div class="card-header p-4 bg-white">
                                <div class="card-header-title"><span class="h5">删除账号</span></div>
                            </div>
                            <div class="card-body p-4">
                                <form>
                                    <p class="card-text">当您删除您的帐户时，您将无法再次登录这个账户，我们会将您的账号注销</p>
                                    <div class="row mb-4 mt-3">
                                        <label for="emailLabel" class="col-sm-3 col-form-label form-label"><i
                                                class="fa-regular fa-fw fa-mobile"></i> 输入您的密码确认删除</label>

                                        <div class="col-sm-9 d-flex align-items-center">
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="sign-up-password"
                                                       placeholder="密码"
                                                       required>
                                                <a class="toggle-password input-group-text"">
                                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                                </a>
                                                <div class="invalid-feedback">
                                                    请输入密码
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="card-footer pt-0 bg-white border-0 p-0">
                                        <div class="d-flex justify-content-end gap-3">

                                            <a class="btn btn btn-danger" href="javascript:;"><i
                                                    class="fa-duotone fa-delete-right fa-fw me-1"></i>删除账号</a>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>


                    </div>
                    <div class="tab-pane fade" id="v-pills-messages" role="tabpanel"
                         aria-labelledby="v-pills-messages-tab">...
                    </div>
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel"
                         aria-labelledby="v-pills-settings-tab">...
                    </div>
                </div>

                <!-- Card -->

            </div>
    </section>
</main>

<div class="modal fade" id="bind-modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-s modal-dialog-centered ">
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
                    <ul class="nav lp-nav-tabs nav-tabs mb-3 border-0" id="" role="tablist">

                        <li class="nav-item hide" role="presentation">
                            <button class="nav-link " id="tab-bind-email" data-bs-toggle="tab"
                                    data-bs-target="#bind-email" type="button" role="tab" aria-controls="home"
                                    aria-selected="true">绑定邮箱
                            </button>
                        </li>

                        <li class="nav-item hide" role="presentation">
                            <button class="nav-link " id="tab-bind-mobile" data-bs-toggle="tab"
                                    data-bs-target="#bind-mobile" type="button" role="tab" aria-controls="home"
                                    aria-selected="true">绑定手机
                            </button>
                        </li>

                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">


                        <form class="tab-pane" id="bind-email">

                            <div class="form-floating mt-4 mb-3 ">
                                <input type="email" class="form-control email" id="" placeholder="name@example.com"
                                       required>
                                <label for="mobile">电子邮箱</label>

                                <div class="invalid-feedback">
                                    请输入正确的邮箱号
                                </div>
                            </div>

                            <div class="form-floating input-group">
                                <input type="text" class="form-control code" id="" placeholder="name@example.com"
                                       required>
                                <label for="mobile">验证码</label>
                                <a class="send-sms-code input-group-text" role="button">
                                    发送验证码
                                </a>
                            </div>
                        </form>

                        <form class="needs-validation tab-pane" id="bind-mobile" role="tabpanel">
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
                                        <a role="button" class=""
                                           onclick="$(this).closest('section').hide().siblings().show()"><i
                                                class="fa-duotone fa-rotate-left"></i> 上一步</a>

                                    </div>
                                    <input type="text" pattern="^\d{4}$" class=" input-smscode-value p-0" maxlength="4"
                                           id="sms-code"
                                           style="outline: none;width:100%;height:0px;border:0;background-color: transparent;color:transparent;     position: absolute;">

                                </div>


                            </section>
                            <input type="hidden" name="tcaptcha-ticket" class="tcaptcha-ticket" value="">
                            <input type="hidden" name="tcaptcha-randstr" class="tcaptcha-randstr" value="">
                        </form>

                    </div>


                </main>
            </div>

        </div>
    </div>
</div>
HTML;
});
