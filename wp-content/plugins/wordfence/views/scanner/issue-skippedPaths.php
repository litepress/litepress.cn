<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents an issue template.
 */

if (function_exists('network_admin_url') && is_multisite()) { $optionsURL = network_admin_url('admin.php?page=WordfenceScan&subpage=scan_options#wf-option-other-scanOutside#wf-scanner-options-general'); }
else { $optionsURL = admin_url('admin.php?page=WordfenceScan&subpage=scan_options#wf-option-other-scanOutside#wf-scanner-options-general'); }

echo wfView::create('scanner/issue-base', array(
	'internalType' => 'skippedPaths',
	'displayType' => __('Skipped Paths', 'wordfence'),
	'iconSVG' => '<svg viewBox="0 0 20 20"><g><path d="M18 16V4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v12c0 .55.45 1 1 1h13c.55 0 1-.45 1-1zM8 11h1c.55 0 1 .45 1 1s-.45 1-1 1H8v1.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V13H6c-.55 0-1-.45-1-1s.45-1 1-1h1V5.5c0-.28.22-.5.5-.5s.5.22.5.5V11zm5-2h-1c-.55 0-1-.45-1-1s.45-1 1-1h1V5.5c0-.28.22-.5.5-.5s.5.22.5.5V7h1c.55 0 1 .45 1 1s-.45 1-1 1h-1v5.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V9z"/></g></svg>',
	'summaryControls' => array(wfView::create('scanner/issue-control-ignore', array('ignoreP' => __('Ignore', 'wordfence'))), wfView::create('scanner/issue-control-show-details')),
	'detailPairs' => array(
		__('Details', 'wordfence') => '{{html longMsg}}',
	),
	'detailControls' => array(
		'<a href="' . $optionsURL . '" class="wf-btn wf-btn-default wf-btn-callout-subtle">' . __('Go To Option', 'wordfence') . '</a>',
		'<a href="#" class="wf-btn wf-btn-default wf-btn-callout-subtle wf-issue-control-mark-fixed" role="button">' . __('Mark as Fixed', 'wordfence') . '</a>',
	),
	'textOutput' => (isset($textOutput) ? $textOutput : null),
	'textOutputDetailPairs' => array(
		__('Details', 'wordfence') => '$longMsg',
	),
))->render();