<?php
/**
 * 替换终极会员插件的Gravatar头像为本土化社区托管的加速镜像
 */

use LitePress\Logger\Logger;
use WCY\Inc\BBPress\Walker_Reply;

global $blog_id;

/**
 * Woo删除多余菜单
 */
add_filter( 'woocommerce_account_menu_items', function ( $menu_links ) {
	unset( $menu_links['edit-address'] );

	unset( $menu_links['dashboard'] );
	unset( $menu_links['payment-methods'] );
	unset( $menu_links['downloads'] );
	unset( $menu_links['edit-account'] );
	unset( $menu_links['customer-logout'] );

	return $menu_links;
} );

/**
 * 对调Woo商品列表中筛选器部分筛选按钮和商品数量区块的位置
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 20 );
add_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 30 );

/**
 * 向Woo的商品列表页注入当前分类的子分类信息，该信息以json格式保存至一个js变量中
 */
add_action( 'woocommerce_before_main_content', function () {
	global $wp;
	$categories  = array();
	$category_id = 0;
	$is_taxonomy = false;

	if ( stristr( $_SERVER['REQUEST_URI'], '/plugins' ) ) {
		$category_id = 15;
	} elseif ( stristr( $_SERVER['REQUEST_URI'], '/themes' ) ) {
		$category_id = 17;
	} elseif ( stristr( $_SERVER['REQUEST_URI'], '/mini_programs' ) ) {
		$category_id = 18;
	} else {
		$is_taxonomy = true;
		$categories  = array(
			array(
				'term_id' => 15,
				'name'    => '插件',
				'slug'    => 'plugins',
			),
			array(
				'term_id' => 17,
				'name'    => '主题',
				'slug'    => 'themes',
			),
		);
	}

	if ( ! $is_taxonomy ) {
		$categories = get_categories( [
				'hide_empty' => false,
				'taxonomy'   => 'product_cat',
				'child_of'   => $category_id
			]
		);
	}

	add_action( 'wp_footer', function () use ( $is_taxonomy, $categories ) {
		echo '<script>';
		printf( 'let categories = %s;', json_encode( $categories, JSON_UNESCAPED_UNICODE ) );
		printf( 'let is_taxonomy = %s;', $is_taxonomy ? 'true' : 'false' );
		echo '</script>';
	} );

	return true;
} );

/**
 * 更改供应商名称显示格式
 */
add_action( 'wcy_product_vendor', function () {
	global $post;

	$sold_by = WC_Product_Vendors_Utils::get_sold_by_link( $post->ID );

	echo '<a href="' . esc_url( $sold_by['link'] ) . '" title="' . esc_attr( $sold_by['name'] ) . '">' . $sold_by['name'] . '</a>';

	return true;
} );

/**
 * 更改Woo产品的默认图
 */
add_filter( 'woocommerce_product_get_image', function ( $image ) {
	if ( strpos( $image, 'woocommerce-placeholder.png' ) ) {
		$image = '<img src="https://cravatar.cn/avatar/' . md5( rand() ) . '?d=identicon&s=256" class="attachment-128 size-128" alt="" loading="lazy">';
	}

	echo $image;
}, 999 );

/**
 * 为nav添加class
 */
add_filter( 'nav_menu_link_attributes', function ( $attr ) {
	$attr['class'] = 'nav-link';

	return $attr;
} );

/**
 * 修改bbpress话题详情的样式
 */
add_filter( 'bbp_get_single_topic_description', function ( $retstr, $r, $args ) {
	$forum_title = bbp_get_forum_title( bbp_get_topic_forum_id() );
	$topic_id    = bbp_get_topic_id();
	$reply_count = str_replace( '个回复', '', bbp_get_topic_replies_link( $topic_id ) );
	$views       = ( (array) get_post_meta( $topic_id, 'views' ) )[0] ?? 0;

	$create_time = get_the_time( 'Y-n-d H:i:s' );
	$last_time   = get_post_meta( $topic_id, '_bbp_last_active_time', true );

	$forum_link = bbp_get_forum_permalink( bbp_get_topic_forum_id() );
	echo <<<html
<p class="book-article-meta" style="margin-bottom: 10px;">
            <a href="{$forum_link}" class=""><i class="fas fa-folder " aria-hidden="true"></i> {$forum_title}
            </a>
            <span class="divider hide-on-mobile">/</span>
            <span class="text-mute"><i class="fas fa-eye"></i> {$views}</span>
            <span class="divider">/</span>
            <span class="text-mute"><i class="fas fa-comments"></i> {$reply_count}</span>
            <span class="divider">/</span>
            <a class="" >
                发布于 {$create_time}
            </a>
            <span class="divider">/</span>
            <a class="" >
				活跃于 {$last_time}
            </a>
        </p>
html;

}, 10, 3 );

