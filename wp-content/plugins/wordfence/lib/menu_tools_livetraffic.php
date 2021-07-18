<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$w = new wfConfig();
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Live Traffic', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;

			//Hash-based option block linking
			if (window.location.hash) {
				var hashes = WFAD.parseHashes();
				var hash = hashes[hashes.length - 1];
				var block = $('.wf-block[data-persistence-key="' + hash + '"]');
				if (block.length) {
					if (!block.hasClass('wf-active')) {
						block.find('.wf-block-content').slideDown({
							always: function() {
								block.addClass('wf-active');
								$('html, body').animate({
									scrollTop: block.offset().top - 100
								}, 1000);
							}
						});

						WFAD.ajax('wordfence_saveDisclosureState', {name: block.data('persistenceKey'), state: true}, function() {});
					}
					else {
						$('html, body').animate({
							scrollTop: block.offset().top - 100
						}, 1000);
					}

					history.replaceState('', document.title, window.location.pathname + window.location.search);
				}
			}
		});
	})(jQuery);
</script>
<div class="wf-section-title">
	<h2><?php esc_html_e('Live Traffic', 'wordfence') ?></h2>
	<span><?php echo wp_kses(sprintf(
		/* translators: URL to support page. */
			__('<a href="%s" target="_blank" rel="noopener noreferrer" class="wf-help-link">Learn more<span class="wf-hidden-xs"> about Live Traffic</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_TOOLS_LIVE_TRAFFIC)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array()))); ?>
		<i class="wf-fa wf-fa-external-link" aria-hidden="true"></i></span>
</div>

<?php if (wfConfig::liveTrafficEnabled() && wfConfig::get('liveActivityPauseEnabled')): ?>
	<div id="wfLiveTrafficOverlayAnchor"></div>
	<div id="wfLiveTrafficDisabledMessage">
		<h2><?php esc_html_e('Live Updates Paused', 'wordfence') ?><br/>
			<small><?php esc_html_e('Click inside window to resume', 'wordfence') ?></small>
		</h2>
	</div>
<?php endif ?>

<p><?php esc_html_e("Wordfence Live Traffic shows you what is happening on your site in real-time, including user logins, hack attempts, and requests that were blocked by the Wordfence Firewall. You can choose to log security-related traffic only or all traffic. Traffic is logged directly on the server, which means it includes visits that don't execute JavaScript. Google and other JavaScript-based analytics packages typically only show visits from browsers that are operated by a human, while Live Traffic can show visits from crawlers like Google and Bing.", 'wordfence') ?></p>

<div class="wordfenceModeElem" id="wordfenceMode_liveTraffic"></div>

<?php
echo wfView::create('tools/options-group-live-traffic', array(
	'stateKey' => 'live-traffic-options',
	'showControls' => true,
))->render();
?>

<?php
$overridden = false;
if (!wfConfig::liveTrafficEnabled($overridden)):
	?>
	<div id="wordfenceLiveActivitySecurityOnly"><p>
			<strong><?php esc_html_e('Traffic logging mode: Security-related traffic only', 'wordfence') ?><?php
				if ($overridden) {
					echo wp_kses(sprintf(
					/* translators: URL to support page. */
							__(' (host setting <a href="%s" class="wfhelp" target="_blank" rel="noopener noreferrer"></a>)', 'wordfence'), wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_LIVE_TRAFFIC_OPTION_ENABLE)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array())));
				} ?>.</strong> <?php esc_html_e('Login and firewall activity will appear below.', 'wordfence') ?></p>
	</div>
<?php else: ?>
	<div id="wordfenceLiveActivityAll"><p>
			<strong><?php esc_html_e('Traffic logging mode: All traffic', 'wordfence') ?><?php
				if ($overridden) {
					echo wp_kses(sprintf(
					/* translators: URL to support page. */
							__(' (host setting <a href="%s" class="wfhelp" target="_blank" rel="noopener noreferrer"></a>)', 'wordfence'), wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_LIVE_TRAFFIC_OPTION_ENABLE)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array())));
				} ?>.</strong> <?php esc_html_e('Regular traffic and security-related traffic will appear below.', 'wordfence') ?></p>
	</div>
<?php endif; ?>

