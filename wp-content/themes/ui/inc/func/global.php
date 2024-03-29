<?php

global $blog_id;

/**
 * 注册小工具区域
 */
register_sidebar( array(
	'name'          => 'UI 侧边栏',
	'id'            => 'ui-sidebar',
	'description'   => '为 UI 主题添加侧边栏',
	'before_widget' => '<li>',
	'after_widget'  => '</li>',
	'before_title'  => '<h2>',
	'after_title'   => '</h2>',
) );



add_filter('request', function( $vars ) {
	    global $wpdb;
	        if( ! empty( $vars['pagename'] ) || ! empty( $vars['category_name'] ) || ! empty( $vars['name'] ) || ! empty( $vars['attachment'] ) ) {
			        $slug = ! empty( $vars['pagename'] ) ? $vars['pagename'] : ( ! empty( $vars['name'] ) ? $vars['name'] : ( !empty( $vars['category_name'] ) ? $vars['category_name'] : $vars['attachment'] ) );
				        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT t.term_id FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'product_cat' AND t.slug = %s" ,array( $slug )));
				        if( $exists ){
						            $old_vars = $vars;
							                $vars = array('product_cat' => $slug );
							                if ( !empty( $old_vars['paged'] ) || !empty( $old_vars['page'] ) )
										                $vars['paged'] = ! empty( $old_vars['paged'] ) ? $old_vars['paged'] : $old_vars['page'];
									            if ( !empty( $old_vars['orderby'] ) )
											                        $vars['orderby'] = $old_vars['orderby'];
										                    if ( !empty( $old_vars['order'] ) )
													                        $vars['order'] = $old_vars['order'];    
												            }
    }
    return $vars;
    });
/**
 * 格式化作品已安装数量
 *
 * 对应小于10000的作品直接返回原数字接一个加号，对于大于等于10000的，转换为汉字显示。
 *
 * @param int $num
 *
 * @return string
 */
function wcy_prepare_installed_num( int $num ): string {
	$str    = $num;
	$length = strlen( $num );
	if ( $length > 8 ) { //亿单位
		$str = str_replace( '00000000', '', $num ) . '亿';
	} elseif ( $length > 4 ) { //万单位
		$str = str_replace( '0000', '', $num ) . '万';
	}

	return $str . '+';
}

/*支持 SVG 上传*/
function wp_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}

add_filter( 'upload_mimes', 'wp_mime_types' );


// 注册logo
add_theme_support( 'custom-logo' );

//添加自定义设置选项
function lpcn_customize_register( $wp_customize ) {
//给相关设置项目加小铅笔
	if ( isset( $wp_customize->selective_refresh ) ) {
//One
		$wp_customize->selective_refresh->add_partial( 'lpcn_sections_text_one', array(
			'selector'        => '.1-1',
			'render_callback' => 'lifet_customize_partial_lpcn_sections_text_one',
		) );
//Two
		$wp_customize->selective_refresh->add_partial( 'lpcn_sections_text_two', array(
			'selector'        => '.1-2',
			'render_callback' => 'lifet_customize_partial_lpcn_sections_text_two',
		) );
	}
//-----------------------------------------------------------------------------
//添加主题设置面板，ID = lpcn_options
	$wp_customize->add_panel( 'lpcn_options',
		array(
			'title'       => __( '自定义设置', 'lpcn' ),
			'description' => __( '自定义设置', 'lpcn' ),
			'priority'    => 30,
			'capabitity'  => 'edit_theme_options',
		) );
//添加文本设置节，ID = lpcn_sections_text
	$wp_customize->add_section( 'lpcn_sections_text',
		array(
			'title'       => 'Logo',
			'description' => '主题的Logo设置',
			'panel'       => 'lpcn_options',
		) );
//文字设置:one，ID = lpcn_sections_logo_range
	$wp_customize->add_setting( 'lpcn_sections_logo_range',
		array(
			'default' => '200',
		)
	);
	$wp_customize->add_control( 'lpcn_sections_logo_range',
		array(
			'label'   => 'Logo宽度',
			'section' => 'lpcn_sections_text',
			'type'    => 'text',
		)
	);
}

add_action( 'customize_register', 'lpcn_customize_register' );


