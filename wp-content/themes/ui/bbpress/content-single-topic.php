<?php

/**
 * Single Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>

<div id="bbpress-forums" class="bbpress-wrapper">


	<?php bbp_breadcrumb(); ?>

<div class="operation-sticker">
        <div class="menu">
          <!--<a class="wp-thumb-up"  href="javascript:;" data-html="点赞">
              <i class="fa fa-thumbs-up" aria-hidden="true"></i>
            <span class="count vote-count mt-2">点赞</span>
          </a>-->

          <a class="wp-favorite"   href="javascript:;" data-html="收藏">
                 <i class="fa fa-heart" aria-hidden="true"></i>
                <span class="fs-small mt-2">收藏</span>
          </a>
          <a class="wp-subscribe"   href="javascript:;" data-html="关注">
                 <i class="fa fa-eye" aria-hidden="true"></i>
                <span class="fs-small mt-2">关注</span>
          </a>
          <a class="wp-replies"   href="#replies" data-html="评论">
                 <i class="fa fa-comments" aria-hidden="true"></i>
                <span class="fs-small mt-2">回复</span>
          </a>
    </div>
    <div class="d-none">
        <?php bbp_topic_subscription_link(); ?>
        <?php bbp_topic_favorite_link(); ?>
    </div>
</div>
	<?php do_action( 'bbp_template_before_single_topic' ); ?>

	<?php if ( post_password_required() ) : ?>

		<?php bbp_get_template_part( 'form', 'protected' ); ?>

	<?php else : ?>





		<?php if ( bbp_show_lead_topic() ) : ?>

    <?php bbp_get_template_part( 'content', 'single-topic-lead' ); ?>

		<?php endif; ?>

		<?php if ( bbp_has_replies() ) : ?>

		<!--	<?php bbp_get_template_part( 'pagination', 'replies' ); ?>-->





			<?php bbp_get_template_part( 'loop',       'replies' ); ?>

<div id="replies" name="replies">

		<div class="bottom-pagination" id="">	<?php bbp_get_template_part( 'pagination', 'replies' ); ?></div>

		<?php endif; ?>
    <section class="theme-boxshadow  bg-white">
		<?php bbp_get_template_part( 'form', 'reply' ); ?>

    </section>

	<?php endif; ?>

	<?php bbp_get_template_part( 'alert', 'topic-lock' ); ?>

	<?php do_action( 'bbp_template_after_single_topic' ); ?>


    </div>
