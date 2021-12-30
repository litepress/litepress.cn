<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
?>
<ul id="wf-option-wafRules" class="wf-option wf-flex-vertical wf-flex-align-left">
	<li class="wf-option-title"><strong><?php esc_html_e('Rules', 'wordfence'); ?></strong> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_RULES); ?>"  target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a></li>
	<li class="wf-option-subtitle"><?php echo ($firewall->isSubDirectoryInstallation() ? esc_html__('You are currently running the WAF from another WordPress installation. These rules can be disabled or enabled once you configure the firewall to run correctly on this site.', 'wordfence') : ''); ?></li>
	<li id="waf-rules-wrapper" class="wf-add-top"></li>
	<?php if (!WFWAF_SUBDIRECTORY_INSTALL): ?>
	<li id="waf-rules-manual-update">
		<ul class="wf-option wf-option-footer wf-padding-no-bottom">
			<li><a class="wf-btn wf-btn-default waf-rules-refresh" href="#" role="button"><?php esc_html_e('Manually Refresh Rules', 'wordfence'); ?></a>&nbsp;&nbsp;</li>
			<li class="wf-padding-add-top-xs-small"><em id="waf-rules-next-update"></em></li>
		</ul>
		<script type="application/javascript">
			(function($) {
				$('.waf-rules-refresh').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					WFAD.wafUpdateRules();
				});
			})(jQuery);
		<?php
		try {
			$lastUpdated = wfWAF::getInstance()->getStorageEngine()->getConfig('rulesLastUpdated', null, 'transient');
			
			$nextUpdate = PHP_INT_MAX;
			$cron = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('cron', null, 'livewaf');
			if (is_array($cron)) {
				/** @var wfWAFCronEvent $event */
				foreach ($cron as $index => $event) {
					if ($event instanceof wfWAFCronFetchRulesEvent) {
						$event->setWaf(wfWAF::getInstance());
						if (!$event->isInPast()) {
							$nextUpdate = min($nextUpdate, $event->getFireTime());
						}
					}
				}
			}
		}
		catch (wfWAFStorageFileException $e) {
			error_log($e->getMessage());
		}
		catch (wfWAFStorageEngineMySQLiException $e) {
			error_log($e->getMessage());
		}
		if (!empty($lastUpdated)): ?>
			var lastUpdated = <?php echo (int) $lastUpdated ?>;
			WFAD.renderWAFRulesLastUpdated(new Date(lastUpdated * 1000));
		<?php endif ?>
		
		<?php if ($nextUpdate < PHP_INT_MAX): ?>
			var nextUpdate = <?php echo (int) $nextUpdate ?>;
			WFAD.renderWAFRulesNextUpdate(new Date(nextUpdate * 1000));
		<?php endif ?>
		</script>
	</li>
	<?php endif ?>
</ul>