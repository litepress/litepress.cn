<?php
/**
 * 替换终极会员插件的Gravatar头像为本土化社区托管的加速镜像
 */

use WCY\Inc\BBPress\Walker_Reply;

global $blog_id;

add_filter( 'um_user_avatar_url_filter', function ( $url ) {
	return str_replace( '//gravatar.com', '//cravatar.cn', $url );
} );

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
		$image = '<img src="https://avatar.ibadboy.net/avatar/' . md5( rand() ) . '?d=identicon&s=256" class="attachment-128 size-128" alt="" loading="lazy">';
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
	$views       = (int) @get_post_meta( $topic_id, 'views' )[0] ?: 0;

	$create_time = human_time_diff( strtotime( get_the_time( 'Y-n-d H:i:s' ) ), current_time( 'timestamp' ) );
	$last_time   = human_time_diff( strtotime( get_post_meta( $topic_id, '_bbp_last_active_time', true ) ), current_time( 'timestamp' ) );

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
                发布于{$create_time}前
            </a>
            <span class="divider">/</span>
            <a class="" >活跃于{$last_time}前
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
 * 重设bbpress的回复时间格式为x年前 or x月前……
 */
add_filter( 'bbp_get_reply_post_date', function ( $result, $reply_id, $humanize, $gmt, $date, $time ) {
	return '回复于' . human_time_diff( strtotime( get_post_time( 'Y-m-d H:i:s', false, $reply_id ) ), current_time( 'timestamp' ) ) . '前';
}, 10, 6 );

/**
 * 将楼主的权限信息修改为楼主，而不显示wordpress用户权限名
 */
add_filter( 'bbp_get_reply_author_role', function ( $author_role, $r, $args ) {
	$nameplate = get_user_meta( bbp_get_reply_author_id( $args['reply_id'] ?? 0 ), 'nameplate', true );
	if ( ! stristr( $nameplate, 'nameplate' ) ) {
		$nameplate = '';
	}
	$data = do_shortcode( sanitize_text_field( $nameplate ) );
	if ( $r['reply_id'] === bbp_get_topic_id() || bbp_get_reply( $r['reply_id'] )->post_author === bbp_get_topic( bbp_get_topic_id() )->post_author ) {
		$data .= '<div class="bbp-author-role">楼主</div>';
	} else {
		$data .= $author_role;
	}

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
	 */
	if ( wp_is_mobile() ) {
		$columns = 1;
	}

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
 * 替换bbp的板块URL
 */
add_filter( 'bbp_get_forum_permalink', function ( $forum_permalink, $forum_id ) {
	switch ( $forum_id ) {
		case 228:
			return '/forum-help';
		case 104:
			return '/forum-translate';
		case 226:
			return '/forum-proposal';
		case 19915:
			return '/experience-sharing';
		case 19921:
			return '/feedback';
		default:
			return $forum_permalink;
	}
}, 1, 2 );

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
		return '<a href="' . esc_url( $link_url ) . '" title="' . esc_attr( $title ) . '">活跃于' . human_time_diff( strtotime( $time_since ), current_time( 'timestamp' ) ) . '前</a>';
	} else {
		return esc_html__( 'No Replies', 'bbpress' );
	}
}, 10, 5 );

/**
 * Woo的商品链接去掉基础链接后有个产品会和分类目录命名冲突，需要解决
 */
add_filter( 'request', function ( $query, $request_url = false, $return_object = false ) {
	$url = $_SERVER['REQUEST_URI'];

	$pos = strpos( $url, '?' );
	if ( $pos ) {
		$url = substr( $url, 0, $pos - strlen( $url ) );
	}

	/**
	 * 产品标签请求
	 */
	$tag = explode( '/product-tag/', $url )[1] ?? '';
	if ( ! empty( $tag ) ) {
		return array( 'product_tag' => $tag );
	}

	/**
	 * Ajax请求
	 */
	$ajax_route = explode( 'store/wp-json', $url )[1] ?? '';
	if ( ! empty( $ajax_route ) ) {
		return array( 'rest_route' => $ajax_route );
	}

	/**
	 * 针对特性URL的规则
	 */
	return match ( $url ) {
		'/store/plugins' => array( 'product_cat' => 'plugins' ),
		'/store/themes' => array( 'product_cat' => 'themes' ),
		default => $query,
	};
}, 9999, 3 );

