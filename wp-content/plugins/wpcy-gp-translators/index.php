<?php
/**
 * Plugin Name: GP-Translators
 * Description: GlotPress的译者列表展示插件
 * Author: WP-China
 * Version: 1.0.0
 * Author URI:https://wp-china.org
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_shortcode('translators', 'get_translators');


/**
 * 译者名单小工具
 */
class gitchat_widget extends WP_Widget {
    public function __construct() {
        $widget_ops = array(
            'classname' => 'gitchat_widget',
            'description' => '这是一个 GitChat 小工具',
        );
        parent::__construct( 'gitchat_widget', 'Gitchat Widget', $widget_ops );
    }

    public function widget( $args, $instance ) {
        echo '<h6 class="translator-title"><i class="dashicons dashicons-before dashicons-translation" aria-hidden="true"></i> 翻译贡献榜</h6>
<ul class="translator-list">';
        global $wpdb;

        $translators = $wpdb->get_results('select * from wp_4_gp_translators order by `count` desc;');

        $i = 0;
        foreach ($translators as $k => $v) {
            if ($i >= 10) {
                break;
            }
            $user_info = get_user_by('id', $v->user_id);
            printf('<li><em>%d.</em> <div class="rank-list__name">%s</div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->display_name, $v->count);

            $i++;
        }

        printf('</ul><a class="rank-list__href" href="https://wp-china.org/translators" target="_blank"><i class="fad fa-users-medical"></i> 参与贡献</a>');
    }
}
add_action('widgets_init', function () {
    register_widget("gitchat_widget");
});

function get_translators() {
    global $wpdb;
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
    $translators = $wpdb->get_results('select * from wp_4_gp_translators order by `count` desc;');

    if (empty($attr)) {
        foreach ($translators as $k => $v) {
            $user_info = get_user_by('id', $v->user_id);
            printf('<span >%d.</span> <span>%s</span><span >%d 条</span> <br/>', $k + 1, $user_info->data->display_name, $v->count);
        }
    } else {
        $i = 0;
        foreach ($translators as $k => $v) {
            if ($i >= $attr['num']) {
                break;
            }
            $user_info = get_user_by('id', $v->user_id);
            printf('<span>%d.</span> <span >%s</span><span >%d 条</span> <br/>', $k + 1, $user_info->data->display_name, $v->count);

            $i++;
        }

        printf('<a href="https://wp-china.org/translators" target="_blank">查看全部</a>');
    }
}