<div id="wf-live-traffic" class="wf-row<?php echo wfConfig::get('liveTraf_displayExpandedRecords') ? ' wf-live-traffic-display-expanded' : '' ?>">
	<div class="wf-col-xs-12">
		<div class="wf-block wf-active">
			<div class="wf-block-content">
				<div class="wf-container-fluid">
					<div class="wf-row">
						<div class="<?php echo wfStyle::contentClasses(); ?>">
							<div id="wf-live-traffic-legend">
								<ul>
									<li class="wfHuman"><?php esc_html_e('Human', 'wordfence') ?></li>
									<li class="wfBot"><?php esc_html_e('Bot', 'wordfence') ?></li>
									<li class="wfNotice"><?php esc_html_e('Warning', 'wordfence') ?></li>
									<li class="wfBlocked"><?php esc_html_e('Blocked', 'wordfence') ?></li>
								</ul>
							</div>
							<div class="wf-row wf-add-bottom-small">
								<div class="wf-col-xs-12" id="wf-live-traffic-legend-wrapper">

									<form data-bind="submit: reloadListings">

										<ul class="wf-live-traffic-controls">
											<li class="wf-live-traffic-filter">
												<div class="wf-padding-no-left"><select id="wf-lt-preset-filters" data-bind="options: presetFiltersOptions, optionsText: presetFiltersOptionsText, value: selectedPresetFilter"></select></div>
												&nbsp;&nbsp;
												<input id="wf-live-traffic-filter-show-advanced" class="wf-option-checkbox" data-bind="checked: showAdvancedFilters" type="checkbox">
												<label for="wf-live-traffic-filter-show-advanced">
													<?php esc_html_e('Show Advanced Filters', 'wordfence') ?>
												</label>
											</li>
											<li class="wf-live-traffic-show-expanded">
												<ul class="wf-option wf-option-toggled-boolean-switch wf-option-no-spacing" data-option="liveTraf_displayExpandedRecords" data-enabled-value="1" data-disabled-value="0" data-original-value="<?php echo wfConfig::get('liveTraf_displayExpandedRecords') ? 1 : 0; ?>">
													<li class="wf-boolean-switch<?php echo wfConfig::get('liveTraf_displayExpandedRecords') ? ' wf-active' : ''; ?>"><a href="#" class="wf-boolean-switch-handle"></a></li>
													<li class="wf-option-title wf-padding-add-left wf-no-right wf-padding-no-right">
														<?php esc_html_e('Expand All Results', 'wordfence'); ?>
													</li>
												</ul>
											</li>
										</ul>

										<div data-bind="visible: showAdvancedFilters" id="wf-lt-advanced-filters">
											<div class="wf-live-traffic-filter-detail">
												<div>
													<div data-bind="foreach: filters">
														<div class="wf-live-traffic-filter-item">
															<div class="wf-live-traffic-filter-item-parameters">
																<div>
																	<select name="param[]" class="wf-lt-advanced-filters-param" data-bind="options: filterParamOptions, optionsText: filterParamOptionsText, value: selectedFilterParamOptionValue, optionsCaption: 'Filter...'"></select>
																</div>
																<div data-bind="visible: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() != 'bool'">
																	<select name="operator[]" class="wf-lt-advanced-filters-operator" data-bind="options: filterOperatorOptions, optionsText: filterOperatorOptionsText, value: selectedFilterOperatorOptionValue"></select>
																</div>
																<div data-bind="attr: {colSpan: (selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'bool' ? 2 : 1)}" class="wf-lt-advanced-filters-value-cell">
																	<span data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'enum'">
																		<select data-bind="options: selectedFilterParamOptionValue().values, optionsText: selectedFilterParamOptionValue().optionsText, value: value"></select>
																	</span>

																	<span data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'text'">
																		<input data-bind="value: value" type="text">
																	</span>

																	<span data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'bool'">
																		<label><?php esc_html_e('Yes', 'wordfence') ?> <input data-bind="checked: value" type="radio" value="1"></label>
																		<label><?php esc_html_e('No', 'wordfence') ?> <input data-bind="checked: value" type="radio" value="0"></label>
																	</span>
																</div>
															</div>
															<div>
																<!--<button data-bind="click: $root.removeFilter" type="button" class="wf-btn wf-btn-default">Remove</button> -->
																<a href="#" data-bind="click: $root.removeFilter" class="wf-live-traffic-filter-remove"><i class="wf-ion-trash-a"></i></a>
															</div>
														</div>
													</div>
													<div>
														<div class="wf-pad-small">
															<button type="button" class="wf-btn wf-btn-default" data-bind="click: addFilter">
																<?php esc_html_e('Add Filter', 'wordfence') ?>
															</button>
														</div>
													</div>
												</div>
												<div class="wf-form wf-form-horizontal">
													<div class="wf-form-group">
														<label for="wf-live-traffic-from" class="wf-col-sm-2"><?php esc_html_e('From:', 'wordfence') ?>&nbsp;</label>
														<div class="wf-col-sm-10">
															<input placeholder="Start date" id="wf-live-traffic-from" type="text" class="wf-datetime" data-bind="value: startDate, datetimepicker: null, datepickerOptions: { timeFormat: 'hh:mm tt z' }">
															<button data-bind="click: startDate('')" class="wf-btn wf-btn-default wf-btn-sm" type="button"><?php esc_html_e('Clear', 'wordfence') ?></button>
														</div>
													</div>
													<div class="wf-form-group">
														<label for="wf-live-traffic-to" class="wf-col-sm-2"><?php esc_html_e('To:', 'wordfence')?>&nbsp;</label>
														<div class="wf-col-sm-10">
															<input placeholder="End date" id="wf-live-traffic-to" type="text" class="wf-datetime" data-bind="value: endDate, datetimepicker: null, datepickerOptions: { timeFormat: 'hh:mm tt z' }">
															<button data-bind="click: endDate('')" class="wf-btn wf-btn-default wf-btn-sm" type="button"><?php esc_html_e('Clear', 'wordfence') ?></button>
														</div>
													</div>
													<div class="wf-form-group">
														<label for="wf-live-traffic-group-by" class="wf-col-sm-2"><?php esc_html_e('Group By:', 'wordfence') ?>&nbsp;</label>
														<div class="wf-col-sm-10">
															<select id="wf-live-traffic-group-by" name="groupby" class="wf-lt-advanced-filters-groupby" data-bind="options: filterGroupByOptions, optionsText: filterGroupByOptionsText, value: groupBy, optionsCaption: 'None'"></select>
														</div>
													</div>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="wf-row">
								<div class="wf-col-xs-12">
									<div id="wf-live-traffic-group-by" class="wf-block" data-bind="if: groupBy(), visible: groupBy()">
										<ul class="wf-filtered-traffic wf-block-list" data-bind="foreach: listings">
											<li class="wf-flex-row wf-padding-add-top wf-padding-add-bottom">
												<div class="wf-flex-row-1">
													<!-- ko if: $root.groupBy().param() == 'ip' -->
													<div data-bind="if: loc()">
														<span data-bind="attr: { class: 'wf-flag wf-flag-' + loc().countryCode.toLowerCase(), title: loc().countryName }"></span>
														<a data-bind="text: (loc().city ? loc().city + ', ' : '') + (loc().region ? loc().region + ', ' : '') + loc().countryName,
																	attr: { href: 'http://maps.google.com/maps?q=' + loc().lat + ',' + loc().lon + '&z=6' }"
																target="_blank" rel="noopener noreferrer"></a>
													</div>
													<div data-bind="if: !loc()">
														<?php esc_html_e('An unknown location at IP', 'wordfence') ?>
														<span data-bind="text: IP" target="_blank" rel="noopener noreferrer"></span>
													</div>

													<div>
														<strong><?php esc_html_e('IP:', 'wordfence') ?></strong>
														<span data-bind="text: IP" target="_blank" rel="noopener noreferrer"></span>
													</div>
													<div>
														<span class="wfReverseLookup"><span data-bind="text: IP" style="display:none;"></span></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'type' -->
													<div>
														<strong><?php esc_html_e('Type:', 'wordfence') ?></strong>
														<span data-bind="if: jsRun() == '1'"><?php esc_html_e('Human', 'wordfence') ?></span>
														<span data-bind="if: jsRun() == '0'"><?php esc_html_e('Bot', 'wordfence') ?></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'user_login' -->
													<div>
														<strong><?php esc_html_e('Username:', 'wordfence') ?></strong>
														<span data-bind="text: username()"></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'statusCode' -->
													<div>
														<strong><?php esc_html_e('HTTP Response Code:', 'wordfence') ?></strong>
														<span data-bind="text: statusCode()"></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'action' -->
													<div>
														<strong><?php esc_html_e('Firewall Response:', 'wordfence') ?></strong>
														<span data-bind="text: firewallAction()"></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'url' -->
													<div>
														<strong><?php esc_html_e('URL:', 'wordfence') ?></strong>
														<span data-bind="text: displayURL()"></span>
													</div>
													<!-- /ko -->
													<div>
														<strong><?php esc_html_e('Last Hit:', 'wordfence') ?></strong> <span
																data-bind="attr: { 'data-timestamp': ctime, text: <?php echo esc_attr(sprintf(json_encode(/* translators: Time ago. */ __('Last hit was %s ago.', 'wordfence')), '"+ ctime() + "')) ?> }"
																class="wfTimeAgo wfTimeAgo-timestamp"></span>
													</div>
													<!-- ko if: $root.groupBy().param() == 'ip' -->
													<div class="wf-add-top-small">
														<span data-bind="if: blocked()">
															<a class="wf-btn wf-btn-default wf-btn-sm" data-bind="click: unblockIP"><?php esc_html_e('Unblock IP', 'wordfence') ?></a>
														</span>
														<span data-bind="if: rangeBlocked()">
															<a class="wf-btn wf-btn-default wf-btn-sm" data-bind="click: unblockNetwork"><?php esc_html_e('Unblock range', 'wordfence') ?></a>
														</span>
														<span data-bind="if: !blocked() && !rangeBlocked()">
															<a class="wf-btn wf-btn-default wf-btn-sm" data-bind="click: blockIP"><?php esc_html_e('Block IP', 'wordfence') ?></a>
														</span>
													</div>
													<!-- /ko -->
												</div>
												<div class="wf-flex-row-0 wf-padding-add-left">
													<?php echo sprintf(/* translators: Number of HTTP requests. */ esc_html__('%s hits', 'wordfence'), '<span class="wf-filtered-traffic-hits" data-bind="text: hitCount"></span>') ?>
												</div>
											</li>

										</ul>
									</div>

									<div id="wf-live-traffic-no-group-by" data-bind="if: !groupBy()">
										<table class="wf-striped-table">
											<thead>
											<tr>
												<th><?php esc_html_e('Type', 'wordfence') ?></th>
												<th><?php esc_html_e('Location', 'wordfence') ?></th>
												<th><?php esc_html_e('Page Visited', 'wordfence') ?></th>
												<th><?php esc_html_e('Time', 'wordfence') ?></th>
												<th><?php esc_html_e('IP Address', 'wordfence') ?></th>
												<th><?php esc_html_e('Hostname', 'wordfence') ?></th>
												<th><?php esc_html_e('Response', 'wordfence') ?></th>
												<th><?php esc_html_e('View', 'wordfence') ?></th>
											</tr>
											</thead>
											<tbody id="wf-lt-listings" class="wf-filtered-traffic" data-bind="foreach: listings">
											<tr data-bind="click: toggleDetails, css: { odd: ($index() % 2 == 1), even: ($index() % 2 == 0), 'wf-details-open': showDetails, highlighted: highlighted }" class="wf-summary-row">
												<td class="wf-center">
													<span data-bind="attr: { 'class': cssClasses }"></span>
												</td>
												<td>
													<span class="wf-flex-horizontal" data-bind="if: loc()">
														<span data-bind="attr: { class: 'wf-flag wf-flag-' + loc().countryCode.toLowerCase(), title: loc().countryName }"></span>
														<span class="wf-padding-add-left-small" data-bind="text: (loc().city ? loc().city + ', ' : '') + (loc().region ? loc().region + ', ' : '') + loc().countryName"></span>
													</span>
													<span class="wf-flex-horizontal" data-bind="if: !loc()">
														<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 64.22 64.37" class="wf-flag wf-flag-unspecified"><path d="M64,28.21a30.32,30.32,0,0,0-5.8-14.73A31.6,31.6,0,0,0,37.43.56C35.7.26,33.94.18,32.2,0h-.35C30.22.18,28.58.3,27,.55A32.14,32.14,0,0,0,.2,35.61,31.4,31.4,0,0,0,10.4,55.87a31.24,31.24,0,0,0,25,8.33,30.5,30.5,0,0,0,18.94-8.79C62,47.94,65.15,38.8,64,28.21ZM57.21,44.68a23.94,23.94,0,0,1-2.3-5.08c-.66-2.45-2.27-.08-2.4,1.52s-1.2,2.8-3.33.4-2.54-1.87-3.2-1.87-1.87,1.6-1.6,9.07c.19,5.33,2.29,6.18,3.67,6.56a27.16,27.16,0,0,1-8.78,4A27.55,27.55,0,0,1,7.85,45.13C2.27,34.4,5,22.26,10.67,15.57c.15,1.21.3,2.29.43,3.37a27.63,27.63,0,0,1-.52,8.79,4.39,4.39,0,0,0,.08,1.94,1.3,1.3,0,0,0,.94.76c.27,0,.75-.41.86-.73a8.27,8.27,0,0,0,.27-1.86c0-.44,0-.89.07-1.58a10.67,10.67,0,0,1,1.06.86c.7.7,1.4,1.4,2,2.15a2.11,2.11,0,0,1,.56,1.21,3.44,3.44,0,0,0,.83,2.13,12.21,12.21,0,0,1,1.07,2.57c.14.37.17.78.33,1.13a2,2,0,0,0,1.8,1.32c1,.07,1.32.44,1.46,1.43l-.74.08c-1.17.11-1.75.65-1.71,1.83a8.43,8.43,0,0,0,2.69,6c.48.45,1,.87,1.46,1.33a3.35,3.35,0,0,1,.92,3.75,12.18,12.18,0,0,0-.69,2.09,6,6,0,0,0,.06,2.23c.18.75.1,2.37.86,2.24,1.36-.24,2.14,0,2.25-1.49a1.22,1.22,0,0,0-.08-.6c-.4-1.42,1.42-5.47,2.52-6.2a27.11,27.11,0,0,0,2.73-2,3.6,3.6,0,0,0,1.26-4,3.22,3.22,0,0,1,1.14-3.59,4.54,4.54,0,0,0,1.71-3.65c-.08-1.53-1.07-2.63-2.37-2.47a9.21,9.21,0,0,0-1.87.59,20.62,20.62,0,0,1-2.72.9c-1.31.23-2.11-.62-2.69-1.66-.47-.83-.63-.9-1.44-.38s-1.37.89-2.08,1.28S22,35.58,21.45,35a5.79,5.79,0,0,0-1.24-.88c-.31-.19-.73-.24-1-.48s-.8-.8-.75-1.15a1.69,1.69,0,0,1,.95-1.1,14.36,14.36,0,0,1,2.29-.51,7.33,7.33,0,0,0,1.22-.33c.52-.21.5-.56.1-.89a3.26,3.26,0,0,0-.69-.37l-3.52-1.39a4.74,4.74,0,0,1-.84-.43c-.74-.49-.83-1-.16-1.61,2.64-2.33,5.72-3,8.45.08.84,1,1.42,2.16,2.22,3.16a12.5,12.5,0,0,0,2.15,2.15,1.62,1.62,0,0,0,1.44.09,1.15,1.15,0,0,0,.29-1.56,8.43,8.43,0,0,0-.86-1.41,5.16,5.16,0,0,1,1.59-7.52,4.38,4.38,0,0,0,2.53-2.58c-.58.16-1,.26-1.42.39-2.3.71-.7-1,.36-1.31.65-.18-.58-.67-.58-.67s.82-.28,1.69-.65a6.85,6.85,0,0,0,1.7-.94,3.79,3.79,0,0,0,.66-1.17l-.16-.18-1.83.24c-1,.11-1.27-.09-1.37-1.14a1,1,0,0,0-1.48-.73c-.45.25-.85.61-1.29.9-1,.66-1.78.39-2.19-.75-.23-.68-.57-.81-1.19-.42-.31.18-.58.47-.89.64a11.53,11.53,0,0,1-1.62.79c-.55.19-1.21.33-1.58-.27a1.25,1.25,0,0,1,.46-1.68A14.78,14.78,0,0,1,27,10c1-.56,2.07-1,3-1.65a1.78,1.78,0,0,0,.79-2.07.88.88,0,0,0-1.37-.65c-.56.28-1.06.72-1.63,1a2.81,2.81,0,0,1-1.41.08c-.17,0-.35-.49-.35-.76s.31-.43.51-.46c1.4-.22,2.81-.41,4.22-.57a.76.76,0,0,1,.58.25,6.84,6.84,0,0,0,3.6,2.15c1.15.34,1.31.18,1.47-1,1.48-.34,3-1,4.46-.09A14.4,14.4,0,0,1,43.14,8c.18.17.07.7,0,1s-.36.87-.48,1.33a1.2,1.2,0,0,0,1.26,1.56c.29,0,.57-.07.86-.08.85,0,1.14.28,1.07,1.13-.11,1.21.09,1.35,1.31,1.15a2.07,2.07,0,0,1,1.67.64c1.14.86,2,.54,2.33-.86,0-.16,0-.32.06-.47.14-.63.49-.79.92-.35.9,1,1.74,2,2.66,3a3,3,0,0,0-.8,3.07,5.19,5.19,0,0,1-.55,3.27A24.63,24.63,0,0,0,52.2,25.5c-.45,1.57.06,2.3,1.66,2.65s1.78.64,1.84,2.14a4.85,4.85,0,0,0,2.92,4.35c.4.19.82.34,1.23.51a25.22,25.22,0,0,1-2.64,9.53Z"/></svg> <span class="wf-padding-add-left-small"><?php esc_html_e('Unspecified', 'wordfence') ?></span>
													</span>
												</td>
												<td>
													<span class="wf-lt-url wf-split-word-xs"
															data-bind="text: displayURLShort, attr: { title: URL }"></span>
												</td>
												<td class="wf-nowrap" data-bind="text: timestamp"></td>
												<td>
													<span data-bind="attr: { title: IP }, text: $root.trimIP(IP())"></span>
												</td>
												<td>
													<span class="wfReverseLookup" data-reverse-lookup-template="wf-live-traffic-hostname-template">
														<span data-bind="text: IP" style="display:none;"></span>
													</span>
												</td>
												<td data-bind="text: statusCode"></td>
												<td class="wf-live-traffic-show-details">
													<span class="wf-ion-eye"></span>
													<span class="wf-ion-eye-disabled"></span>
												</td>
											</tr>
											<tr data-bind="css: {
												'wf-details-visible': showDetails,
												'wf-details-hidden': !(showDetails()),
												highlighted: highlighted,
												odd: ($index() % 2 == 1), even: ($index() % 2 == 0) }" class="wf-details-row">
												<td colspan="8" data-bind="attr: { id: ('wfActEvent_' + id()) }" class="wf-live-traffic-details">
													<div class="wf-live-traffic-activity-detail-wrapper">
														<div class="wf-live-traffic-activity-type">
															<div data-bind="attr: { 'class': typeIconClass }"></div>
															<div data-bind="text: typeText"></div>
														</div>
														<div class="wf-live-traffic-activity-detail">
															<h2><?php esc_html_e('Activity Detail', 'wordfence') ?></h2>
															<div>
																<span data-bind="if: action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user()">
																	<span data-bind="attr: {'data-userid': user().ID}" class="wfAvatar"></span>
																	<a data-bind="attr: { href: user().editLink }, text: user().display_name"
																			target="_blank" rel="noopener noreferrer"></a>
																</span>
																<span data-bind="if: loc()">
																	<span data-bind="if: action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user()"> in</span>
																	<span data-bind="attr: { class: 'wf-flag wf-flag-' + loc().countryCode.toLowerCase(), title: loc().countryName }"></span>
																	<a data-bind="text: (loc().city ? loc().city + ', ' : '') + (loc().region ? loc().region + ', ' : '') + loc().countryName,
																		attr: { href: 'http://maps.google.com/maps?q=' + loc().lat + ',' + loc().lon + '&z=6' }"
																			target="_blank" rel="noopener noreferrer"></a>
																</span>
																<span data-bind="if: !loc()">
																	<span data-bind="if: action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user()">
																		<?php echo wp_kses(sprintf(
																		/* translators: 1. User agent. 2. IP address  */
																				__('%1$s at an unknown location at IP %2$s', 'wordfence'), '', '<a data-bind="text: IP, attr: { href: WFAD.makeIPTrafLink(IP()) }" target="_blank" rel="noopener noreferrer"></a>'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'data-bind'=>array()))) ?>
																	</span>
																	<span data-bind="if: !(action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user())">
																		<?php echo wp_kses(sprintf(
																		/* translators: IP address  */
																				__('An unknown location at IP %s', 'wordfence'), '<a data-bind="text: IP, attr: { href: WFAD.makeIPTrafLink(IP()) }" target="_blank" rel="noopener noreferrer"></a>'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'data-bind'=>array()))) ?>
																	</span>
																</span>
																<span data-bind="if: referer()">
																	<span data-bind="if: extReferer()">
																		<?php echo wp_kses(sprintf(
																		/* translators: 1. User agent. 2. HTTP referer. 3. Server response. */
																				__('%1$s arrived from %2$s and %3$s', 'wordfence'), '', '<a data-bind="text: LiveTrafficViewModel.truncateText(referer(), 100), attr: { title: referer, href: referer }" target="_blank" rel="noopener noreferrer" class="wf-split-word-xs"></a>', ''), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array(), 'data-bind'=>array()))) ?>
																	</span>
																	<span data-bind="if: !extReferer()">
																		<?php echo wp_kses(sprintf(
																		/* translators: 1. User agent. 2. HTTP referer. 3. Server response. */
																				__('%1$s left %2$s and %3$s', 'wordfence'), '', '<a data-bind="text: LiveTrafficViewModel.truncateText(referer(), 100), attr: { title: referer, href: referer }" target="_blank" rel="noopener noreferrer" class="wf-split-word-xs"></a>', ''), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array(), 'data-bind'=>array()))) ?>
																	</span>
																</span>
																<span data-bind="if: statusCode() == 404">
																	<?php echo wp_kses(sprintf(
																	/* translators: User agent. */
																			__('%s tried to access a <span style="color: #F00;">non-existent page</span>', 'wordfence'), ''), array('span'=>array('style'=>array()))) ?>
																</span>

																<span data-bind="if: statusCode() == 200 && !action()">
																	<?php echo esc_html(sprintf(
																	/* translators: 1. User agent. 2. URL of page visited. */
																			__('%1$s visited %2$s', 'wordfence'), '', '')) ?>
																</span>
																<span data-bind="if: (statusCode() == 301 || statusCode() == 302) && !action()">
																	<?php echo esc_html(sprintf(
																	/* translators: 1. User agent. 2. URL of page visited. */
																			__('%1$s was redirected when visiting %2$s', 'wordfence'), '', '')) ?>
																</span>
																<span data-bind="if: (statusCode() == 301 || statusCode() == 302) && action() && firewallAction()">
																	<?php echo wp_kses(sprintf(
																	/* translators: 1. User agent. 2. Firewall action (blocked, rate limited, etc). 3. Time ago. */
																			__('%1$s was %2$s at %3$s', 'wordfence'), '', '<span data-bind="text: firewallAction"></span>', ''), array('span'=>array('data-bind'=>array()))) ?>
																</span>
																<span data-bind="if: ((statusCode() == 403 || statusCode() == 503) && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername')">
																	<?php printf(
																	/* translators: 1. User agent. 2. Firewall action (blocked, rate limited, etc). 3. Time ago. */
																			esc_html(__('%1$s was %2$s at %3$s', 'wordfence')), '', '<span data-bind="text: firewallAction" style="color: #F00;"></span>', '') ?>
																</span>

																<span data-bind="if: action() == 'loginOK'">
																	<?php echo wp_kses(sprintf(
																	/* translators: 1. User agent. 2. WordPress username. */
																			__('%1$s logged in successfully as "%2$s".', 'wordfence'), '', '<strong data-bind="text: username"></strong>'), array('strong'=>array('data-bind'=>array()))) ?>
																</span>
																<span data-bind="if: action() == 'logout'">
																	<?php echo esc_html(sprintf(/* translators: WordPress username. */ __('%s logged out successfully.', 'wordfence'), '')) ?>
																</span>
																<span data-bind="if: action() == 'lostPassword'">
																	<?php echo esc_html(sprintf(/* translators: WordPress username. */__('%s requested a password reset.', 'wordfence'), '')) ?>
																</span>
																<span data-bind="if: action() == 'loginFailValidUsername'">
																	<?php echo wp_kses(sprintf(/* translators: 1. User agent. 2. WordPress username. */ __('%1$s attempted a <span style="color: #F00;">failed login</span> as "%2$s".', 'wordfence'), '', '<strong data-bind="text: username"></strong>'), array('span'=>array('style'=>array()), 'strong'=>array('data-bind'=>array()))) ?>
																</span>
																<span data-bind="if: action() == 'loginFailInvalidUsername'">
																	<?php echo wp_kses(sprintf(/* translators: 1. User agent. 2. WordPress username. */ __('%1$s attempted a <span style="color: #F00;">failed login</span> using an invalid username "%2$s".', 'wordfence'), '', '<strong data-bind="text: username"></strong>'), array('span'=>array('style'=>array()), 'strong'=>array('data-bind'=>array()))) ?>
																</span>
																<span data-bind="if: action() == 'user:passwordReset'">
																	<?php echo esc_html(sprintf(/* translators: WordPress username. */ __('%s changed their password.', 'wordfence'), '')) ?>
																</span>
																<a class="wf-lt-url wf-split-word-xs"
																		data-bind="text: displayURL, attr: { href: URL, title: URL }"
																		target="_blank" rel="noopener noreferrer"></a>
															</div>
															<div>
																<span data-bind="text: timeAgo, attr: { 'data-timestamp': ctime }"
																		class="wfTimeAgo-timestamp"></span>&nbsp;&nbsp;
															</div>
															<div>
																<strong><?php esc_html_e('IP:', 'wordfence') ?></strong> <span data-bind="text: IP"></span>
																<span class="wfReverseLookup">
																	<span data-bind="text: IP" style="display:none;"></span>
																</span>
																<span data-bind="if: blocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm wf-block-ip-btn"
																			data-bind="click: unblockIP">
																		<?php esc_html_e('Unblock IP', 'wordfence') ?>
																	</a>
																</span>
																<span data-bind="if: rangeBlocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm wf-block-ip-btn"
																			data-bind="click: unblockNetwork"><?php esc_html_e('Unblock range', 'wordfence') ?>
																	</a>
																</span>
																<span data-bind="if: !blocked() && !rangeBlocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm wf-block-ip-btn"
																			data-bind="click: blockIP">
																		<?php esc_html_e('Block IP', 'wordfence') ?>
																	</a>
																</span>
															</div>
															<div data-bind="visible: (jQuery.inArray(parseInt(statusCode(), 10), [403, 503, 404]) !== -1 || action() == 'loginFailValidUsername' || action() == 'loginFailInvalidUsername')">
																<strong><?php esc_html_e('Human/Bot:', 'wordfence') ?></strong> <span data-bind="text: (jsRun() === '1' ? <?php echo esc_attr(json_encode(__('Human', 'wordfence'))) ?> : <?php echo esc_attr(json_encode(__('Bot', 'wordfence'))) ?>)"></span>
															</div>
															<div class="wf-split-word" data-bind="text: UA"></div> 
															<div class="wf-live-traffic-actions">
																<span data-bind="if: blocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: unblockIP">
																		<?php esc_html_e('Unblock IP', 'wordfence') ?>
																	</a>
																</span>
																<span data-bind="if: rangeBlocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: unblockNetwork">
																		<?php esc_html_e('Unblock range', 'wordfence') ?>
																	</a>
																</span>
																<span data-bind="if: !blocked() && !rangeBlocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: blockIP">
																		<?php esc_html_e('Block IP', 'wordfence') ?>
																	</a>
																</span>
																<a class="wf-btn wf-btn-default wf-btn-sm" data-bind="click: showWhoisOverlay"
																		target="_blank" rel="noopener noreferrer"><?php esc_html_e('Run Whois', 'wordfence') ?></a>
																<a class="wf-btn wf-btn-default wf-btn-sm"
																		data-bind="click: showRecentTraffic" target="_blank" rel="noopener noreferrer">
																	<span class="wf-hidden-xs"><?php esc_html_e('See recent traffic', 'wordfence'); ?></span><span class="wf-visible-xs"><?php esc_html_e('Recent', 'wordfence'); ?></span>
																</a>
																<span data-bind="if: action() == 'blocked:waf'">
																	<a class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: function () { $root.whitelistWAFParamKey(actionData().path, actionData().paramKey, actionData().failedRules) }"
																			title="<?php esc_attr_e('If this is a false positive, you can exclude this parameter from being filtered by the firewall', 'wordfence') ?>">
																		<?php esc_html_e('Add Param to Firewall Allowlist', 'wordfence') ?>
																	</a>
																	<?php if (WFWAF_DEBUG): ?>
																		<a class="wf-btn wf-btn-default wf-btn-sm"
																				data-bind="attr: { href: '<?php echo esc_js(home_url()) ?>?_wfsf=debugWAF&nonce=' + WFAD.nonce + '&hitid=' + id() }" target="_blank" rel="noopener noreferrer">
																			Debug this Request
																		</a>
																	<?php endif ?>
															</span>
															</div>
														</div>
													</div>
												</td>
											</tr>
											</tbody>
										</table>
									</div>
									<div class="wf-live-traffic-none" data-bind="if: listings().length == 0">
										<?php esc_html_e('No requests to report yet.', 'wordfence') ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="application/javascript">
	(function($) {
		$(function() {
			$('.wf-option.wf-option-toggled-boolean-switch[data-option="liveTraf_displayExpandedRecords"]').on('change', function() {
				delete WFAD.pendingChanges['liveTraf_displayExpandedRecords'];
				var isOn = $(this).find('.wf-boolean-switch').hasClass('wf-active');
				WFAD.setOption($(this).data('option'), (isOn ? $(this).data('enabledValue') : $(this).data('disabledValue')), function() {
					$('#wf-live-traffic').toggleClass('wf-live-traffic-display-expanded', isOn);
				});
			});
			
			$(document).on('heartbeat-tick', function(heartbeat) {
				WFAD.serverMicrotime = heartbeat.timeStamp / 1000;
				WFAD.updateTimeAgo();
			})
		});
	})(jQuery);
