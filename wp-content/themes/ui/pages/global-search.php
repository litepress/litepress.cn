<?php
/**
 * Template name: 全局搜索模板
 * Description: 该模板提供一个可以搜索全平台内容的搜索引擎
 */

get_header();

// 定义全局变量
$none_result = false; // 搜索结果为空
$is_error    = false; // 执行搜索时遭遇异常


// 获取查询数据
$keyword = sanitize_text_field( $_GET['keyword'] ?? '' );
// 搜索关键词为空的时候直接返回一个只有搜索框的页面
if ( empty( $keyword ) ) {
	?>


    <main class="wp-body d-flex">
        <div class="container ">
            <nav class="row">
                <section class="mt-11 text-center">

                    <section class=" "><img src="wp-content/themes/ui/assets/img/logo.svg"
                                            alt="LitePress 社区"
                                            width="200">
                        <p class="my-3">通过我们的搜索引擎,你可以在全平台检索内容</p>
                    </section>

                    <form style="max-width: 640px" class="m-auto header-search" method="get" action="/search">
                        <div class="position-relative">
                            <input type="text" name="keyword" class="form-control" placeholder="搜索……">
                            <button type="submit">
                                <div class="actions"><i class="fad fa-search"></i>
                                </div>
                            </button>
                        </div>

                    </form>


                </section>
        </div>
        </div>
    </main>


	<?php

	get_footer();
	exit;
}

// 获取要查询的站点
$tag_id = (int) sanitize_text_field( $_GET['tag_id'] ?? 0 );

// 获取当前页数
$paged = (int) sanitize_text_field( $_GET['paged'] ?? 1 );
// 不允许查看大于 10 页的结果，正常人也不会翻这么多页
$paged = $paged < 10 ? $paged : 10;
// 不允许检索负数的分页
$paged = $paged > 1 ? $paged : 1;


$sites = array(
	1  => array(
		'slug' => '/',
		'name' => '论坛',
	),
	3  => array(
		'slug' => 'store',
		'name' => '市场'
	),
	4  => array(
		'slug' => 'translate',
		'name' => '翻译'
	),
	6  => array(
		'slug' => 'manual',
		'name' => '手册'
	),
	11 => array(
		'slug' => 'support',
		'name' => '文档'
	),
	14 => array(
		'slug' => 'news',
		'name' => '博客'
	),
);


$args = array(
	's'         => $keyword,
	'post_type' => array( 'post', 'product', 'topic', 'reply', 'docs' ),
	'paged'     => $paged,
);

if ( ! empty( $tag_id ) && key_exists( $tag_id, $sites ) ) {
	$args['sites'] = $tag_id;
} else {
	$args['sites'] = 'all';
}

$start_time = microtime( true );
$wp_query   = new WP_Query( $args );
$end_time   = microtime( true );
$query_time = sprintf( "%.3f", $end_time - $start_time );

