<?php

namespace LitePress\GlotPress\MT;

use GP;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		if ( isset( $_GET['debug'] ) ) {
			add_action( 'gp_originals_imported', array( $this, 'schedule_gp_mt' ), 999 );
			add_action( 'lpcn_schedule_gp_mt', array( Translate::class, 'job' ), 999, 3 );
		}
	}

	/**
	 * 创建机器翻译填充任务
	 */
	public function schedule_gp_mt( $project_id ) {
		$project_id = (int) $project_id;

		$project = GP::$project->find_one( array( 'id' => $project_id ) )->fields();

		$project_name = $this->get_name_by_project_id( $project['id'] );

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
);
SQL;

		$originals = GP::$original->many( $sql );

		$excluded = array(
			$project_name
		);
		for ( $i = 0; true; $i += 100 ) {
			$item = array_slice( $originals, $i, 100, true );
			if ( empty( $item ) ) {
				break;
			}

			//do_action('lpcn_schedule_gp_mt' , $project_id, $item, $excluded );
			wp_schedule_single_event( time() + 60, 'lpcn_schedule_gp_mt', [
				'project_id' => $project_id,
				'originals'  => $item,
				'excluded'   => $excluded
			] );
		}
		var_dump( 'over' );
		exit;
	}

	/**
	 * 通过项目ID获取项目名
	 *
	 * 这不是简单的读取项目属性，因为项目属性中的项目名通常包含了长尾副词，所以这个函数尝试用项目原文中分析项目名称
	 */
	private function get_name_by_project_id( int $project_id ): string {
		$allowed = array(
			'Theme Name of the theme',
			'Plugin Name of the plugin',
			'Name of the plugin',
			'Name of the theme',
		);

		$original = GP::$original->find_one( array(
			'project_id' => $project_id,
			'comment'    => $allowed,
		) )->fields();

		return $original['singular'] ?? '';
	}

}
