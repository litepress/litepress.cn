<?php

namespace WordfenceLS;

use WordfenceLS\Crypto\Model_JWT;
use WordfenceLS\Crypto\Model_Symmetric;

class Controller_AJAX {
	protected $_actions = null; //Populated on init
	
	/**
	 * Returns the singleton Controller_AJAX.
	 *
	 * @return Controller_AJAX
	 */
	public static function shared() {
		static $_shared = null;
		if ($_shared === null) {
			$_shared = new Controller_AJAX();
		}
		return $_shared;
	}
	
	public function init() {
		$this->_actions = array(
			'authenticate' => array(
				'handler' => array($this, '_ajax_authenticate_callback'),
				'nopriv' => true,
				'nonce' => false,
				'permissions' => array(), //Format is 'permission' => 'error message'
				'required_parameters' => array(),
			),
			'register_support' => array(
				'handler' => array($this, '_ajax_register_support_callback'),
				'nopriv' => true,
				'nonce' => false,
				'permissions' => array(),
				'required_parameters' => array('user_login', 'user_email', 'wfls-message-nonce', 'wfls-message'),
			),
			'activate' => array(
				'handler' => array($this, '_ajax_activate_callback'),
				'permissions' => array(),
				'required_parameters' => array('nonce', 'secret', 'recovery', 'code', 'user'),
			),
			'deactivate' => array(
				'handler' => array($this, '_ajax_deactivate_callback'),
				'permissions' => array(),
				'required_parameters' => array('nonce', 'user'),
			),
			'regenerate' => array(
				'handler' => array($this, '_ajax_regenerate_callback'),
				'permissions' => array(),
				'required_parameters' => array('nonce', 'user'),
			),
			'save_options' => array(
				'handler' => array($this, '_ajax_save_options_callback'),
				'permissions' => array(Controller_Permissions::CAP_MANAGE_SETTINGS => __('You do not have permission to change options.', 'wordfence-2fa')),
				'required_parameters' => array('nonce', 'changes'),
			),
			'send_grace_period_notification' => array(
				'handler' => array($this, '_ajax_send_grace_period_notification_callback'),
				'permissions' => array(Controller_Permissions::CAP_MANAGE_SETTINGS => __('You do not have permission to send notifications.', 'wordfence-2fa')),
				'required_parameters' => array('nonce'),
			),
			'update_ip_preview' => array(
				'handler' => array($this, '_ajax_update_ip_preview_callback'),
				'permissions' => array(Controller_Permissions::CAP_MANAGE_SETTINGS => __('You do not have permission to change options.', 'wordfence-2fa')),
				'required_parameters' => array('nonce', 'ip_source', 'ip_source_trusted_proxies'),
			),
			'dismiss_notice' => array(
				'handler' => array($this, '_ajax_dismiss_notice_callback'),
				'permissions' => array(),
				'required_parameters' => array('nonce', 'id'),
			),
			'reset_recaptcha_stats' => array(
				'handler' => array($this, '_ajax_reset_recaptcha_stats_callback'),
				'permissions' => array(Controller_Permissions::CAP_MANAGE_SETTINGS => __('You do not have permission to reset reCAPTCHA statistics.', 'wordfence-2fa')),
				'required_parameters' => array('nonce'),
			),
		);
		
		$this->_init_actions();
	}
	
	public function _init_actions() {
		foreach ($this->_actions as $action => $parameters) {
			if (isset($parameters['nopriv']) && $parameters['nopriv']) {
				add_action('wp_ajax_nopriv_wordfence_ls_' . $action, array($this, '_ajax_handler'));
			}
			add_action('wp_ajax_wordfence_ls_' . $action, array($this, '_ajax_handler'));
		}
	}
	
	/**
	 * This is a convenience function for sending a JSON response and ensuring that execution stops after sending
	 * since wp_die() can be interrupted.
	 *
	 * @param $response
	 * @param int|null $status_code
	 */
	public static function send_json($response, $status_code = null) {
		wp_send_json($response, $status_code);
		die();
	}
	
