<?php

/**
 * Replies Loop - Single Reply 回复循环 单回复
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>
<div class="bbp-reply-author-avatar">

    <?php bbp_reply_author_link( array( 'show_role' => true ) ); ?>

</div>
<div class="wp-reply-content">
<div id="comment" <?php bbp_reply_class(); ?>>


    <div class="bbp-reply-author-avatar wp-reply-content-avatar">

        <?php bbp_reply_author_link( array( 'show_role' => true ) ); ?>

    </div>

<main>
    <div class="bbp-reply-author ">

        <?php
        $user_id = get_post(bbp_get_reply_id())->post_author;
        $user_meta = get_user_meta($user_id, '', true);
        $user_name = @$user_meta['nickname'][0] ?: '已注销';
        $user_slug = @$user_meta['um_user_profile_url_slug_user_login'];
        if ( is_array( $user_slug ) && ! empty( $user_slug ) ) {
	        $user_slug = $user_slug[0];
        } else {
            $user_slug = '';
        }
        ?>

        <?php do_action( 'bbp_theme_before_reply_author_details' ); ?>

        <div class="bbp-reply-author-meta">
            <span class="bbp-reply-author-name"><a href="/user/<?php echo $user_slug; ?>" target="_blank"><?php echo $user_name; ?></a></span>
            <span class="bbp-reply-author-rule"><?php echo bbp_get_reply_author_role(['reply_id' => bbp_get_reply_id()]); ?></span>

            <?php if ( current_user_can( 'moderate', bbp_get_reply_id() ) ) : ?>

                <?php do_action( 'bbp_theme_before_reply_author_admin_details' ); ?>

                <div class="bbp-reply-ip"><?php bbp_author_ip( bbp_get_reply_id() ); ?></div>

                <?php do_action( 'bbp_theme_after_reply_author_admin_details' ); ?>

            <?php endif; ?>

            <span class="bbp-reply-post-date"><?php bbp_reply_post_date(); ?></span>

            <?php do_action( 'bbp_theme_after_reply_author_details' ); ?>

        </div>
    </div><!-- .bbp-reply-author -->

	<div class="bbp-reply-content heti" >

		<?php do_action( 'bbp_theme_before_reply_content' ); ?>

        <?php bbp_reply_content(); ?>

        <div id="post-<?php bbp_reply_id(); ?>" class="bbp-reply-header">
            <div class="bbp-meta">
	            <?php
	            if ( bbp_is_topic( bbp_get_reply_id() ) ) {
		            bbp_topic_tag_list();
	            }
	            ?>

                <?php if ( bbp_is_single_user_replies() ) : ?>

                    <span class="bbp-header">
				<?php esc_html_e( 'in reply to: ', 'bbpress' ); ?>
				<a class="bbp-topic-permalink" href="<?php bbp_topic_permalink( bbp_get_reply_topic_id() ); ?>"><?php bbp_topic_title( bbp_get_reply_topic_id() ); ?></a>
			</span>

                <?php endif; ?>
<section class="wp-toolbar">
                <a href="<?php bbp_reply_url(); ?>" class="bbp-reply-permalink">#<?php bbp_reply_id(); ?></a>

                <?php do_action( 'bbp_theme_before_reply_admin_links' ); ?>

                <?php bbp_reply_admin_links(); ?>

                <?php do_action( 'bbp_theme_after_reply_admin_links' ); ?>
</section>
            </div><!-- .bbp-meta -->
        </div><!-- #post-<?php bbp_reply_id(); ?> -->

		<?php do_action( 'bbp_theme_after_reply_content' ); ?>

	</div>

    <!-- .bbp-reply-content -->
</main>

    </section>

    <section class="theme-boxshadow bg-white bbp-footer">
    <div class="bbp-reply-author">

        <?php
        $user_id = get_post(bbp_get_reply_id())->post_author;
        $user_meta = get_user_meta($user_id, '', true);
        $user_name = @$user_meta['nickname'][0] ?: '已注销';
        $description = @$user_meta['description'][0] ?: '暂无个人简介~';
        $user_slug = @$user_meta['um_user_profile_url_slug_user_login'];
        if ( is_array( $user_slug ) && ! empty( $user_slug ) ) {
	        $user_slug = $user_slug[0];
        } else {
	        $user_slug = '';
        }
        ?>

        <?php do_action( 'bbp_theme_before_reply_author_details' ); ?>

        <div class="bbp-reply-author-avatar">

            <?php bbp_reply_author_link( array( 'show_role' => true ) ); ?>

        </div>


        <div class="bbp-reply-author-meta">
            <span class="bbp-reply-author-name"><a href="/user/<?php echo $user_slug; ?>" target="_blank"><?php echo $user_name; ?></a></span>
            <span class="bbp-reply-author-rule"><?php echo bbp_get_reply_author_role(['reply_id' => bbp_get_reply_id()]); ?></span>

            <?php if ( current_user_can( 'moderate', bbp_get_reply_id() ) ) : ?>

                <?php do_action( 'bbp_theme_before_reply_author_admin_details' ); ?>

                <div class="bbp-reply-ip"><?php bbp_author_ip( bbp_get_reply_id() ); ?></div>

                <?php do_action( 'bbp_theme_after_reply_author_admin_details' ); ?>

            <?php endif; ?>

            <span class="bbp-reply-author-description"><?php echo $description; ?></span>

            <?php do_action( 'bbp_theme_after_reply_author_details' ); ?>

        </div>
    </div><!-- .bbp-reply-author -->
    </section>

</div><!-- .reply -->
<div class="wp-reply">
