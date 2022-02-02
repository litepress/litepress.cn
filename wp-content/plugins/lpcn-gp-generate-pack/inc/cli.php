<?php
/**
 * 从 WordPress.org 导入发行版的翻译
 *
 * 这是一个 Cli 命令行程序，由管理员在使用时手工调用
 */

namespace LitePress\GlotPress\Generate_Pack;

use WP_CLI_Command;
use WP_CLI;

require_once __DIR__ . '/class-plugin.php';

class Cli extends WP_CLI_Command {

	public function worker( $args, $assoc_args ) {
		Plugin::get_instance()->generate_all_language_pack();

		WP_CLI::line( '恭喜你，执行成功' );
	}

}

WP_CLI::add_command( 'lpcn translate generate-pack', __NAMESPACE__ . '\Cli' );

