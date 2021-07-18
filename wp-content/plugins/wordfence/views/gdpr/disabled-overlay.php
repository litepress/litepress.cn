<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
?>
<div id="wf-toupp-required-overlay"></div>
<div id="wf-toupp-required-message">
	<div id="wf-toupp-required-message-inner">
		<p><?php esc_html_e('Our Terms of Use and Privacy Policy have changed.  To continue using Wordfence, you will need to review and accept the updated Terms of Use and Privacy Policy by clicking Review.', 'wordfence'); ?></p>
		<p><a href="#" class="wf-btn wf-btn-default" onclick="jQuery('#wf-gdpr-review').trigger('click'); return false;"><?php esc_html_e('Review', 'wordfence'); ?></a></p>
	</div>
</div>
