<?php
/**
 * Template name: 推广者模板
 * Description: 该模板是推广者模板
 */

get_header();
?>
    <div id="onvoarding-container">
        <onboarding-background class="fade-in">
            <div id="imgcontainer" style="display: block;">
                <!-- Using span as container for an :after element that actually contains the blue-circle svg, because the animation needs to curve so x and y needs to be animated separately. -->
                <span id="red-triangle"></span>
                <span id="yellow-semicircle"></span>
            </div>
        </onboarding-background>
    </div>
    <main class="wp-body promoters">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <section class="theme-boxshadow wp-banner bg-white">
                        <h2 class="m-4 text-center">
                            <span class="heading-text">TA 们在使用 LitePress</span>
                        </h2>
                        <!-- <p class="heading-p"></p>-->
                        <div class="text-center pb-4">
                            <div class="wp-btn-group">
                                <a class="btn btn-outline-primary" href="#" role="button"><i
                                            class="fad fa-arrow-alt-left"></i>返回社区</a>
                                <a class="btn btn-primary" href="#" role="button"><i class="fad fa-pencil-alt"></i>申请上墙</a>
                            </div>
                        </div>
                    </section>

                    <section class=" mt-4 promoters-card">
                        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 g-3">
                            <div class="col">

                                <div class="card shadow-sm">
                                    <a href="https://www.wpdaxue.com/wordpress-org-429-too-many-requests.html"
                                       target="_blank" rel="noreferrer noopener">
                                        <!--<div class="placeholder">
                                            应用市场入驻帮助                                    </div>-->
                                        <div class="promoters-logo">
                                            <img src="https://litepress.cn/wp-content/uploads/2021/01/wpdaxue-logo-new.png"
                                                 alt="" loading="lazy">
                                        </div>

                                    </a>
                                </div>
                            </div>
                            <div class="col">

                                <div class="card shadow-sm">
                                    <a href="https://www.wpdaxue.com/wordpress-org-429-too-many-requests.html"
                                       target="_blank" rel="noreferrer noopener">
                                        <!--<div class="placeholder">
                                            应用市场入驻帮助                                    </div>-->
                                        <div class="promoters-logo">
                                            <img src="https://litepress.cn/wp-content/uploads/2021/01/wpdaxue-logo-new.png"
                                                 alt="" loading="lazy">
                                        </div>

                                    </a>
                                </div>
                            </div>
                            <div class="col">

                                <div class="card shadow-sm">
                                    <a href="https://www.wpdaxue.com/wordpress-org-429-too-many-requests.html"
                                       target="_blank" rel="noreferrer noopener">
                                        <!--<div class="placeholder">
                                            应用市场入驻帮助                                    </div>-->
                                        <div class="promoters-logo">
                                            <img src="https://litepress.cn/wp-content/uploads/2021/01/wpdaxue-logo-new.png"
                                                 alt="" loading="lazy">
                                        </div>

                                    </a>
                                </div>
                            </div>


                        </div>
                    </section>

                </div>
                <?php get_sidebar(); ?>
            </div>
        </div>
    </main>
<?php
get_footer();
