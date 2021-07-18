<?php

namespace WCY\Inc\MetaBox;

use WP_Post;

/**
 * 侧边栏Meta Box
 *
 * @since 1.0.0
 */
class App_Release {

	/**
	 * 注册钩子
	 *
	 * @since 1.0.0
	 */
	public function register_hook() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * 添加元框
	 *
	 * @since 1.0.0
	 */
	public function add() {
		add_meta_box(
			'app_release',
			'应用信息',
			array( $this, 'html' ),
			[ 'product' ],
			'advanced'
		);
	}

	/**
	 * 在文章发布 or 保存时更新元框数据
	 *
	 * @param int $post_id 文章ID
	 *
	 * @since 1.0.0
	 */
	public function save( int $post_id ) {
		if ( key_exists( 'sidebar', $_POST ) ) {
			update_post_meta(
				$post_id,
				'sidebar',
				$_POST['sidebar']
			);
		}
	}

	/**
	 * 输出元框表单
	 *
	 * @param WP_Post $post Post对象
	 *
	 * @since 1.0.0
	 */
	public function html( WP_Post $post ) {
		$value                    = get_post_meta( $post->ID );
		$value['excerpt']         = array( $post->post_excerpt );
		$value['product-type']    = wp_get_post_terms( $post->ID, 'product_type' )[0]->slug ?? 'simple';
		$value['product_cat_ids'] = wc_get_product_cat_ids( $post->ID );

		require_once 'template/app-release.php';
	}

}
