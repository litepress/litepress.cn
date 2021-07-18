<?php
/**
 * 父模板
 *
 * @package WP_REAL_PERSON_VERIFY
 */
?>

<?php get_header(); ?>
<?php if ( ! is_user_logged_in() ): ?>
    <main class="step-main">

        <nav class="step-nav">
            <div class="container">
                <div class="row">
                    <div class="item  in-progress item1">
      <span class="fa-stack fa-lg">
        <i class="fa fa-circle fa-stack-2x"></i>
        <span class="fa-stack-1x">1</span>
      </span>
                        <div class="d-inline-block">
                            <p class="mb-1"><strong>认证类型</strong></p>
                        </div>
                    </div>
                    <span class="col border  mx-3 my-auto"></span>
                    <div class="item text-muted in-waiting item2">
      <span class="fa-stack fa-lg">
        <i class="far fa-circle fa-stack-2x"></i>
        <span class="fa-stack-1x">2</span>
      </span>
                        <div class="d-inline-block">
                            <p class="mb-1">实名认证</p>
                        </div>
                    </div>
                    <span class="col border mx-3 my-auto"></span>
                    <div class="item text-muted in-waiting item3">
      <span class="fa-stack fa-lg">
        <i class="far fa-circle fa-stack-2x"></i>
        <span class="fa fa-stack-1x fa-check"></span>
      </span>
                        <div class="d-inline-block">
                            <p class="mb-1">认证结果</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <article class="container step-page step-complete">
            <div class="row  mb-3 text-center justify-content-around ">
                <div class="col-xl-3">
                    <div class="card mb-4 rounded-3 theme-boxshadow">
                        <div class="card-body">


                            <i class="fad fa-user-tie"></i>
                                <h1 class="title">请登录</h1>
                                <p class="des">

                                </p>
                                <a href="/login" class="step-complete-href">
                                    <button type="button" class="w-100 btn btn-lg btn-outline-primary">去登录</button>
                                </a>


                        </div>
                    </div>
                </div>

            </div>
        </article>

    </main>
<?php else: ?>
    <main class="step-main">

        <nav class="step-nav">
            <div class="container">
                <div class="row">
                    <div class="item  in-progress item1">
      <span class="fa-stack fa-lg">
        <i class="fa fa-circle fa-stack-2x"></i>
        <span class="fa-stack-1x">1</span>
      </span>
                        <div class="d-inline-block">
                            <p class="mb-1"><strong>认证类型</strong></p>
                        </div>
                    </div>
                    <span class="col border  mx-3 my-auto"></span>
                    <div class="item text-muted in-waiting item2">
      <span class="fa-stack fa-lg">
        <i class="far fa-circle fa-stack-2x"></i>
        <span class="fa-stack-1x">2</span>
      </span>
                        <div class="d-inline-block">
                            <p class="mb-1">实名认证</p>
                        </div>
                    </div>
                    <span class="col border mx-3 my-auto"></span>
                    <div class="item text-muted in-waiting item3">
      <span class="fa-stack fa-lg">
        <i class="far fa-circle fa-stack-2x"></i>
        <span class="fa fa-stack-1x fa-check"></span>
      </span>
                        <div class="d-inline-block">
                            <p class="mb-1">认证结果</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
		<?php do_action( 'wprpv_sub_tpl' ); ?>
    </main>

    <script>
        const wprpv_rest_api_nonce = '<?php echo wp_create_nonce( 'wp_rest' ) ?>';
    </script>
<?php endif; ?>
<?php get_footer(); ?>
