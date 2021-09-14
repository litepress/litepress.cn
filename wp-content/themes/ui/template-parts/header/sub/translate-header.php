<?php
/**
 * 翻译banner部分
 */
?>
<?php if ( '/translate/' !== $_SERVER['REQUEST_URI'] ): ?>
    <header class="sub-banner banner">
        <div class="container">
            <div class="row align-items-center project-row">
                <div class="col-xl-6 ">
                    <h1>
                    <span>
                        <?php
                        echo substr( gp_title(), 0, strlen( gp_title() ) - ( strlen( ' – LitePress翻译平台' ) + 3 ) );
                        ?>
                    </span>
                    </h1>
                </div>
                <div class="col-xl-6">
                    <div class="search-form  row justify-content-end">
                        <div class="col-xl-4 text-end">
                            <a class="btn btn-light btn-small"
                               href="/translate/languages/zh-cn/default/glossary">术语表</a>
                        </div>
                        <form class="col-xl-8">
                            <input placeholder="搜索插件..." type="search" id="projects-filter" name="s" value=""
                                   class="filter-search" onkeydown="if(event.keyCode==13)return false;">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>


<?php endif; ?>