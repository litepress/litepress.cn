<?php

/**
 * New/Edit Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( bbp_is_reply_edit() ) : ?>

    <div id="bbpress-forums" class="bbpress-wrapper">

<?php endif; ?>

<?php if ( bbp_current_user_can_access_create_reply_form() ) : ?>

    <div id="new-reply-<?php bbp_topic_id(); ?>" class="bbp-reply-form">

        <form id="new-post" name="new-post" method="post">

			<?php do_action( 'bbp_theme_before_reply_form' ); ?>

            <fieldset class="bbp-form">
                <legend><?php printf( esc_html__( 'Reply To: %s', 'bbpress' ), ( bbp_get_form_reply_to() ) ? sprintf( esc_html__( 'Reply #%1$s in %2$s', 'bbpress' ), bbp_get_form_reply_to(), bbp_get_topic_title() ) : bbp_get_topic_title() ); ?></legend>

				<?php do_action( 'bbp_theme_before_reply_form_notices' ); ?>

				<?php if ( ! bbp_is_topic_open() && ! bbp_is_reply_edit() ) : ?>

                    <div class="bbp-template-notice">
                        <ul>
                            <li><?php esc_html_e( 'This topic is marked as closed to new replies, however your posting capabilities still allow you to reply.', 'bbpress' ); ?></li>
                        </ul>
                    </div>

				<?php endif; ?>

				<?php if ( ! bbp_is_reply_edit() && bbp_is_forum_closed() ) : ?>

                    <div class="bbp-template-notice">
                        <ul>
                            <li><?php esc_html_e( 'This forum is closed to new content, however your posting capabilities still allow you to post.', 'bbpress' ); ?></li>
                        </ul>
                    </div>

				<?php endif; ?>

				<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>

                    <div class="bbp-template-notice">
                        <ul>
                            <li><?php esc_html_e( 'Your account has the ability to post unrestricted HTML content.', 'bbpress' ); ?></li>
                        </ul>
                    </div>

				<?php endif; ?>
                <!--图片上传按钮-->
                <input id="upload_image" type="file" accept="image/*" multiple="multiple"/>
                <label for="upload_image" id="up_img_label"><i class="fa fa-image"></i> 上传图片</label> <span
                        id="image_url">编辑器升级中，暂由此处上传图片</span>
                <hr>
                <style type="text/css">
                    #upload_image {
                        display: none;
                    }

                    #up_img_label {
                        color: #fff;
                        background-color: #0274be;
                        border-radius: 5px;
                        display: inline-block;
                        padding: 5.2px;
                    }
                </style>
                <script type="text/javascript">
                    jQuery('#upload_image').change(function () {
                        for (var i = 0; i < this.files.length; i++) {
                            var f = this.files[i];
                            var formData = new FormData();
                            formData.append('upload_img_file', f);
                            jQuery.ajax({
                                async: true,
                                crossDomain: true,
                                url: '<?php echo rest_url( "upload_image/v1/upload" ); ?>',
                                type: 'POST',
                                processData: false,
                                contentType: false,
                                data: formData,
                                beforeSend: function () {
                                    jQuery('#up_img_label').html('<i class="fa fa-image"></i> 上传中...');
                                },
                                success: function (res) {
                                    if (res.code == 0) {
                                        var oInput = document.createElement('input');
                                        oInput.value = res.link;
                                        document.body.appendChild(oInput);
                                        oInput.select(); // 选择对象
                                        document.execCommand("Copy"); // 执行浏览器复制命令
                                        oInput.className = 'oInput';
                                        oInput.style.display = 'none';
                                        jQuery("#image_url").html('外链：' + res.link);
                                        jQuery("#image_url").css('font-size', '10px');
                                        alert('上传成功，链接已复制，请粘贴到图片区块！');
                                        jQuery("#up_img_label").html('<i class="fa fa-image"></i> 上传图片');
                                    } else {
                                        alert('上传出错，请刷新重试');
                                    }
                                },
                                error: function () {
                                    alert('上传出错，请刷新重试');
                                }
                            });
                        }
                    });
                </script>
				<?php do_action( 'bbp_template_notices' ); ?>

                <div>

					<?php bbp_get_template_part( 'form', 'anonymous' ); ?>

					<?php do_action( 'bbp_theme_before_reply_form_content' ); ?>

					<?php bbp_the_content( array( 'context' => 'reply' ) ); ?>

					<?php do_action( 'bbp_theme_after_reply_form_content' ); ?>

					<?php if ( ! ( bbp_use_wp_editor() || current_user_can( 'unfiltered_html' ) ) ) : ?>

                        <p class="form-allowed-tags">
                            <label><?php esc_html_e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:', 'bbpress' ); ?></label><br/>
                            <code><?php bbp_allowed_tags(); ?></code>
                        </p>

					<?php endif; ?>
                    <!--精简回复标签
					<?php if ( bbp_allow_topic_tags() && current_user_can( 'assign_topic_tags', bbp_get_topic_id() ) ) : ?>

						<?php do_action( 'bbp_theme_before_reply_form_tags' ); ?>

						<p>
							<label for="bbp_topic_tags"><?php esc_html_e( 'Tags:', 'bbpress' ); ?></label><br />
							<input type="text" value="<?php bbp_form_topic_tags(); ?>" size="40" name="bbp_topic_tags" id="bbp_topic_tags" <?php disabled( bbp_is_topic_spam() ); ?> />
						</p>

						<?php do_action( 'bbp_theme_after_reply_form_tags' ); ?>

					<?php endif; ?>
-->
					<?php if ( bbp_is_subscriptions_active() && ! bbp_is_anonymous() && ( ! bbp_is_reply_edit() || ( bbp_is_reply_edit() && ! bbp_is_reply_anonymous() ) ) ) : ?>

						<?php do_action( 'bbp_theme_before_reply_form_subscription' ); ?>

                        <p class="bbp_topic_subscription">

                            <input class="form-check-input" name="bbp_topic_subscription" checked
                                   id="bbp_topic_subscription" type="checkbox"
                                   value="bbp_subscribe"<?php bbp_form_topic_subscribed(); ?> />

							<?php if ( bbp_is_reply_edit() && ( bbp_get_reply_author_id() !== bbp_get_current_user_id() ) ) : ?>

                                <label for="bbp_topic_subscription"><?php esc_html_e( 'Notify the author of follow-up replies via email', 'bbpress' ); ?></label>

							<?php else : ?>

                                <label for="bbp_topic_subscription"><?php esc_html_e( 'Notify me of follow-up replies via email', 'bbpress' ); ?></label>

							<?php endif; ?>

                        </p>

						<?php do_action( 'bbp_theme_after_reply_form_subscription' ); ?>

					<?php endif; ?>

					<?php if ( bbp_is_reply_edit() ) : ?>

						<?php if ( current_user_can( 'moderate', bbp_get_reply_id() ) ) : ?>

							<?php do_action( 'bbp_theme_before_reply_form_reply_to' ); ?>

                            <p class="form-reply-to">
                                <label for="bbp_reply_to"><?php esc_html_e( 'Reply To:', 'bbpress' ); ?></label><br/>
								<?php bbp_reply_to_dropdown(); ?>
                            </p>

							<?php do_action( 'bbp_theme_after_reply_form_reply_to' ); ?>

							<?php do_action( 'bbp_theme_before_reply_form_status' ); ?>

                            <p>
                                <label for="bbp_reply_status"><?php esc_html_e( 'Reply Status:', 'bbpress' ); ?></label><br/>
								<?php bbp_form_reply_status_dropdown(); ?>
                            </p>

							<?php do_action( 'bbp_theme_after_reply_form_status' ); ?>

						<?php endif; ?>

						<?php if ( bbp_allow_revisions() ) : ?>

							<?php do_action( 'bbp_theme_before_reply_form_revisions' ); ?>

                            <fieldset class="bbp-form">
                                <legend>
                                    <input name="bbp_log_reply_edit" id="bbp_log_reply_edit" type="checkbox"
                                           value="1" <?php bbp_form_reply_log_edit(); ?> />
                                    <label for="bbp_log_reply_edit"><?php esc_html_e( 'Keep a log of this edit:', 'bbpress' ); ?></label><br/>
                                </legend>

                                <div>
                                    <label for="bbp_reply_edit_reason"><?php printf( esc_html__( 'Optional reason for editing:', 'bbpress' ), bbp_get_current_user_name() ); ?></label><br/>
                                    <input type="text" value="<?php bbp_form_reply_edit_reason(); ?>" size="40"
                                           name="bbp_reply_edit_reason" id="bbp_reply_edit_reason"/>
                                </div>
                            </fieldset>

							<?php do_action( 'bbp_theme_after_reply_form_revisions' ); ?>

						<?php endif; ?>

					<?php endif; ?>


                    <fieldset class="bbp-form bbp-upload">
                        <label class="pf-side-label"><?php _e( "Upload Attachments", "gd-bbpress-attachments" ); ?></label>
                        <div class="bbp-upload-body">
                            <div class="bbp-template-notice">
                                <p><?php
									$file_size = GDATTCore::instance()->get_file_size();
									$size      = $file_size < 1024 ? $file_size . " KB" : floor( $file_size / 1024 ) . " MB";

									printf( __( "Maximum file size allowed is %s.", "gd-bbpress-attachments" ), $size );

									?></p>
                            </div>
                            <p class="bbp-attachments-form">
                                <!--<label for="bbp_topic_tags">
            <?php _e( "Attachments", "gd-bbpress-attachments" ); ?>:
        </label><br/>-->

                                <button type="button" class="ant-btn btn btn-outline-primary"><i
                                            class="fas fa-cloud-upload"></i><span>点击上传</span><input
                                            class="bbp-upload-input" type="file" size="40" multiple="multiple"
                                            name="d4p_attachment[]" style="    opacity: 0;
    position: absolute;
    left: 0;
    top: 0;
    font-size: 18px;"></button>
                                <!--<a class="d4p-attachment-addfile" href="#"><?php _e( "Add another file", "gd-bbpress-attachments" ); ?></a>-->
                            <div class="fileerrorTip" style="padding-left:65px;"></div>
                            <div id="filename" style="line-height:70px"></div>

                            <div class="showFileName">
                                <ul></ul>
                            </div>

                            </p></div>
                    </fieldset>


					<?php do_action( 'bbp_theme_before_reply_form_submit_wrapper' ); ?>

                    <div class="bbp-submit-wrapper">

						<?php do_action( 'bbp_theme_before_reply_form_submit_button' ); ?>
                        <div class="submit btn btn-outline-primary "><i
                                    class="fad fa-times-circle"></i><?php bbp_cancel_reply_to_link(); ?></div>


                        <button type="submit" id="bbp_reply_submit" name="bbp_reply_submit"
                                class="button submit btn btn-primary"><i
                                    class="fad fa-paper-plane"></i><?php esc_html_e( 'Submit', 'bbpress' ); ?>
                        </button>

						<?php do_action( 'bbp_theme_after_reply_form_submit_button' ); ?>

                    </div>

					<?php do_action( 'bbp_theme_after_reply_form_submit_wrapper' ); ?>

                </div>

				<?php bbp_reply_form_fields(); ?>

            </fieldset>

			<?php do_action( 'bbp_theme_after_reply_form' ); ?>

        </form>
    </div>

<?php elseif ( bbp_is_topic_closed() ) : ?>

    <div id="no-reply-<?php bbp_topic_id(); ?>" class="bbp-no-reply">
        <div class="bbp-template-notice">
            <ul>
                <li><?php printf( esc_html__( 'The topic &#8216;%s&#8217; is closed to new replies.', 'bbpress' ), bbp_get_topic_title() ); ?></li>
            </ul>
        </div>
    </div>

<?php elseif ( bbp_is_forum_closed( bbp_get_topic_forum_id() ) ) : ?>

    <div id="no-reply-<?php bbp_topic_id(); ?>" class="bbp-no-reply">
        <div class="bbp-template-notice">
            <ul>
                <li><?php printf( esc_html__( 'The forum &#8216;%s&#8217; is closed to new topics and replies.', 'bbpress' ), bbp_get_forum_title( bbp_get_topic_forum_id() ) ); ?></li>
            </ul>
        </div>
    </div>

<?php else : ?>

    <div id="no-reply-<?php bbp_topic_id(); ?>" class="bbp-no-reply">
        <div class="bbp-template-notice">
            <ul>
                <li><?php is_user_logged_in()
						? esc_html_e( 'You cannot reply to this topic.', 'bbpress' )
						: esc_html_e( 'You must be logged in to reply to this topic.', 'bbpress' );
					?></li>
            </ul>
        </div>

		<?php if ( ! is_user_logged_in() ) : ?>

			<?php bbp_get_template_part( 'form', 'user-login' ); ?>

		<?php endif; ?>

    </div>

<?php endif; ?>

<?php if ( bbp_is_reply_edit() ) : ?>

    </div>


<?php endif;
