<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

/**
 * Presents the manual message for WAF auto prepend installation.
 *
 */

?>
<p><?php echo wp_kses(__('The required file has been created. You\'ll need to insert the following code into your <code>php.ini</code> to finish installation:', 'wordfence'), array('code'=>array())); ?></p>
<pre class="wf-pre">auto_prepend_file = '<?php echo esc_html(addcslashes(wordfence::getWAFBootstrapPath(), "'")); ?>'</pre>
<p><?php echo wp_kses(sprintf(/* translators: Support URL. */ __('You can find more details on alternative setup steps, including installation on SiteGround or for multiple sites sharing a single php.ini, <a target="_blank" rel="noopener noreferrer" href="%s">in our documentation</a>.', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></p>