/**
 * 重定义bbpress的回复列表的class
 */
function wcy_bbp_list_replies( $args = array() ) {
	// Get bbPress
	$bbp = bbpress();

	// Reset the reply depth
	$bbp->reply_query->reply_depth = 0;

	// In reply loop
	$bbp->reply_query->in_the_loop = true;

	// Parse arguments
	$r = bbp_parse_args( $args, array(
		'walker'       => new Walker_Reply(),
		'max_depth'    => bbp_thread_replies_depth(),
		'style'        => 'ul',
		'callback'     => null,
		'end_callback' => null,
		'page'         => 1,
		'per_page'     => - 1
	), 'list_replies' );

	//$bbp->reply_query->posts[0]->reply_to = 0;

	// Get replies to loop through in $_replies
	echo '<ul>' . $r['walker']->paged_walk( $bbp->reply_query->posts, $r['max_depth'], $r['page'], $r['per_page'], $r ) . '</ul>';

	$bbp->max_num_pages            = $r['walker']->max_pages;
	$bbp->reply_query->in_the_loop = false;
}

/**
 * 为楼中楼回复的回复增加回复目标
 */
add_filter( 'bbp_get_reply_content', function ( $content, $reply_id ) {
	$parent_id = bbp_get_reply_to( $reply_id );

	$author_url  = bbp_get_reply_author_url( $parent_id );
	$author_name = bbp_get_reply_author_display_name( $parent_id );

	if ( 0 !== $parent_id && 0 !== bbp_get_reply_to( $parent_id ) ) {
		return "回复<a href='{$author_url}'> @ {$author_name}</a>：" . $content;
	} else {
		return $content;
	}

}, 1, 2 );

/**
 * 给顶级回复增加名为comment-body的class
 */
add_filter( 'bbp_get_reply_class', function ( $post_classes, $reply_id, $classes ) {
	if ( 0 === bbp_get_reply_to( $reply_id ) ) {
		array_push( $post_classes, 'comment-body' );
	}

	return $post_classes;
}, 10, 3 );

/**
 * 重设bbpress的回复时间格式为2021-9-25 11:11:11
 */
add_filter( 'bbp_get_reply_post_date', function ( $result, $reply_id, $humanize, $gmt, $date, $time ) {
	return '回复于 ' . get_post_time( 'Y-m-d H:i:s', false, $reply_id );
}, 10, 6 );

/**
 * 将楼主的权限信息修改为楼主，而不显示wordpress用户权限名
 */
add_filter( 'bbp_get_reply_author_role', function ( $author_role, $r, $args ) {
	$nameplate = get_user_meta( bbp_get_reply_author_id( $args['reply_id'] ?? 0 ), 'nameplate', true );
	if ( ! stristr( $nameplate, 'nameplate' ) ) {
		$nameplate = '';
	}

	$reply_user_id = bbp_get_reply( $r['reply_id'] )->post_author ?? null;
	$topic_user_id = bbp_get_topic( bbp_get_topic_id() )->post_author ?? null;


	if ( $r['reply_id'] === bbp_get_topic_id() || $reply_user_id === $topic_user_id ) {
		$data = '<div class="bbp-author-role">楼主</div>';
	} else {
		$data = $author_role;
	}

	$data .= do_shortcode( sanitize_text_field( $nameplate ) );

	$data .= do_shortcode( '[translate_nameplate user_id="' . ( $reply_user_id ?: $topic_user_id ) . '"]' );

	return $data;
}, 10, 3 );

/**
 * 更改Woo商城的商品列数
 */
