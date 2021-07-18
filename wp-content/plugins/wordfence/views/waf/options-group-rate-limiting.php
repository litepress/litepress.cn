<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the Rate Limiting group.
 *
 * Expects $firewall, $waf, and $stateKey.
 *
 * @var wfFirewall $firewall
 * @var wfWAF $waf
 * @var string $stateKey The key under which the collapse state is stored.
 * @var bool $collapseable If defined, specifies whether or not this grouping can be collapsed. Defaults to true.
 */

$config = $waf->getStorageEngine();

if (!isset($collapseable)) {
	$collapseable = true;
}
?>
<div class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-block<?php if (!$collapseable) { echo ' wf-always-active'; } else { echo (wfPersistenceController::shared()->isActive($stateKey) ? ' wf-active' : ''); } ?>" data-persistence-key="<?php echo esc_attr($stateKey); ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Rate Limiting', 'wordfence'); ?></strong>
					</div>
					<?php if ($collapseable): ?><div class="wf-block-header-action"><div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive($stateKey) ? 'true' : 'false'); ?>" tabindex="0"></div></div><?php endif; ?>
				</div>
			</div>
			<div class="wf-block-content">
				<ul class="wf-block-list">
					<li>
						<?php
						echo wfView::create('options/option-switch', array(
							'optionName' => 'firewallEnabled',
							'value' => wfConfig::get('firewallEnabled') ? '1': '0',
							'title' => __('Enable Rate Limiting and Advanced Blocking', 'wordfence'),
							'subtitle' => __('NOTE: This checkbox enables ALL blocking/throttling functions including IP, country and advanced blocking, and the "Rate Limiting Rules" below.', 'wordfence'),
							'states' => array(
								array('value' => '0', 'label' => __('Off', 'wordfence')),
								array('value' => '1', 'label' => __('On', 'wordfence')),
							),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_ENABLE_ADVANCED_BLOCKING),
							'noSpacer' => true,
							'alignment' => 'wf-right',
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-select', array(
							'selectOptionName' => 'neverBlockBG',
							'selectOptions' => array(
								array('value' => 'neverBlockVerified', 'label' => __('Verified Google crawlers will not be rate-limited', 'wordfence')),
								array('value' => 'neverBlockUA', 'label' => __('Anyone claiming to be Google will not be rate-limited', 'wordfence')),
								array('value' => 'treatAsOtherCrawlers', 'label' => __('Treat Google like any other Crawler', 'wordfence')),
							),
							'selectValue' => wfConfig::get('neverBlockBG'),
							'title' => __('How should we treat Google\'s crawlers', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_GOOGLE_ACTION),
						))->render();
						?>
					</li>
					<?php
					$rateOptions = array(
						array('value' => 'DISABLED', 'label' => __('Unlimited', 'wordfence')),
						array('value' => 1920, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 1920)),
						array('value' => 960, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 960)),
						array('value' => 480, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 480)),
						array('value' => 240, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 240)),
						array('value' => 120, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 120)),
						array('value' => 60, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 60)),
						array('value' => 30, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 30)),
						array('value' => 15, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 15)),
						array('value' => 10, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 10)),
						array('value' => 5, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 5)),
						array('value' => 4, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 4)),
						array('value' => 3, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 3)),
						array('value' => 2, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 2)),
						array('value' => 1, 'label' => sprintf(/* translators: Number of HTTP requests. */__('%d per minute', 'wordfence'), 1)),
					);
					$actionOptions = array(
						array('value' => 'throttle', 'label' => __('throttle it', 'wordfence')),
						array('value' => 'block', 'label' => __('block it', 'wordfence')),
					);
					?>
					<li>
						<?php
						echo wfView::create('waf/option-rate-limit', array(
							'toggleOptionName' => 'maxGlobalRequests_enabled',
							'toggleValue' => !!wfConfig::get('maxGlobalRequests_enabled') ? 1 : 0,
							'rateOptionName' => 'maxGlobalRequests',
							'rateOptions' => $rateOptions,
							'rateValue' => wfConfig::get('maxGlobalRequests'),
							'lowValue' => 120,
							'actionOptionName' => 'maxGlobalRequests_action',
							'actionOptions' => $actionOptions,
							'actionValue' => wfConfig::get('maxGlobalRequests_action'),
							'title' => __('If anyone\'s requests exceed', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_ANY),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('waf/option-rate-limit', array(
							'toggleOptionName' => 'maxRequestsCrawlers_enabled',
							'toggleValue' => !!wfConfig::get('maxRequestsCrawlers_enabled') ? 1 : 0,
							'rateOptionName' => 'maxRequestsCrawlers',
							'rateOptions' => $rateOptions,
							'rateValue' => wfConfig::get('maxRequestsCrawlers'),
							'lowValue' => 120,
							'actionOptionName' => 'maxRequestsCrawlers_action',
							'actionOptions' => $actionOptions,
							'actionValue' => wfConfig::get('maxRequestsCrawlers_action'),
							'title' => __('If a crawler\'s page views exceed', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_CRAWLER),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('waf/option-rate-limit', array(
							'toggleOptionName' => 'max404Crawlers_enabled',
							'toggleValue' => !!wfConfig::get('max404Crawlers_enabled') ? 1 : 0,
							'rateOptionName' => 'max404Crawlers',
							'rateOptions' => $rateOptions,
							'rateValue' => wfConfig::get('max404Crawlers'),
							'lowValue' => 60,
							'actionOptionName' => 'max404Crawlers_action',
							'actionOptions' => $actionOptions,
							'actionValue' => wfConfig::get('max404Crawlers_action'),
							'title' => __('If a crawler\'s pages not found (404s) exceed', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_CRAWLER_404),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('waf/option-rate-limit', array(
							'toggleOptionName' => 'maxRequestsHumans_enabled',
							'toggleValue' => !!wfConfig::get('maxRequestsHumans_enabled') ? 1 : 0,
							'rateOptionName' => 'maxRequestsHumans',
							'rateOptions' => $rateOptions,
							'rateValue' => wfConfig::get('maxRequestsHumans'),
							'lowValue' => 120,
							'actionOptionName' => 'maxRequestsHumans_action',
							'actionOptions' => $actionOptions,
							'actionValue' => wfConfig::get('maxRequestsHumans_action'),
							'title' => __('If a human\'s page views exceed', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_HUMAN),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('waf/option-rate-limit', array(
							'toggleOptionName' => 'max404Humans_enabled',
							'toggleValue' => !!wfConfig::get('max404Humans_enabled') ? 1 : 0,
							'rateOptionName' => 'max404Humans',
							'rateOptions' => $rateOptions,
							'rateValue' => wfConfig::get('max404Humans'),
							'lowValue' => 60,
							'actionOptionName' => 'max404Humans_action',
							'actionOptions' => $actionOptions,
							'actionValue' => wfConfig::get('max404Humans_action'),
							'title' => __('If a human\'s pages not found (404s) exceed', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_HUMAN_404),
						))->render();
						?>
					</li>
					<li>
						<?php
						$breakpoints = array(60, 300, 1800, 3600, 7200, 21600, 43200, 86400, 172800, 432000, 864000, 2592000);
						$options = array();
						foreach ($breakpoints as $b) {
							$options[] = array('value' => $b, 'label' => wfUtils::makeDuration($b));
						}
						echo wfView::create('options/option-select', array(
							'selectOptionName' => 'blockedTime',
							'selectOptions' => $options,
							'selectValue' => wfConfig::getInt('blockedTime'),
							'title' => __('How long is an IP address blocked when it breaks a rule', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_AUTOMATIC_BLOCK_DURATION),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-textarea', array(
							'textOptionName' => 'allowed404s',
							'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('allowed404s')),
							'title' => __('Allowlisted 404 URLs', 'wordfence'),
							'subtitle' => __('These URL patterns will be excluded from the throttling rules used to limit crawlers.', 'wordfence'),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_WHITELISTED_404),
						))->render();
						?>
					</li>
				</ul>
				<script type="application/javascript">
					(function($) {
						WFAD.updateRateLimitDisplay = function(value, lowValue, warningElement) {
							var hide = true;
							if (Number.isNaN) { //Anything except IE
								var originalRateValue = Number.parseInt(value);
								var lowValue = Number.parseInt(lowValue);
								if (!Number.isNaN(originalRateValue) && !Number.isNaN(lowValue)) {
									if (originalRateValue < lowValue) {
										hide = false;
									}
								}
							}
							else { //IE
								var originalRateValue = parseInt(value);
								var lowValue = parseInt(lowValue);
								if (!isNaN(originalRateValue) && !isNaN(lowValue)) {
									if (originalRateValue < lowValue) {
										hide = false;
									}
								}
							}

							$(warningElement).css('display', hide ? 'none' : 'block');
						};
						
						$(function() {
							$('.wf-option.wf-option-rate-limit > .wf-option-content > ul > li.wf-option-select select').wfselect2({
								minimumResultsForSearch: -1
							}).on('change', function () {
								var optionElement = $(this).closest('.wf-option');
								if ($(this).hasClass('wf-rate-limit-rate')) {
									var option = optionElement.data('rateOption');
									var value = $(this).val();

									var originalValue = optionElement.data('originalRateValue');
									if (originalValue == value) {
										delete WFAD.pendingChanges[option];
									}
									else {
										WFAD.pendingChanges[option] = value;
									}

									WFAD.updateRateLimitDisplay(value, $(optionElement).data('lowValue'), $(optionElement).find('.wf-rate-limit-warning'));
								}
								else if ($(this).hasClass('wf-rate-limit-action')) {
									var option = optionElement.data('actionOption');
									var value = $(this).val();

									var originalValue = optionElement.data('originalActionValue');
									if (originalValue == value) {
										delete WFAD.pendingChanges[option];
									}
									else {
										WFAD.pendingChanges[option] = value;
									}
								}

								WFAD.updatePendingChanges();
							}).triggerHandler('change');

							$(window).on('wfOptionsReset', function() {
								$('.wf-option.wf-option-rate-limit').each(function() {
									var originalRateValue = $(this).data('originalRateValue');
									$(this).find('.wf-rate-limit-rate').val(originalRateValue).trigger('change');
									var originalActionValue = $(this).data('originalActionValue');
									$(this).find('.wf-rate-limit-action').val(originalActionValue).trigger('change');
									WFAD.updateRateLimitDisplay($(this).data('originalRateValue'), $(this).data('lowValue'), $(this).find('.wf-rate-limit-warning'));
								});
							});

							//Initial warning display
							$('.wf-option.wf-option-rate-limit').each(function() {
								WFAD.updateRateLimitDisplay($(this).data('originalRateValue'), $(this).data('lowValue'), $(this).find('.wf-rate-limit-warning'));
							});
						});
					})(jQuery);
				</script>
			</div>
		</div>
	</div>
</div> <!-- end rate limiting -->