</script>

<div id="wf-live-traffic-util-overlay-wrapper" style="display: none">
	<div class="wf-live-traffic-util-overlay">
		<div class="wf-live-traffic-util-overlay-header"></div>
		<div class="wf-live-traffic-util-overlay-body"></div>
		<span class="wf-live-traffic-util-overlay-close wf-ion-android-close"></span>
	</div>
</div>

<div id="wfrawhtml"></div>

<script type="text/x-jquery-template" id="wf-live-traffic-hostname-template">
	<span title="${ip}">${(ip && ip.length > 22) ? '...' + ip.substring(ip.length - 22) : ip}</span>
</script>

<?php if (wfOnboardingController::willShowNewTour(wfOnboardingController::TOUR_LIVE_TRAFFIC)): ?>
	<script type="application/javascript">
		(function($) {
			$(function() {
				WFAD.tour1 = function() {
					WFAD.tour('wfNewTour1', 'wf-live-traffic', 'bottom', 'bottom', null, WFAD.tourComplete);
				};
				WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_LIVE_TRAFFIC); ?>'); };
				
				<?php if (wfOnboardingController::shouldShowNewTour(wfOnboardingController::TOUR_LIVE_TRAFFIC)): ?>
				if (!WFAD.isSmallScreen) { WFAD.tour1(); }
				<?php endif; ?>
			});
		})(jQuery);
	</script>

	<script type="text/x-jquery-template" id="wfNewTour1">
		<div>
			<h3><?php esc_html_e('Live Traffic', 'wordfence'); ?></h3>
			<p><?php echo wp_kses(__('Live traffic defaults to a summary view of all security-related traffic. Details are viewable by clicking anywhere within the summary record. To switch to the expanded view, click the <strong>Expand All Records</strong> switch.', 'wordfence'), array('strong'=>array())); ?></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li class="wf-active">&bullet;</li>
				</ul>
				<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Got it', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
