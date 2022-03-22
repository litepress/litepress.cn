<?php

use function LitePress\User\Inc\login_by_user_id;

add_action( 'wp_loaded', function () {
	list( $uri ) = explode( '?', $_SERVER['REQUEST_URI'] );
	if ( '/auth/oauth/callback/qq' === $uri ) {
		if ( is_user_logged_in() ) {
			echo '你已经处于登录状态，平台不允许重复登录，请刷新页面后查看。';
			exit;
		}

		$qc = new QC();
		$qc->qq_callback();
		$openid = $qc->get_openid();

		if ( empty( $openid ) ) {
			echo 'Error!!!';
			exit;
		}

		// 尝试直接使用 此 openid 登录
		global $wpdb;
		$r = $wpdb->get_row(
			$wpdb->prepare( 'select user_id from wp_usermeta where meta_key=%s and meta_value=%s', "qq_openid", $openid )
		);

		// 如果账号不存在就生成账号绑定 Token，然后要求前端跳转页面
		if ( empty( $r ) ) {
			$token = md5( rand( 100, 999 ) + time() );
			set_transient( "lpcn_user_bind_$token", array(
				'type'      => 'qq',
				'qq_openid' => $openid,
			), 300 );

			echo <<<JS
<script>
window.opener.closeChildWindowAndBindUser('{$token}');
</script>
JS;
			exit;
		}

		// 登录成功
		login_by_user_id( $r->user_id );
		echo <<<JS
<script>
window.opener.closeChildWindow();
</script>
JS;
	}
} );
