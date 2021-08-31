<?php
$args = $args ?? array();
?>

<ul>
	<?php foreach ( $args as $item ): ?>
		<?php
		if ( ! function_exists( 'gp_get_meta' ) ) {
			require WP_CONTENT_DIR . '/plugins/glotpress/gp-includes/meta.php';
		}

		$current_blog_id = get_current_blog_id();

		switch_to_blog( 4 );

        global $wpdb;
		$wpdb->gp_meta = 'wp_4_gp_meta';
		$version = gp_get_meta( 'project', $item['id'], 'version' );
		$slug    = $item['slug'];

		if ( 1 === (int) $item['parent_project_id'] ) {
			$icon = sprintf( '<img width="64" height="64" loading="lazy" class="plugin-icon"
                             src="https://ps.w.org.ibadboy.net/%s/assets/icon-128x128.png"
                             onError="this.src=\'https://cravatar.cn/avatar/%s?d=identicon&s=133\';">', $slug, md5( $slug ) );
		} elseif ( 2 === (int) $item['parent_project_id'] ) {
			$icon = sprintf( '<img width="64" height="64" loading="lazy" class="plugin-icon"
                             src="https://i0.wp.com/themes.svn.wordpress.org/%s/%s/screenshot.png"
                             onError="this.src=\'https://i0.wp.com/themes.svn.wordpress.org/%s/%s/screenshot.jpg\';">', $slug, $version, $slug, $version );
		} else {
			$icon_url = gp_get_meta( 'project', $item['id'], 'icon' );
			if ( empty( $icon_url ) ) {
				$icon_url = sprintf( 'https://cravatar.cn/avatar/%s?d=identicon&s=133', md5( $slug ) );
			}

			$icon = sprintf( '<img width="64" height="64" loading="lazy" class="plugin-icon" src="%s">', $icon_url );
		}

		switch_to_blog( $current_blog_id );
		?>
        <li class="um-item d-flex align-items-center">
            <aside>
				<?php echo $icon; ?>
            </aside>
            <article class="ms-2"><a href="/translate/projects/<?php echo $item['path'] ?>"
                                     target="_blank"><?php echo $item['name'] ?></a></article>
            <article class="ms-2"><?php echo $item['description'] ?></article>
        </li>
	<?php endforeach; ?>
</ul>
