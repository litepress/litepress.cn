<?php
/**
 * 文档banner部分
 */
?>
<header class="sub-banner banner">
    <div class="container">
        <div class="row align-items-center project-row">
            <div class="col-xl-6 ">
                <h1><span>手册资源</span></h1></div>
            <div class="col-xl-6">
                <div class="search-form  row justify-content-end">
                    <form class="col-xl-8" method="get" action="/search">
                        <input placeholder="手册搜索..." name="keyword" type="search" value="" id="projects-filter"
                               class="filter-search"
                        >
                        <input type="hidden" name="tag_id" value="6"/>
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