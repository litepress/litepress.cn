<?php

use LitePress\Lavatar\Inc\Upyun;

define( 'CA_ROOT_PATH', get_stylesheet_directory() );
define( 'CA_ROOT_URL', get_stylesheet_directory_uri() );

require 'inc/functions.php';

require 'inc/enqueue-scripts.php';

require 'inc/class-upyun.php';

require 'inc/DataObject/class-avatar-status.php';

require 'inc/avatar-verify.php';

require 'inc/route.php';

$upyun = new Upyun();
/*
$r = $upyun->get( 'flow/common_data', array(
	'start_time'  => '2021-7-1 10:0:0',
	'end_time'    => '2021-7-2 10:0:0',
	'query_type'  => 'domain',
	'query_value' => 'd.w.org.ibadboy.net',
	'flow_type'   => 'cdn',
	'flow_source' => 'cdn',
) );
*/
/*
$upyun = new Upyun();
$r = $upyun->post( 'buckets/purge/batch', array(
	'noif'  => 1,
	'source_url'    => 'https://download.wp-china-yes.net/image/*',
) );
var_dump($r);
exit;
*/