// 在编辑器中启用字体和字体大小选择
if ( ! function_exists( 'lpcn_mce_buttons' ) ) {
	function lpcn_mce_buttons( $buttons ) {
		array_unshift( $buttons, 'fontselect' ); // 添加字体选择
		array_unshift( $buttons, 'fontsizeselect' ); // 添加字体大小选择

		return $buttons;
	}
}
add_filter( 'mce_buttons_2', 'lpcn_mce_buttons' );
// 自定义编辑器
if ( ! function_exists( 'lpcn_mce' ) ) {
	function lpcn_mce( $initArray ) {
		$initArray['fontsize_formats'] = "9px 10px 12px 13px 14px 16px 18px 21px 24px 28px 32px 36px";
		$initArray['font_formats']     = "微软雅黑=Microsoft YaHei,Helvetica Neue,PingFang SC,sans-serif;苹果苹方=PingFang SC,Microsoft YaHei,sans-serif;宋体=simsun,serif;仿宋体=FangSong,serif;黑体=SimHei,sans-serif;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats;知乎配置=BlinkMacSystemFont, Helvetica Neue, PingFang SC, Microsoft YaHei, Source Han Sans SC, Noto Sans CJK SC, WenQuanYi Micro Hei, sans-serif;小米配置=Helvetica Neue,Helvetica,Arial,Microsoft Yahei,Hiragino Sans GB,Heiti SC,WenQuanYi Micro Hei,sans-serif";
		$initArray['setup ']           = "function(ed)
            {
                ed.on('init', function() 
                {
                    this.getDoc().body.style.fontSize = '14px';
                    this.getDoc().body.style.fontFamily = '微软雅黑'
                })
            }";

		return $initArray;
	}
}
add_filter( 'tiny_mce_before_init', 'lpcn_mce' );



/*
?* 评论列表的显示
?*/
if ( ! function_exists( 'bootstrapwp_comment' ) ) :
	function bootstrapwp_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
				// 用不同于其它评论的方式显示 trackbacks 。
				?>
                <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
                <p><?php _e( 'Pingback:', 'bootstrapwp' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'bootstrapwp' ), '<span class="edit-link">', '</span>' ); ?>
                </p>
				<?php
				break;
			default :
				// 开始正常的评论
				global $post;
				?>
            <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
                <article id="comment-<?php comment_ID(); ?>" class="media comment">
                    <aside class="wp-author-avatar">
						<?php // 显示评论作者头像
						echo get_avatar( $comment, 64 );
						?>
                    </aside>
					<?php // 未审核的评论显示一行提示文字
					if ( '0' == $comment->comment_approved ) : ?>
                        <p class="comment-awaiting-moderation">
							<?php _e( 'Your comment is awaiting moderation.', 'bootstrapwp' ); ?>
                        </p>
					<?php endif; ?>
                    <article class="wp-reply-article">
                        <h4 class="wp-reply-author ">
                            <cite class="fn">
								<?php // 显示评论作者名称
								printf( '%1$s %2$s',
									get_comment_author_link(),
									// 如果当前文章的作者也是这个评论的作者，那么会出现一个标签提示。
									( $comment->user_id === $post->post_author ) ? '<span class="label label-info"> ' . __( '作者', 'bootstrapwp' ) . '</span>' : ''
								);
								?></cite>
                            <small>
								<?php // 显示评论的发布时间
								printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									// 翻译: 1: 日期, 2: 时间
									sprintf( __( '%1$s %2$s', 'fenikso' ), get_comment_date(), get_comment_time() )
								);
								?>
                            </small>
                        </h4>
                        <div class="wp-reply-content heti">
							<?php // 显示评论内容
							comment_text();
							?></div>
                        <footer class="wp-reply-meta">
                            <ul class="nav">
							<?php // 显示评论的编辑链接
							edit_comment_link( __( '编辑', 'bootstrapwp' ), '<li class="edit-link">', '</li>' );
							?>
                            <li class="reply">
								<?php // 显示评论的回复链接
								comment_reply_link( array_merge( $args, array(
									'reply_text' => __( '回复', 'bootstrapwp' ),
									'after'      => ' <span></span>',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth']
								) ) );
								?>
                            </li>
                            <?php if ( current_user_can('level_10') ) {
                            $url = home_url();
                            echo '<li><a id="delete-'. $comment->comment_ID .'" href="' . wp_nonce_url("$url/wp-admin/comment.php?action=deletecomment&p=" . $comment->comment_post_ID . '&c=' . $comment->comment_ID, 'delete-comment_' . $comment->comment_ID) . '" >删除</a></li>';
                            }
                            ?>
                            </ul>
                        </footer>
                    </article>
                </article>
				<?php
				break;
		endswitch; // end comment_type check
	}
endif;


