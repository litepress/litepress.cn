<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the scan activity log and its controls.
 *
 * Expects $scanner.
 *
 * @var wfScanner $scanner The scanner state.
 */
?>
<div class="wf-alert wf-alert-danger" id="wf-scan-failed" style="display: none;">
	
</div>
<ul class="wf-flex-horizontal wf-flex-vertical-xs wf-flex-full-width wf-no-top wf-no-bottom">
	<li id="wf-scan-last-status"></li>
	<li id="wf-scan-activity-log-controls"><a href="#" id="wf-scan-email-activity-log"><?php echo wp_kses(__('Email<span class="wf-hidden-xs"> activity</span> log', 'wordfence'), array('span'=>array('class'=>array()))); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo esc_attr(wfUtils::getSiteBaseURL()); ?>?_wfsf=viewActivityLog&amp;nonce=<?php echo esc_attr(wp_create_nonce('wp-ajax')); ?>" id="wf-scan-full-activity-log" target="_blank"><?php echo wp_kses(__('View<span class="wf-hidden-xs"> full</span> log', 'wordfence'), array('span'=>array('class'=>array()))); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" id="wf-scan-toggle-activity-log" class="<?php echo (wfPersistenceController::shared()->isActive('wf-scan-activity-log') ? 'wf-active' : '') ?>"><span class="wf-scan-activity-log-visible"><?php esc_html_e('Hide log', 'wordfence'); ?></span><span class="wf-scan-activity-log-hidden"><?php esc_html_e('Show log', 'wordfence'); ?></span></a></li>
</ul>
<div id="wf-scan-running-bar" style="<?php if (!$scanner->isRunning()) { echo 'display: none;'; } ?>"><div id="wf-scan-running-bar-pill"></div></div>
<ul id="wf-scan-activity-log" class="<?php echo (wfPersistenceController::shared()->isActive('wf-scan-activity-log') ? ' wf-active' : '') ?>"></ul>
<script type="application/javascript">
	(function($) {
		$(function() {
			$('#wf-scan-email-activity-log').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				WFAD.emailActivityLog();
			});

			$('#wf-scan-toggle-activity-log').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var isActive = $('#wf-scan-activity-log').hasClass('wf-active');
				if (isActive) {
					$('#wf-scan-activity-log').slideUp({
						always: function() {
							$('#wf-scan-activity-log').removeClass('wf-active');
							$('#wf-scan-toggle-activity-log').removeClass('wf-active');
						}
					});
				}
				else {
					$('#wf-scan-activity-log').slideDown({
						always: function() {
							$('#wf-scan-activity-log').addClass('wf-active');
							$('#wf-scan-toggle-activity-log').addClass('wf-active');
						}
					});
				}

				WFAD.ajax('wordfence_saveDisclosureState', {name: 'wf-scan-activity-log', state: !isActive}, function() {});
			});
		});
	})(jQuery);
</script>