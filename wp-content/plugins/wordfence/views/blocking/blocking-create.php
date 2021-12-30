<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

$wfBlockRange = filter_input(INPUT_GET, 'wfBlockRange', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR);
?>
<ul class="wf-block-list wf-block-list-vertical">
	<li class="wf-flex-vertical wf-padding-add-top wf-padding-add-bottom">
		<table id="wf-create-block" class="wf-option">
			<tr>
				<th class="wf-right wf-padding-add-right"><?php echo wp_kses(__('<span class="wf-hidden-xs">Block </span>Type', 'wordfence'), array('span'=>array('class'=>array()))); ?></th>
				<td class="wf-block-type">
					<ul id="wf-block-type" class="wf-nav wf-nav-pills wf-nav-pills-bordered wf-nav-pills-connected">
						<li><a href="#" data-value="ip-address" data-new-button="<?php esc_attr_e('Block<span class="wf-hidden-xs"> this IP Address</span>', 'wordfence'); ?>" role="button"><?php echo wp_kses(__('IP<span class="wf-hidden-xs"> Address</span>', 'wordfence'), array('span'=>array('class'=>array()))); ?></a></li>
						<li><a href="#" data-value="country" data-new-button="<?php esc_attr_e('Block<span class="wf-hidden-xs"> the Selected Countries</span>', 'wordfence'); ?>" data-edit-button="<?php esc_attr_e('Update<span class="wf-hidden-xs"> Block</span>', 'wordfence'); ?>" role="button"><?php esc_html_e('Country', 'wordfence'); ?></a></li>
						<li<?php echo $wfBlockRange ? ' class="wf-active"' : '' ?>><a href="#" data-value="custom-pattern" data-new-button="<?php esc_attr_e('Block<span class="wf-hidden-xs"> Visitors Matching this Pattern</span>', 'wordfence'); ?>" role="button"><?php echo wp_kses(__('<span class="wf-hidden-xs">Custom </span>Pattern', 'wordfence'), array('span'=>array('class'=>array()))); ?></a></li>
					</ul>
					<script type="application/javascript">
						(function($) {
							WFAD.updateCreateBlockPattern = function() {
								var active = $('#wf-block-type > li.wf-active a').data('value');

								var duration = Date.parse('t + ' + $('#wf-block-duration').val());
								if (duration === null || !$('#wf-block-duration').val() || $('#wf-block-duration').val() == 'forever') {
									duration = 0;
								}
								else {
									duration = (Date.now().getTime() - duration.getTime()) / 1000;
								}
								
								var allowCreation = duration >= 0 && !!$('#wf-block-reason').val();
								if (active == 'ip-address') {
									allowCreation = allowCreation && $('#wf-block-ip').val() && WFAD.isValidIP($('#wf-block-ip').val());
								}
								else if (active == 'country') {
									var countries = $('#wf-block-country-countries').val() || [];
									allowCreation = allowCreation && ($('#wf-block-country-login .wf-option-checkbox').hasClass('wf-checked') || $('#wf-block-country-site .wf-option-checkbox').hasClass('wf-checked')) && countries.length > 0;
								}
								else if (active == 'custom-pattern') {
									allowCreation = allowCreation && (($('#wf-block-ip-range').val() && WFAD.parseIPRange($('#wf-block-ip-range').val())) || $('#wf-block-hostname').val() || $('#wf-block-user-agent').val() || $('#wf-block-referrer').val());
								}
								
								$('#wf-block-add-save').toggleClass('wf-disabled', !allowCreation);
							};
							
							$(function() {
								$('#wf-block-type a').on('click', function(e) {
									e.preventDefault(); 
									e.stopPropagation();
		
									$('#wf-block-type > li').removeClass('wf-active');
									$(this).closest('li').addClass('wf-active');

									$('#wf-block-duration, #wf-block-reason, #wf-block-ip, #wf-block-ip-range, #wf-block-hostname, #wf-block-user-agent, #wf-block-referrer').val('');
									
									var title = $('#wf-block-parameters-title').data('newTitle');
									var saveButton = $('#wf-block-type > li.wf-active a').data('newButton');
									var active = $('#wf-block-type > li.wf-active a').data('value');
									if (active == 'ip-address') {
										$('.wf-block-add-country, .wf-block-add-pattern').hide();
										$('.wf-block-add-ip').show();
									}
									else if (active == 'country') {
										$('.wf-block-add-ip, .wf-block-add-pattern').hide(); 
										$('.wf-block-add-country').show();

										$('#wf-block-reason').val('<?php esc_attr_e('Country Blocking', 'wordfence'); ?>');
										
										if (!!$('#wf-blocks-wrapper').data('hasCountryBlock')) {
											title = $('#wf-block-parameters-title').data('editTitle');
											saveButton = $('#wf-block-type > li.wf-active a').data('editButton');
											
											var editValues = $('#wf-blocks-wrapper').data('hasCountryBlock');
											$('.wf-block-edit').first().closest('tr').addClass('wf-editing');
											$('#wf-block-reason').val(editValues.reason);
											$('#wf-block-country-login .wf-option-checkbox').toggleClass('wf-checked', !!editValues.blockLogin);
											$('#wf-block-country-site .wf-option-checkbox').toggleClass('wf-checked', !!editValues.blockSite);
											$('#wf-block-country-countries').val(editValues.countries).trigger('change');
										}
										else {
											$('#wf-block-country-login .wf-option-checkbox').toggleClass('wf-checked', true);
											$('#wf-block-country-site .wf-option-checkbox').toggleClass('wf-checked', true);
											$('#wf-block-country-countries').val([]).trigger('change');
										}
									}
									else if (active == 'custom-pattern') {
										$('.wf-block-add-ip, .wf-block-add-country').hide();
										$('.wf-block-add-pattern').show();
									}

									$('#wf-block-parameters-title').text(title);
									$('#wf-block-add-save').html(saveButton);
									
									$('.wf-block-add-common').show();
								});

								$('#wf-block-type .wf-active a').triggerHandler('click');
								
								<?php if ($wfBlockRange): ?>
								$('#wf-block-ip-range').val('<?php echo esc_attr($wfBlockRange); ?>');
								<?php endif; ?>
								
								$('#wf-block-reason, #wf-block-ip, #wf-block-ip-range, #wf-block-hostname, #wf-block-user-agent, #wf-block-referrer').on('change paste keyup', function() {
									setTimeout(function() {
										WFAD.updateCreateBlockPattern();
									}, 4);
								});
							});
						})(jQuery);
					</script>
				</td>
			</tr>
			<tr class="" style="display: none;">
				<th class="wf-right wf-padding-add-right"><?php esc_html_e('Block Duration', 'wordfence'); ?></th>
				<td class="wf-option-text">
					<input id="wf-block-duration" type="text" placeholder="<?php esc_attr_e('Enter a duration (default is forever)', 'wordfence'); ?>">
					<script type="application/javascript">
						<?php
						$locale = get_locale();
						$locale = preg_replace('/_/', '-', $locale);
						$localizedDateJS = wfDateLocalization::localizationForLanguage($locale);
						if ($localizedDateJS === false) {
							$localizedDateJS = wfDateLocalization::localizationForLanguage('en-US');
						}
						echo $localizedDateJS;
						?>
					</script>
					<?php if (false): ?><script type="application/javascript" src="<?php echo esc_attr(wfUtils::getBaseURL() . 'js/date.js'); ?>"></script><?php endif; ?>
				</td>
			</tr>
		<?php if (wfConfig::get('isPaid')): ?>
			<tr class="wf-block-add-country" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php echo wp_kses(__('<span class="wf-hidden-xs">What to </span>Block', 'wordfence'), array('span'=>array('class'=>array()))); ?></th>
				<td class="wf-padding-add-top-small wf-form-field">
					<div class="wf-option-checkboxes">
						<ul id="wf-block-country-login">
							<li class="wf-option-checkbox wf-checked"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></li>
							<li class="wf-option-title"><?php esc_html_e('Login Form', 'wordfence'); ?></li>
						</ul>
						<ul id="wf-block-country-site">
							<li class="wf-option-checkbox wf-checked"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></li>
							<li class="wf-option-title"><?php echo wp_kses(__('<span class="wf-hidden-xs">Block access to the rest of the site</span><span class="wf-visible-xs">Rest of site</span>', 'wordfence'), array('span'=>array('class'=>array()))); ?></li>
						</ul>
					</div>
				</td>
			</tr>
			<tr class="wf-block-add-country" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"></th>
				<td class="wf-padding-add-top-small wf-form-field">
					<em><?php echo wp_kses(sprintf(/* translators: Support URL */ __('If you use Google Ads, blocking countries from accessing the entire site is not recommended. <a href="%s" target="_blank" rel="noopener noreferrer">Learn More<span class="screen-reader-text"> (opens in new tab)</span></a>', 'wordfence'), wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_BLOCKING_FULL_SITE)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()), 'span'=>array('class'=>array()))); ?></em>
				</td>
			</tr>
			<tr class="wf-block-add-country" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php echo wp_kses(__('Countries<span class="wf-hidden-xs"> to Block</span>', 'wordfence'), array('span'=>array('class'=>array()))); ?><br><a href="#" id="wf-block-country-countries-popup" role="button"><?php echo wp_kses(__('Pick<span class="wf-hidden-xs"> from List</span>', 'wordfence'), array('span'=>array('class'=>array()))); ?></a></th>
				<td class="wf-option-text wf-padding-add-top-small">
					<select id="wf-block-country-countries" multiple>
					<?php
					require(WORDFENCE_PATH . 'lib/wfBulkCountries.php'); /** @var array $wfBulkCountries */
					asort($wfBulkCountries);
					foreach ($wfBulkCountries as $code => $name):
					?>
						<option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="wf-block-add-country" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"></th>
				<td class="wf-option-text wf-padding-add-top-small">
					<div id="wf-block-country-countries-tags"></div>
				</td>
			</tr>
		<?php else: ?>
			<tr class="wf-block-add-country" style="display: none;">
				<td colspan="2">
					<ul class="wf-flex-vertical">
						<li><h3><?php esc_html_e('Put Geographic Protection In Place With Country Blocking', 'wordfence'); ?></h3></li>
						<li><p class="wf-no-top"><?php esc_html_e('Wordfence country blocking is designed to stop an attack, prevent content theft, or end malicious activity that originates from a geographic region in less than 1/300,000th of a second. Blocking countries who are regularly creating failed logins, a large number of page not found errors, and are clearly engaged in malicious activity is an effective way to protect your site during an attack.', 'wordfence'); ?></p></li>
						<li><?php echo wfView::create('blocking/country-block-map')->render(); ?></li>
						<li><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1countryBlockUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade to Premium', 'wordfence'); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a></li>
					</ul>
				</td>
			</tr>
		<?php endif; ?>
			<tr class="wf-block-add-ip" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php echo wp_kses(__('IP<span class="wf-hidden-xs"> Address to Block</span>', 'wordfence'), array('span'=>array('class'=>array()))); ?></th>
				<td class="wf-option-text wf-padding-add-top-small"><input id="wf-block-ip" type="text" placeholder="<?php esc_attr_e('Enter an IP address', 'wordfence'); ?>"></td>
			</tr>
			<tr class="wf-block-add-pattern" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php echo wp_kses(__('IP<span class="wf-hidden-xs"> Address</span> Range', 'wordfence'), array('span'=>array('class'=>array()))); ?></th>
				<td class="wf-option-text wf-padding-add-top-small"><input id="wf-block-ip-range" type="text" placeholder="<?php esc_attr_e('e.g., 192.168.200.200 - 192.168.200.220 or 192.168.200.0/24', 'wordfence'); ?>"></td>
			</tr>
			<tr class="wf-block-add-pattern" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php esc_html_e('Hostname', 'wordfence'); ?></th>
				<td class="wf-option-text wf-padding-add-top-small"><input id="wf-block-hostname" type="text" placeholder="<?php esc_attr_e('e.g., *.amazonaws.com or *.linode.com', 'wordfence'); ?>"></td>
			</tr>
			<tr class="wf-block-add-pattern" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php echo wp_kses(__('<span class="wf-hidden-xs">Browser </span>User Agent', 'wordfence'), array('span'=>array('class'=>array()))); ?></th>
				<td class="wf-option-text wf-padding-add-top-small"><input id="wf-block-user-agent" type="text" placeholder="<?php esc_attr_e('e.g., *badRobot*, *MSIE*, or *browserSuffix', 'wordfence'); ?>"></td>
			</tr>
			<tr class="wf-block-add-pattern" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php esc_html_e('Referrer', 'wordfence'); ?></th>
				<td class="wf-option-text wf-padding-add-top-small"><input id="wf-block-referrer" type="text" placeholder="<?php esc_attr_e('e.g., *badwebsite.example.com*', 'wordfence'); ?>"></td>
			</tr>
			<tr class="wf-block-add-ip wf-block-add-pattern" style="display: none;">
				<th class="wf-right wf-padding-add-right wf-padding-add-top-small"><?php echo wp_kses(__('<span class="wf-hidden-xs">Block </span>Reason', 'wordfence'), array('span'=>array('class'=>array()))); ?><span class="wf-red-dark">*</span></th> 
				<td class="wf-option-text wf-padding-add-top-small"><input id="wf-block-reason" type="text" placeholder="<?php esc_attr_e('Enter a reason', 'wordfence'); ?>" maxlength="50"></td>
			</tr>
		</table>
	</li>
	<li class="<?php echo (wfConfig::get('isPaid') ? 'wf-block-add-common' : 'wf-block-add-ip wf-block-add-pattern'); ?>" style="display: none;">
		<div class="wf-right wf-padding-add-top wf-padding-add-bottom">
			<a id="wf-block-add-cancel" class="wf-btn wf-btn-default wf-btn-callout-subtle" href="#" role="button"><?php esc_html_e('Cancel', 'wordfence'); ?></a>&nbsp;&nbsp;<a id="wf-block-add-save" class="wf-btn wf-btn-primary wf-btn-callout-subtle wf-disabled" href="#"><?php echo wp_kses(__('Block<span class="wf-hidden-xs"> Visitors Matching this</span> Pattern', 'wordfence'), array('span'=>array('class'=>array()))); ?></a>
			<script type="application/javascript">
				(function($) {
					$(function() {
						$('.wf-option-checkboxes .wf-option-checkbox').each(function() {
							$(this).on('click', function(e) {
								e.preventDefault();
								e.stopPropagation();

								$(this).toggleClass('wf-checked');
								WFAD.updateCreateBlockPattern();
							});
						});
						
						$('#wf-block-country-countries').wfselect2({
							tags: true,
							tokenSeparators: [',', ' '],
							placeholder: "Hit enter to add",
							width: 'element',
							minimumResultsForSearch: 1,
							minimumInputLength: 2,
							selectOnClose: false,
							createTag: function (params) {
								return null; //No custom tags
							},
							sorter: function(results) {
								var term = $('#wf-block-country-countries').data('wfselect2').$container.find('.wfselect2-search__field').val();
								if (term) {
									var escapedTerm = term.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
									var termRegex = new RegExp('^' + escapedTerm, 'i');
									return results.sort(function(a, b) {
										var aPrefix = termRegex.test(a.text);
										var bPrefix = termRegex.test(b.text);
										if (aPrefix && !bPrefix) { return -1; }
										if (!aPrefix && bPrefix) { return 1; }
										return a.text.localeCompare(b.text);
									});
								}
								return results;
							}
						}).on('change', function () {
							var selected = $(this).find('option:selected');
							var container = $('#wf-block-country-countries-tags');

							var list = $('<ul>');
							selected.each(function(index, value) {
								var li = $('<li class="wf-tag-selected' + (index > 4 && !container.data('expanded') ? ' wf-hidden' : '') + '"><a class="wf-destroy-tag-selected">×</a>' + $(value).text() + '</li>');
								li.children('a.wf-destroy-tag-selected')
									.off('click.wfselect2-copy')
									.on('click.wfselect2-copy', function(e) {
										var opt = $(this).data('wfselect2-opt');
										opt.prop('selected', false);
										opt.parents('select').trigger('change');
									}).data('wfselect2-opt', $(value));
								list.append(li);
							});
							
							if (selected.length > 5) {
								if (!container.data('expanded')) {
									list.append($('<li class="wf-tags-show-hide-more"><a href="#" role="button">and ' + (selected.length - 5) + ' more...' + '</a></li>'));
								}
								else {
									list.append($('<li class="wf-tags-show-hide-more"><a href="#" role="button">Hide' + '</a></li>'));
								}
							}
							
							container.html('').append(list);
							
							$('.wf-tags-show-hide-more').on('click', function(e) {
								e.preventDefault(); 
								e.stopPropagation();
								
								var expanded = !!container.data('expanded');
								$('.wf-tag-selected').slice(5).toggleClass('wf-hidden', expanded);
								container.data('expanded', !expanded);
								
								$(this).find('a').text(expanded ? 'and ' + (selected.length - 5) + ' more...' : 'Hide');
							});

							WFAD.updateCreateBlockPattern();
						}).triggerHandler('change');

						if ($('#wf-block-country-countries').length > 0) {
							$('#wf-block-country-countries').data('wfselect2').$container.addClass('wf-select2-placeholder-fix wf-select2-hide-tags');
						}
						
						$('#wf-block-country-countries-popup').on('click', function(e) {
							e.preventDefault();
							e.stopPropagation();
							
							var content = $('#wfTmpl_countrySelector').tmpl();
							$(content).find('#wf-country-selector-confirm').text($('#wf-block-add-save').text());

							var modal = $(content);
							var countries = {};
							var currentSelection = $('#wf-block-country-countries').val() || [];
							for (var i = 0; i < currentSelection.length; i++) {
								countries[currentSelection[i]] = 1;
								modal.find('li[data-country="' + currentSelection[i] + '"]').addClass('wf-active');
							}
							modal.data('countries', countries);
							
							$.wfDrawer({
								width: WFAD.isSmallScreen ? '320px' : '800px',
								content: content,
								onComplete: function() {
									var updateCount = function() {
										var count = $('.wf-blocked-countries li.wf-active').length;
										$('#wf-country-selector-count').text(count + (count == 1 ? ' Country Selected' : ' Countries Selected'));	
									};
									updateCount();
	
									$('.wf-blocked-countries a').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
	
										$(this).closest('li').trigger('click');
									});
	
									$('.wf-blocked-countries li').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
	
										var modal = $('#wf-country-selector');
										var countries = modal.data('countries');
										var country = $(this).data('country');
	
										$(this).toggleClass('wf-active');
										if ($(this).hasClass('wf-active')) {
											countries[country] = 1;
										}
										else {
											delete countries[country];
										}
	
										modal.data('countries', countries);
										updateCount();
									});
									
									$('#wf-country-selector-block-all').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
	
										var modal = $('#wf-country-selector');
										var countries = {};
										modal.find('li[data-country]').addClass('wf-active').each(function() {
											countries[$(this).data('country')] = 1;
										});
										
										modal.data('countries', countries);
										updateCount();
									});
	
									$('#wf-country-selector-unblock-all').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
	
										var modal = $('#wf-country-selector');
										modal.data('countries', {});
										modal.find('li[data-country]').removeClass('wf-active');
										updateCount();
									});
	
									$('.wf-country-selector-section-options li a').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
	
										$(this).closest('li').trigger('click');
									});
									
									$('.wf-country-selector-section-options li').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
	
										var letter = $(this).find('a').data('letter');
										var scrollTarget = $('.wf-blocked-countries[data-letter="' + letter + '"]');
										$('.wf-country-selector-inner-wrapper').animate({
											scrollTop: $('.wf-country-selector-inner-wrapper').scrollTop() + scrollTarget.offset().top - $('.wf-country-selector-inner-wrapper').offset().top
										}, 500);
									});
									
									$('#wf-country-selector-cancel').on('click', function(e) { //Commits but doesn't save
										e.preventDefault();
										e.stopPropagation();

										var modal = $('#wf-country-selector');
										var countries = Object.keys(modal.data('countries')) || [];
										$('#wf-block-country-countries').val(countries).trigger('change');

										$.wfDrawer.close()
									});
	
									$('#wf-country-selector-confirm').on('click', function(e) { //Commits and saves
										e.preventDefault();
										e.stopPropagation();
	
										var modal = $('#wf-country-selector');
										var countries = Object.keys(modal.data('countries')) || [];
										$('#wf-block-country-countries').val(countries).trigger('change');
										$('#wf-block-add-save').trigger('click');
	
										$.wfDrawer.close()
									});
							}});
						});
						
						$('#wf-block-add-cancel').on('click', function(e) {
							e.preventDefault();
							e.stopPropagation();

							$('.wf-blocks-table > tbody > tr').removeClass('wf-editing');
							$('#wf-block-parameters-title').text($('#wf-block-parameters-title').data('newTitle'));
							$('#wf-block-type > li').removeClass('wf-active');
							$('.wf-block-add-common, .wf-block-add-ip, .wf-block-add-country, .wf-block-add-pattern').hide();
							$('#wf-block-duration, #wf-block-reason, #wf-block-ip, #wf-block-ip-range, #wf-block-hostname, #wf-block-user-agent, #wf-block-referrer').val('');
						});
						
						$('#wf-block-add-save').on('click', function(e) {
							var selectedCountries = $('#wf-block-country-countries').val() || [];
							var blockSite = $('#wf-block-country-site .wf-option-checkbox').hasClass('wf-checked') ? 1 : 0;
							var button = $(this);
							var includesUs = false;
							for (var index in selectedCountries) {
								if (selectedCountries[index] === 'US') {
									includesUs = true;
									break;
								}
							}
							if (includesUs && blockSite && !button.data('acknowledged')) {
								var modal = $("#wfTmpl_blockUnitedStatesPrompt").tmpl();
								var modalHtml = $("<div>").append(modal).html();
								WFAD.colorboxHTML(
									WFAD.isSmallScreen ? '300px' : '400px',
									modalHtml,
									{
										onComplete: function() {
											$('#wf-us-blocking-prompt-cancel').on('click', function(e) {
												e.preventDefault();
												e.stopPropagation();
												WFAD.colorboxClose();
											});
											$('#wf-us-blocking-prompt-continue').on('click', function(e) {
												e.preventDefault();
												e.stopPropagation();
												button.data('acknowledged', true);
												WFAD.colorboxClose();
												button.trigger('click');
											});
										},
										className: 'wf-modal',
										closeButton: false,
										overlayClose: false
									}
								);
								return;
							}
							button.data('acknowledged', false);
							e.preventDefault();
							e.stopPropagation();

							var active = $('#wf-block-type > li.wf-active a').data('value');
							var payload = {type: active};
							
							payload['duration'] = Date.parse('t + ' + $('#wf-block-duration').val());
							if (payload['duration'] === null || !$('#wf-block-duration').val() || $('#wf-block-duration').val() == 'forever') {
								payload['duration'] = 0;
							}
							else {
								payload['duration'] = (Date.now().getTime() - payload['duration'].getTime()) / 1000; 
							}
							
							payload['reason'] = $('#wf-block-reason').val();
							if (active == 'ip-address') {
								payload['ip'] = $('#wf-block-ip').val();
							}
							else if (active == 'country') {
								payload['blockLogin'] = $('#wf-block-country-login .wf-option-checkbox').hasClass('wf-checked') ? 1 : 0;
								payload['blockSite'] = blockSite;
								payload['countries'] = selectedCountries;
							}
							else if (active == 'custom-pattern') {
								payload['ipRange'] = $('#wf-block-ip-range').val();
								payload['hostname'] = $('#wf-block-hostname').val();
								payload['userAgent'] = $('#wf-block-user-agent').val();
								payload['referrer'] = $('#wf-block-referrer').val();
							}

							WFAD.loadingBlocks = true;
							WFAD.ajax('wordfence_createBlock', {payload: JSON.stringify(payload), sortColumn: WFAD.sortColumn, sortDirection: WFAD.sortDirection, blocksFilter: WFAD.blocksFilter}, function(res) {
								WFAD.loadingBlocks = false;
								if (res.success) {
									$(window).trigger('wordfenceRefreshBlockList', [res, false]);

									$('.wf-blocks-table > tbody > tr').removeClass('wf-editing');
									$('#wf-block-parameters-title').text($('#wf-block-parameters-title').data('newTitle'));
									$('#wf-block-type > li').removeClass('wf-active');
									$('.wf-block-add-common, .wf-block-add-ip, .wf-block-add-country, .wf-block-add-pattern').hide();
									$('#wf-block-duration, #wf-block-reason, #wf-block-ip, #wf-block-ip-range, #wf-block-hostname, #wf-block-user-agent, #wf-block-referrer').val('');
								}
								else {
									WFAD.colorboxModalHTML((WFAD.isSmallScreen ? '300px' : '400px'), <?php echo json_encode(esc_html__('Error Saving Block', 'wordfence')) ?>, res.error);
								}
							});
						});
					});
				})(jQuery);
			</script>
		</div></li>
</ul>
<?php
echo wfView::create('blocking/country-modal')->render();
?>
<script type="text/x-jquery-template" id="wfTmpl_blockUnitedStatesPrompt">
<?php
echo wfView::create('common/modal-prompt', array(
	'title' => __('U.S. Blocked', 'wordfence'),
	'messageHTML' => esc_html__('For most sites, we recommend allowing access from the United States, where Google and other benign crawlers are located. Some well-known crawlers also have locations in Europe, so be careful when blocking other countries, especially if you have not seen significant attacks from them.', 'wordfence') . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_BLOCKING_FULL_SITE). '" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn More', 'wordfence') . '</a>',
	'primaryButton' => array('id' => 'wf-us-blocking-prompt-cancel', 'label' => __('Cancel', 'wordfence'), 'link' => '#'),
	'secondaryButtons' => array(array('id' => 'wf-us-blocking-prompt-continue', 'label' => __('Continue', 'wordfence'), 'link' => '#')),
))->render();
?>
</script>