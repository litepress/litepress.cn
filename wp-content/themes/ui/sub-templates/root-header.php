<?php
/**
 * 文档banner部分
 */
?>

<?php if ( str_starts_with( $_SERVER['REQUEST_URI'], '/forums' ) ) : ?>
    <header class="sub-banner banner">
        <div class="container">
            <div class="row align-items-center project-row">
                <div class="col-xl-6 ">
                    <h1><span>社区论坛</span></h1></div>
                <div class="col-xl-6">
                    <div class="search-form  row justify-content-end">
                        <form class="col-xl-8" method="get" action="/search">
                            <input placeholder="论坛搜索..." name="keyword" type="search" value="" id="projects-filter"
                                   class="filter-search"
                            >
                            <input type="hidden" name="tag_id" value="1"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="breadcrumb">
        <div class="container">
			<?php bbp_breadcrumb(); ?>
        </div>
    </section>
<?php endif; ?>
