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
                                     target="_blank"><?php echo $item['name'] ?></a>
                <p><?php echo $item['description'] ?></p>
            </article>

        </li>
	<?php endforeach; ?>
</ul>
<div class="um-load-items">
    <a href="javascript:void(0);" class="um-ajax-action1  um-button" data-hook="lpcn_translate_load"  data-args="" one-link-mark="yes">加载更多回复</a>
</div>

<script>

    (function ($) {
        $.extend({
            Request: function (m) {
                var sValue = location.search.match(new RegExp("[\?\&]" + m + "=([^\&]*)(\&?)", "i"));
                return sValue ? sValue[1] : sValue;
            },
            UrlUpdateParams: function (url, name, value) {
                var r = url;
                if (r != null && r != 'undefined' && r != "") {
                    value = encodeURIComponent(value);
                    var reg = new RegExp("(^|)" + name + "=([^&]*)(|$)");
                    var tmp = name + "=" + value;
                    if (url.match(reg) != null) {
                        r = url.replace(eval(reg), tmp);
                    }
                    else {
                        if (url.match("[\?]")) {
                            r = url + "&" + tmp;
                        } else {
                            r = url + "?" + tmp;
                        }
                    }
                }
                return r;
            }

        });
    })(jQuery);


    jQuery(document).on("click", ".um-ajax-action1", function(e) {
        e.preventDefault();
        var t = jQuery(this).data("hook")
            , a = jQuery(this).data("user_id")
            , arguments = jQuery(this).data("arguments")
        , subnav = $.Request("subnav")
        ;
        return jQuery(this).data("js-remove") && jQuery(this).parents("." + jQuery(this).data("js-remove")).fadeOut("fast"),
            jQuery.ajax({
                url: wp.ajax.settings.url,
                type: "post",
                data: {
                    action: "um_muted_action",
                    hook: t,
                    user_id: a,
                    nonce: um_scripts.nonce,
                    arguments: "",
                    sub:subnav,

                },
                success: function(e) {
                    console.log(e)

                }
            }),
            !1
    })
</script>

