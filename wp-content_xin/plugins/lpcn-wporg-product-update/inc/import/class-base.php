<?php

namespace LitePress\Store\WPOrg_Product_Update\Import;

use LitePress\Logger\Logger;
use LitePress\Tools\SVN;
use function LitePress\Helper\execute_command;

class Base {

	// 该变量由子类定义
	protected string $svn_url = '';

	// 存在更新的产品 Slug
	protected array $slug_update_list = array();

	public function __construct() {

	}

	public function worker() {
		$this->check_svn();
	}

	private function check_svn(): bool {
		$start_time = date( DATE_ISO8601, 1656985988 );
		$end_time   = date( DATE_ISO8601, 1656985988 + 3600 );

		$log = SVN::log( $this->svn_url, array( $start_time, $end_time ) );

		if ( ! empty( $log['errors'] ) ) {
			Logger::error( Logger::STORE, "WP.org 商品导入失败", $log['errors'] );

			return false;
		}

		foreach ( $log['log'] as $project ) {
			$i = 0;

			foreach ( $project['paths'] as $path ) {
				preg_match( '|/([^/]+)|', $path, $matches );
				if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
					// 为了防止重复项，这里使用键来存储
					$this->slug_update_list[ $matches[1] ] = $i;

					$i ++;
				}
			}
		}

		$this->slug_update_list = array_flip( $this->slug_update_list );

		var_dump( $this->slug_update_list );
		exit;

		return true;
	}

}
