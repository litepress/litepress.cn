<?php
/**
 * 实名完成模板
 *
 * @package WP_REAL_PERSON_VERIFY
 */
?>

<article class="container step-page step-complete">
    <div class="row  mb-3 text-center justify-content-around ">
        <div class="col-xl-3">
            <div class="card mb-4 rounded-3 theme-boxshadow">
                <div class="card-body">

                    <?php if ( 'yes' === sanitize_key( $_GET['passed'] ) ): ?>
                    <i class="fad fa-check-circle"></i>
                    <h1 class="title">认证通过</h1>
                        <p class="des">

                        </p>
                        <a href="/" class="step-complete-href">
                            <button type="button" class="w-100 btn btn-lg btn-outline-primary"><span id="num">3</span>秒后返回首页</button>
                        </a>
                        <?php else: ?>
                    <i class="fad fa-times-circle"></i>
                    <h1 class="title">认证未通过</h1>
                        <p class="des">

                        </p>
                        <a href="#" onclick="javascript:history.back(-1);">
                            <button type="button" class="w-100 btn btn-lg btn-outline-primary">返回重新认证</button>
                        </a>
                        <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</article>



