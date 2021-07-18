<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }
?>
<div id="wfls-settings" class="wfls-flex-row wfls-flex-row-wrappable wfls-flex-row-equal-heights">
	<!-- begin status content -->
	<div id="wfls-user-stats" class="wfls-flex-row wfls-flex-row-equal-heights wfls-flex-item-xs-100">
		<?php
		echo \WordfenceLS\Model_View::create('settings/user-stats', array(
			'counts' => \WordfenceLS\Controller_Users::shared()->detailed_user_counts(),
		))->render();
		?>
	</div>
	<!-- end status content -->
	<!-- begin options content -->
	<div id="wfls-options" class="wfls-flex-row wfls-flex-row-equal-heights wfls-flex-item-xs-100">
		<?php
		echo \WordfenceLS\Model_View::create('settings/options', array(
			
		))->render();
		?>
	</div>
	<!-- end options content -->
</div>
