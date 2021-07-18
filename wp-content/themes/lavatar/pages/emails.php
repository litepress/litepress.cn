<?php
/**
 * Template name: 我的头像
 * Description: 我的头像和邮件列表管理
 */

use function LitePress\Lavatar\Inc\get_user_emails;
use function LitePress\Lavatar\Inc\handle_email_delete;

$user = wp_get_current_user();
if ( empty( $user->ID ) ) {
    require 'need-login.php';

	exit;
}


if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	/**
	 * TODO:缺Nonce
	 */

	$email = sanitize_email( $_POST['email'] );

	$r = handle_email_delete( $user->ID, $email );
	if ( is_wp_error( $r ) ) {
		add_filter( 'email_manage_message', function () use ( $r ) {
			return "<div class='message-error'>{$r->get_error_message()}</div>";
		} );
	} else {
		add_filter( 'email_manage_message', function () use ( $email ) {
			return "<div class='message-success'>你已成功删除 {$email}</div>";
		} );
	}
}

get_header();
?>
    <main class="main-body">
        <div class="container">
            <div class="row">
                <div class="message">
					<?php echo apply_filters( 'email_manage_message', null ); ?>
                </div>
                <section class="email-box wp-card p-3">
                    <h2>管理头像</h2>

                    <h3>管理你的邮箱</h3>
                    <ul class="email_list">
                        <li class="email selected">
							<?php echo $user->user_email; ?> <br/>
                            <ul class="tip">主邮箱用于账户登录，不可删除，如需更改请前往 <a
                                        href="<?php echo home_url( '/account' ) ?>">我的设置</a></ul>
                        </li>
						<?php foreach ( (array) get_user_emails( $user->ID ) as $email ): ?>
							<?php if ( $user->user_email !== $email ): ?>
                                <li class="email">
									<?php echo $email; ?> <br/>
                                    <a class="tip" href="#">删除该邮箱</a>
                                </li>
							<?php endif; ?>
						<?php endforeach; ?>
                    </ul>
                    <a class="btn btn-primary  my-3" href="<?php echo home_url( '/emails/new' ) ?>">添加新邮箱</a>

                    <h3>设置头像</h3>

					<?php
					$current_avatar = um_get_user_avatar_url( $user->ID );
					$timestamp      = time();
					?>

                    <form enctype="multipart/form-data" id="useravater" onsubmit="return false">
                        <div class="avater_field hidden" data-user_id="<?php echo $user->ID; ?>"
                             data-resize-nonce="<?php echo wp_create_nonce( 'um-frontend-nonce' ) ?>"
                             data-nonce="<?php echo wp_create_nonce( "um_upload_nonce-{$timestamp}" ); ?>"
                             data-icon="um-faicon-camera" data-set_id="0" data-set_mode="profile"
                             data-type="image" data-key="profile_photo" data-max_size="2048000"
                             data-max_size_error="图像太大！"
                             data-min_size_error="图像太小！" data-timestamp="<?php echo $timestamp; ?>"
                             data-extension_error="这不是有效的图像。"
                             data-allowed_types="gif,jpg,jpeg,png"
                             data-upload_text="在此处上传您的图像<small class=&quot;um-max-filesize&quot;>( 最大: <span>2MB</span> )</small>"
                             data-max_files_error="您只能上传一张图像" data-upload_help_text="" style="display: none;">上传
                        </div>
                        <div class="alert hide"></div>

                        <div class="form-group mt-3">
                            <div class="fileinput fileinput-new">
                                <div class="avatar-view position-relative">
                                    <label class="label" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                           title="点击更改头像">
                                        <div class=""><img class="rounded" id="avatar"
                                                           src="<?php echo $current_avatar; ?>" alt="avatar">
                                            <span class="avatar-tooltip"><i class="fad fa-camera-retro"></i></span>
                                        </div>
                                        <input type="file" class="sr-only" id="avatarInput" name="avatar_file"
                                               accept="image/*">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="avatar-modal" aria-hidden="true" data-bs-backdrop="static"
                             data-bs-keyboard="false" aria-labelledby="avatar-modal-label"
                             role="dialog" tabindex="-1" style="display: none;">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="avatar-modal-label">更换头像</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="avatar-body">
                                            <!-- Upload image and data -->
                                            <div class="alert hide"></div>
                                            <!-- Crop and preview -->
                                            <div class=" avatar-Crop">
                                                <div class="">
                                                    <div class="avatar-wrapper">
                                                        <img src="" class="cropper-hidden cropper-view">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary btn-block avatar-save" type="submit"><i
                                                    class="fa fa-save"></i> 保存修改
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

<?php
get_footer();