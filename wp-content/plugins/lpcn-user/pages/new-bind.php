<?php
/**
 * 处理用户绑定的通用页面
 *
 * 比如说使用手机号登录时绑定已有用户，以及使用 QQ 登录时绑定已有用户。
 */

add_action('wp_loaded', function () {
    list($uri) = explode('?', $_SERVER['REQUEST_URI']);
    if ('/user/new/bind' === $uri) {
        $token = sanitize_text_field($_GET['token'] ?? '');

        $token_data = get_transient("lpcn_user_bind_{$token}");
        if (empty($token_data)) {
            wp_die('Token 过期或不存在');
        }

        get_header();

        $login_type      = sanitize_text_field($_GET['type'] ?? '');
        $login_type_html = match ($login_type) {
            'qq' => '<i class="fa-brands fa-qq" style="color: #4CAFE9"></i> QQ',
            'mobile' => '<i class="fa-duotone fa-mobile"></i> 手机号',
            default => '<i class="fa-duotone fa-question"></i> 未知',
        };

        $content = match ($login_type) {
            'qq' => <<<HTML
<div class="d-flex align-items-center col-10 mb-4">
									<img class="lp-avatar me-3" src="{$token_data["figureurl"]}" alt="{$token_data["qq_nickname"]}">
                                    <div class="text-muted">
                                        欢迎你，{$token_data["qq_nickname"]}！<br>
                                        当前你正在使用 {$login_type_html} 登录，请绑定已有帐户，或者注册新用户绑定。
                                    </div>
                            </div>
HTML,
            'mobile' => <<<HTML
<div class="d-flex align-items-center col-10 mb-4"> 
                                        当前你正在使用 {$login_type_html} 登录，请绑定已有帐户，或者注册新用户绑定。
                                    </div>
                            </div>
HTML
        };

        echo <<<HTML
		<main class="wp-body d-flex">
        <div class="container" >
                <div class="bg-white theme-boxshadow pt-5  pb-5 m-xl-0 m-3 row justify-content-center" >
                    <div class="row justify-content-center gx-5" >
                    	<section class="col-12 row  justify-content-center">
                            $content
						</section>
						<hr class="mb-4">
                <section class="col-12 col-xl-5 border-xl-end border-0">
                <h6 class="form-title">我是老用户，绑定已有帐户</h6>
                    <form class="needs-validation mt-3" id="bd-old-account" >
                            <div class="form-floating mb-3 was-validated">
                                <input type="text" class="form-control username" id="qq-sign-up-username" placeholder="name@example.com" required="">
                                <label for="qq-sign-up-username">手机号/用户名/邮箱</label>
                                <div class="invalid-feedback">
                                    请输入帐号
                                </div>
                            </div>
                            <div class="form-floating mb-3 input-group">
                                <input type="password" class="form-control password" id="qq-sign-up-password" placeholder="Password" required="">
                                <label for="qq-sign-up-password">密码</label>
                                <a class="toggle-password input-group-text" "="">
                                <i class="fa-duotone fa-fw fa-eye-slash"></i>
                                </a>
                                <div class="invalid-feedback">
                                    请输入登录密码
                                </div>
                            </div>

                            <button class="w-100 btn btn-primary btn_loading" data-type="submit" type="button">
                               <i class="fa-duotone fa-user-lock"></i> 登录并绑定
                            </button>
                            <input type="hidden" name="tcaptcha-ticket" class="tcaptcha-ticket" value="">
                            <input type="hidden" name="tcaptcha-randstr" class="tcaptcha-randstr" value="">
                        </form>
				</section>

				<section class="col-12 col-xl-5 mt-4 mt-xl-0 border-top border-xl-top-0 pt-xl-0 pt-3">
				<h6 class="form-title">我是新用户，没有帐户</h6>
				<form class="needs-validation mt-3" id="bd-new-account" >
                    <button class="w-100 btn btn-primary" data-type="submit" type="button">
                       <i class="fa-duotone fa-user-plus"></i> 新账号登录
                    </button>
                </form>
				</section>

                </div>
        </div>
      </div>
    </main>
HTML;

        get_footer();
        exit;
    }
});

