<?php

namespace LitePress\GlotPress\MT;

use GP;
use GP_Route;

class Web extends GP_Route{

	/**
	 * 创建网页端翻译任务
	 */
	public function add_web_translate_job( $project_id ) {
		$project = GP::$project->find_one( array( 'id' => $project_id ) )->fields();

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

		for ( $i = 0; true; $i += 300 ) {
			$item = array_slice( $originals, $i, 500, true );
			if ( empty( $item ) ) {
				break;
			}

			do_action( 'lpcn_schedule_gp_mt', $project_id, $item );

			wp_schedule_single_event( time() + 1, 'lpcn_schedule_gp_mt', [
				'project_id' => $project_id,
				'originals'  => $item,
			] );
		}

		$referer = gp_url_project( $project['path'] );
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer = $_SERVER['HTTP_REFERER'];
		}

		$route            = new GP_Route();
		$route->notices[] = '该请求已加入队列，请稍后刷新页面';
		$route->redirect( $referer );
	}

}