add_filter( 'loop_shop_columns', function () {
	global $woocommerce;

	// Default Value also used for categories and sub_categories
	$columns = 3;

	// 如果当前处于主题列表，则更改为4列，因为3列的话会导致主题图片过大
	if ( wcy_is_theme_list() ) :
		$columns = 4;
	endif;

	//Related Products
	if ( is_product() ) :
		$columns = 2;
	endif;

	//Cross Sells
	if ( is_checkout() ) :
		$columns = 3;
	endif;

	/**
	 * 如果当前使用手机访问，就返回一列
	 **/
	//if ( wp_is_mobile() ) {
	//	$columns = 1;
	//}

	return $columns;
}, 999 );

/**
 * 更改Woo每页产品数量
 */
add_filter( 'loop_shop_per_page', function () {
	return 12;
}, 9999 );

/**
 * 让供应商作品页面以及标签产品列表页面支持选择分类并默认使用插件分类
 */
add_filter( 'woocommerce_product_query_tax_query', function ( $tax_query ) {
	global $wp;

	if ( ! isset( $wp->query_vars['wcpv_product_vendors'] ) && ! is_product_tag() ) {
		return $tax_query;
	}

	$terms = array( 'themes' );
	if ( 'themes' === ( $_GET['category'] ?? '' ) ) {
		$terms = array( 'plugins' );
	}

	$taxonomy = 'product_cat';

	$tax_query[] = array(
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $terms,
		'operator' => 'NOT IN',
	);

	return $tax_query;
}, 999 );

/**
 * 向购物车添加商品前先清空购物车
 */
function empty_cart_before_add_to_cart() {
	global $woocommerce;
	$woocommerce->cart->empty_cart();
}

add_action( 'woocommerce_add_to_cart_handler', 'empty_cart_before_add_to_cart' );

/**
 * 删除结算页面的“成功添加到购物车”消息
 */
add_filter( 'wc_add_to_cart_message_html', '__return_null' );

/**
 * 控制用户访问商家入驻界面的条件（必须登录 and 必须实名）
 */
if ( stristr( $_SERVER['REQUEST_URI'], '/vendor-registration' ) ) {
	if ( ! is_user_logged_in() ) {
		add_filter( 'the_content', function ( $content ) {
			return '<p class="woocommerce-message">您必须先 <a href="/login">登录</a></p>';
		}, 9999 );
	}

	$name = get_user_meta( get_current_user_id(), 'wprpv_real_name', true );
	if ( empty( $name ) ) {
		add_filter( 'the_content', function ( $content ) {
			return '<p class="woocommerce-message">您必须先 <a href="/real-person-verify">实名认证</a></p>';
		}, 9998 );
	}
}

/**
 * 添加百度统计代码
 */
add_action( 'wp_footer', function () {
	echo <<<html
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?b09919b0ba91f0ea1f1f6d62c3c78a1f";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
html;
} );

/**
 * 删掉bbpress帖子”回复“按钮上的click事件，防止点击回复后帖子刷新
 */
add_filter( 'bbp_get_reply_to_link', function ( $retval, $r, $args ) {
	if ( bbp_thread_replies() ) {
		$reply     = bbp_get_reply( $r['id'] );
		$move_form = array(
			$r['add_below'] . '-' . $reply->ID,
			$reply->ID,
			$r['respond_id'],
			$reply->post_parent
		);
		$onclick   = ' onclick="return addReply.moveForm(\'' . implode( "','", $move_form ) . '\');"';
	} else {
		$onclick = '';
	}

	return $r['link_before'] . '<a role="button" class="bbp-reply-to-link"' . $onclick . '>' . $r['reply_text'] . '</a>' . $r['link_after'];
}, 9999, 3 );

/**
 * 重新拼接bbpress帖子列表页面的最后活跃时间
 */
add_filter( 'bbp_get_topic_freshness_link', function ( $anchor, $topic_id, $time_since, $link_url, $title ) {
	$time_since = get_post_meta( $topic_id, '_bbp_last_active_time', true );

	if ( ! empty( $time_since ) ) {
		return '<a href="' . esc_url( $link_url ) . '" title="' . esc_attr( $title ) . '">' . $time_since . '</a>';
	} else {
		return esc_html__( 'No Replies', 'bbpress' );
	}
}, 10, 5 );





