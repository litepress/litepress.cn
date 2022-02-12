<?php
/**
 * 站点banner部分
 */

?>
<header class="sub-banner banner">
    <div class="container">
        <div class="row align-items-center project-row">
            <div class="col-xl-6 ">
                <h1><span><?php wcy_the_get_title(); ?></span></h1></div>
            <div class="col-xl-6">
                <div class="search-form  row justify-content-end align-items-center ">
                    <div class="col-xl-4 text-end">
						<?php if ( WC_Product_Vendors_Utils::is_admin_vendor( get_current_user_id() ) ): ?>
                            <a class="btn btn-light btn-small" href="/store/wp-admin">商家后台</a>
						<?php else: ?>
                            <a class="btn btn-light btn-small" href="/store/vendor-registration">申请入驻</a>
						<?php endif; ?>
                    </div>
                    <form class="col-xl-8" method="get" action="/search">
                        <input placeholder="搜索插件..." name="keyword" type="search" value="" id="projects-filter"
                               class="filter-search"
                        >
                        <input type="hidden" name="tag_id" value="3"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
<section class="breadcrumb">
    <div class="container">
		<?php
		woocommerce_breadcrumb( array(
			'delimiter'   => '&nbsp;&#187;&nbsp;',
			'wrap_before' => '',
			'wrap_after'  => '',
			'before'      => '',
			'after'       => '',
			'home'        => _x( 'Home', 'breadcrumb', 'woocommerce' ),
		) );
		?>
    </div>
</section>