/**
 * 供应商入驻表单申请提交成功后在用户的user meta中更新上当前激活的供应商ID，否则的话这个鸟插件会在用户登录供应商后台时遍历所有的供应商term来
 * 确定哪个是该用户管理的……当用户量大了之后这种骚操作会导致页面加载时间飞到一分多钟
 */
add_action( 'wcpv_shortcode_registration_form_process', function ( $args, $term ) {
	WC_Product_Vendors_Utils::set_user_active_vendor( get_term_by( 'name', $term['vendor_name'], WC_PRODUCT_VENDORS_TAXONOMY, ARRAY_A )['term_id'] );
}, 10, 2 );

function wcy_is_plugin_list(): bool {
	global $wp;

	return stristr( $_SERVER['REQUEST_URI'], '/plugins' ) || ( ( isset( $wp->query_vars['wcpv_product_vendors'] ) || is_product_tag() ) && ( 'plugins' === ( $_GET['category'] ?? '' ) || empty( ( $_GET['category'] ?? '' ) ) ) );
}

function wcy_is_theme_list(): bool {
	global $wp;

	return stristr( $_SERVER['REQUEST_URI'], '/themes' ) || ( ( isset( $wp->query_vars['wcpv_product_vendors'] ) || is_product_tag() ) && 'themes' === ( $_GET['category'] ?? '' ) );
}

/**
 * 获取当前页面标题
 */
function wcy_the_get_title() {
	$title = '';
	if ( is_singular() ) {
		$title = get_the_title();
	} else {
		$title = single_cat_title();
	}

	echo $title;
}

/**
 * 去除已登录会员的顶部工具条
 */
if ( ! current_user_can( 'manage_options' ) ) {
	add_filter( 'show_admin_bar', '__return_false' );
}

/**
 * 替换终极会员插件的gravatar头像地址
 */
add_filter( 'um_user_avatar_url_filter', function ( $url, $user_id ) {
	$user = get_user_by( 'ID', $user_id );

	// 邮箱转小写并去除首尾空格
	$address = strtolower( trim( $user->user_email ) );

	// 获取邮箱的MD5哈希值
	$hash = md5( $address );

	// 拼接出最终的头像URL
	return 'https://cravatar.cn/avatar/' . $hash . '?s=200&d=mp&test=1';
}, 99999, 2 );

/**
 * bbpress可视化编辑
 */
add_filter( 'bbp_after_get_the_content_parse_args', function ( $args ) {
	$args['tinymce']       = true;
	$args['teeny']         = false;
	$args['quicktags']     = true;
	$args['media_buttons'] = false;
	$args['tabindex']      = true;
	$args['dfw']           = true;

	return $args;
}, 99 );

/**
 * 禁用帖子快速编辑功能
 */
if ( 3 === (int) $blog_id ) {
	function remove_quick_edit( $actions ) {
		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}

	add_filter( 'post_row_actions', 'remove_quick_edit', 10, 1 );
}

/**
 * 去掉文档平台内容页的docs前缀
 */
add_filter( 'wedocs_post_type', function ( $args ) {
	$args['rewrite']['slug']       = 'archives';
	$args['rewrite']['with_front'] = false;

	return $args;
}, 9999 );

/**
 * 重定义GlotPress的模板路径
 */
add_filter( 'gp_tmpl_load_locations', function ( $locations, $template, $args, $template_path ) {
	/**
	 * 如果请求的模板路径中第一个数组元素不是 GlotPress 插件自身的，则不替换模板路径
	 */
	if ( ! str_contains( $locations[0] ?? '', 'plugins/glotpress' ) ) {
		return $locations;
	}

	return [
		WP_CONTENT_DIR . '/themes/ui/glotpress/'
	];
}, 10, 4 );

/**
 * 为Woo的API返回增加字段
 */