/*删除取消回复*/
function remove_comment_reply_link($link) {
    return '';
}
add_filter('cancel_comment_reply_link', 'remove_comment_reply_link', 10);
// Add the comment reply button to the end of the comment form.
// Remove the my_remove_comment_reply_link filter first so that it will actually output something.
function after_comment_form($post_id) {
    remove_filter('cancel_comment_reply_link', 'remove_comment_reply_link', 10);
    echo "<div class='btn mt-2 ms-2 btn-outline-primary cancel-comment-reply-link'><i class=\"fa-duotone fa-xmark\"></i> ";
    cancel_comment_reply_link('取消回复');
    echo "</div>";
}
add_action('comment_form_submit_field', 'after_comment_form', 99);
/**
 * 身份铭牌支持
 */
add_shortcode( 'nameplate', function ( $attr, $content = '' ) {
	$excludes       = array( '管理员', '超级管理员', 'admin', 'administrators', '站长', '所有者', '管理' );
	$excluded_blank = array( ' ', '　', '&nbsp;', '\n', '\r', '\t' );

	if ( ! isset( $attr['title'] ) || empty( $attr['title'] ) || ! isset( $attr['link'] ) || empty( $attr['link'] ) ) {
		return '';
	}

	// 防止被通过GBK等编码插入标题
	$attr['title'] = sanitize_file_name( $attr['title'] );

	// 防止XSS注入
	$attr['title'] = esc_html( $attr['title'] );
	$attr['link']  = esc_url_raw( $attr['link'] );

	$verify_data = strtolower( $attr['title'] );
	$verify_data = str_replace( $excluded_blank, '', $verify_data );

	if ( ! isset( $attr['title'] ) || empty( $attr['title'] ) ) { // 如果标题为空
		return "<div class='user-nameplate'><a>您未填写铭牌哦</a></div>";
	} elseif ( in_array( $verify_data, $excludes ) ) { // 如果标题使用了违禁词违禁词
		return "<div class='user-nameplate'><a>使用了违禁字符串</a></div>";
	} elseif ( preg_match( '/([\x{256}-\x{4e00}])|([\x{9fa5}-\x{ffff}])/u', $attr['title'], $match ) > 0 ) { // 垃圾UTF 8字符（非英文和中文编码范围）
		return "<div class='user-nameplate'><a>包含无效字符</a></div>";
	}

	return "<div class='user-nameplate'><a target='_blank' href='{$attr['link']}'>{$attr['title']}</a></div>";
} );

/**
 * 译者名牌展示
 */
add_shortcode( 'translate_nameplate', function ( $attr ) {
	global $wpdb;

	if ( ! isset( $attr['user_id'] ) || empty( $attr['user_id'] ) ) {
		return '';
	}
	$user_id = $attr['user_id'];

	$html = '';

	// 添加译者铭牌
	$translation_by_current_user = $wpdb->get_row( "select id from wp_4_gp_translations where user_id={$user_id} LIMIT 1;" );
	if ( ! empty( $translation_by_current_user ) ) {
		$html = "<div class='translate-contribution-nameplate'><i data-bs-toggle='tooltip' data-bs-placement='bottom' title='翻译贡献者' class='fal fa-language'></i></div>";
	}

	// 添加翻译编辑铭牌
	$translation_edit_by_current_user = $wpdb->get_row( "select id from wp_4_gp_permissions where user_id={$user_id} LIMIT 1;" );
	if ( ! empty( $translation_edit_by_current_user ) ) {
		$html = "<div class='translate-edit-nameplate'><i data-bs-toggle='tooltip' data-bs-placement='bottom' title='翻译编辑者' class='fad fa-language'></i></div>";
	}

	return $html;
} );

/**
 * 让目录模式部署的多站点的所有子站都从主站读取静态资源
 */
add_filter( 'set_url_scheme', function ( $url ) {
	global $blog_id;

	// 排除PHP文件
	if ( stristr( $url, '.php' ) ) {
		return $url;
	}

	if ( 0 !== (int) $blog_id && 1 !== (int) $blog_id ) {
		return preg_replace( '#([_0-9a-zA-Z-]+/)(wp-content|wp-includes)#', '$2', $url );
	}

	return $url;
}, 9999 );

/**
 * bbpress无需帖子ID调用内置简码
 */
add_shortcode( 'wcorg-bbp-single-topic', function () {
	return do_shortcode( '[bbp-single-topic id=' . get_the_ID() . ']' );
} );