	public function _ajax_handler() {
		$action = (isset($_POST['action']) && is_string($_POST['action']) && $_POST['action']) ? $_POST['action'] : $_GET['action'];
		if (preg_match('~wordfence_ls_([a-zA-Z_]+)$~', $action, $matches)) {
			$action = $matches[1];
			if (!isset($this->_actions[$action])) {
				self::send_json(array('error' => esc_html__('An unknown action was provided.', 'wordfence-2fa')));
			}
			
			$parameters = $this->_actions[$action];
			if (!empty($parameters['required_parameters'])) {
				foreach ($parameters['required_parameters'] as $k) {
					if (!isset($_POST[$k])) {
						self::send_json(array('error' => esc_html__('An expected parameter was not provided.', 'wordfence-2fa')));
					}
				}
			}
			
			if (!isset($parameters['nonce']) || $parameters['nonce']) {
				$nonce = (isset($_POST['nonce']) && is_string($_POST['nonce']) && $_POST['nonce']) ? $_POST['nonce'] : $_GET['nonce'];
				if (!is_string($nonce) || !wp_verify_nonce($nonce, 'wp-ajax')) {
					self::send_json(array('error' => esc_html__('Your browser sent an invalid security token. Please try reloading this page.', 'wordfence-2fa'), 'tokenInvalid' => 1));
				}
			}
			
			if (!empty($parameters['permissions'])) {
				$user = wp_get_current_user();
				foreach ($parameters['permissions'] as $permission => $error) {
					if (!user_can($user, $permission)) {
						self::send_json(array('error' => $error));
					}
				}
			}
			
			call_user_func($parameters['handler']);
		}
	}
	
