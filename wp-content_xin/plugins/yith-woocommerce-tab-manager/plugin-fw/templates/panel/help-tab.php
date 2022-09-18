<?php
/**
 * The Template for displaying the Help tab.
 *
 * @var array  $options         Array of options.
 * @var string $current_tab     The current tab.
 * @var string $current_sub_tab The current sub-tab.
 * @var array  $latest_articles Latest HC articles.
 * @var bool   $is_extended     Whether current plugin is an Extended version.
 * @var bool   $is_premium      Whether current plugin is a Premium version.
 *
 * @package YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$current_locale       = substr( get_user_locale(), 0, 2 );
$the_title            = $options['title'];
$the_description      = $options['description'];
$show_articles        = $options['show_hc_articles'] && ! empty( $latest_articles );
$show_submit_ticket   = $options['show_submit_ticket'] && $options['submit_ticket_url'];
$has_video            = $options['main_video'] && ! empty( $options['main_video']['url'] );
$show_view_all_faq    = ! ! $options['hc_url'];
$has_any_playlist     = ! ! $options['playlists'];
$has_additional_links = $has_any_playlist || ! ! $options['doc_url'] || $show_view_all_faq;
$has_default_playlist = $options['playlists'] && ! empty( $options['playlists'] );

// search for correct video url.
$video_url = false;

if ( $has_video ) {
	if ( is_array( $options['main_video']['url'] ) ) {
		if ( ! empty( $options['main_video']['url'][ $current_locale ] ) ) {
			$video_url = $options['main_video']['url'][ $current_locale ];
		} elseif ( ! empty( $options['main_video']['url']['en'] ) ) {
			$video_url = $options['main_video']['url']['en'];
		}
	} else {
		$video_url = $options['main_video']['url'];
	}
}

// search for correct playlist.
$default_playlist = false;

if ( $has_default_playlist ) {
	if ( is_array( $options['playlists'] ) ) {
		if ( ! empty( $options['playlists'][ $current_locale ] ) ) {
			$default_playlist = $options['playlists'][ $current_locale ];
		} elseif ( ! empty( $options['playlists']['en'] ) ) {
			$default_playlist = $options['playlists']['en'];
		}
	} else {
		$default_playlist = $options['playlists'];
	}
}
?>

<div id='yith_plugin_fw_panel_help_tab' class='yith-plugin-fw-panel-help-tab-container <?php echo esc_attr( $is_extended ? 'extended' : '' ); ?>'>
	<div class="yith-plugin-fw-panel-help-tab-content">
		<?php if ( $the_title ) : ?>
			<h2 class="yith-plugin-fw-panel-help-tab-title"><?php echo wp_kses_post( $the_title ); ?></h2>
		<?php endif; ?>

		<?php if ( $the_description ) : ?>
			<p class="yith-plugin-fw-panel-tab-description">
				<?php echo wp_kses_post( $the_description ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $is_premium && ( $has_video || $has_additional_links ) ) : ?>
			<div class="row">
				<?php if ( $video_url ) : ?>
					<div class="yith-plugin-fw-help-tab-video <?php echo $has_additional_links ? 'column-left' : 'full-width'; ?>">
						<?php if ( isset( $options['main_video']['desc'] ) ) : ?>
							<p class="video-description"><?php echo wp_kses_post( $options['main_video']['desc'] ); ?></p>
						<?php endif; ?>

						<div class="video-container">
							<iframe src="<?php echo esc_url( $video_url ); ?>"></iframe>
						</div>

						<?php if ( $has_any_playlist ) : ?>
							<div class="video-caption">
								<?php if ( $default_playlist ) : ?>
									<p>
										<?php
										// translators: 1. Url to EN playlist.
										echo wp_kses_post( sprintf( _x( 'Check the full <a href="%s" target="_blank">Playlist on Youtube</a> to learn more >', 'Help tab view all video link', 'yith-plugin-fw' ), $default_playlist ) );
										?>
									</p>
								<?php endif; ?>

								<p>
									<b>
										<?php echo esc_html_x( 'Videos are also available in:', 'Help tab Watch Videotutorials link', 'yith-plugin-fw' ); ?>
									</b>
									<?php $first = true; ?>
									<?php foreach ( $options['playlists'] as $lang => $url ) : ?>
										<?php
										if ( $url === $default_playlist ) {
												continue;
										}
										?>
										<?php if ( ! $first ) : ?>
											<span class="separator">|</span>
										<?php endif; ?>

										<a target="_blank" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( yit_get_language_from_locale( $lang, true ) ); ?></a>

										<?php $first = false; ?>
									<?php endforeach; ?>
								</p>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $has_additional_links ) : ?>
					<ul class="yith-plugin-fw-help-tab-actions <?php echo $video_url ? 'column-right' : 'full-width'; ?>">

						<?php if ( $options['doc_url'] ) : ?>
							<li class="read-documentation box-with-shadow">
								<a target="_blank" href="<?php echo esc_url( $options['doc_url'] ); ?>">
									<h4>
										<?php echo esc_html_x( 'Read the documentation', 'Help tab Read Documentation link', 'yith-plugin-fw' ); ?>
									</h4>
									<p class="description">
										<?php echo esc_html_x( 'to learn how the plugin works from the basics.', 'Help tab Read Documentation link', 'yith-plugin-fw' ); ?>
									</p>
								</a>
							</li>
						<?php endif; ?>

						<?php if ( $default_playlist ) : ?>
							<li class="watch-videotutorials box-with-shadow">
								<a target="_blank" href="<?php echo esc_url( $default_playlist ); ?>">
									<h4>
										<?php echo esc_html_x( 'Watch our video tutorials', 'Help tab Watch video tutorials link', 'yith-plugin-fw' ); ?>
									</h4>
									<p class="description">
										<?php echo esc_html_x( 'to see some helpful use cases.', 'Help tab Watch video tutorials link', 'yith-plugin-fw' ); ?>
									</p>
								</a>
							</li>
						<?php endif; ?>

						<?php if ( $show_view_all_faq ) : ?>
							<li class="check-faqs box-with-shadow">
								<a target="_blank" href="<?php echo esc_url( $options['hc_url'] ); ?>">
									<h4>
										<?php echo esc_html_x( 'Check the FAQs', 'Help tab view FAQs link', 'yith-plugin-fw' ); ?>
									</h4>
									<p class="description">
										<?php echo esc_html_x( 'to find answers to your doubts.', 'Help tab view FAQs link', 'yith-plugin-fw' ); ?>
									</p>
								</a>
							</li>
						<?php endif; ?>

					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $is_premium && ( $show_articles || $show_submit_ticket ) ) : ?>
			<div class="row">
				<?php if ( $show_articles ) : ?>
					<div class="yith-plugin-fw-hc-articles <?php echo $show_submit_ticket ? 'column-left' : 'full-width'; ?>">
						<h3 class="yith-plugin-fw-hc-articles-title"><?php echo esc_html_x( 'Last FAQs in our Help Center', 'Help tab FAQ title', 'yith-plugin-fw' ); ?></h3>

						<ul class="yith-plugin-fw-hc-articles-list">
							<?php foreach ( $latest_articles as $article ) : ?>
								<li>
									<a target="_blank" href="<?php echo esc_url( $article['url'] ); ?>">
										<?php echo esc_html( $article['title'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>

						<?php if ( $show_view_all_faq ) : ?>
							<a target="_blank" class="button button-secondary" href="<?php echo esc_url( $options['hc_url'] ); ?>">
								<?php echo esc_html_x( 'View all FAQs >', 'Help tab FAQ link', 'yith-plugin-fw' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_submit_ticket ) : ?>
					<div class="yith-plugin-fw-submit-ticket <?php echo $show_articles ? 'column-right' : 'full-width'; ?>">
						<div class="box-with-shadow">
							<h3><?php echo esc_html_x( 'Need help?', 'Help tab submit ticket title', 'yith-plugin-fw' ); ?></h3>
							<p>
								<?php
									echo esc_html_x(
										'If you are experiencing any technical issues, ask for help from our developers. Submit a ticket through our support desk and we will help you as soon as possible.',
										'Help tab submit ticket description',
										'yith-plugin-fw'
									);
								?>
							</p>
							<a target="_blank" href="<?php echo esc_url( $options['submit_ticket_url'] ); ?>" class="yit-plugin-fw-submit-ticket-button button button-primary">
								<?php echo esc_html_x( 'Submit a ticket', 'Help tab submit ticket button', 'yith-plugin-fw' ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $is_extended ) : ?>
			<div class="row">
				<?php if ( $options['doc_url'] ) : ?>
					<div class="box-with-image">
						<img src="<?php echo esc_url( YIT_CORE_PLUGIN_URL ); ?>/assets/images/help-tab/documentation.svg" alt="<?php echo esc_attr_x( 'Read the plugin documentation', 'Help tab documentation', 'yith-plugin-fw' ); ?>">
						<div class="box-content">
							<h3><?php echo esc_html_x( 'Read the plugin documentation', 'Help tab documentation', 'yith-plugin-fw' ); ?></h3>
							<p>
								<?php echo esc_html_x( 'to learn how it works from the basics.', 'Help tab documentation', 'yith-plugin-fw' ); ?>
							</p>
							<a target="_blank" href="<?php echo esc_url( $options['doc_url'] ); ?>" class="button yith-plugin-fw__button--secondary">
								<?php echo esc_html_x( 'Read the plugin documentation', 'Help tab documentation', 'yith-plugin-fw' ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $show_submit_ticket ) : ?>
					<div class="box-with-image">
						<img src="<?php echo esc_url( YIT_CORE_PLUGIN_URL ); ?>/assets/images/help-tab/support-desk.svg" alt="<?php echo esc_attr_x( 'Need some help?', 'Help tab support', 'yith-plugin-fw' ); ?>">
						<div class="box-content">
							<h3><?php echo esc_html_x( 'Need some help?', 'Help tab support', 'yith-plugin-fw' ); ?></h3>
							<p>
								<b><?php echo esc_html_x( 'From DIY to full-service help', 'Help tab support', 'yith-plugin-fw' ); ?></b>
							</p>
							<p>
								<?php echo esc_html_x( 'Call or chat 24/7 with our support agents, or let our experts build your site.', 'Help tab support', 'yith-plugin-fw' ); ?>
							</p>
							<a href="<?php echo esc_url( $options['submit_ticket_url'] ); ?>" class="yit-plugin-fw-submit-ticket-button button yith-plugin-fw__button--secondary">
								<?php echo esc_html_x( 'Yes, I need help', 'Help tab support', 'yith-plugin-fw' ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
