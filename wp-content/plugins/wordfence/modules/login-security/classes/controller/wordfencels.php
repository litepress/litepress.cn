<?php

namespace WordfenceLS;

use WordfenceLS\Crypto\Model_JWT;
use WordfenceLS\Crypto\Model_Symmetric;
use WordfenceLS\Text\Model_HTML;
use WordfenceLS\View\Model_Tab;
use WordfenceLS\View\Model_Title;

class Controller_WordfenceLS {
	const VERSION_KEY = 'wordfence_ls_version';
	
	/**
	 * Returns the singleton Controller_Wordfence2FA.
	 *
	 * @return Controller_WordfenceLS
	 */
	public static function shared() {
		static $_shared = null;
		if ($_shared === null) {
			$_shared = new Controller_WordfenceLS();
		}
		return $_shared;
	}
	
	public function init() {
		$this->_init_actions();
		Controller_AJAX::shared()->init();
		Controller_Users::shared()->init();
		Controller_Time::shared()->init();
		Controller_Permissions::shared()->init();
	}
	
	protected function _init_actions() {
		register_activation_hook(WORDFENCE_LS_FCPATH, array($this, '_install_plugin'));
		register_deactivation_hook(WORDFENCE_LS_FCPATH, array($this, '_uninstall_plugin'));
		
		$versionInOptions = ((is_multisite() && function_exists('get_network_option')) ? get_network_option(null, self::VERSION_KEY, false) : get_option(self::VERSION_KEY, false));
		if (!$versionInOptions || version_compare(WORDFENCE_LS_VERSION, $versionInOptions, '>')) { //Either there is no version in options or the version in options is greater and we need to run the upgrade
			$this->_install();
		}
		
		if (!Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_ALLOW_XML_RPC)) {
			add_filter('xmlrpc_enabled', array($this, '_block_xml_rpc'));
		}
		
		add_action('admin_init', array($this, '_admin_init'));
		add_action('login_enqueue_scripts', array($this, '_login_enqueue_scripts'));
		add_filter('authenticate', array($this, '_authenticate'), 25, 3);
		add_action('set_logged_in_cookie', array($this, '_set_logged_in_cookie'), 25, 4);
		add_action('wp_login', array($this, '_record_login'), 999, 1);
		add_action('register_post', array($this, '_register_post'), 25, 3);
		add_filter('wp_login_errors', array($this, '_wp_login_errors'), 25, 3);
		
		$useSubmenu = WORDFENCE_LS_FROM_CORE;
		if (is_multisite() && !is_network_admin()) {
			$useSubmenu = false;
		}
		
		add_action('admin_menu', array($this, '_admin_menu'), $useSubmenu ? 55 : 10);
		if (is_multisite()) {
			add_action('network_admin_menu', array($this, '_admin_menu'), $useSubmenu ? 55 : 10);
		}
		add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
		
