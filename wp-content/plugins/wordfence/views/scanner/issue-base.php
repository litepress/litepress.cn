<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Shared parent view of all scan issues.
 * 
 * Expects $internalType, $displayType, $iconSVG, and $controls.
 * 
 * @var string $internalType The internal issue type used to select the correct template.
 * @var string $displayType A human-readable string for displaying the issue type.
 * @var string $iconSVG The SVG HTML for the issue's icon.
 * @var array $summaryControls An array of summary controls for the issue type.
 * @var array $detailPairs An array of label/value pairs for the issue's detail data. If the entry should only be conditionally shown, the value may be an array of the format array(conditional, displayValue).
 * @var array $detailControls An array of detail controls for the issue type.
 * @var array $textOutput If provided, used the content of the array to output plain text rather than the HTML template.
 * @var array $textOutputDetailPairs An array of label/value pairs for the issue's detail data if outputting via text. If the entry should only be conditionally shown, the value may be an array of the format array(conditional, displayValue) where conditional is one or more keypaths that must all be truthy. It is preprocessed lightly for output: all values prefixed with $ will be treated as keypaths in the $textOutput array. If that is prefixed with ! for the conditional, its value will be inverted.
 */

if (!isset($textOutput) || !is_array($textOutput)):
?>
<script type="text/x-jquery-template" id="issueTmpl_<?php echo $internalType; ?>">
<ul class="wf-issue wf-issue-<?php echo $internalType; ?>
	{{if severity == <?php echo wfIssues::SEVERITY_CRITICAL ?>}}wf-issue-severity-critical{{/if}}
	{{if severity == <?php echo wfIssues::SEVERITY_HIGH ?>}}wf-issue-severity-high{{/if}}
	{{if severity == <?php echo wfIssues::SEVERITY_MEDIUM ?>}}wf-issue-severity-medium{{/if}}
	{{if severity == <?php echo wfIssues::SEVERITY_LOW ?>}}wf-issue-severity-low{{/if}}" data-issue-id="${id}" data-issue-type="<?php echo $internalType; ?>" data-issue-severity="${severity}" data-high-sensitivity="{{if (data.highSense == '1')}}1{{else}}0{{/if}}" data-beta-signatures="{{if (data.betaSigs == '1')}}1{{else}}0{{/if}}">
	<li class="wf-issue-summary">
		<ul>
			<li class="wf-issue-icon"><?php echo $iconSVG; ?></li>
			<li class="wf-issue-short wf-hidden-xs"><div class="wf-issue-message">${shortMsg}</div><div class="wf-issue-type"><?php echo __('Type:', 'wordfence') . ' ' . $displayType; ?></div></li>
			<li class="wf-issue-stats wf-hidden-xs">
				<div class="wf-issue-time"><?php esc_html_e('Issue Found ', 'wordfence'); ?> ${displayTime}</div>
				{{if severity == <?php echo wfIssues::SEVERITY_CRITICAL ?>}}<div class="wf-issue-severity-critical"><?php echo esc_html__('Critical', 'wordfence'); ?></div>{{/if}}
				{{if severity == <?php echo wfIssues::SEVERITY_HIGH ?>}}<div class="wf-issue-severity-high"><?php echo esc_html__('High', 'wordfence'); ?></div>{{/if}}
				{{if severity == <?php echo wfIssues::SEVERITY_MEDIUM ?>}}<div class="wf-issue-severity-medium"><?php echo esc_html__('Medium', 'wordfence'); ?></div>{{/if}}
				{{if severity == <?php echo wfIssues::SEVERITY_LOW ?>}}<div class="wf-issue-severity-low"><?php echo esc_html__('Low', 'wordfence'); ?></div>{{/if}}
			</li>
			<li class="wf-issue-short-stats wf-hidden-sm wf-hidden-md wf-hidden-lg">
				<div class="wf-issue-message wf-split-word-xs">${shortMsg}</div>
				<div class="wf-issue-type"><?php echo esc_html__('Type:', 'wordfence') . ' ' . $displayType; ?></div>
				<div class="wf-issue-time"><?php esc_html_e('Found ', 'wordfence'); ?> ${displayTime}</div>
				{{if severity == <?php echo wfIssues::SEVERITY_CRITICAL ?>}}<div class="wf-issue-severity-critical"><?php echo esc_html__('Critical', 'wordfence'); ?></div>{{/if}}
				{{if severity == <?php echo wfIssues::SEVERITY_HIGH ?>}}<div class="wf-issue-severity-high"><?php echo esc_html__('High', 'wordfence'); ?></div>{{/if}}
				{{if severity == <?php echo wfIssues::SEVERITY_MEDIUM ?>}}<div class="wf-issue-severity-medium"><?php echo esc_html__('Medium', 'wordfence'); ?></div>{{/if}}
				{{if severity == <?php echo wfIssues::SEVERITY_LOW ?>}}<div class="wf-issue-severity-low"><?php echo esc_html__('Low', 'wordfence'); ?></div>{{/if}}
				<div class="wf-issue-controls"><?php echo implode("\n", $summaryControls); ?></div>
			</li>
			<li class="wf-issue-controls wf-hidden-xs"><?php echo implode("\n", $summaryControls); ?></li>
		</ul>
	</li>
	<li class="wf-issue-detail">
		<ul>
			<!--<li><strong><?php esc_html_e('Status', 'wordfence'); ?>: </strong>{{if status == 'new' }}<?php esc_html_e('New', 'wordfence'); ?>{{/if}}{{if status == 'ignoreP' || status == 'ignoreC' }}<?php esc_html_e('Ignored', 'wordfence'); ?>{{/if}}</li>
			<li><strong><?php esc_html_e('Issue First Detected', 'wordfence'); ?>: </strong>${timeAgo} <?php esc_html_e('ago', 'wordfence'); ?>.</li>-->
		<?php
		foreach ($detailPairs as $label => $value):
			if ($value === null) {
				echo '<li class="wf-issue-detail-spacer"></li>';
				continue;
			}
			
			unset($conditional);
			if (is_array($value)) {
				$conditional = $value[0];
				$value = $value[1];
			}
			
			if (isset($conditional)) { echo '{{if (' . $conditional . ')}}'; }
		?>
			<li><strong><?php echo esc_html($label); ?>: </strong><?php echo wp_kses($value, array(
				'a' => array(
					'href'=>array(),
					'target'=>array(),
					'rel'=>array(),
					'class'=>array()
				),
				'strong' => array(
					'class'=>array()
				),
				'br' => array(
				),
				'span' => array(
					'class'=>array()
				)
			)); ?></li>
		<?php
			if (isset($conditional)) { echo '{{/if}}'; }
		endforeach;
		?>
		<?php if (count($detailControls)): ?>
			<li class="wf-issue-detail-controls"><?php echo implode("\n", $detailControls); ?></li>
		<?php endif; ?>
		</ul>
	</li>
