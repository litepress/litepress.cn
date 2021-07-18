<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Displays the Site Cleaning lower prompt.
 */
?>
<div id="wf-site-cleaning-bottom" class="wf-block wf-add-top-small wf-active">
	<div class="wf-block-content">
		<ul class="wf-block-list">
			<li>
				<div class="wf-flex-vertical">
					<h3 class="wf-center"><?php esc_html_e('Need help from the WordPress security experts?', 'wordfence'); ?></h3>
					<p class="wf-center wf-no-top"><?php echo wp_kses(__('Wordfence security analysts can help you tighten site security or remove an active infection for good. All security services include a detailed report and a <strong class="wf-blue">Wordfence Premium license, with a 1-year clean site guarantee.</strong>', 'wordfence'), array('strong'=>array('class'=>array()))); ?></p>
					<p class="wf-center wf-add-bottom">
						<a class="wf-btn wf-btn-default wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1scanLowerAd/site-security-audit/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Audit My Site Security', 'wordfence'); ?></a>
						&nbsp;&nbsp;&nbsp;
						<a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1scanLowerAd/wordfence-site-cleanings/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Clean My Hacked Site', 'wordfence'); ?></a>
					</p>
				</div>
			</li>
		</ul>
	</div>
</div>