?>


    <header class="search-banner banner">
        <div class="container">
            <div class="row align-items-center project-row ">

                <h5 class="text-center"><span>在 LitePress.cn 中搜索</span></h5>
                <p>通过我们的搜索引擎,你可以在全平台检索内容
                </p>

                <div class="search-form row justify-content-center m-auto header-search mt-3">
                    <form style="max-width: 640px" class="m-auto header-search" method="get" action="/search">
                        <div class="position-relative">
                            <input type="text" name="keyword" class="form-control" placeholder="搜索……">
                            <button type="submit">
                                <div class="actions"><i class="fad fa-search"></i>

                                </div>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </header>
    <main class="wp-body">
        <div class="container">
            <nav class="row">


                <section class="theme-boxshadow bg-white px-3 pb-2">
                    <ul class="forum_menu">
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2143 <?php echo 0 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a href="<?php echo remove_query_arg( array( 'tag_id', 'paged' ) ); ?>" aria-current="page"
                               class="nav-link">聚合</a>
                        </li>
                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2150 <?php echo 1 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a
                                    href="<?php echo add_query_arg( array( 'tag_id' => 1 ), remove_query_arg( 'paged' ) ) ?>"
                                    class="nav-link">论坛</a>
                        </li>
                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2149 <?php echo 14 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a
                                    href="<?php echo add_query_arg( array( 'tag_id' => 14 ), remove_query_arg( 'paged' ) ) ?>"
                                    class="nav-link">博客</a></li>
                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2148 <?php echo 3 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a
                                    href="<?php echo add_query_arg( array( 'tag_id' => 3 ), remove_query_arg( 'paged' ) ) ?>"
                                    class="nav-link">市场</a></li>
                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-19918 <?php echo 11 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a
                                    href="<?php echo add_query_arg( array( 'tag_id' => 11 ), remove_query_arg( 'paged' ) ) ?>"
                                    class="nav-link">文档</a></li>
                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-manual <?php echo 6 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a
                                    href="<?php echo add_query_arg( array( 'tag_id' => 6 ), remove_query_arg( 'paged' ) ) ?>"
                                    class="nav-link">手册</a></li>
                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-translate <?php echo 4 === (int) $tag_id ? 'current-menu-item' : ''; ?>">
                            <a
                                    href="<?php /*echo add_query_arg( array( 'tag_id' => 4 ), remove_query_arg( 'paged' ) ) */?>"
                                    class="nav-link">翻译</a></li>
                    </ul>

                    <article class="px-2 mt-1">
                        <small class="result-info "><!--输出搜索元信息-->
                            本次查询耗时 <?php echo $query_time ?> 秒，共检索到 <?php echo $wp_query->found_posts ?> 个项目</small>
                        <hr class="dropdown-divider ">
                        <!--输出搜索结果-->
						<?php if ( ! empty( (int) $wp_query->found_posts ) ): ?>
							<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
								<?php
								// 为了获取到详细内容，需要把当前站点 ID 切换为数据来源站点
								if ( isset( $wp_query->post->site_id ) && ! empty( $wp_query->post->site_id ) ) {
									switch_to_blog( $wp_query->post->site_id );
								}
								?>

                                <li class="py-3 d-flex align-items-center result-list">

                                    <div class="result-body ms-2">
                                        <header class="mb-2 d-flex  align-items-center">
                                            <!--输出当前结果属于哪个站点-->
											<?php $site = $sites[ $wp_query->post->site_id ]['name'] ?? '' ?>
											<?php if ( ! empty( $site ) ): ?>
                                                <span class="badge bg-primary"><?php echo $site ?></span>
											<?php endif; ?>
                                            <!--输出标题及超链接-->
											<?php
											// 论坛帖子回复的超链接需要特别适配一下
											$permalink = get_the_permalink();

											if ( 'reply' === $wp_query->post->post_type ) {
												// 获取该回复所属的帖子
												$parent_permalink = get_permalink( $wp_query->post->post_parent );
												if ( ! empty( $parent_permalink ) ) {
													$permalink = "{$parent_permalink}#post-{$wp_query->post->ID}";
												}
											}
											?>
                                            <a class="title ms-2" href="<?php echo $permalink; ?>"
                                               target="_blank"><?php the_title() ?></a>
                                        </header>
                                        <!--输出详情-->
                                        <article class="d-flex align-items-center">
                                            <aside class="thumbnail me-2">
                                                <!--输出缩略图-->
												<?php the_post_thumbnail(); ?>
                                            </aside>
                                            <small><p><?php the_excerpt() ?></p></small>
                                        </article>
                                    </div>

                                </li>
							<?php endwhile; ?>

							<?php
							// 执行完文章循环后需要切换回当前站点
							restore_current_blog();
							?>
						<?php else: ?>
                            <div class="alert alert-warning mb-2">当前未检索到任何内容哦，换个关键词试一下吧！</div>
						<?php endif; ?>
                        <nav aria-label="Page navigation " class="py-3">
                            <ul class="pagination">
                                <!--输出上一页按钮-->
								<?php if ( $paged > 1 ): ?>

                                    <a class="page-link"
                                       href="<?php echo add_query_arg( array( 'paged' => $paged - 1 ) ) ?>">上一页</a>
								<?php endif; ?>

                                <!--输出分页按钮-->

								<?php
								// 获取总页数
								$page_count = ceil( $wp_query->found_posts / 10 );
								$page_count = $page_count < 10 ? $page_count : 10;
								?>
								<?php
								// 循环输出分页按钮
								for ( $i = 1; $i <= $page_count; $i ++ ):?>
                                    <li class="page-item <?php echo (int) $paged === (int) $i ? 'active' : ''; ?>">
                                        <a class="page-link"
                                           href="<?php echo add_query_arg( array( 'paged' => $i ) ) ?>"><?php echo $i ?></a>
                                    </li>
								<?php endfor; ?>


                                <!--输出下一页按钮-->
								<?php if ( $paged < $page_count ): ?>
                                    <li class="page-item"><a class="page-link"
                                                             href="<?php echo add_query_arg( array( 'paged' => $paged + 1 ) ) ?>">下一页</a>
                                    </li>
								<?php endif; ?>
                            </ul>
                        </nav>
                    </article>
                </section>
        </div>
        </div>
    </main>


<?php
get_footer();