if ( 6 === (int) $blog_id ) {
	add_filter( 'comment_form_defaults', function ( $fields ) {
		$fields['logged_in_as']  = get_avatar( get_current_user_id(), 32 );
		$fields['submit_button'] = '';
		$fields['comment_field'] = sprintf(
			'<p class="comment-form-comment">%s %s %s</p>',
			sprintf(
				'<label for="comment">%s</label>',
				_x( 'Comment', 'noun' )
			),
			'<textarea class="form-control" id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required"></textarea>',
			'<input type="submit" id="submit" class="btn btn-primary" value="提交评论" />'
		);

		return $fields;
	} );
}

/*
 * 用来批量替换自定义固定连接的代码，这段代码看不懂的话千万别取消注释，很容易造成破坏
$urls = get_option('permalink-manager-uris');
$new_urls = array();
foreach ($urls as $k => $v) {
	$new_urls[$k] = str_replace( 'apps/', '', $v );
}
update_option('permalink-manager-uris', $new_urls);
*/

/**
 * 身份铭牌支持
 */
add_shortcode( 'nameplate', function ( $attr, $content = '' ) {
	$excludes       = array( '管理员', '超级管理员', 'admin', 'administrators', '站长', '所有者', '管理' );
	$excluded_blank = array( ' ', '　', '&nbsp;', '\n', '\r', '\t' );

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

add_filter( 'woocommerce_breadcrumb_home_url', function () {
	return '/';
} );

add_filter( 'gp_breadcrumb_items', function ( $breadcrumbs ) {
	array_unshift( $breadcrumbs, '<a href="/">首页</a>' );

	foreach ( $breadcrumbs as $key => $breadcrumb ) {
		if ( stristr( '<a href="/translate/projects/">项目</a>', $breadcrumb ) ) {
			$breadcrumbs[ $key ] = '<a href="/translate/">翻译平台</a>';
		}
	}

	return $breadcrumbs;
}, 9999 );

add_filter( 'wedocs_breadcrumbs', function ( $args ) {
	$args['delimiter'] = '&nbsp;&#187;&nbsp;';

	return $args;
} );

add_filter( 'wpseo_breadcrumb_links', function ( $links ) {
	array_unshift( $links, array(
		'url'  => '/',
		'text' => '首页',
		'id'   => 1,
	) );
	$links[1]['text'] = '文档平台';

	return $links;
}, 9999 );

/**
 * 搜索结果页面短码支持
 */
add_shortcode( 'wcorg-search-results-tab', function () {
	return lava_ajaxSearch()->core->print_tabs();
} );

add_shortcode( 'wcorg-search-results-results', function () {
	return lava_ajaxSearch()->core->print_results();
} );

add_shortcode( 'translators', 'wcy_get_translators' );


/**
 * 译者名单
 */
function wcy_get_translators() {
	/*
// 把当前存量的用户翻译入库
$tmp = $wpdb->get_results('select * from wp_4_gp_translations where `user_id`!=1;');
$ohehe = [];
foreach ($tmp as $v) {
	if (key_exists($v->user_id, $ohehe)) {
		$ohehe[$v->user_id] += 1;
	} else {
		$ohehe[$v->user_id] = 1;
	}
}

foreach ($ohehe as $k => $v) {
	$wpdb->insert('wp_4_gp_translators', [
		'user_id' => $k,
		'count' => $v
	]);
}
*/

	$html = '<ul class="translator-list">';

	global $wpdb;

	$translators = $wpdb->get_results( 'select * from wp_4_gp_translators order by `count` desc;' );

	$i = 0;
	foreach ( $translators as $k => $v ) {
		if ( $i >= 10 ) {
			break;
		}
		$user_info = get_user_by( 'id', $v->user_id );
		$html      .= sprintf( '<li><em>%d.</em> <div class="rank-list__name">%s</div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->display_name, $v->count );

		$i ++;
	}

	return $html .= '</ul>';
}

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
