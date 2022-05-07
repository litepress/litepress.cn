<?php

use function LitePress\User\Inc\login_by_user_id;

add_action( 'wp_loaded', function () {
	list( $uri ) = explode( '?', $_SERVER['REQUEST_URI'] );
	if ( '/user/oauth/callback/qq' === $uri ) {
		$qc           = new QC();
		$acs          = $qc->qq_callback();
		$openid       = $qc->get_openid();
		$qc           = new QC( $acs, $openid );
		$qq_user_info = $qc->get_user_info();

		if ( empty( $openid ) ) {
			echo 'Error!!!';
			exit;
		}

		if ( is_user_logged_in() ) {
			// 已登录状态下绑定 QQ 的行为视为绑定 QQ 号。
			$user_id = get_current_user_id();

			update_user_meta( $user_id, 'qq_openid', $openid );
			update_user_meta( $user_id, 'qq_nickname', $qq_user_info['nickname'] );

			echo <<<JS
<script>
window.opener.closeChildWindow();
</script>
JS;
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
				'type'        => 'qq',
				'qq_openid'   => $openid,
				'qq_nickname' => $qq_user_info['nickname'],
				'figureurl'   => $qq_user_info['figureurl_2'],
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
