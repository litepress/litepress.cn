<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains comments and the comment form.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

/*
 * If the current post is protected by a password and the visitor has not yet
 * entered the password we will return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">


	<?php if ( have_comments() ) : ?>

        <header class="  d-flex aside-header align-items-center comments-title">
            <div class="me-2 wp-icon">
                <i class="fad fa-comments"></i></div>
            <span>
            <?php
            printf( _nx( '1 条回复', '%1$s 条回复', get_comments_number(), 'comments title', 'twentythirteen' ),
	            number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
            ?></span></header>
        <ol class="comment-list">
			<?php wp_list_comments( array(
				'callback' => 'bootstrapwp_comment',
			) ); ?>
        </ol><!-- .comment-list -->

		<?php
		// Are there comments to navigate through?
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
			?>
            <nav class="navigation comment-navigation" role="navigation">
                <h1 class="screen-reader-text section-heading"><?php _e( 'Comment navigation', 'twentythirteen' ); ?></h1>
                <div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'twentythirteen' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'twentythirteen' ) ); ?></div>
            </nav><!-- .comment-navigation -->
		<?php endif; // Check for comment navigation ?>

		<?php if ( ! comments_open() && get_comments_number() ) : ?>
            <p class="no-comments"><?php _e( 'Comments are closed.', 'twentythirteen' ); ?></p>
		<?php endif; ?>

	<?php endif; // have_comments() ?>

	<?php if ( is_user_logged_in() ): ?>
		<?php comment_form();?>
	<?php else: ?>

        <section class="comment_form_sign">
            <p class="text-center"> 加入 <?php echo get_bloginfo( 'name' ); ?> ，参与知识分享与交流
                <br>
                <a href="https://litepress.cn/login"><i class="fas fa-sign-in-alt"
                                                        aria-hidden="true"></i> 登录</a> 或 <a
                        href="https://litepress.cn/register"><i class="fas fa-user-plus"
                                                                aria-hidden="true"></i> 注册</a> 进行评论
                <br>
                <a class="btn btn-primary mt-2" href="https://litepress.cn/login">立即加入</a>
            </p>
        </section>

	<?php endif; ?>


</div><!-- #comments -->