add_shortcode( 'wcorg-bbp-single-forum', function () {
	return do_shortcode( '[bbp-single-forum id=' . get_the_ID() . ']' );
} );

add_shortcode( 'wcorg-bbp-topic-meta', function () {
	return do_shortcode( '[bbp-single-forum id=' . get_the_ID() . ']' );
} );

/**
 * Woo订单自动完成
 */
add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order' );
function custom_woocommerce_auto_complete_order( $order_id ) {
	if ( ! $order_id ) {
		return;
	}
	$order = wc_get_order( $order_id );
	$order->update_status( 'completed' );
}

/**
 * 中级会员插件提示上传头像需前往Cravatar
 */
add_action( 'um_after_header_info', function () {
	if ( ! isset( $_GET['um_action'] ) || 'edit' !== $_GET['um_action'] ) {
		return;
	}

	echo <<<HTML


<div class="popover fade show bs-popover-bottom" role="tooltip" data-popper-placement="bottom" style="top: 87px;left: -10px;">
<div class="popover-arrow" style="    left: 0;
    right: 0;
    margin: auto;"></div>
<div class="popover-body">如需更换头像，请前往 <a href="https://cravatar.cn">Cravatar</a></div>
</div>
HTML;
} );

/**
 * 如果用户在 URL 上拼接了 login_token 查询参数，则尝试解析 token 并使用其对应的用户来登录（如果已经登录其他用户则会切换为 token 对应的用户）
 */
add_action( 'wp_loaded', function () {
	if ( ! isset( $_GET['login_token'] ) || empty( $_GET['login_token'] ) ) {
		return;
	}
	$login_token = sanitize_text_field( $_GET['login_token'] );

	// 解析 token
	if ( ! class_exists( 'Jwt_Auth' ) || ! class_exists( 'Jwt_Auth_Public' ) ) {
		return;
	}
	$jwt        = new Jwt_Auth();
	$jwt_public = new Jwt_Auth_Public( $jwt->get_plugin_name(), $jwt->get_version() );

	$r = $jwt_public->validate_token( false, $login_token );
	if ( is_wp_error( $r ) ) {
		Logger::warning( 'Auth', '用户在网页端使用 login_token 登录时遇到了错误', array(
			'token' => $login_token,
			'error' => $r,
		) );

		return;
	}

	$user_id = (int) $r?->data?->user?->id;
	if ( empty( $user_id ) ) {
		return;
	}

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id );
} );

/**
 * 按当前子站点的 Slug 为标准去主题的 sub_templates 目录下引用子模板
 *
 * @param string $father_template_name
 *
 * @return bool
 */
function lpcn_use_sub_template( string $father_template_name ): bool {
	global $blog_id;

	$site = get_site( $blog_id );

	$site = str_replace( '/', '', (string) $site?->path );

	$site = $site ?: 'root';

	$sub_template_name = "{$site}-{$father_template_name}";
	$sub_template_path = UI_ROOT_PATH . "/sub-templates/$sub_template_name.php";

	if ( file_exists( $sub_template_path ) ) {
		require $sub_template_path;

		return true;
	}

	return false;
}

// 定制标题
add_filter( 'wp_title', function ( $title, $sep, $seplocation ) {
	$uri = $_SERVER['REQUEST_URI'];
	list( $uri ) = explode( '?', $uri );
	list( $uri ) = explode( '#', $uri );

	$site_title = get_bloginfo( 'name' );

	if ( '/' === $uri ) {
		$title = 'LitePress &#8211; 中国本土的 WordPress 衍生版';
	} else {
		$title .= $site_title;
	}

	return $title;
}, 9999, 3 );

/**
 * 面包屑
 */