<?php endif; ?>

<?php if (wfOnboardingController::willShowUpgradeTour(wfOnboardingController::TOUR_LIVE_TRAFFIC)): ?>
	<script type="application/javascript">
		(function($) {
			$(function() {
				WFAD.tour1 = function() {
					WFAD.tour('wfUpgradeTour1', 'wf-live-traffic', 'bottom', 'bottom', null, WFAD.tourComplete);
				};
				WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_LIVE_TRAFFIC); ?>'); };
				
				<?php if (wfOnboardingController::shouldShowUpgradeTour(wfOnboardingController::TOUR_LIVE_TRAFFIC)): ?>
				if (!WFAD.isSmallScreen) { WFAD.tour1(); }
				<?php endif; ?>
			});
		})(jQuery);
	</script>

	<script type="text/x-jquery-template" id="wfUpgradeTour1">
		<div>
			<h3><?php esc_html_e('Live Traffic', 'wordfence'); ?></h3>
			<p><?php echo wp_kses(__('Live traffic now defaults to a summary view. Details are viewable by clicking anywhere within the summary record. To switch to the expanded view, click the <strong>Expand All Records</strong> switch. New installations will only log security-related traffic by default, though your previous setting has been preserved.', 'wordfence'), array('strong'=>array())); ?></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li class="wf-active">&bullet;</li>
				</ul>
				<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Got it', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
<?php endif; ?>