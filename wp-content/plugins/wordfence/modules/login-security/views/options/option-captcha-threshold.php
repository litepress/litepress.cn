<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }

$optionName = \WordfenceLS\Controller_Settings::OPTION_RECAPTCHA_THRESHOLD;
$currentValue = \WordfenceLS\Controller_Settings::shared()->get_float($optionName, 0.5);
$selectOptions = array(
	array('label' => __('1.0 (definitely a human)', 'wordfence-2fa'), 'value' => 1.0),
	array('label' => __('0.9', 'wordfence-2fa'), 'value' => 0.9),
	array('label' => __('0.8', 'wordfence-2fa'), 'value' => 0.8),
	array('label' => __('0.7', 'wordfence-2fa'), 'value' => 0.7),
	array('label' => __('0.6', 'wordfence-2fa'), 'value' => 0.6),
	array('label' => __('0.5 (probably a human)', 'wordfence-2fa'), 'value' => 0.5),
	array('label' => __('0.4', 'wordfence-2fa'), 'value' => 0.4),
	array('label' => __('0.3', 'wordfence-2fa'), 'value' => 0.3),
	array('label' => __('0.2', 'wordfence-2fa'), 'value' => 0.2),
	array('label' => __('0.1', 'wordfence-2fa'), 'value' => 0.1),
	array('label' => __('0.0 (definitely a bot)', 'wordfence-2fa'), 'value' => 0.0),
);
?>
<ul class="wfls-flex-vertical wfls-flex-align-left">
	<li>
		<ul id="wfls-option-recaptcha-threshold" class="wfls-option wfls-option-select" data-select-option="<?php echo esc_attr($optionName); ?>" data-original-select-value="<?php echo esc_attr($currentValue); ?>">
			<li class="wfls-option-spacer"></li>
			<li class="wfls-option-content">
				<ul>
					<li class="wfls-option-title">
						<ul class="wfls-flex-vertical wfls-flex-align-left">
							<li><span id="wfls-option-recaptcha-threshold-label"><strong><?php esc_html_e('reCAPTCHA human/bot threshold score', 'wordfence-2fa'); ?></strong></span></li>
							<li class="wfls-option-subtitle"><?php esc_html_e('A reCAPTCHA score equal to or higher than this value will be considered human. Anything lower will be treated as a bot and require additional verification for login and registration.', 'wordfence-2fa'); ?></li>
						</ul>
					</li>
					<li class="wfls-option-select wfls-padding-add-top-xs-small">
						<select aria-labelledby="wfls-option-recaptcha-threshold-label">
							<?php foreach ($selectOptions as $o): ?>
								<option class="wfls-option-select-option" value="<?php echo esc_attr($o['value']); ?>"<?php if (((int) ($o['value'] * 10)) == ((int) ($currentValue * 10))) { echo ' selected'; } ?>><?php echo esc_html($o['label']); ?></option>
							<?php endforeach; ?>
						</select>
					</li>
				</ul>
			</li>
		</ul>
	</li>
	<li>
		<ul class="wfls-option">
			<li class="wfls-option-spacer"></li>
			<li>
				<canvas id="wfls-recaptcha-score-history"></canvas>
				<div class="wfls-center"><a href="#" id="wfls-reset-recaptcha-score-stats" class="wfls-text-small"><?php esc_html_e('Reset Score Statistics', 'wordfence'); ?></a></div>
			</li>
		</ul>
	</li>
</ul>
<script type="application/javascript">
	<?php
		$stats = \WordfenceLS\Controller_Settings::shared()->get_array(\WordfenceLS\Controller_Settings::OPTION_CAPTCHA_STATS);
	?>
	(function($) {
		$(function() {
			$('#wfls-reset-recaptcha-score-stats').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				WFLS.ajax('wordfence_ls_reset_recaptcha_stats', {}, function(res) {
					if (res.success) {
						window.location.reload(true);
					}
					else {
						if (res.hasOwnProperty('html') && res.html) {
							WFLS.panelModalHTML((WFLS.screenSize(500) ? '300px' : '400px'), 'Error Resetting reCAPTCHA Statistics', res.error);
						}
						else {
							WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), 'Error Resetting reCAPTCHA Statistics', res.error);
						}
					}
				});
			});
		});
		
		$(window).on('wfls-tab-change', function(e, target) {
			if (target == 'settings') {
				var barChartData = {
					labels: ['0.0', '0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1.0'],
					datasets: [{
						label: '<?php esc_attr_e('Requests', 'wordfence-2fa'); ?>',
						backgroundColor: 'rgba(75,192,192,0.4)',
						borderColor: 'rgba(75,192,192,1.0)',
						borderWidth: 1,
						data: [
							<?php echo (int) @$stats['counts'][0]; ?>,
							<?php echo (int) @$stats['counts'][1]; ?>,
							<?php echo (int) @$stats['counts'][2]; ?>,
							<?php echo (int) @$stats['counts'][3]; ?>,
							<?php echo (int) @$stats['counts'][4]; ?>,
							<?php echo (int) @$stats['counts'][5]; ?>,
							<?php echo (int) @$stats['counts'][6]; ?>,
							<?php echo (int) @$stats['counts'][7]; ?>,
							<?php echo (int) @$stats['counts'][8]; ?>,
							<?php echo (int) @$stats['counts'][9]; ?>,
							<?php echo (int) @$stats['counts'][10]; ?>
						]
					}]
				};

				new Chart($('#wfls-recaptcha-score-history'), {
					type: 'bar',
					data: barChartData,
					options: {
						responsive: true,
						legend: {
							display: false,
						},
						title: {
							display: true,
							text: '<?php esc_attr_e('reCAPTCHA Score History', 'wordfence-2fa'); ?>'
						},
						scales: {
							yAxes: [{
								display: true,
								scaleLabel: {
									display: true,
									labelString: '<?php esc_attr_e('Count', 'wordfence-2fa'); ?>'
								},
								ticks: {
									min: 0,
									precision: 0,
									stepSize: <?php echo max(10, pow(10, floor(log10(array_sum($stats['counts']) / 5)))); ?>
								}
							}]
						}
					}
				});
			}
		});
	})(jQuery);
</script>
