<?php
/**
 * 文档banner部分
 */
?>
<header class="sub-banner banner">
    <div class="container">
        <div class="row align-items-center project-row">
            <div class="col-xl-6 ">
                <h1><span>博客</span></h1></div>
            <div class="col-xl-6">
                <div class="search-form  row justify-content-end">
                    <form class="col-xl-8" method="get" action="https://wp-china-yes.com/">
                        <input placeholder="搜索..." type="search" value="" id="projects-filter" class="filter-search"
                               onkeydown="if(event.keyCode==13)return false;">
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
<section class="breadcrumb">
    <div class="container">
        <?php lpcn_breadcrumb(); ?>
    </div>
</section>