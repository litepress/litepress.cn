<?php

namespace LitePress\Store\WPOrg_Product_Update;

use DiDom\Document;
use DiDom\Query;
use WP_CLI_Command;
use WP_CLI;
use function LitePress\WP_Http\wp_remote_get;

class Sync_WPOrg_Svn extends WP_CLI_Command {

	public function worker( $args, $assoc_args ) {
		//$group_id = $assoc_args['group_id'];
		//$server   = $assoc_args['server'];

		// 从文件读入数组
		$plugins = file('/www/wwwroot/litepress.cn/active_plugins.txt', FILE_IGNORE_NEW_LINES);
		$themes = file('/www/wwwroot/litepress.cn/active_themes.txt', FILE_IGNORE_NEW_LINES);

		

		foreach ( $plugins as $item ) {
			$slug = $item;

			$error_count = 0;
			system( "mkdir /data/svn/plugins/$slug" );
			while ( true ) {
				system( "cd /data/svn/plugins/$slug && svn cleanup" );

				system( "cd /data/svn/plugins && svn checkout https://plugins.svn.wordpress.org/$slug", $status );

				// 0 是成功， 1 是失败
				if ( 0 === (int) $status ) {
					break;
				} else if ( $error_count > 10 ) {
					file_put_contents( '/www/wwwroot/litepress.cn/svn_error_plugins.log', $slug, FILE_APPEND );
					break;
				} else {
					$error_count ++;
				}
			}
		}
		echo "插件同步完成。";
		foreach ( $themes as $item ) {
			$slug = $item;

			$error_count = 0;
			system( "mkdir /data/svn/themes/$slug" );
			while ( true ) {
				system( "cd /data/svn/themes/$slug && svn cleanup" );

				system( "cd /data/svn/themes && svn checkout https://themes.svn.wordpress.org/$slug", $status );

				// 0 是成功， 1 是失败
				if ( 0 === (int) $status ) {
					break;
				} else if ( $error_count > 10 ) {
					file_put_contents( '/www/wwwroot/litepress.cn/sync-wporg-svn-error.log', $slug, FILE_APPEND );
					break;
				} else {
					$error_count ++;
				}
			}
		}
		echo "主题同步完成。";
	}

}

WP_CLI::add_command( 'lpcn sync_wporg_svn', __NAMESPACE__ . '\Sync_WPOrg_Svn' );