//add_action( 'rest_api_init', function () {
//	/**
//	 * 供应商字段
//	 */
//	register_rest_field( 'product', 'vendor', array(
//		'get_callback' => function ( $product ) {
//			$vendor = WC_Product_Vendors_Utils::get_vendor_data_by_id( WC_Product_Vendors_Utils::get_vendor_id_from_product( $product['id'] ) );
//
//			$allowed = array( 'name', 'slug' );
//
//			foreach ( (array) $vendor as $key => $value ) {
//				if ( ! in_array( $key, $allowed ) ) {
//					unset( $vendor[ $key ] );
//				}
//			}
//
//			return $vendor;
//		},
//	) );
//
//	/**
//	 * 缩略图字段
//	 */
//	register_rest_field( 'product', 'thumbnail_src', array(
//		'get_callback' => function ( $product ) {
//			$medium_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product['id'] ), 'medium' );
//
//			return $medium_image_url[0] ?? 'https://avatar.ibadboy.net/avatar/' . md5( rand() ) . '?d=identicon&s=256';
//		},
//	) );
//
//	/**
//	 * Woo默认的傻逼Meta Data惊得我下巴都掉了，非常不便于索引，于是这里重新搞个meta字段
//	 */
//	register_rest_field( 'product', 'meta', array(
//		'get_callback' => function ( $product ) {
//			$meta = get_post_meta( $product['id'] );
//
//			foreach ( $meta as $key => $value ) {
//				$meta[ $key ] = $value[0];
//			}
//
//			return $meta;
//		},
//	) );
//
//	/**
//	 * 为Woo增加评论内容
//	 */
//	register_rest_field( 'product', 'reviews', array(
//		'get_callback' => function ( $product ) {
//			$args             = array(
//				'status'      => 'approve',
//				'post_status' => 'publish',
//				'post_id'     => $product['id'],
//			);
//			$comments         = get_comments( $args );
//			$comments_section = '';
//
//			foreach ( $comments as $comment ) {
//				/**
//				 * 不知道哪个鸟插件拦截修改了wp的get_avatar方法，导致修改不了图像大小，他妈的，只能自己手工拼接了
//				 */
//				$args         = array(
//					'size' => 12,
//				);
//				$gravatar_url = get_avatar_url( $comment->comment_author_email, $args );
//				$gravatar     = "<img src='$gravatar_url'>";
//				$star_rating  = '';
//
//				$rating_num     = (float) get_comment_meta( $comment->comment_ID, 'rating', true ) ?: 0;
//				$rating_num_tmp = $rating_num;
//				for ( $i = 0; $i < 5; $i ++ ) {
//					if ( 0 < $rating_num_tmp && $rating_num_tmp < 1 ) {
//						$star_rating .= '<span class="star dashicons dashicons-star-half"></span>';
//					} elseif ( $rating_num_tmp >= 1 ) {
//						$star_rating .= '<span class="star dashicons dashicons-star-filled"></span>';
//					} else {
//						$star_rating .= '<span class="star dashicons dashicons-star-empty"></span>';
//					}
//
//					$rating_num_tmp -= 1;
//				}
//
//				$comments_section .= <<<html
//<div class="review">
//    <div class="review-head">
//        <div class="reviewer-info">
//            <div class="review-title-section">
//                <div class="star-rating">
//                    <div class="wporg-ratings" aria-label="{$rating_num}星（最高5星）" data-title-template="%s星（最高5星）" data-rating="5" style="color:#ffb900;">
//                      {$star_rating}
//                </div>
//            </div>
//            <p class="reviewer">
//                由 {$gravatar} {$comment->comment_author} 发表于<span class="review-date">{$comment->comment_date}</span>
//            </p>
//        </div>
//    </div>
//    <div class="review-body">
//        {$comment->comment_content}
//    </div>
//</div>
//html;
//			}
//
//			return $comments_section;
//		},
//	) );
//
//	/**
//	 * 为订单增加支付URL
//	 */
//	register_rest_field( 'shop_order', 'pay_url', array(
//		'get_callback' => function ( $order ) {
//			$pay = new Xunhu_Wechat();
//
//			return $pay->woocommerce_receipt( $order['id'] );
//		},
//	) );
//
//	/**
//	 * 注册订单支付状况监控API
//	 */
//	register_rest_route( 'lp-api/v1', '/woo/orders/is_paid', array(
//		'methods'             => WP_REST_Server::READABLE,
//		'callback'            => function () {
//			$pay = new Xunhu_Wechat();
//
//			$pay->wechat_order_is_paid();
//		},
//		'permission_callback' => '__return_true'
//	) );
//} );

/**
 * 新需求默认发布.
 *
 * @return string
 */
function jck_sfr_change_default_post_status() {
	return 'publish';
}

add_filter( 'jck_sfr_get_default_post_status', 'jck_sfr_change_default_post_status', 10 );

/**
 * BBPress关闭垃圾邮件拦截
 */
add_filter( 'bbp_bypass_check_for_moderation', function () {
	return true;
} );

/**
 * 修改供应商详情的slug，如果不强制改成product-vendors的话这个傻逼插件的slug会在product-vendors和vendor之间来回跳，超级迷惑
 */
add_filter( 'wcpv_vendor_slug', function () {
	return 'product-vendors';
} );

/**
 * 移除Woo的面包屑
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/**
 * 在首页和产品页面、分类页面、标签页面上禁用Woo的购物车检查
 */
