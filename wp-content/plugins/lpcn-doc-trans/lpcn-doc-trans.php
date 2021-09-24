<?php
/**
 * Plugin Name: 在前端翻译文档
 * Description: 该插件于 GlotPress 相配合，可在支持在网页前端点击翻译 GlotPress 中托管的文档项目
 * Author: LitePress 社区团队
 * Author URI:https://litepress.cn/
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Docs\Translate;

use LitePress\I18n\i18n;
use function LitePress\Helper\html_split;

require_once 'vendor/autoload.php';

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;
define( "LitePress\Docs\Translate\PLUGIN_URL", plugin_dir_url( PLUGIN_FILE ) );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'community-translator', PLUGIN_URL . '/community-translator/community-translator.css' );

	wp_enqueue_script( 'community-translator', PLUGIN_URL . '/community-translator/community-translator.js', [], false, true );
} );

add_action( 'plugins_loaded', function () {

	add_filter( 'the_content', function ( $content_originals ): string {
		$post_id = get_the_ID();
		if ( empty( $post_id ) ) {
			return $content_originals;
		}

		$post = get_post( $post_id );


		$content              = '';
		$strings_used_on_page = array(
			'Overview of WordPress' => array(
				'Overview of WordPress'
			),
			'WordPress is a free and open source content' => array(
				'WordPress is a free and open source content'
			),
			'WordPress started as a simple blogging system in 2003, but it has evolved into a full CMS with thousands of plugins, widgets, and themes. It is licensed under the General Public License (GPLv2 or later). ' => array(
				'WordPress started as a simple blogging system in 2003, but it has evolved into a full CMS with thousands of plugins, widgets, and themes. It is licensed under the General Public License (GPLv2 or later). '
			),
		);

		//$section_strings = html_split( $content_originals );
/*
		foreach ( $section_strings as $section_string ) {
			$translation                          = i18n::get_instance()->translate( sanitize_key( $section_string ), $section_string, "docs/docs-$post->post_name" );
			$strings_used_on_page[ $translation ] = array( $section_string );
			$content                              .= $translation;
		}
*/
		$strings_used_on_page = json_encode( $strings_used_on_page, JSON_UNESCAPED_UNICODE );

		add_action( 'wp_footer', function () use ( $post, $strings_used_on_page ) { ?>
            <script type="text/javascript">
                translatorJumpstart = {
                    stringsUsedOnPage: <?php echo $strings_used_on_page; ?>,
                    localeCode: "zh-cn",
                    languageName: "Chinese",
                    pluralForms: "nplurals=2; plural=(n > 1)",
                    glotPress: {
                        url: "https:\/\/litepress.cn/translate",
                        project: "docs/docs-<?php echo $post->post_name ?>"
                    }
                };
            </script>
		<?php } );

		return $content_originals;
	} );

} );
