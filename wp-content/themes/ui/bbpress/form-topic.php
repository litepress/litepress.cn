<?php

/**
 * New/Edit Topic
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! bbp_is_single_forum() ) : ?>

    <secation id="bbpress-forums" class="bbpress-wrapper">

<?php endif; ?>

<?php if ( bbp_is_topic_edit() ) : ?>

	<?php bbp_topic_tag_list( bbp_get_topic_id() ); ?>

	<?php bbp_single_topic_description( array( 'topic_id' => bbp_get_topic_id() ) ); ?>

	<?php bbp_get_template_part( 'alert', 'topic-lock' ); ?>

<?php endif; ?>

<?php if ( bbp_current_user_can_access_create_topic_form() ) : ?>

    <div id="new-topic-<?php bbp_topic_id(); ?>" class="bbp-topic-form bg-white theme-boxshadow p-20">

        <form id="new-post" name="new-post" method="post">

			<?php do_action( 'bbp_theme_before_topic_form' ); ?>

            <fieldset class="bbp-form ">
                <!--标题删除-->
                <!--<legend>

					<?php
				if ( bbp_is_topic_edit() ) :
					printf( esc_html__( 'Now Editing &ldquo;%s&rdquo;', 'bbpress' ), bbp_get_topic_title() );
				else :
					( bbp_is_single_forum() && bbp_get_forum_title() )
						? printf( esc_html__( 'Create New Topic in &ldquo;%s&rdquo;', 'bbpress' ), bbp_get_forum_title() )
						: esc_html_e( 'Create New Topic', 'bbpress' );
				endif;
				?>

				</legend>-->
                <!--通知-->
				<?php do_action( 'bbp_theme_before_topic_form_notices' ); ?>

				<?php if ( ! bbp_is_topic_edit() && bbp_is_forum_closed() ) : ?>

                    <div class="bbp-template-notice">
                        <ul>
                            <li><?php esc_html_e( 'This forum is marked as closed to new topics, however your posting capabilities still allow you to create a topic.', 'bbpress' ); ?></li>
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

				<?php do_action( 'bbp_template_notices' ); ?>

                <div class="row">

					<?php bbp_get_template_part( 'form', 'anonymous' ); ?>

					<?php do_action( 'bbp_theme_before_topic_form_title' ); ?>
                    <!--表单标题-->
                    <div class="post-form-main fl-col col-xl-9">

                        <li class="form-floating">
                            <input type="text" class="form-control" id="bbp_topic_title" value="<?php bbp_form_topic_title(); ?>"  placeholder="在此输入标题……" size="40" name="bbp_topic_title"
                            maxlength="<?php bbp_title_max_length(); ?>" required="">
                            <label for="bbp_topic_title"><?php printf( esc_html__( 'Topic Title (Maximum Length: %d):', 'bbpress' ), bbp_get_title_max_length() ); ?></label><br/>
                            <div class="invalid-feedback">
                                必填项 *
                            </div>
                        </li>
						<?php do_action( 'bbp_theme_after_topic_form_title' ); ?>

						<?php do_action( 'bbp_theme_before_topic_form_content' ); ?>

						 <?php /*bbp_the_content( array( 'context' => 'topic' ) ); */?>
                        <textarea class="bbp-the-content wp-editor-area"  autocomplete="off" cols="40" name="bbp_topic_content" id="bbp_topic_content" aria-hidden="true" style="display: none;"></textarea>
                        <div class="d-none bbp_topic_content_hide"><?php bbp_topic_content(); ?></div>
                        <section class="wang-editor">
                            <div id="bbp-editor-toolbar" class="editor-toolbar"></div>
                            <div id="bbp-editor-container" style="height:301px" class="editor-container heti"></div>
                        </section>
						<?php do_action( 'bbp_theme_after_topic_form_content' ); ?>

						<?php if ( ! ( bbp_use_wp_editor() || current_user_can( 'unfiltered_html' ) ) ) : ?>

                            <p class="form-allowed-tags">
                                <label><?php printf( esc_html__( 'You may use these %s tags and attributes:', 'bbpress' ), '<abbr title="HyperText Markup Language">HTML</abbr>' ); ?></label><br/>
                                <code><?php bbp_allowed_tags(); ?></code>
                            </p>

						<?php endif; ?>

						<?php if ( bbp_allow_topic_tags() && current_user_can( 'assign_topic_tags', bbp_get_topic_id() ) ) : ?>

						<?php do_action( 'bbp_theme_before_topic_form_tags' ); ?>
                    </div>
                <!--表单标签-->
                    <div class="fl-col post-form-sidebar col-xl-3">
                        <p class="form-floating my-3  mt-xl-0">

                            <input class="form-control" type="text" value="<?php bbp_form_topic_tags(); ?>" size="40"
                                   name="bbp_topic_tags"
                                   id="bbp_topic_tags" <?php disabled( bbp_is_topic_spam() ); ?> placeholder="输入话题标签……" />
                            <label for="bbp_topic_tags"><?php esc_html_e( 'Topic Tags:', 'bbpress' ); ?></label>
                        </p>

						<?php do_action( 'bbp_theme_after_topic_form_tags' ); ?>

						<?php endif; ?>

						<?php if ( ! bbp_is_single_forum() ) : ?>

							<?php do_action( 'bbp_theme_before_topic_form_forum' ); ?>
                            <!--表单分类-->
                            <p class="pf-side-item">
                                <label for="bbp_forum_id"
                                       class="pf-side-label"><?php esc_html_e( 'Forum:', 'bbpress' ); ?></label><br/>
								<?php
								bbp_dropdown( array(
									'show_none' => esc_html__( '&mdash; No forum &mdash;', 'bbpress' ),
									'selected'  => bbp_get_form_topic_forum()
								) );
								?>
                            </p>

							<?php do_action( 'bbp_theme_after_topic_form_forum' ); ?>

						<?php endif; ?>

						<?php if ( current_user_can( 'moderate', bbp_get_topic_id() ) ) : ?>

							<?php do_action( 'bbp_theme_before_topic_form_type' ); ?>

                            <p class="pf-side-item">

                                <label class="pf-side-label"
                                       for="bbp_stick_topic"><?php esc_html_e( 'Topic Type:', 'bbpress' ); ?></label><br/>

								<?php bbp_form_topic_type_dropdown(); ?>

                            </p>

							<?php do_action( 'bbp_theme_after_topic_form_type' ); ?>

							<?php do_action( 'bbp_theme_before_topic_form_status' ); ?>

                            <p class="pf-side-item">

                                <label class="pf-side-label"
                                       for="bbp_topic_status"><?php esc_html_e( 'Topic Status:', 'bbpress' ); ?></label><br/>

								<?php bbp_form_topic_status_dropdown(); ?>

                            </p>

							<?php do_action( 'bbp_theme_after_topic_form_status' ); ?>

						<?php endif; ?>

						<?php if ( bbp_is_subscriptions_active() && ! bbp_is_anonymous() && ( ! bbp_is_topic_edit() || ( bbp_is_topic_edit() && ! bbp_is_topic_anonymous() ) ) ) : ?>

							<?php do_action( 'bbp_theme_before_topic_form_subscriptions' ); ?>

                            <p class="bbp_topic_subscription-btn">
                                <input class="form-check-input" name="bbp_topic_subscription"
                                       id="bbp_topic_subscription" type="checkbox"
                                       value="bbp_subscribe" <?php bbp_form_topic_subscribed(); ?> />

								<?php if ( bbp_is_topic_edit() && ( bbp_get_topic_author_id() !== bbp_get_current_user_id() ) ) : ?>

                                    <label for="bbp_topic_subscription"><?php esc_html_e( 'Notify the author of follow-up replies via email', 'bbpress' ); ?></label>

								<?php else : ?>

                                    <label for="bbp_topic_subscription"><?php esc_html_e( 'Notify me of follow-up replies via email', 'bbpress' ); ?></label>

								<?php endif; ?>
                            </p>

							<?php do_action( 'bbp_theme_after_topic_form_subscriptions' ); ?>

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


						<?php if ( bbp_allow_revisions() && bbp_is_topic_edit() ) : ?>

							<?php do_action( 'bbp_theme_before_topic_form_revisions' ); ?>

                            <fieldset class="bbp-form">
                                <legend>
                                    <input name="bbp_log_topic_edit" id="bbp_log_topic_edit" type="checkbox"
                                           value="1" <?php bbp_form_topic_log_edit(); ?> />
                                    <label for="bbp_log_topic_edit"><?php esc_html_e( 'Keep a log of this edit:', 'bbpress' ); ?></label><br/>
                                </legend>

                                <div>
                                    <label for="bbp_topic_edit_reason"><?php printf( esc_html__( 'Optional reason for editing:', 'bbpress' ), bbp_get_current_user_name() ); ?></label><br/>
                                    <input type="text" value="<?php bbp_form_topic_edit_reason(); ?>" size="40"
                                           name="bbp_topic_edit_reason" id="bbp_topic_edit_reason"/>
                                </div>
                            </fieldset>

							<?php do_action( 'bbp_theme_after_topic_form_revisions' ); ?>

						<?php endif; ?>

						<?php do_action( 'bbp_theme_before_topic_form_submit_wrapper' ); ?>

                        <div class="bbp-submit-wrapper">

							<?php do_action( 'bbp_theme_before_topic_form_submit_button' ); ?>
                            <button type="submit" id="bbp_reply_submit" name="bbp_reply_submit"
                                    class="button submit btn btn-primary"><i
                                        class="fad fa-paper-plane"></i><?php esc_html_e( 'Submit', 'bbpress' ); ?>
                            </button>

                        </div>
						<?php do_action( 'bbp_theme_after_topic_form_submit_button' ); ?>

                    </div>

					<?php do_action( 'bbp_theme_after_topic_form_submit_wrapper' ); ?>

                </div>

				<?php bbp_topic_form_fields(); ?>

            </fieldset>

			<?php do_action( 'bbp_theme_after_topic_form' ); ?>

        </form>
    </div>

<?php elseif ( bbp_is_forum_closed() ) : ?>

    <div id="forum-closed-<?php bbp_forum_id(); ?>" class="bbp-forum-closed">
        <div class="bbp-template-notice">
            <ul>
                <li><?php printf( esc_html__( 'The forum &#8216;%s&#8217; is closed to new topics and replies.', 'bbpress' ), bbp_get_forum_title() ); ?></li>
            </ul>
        </div>
    </div>

<?php else : ?>

    <div id="no-topic-<?php bbp_forum_id(); ?>" class="bbp-no-topic bg-white theme-boxshadow">
        <div class="bbp-template-notice">
            <ul>
                <li><?php is_user_logged_in()
						? esc_html_e( 'You cannot create new topics.', 'bbpress' )
						: esc_html_e( 'You must be logged in to create new topics.', 'bbpress' );
					?></li>
            </ul>
        </div>

		<?php if ( ! is_user_logged_in() ) : ?>

			<?php bbp_get_template_part( 'form', 'user-login' ); ?>

		<?php endif; ?>

    </div>

<?php endif; ?>

<?php if ( ! bbp_is_single_forum() ) : ?>


<?php endif;