// 全局面包屑
function lpcn_breadcrumb() {
	printf( '<a href="/" rel="nofollow">首页</a>' );

	$uri = $_SERVER['REQUEST_URI'];
	list( $uri ) = explode( '?', $uri );
	list( $uri ) = explode( '#', $uri );

	global $current_blog;

	$blog_name = str_replace( 'LitePress ', '', get_bloginfo( 'name' ) );

	if ( $current_blog->path === $uri ) {
		printf( " » $blog_name" );
	} else if ( str_starts_with( $uri, $current_blog->path ) ) {
		printf( ' » <a href="%s" rel="nofollow">%s</a>', $current_blog->path, $blog_name );
	}

	if ( is_category() || is_single() ) {
		$cat  = get_the_category()[0] ?? new stdClass();
		$link = get_category_link( $cat->term_id );

		$site_info = get_current_site();

		$link = str_replace( "https://$site_info->domain", '', $link );

		if ( $link === $uri ) {
			printf( " » $cat->name" );
		} else if ( str_starts_with( $uri, $current_blog->path ) ) {
			printf( ' » <a href="%s" rel="nofollow">%s</a>', $link, $cat->name );
		}

		if ( is_single() ) {
			echo ' » ';
			the_title();
		}
	} elseif ( is_page() ) {
		echo ' » ';
		echo the_title();
	} elseif ( is_search() ) {
		echo ' » Search Results for…';
		echo '“<em>';
		the_search_query();
		echo '</em>”';
	}
}

// BBP 面包屑定制
add_filter( 'bbp_after_get_breadcrumb_parse_args', function ( array $args ): array {
	$args['sep'] = ' »';

	return $args;
} );

// Woo 面包屑定制
add_filter( 'woocommerce_breadcrumb_home_url', function () {
	return '/';
} );

// GlotPress 面包屑定制
add_filter( 'gp_breadcrumb_items', function ( $breadcrumbs ) {
	array_unshift( $breadcrumbs, '<a href="/">首页</a>' );

	foreach ( $breadcrumbs as $key => $breadcrumb ) {
		if ( stristr( '<a href="/translate/projects/">项目</a>', $breadcrumb ) ) {
			$breadcrumbs[ $key ] = '<a href="/translate/">翻译平台</a>';
		}
	}

	return $breadcrumbs;
}, 9999 );

// WeDocs 面包屑定制
add_filter( 'wedocs_breadcrumbs', function ( $args ) {
	$args['delimiter'] = '&nbsp;&#187;&nbsp;';

	return $args;
} );

add_filter( 'wedocs_breadcrumbs_html', function ( $html, $args ) {
	// 去除所有换行
	$html = str_replace( PHP_EOL, '', $html );

	// 替换开头部分
	$html = str_replace(
		'<li><i class="wedocs-icon wedocs-icon-home"></i></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">            <a itemprop="item" href="https://litepress.cn/manual/">            <span itemprop="name">首页</span></a>            <meta itemprop="position" content="1" />        </li>&nbsp;&#187;&nbsp;<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">            <a itemprop="item" href="https://litepress.cn/manual/">            <span itemprop="name">文档</span></a>            <meta itemprop="position" content="2" />        </li>',
		'<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">            <a itemprop="item" href="https://litepress.cn/">            <span itemprop="name">首页</span></a>            <meta itemprop="position" content="1" />        </li>&nbsp;&#187;&nbsp;<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">            <a itemprop="item" href="https://litepress.cn/manual/">            <span itemprop="name">手册资源</span></a>            <meta itemprop="position" content="2" />        </li>',
		$html
	);

	return $html;
}, 10, 2 );

// 移除bbPress对非管理员提交的过滤
remove_filter( 'bbp_new_topic_pre_content', 'bbp_encode_bad', 10 );
remove_filter( 'bbp_new_topic_pre_content', 'bbp_filter_kses', 30 );
remove_filter( 'bbp_new_forum_pre_content', 'bbp_encode_bad', 10 );
remove_filter( 'bbp_new_forum_pre_content', 'bbp_filter_kses', 30 );
remove_filter( 'bbp_new_reply_pre_content', 'bbp_encode_bad', 10 );
remove_filter( 'bbp_new_reply_pre_content', 'bbp_filter_kses', 30 );

remove_filter( 'bbp_edit_topic_pre_content', 'bbp_encode_bad', 10 );
remove_filter( 'bbp_edit_topic_pre_content', 'bbp_filter_kses', 30 );
remove_filter( 'bbp_edit_forum_pre_content', 'bbp_encode_bad', 10 );
remove_filter( 'bbp_edit_forum_pre_content', 'bbp_filter_kses', 30 );
remove_filter( 'bbp_edit_reply_pre_content', 'bbp_encode_bad', 10 );
remove_filter( 'bbp_edit_reply_pre_content', 'bbp_filter_kses', 30 );

/**
 * 修改文章简介的阶段样式
 */
add_filter( 'excerpt_more', function () {
	return '...';
} );
