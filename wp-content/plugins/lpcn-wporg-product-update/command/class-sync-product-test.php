<?php

namespace LitePress\Store\WPOrg_Product_Update;

use LitePress\Store\WPOrg_Product_Update\Import;
use WP_CLI_Command;
use WP_CLI;

class Sync_Product_Test extends WP_CLI_Command {

	public function worker( $args, $assoc_args ) {
		$plugins_import = new Import\Plugins();
		$themes_import  = new Import\Themes();

		while ( true ) {
			echo 'ALL OK';
			var_dump( $plugins_import->worker() );
			exit;

			ob_end_clean();
			ob_implicit_flush();
			sleep( 1 );
		}
	}

}

WP_CLI::add_command( 'lpcn sync_product_test', __NAMESPACE__ . '\Sync_Product_Test' );
