<?php
/**
 * 选择认证类型（个人 or 企业）模板
 *
 * @package WP_REAL_PERSON_VERIFY
 */
?>

<article class="container step-page step-certify">
    <div class="row  mb-3 text-center justify-content-around ">
        <div class="col-xl-3">
            <div class="card mb-4 rounded-3 theme-boxshadow">
                <div class="card-body">
                    <i class="fad fa-user-tie"></i>
                    <h1 class="title">个人认证</h1>
                    <p class="des">
                        通过身份证，银行卡等信息进行快速认证。
                    </p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>real-person-verify/personal">
                        <button type="button" class="w-100 btn btn-lg btn-outline-primary">开始认证</button>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card mb-4 rounded-3 theme-boxshadow">
                <div class="card-body">
                    <i class="fad fa-building"></i>
                    <h1 class="title">企业认证</h1>
                    <p class="des">
                        通过营业执照，对公账户，开户证明等方式进行企业认证。
                    </p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>real-person-verify/enterprise">
                        <button type="button" class="w-100 btn btn-lg btn-outline-primary">开始认证</button>
                    </a>
                </div>
            </div>
        </div>

    </div>
</article>
