<?php

namespace WCY\Inc\Ultimate_Member;

/**
 * 获取用户所管理的翻译项目
 *
 * @param int $user_id 用户 ID
 * @param bool $is_count
 *
 * @return array|int
 */
function get_gp_manage_projects( int $user_id, bool $is_count = false ): array|int {
	global $wpdb;

	$sql         = $wpdb->prepare( "select object_id from wp_4_gp_permissions where 1=1 and object_type='project|locale|set-slug' and user_id=%d and action='approve';", $user_id );
	$permissions = $wpdb->get_results( $sql, ARRAY_A );

	// 如果只需要统计数量的话此时就返回，因为一行权限就代表对一个项目拥有权限
	if ( $is_count ) {
		return count( $permissions );
	}

	$project_ids = array();
	foreach ( $permissions as $permission ) {
		$project_ids[] = explode( '|', $permission['object_id'] )[0];
	}

	$search = $_GET['ts'] ?? '';
	$paged  = $_GET['paged'] ?? 1;

	$project_ids = join( ',', $project_ids );
	$sql         = $wpdb->prepare( "select id, name, author, slug, path, description, parent_project_id from wp_4_gp_projects where 1=1 and id in ( {$project_ids} ) and active=1 and name like '%%%s%%' limit %d,%d;", $search, ( $paged - 1 ) * 15, 15 );

	return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * 获取用户参与贡献的翻译项目
 *
 * @param int $user_id 用户 ID
 * @param bool $is_count
 *
 * @return array|int
 */
function get_gp_contribution_projects( int $user_id, bool $is_count = false ): array|int {
	global $wpdb;

	$sql          = $wpdb->prepare( "select translation_set_id from wp_4_gp_translations where 1=1 and user_id=%d and status='current' group by translation_set_id;", $user_id );
	$translations = $wpdb->get_results( $sql, ARRAY_A );

	// 如果只需要统计数量的话此时就返回，因为一行权限就代表对一个项目拥有权限
	if ( $is_count ) {
		return count( $translations );
	}

	$translation_set_ids = array();
	foreach ( $translations as $translation ) {
		$translation_set_ids[] = $translation['translation_set_id'];
	}

	$search = $_GET['ts'] ?? '';
	$paged  = $_GET['paged'] ?? 1;

	$translation_set_ids = join( ',', $translation_set_ids );
	$sql                 = sprintf( <<<SQL
select *
from wp_4_gp_projects
where 1 = 1
  and id in (select parent_project_id
             from wp_4_gp_projects
             where id in (select project_id from wp_4_gp_translation_sets where id in ({$translation_set_ids})))
 and name like '%%%s%%'
 limit %d,%d;
SQL, $search, ( $paged - 1 ) * 15, 15 );

	return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * 通过 Ajax 返回翻译项目数据
 */
function translate_load(): void {
	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => '该用户信息读取失败' ) );
	}

	$type = $_POST['sub'] ?? '';

	$data = array();
	if ( 'contribution' === $type ) {
		$data = get_gp_contribution_projects( $user_id );
	} else {
		$data = get_gp_manage_projects( $user_id );
	}

	foreach ( $data as &$item ) {
		if ( ! function_exists( 'gp_get_meta' ) ) {
			require WP_CONTENT_DIR . '/plugins/glotpress/gp-includes/meta.php';
		}

		$current_blog_id = get_current_blog_id();

		switch_to_blog( 4 );

		global $wpdb;
		$wpdb->gp_meta = 'wp_4_gp_meta';
		$version       = gp_get_meta( 'project', $item['id'], 'version' );
		$slug          = $item['slug'];

		if ( 1 === (int) $item['parent_project_id'] ) {
			$item['icon'] = sprintf( '<img width="64" height="64" loading="lazy" class="plugin-icon"
                             src="https://ps.w.org.ibadboy.net/%s/assets/icon-128x128.png"
                             onError="this.src=\'https://cravatar.cn/avatar/%s?d=identicon&s=133\';">', $slug, md5( $slug ) );
		} elseif ( 2 === (int) $item['parent_project_id'] ) {
			$item['icon'] = sprintf( '<img width="64" height="64" loading="lazy" class="plugin-icon"
                             src="https://i0.wp.com/themes.svn.wordpress.org/%s/%s/screenshot.png"
                             onError="this.src=\'https://i0.wp.com/themes.svn.wordpress.org/%s/%s/screenshot.jpg\';">', $slug, $version, $slug, $version );
		} else {
			$icon_url = gp_get_meta( 'project', $item['id'], 'icon' );
			if ( empty( $icon_url ) ) {
				$icon_url = sprintf( 'https://cravatar.cn/avatar/%s?d=identicon&s=133', md5( $slug ) );
			}

			$item['icon'] = sprintf( '<img width="64" height="64" loading="lazy" class="plugin-icon" src="%s">', $icon_url );
		}

		switch_to_blog( $current_blog_id );
	}
	unset( $item );

	wp_send_json_success( $data );
}

add_action( 'um_ajax_load_posts__lpcn_translate_load', 'WCY\Inc\Ultimate_Member\translate_load' );

/**
 * 添加翻译项目 Tabs
 */
add_filter( 'um_user_profile_tabs', function ( array $tabs ) {
	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		return $tabs;
	}

	$manage_project_count       = get_gp_manage_projects( $user_id, true );
	$contribution_project_count = get_gp_contribution_projects( $user_id, true );

	if ( 0 === $manage_project_count && 0 === $contribution_project_count ) {
		return $tabs;
	}

	$tabs['translate'] = array(
		'name' => '翻译',
		'icon' => 'um-faicon-pencil',
	);

	if ( 0 !== $manage_project_count ) {
		$tabs['translate']['subnav']['manage'] = sprintf( '管理的项目<span>%d</span>', $manage_project_count );
	}

	if ( 0 !== $contribution_project_count ) {
		$tabs['translate']['subnav']['contribution'] = sprintf( '参与的项目<span>%d</span>', $contribution_project_count );
	}

	$tabs['translate']['subnav_default'] = 'manage';

	if ( isset( $tabs['translate'] ) && ! isset( $tabs['translate']['subnav'][ $tabs['translate']['subnav_default'] ] ) ) {
		$i = 0;
		if ( isset( $tabs['translate']['subnav'] ) ) {
			foreach ( $tabs['translate']['subnav'] ?? array() as $id => $data ) {
				$i ++;
				if ( $i == 1 ) {
					$tabs['translate']['subnav_default'] = $id;
				}
			}
		}
	}

	return $tabs;
}, 10, 1 );

/**
 * 返回用户所管理的项目的内容
 */
function translate_manage( array $args ): array {
	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		return $args;
	}

	$data = get_gp_manage_projects( $user_id );

	get_template_part( 'ultimate-member/templates/translate/projects', null, $data );

	return $args;
}

add_action( 'um_profile_content_translate_manage', 'WCY\Inc\Ultimate_Member\translate_manage', 10, 1 );

/**
 * 返回用户所管理的项目的内容
 */
function translate_contribution( array $args ): array {
	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		return $args;
	}

	$data = get_gp_contribution_projects( $user_id );

	get_template_part( 'ultimate-member/templates/translate/projects', null, $data );

	return $args;
}

add_action( 'um_profile_content_translate_contribution', 'WCY\Inc\Ultimate_Member\translate_contribution', 10, 1 );

/**
 * 返回默认的 Tab 内容
 */
add_action( 'um_profile_content_translate_default', function ( array $args ) {
	$tabs = UM()->profile()->tabs_active();

	$default_tab = $tabs['translate']['subnav_default'];

	do_action( "um_profile_content_translate_{$default_tab}", $args );
}, 10, 1 );
