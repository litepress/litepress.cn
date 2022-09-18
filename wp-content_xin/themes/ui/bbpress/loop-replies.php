<?php

/**
 * Replies Loop 回复循环
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

do_action( 'bbp_template_before_replies_loop' ); ?>

<ul id="topic-<?php bbp_topic_id(); ?>-replies" class="forums bbp-replies">
    <section class="theme-boxshadow wp-banner bg-white">
    <h1 class="bbp-title"><?php the_title(); ?></h1>
        <?php bbp_single_topic_description(); ?>
        <hr class="dropdown-divider">

    <li class="bbp-body">

        <?php if ( bbp_thread_replies() ) : ?>

	        <?php wcy_bbp_list_replies(); ?>

        <?php else : ?>

            <?php while ( bbp_replies() ) : bbp_the_reply(); ?>

                <?php bbp_get_template_part( 'loop', 'single-reply' ); ?>

            <?php endwhile; ?>

        <?php endif; ?>

    </li><!-- .bbp-body -->


    <!-- .bbp-footer -->
</ul><!-- #topic-<?php bbp_topic_id(); ?>-replies -->

<?php do_action( 'bbp_template_after_replies_loop' );
