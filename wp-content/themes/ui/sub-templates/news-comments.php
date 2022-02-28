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
        </ol>
        <!-- .comment-list -->

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
    <section class="comment-form-comment">
    <aside>
        <?php echo get_avatar( get_current_user_id(), 32 )?>
    </aside>
        <?php
        comment_form(
            array(
                'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title d-none">',
                'title_reply_after'  => '</h2>',
                'logged_in_as'  => '',
                'comment_field'        => '<textarea  class="form-control d-none" id="comment" name="comment"  maxlength="65525" ></textarea>
<section class="wang-editor">
                        <div id="editor-toolbar" class="editor-toolbar"></div>
                        <div id="editor-container" class="editor-container heti"></div>
</section>
 <button type="submit" id="submit" class="btn btn-primary mt-2"><i class="fad fa-paper-plane"></i> 提交评论</button>                   
                    ',
            )
        );
        ?>
    </section>
<!--        <form action="<?php /*echo esc_url( home_url( '/' ) ); */?>wp-comments-post.php" method="post" class="comment-form">
            <?php /*echo get_avatar( get_current_user_id(), 32 )*/?>
            <main class="comment-form-comment">
                <li class="form-floating w-100">
                    <textarea  class="form-control d-none" id="comment" name="comment"  maxlength="65525" ></textarea>
                </li>
                    <section class="wang-editor w-100">
                        <div id="editor-toolbar" class="editor-toolbar"></div>
                        <div id="editor-container" class="editor-container heti"></div>
                    </section>
<ul class="mt-2">
                <button type="submit" id="submit" class="btn btn-primary "><i class="fad fa-paper-plane"></i> 提交评论</button>
                <button type="submit" id="submit" class="btn btn-outline-primary mt-2"><i class="fa-duotone fa-xmark"></i> 取消</button>
</ul>
                </main>
            <?php
/*            echo get_comment_id_fields( $post->id );
            do_action( 'comment_form', $post->id );
            */?>
        </form>-->
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