</ul>
</script>
<?php else: ?>
<?php
echo '[' . $displayType . ($textOutput['status'] == 'ignoreP' || $textOutput['status'] == 'ignoreP' ? ', ' . __('Ignored', 'wordfence') : '') . ']' . "\n";
echo $textOutput['shortMsg'] . "\n";
echo sprintf(/* translators: Localized date. */ __('Issue Found: %s', 'wordfence'), $textOutput['displayTime']) . "\n";
$severity = null;
switch ($textOutput['severity']) {
	case wfIssues::SEVERITY_CRITICAL:
		$severity = __('Critical', 'wordfence');
		break;
	case wfIssues::SEVERITY_HIGH:
		$severity = __('High', 'wordfence');
		break;
	case wfIssues::SEVERITY_MEDIUM:
		$severity = __('Medium', 'wordfence');
		break;
	case wfIssues::SEVERITY_LOW:
		$severity = __('Low', 'wordfence');
		break;
	default:
		$severity = __('None', 'wordfence');
		break;
}
if ($severity) {
	echo sprintf(/* translators: Severity level. */ __('Severity: %s', 'wordfence'), $severity) . "\n";
}

foreach ($textOutputDetailPairs as $label => $value) {
	if ($value === null) {
		echo "\n";
		continue;
	}
	
	unset($conditional);
	if (is_array($value)) {
		$conditional = $value[0];
		if (!is_array($conditional)) {
			$conditional = array($conditional);
		}
		$value = $value[1];
	}
	
	$allow = true;
	if (isset($conditional)) {
		foreach ($conditional as $test) {
			if (!$allow) {
				break;
			}
			
			if (preg_match('/^!?\$(\S+)/', $test, $matches)) {
				$invert = (strpos($test, '!') === 0);
				$components = explode('.', $matches[1]);
				$tier = $textOutput;
				foreach ($components as $index => $c) {
					if (is_array($tier) && !isset($tier[$c])) {
						if (!$invert) {
							$allow = false;
						}
						break;
					}
					
					if ($index == count($components) - 1 && is_array($tier)) {
						if ((!$tier[$c] && !$invert) || ($tier[$c] && $invert)) {
							$allow = false;
						}
						break;
					}
					else if (!is_array($tier)) {
						$allow = false;
						break;
					}
					
					$tier = $tier[$c];
				}
			}
		}
	}
	
	if (!$allow) {
		continue;
	}
	
	if (preg_match_all('/(?<=^|\s)\$(\S+)(?=$|\s)/', $value, $matches, PREG_OFFSET_CAPTURE)) {
		array_shift($matches);
		$matches = $matches[0];
		$matches = array_reverse($matches);
		foreach ($matches as $m) {
			$resolvedKeyPath = '';
			$components = explode('.', $m[0]);
			$tier = $textOutput;
			foreach ($components as $index => $c) {
				if (is_array($tier) && !isset($tier[$c])) {
					$allow = false;
					break 2;
				}
				
				if ($index == count($components) - 1 && is_array($tier)) {
					$resolvedKeyPath = (string) $tier[$c];
					break;
				}
				else if (!is_array($tier)) {
					$allow = false;
					break 2;
				}
				
				$tier = $tier[$c];
			}
			
			$value = substr($value, 0, $m[1] - 1) . strip_tags($resolvedKeyPath) . substr($value, $m[1] + strlen($m[0]));
		}
	}
	
	if (!$allow) {
		continue;
	}
	
	echo $label . ': ' . $value . "\n";
}
?>
<?php endif; ?>