<?php
/**
 * 机器翻译指定内容
 *
 * 这是一个 Cli 命令行程序，由管理员在使用时手工调用
 */

namespace LitePress\GlotPress\MT;

use GP;
use WP_CLI_Command;
use WP_CLI;
use Translations;

class Translate_CLI extends WP_CLI_Command {

	public function translate( $args, $assoc_args ) {
		if ( ! isset( $assoc_args['slug'] ) ) {
			WP_CLI::line( '你需要给出slug：readme or body' );
			exit;
		}

		$this->worker( $assoc_args['slug'] );

		WP_CLI::line( '恭喜你，执行成功' );
	}

	/**
	 * @param string $type 项目类型：readme or body
	 */
	private function worker( string $slug ) {
		// 取出全部项目
		$projects = GP::$project->find_many( array(
			'slug' => $slug,
		) );

		// 循环全部项目，开始翻译
		foreach ( $projects as $project ) {

			$project_id = $project->id;
			$project    = GP::$project->find_one( array( 'id' => $project_id ) )->fields();

			// 获取待翻译原文
			$sql = <<<SQL
select *
from wp_4_gp_originals
where project_id = {$project_id}
  and id not in (
    select original_id
    from wp_4_gp_translations
    where translation_set_id = (
        select id
        from wp_4_gp_translation_sets
        where project_id = {$project_id}
    )
)
  and status = '+active';
SQL;

			$originals = GP::$original->many( $sql );

			$translate    = new Translate();
			$translations = $translate->web( $project_id, $originals );

			if ( $slug == 'body' ) {
				$file = 'log-body.txt';
			} else {
				$file = 'log-readme.txt';
			}

			$f = file_put_contents( $file, 'ID为：' . $project_id . ' 的项目 ' . $slug . ' 翻译成功' . PHP_EOL, FILE_APPEND );
		}

	}

}

WP_CLI::add_command( 'lpcn translate-cli', __NAMESPACE__ . '\Translate_CLI' );