	public function _ajax_authenticate_callback() {
		if (!isset($_POST['log']) || !is_string($_POST['log']) || empty($_POST['log']) || !isset($_POST['pwd']) || !is_string($_POST['pwd']) || empty($_POST['pwd'])) {
			self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: A username and password must be provided. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), wp_lostpassword_url()), array('strong'=>array(), 'a'=>array('href'=>array(), 'title'=>array())))));
		}
		
		$legacy2FAActive = Controller_WordfenceLS::shared()->legacy_2fa_active();
		if ($legacy2FAActive) { //Legacy 2FA is active, pass it on to the authenticate filter
			self::send_json(array('login' => 1));
		}
		
		$username = $_POST['log'];
		$password = $_POST['pwd'];
		do_action_ref_array('wp_authenticate', array(&$username, &$password));
		
		define('WORDFENCE_LS_AUTHENTICATION_CHECK', true); //Prevents our auth filter from recursing
		$user = wp_authenticate($username, $password);
		if (is_object($user) && ($user instanceof \WP_User)) {
			$captcha = array();
			if (defined('WORDFENCE_LS_CAPTCHA_CACHE')) {
				$captcha = array('captcha' => WORDFENCE_LS_CAPTCHA_CACHE);
			}
			
			if (!Controller_Users::shared()->has_2fa_active($user) || Controller_Whitelist::shared()->is_whitelisted(Model_Request::current()->ip()) || Controller_Users::shared()->has_remembered_2fa($user)) { //Not enabled for this user, is whitelisted, or has a valid remembered cookie, pass the credentials on to the normal login flow
				self::send_json(array_merge($captcha, array('login' => 1)));
			}
			
			$encrypted = Model_Symmetric::encrypt((string) $user->ID);
			if (!$encrypted) { //Can't generate payload due to host failure, pass the credentials on to the normal login flow
				self::send_json(array_merge($captcha, array('login' => 1)));
			}
			
			if (defined('WORDFENCE_LS_COMBINED_IS_VALID') && WORDFENCE_LS_COMBINED_IS_VALID) {
				$nonce = Model_Crypto::random_bytes(32);
				$encrypted_nonce = Model_Symmetric::encrypt($nonce);
				if (!$encrypted_nonce) { //Can't generate payload due to host failure, pass the credentials on to the normal login flow
					self::send_json(array('login' => 1));
				}
				
				update_user_meta($user->ID, 'wfls-nonce', json_encode(array('nonce' => bin2hex($nonce), 'expiration' => Controller_Time::time() + 30)));
				$jwt = new Model_JWT(array('user' => $encrypted, 'nonce' => $encrypted_nonce), Controller_Time::time() + 30);
				self::send_json(array_merge($captcha, array('login' => 1, 'jwt' => (string) $jwt, 'combined' => 1)));
			}
			
			$jwt = new Model_JWT(array('user' => $encrypted), Controller_Time::time() + 300);
			self::send_json(array_merge($captcha, array('login' => 1, 'jwt' => (string) $jwt)));
		}
		else if (is_wp_error($user)) {
			$errors = array();
			$messages = array();
			$reset = false;
			foreach ($user->get_error_codes() as $code) {
				if ($code == 'invalid_username' || $code == 'invalid_email' || $code == 'incorrect_password' || $code == 'authentication_failed') {
					$errors[] = wp_kses(sprintf(__('<strong>ERROR</strong>: The username or password you entered is incorrect. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), wp_lostpassword_url()), array('strong'=>array(), 'a'=>array('href'=>array(), 'title'=>array())));
				}
				else {
					if ($code == 'wfls_twofactor_invalid') {
						$reset = true;
					}
					
					$severity = $user->get_error_data($code);
					foreach ($user->get_error_messages($code) as $error_message) {
						if ($severity == 'message') {
							$messages[] = $error_message;
						}
						else {
							$errors[] = $error_message;
						}
					}
				}
			}
			
			if (!empty($errors)) {
				$errors = implode('<br>', $errors);
				$errors = apply_filters('login_errors', $errors);
				self::send_json(array('error' => $errors, 'reset' => $reset));
			}
			
			if (!empty($messages)) {
				$messages = implode('<br>', $messages);
				$messages = apply_filters('login_errors', $messages);
				self::send_json(array('message' => $messages, 'reset' => $reset));
			}
		}
		
		self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: The username or password you entered is incorrect. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), wp_lostpassword_url()), array('strong'=>array(), 'a'=>array('href'=>array(), 'title'=>array())))));
	}
	
	public function _ajax_register_support_callback() {
		if (!isset($_POST['user_login']) || !is_string($_POST['user_login']) ||
			!isset($_POST['user_email']) || !is_string($_POST['user_email']) ||
			!isset($_POST['wfls-message']) || !is_string($_POST['wfls-message']) ||
			!isset($_POST['wfls-message-nonce']) || !is_string($_POST['wfls-message-nonce'])) {
			self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: Unable to send message. Please refresh the page and try again.')), array('strong'=>array()))));
		}
		
		$login = sanitize_user($_POST['user_login']);
		$email = sanitize_email($_POST['user_email']);
		$message = strip_tags($_POST['wfls-message']);
		$nonce = $_POST['wfls-message-nonce'];
		
		if (empty($login) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
			self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: Unable to send message. Please refresh the page and try again.')), array('strong'=>array()))));
		}
		
		$jwt = Model_JWT::decode_jwt($_POST['wfls-message-nonce']);
		if ($jwt && isset($jwt->payload['ip']) && isset($jwt->payload['score'])) {
			$decryptedIP = Model_Symmetric::decrypt($jwt->payload['ip']);
			$decryptedScore = Model_Symmetric::decrypt($jwt->payload['score']);
			if ($decryptedIP === false || $decryptedScore === false || Model_IP::inet_pton($decryptedIP) !== Model_IP::inet_pton(Model_Request::current()->ip())) { //JWT IP and the current request's IP don't match, refuse the message
				self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: Unable to send message. Please refresh the page and try again.')), array('strong'=>array()))));
			}
			
			$identifier = bin2hex(Model_IP::inet_pton($decryptedIP));
			$tokenBucket = new Model_TokenBucket('rate:' . $identifier, 2, 1 / (6 * Model_TokenBucket::HOUR)); //Maximum of two requests, refilling at a rate of one per six hours
			if (!$tokenBucket->consume(1)) {
				self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: Unable to send message. You have exceeded the maximum number of messages that may be sent at this time. Please try again later.')), array('strong'=>array()))));
			}
			
			$email = array(
				'to'      => get_site_option('admin_email'),
				'subject' => __('Blocked User Registration Contact Form', 'wordfence-ls'),
				'body'    => sprintf(__("A visitor blocked from registration sent the following message.\n\n----------------------------------------\n\nIP: %s\nUsername: %s\nEmail: %s\nreCAPTCHA Score: %f\n\n----------------------------------------\n\n%s", 'wordfence-ls'), $decryptedIP, $login, $email, $decryptedScore, $message),
				'headers' => '',
			);
			$success = wp_mail($email['to'], $email['subject'], $email['body'], $email['headers']);
			if ($success) {
				self::send_json(array('message' => wp_kses(sprintf(__('<strong>MESSAGE SENT</strong>: Your message was sent to the site owner.')), array('strong'=>array()))));
			}
			
			self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: An error occurred while sending the message. Please try again.')), array('strong'=>array()))));
		}
		
		self::send_json(array('error' => wp_kses(sprintf(__('<strong>ERROR</strong>: Unable to send message. Please refresh the page and try again.')), array('strong'=>array()))));
	}
	
	public function _ajax_activate_callback() {
		$userID = (int) @$_POST['user'];
		$user = wp_get_current_user();
		if ($user->ID != $userID) {
			if (!user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)) {
				self::send_json(array('error' => esc_html__('You do not have permission to activate the given user.', 'wordfence-2fa')));
			}
			else {
				$user = new \WP_User($userID);
				if (!$user->exists()) {
					self::send_json(array('error' => esc_html__('The given user does not exist.', 'wordfence-2fa')));
				}
			}
		}
		else if (!user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_SELF)) {
			self::send_json(array('error' => esc_html__('You do not have permission to activate 2FA.', 'wordfence-2fa')));
		}
		
		if (Controller_Users::shared()->has_2fa_active($user)) {
			self::send_json(array('error' => esc_html__('The given user already has two-factor authentication active.', 'wordfence-2fa')));
		}
		
		$matches = (isset($_POST['secret']) && isset($_POST['code']) && is_string($_POST['secret']) && is_string($_POST['code']) && Controller_TOTP::shared()->check_code($_POST['secret'], $_POST['code']));
		if ($matches === false) {
			self::send_json(array('error' => esc_html__('The code provided does not match the expected value. Please verify that the time on your authenticator device is correct and that this server\'s time is correct.', 'wordfence-2fa')));
		}
		
		Controller_TOTP::shared()->activate_2fa($user, $_POST['secret'], $_POST['recovery'], $matches);
		Controller_Notices::shared()->remove_notice(false, 'wfls-will-be-required', $user);
		self::send_json(array('activated' => 1, 'text' => sprintf(count($_POST['recovery']) == 1 ? esc_html__('%d unused recovery code remains. You may generate a new set by clicking below.', 'wordfence-2fa') : esc_html__('%d unused recovery codes remain. You may generate a new set by clicking below.', 'wordfence-2fa'), count($_POST['recovery']))));
	}
	
	public function _ajax_deactivate_callback() {
		$userID = (int) @$_POST['user'];
		$user = wp_get_current_user();
		if ($user->ID != $userID) {
			if (!user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)) {
				self::send_json(array('error' => esc_html__('You do not have permission to deactivate the given user.', 'wordfence-2fa')));
			}
			else {
				$user = new \WP_User($userID);
				if (!$user->exists()) {
					self::send_json(array('error' => esc_html__('The user does not exist.', 'wordfence-2fa')));
				}
			}
		}
		else if (!user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_SELF)) {
			self::send_json(array('error' => esc_html__('You do not have permission to deactivate 2FA.', 'wordfence-2fa')));
		}
		
		if (!Controller_Users::shared()->has_2fa_active($user)) {
			self::send_json(array('error' => esc_html__('The user specified does not have two-factor authentication active.', 'wordfence-2fa')));
		}
		
		Controller_Users::shared()->deactivate_2fa($user);
		self::send_json(array('deactivated' => 1));
	}
	
	public function _ajax_regenerate_callback() {
		$userID = (int) @$_POST['user'];
		$user = wp_get_current_user();
		if ($user->ID != $userID) {
			if (!user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)) {
				self::send_json(array('error' => esc_html__('You do not have permission to generate new recovery codes for the given user.', 'wordfence-2fa')));
			}
			else {
				$user = new \WP_User($userID);
				if (!$user->exists()) {
					self::send_json(array('error' => esc_html__('The user does not exist.', 'wordfence-2fa')));
				}
			}
		}
		else if (!user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_SELF)) {
			self::send_json(array('error' => esc_html__('You do not have permission to generate new recovery codes.', 'wordfence-2fa')));
		}
		
		if (!Controller_Users::shared()->has_2fa_active($user)) {
			self::send_json(array('error' => esc_html__('The user specified does not have two-factor authentication active.', 'wordfence-2fa')));
		}
		
		$codes = Controller_Users::shared()->regenerate_recovery_codes($user);
		self::send_json(array('regenerated' => 1, 'recovery' => array_map(function($r) { return implode(' ', str_split(bin2hex($r), 4)); }, $codes), 'text' => sprintf(count($codes) == 1 ? esc_html__('%d unused recovery code remains. You may generate a new set by clicking below.', 'wordfence-2fa') : esc_html__('%d unused recovery codes remain. You may generate a new set by clicking below.', 'wordfence-2fa'), count($codes))));
	}
	
	public function _ajax_save_options_callback() {
		if (!empty($_POST['changes']) && is_string($_POST['changes']) && ($changes = json_decode(stripslashes($_POST['changes']), true)) !== false) {
			try {
				$errors = Controller_Settings::shared()->validate_multiple($changes);
				if ($errors !== true) {
					if (count($errors) == 1) {
						$e = array_shift($errors);
						self::send_json(array('error' => esc_html(sprintf(__('An error occurred while saving the configuration: %s', 'wordfence-2fa'), $e))));
					}
					else if (count($errors) > 1) {
						$compoundMessage = array();
						foreach ($errors as $e) {
							$compoundMessage[] = esc_html($e);
						}
						self::send_json(array(
							'error' => wp_kses(sprintf(__('Errors occurred while saving the configuration: %s', 'wordfence-2fa'), '<ul><li>' . implode('</li><li>', $compoundMessage) . '</li></ul>'), array('ul'=>array(), 'li'=>array())),
							'html' => true,
						));
					}
					
					self::send_json(array(
						'error' => esc_html__('Errors occurred while saving the configuration.', 'wordfence-2fa'),
					));
				}
				
				Controller_Settings::shared()->set_multiple($changes);
				
				$response = array('success' => true);
				return self::send_json($response);
			}
			catch (\Exception $e) {
				self::send_json(array(
					'error' => $e->getMessage(),
				));
			}
		}
		
		self::send_json(array(
			'error' => esc_html__('No configuration changes were provided to save.', 'wordfence'),
		));
	}
	
	public function _ajax_send_grace_period_notification_callback() {
		if (!Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REQUIRE_2FA_ADMIN)) {
			self::send_json(array('error' => esc_html__('Two-factor authentication is not currently required for administrators.', 'wordfence-2fa')));
		}
		
		if (!(Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD_ENABLED) && \WordfenceLS\Controller_Time::time() < Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD))) {
			self::send_json(array('error' => esc_html__('A valid grace period is not configured to allow administrators time to activate two-factor authentication.', 'wordfence-2fa')));
		}
		
		$subject = sprintf(__('2FA will soon be required on %s', 'wordfence-2fa'), home_url());
		$requiredDate = Controller_Time::format_local_time('F j, Y', Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD));
		
		$admins = Controller_Users::shared()->admin_users();
		$sent = 0;
		foreach ($admins as $a) {
			/** @var \WP_User $a */
			if (Controller_Users::shared()->has_2fa_active($a)) {
				continue;
			}

			$message = sprintf(
				__("You do not currently have two-factor authentication active on your account, which will be required beginning %s.\n\nConfigure 2FA: %s", 'wordfence-2fa'),
				$requiredDate,
				(is_multisite() && is_super_admin($a->ID)) ? network_admin_url('admin.php?page=WFLS') : admin_url('admin.php?page=WFLS')
			);
			
			wp_mail($a->user_email, $subject, $message);
			$sent++;
		}
		
		if ($sent == 0) {
			self::send_json(array('confirmation' => esc_html__('All administrators already have two-factor authenication activated.', 'wordfence-2fa')));
		}
		else if ($sent == 1) {
			self::send_json(array('confirmation' => esc_html(sprintf(__('A reminder to activate two-factor authentication was sent to %d administrator.', 'wordfence-2fa'), $sent))));
		}
		self::send_json(array('confirmation' => esc_html(sprintf(__('A reminder to activate two-factor authentication was sent to %d administrators.', 'wordfence-2fa'), $sent))));
	}
	
	public function _ajax_update_ip_preview_callback() {
		$source = $_POST['ip_source'];
		$raw_proxies = $_POST['ip_source_trusted_proxies'];
		if (!is_string($source) || !is_string($raw_proxies)) {
			die();
		}
		
		$valid = array();
		$invalid = array();
		$test = preg_split('/[\r\n,]+/', $raw_proxies);
		foreach ($test as $value) {
			if (strlen($value) > 0) {
				if (Model_IP::is_valid_ip($value) || Model_IP::is_valid_cidr_range($value)) {
					$valid[] = $value;
				}
				else {
					$invalid[] = $value;
				}
			}
		}
		$trusted_proxies = $valid;
		
		$preview = Model_Request::current()->detected_ip_preview($source, $trusted_proxies);
		$ip = Model_Request::current()->ip_for_field($source, $trusted_proxies);
		self::send_json(array('ip' => $ip[0], 'preview' => $preview));
	}
	
	public function _ajax_dismiss_notice_callback() {
		Controller_Notices::shared()->remove_notice($_POST['id'], false, wp_get_current_user());
	}
	
	public function _ajax_reset_recaptcha_stats_callback() {
		Controller_Settings::shared()->set_array(Controller_Settings::OPTION_CAPTCHA_STATS, array('counts' => array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), 'avg' => 0));
		$response = array('success' => true);
		self::send_json($response);
	}
}