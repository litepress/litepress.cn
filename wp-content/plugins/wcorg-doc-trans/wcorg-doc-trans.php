<?php
/**
 * Plugin Name: 在前端翻译文档
 * Description: 该插件于glotpress相配合，可在支持在网页前端点击翻译glotpress中托管的文档项目
 * Author: WP中国本土化社区
 * Author URI:https://wp-china.org/
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

use voku\helper\HtmlDomParser;

require_once 'vendor/autoload.php';

add_action('init', function () {
    $args = array(
        'public' => true,
        'show_in_rest' => true,
        'label' => 'docs'
    );
    register_post_type('docs', $args);
});

//var_dump('asf');
//exit;

add_action('plugins_loaded', function () {
    load_plugin_textdomain('what-is-a-plugin', FALSE, basename(dirname(__FILE__)) . '/languages/');

    add_filter('the_content', function ($content_original) {
        $content = '';
        $strings_used_on_page = '';
        $dom = HtmlDomParser::str_get_html('<body>' . $content_original . '</body>>');
        foreach ($dom->find('body', 0)->childNodes() as $a) {
            $node_html = __($a->html, 'what-is-a-plugin');
            $strings_used_on_page .= sprintf('"%s": ["%s"],', addslashes(HtmlDomParser::str_get_html($node_html)->text), addslashes($a->html));
            $content .= $node_html;
        }

        add_action( 'wp_footer', function () use ($strings_used_on_page) { ?>
            <script type="text/javascript">
                translatorJumpstart = {
                    stringsUsedOnPage: {
                        <?php echo $strings_used_on_page; ?>
                        "": [""]
                    },
                    localeCode: "zh-cn",
                    languageName: "Chinese",
                    pluralForms: "nplurals=2; plural=(n > 1)",
                    glotPress: {
                        url: "https:\/\/translate.wp-china-yes.com",
                        project: "docs/what-is-a-plugin"
                    }
                };
            </script>
        <?php } );

        return $content;
    });

    //echo __( '<p><a title="Hello Dolly" href="http://wp101.net/plugins/hello-dolly/" target="_blank" rel="noopener noreferrer">Hello Dolly</a>, one of the first plugins, is only<a href="http://plugins.trac.wp101.net/browser/hello-dolly/trunk/hello.php">82 lines</a>long. Hello Dolly shows lyrics from <a href="http://en.wikipedia.org/wiki/Hello,_Dolly!_(song)">the famous song</a> in the WordPress admin. Some CSS is used in the PHP file to control how the lyric is styled.</p>', 'what-is-a-plugin' );
    //echo __( 'abc', 'what-is-a-plugin' );
});
