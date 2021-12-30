<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
?>
<ul class="wf-flex-horizontal wf-flex-full-width wf-no-top">
	<li class="wf-tip-light-bulb"><i class="wf-ion-ios-lightbulb-outline"></i></li>
	<li class="wf-tip-info-message"><strong><?php echo wp_kses(sprintf(/* translators: Support URL. */ __('The Web Application Firewall is currently in Learning Mode. <a href="%s" target="_blank" rel="noopener noreferrer">Learn More<span class="screen-reader-text"> (opens in new tab)</span></a>', 'wordfence'), wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_LEARNING_MODE)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()), 'span'=>array('class'=>array()))); ?></strong></li>
</ul>