<?php
/**
 * Template name: 社区首页模板
 * Description: 该模板是社区首页模板
 */

get_header();
?>
    <main class="wp-body">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <section class="theme-boxshadow wp-banner bg-white">
                        <h2 class="m-4 text-center">
                            <span class="heading-text">LitePress是首个中国本土的WordPress发行版</span>
                        </h2>
                        <p class="heading-p">其诞生的目的是解决WordPress在中国高墙环境下水土不服的问题，以及适应中国特有的微信互联网。<br/>你可以选择直接安装LitePress发行版，或通过安装WP-China-Yes插件一键在WordPress上对接LitePress生态体系
                        </p>
                        <div class="text-center pb-5">
                            <div class="btn-group uabb-dual-button" role="group" aria-label="Basic example">
                                <a href="https://a1.wp-china-yes.net/apps/wp-china-yes.zip">
                                    <button type="button" class="btn btn-primary align-items-center uabb-btn-one "><i
                                                class="fa fa-plug" aria-hidden="true"></i>
                                        WP-China-Yes 插件下载<span class="version">V3.5.0</span>
                                    </button>
                                    <span class="middle-text">					or				</span>
                                </a>

                                <a href="https://a1.wp-china-yes.net/apps/wordpress.zip">
                                    <button type="button"
                                            class="btn btn-primary align-items-center uabb-btn-two tooltip-show"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="LitePress将于未来数月内上线，在此期间我们为你准备了最新的WordPress安装包，其中内置WP-China-Yes插件"><i
                                                class="fab fa-wordpress"></i>WordPress 最新版下载<span class="version">V5.8.0</span>
                                    </button>
                                </a>
                            </div>
                        </div>
                    </section>

                    <section class="theme-boxshadow bg-white mt-4 index-forum heading-xxs wp-notice">
                        <i class="fas fa-bullhorn"></i> 领取你的专属技术顾问吧！任何和LitePress及WordPress相关的问题都可以直接在此发帖提问，我们会在工作日的一小时内给出可行方案！
                    </section>

                    <section class="theme-boxshadow bg-white mt-4 index-forum">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'forum_menu',
								'container'      => false,
								'items_wrap'     => '<ul class="forum_%2$s">%3$s</ul>',
								'fallback_cb'    => false,
								'walker'         => new WCY_Sub_Menu(),
							)
						);
						?>
                        <hr class="dropdown-divider forum-menu-divider">
						<?php the_content(); ?>
                    </section>
                </div>
				<?php get_sidebar(); ?>
            </div>
        </div>
    </main>

<?php
get_footer();
