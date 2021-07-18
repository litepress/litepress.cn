<?php

namespace WordfenceLS;

class Controller_CAPTCHA {
	const RESPONSE_MODE_ALLOW = 'allow';
	const RESPONSE_MODE_REQUIRE_VERIFICATION = 'verify';
	
	const RECAPTCHA_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';
	
	/**
	 * Returns the singleton Controller_CAPTCHA.
	 *
	 * @return Controller_CAPTCHA
	 */
	public static function shared() {
		static $_shared = null;
		if ($_shared === null) {
			$_shared = new Controller_CAPTCHA();
		}
		return $_shared;
	}
	
	/**
	 * Returns whether or not the authentication CAPTCHA is enabled.
	 * 
	 * @return bool
	 */
	public function enabled() {
		$key = $this->site_key();
		$secret = $this->_secret();
		return Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_ENABLE_AUTH_CAPTCHA) && !empty($key) && !empty($secret);
	}
	
	/**
	 * Returns the public reCAPTCHA key if set.
	 * 
	 * @return string|bool
	 */
	public function site_key() {
		return Controller_Settings::shared()->get(Controller_Settings::OPTION_RECAPTCHA_SITE_KEY);
	}
	
	/**
	 * Returns the private reCAPTCHA secret if set.
	 * 
	 * @return string|bool
	 */
	protected function _secret() {
		return Controller_Settings::shared()->get(Controller_Settings::OPTION_RECAPTCHA_SECRET);
	}
	
	/**
	 * Returns the bot/human threshold for comparing the score against, defaulting to 0.5.
	 * 
	 * @return float
	 */
	public function threshold() {
		return Controller_Settings::shared()->get_float(Controller_Settings::OPTION_RECAPTCHA_THRESHOLD, 0.5);
	}

	/**
	 * Determine whether or not test mode for reCAPTCHA is enabled
	 *
	 * @return bool
	 */
	public function test_mode() {
		return Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_CAPTCHA_TEST_MODE);
	}
	
	/**
	 * Queries the reCAPTCHA endpoint with the given token, verifies the action matches, and returns the corresponding 
	 * score. If validation fails, false is returned. Any other failure (e.g., mangled response or connection dropped) returns 0.0.
	 * 
	 * @param string $token
	 * @param string $action
	 * @param int $timeout
	 * @return float|false
	 */
	public function score($token, $action = 'login', $timeout = 10) {
		try {
			$payload = array(
				'secret' => $this->_secret(),
				'response' => $token,
				'remoteip' => Model_Request::current()->ip(),
			);
			
			$response = wp_remote_post(self::RECAPTCHA_ENDPOINT,
				array(
					'body'    => $payload,
					'headers' => array(
						'Referer' => false,
					),
					'timeout' => $timeout,
					'blocking' => true,
			));
			
			if (!is_wp_error($response)) {
				$jsonResponse = wp_remote_retrieve_body($response);
				$decoded = @json_decode($jsonResponse, true);
				if (is_array($decoded) && isset($decoded['success']) && isset($decoded['score']) && isset($decoded['action'])) {
					if ($decoded['success'] && $decoded['action'] == $action) {
						return (float) $decoded['score'];
					}
					return false;
				}
			}
		}
		catch (\Exception $e) {
			//Fall through
		}
		
		return 0.0;
	}
	
	/**
	 * Returns true if the score is >= the threshold to be considered a human request.
	 * 
	 * @param float $score
	 * @return bool
	 */
	public function is_human($score) {
		if ($this->test_mode()) {
			return true;
		}
		
		$threshold = $this->threshold();
		return ($score >= $threshold || abs($score - $threshold) < 0.0001);
	}
}