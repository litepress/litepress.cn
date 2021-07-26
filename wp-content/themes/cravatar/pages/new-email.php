<?php
/**
 * Template name: 添加新邮箱模板
 * Description: 添加新邮箱模板
 */

use function LitePress\Cravatar\Inc\handle_email_bind;
use function LitePress\Cravatar\Inc\has_email;
use function LitePress\Cravatar\Inc\send_email_for_bind_email;

$user = wp_get_current_user();
if ( empty( $user->ID ) ) {
	require 'need-login.php';

	exit;
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	$new_email = sanitize_email( $_POST['email'] );

	if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'new_email' ) ) {
		add_filter( 'new_email_error_message', function () {
			return '<div class="alert alert-warning"><i class="fad fa-times-circle"></i> 无效的请求</div>';
		} );
	} elseif ( has_email( $new_email ) ) {
		add_filter( 'new_email_error_message', function () {
			return '<div class="alert alert-warning"><i class="fad fa-times-circle"></i> 该邮箱已存在,请重新添加</div>';
		} );
	} else {
		send_email_for_bind_email( $new_email );

		add_filter( 'new_email_body', function () use ( $new_email ) {
			return <<<html
我们已经发送了一封激活邮件到{$new_email}，你需要点击邮件中的激活链接才可正式添加此邮箱。
html;
		} );
	}
} else if ( isset( $_GET['token'] ) ) {
	$data = get_transient( 'email_bind_' . $_GET['token'] );

	if ( ! isset( $data['user_id'] ) || ! isset( $data['address'] ) ) {
		add_filter( 'new_email_error_message', function () {
			return '<div class="alert alert-warning"><i class="fad fa-times-circle"></i> 无效的激活地址</div>';
		} );

		add_filter( 'new_email_body', '__return_null' );
	} else {
		handle_email_bind( $data['user_id'], $data['address'] );

		add_filter( 'new_email_body', function () use ( $data ) {
			$user             = new WP_User();
			$user->user_email = $data;
			do_action( 'lavatar-updated-email', $user );
			$avatar_manage_url = home_url( '/emails' );

			// 成功绑定后要清空瞬态
			delete_transient( 'email_bind_' . $_GET['token'] );

			return <<<html
你已成功添加新邮箱：{$data['address']}
<a href="{$avatar_manage_url}">点击返回我的头像管理</a>
html;
		} );
	}
}

get_header();
?>
    <main class="main-body">
    <div class="container">
    <div class="row">
    <section class="email-box wp-card p-3">
    <h2>添加新邮箱</h2>
    <div>
		<?php echo apply_filters( 'new_email_error_message', null ); ?>
    </div>


<?php
$nonce = wp_create_nonce( 'new_email' );
echo apply_filters( 'new_email_body', <<<html
<form method="post">
    <div class="form-floating mb-3">
    <input type="hidden" name="nonce" value="$nonce">
        <input class="form-control" type="email" name="email" style="max-width: 400px;"  placeholder="name@example.com">
        <label for="floatingInput">输入新的邮箱地址</label>
        <div id="emailHelp" class="form-text">我们将向你的新邮箱地址发送一份电子邮件，其中包含一个确认链接。只有点击此连接后新的邮箱才会生效。</div>
    </div>
    
    <button class="btn btn-primary" type="submit">添加</button>
    <a class="btn btn-outline-primary" href=".">取消</a>
    
</form>

html
); ?>
    </section>
    </div>
    </div>
    </main>
<?php
get_footer();