		add_action('show_user_profile', array($this, '_edit_user_profile'), 0); //We can't add it to the password section directly -- priority 0 is as close as we can get
		add_action('edit_user_profile', array($this, '_edit_user_profile'), 0);
	}
	
	public function _admin_init() {
		if (WORDFENCE_LS_FROM_CORE) {
			\wfModuleController::shared()->addOptionIndex('wfls-option-enable-2fa-roles', __('Login Security: Enable 2FA for these roles', 'wordfence-2fa'));
			\wfModuleController::shared()->addOptionIndex('wfls-option-allow-remember', __('Login Security: Allow remembering device for 30 days', 'wordfence-2fa'));
			\wfModuleController::shared()->addOptionIndex('wfls-option-require-2fa-xml-rpc', __('Login Security: Require 2FA for XML-RPC call authentication', 'wordfence-2fa'));
			\wfModuleController::shared()->addOptionIndex('wfls-option-disable-xml-rpc', __('Login Security: Disable XML-RPC authentication', 'wordfence-2fa'));
			\wfModuleController::shared()->addOptionIndex('wfls-option-whitelist-2fa', __('Login Security: Allowlisted IP addresses that bypass 2FA', 'wordfence-2fa'));
			\wfModuleController::shared()->addOptionIndex('wfls-option-enable-captcha', __('Login Security: Enable reCAPTCHA on the login and user registration pages', 'wordfence-2fa'));
			
			$title = __('Login Security Options', 'wordfence-ls');
			$description = __('Login Security options are available on the Login Security options page', 'wordfence-ls');
			$url = esc_url(network_admin_url('admin.php?page=WFLS#top#settings'));
			$link = __('Login Security Options', 'wordfence');;
			\wfModuleController::shared()->addOptionBlock(<<<END
<div class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-block wf-always-active" data-persistence-key="">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong>{$title}</strong>
					</div>
				</div>
			</div>
			<div class="wf-block-content">
				<ul class="wf-block-list">
					<li>
						<ul class="wf-flex-horizontal wf-flex-vertical-xs wf-flex-full-width wf-add-top wf-add-bottom">
							<li>{$description}</li>
							<li class="wf-right wf-left-xs wf-padding-add-top-xs-small">
								<a href="{$url}" class="wf-btn wf-btn-primary wf-btn-callout-subtle" id="wf-login-security-options">{$link}</a>
							</li>
						</ul>
						<input type="hidden" id="wfls-option-enable-2fa-roles">
						<input type="hidden" id="wfls-option-allow-remember">
						<input type="hidden" id="wfls-option-require-2fa-xml-rpc">
						<input type="hidden" id="wfls-option-disable-xml-rpc">
						<input type="hidden" id="wfls-option-whitelist-2fa">
						<input type="hidden" id="wfls-option-enable-captcha">
					</li>
				</ul>
			</div>
		</div>
	</div>
</div> <!-- end ls options -->
END
);
		}
		
		if ((is_plugin_active('jetpack/jetpack.php') || (is_multisite() && is_plugin_active_for_network('jetpack/jetpack.php'))) && !Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_ALLOW_XML_RPC) && Controller_Permissions::shared()->can_manage_settings()) {
			if (is_multisite()) {
				add_action('network_admin_notices', array($this, '_jetpack_xml_rpc_notice'));
			}
			else {
				add_action('admin_notices', array($this, '_jetpack_xml_rpc_notice'));
			}
		}
		
		if (Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_CAPTCHA_TEST_MODE) && Controller_CAPTCHA::shared()->enabled() && Controller_Permissions::shared()->can_manage_settings()) {
			if (is_multisite()) {
				add_action('network_admin_notices', array($this, '_recaptcha_test_notice'));
			}
			else {
				add_action('admin_notices', array($this, '_recaptcha_test_notice'));
			}
		}
	}
	
	/**
	 * Notices
	 */
	
	public function _jetpack_xml_rpc_notice() {
		echo '<div class="notice notice-warning"><p>' . wp_kses(sprintf(__('XML-RPC authentication is disabled. Jetpack is currently active and requires XML-RPC authentication to work correctly. <a href="%s">Manage Settings</a>', 'wordfence-2fa'), esc_url(network_admin_url('admin.php?page=WFLS#top#settings'))), array('a'=>array('href'=>array()))) . '</p></div>';
	}
	
	public function _recaptcha_test_notice() {
		echo '<div class="notice notice-warning"><p>' . wp_kses(sprintf(__('reCAPTCHA test mode is enabled. While enabled, login and registration requests will be checked for their score but will not be blocked if the score is below the minimum score. <a href="%s">Manage Settings</a>', 'wordfence-2fa'), esc_url(network_admin_url('admin.php?page=WFLS#top#settings'))), array('a'=>array('href'=>array()))) . '</p></div>';
	}
	
	/**
	 * Installation/Uninstallation
	 */
	
	public function _install_plugin() {
		$this->_install();
	}
	
	public function _uninstall_plugin() {
		Controller_Time::shared()->uninstall();
		
		foreach (array(self::VERSION_KEY) as $opt) {
			if (is_multisite() && function_exists('delete_network_option')) {
				delete_network_option(null, $opt);
			}
			delete_option($opt);
		}
		
		if (Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_DELETE_ON_DEACTIVATION)) {
			Controller_DB::shared()->uninstall();
		}
	}
	
	protected function _install() {
		static $_runInstallCalled = false;
		if ($_runInstallCalled) { return; }
		$_runInstallCalled = true;
		
		if (function_exists('ignore_user_abort')) {
			@ignore_user_abort(true);
		}
		
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		
		$previousVersion = ((is_multisite() && function_exists('get_network_option')) ? get_network_option(null, self::VERSION_KEY, '0.0.0') : get_option(self::VERSION_KEY, '0.0.0'));
		if (is_multisite() && function_exists('update_network_option')) {
			update_network_option(null, self::VERSION_KEY, WORDFENCE_LS_VERSION); //In case we have a fatal error we don't want to keep running install.	
		}
		else {
			update_option(self::VERSION_KEY, WORDFENCE_LS_VERSION); //In case we have a fatal error we don't want to keep running install.
		}
		
		Controller_DB::shared()->install();
		Controller_Settings::shared()->set_defaults();
		
		if (\WordfenceLS\Controller_Time::time() > Controller_Settings::shared()->get_int(Controller_Settings::OPTION_LAST_SECRET_REFRESH) + 180 * 86400) {
			Model_Crypto::refresh_secrets();
		}
		
		Controller_Time::shared()->install();
		Controller_Permissions::shared()->install();
	}
	
	public function _block_xml_rpc() {
		/**
		 * Fires just prior to blocking an XML-RPC request. After firing this action hook the XML-RPC request is blocked.
		 *
		 * @param int $source The source code of the block.
		 */
		do_action('wfls_xml_rpc_blocked', 2);
		return false;
	}
	
	/**
	 * Login Page
	 */
	
	public function _login_enqueue_scripts() {
		$useCAPTCHA = Controller_CAPTCHA::shared()->enabled();
		if ($useCAPTCHA) {
			wp_enqueue_script('wordfence-ls-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . urlencode(Controller_Settings::shared()->get(Controller_Settings::OPTION_RECAPTCHA_SITE_KEY)));
		}
		
		if ($useCAPTCHA || Controller_Users::shared()->any_2fa_active()) {
			$verification = '';
			if (isset($_REQUEST['wfls-email-verification']) && is_string($_REQUEST['wfls-email-verification'])) {
				$jwt = Model_JWT::decode_jwt($_REQUEST['wfls-email-verification']);
				if ($jwt && isset($jwt->payload['user'])) {
					$verification = $_REQUEST['wfls-email-verification'];
				}
			}
			
			wp_enqueue_script('wordfence-ls-login', Model_Asset::js('login.js'), array('jquery'), WORDFENCE_LS_VERSION);
			wp_enqueue_style('wordfence-ls-login', Model_Asset::css('login.css'), array(), WORDFENCE_LS_VERSION);
			wp_localize_script('wordfence-ls-login', 'WFLSVars', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('wp-ajax'),
				'recaptchasitekey' => Controller_Settings::shared()->get(Controller_Settings::OPTION_RECAPTCHA_SITE_KEY),
				'useCAPTCHA' => $useCAPTCHA,
				'allowremember' => Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REMEMBER_DEVICE_ENABLED),
				'verification' => $verification,
			));
		}
	}
	
	/**
	 * Admin Pages
	 */
	public function _admin_enqueue_scripts() {
		if (isset($_GET['page']) && $_GET['page'] == 'WFLS') {
			wp_enqueue_script('wordfence-ls-jquery.qrcode', Model_Asset::js('jquery.qrcode.min.js'), array('jquery'), WORDFENCE_LS_VERSION);
			wp_enqueue_script('wordfence-ls-jquery.tmpl', Model_Asset::js('jquery.tmpl.min.js'), array('jquery'), WORDFENCE_LS_VERSION);
			wp_enqueue_script('wordfence-ls-jquery.colorbox', Model_Asset::js('jquery.colorbox.min.js'), array('jquery'), WORDFENCE_LS_VERSION);
			if (Controller_Permissions::shared()->can_manage_settings()) { 
				wp_enqueue_script('wordfence-ls-jquery-ui.timepicker', Model_Asset::js('jquery-ui-timepicker-addon.js'), array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), WORDFENCE_LS_VERSION);
				
				wp_enqueue_style('wordfence-ls-jquery-ui-css', Model_Asset::css('jquery-ui.min.css'), array(), WORDFENCE_LS_VERSION);
				wp_enqueue_style('wordfence-ls-jquery-ui-css.structure', Model_Asset::css('jquery-ui.structure.min.css'), array(), WORDFENCE_LS_VERSION);
				wp_enqueue_style('wordfence-ls-jquery-ui-css.theme', Model_Asset::css('jquery-ui.theme.min.css'), array(), WORDFENCE_LS_VERSION);
				wp_enqueue_style('wordfence-ls-jquery-ui-css.timepicker', Model_Asset::css('jquery-ui-timepicker-addon.css'), array(), WORDFENCE_LS_VERSION);
			}
			wp_enqueue_script('wordfence-ls-admin', Model_Asset::js('admin.js'), array('jquery'), WORDFENCE_LS_VERSION);
			if (!WORDFENCE_LS_FROM_CORE) {
				wp_register_script('chart-js', Model_Asset::js('Chart.bundle.min.js'), array('jquery'), '2.4.0');
				wp_register_script('wordfence-select2-js', Model_Asset::js('wfselect2.min.js'), array('jquery'), WORDFENCE_LS_VERSION);
				wp_register_style('wordfence-select2-css', Model_Asset::css('wfselect2.min.css'), array(), WORDFENCE_LS_VERSION);
			}
			wp_enqueue_script('chart-js');
			wp_enqueue_script('wordfence-select2-js');
			wp_enqueue_style('wordfence-select2-css');
			wp_enqueue_style('wordfence-ls-admin', Model_Asset::css('admin.css'), array(), WORDFENCE_LS_VERSION);
			wp_enqueue_style('wordfence-ls-colorbox', Model_Asset::css('colorbox.css'), array(), WORDFENCE_LS_VERSION);
			wp_enqueue_style('wordfence-ls-ionicons', Model_Asset::css('ionicons.css'), array(), WORDFENCE_LS_VERSION);
			if (!WORDFENCE_LS_FROM_CORE) { wp_enqueue_style('wordfence-ls-font-awesome', Model_Asset::css('font-awesome.css'), array(), WORDFENCE_LS_VERSION); }
			wp_localize_script('wordfence-ls-admin', 'WFLSVars', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('wp-ajax'),
				'modalTemplate' => Model_View::create('common/modal-prompt', array('title' => '${title}', 'message' => '${message}', 'primaryButton' => array('id' => 'wfls-generic-modal-close', 'label' => __('Close', 'wordfence'), 'link' => '#')))->render(),
				'tokenInvalidTemplate' => Model_View::create('common/modal-prompt', array('title' => '${title}', 'message' => '${message}', 'primaryButton' => array('id' => 'wfls-token-invalid-modal-reload', 'label' => __('Reload', 'wordfence'), 'link' => '#')))->render(),
				'modalHTMLTemplate' => Model_View::create('common/modal-prompt', array('title' => '${title}', 'message' => '{{html message}}', 'primaryButton' => array('id' => 'wfls-generic-modal-close', 'label' => __('Close', 'wordfence'), 'link' => '#')))->render(),
			));
		}
		else {
			wp_enqueue_style('wordfence-ls-admin-global', Model_Asset::css('admin-global.css'), array(), WORDFENCE_LS_VERSION);
		}
		
		if (Controller_Notices::shared()->has_notice(wp_get_current_user())) {
			wp_enqueue_script('wordfence-ls-admin-global', Model_Asset::js('admin-global.js'), array('jquery'), WORDFENCE_LS_VERSION);
			
			wp_localize_script('wordfence-ls-admin-global', 'GWFLSVars', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('wp-ajax'),
			));
		}
	}
	
	public function _edit_user_profile($user) {
		if ($user->ID == get_current_user_id() || !current_user_can(Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)) {
			$manageURL = admin_url('admin.php?page=WFLS');
		}
		else {
			$manageURL = admin_url('admin.php?page=WFLS&user=' . ((int) $user->ID));
		}
		
		if (is_multisite() && is_super_admin()) {
			if ($user->ID == get_current_user_id()) {
				$manageURL = network_admin_url('admin.php?page=WFLS');
			}
			else {
				$manageURL = network_admin_url('admin.php?page=WFLS&user=' . ((int) $user->ID));
			}
		}
		
		if (Controller_Users::shared()->can_activate_2fa($user) && $user->ID == get_current_user_id()):
		?>
		<h2><?php esc_html_e('Wordfence Login Security', 'wordfence-2fa'); ?></h2>
		<table class="form-table">
			<tr id="wordfence-ls">
				<th><label for="wordfence-ls-btn"><?php esc_html_e('2FA Status'); ?></label></th>
				<td>
					<p><strong><?php echo (Controller_Users::shared()->has_2fa_active($user) ? esc_html__('Active', 'wordfence-2fa') :  esc_html__('Inactive', 'wordfence-2fa')); ?>:</strong> <?php echo (Controller_Users::shared()->has_2fa_active($user) ? esc_html__('Wordfence 2FA is active.', 'wordfence-2fa') :  esc_html__('Wordfence 2FA is inactive.', 'wordfence-2fa')); ?> <a href="<?php echo Controller_Support::esc_supportURL(Controller_Support::ITEM_MODULE_LOGIN_SECURITY_2FA); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence-2fa'); ?></a></p>
					<p><a href="<?php echo esc_url($manageURL); ?>" class="button" id="wordfence-ls-btn"><?php echo (Controller_Users::shared()->has_2fa_active($user) ? esc_html__('Manage 2FA', 'wordfence-2fa') :  esc_html__('Activate 2FA', 'wordfence-2fa')); ?></a></p>
				</td>
			</tr>
		</table>
		<?php
		elseif (current_user_can(Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)):
		?>
		<h2><?php esc_html_e('Wordfence Login Security', 'wordfence-2fa'); ?></h2>
		<table class="form-table">
			<tr id="wordfence-ls">
				<th><label for="wordfence-ls-btn"><?php esc_html_e('2FA Status'); ?></label></th>
				<td>
				<?php if (Controller_Users::shared()->can_activate_2fa($user)): ?>
					<p><strong><?php echo (Controller_Users::shared()->has_2fa_active($user) ? esc_html__('Active', 'wordfence-2fa') :  esc_html__('Inactive', 'wordfence-2fa')); ?>:</strong> <?php echo (Controller_Users::shared()->has_2fa_active($user) ? esc_html__('Wordfence 2FA is active.', 'wordfence-2fa') :  esc_html__('Wordfence 2FA is inactive.', 'wordfence-2fa')); ?> <a href="<?php echo Controller_Support::esc_supportURL(Controller_Support::ITEM_MODULE_LOGIN_SECURITY_2FA); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence-2fa'); ?></a></p>
					<?php if (Controller_Users::shared()->has_2fa_active($user)): ?><p><a href="<?php echo esc_url($manageURL); ?>" class="button" id="wordfence-ls-btn"><?php echo esc_html__('Manage 2FA', 'wordfence-2fa'); ?></a></p><?php endif; ?>
				<?php else: ?>
					<p><strong><?php esc_html_e('Disabled', 'wordfence-2fa'); ?>:</strong> <?php esc_html_e('Two-factor authentication is not currently enabled for this account type. To enable it, visit the Wordfence 2FA Settings page.', 'wordfence-2fa'); ?> <a href="#"><?php esc_html_e('Learn More', 'wordfence-2fa'); ?></a></p>
					<p><a href="<?php echo esc_url(is_multisite() ? network_admin_url('admin.php?page=WFLS#top#settings') : admin_url('admin.php?page=WFLS#top#settings')); ?>" class="button" id="wordfence-ls-btn"><?php esc_html_e('Manage 2FA Settings', 'wordfence-2fa'); ?></a></p>
				<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
		endif;
	}
	
	/**
	 * Authentication
	 */
	
	public function _authenticate($user, $username, $password) {
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST && !Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_XMLRPC_ENABLED)) { //XML-RPC call and we're not enforcing 2FA on it
			return $user;
		}
		
		if (Controller_Whitelist::shared()->is_whitelisted(Model_Request::current()->ip())) { //Whitelisted, so we're not enforcing 2FA
			return $user;
		}
		
		/*
		 * CAPTCHA Check
		 * 
		 * It will be enforced so long as:
		 * 
		 * 1. It's enabled and keys are set.
		 * 2. This is not an XML-RPC request. An XML-RPC request is de facto an automated request, so a CAPTCHA makes
		 *    no sense.
		 * 3. A filter does not override it. This is to allow plugins with REST endpoints that handle authentication
		 *    themselves to opt out of the requirement.
		 * 4. The user does not have 2FA enabled. 2FA exempts the user from requiring email verification if the score is 
		 *    below the threshold.
		 */
		if (!empty($username)) { //Login attempt, not just a wp-login.php page load
			$requireCAPTCHA = Controller_CAPTCHA::shared()->enabled() && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST); //CAPTCHA is enabled, not an XML-RPC request
			$requireCAPTCHA = apply_filters('wordfence_ls_require_captcha', $requireCAPTCHA);
			
			$performVerification = false;
			$token = (isset($_POST['wfls-captcha-token']) && is_string($_POST['wfls-captcha-token']) ? $_POST['wfls-captcha-token'] : '');
			if ($requireCAPTCHA && empty($token) && !Controller_CAPTCHA::shared()->test_mode()) { //No CAPTCHA token means forced additional verification (if neither 2FA nor test mode are active)
				$performVerification = true;
			}
			
			if ($requireCAPTCHA && !$performVerification) {
				if (isset($_POST['wfls-captcha-jwt']) && is_string($_POST['wfls-captcha-jwt']) && is_object($user) && $user instanceof \WP_User) {
					$jwt = Model_JWT::decode_jwt($_POST['wfls-captcha-jwt']);
					if ($jwt && isset($jwt->payload['nonce'])) {
						$encryptedNonce = $jwt->payload['nonce'];
						$nonce = Model_Symmetric::decrypt($encryptedNonce);
						if ($nonce) {
							$cachedJSON = get_user_meta($user->ID, 'wfls-captcha-nonce', true);
							$cached = @json_decode($cachedJSON, true); //Expected: nonce, score, token, expiration
							if (is_array($cached) && isset($cached['expiration']) && Controller_Time::time() <= $cached['expiration'] && hash_equals($cached['token'], $token) && hash_equals(bin2hex($nonce), $cached['nonce'])) {
								$score = (float) $cached['score'];
							}
							delete_user_meta($user->ID, 'wfls-captcha-nonce');
						}
						//else - unable to decrypt, probably a host error, so let it fall through to a re-check
					}
					//else - invalid JWT or host error, so let it fall through to a re-check
				}
				
				if (!isset($score)) {
					$score = Controller_CAPTCHA::shared()->score($token);
					if ($score === false && !Controller_CAPTCHA::shared()->test_mode()) { //An invalid token will require additional verification (if neither 2FA nor test mode are active)
						$performVerification = true;
					}
				}
			}
			
			if (!isset($score)) { $score = false; }
			
			if (is_object($user) && $user instanceof \WP_User) {
				if (Controller_Users::shared()->has_2fa_active($user)) { //CAPTCHA enforcement skipped for users with 2FA active
					$requireCAPTCHA = false;
					$performVerification = false;
				}
				else { //Cache the score/token combo for this specific user
					$nonce = Model_Crypto::random_bytes(32);
					$encryptedNonce = Model_Symmetric::encrypt($nonce);
					if ($encryptedNonce) {
						update_user_meta($user->ID, 'wfls-captcha-nonce', json_encode(array('nonce' => bin2hex($nonce), 'score' => $score, 'token' => $token, 'expiration' => Controller_Time::time() + 30)));
						$jwt = new Model_JWT(array('nonce' => $encryptedNonce), Controller_Time::time() + 30);
						if (!defined('WORDFENCE_LS_CAPTCHA_CACHE')) { define('WORDFENCE_LS_CAPTCHA_CACHE', (string) $jwt); }
					}
					// else Can't generate payload, so we'll end up re-querying the reCAPTCHA token next hit
				}
				
				Controller_Users::shared()->record_captcha_score($user, $score);
				
				if (isset($_REQUEST['wfls-email-verification']) && !empty($_REQUEST['wfls-email-verification']) && is_string($_REQUEST['wfls-email-verification'])) {
					$jwt = Model_JWT::decode_jwt($_REQUEST['wfls-email-verification']);
					if ($jwt && isset($jwt->payload['user'])) {
						$decryptedUser = Model_Symmetric::decrypt($jwt->payload['user']);
						if (!$decryptedUser || $decryptedUser == $user->ID) { //Skip the CAPTCHA check if the user in the JWT matches or decryption failed due to a server error
							$requireCAPTCHA = false;
							$performVerification = false;
						}
					}
				}
				
				if ($requireCAPTCHA && !$performVerification) {
					if (!Controller_CAPTCHA::shared()->is_human($score)) { //Score is below the human threshold, require email verification
						$performVerification = true;
					}
				}
				
				if ($requireCAPTCHA && $performVerification) {
					$encrypted = Model_Symmetric::encrypt((string) $user->ID);
					if ($encrypted) {
						$jwt = new Model_JWT(array('user' => $encrypted), Controller_Time::time() + 60 * WORDFENCE_LS_EMAIL_VALIDITY_DURATION_MINUTES);
						$view = new Model_View('email/login-verification', array(
							'siteName' => get_bloginfo('name', 'raw'),
							'siteURL' => rtrim(site_url(), '/') . '/',
							'verificationURL' => add_query_arg(array('wfls-email-verification' => (string) $jwt), wp_login_url()),
							'ip' => Model_Request::current()->ip(),
							'canEnable2FA' => Controller_Users::shared()->can_activate_2fa($user),
						));
						wp_mail($user->user_email, __('Login Verification Required', 'wordfence-ls'), $view->render(), "Content-Type: text/html");
						
						return new \WP_Error('wfls_captcha_verify', wp_kses(__('<strong>VERIFICATION REQUIRED</strong>: Additional verification is required for login. Please check the email address associated with the account for a verification link.', 'wordfence-ls'), array('strong'=>array())));
					}
					//else -- Can't generate payload due to host failure, allow it to proceed
				}
			}
		}
		
		/*
		 * Check 1
		 * 
		 * If we have a valid JWT that authenticates the account _and_ code, fetch and return that user.
		 */
		if (isset($_POST['wfls-token-jwt']) && is_string($_POST['wfls-token-jwt'])) {
			$jwt = Model_JWT::decode_jwt($_POST['wfls-token-jwt']);
			if (!$jwt) { //Possibly user-corrupted or expired JWT
				return new \WP_Error('wfls_twofactor_invalid', wp_kses(__('<strong>VALIDATION FAILED</strong>: The 2FA code could not be validated. Please try logging in again.', 'wordfence-2fa'), array('strong'=>array())));
			}
			
			if (!isset($jwt->payload['user'])) { //Possibly user-corrupted JWT
				return new \WP_Error('wfls_twofactor_invalid', wp_kses(__('<strong>VALIDATION FAILED</strong>: The 2FA code could not be validated. Please try logging in again.', 'wordfence-2fa'), array('strong'=>array())));
			}
			
			$decryptedUser = Model_Symmetric::decrypt($jwt->payload['user']);
			if (!$decryptedUser) {
				return $user; //Likely a server failure, allow authentication without our authenticate filter
			}
			
			if (isset($jwt->payload['nonce'])) { //JWT includes previous token validation
				$decryptedNonce = Model_Symmetric::decrypt($jwt->payload['nonce']);
				if (!$decryptedNonce) {
					return $user; //Likely a server failure, allow authentication without our authenticate filter
				}
				
				$expectedNonceJSON = get_user_meta((int) $decryptedUser, 'wfls-nonce', true);
				$expectedNonce = @json_decode($expectedNonceJSON, true);
				if ($expectedNonce && $expectedNonce['expiration'] > Controller_Time::time() && hash_equals($decryptedNonce, Model_Compat::hex2bin($expectedNonce['nonce']))) {
					delete_user_meta((int) $decryptedUser, 'wfls-nonce');
					$user = new \WP_User((int) $decryptedUser);
					return $user;
				}
				
				//Invalid nonce or expired nonce
				return new \WP_Error('wfls_twofactor_invalid', wp_kses(__('<strong>VALIDATION FAILED</strong>: The 2FA code could not be validated. Please try logging in again.', 'wordfence-2fa'), array('strong'=>array())));
			}
		}
		
		/*
		 * Check 2
		 * 
		 * If we don't have a valid $user at this point, it means the $username/$password combo is invalid. We'll check 
		 * to see if the user has provided a combined password in the format `<password><code>`, populating $user from
		 * that if so.
		 */
		if (!defined('WORDFENCE_LS_CHECKING_COMBINED') && (!isset($_POST['wfls-token']) || !is_string($_POST['wfls-token'])) && (!is_object($user) || !($user instanceof \WP_User))) {
			//Compatibility with WF legacy 2FA
			$combinedTOTPRegex = '/((?:[0-9]{3}\s*){2})$/i';
			$combinedRecoveryRegex = '/((?:[a-f0-9]{4}\s*){4})$/i';
			if ($this->legacy_2fa_active()) {
				$combinedTOTPRegex = '/(?<! wf)((?:[0-9]{3}\s*){2})$/i';
				$combinedRecoveryRegex = '/(?<! wf)((?:[a-f0-9]{4}\s*){4})$/i';
			}
			
			if (preg_match($combinedTOTPRegex, $password, $matches)) { //Possible TOTP code
				if (strlen($password) > strlen($matches[1])) {
					$revisedPassword = substr($password, 0, strlen($password) - strlen($matches[1]));
					$code = $matches[1];
				}
			}
			else if (preg_match($combinedRecoveryRegex, $password, $matches)) { //Possible recovery code
				if (strlen($password) > strlen($matches[1])) {
					$revisedPassword = substr($password, 0, strlen($password) - strlen($matches[1]));
					$code = $matches[1];
				}
			}
			
			if (isset($revisedPassword)) {
				define('WORDFENCE_LS_CHECKING_COMBINED', true); //Avoid recursing into this block
				if (!defined('WORDFENCE_LS_AUTHENTICATION_CHECK')) { define('WORDFENCE_LS_AUTHENTICATION_CHECK', true); }
				$revisedUser = wp_authenticate($username, $revisedPassword);
				if (is_object($revisedUser) && ($revisedUser instanceof \WP_User) && Controller_TOTP::shared()->validate_2fa($revisedUser, $code)) {
					define('WORDFENCE_LS_COMBINED_IS_VALID', true); //AJAX call will use this to generate a different JWT that authenticates for the account _and_ code
					return $revisedUser;
				}
			}
		}
		
		/*
		 * Check 3
		 * 
		 * If we have a valid JWT user and the user has provided a code, check to see if the code is valid. If it is,
		 * the JWT user is returned.
		 */
		if (isset($decryptedUser) && isset($_POST['wfls-token']) && is_string($_POST['wfls-token'])) {
			$jwtUser = new \WP_User((int) $decryptedUser);
			if (Controller_Users::shared()->has_2fa_active($jwtUser)) {
				if (Controller_TOTP::shared()->validate_2fa($jwtUser, $_POST['wfls-token'])) {
					define('WORDFENCE_LS_COMBINED_IS_VALID', true); //AJAX call will use this to generate a different JWT that authenticates for the account _and_ code
					return $jwtUser;
				}
				
				return new \WP_Error('wfls_twofactor_failed', wp_kses(__('<strong>CODE INVALID</strong>: The 2FA code provided is either expired or invalid. Please try again.', 'wordfence-2fa'), array('strong'=>array())));
			}
		}
		
		if (defined('WORDFENCE_LS_AUTHENTICATION_CHECK') && WORDFENCE_LS_AUTHENTICATION_CHECK) { //Checking for the purpose of prompting for 2FA, don't enforce it here -- AJAX calls will halt here, POST will continue
			return $user;
		}
		
		/*
		 * Check 4
		 * 
		 * If we have a user from a previous filter, check to see if it has 2FA enabled or a remembered 2FA. If it does, it has not
		 * provided a code, so block its login.
		 */
		if (is_object($user) && ($user instanceof \WP_User)) {
			if (Controller_Users::shared()->has_remembered_2fa($user)) {
				return $user;
			}
			
			if (Controller_Users::shared()->has_2fa_active($user)) {
				$legacy2FAActive = Controller_WordfenceLS::shared()->legacy_2fa_active();
				if ($legacy2FAActive) {
					return new \WP_Error('wfls_twofactor_required', wp_kses(__('<strong>CODE REQUIRED</strong>: Please enter your 2FA code immediately after your password in the same field.', 'wordfence-2fa'), array('strong'=>array())));
				}
				return new \WP_Error('wfls_twofactor_required', wp_kses(__('<strong>CODE REQUIRED</strong>: Please provide your 2FA code when prompted.', 'wordfence-2fa'), array('strong'=>array())));
			}
			else if (Controller_Users::shared()->requires_2fa($user)) {
				return new \WP_Error('wfls_twofactor_blocked', wp_kses(__('<strong>LOGIN BLOCKED</strong>: 2FA is required to be active on all administrator accounts.', 'wordfence-2fa'), array('strong'=>array())));
			}
			else if (defined('WFLS_WILL_BE_REQUIRED') && WFLS_WILL_BE_REQUIRED) {
				Controller_Notices::shared()->add_notice(Model_Notice::SEVERITY_CRITICAL, new Model_HTML(wp_kses(sprintf(__('You do not currently have two-factor authentication active on your account, which will be required beginning %s. <a href="%s">Configure 2FA</a>', 'wordfence-2fa'), Controller_Time::format_local_time('F j, Y', Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD)), esc_url((is_multisite() && is_super_admin($user->ID)) ? network_admin_url('admin.php?page=WFLS') : admin_url('admin.php?page=WFLS'))), array('a'=>array('href'=>array())))), 'wfls-will-be-required', $user);
			}
		}
		
		return $user;
	}
	
	public function _set_logged_in_cookie($logged_in_cookie, $expire, $expiration, $user_id) {
		$user = new \WP_User($user_id);
		if (Controller_Users::shared()->has_2fa_active($user) && isset($_POST['wfls-remember-device']) && $_POST['wfls-remember-device']) {
			Controller_Users::shared()->remember_2fa($user);
		}
		delete_user_meta($user_id, 'wfls-captcha-nonce');
	}
	
	public function _record_login($user_login/*, $user -- we'd like to use the second parameter instead, but too many plugins call this hook and only provide one of the two required parameters*/) {
		$user = get_user_by('login', $user_login);
		if (is_object($user) && $user instanceof \WP_User && $user->exists()) {
			update_user_meta($user->ID, 'wfls-last-login', Controller_Time::time());
		}
	}
	
	public function _register_post($sanitized_user_login, $user_email, $errors) {
		if (Controller_Whitelist::shared()->is_whitelisted(Model_Request::current()->ip())) { //Whitelisted, so we're not enforcing 2FA
			return;
		}
		
		/*
		 * CAPTCHA Check
		 * 
		 * It will be enforced so long as:
		 * 
		 * 1. It's enabled and keys are set.
		 * 2. This is not an XML-RPC request. An XML-RPC request is de facto an automated request, so a CAPTCHA makes
		 *    no sense.
		 * 3. A filter does not override it. This is to allow plugins with REST endpoints that handle authentication
		 *    themselves to opt out of the requirement.
		 */
		$requireCAPTCHA = Controller_CAPTCHA::shared()->enabled() && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST); //CAPTCHA is enabled, not an XML-RPC request
		$requireCAPTCHA = apply_filters('wordfence_ls_require_captcha', $requireCAPTCHA);

		$token = (isset($_POST['wfls-captcha-token']) && is_string($_POST['wfls-captcha-token']) ? $_POST['wfls-captcha-token'] : '');

		if ($requireCAPTCHA && empty($token) && !empty($sanitized_user_login) && !Controller_CAPTCHA::shared()->test_mode()) { //A CAPTCHA token must be provided for the login attempt to proceed past this point except in test mode
			$errors->add('wfls_captcha_required', wp_kses(__('<strong>REGISTRATION ATTEMPT BLOCKED</strong>: This site requires a security token created when the page loads for all registration attempts. Please ensure JavaScript is enabled and try again.', 'wordfence-ls'), array('strong'=>array())));
			return;
		}
		
		$score = false;
		if ($requireCAPTCHA) {
			$score = Controller_CAPTCHA::shared()->score($token);
			if ($score === false && !Controller_CAPTCHA::shared()->test_mode()) { //The token must be valid except in test mode
				$errors->add('wfls_captcha_required', wp_kses(__('<strong>REGISTRATION ATTEMPT BLOCKED</strong>: The security token for the login attempt was invalid or expired. Please reload the page and try again.', 'wordfence-ls'), array('strong'=>array())));
				return;
			}
		}
		
		if ($requireCAPTCHA) {
			Controller_Users::shared()->record_captcha_score(null, $score);
			
			if (!Controller_CAPTCHA::shared()->is_human($score)) { //Score is below the human threshold, block the user registration
				$encryptedIP = Model_Symmetric::encrypt(Model_Request::current()->ip());
				$encryptedScore = Model_Symmetric::encrypt($score);
				if ($encryptedIP && $encryptedScore && filter_var(get_site_option('admin_email'), FILTER_VALIDATE_EMAIL)) {
					$jwt = new Model_JWT(array('ip' => $encryptedIP, 'score' => $encryptedScore), Controller_Time::time() + 600);
					$token = (string) $jwt;
					
					$message = wp_kses(sprintf(__('<strong>REGISTRATION BLOCKED</strong>: The registration request was blocked because it was flagged as spam. Please try again or <a href="#" class="wfls-registration-captcha-contact" data-token="%s">contact the site owner</a> for help.', 'wordfence-ls'), esc_attr($token)), array('strong'=>array(), 'a'=>array('href'=>array(), 'class'=>array(), 'data-token'=>array())));
				}
				else {
					$message = wp_kses(__('<strong>REGISTRATION BLOCKED</strong>: The registration request was blocked because it was flagged as spam. Please try again or contact the site owner for help.', 'wordfence-ls'), array('strong'=>array()));
				}
				
				/**
				 * Fires just prior to blocking user registration due to a failed CAPTCHA. After firing this action hook 
				 * the registration attempt is blocked.
				 *
				 * @param int $source The source code of the block.
				 */
				do_action('wfls_registration_blocked', 1);
				
				/**
				 * Filters the message to show if registration is blocked due to a captcha rejection.
				 *
				 * @since 1.0.0
				 *
				 * @param string $message The message to display, HTML allowed.
				 */
				$message = apply_filters('wfls_registration_blocked_message', $message);
				$errors->add('wfls_registration_blocked', $message);
				return;
			}
		}
	}
	
	/**
	 * @param \WP_Error $errors
	 * @param string $redirect_to
	 * @return \WP_Error
	 */
	public function _wp_login_errors($errors, $redirect_to) {
		$has_errors = (method_exists($errors, 'has_errors') ? $errors->has_errors() : !empty($errors->errors)); //has_errors was added in WP 5.1
		if (!$has_errors && isset($_REQUEST['wfls-email-verification']) && is_string($_REQUEST['wfls-email-verification'])) {
			$jwt = Model_JWT::decode_jwt($_REQUEST['wfls-email-verification']);
			if ($jwt && isset($jwt->payload['user'])) {
				$errors->add('wfls_email_verified', esc_html__('Email verification succeeded. Please continue logging in.', 'wordfence-2fa'), 'message');
			}
			else {
				$errors->add('wfls_email_not_verified', esc_html__('Email verification invalid or expired. Please try again.', 'wordfence-2fa'), 'message');
			}
		}
		return $errors;
	}
	
	public function legacy_2fa_active() {
		$wfLegacy2FAActive = false;
		if (class_exists('wfConfig') && \wfConfig::get('isPaid')) {
			$twoFactorUsers = \wfConfig::get_ser('twoFactorUsers', array());
			if (is_array($twoFactorUsers) && count($twoFactorUsers) > 0) {
				foreach ($twoFactorUsers as $t) {
					if ($t[3] == 'activated') {
						$testUser = get_user_by('ID', $t[0]);
						if (is_object($testUser) && $testUser instanceof \WP_User && \wfUtils::isAdmin($testUser)) {
							$wfLegacy2FAActive = true;
							break;
						}
					}
				}
			}
			
			if ($wfLegacy2FAActive && class_exists('wfCredentialsController') && method_exists('wfCredentialsController', 'useLegacy2FA') && !\wfCredentialsController::useLegacy2FA()) {
				$wfLegacy2FAActive = false;
			}
		}
		return $wfLegacy2FAActive;
	}
	
	/**
	 * Menu
	 */
	
	public function _admin_menu() {
		$user = wp_get_current_user();
		if (Controller_Notices::shared()->has_notice($user)) {
			if (!Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REQUIRE_2FA_ADMIN) || !(Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD_ENABLED) && Controller_Time::time() < Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD))) {
				Controller_Notices::shared()->remove_notice(false, 'wfls-will-be-required', $user);
			}
		}
		
		Controller_Notices::shared()->enqueue_notices();
		
		$useSubmenu = WORDFENCE_LS_FROM_CORE && current_user_can('activate_plugins');
		if (is_multisite() && !is_network_admin()) {
			$useSubmenu = false;
			
			if (is_super_admin()) {
				return;
			}
		}
		
		if ($useSubmenu) {
			add_submenu_page('Wordfence', __('Login Security', 'wordfence-2fa'), __('Login Security', 'wordfence-2fa'), Controller_Permissions::CAP_ACTIVATE_2FA_SELF, 'WFLS', array($this, '_menu'));
		}
		else {
			add_menu_page(__('Login Security', 'wordfence-2fa'), __('Login Security', 'wordfence-2fa'), Controller_Permissions::CAP_ACTIVATE_2FA_SELF, 'WFLS', array($this, '_menu'), Model_Asset::img('menu.svg'));
		}
	}
	
	public function _menu() {
		$user = wp_get_current_user();
		$administrator = false;
		$canEditUsers = false;
		if (Controller_Permissions::shared()->can_manage_settings($user)) {
			$administrator = true;
		}
		
		if (user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)) {
			$canEditUsers = true;
			if (isset($_GET['user'])) {
				$user = new \WP_User((int) $_GET['user']);
				if (!$user->exists()) {
					$user = wp_get_current_user();
				}
			}
		}
		
		$sections = array(
			array(
				'tab' => new Model_Tab('manage', 'manage', __('Two-Factor Authentication', 'wordfence-2fa'), __('Two-Factor Authentication', 'wordfence-2fa')),
				'title' => new Model_Title('manage', __('Two-Factor Authentication', 'wordfence-2fa'), Controller_Support::supportURL(Controller_Support::ITEM_MODULE_LOGIN_SECURITY_2FA), new Model_HTML(wp_kses(__('Learn more<span class="wfls-hidden-xs"> about Two-Factor Authentication</span>', 'wordfence'), array('span'=>array('class'=>array()))))),
				'content' => new Model_View('page/manage', array(
					'user' => $user,
					'canEditUsers' => $canEditUsers,
				)),
			),
		);
		
		if ($administrator) {
			$sections[] = array(
				'tab' => new Model_Tab('settings', 'settings', __('Settings', 'wordfence-2fa'), __('Settings', 'wordfence-2fa')),
				'title' => new Model_Title('settings', __('Login Security Settings', 'wordfence-2fa'), Controller_Support::supportURL(Controller_Support::ITEM_MODULE_LOGIN_SECURITY), new Model_HTML(wp_kses(__('Learn more<span class="wfls-hidden-xs"> about Login Security</span>', 'wordfence'), array('span'=>array('class'=>array()))))),
				'content' => new Model_View('page/settings', array(
				)),
			);
		}
		
		$view = new Model_View('page/page', array(
			'sections' => $sections,
		));
		echo $view->render();
	}
}