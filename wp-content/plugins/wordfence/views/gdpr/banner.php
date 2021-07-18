<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the persistent banner.
 */
?>
<ul id="wf-gdpr-banner">
	<li><?php esc_html_e('Wordfence\'s terms of use and privacy policy have changed', 'wordfence'); ?></li>
	<li><a href="#" class="wf-btn wf-btn-default" id="wf-gdpr-review"><?php esc_html_e('Review', 'wordfence'); ?></a></li>
</ul>

<script type="application/javascript">
	(function($) {
		$(function() {
			$('#wf-gdpr-review').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var prompt = $('#wfTmpl_touppChangedModal').tmpl();
				var promptHTML = $("<div />").append(prompt).html();
				WFAD.colorboxHTML(WFAD.isSmallScreen ? '300px' : '800px', promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
					$('#wf-toupp-changed-cancel').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();

						WFAD.colorboxClose();
					});

					$('#wf-toupp-changed-agree').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();

						$('#wf-gdpr-banner').slideUp();
						$('.wf-toupp-required').removeClass('wf-toupp-required');
						WFAD.ajax('wordfence_recordTOUPP', {}, function(res) {
							//Do nothing
						});

						WFAD.colorboxClose();
					});
				}});
			});
		});
	})(jQuery);
</script>

<script type="text/x-jquery-template" id="wfTmpl_touppChangedModal">
<div class="wf-modal" id="wf-toupp-changed-modal">
	<div class="wf-modal-content">
		<p><?php echo wp_kses(__('We have updated our Terms of Use and Privacy Policy. To continue using Wordfence, you will need to review and accept the updated <a href="https://www.wordfence.com/terms-of-use/" target="_blank" rel="noopener noreferrer">Terms of Use</a> and <a href="https://www.wordfence.com/privacy-policy/" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></p>
		<?php if (wfConfig::get('isPaid')): ?><p><?php echo wp_kses(__('You can log in to <a href="https://www.wordfence.com/" target="_blank" rel="noopener noreferrer">wordfence.com</a> to accept the updated terms and privacy policy for all of your premium license keys at once.', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></p><?php endif; ?>
	</div>
	<div class="wf-modal-footer">
		<ul class="wf-flex-horizontal wf-full-width wf-flex-align-right">
			<li class="wf-padding-add-right"><a href="https://www.wordfence.com/help/general-data-protection-regulation/#agreement-to-new-terms-and-privacy-policies" class="wf-btn wf-btn-default" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence'); ?></a></li>
			<li><a href="#" class="wf-btn wf-btn-primary" id="wf-toupp-changed-agree"><?php esc_html_e('Agree', 'wordfence'); ?></a></li>
		</ul>
	</div>
</div>
</script>