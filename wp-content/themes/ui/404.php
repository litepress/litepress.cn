<?php
/**
 * 404 页面模板
 */

get_header();
?>
    <main class="container body">
        <style>
            main.container {
                flex: 1;
                display: flex;
                align-items: center;
            }

            .breadcrumb {
                display: none;
            }
        </style>
        <section class="text-center w-100">
            <div class="container">
                <h1 class="display-1 mb-1">404! 😭</h1>
                <h5 class="text-gray-soft text-regular mb-4">这里似乎没有东西。</h5>
                <a class="btn btn-primary" href="/">回到首页</a>
            </div>
        </section>
    </main>
<?php
get_footer();
