<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SSO 单点登录</title>
    <?php wp_head(); ?>
</head>
<body>


<main id="content" role="main" class="main pt-0">
    <!-- Content -->
    <div class="container-fluid px-3">
        <div class="row">
            <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center min-vh-lg-100 position-relative bg-light px-0">
                <!-- Logo & Language -->
                <div class="position-absolute top-0 start-0 end-0 mt-3 mx-3">
                    <div class="d-none d-lg-flex justify-content-between">
                        <a href="./index.html">
                            <img class="w-100" src="/wp-content/uploads/2021/05/logo.svg" alt="Image Description" data-hs-theme-appearance="default" style="min-width: 7rem; max-width: 7rem;">
                            <!--<img class="w-100" src="./assets/svg/logos-light/logo.svg" alt="Image Description" data-hs-theme-appearance="dark" style="min-width: 7rem; max-width: 7rem;">
                        --></a>


                    </div>
                </div>
                <!-- End Logo & Language -->

                <div style="max-width: 23rem;">
                    <div class="text-center mb-5">
                        <img class="img-fluid" src="https://htmlstream.com/preview/front-dashboard-v2.0/assets/svg/illustrations/oc-chatting.svg" alt="Image Description" style="width: 12rem;" data-hs-theme-appearance="default">
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

            <div class="col-lg-6 d-flex justify-content-center align-items-center min-vh-lg-100">
                <div class="w-100 content-space-t-4 content-space-t-lg-2 content-space-b-1" style="max-width: 25rem;">
                    <!-- Form -->
                    <form class="js-validate needs-validation" novalidate="">
                        <div class="text-center">
                            <div class="mb-5">
                                <h1 class="display-5">Sign in</h1>
                                <p>Don't have an account yet? <a class="link" href="./authentication-signup-cover.html">Sign up here</a></p>
                            </div>

                            <div class="d-grid mb-4">
                                <a class="btn btn-white btn-lg" href="#">
                    <span class="d-flex justify-content-center align-items-center">
                      <img class="avatar avatar-xss me-2" src="./assets/svg/brands/google-icon.svg" alt="Image Description">
                      Sign in with Google
                    </span>
                                </a>
                            </div>

                            <span class="divider-center text-muted mb-4">OR</span>
                        </div>

                        <!-- Form -->
                        <div class="mb-4">
                            <label class="form-label" for="signinSrEmail">Your email</label>
                            <input type="email" class="form-control form-control-lg" name="email" id="signinSrEmail" tabindex="1" placeholder="email@address.com" aria-label="email@address.com" required="">
                            <span class="invalid-feedback">Please enter a valid email address.</span>
                        </div>
                        <!-- End Form -->

                        <!-- Form -->
                        <div class="mb-4">
                            <label class="form-label w-100" for="signupSrPassword" tabindex="0">
                  <span class="d-flex justify-content-between align-items-center">
                    <span>Password</span>
                    <a class="form-label-link mb-0" href="./authentication-reset-password-cover.html">Forgot Password?</a>
                  </span>
                            </label>

                            <div class="input-group input-group-merge" data-hs-validation-validate-class="">
                                <input type="password" class="js-toggle-password form-control form-control-lg" name="password" id="signupSrPassword" placeholder="8+ characters required" aria-label="8+ characters required" required="" minlength="8" data-hs-toggle-password-options="{
                           &quot;target&quot;: &quot;#changePassTarget&quot;,
                           &quot;defaultClass&quot;: &quot;bi-eye-slash&quot;,
                           &quot;showClass&quot;: &quot;bi-eye&quot;,
                           &quot;classChangeTarget&quot;: &quot;#changePassIcon&quot;
                         }">
                                <a id="changePassTarget" class="input-group-append input-group-text" href="javascript:;">
                                    <i id="changePassIcon" class="bi-eye-slash"></i>
                                </a>
                            </div>

                            <span class="invalid-feedback">Please enter a valid password.</span>
                        </div>
                        <!-- End Form -->

                        <!-- Form Check -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="" id="termsCheckbox">
                            <label class="form-check-label" for="termsCheckbox">
                                Remember me
                            </label>
                        </div>
                        <!-- End Form Check -->

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Sign in</button>
                        </div>
                    </form>
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
