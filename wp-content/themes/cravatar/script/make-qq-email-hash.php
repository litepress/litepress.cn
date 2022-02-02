<?php
/**
 * 这是一个独立执行的PHP脚本文件，用于穷举所有QQ邮箱的Hash值
 */


ini_set( 'memory_limit', '10240M' );

$conn = mysqli_connect( "host", 'user', "password", 'db_name' );

mysqli_query( $conn, 'set sql_log_bin = 0;' );

const TABLE_NUM = 5000;

for ( $i = 4652; $i <= TABLE_NUM; $i ++ ) {
	$sql = "LOAD DATA INFILE '/data/sql/email_hash_{$i}.csv' INTO TABLE email_hash_{$i} FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n';";

	mysqli_query( $conn, $sql );
}
/*
for ( $i = 1; $i <= TABLE_NUM; $i ++ ) {
	$table_name = 'email_hash_' . $i;

	$sql = "DROP TABLE " . $table_name;

	mysqli_query( $conn, $sql );
}

for ( $i = 1; $i <= TABLE_NUM; $i ++ ) {
	$table_name = 'email_hash_' . $i;

	$sql = "CREATE TABLE " . $table_name . " (
          md5 VARCHAR(32),
          qq BIGINT,
          PRIMARY KEY ( `md5` )
          );";

	mysqli_query( $conn, $sql );
}
*/
/**
 * QQ号生成范围：50000-5000000000
 */
/*
$a = time();

const NUM = 5000000000;// 本次要生成的QQ号的最高位
$tables = array();
$j      = 0;
for ( $i = 50000; $i < NUM; $i ++ ) {
	$email = "{$i}@qq.com";
	$md5   = md5( $email );

	$table = 'email_hash_' . ( hexdec( substr( $md5, 0, 10 ) ) ) % ( TABLE_NUM + 1 ) + 1;

	//$tables[ $table ][] = "('{$md5}', '{$i}')";
	$tables[ $table ][] = "{$md5},{$i}";

	// 每次缓存五千万条数据然后一次性入库
	if ( $j >= 49999999 || $i >= NUM - 1 ) {
		foreach ( $tables as $table => $value ) {
			//$sql = sprintf( 'insert into %s (md5, qq) values %s;', $table, join( ',', $value ) );

			$handle = fopen( '/data/sql/' . $table . '.csv', 'a+' );
			fwrite( $handle, join( PHP_EOL, $value ) . PHP_EOL );
			fclose( $handle );
			//mysqli_query( $conn, $sql );
		}

		unset( $tables );
		$tables = array();
		$j      = 0;
	} else {
		$j ++;
	}
}
$b = time();

echo $b - $a;
*/