if ( 3 === (int) $blog_id ) {
	add_action( 'wp_enqueue_scripts', 'dequeue_woocommerce_cart_fragments', 11 );
	function dequeue_woocommerce_cart_fragments() {
		if ( is_front_page() || is_product() || is_product_tag() || is_product_tag() ) {
			wp_dequeue_script( 'wc-cart-fragments' );
		}
	}
}

/**
 * 恢复自WP 4.7以来被删除的Rest Api filter参数
 */
add_filter( 'woocommerce_rest_product_object_query', function ( $args, $request ) {
	if ( empty( $request['filter'] ) || ! is_array( $request['filter'] ) ) {
		return $args;
	}

	$filter = $request['filter'];

	if ( isset( $filter['posts_per_page'] ) && ( (int) $filter['posts_per_page'] >= 1 && (int) $filter['posts_per_page'] <= 100 ) ) {
		$args['posts_per_page'] = $filter['posts_per_page'];
	}

	global $wp;
	$vars = apply_filters( 'rest_query_vars', $wp->public_query_vars );

	$vars = array_unique( array_merge( $vars, array( 'meta_query', 'meta_key', 'meta_value', 'meta_compare' ) ) );

	foreach ( $vars as $var ) {
		if ( isset( $filter[ $var ] ) ) {
			$args[ $var ] = $filter[ $var ];
		}
	}

	return $args;
}, 10, 2 );

/**
 * 通知未填写提现支付宝的商家填写之
 */
add_action( 'wp_after_admin_bar_render', function () {
	global $blog_id;

	if ( 3 !== (int) $blog_id ) {
		return;
	}
	if ( class_exists( WC_Product_Vendors_Utils::class ) ) {
		$vendor = WC_Product_Vendors_Utils::get_vendor_data_from_user();
		if ( ! empty( $vendor ) && 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			if ( ! isset( $vendor['paypal'] ) || empty( $vendor['paypal'] ) ) {
				echo '<div class="updated"><p>你还未填写支付宝提现信息哦，请前往 <a href="/store/wp-admin/admin.php?page=wcpv-vendor-settings">店铺设置</a> 填写</p></div>';
			}
		}
	}
} );

/**
 * wordpress上传文件重命名
 */
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	// 重命名不对zip压缩包生效，因为压缩包重命名后就不知道传的是啥了
	if ( stristr( $file['name'], '.zip' ) ) {
		return $file;
	}

	$time         = date( "YmdHis" );
	$file['name'] = $time . "" . mt_rand( 1, 100 ) . "." . pathinfo( $file['name'], PATHINFO_EXTENSION );

	return $file;
} );

/**
 * 取消EP最大索引10000数据的限制
 */
add_filter( 'ep_formatted_args', function ( $formatted_args ) {
	$formatted_args['track_total_hits'] = true;

	return $formatted_args;
} );

/**
 * 应用市场 ES 搜索时除了搜翻译后的中文外也允许搜英文原文
 */
add_filter( 'ep_weighting_configuration_for_search', function ( $weight_config, $args ): array {
	if ( key_exists( 'product', $weight_config ) ) {
		$weight_config['product']['post_title_en'] = array(
			'weight'  => 100,
			'enabled' => true,
		);
	}

	return $weight_config;
}, 10, 2 );

/**
 * 过滤允许的古腾堡区块
 */
add_filter( 'gutenberg_everywhere_allowed_blocks', function ( $allowed, $editor_type ) {

	return null;
}, 10, 2 );

/**
 * 为古腾堡提个一个上传图片接口
 */
function upload_image(): WP_REST_Response {

	require_once ABSPATH . "wp-admin" . '/includes/image.php';
	require_once ABSPATH . "wp-admin" . '/includes/file.php';
	require_once ABSPATH . "wp-admin" . '/includes/media.php';

	if ( ! $_FILES["upload_img_file"] ) {
		$output = array(
			'errno'   => 1,
			'message' => '非法请求',
		);

		return new WP_REST_Response( $output );
	}
	$attach_id = media_handle_upload( 'upload_img_file', 0 );
	$image_url = wp_get_attachment_image_src( $attach_id, 'full' )[0];
	$output    = array(
		'errno'   => 0,
		'data'    => array(
			'url' => $image_url,
		),
	);

	return new WP_REST_Response( $output );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'upload_image/v1', '/upload', array(
		'methods'             => 'POST',
		'callback'            => 'upload_image',
		'permission_callback' => '__return_true',
	) );
} );
