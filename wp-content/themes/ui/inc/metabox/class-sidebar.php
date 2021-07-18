<?php

namespace WCY\Inc\MetaBox;

use WP_Post;

/**
 * 侧边栏Meta Box
 *
 * @since 1.0.0
 */
class Sidebar {

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
			'sidebar',
			'侧边栏',
			array( $this, 'html' ),
			['post', 'page'],
			'side'
		);
	}

	/**
	 * 在文章发布 or 保存时更新元框数据
	 *
	 * @since 1.0.0
	 * @param int $post_id 文章ID
	 */
	public function save( int $post_id ) {
		if (key_exists('sidebar', $_POST)) {
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
	 * @since 1.0.0
	 * @param WP_Post $post Post对象
	 */
	public function html( WP_Post $post ) {
		$value = get_post_meta( $post->ID, 'sidebar', true );
		echo '<input type="radio" name="sidebar" value="on" '.checked( $value, 'on', false ).'/>开启';
		echo '<input type="radio" name="sidebar" value="off" '.checked( $value, 'off', false ).'/>关闭';
	}

}
