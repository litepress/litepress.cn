<?php
require_once(dirname(__FILE__) . '/wordfenceConstants.php');
require_once(dirname(__FILE__) . '/wordfenceClass.php');

class wfAPI {
	const KEY_TYPE_FREE = 'free';
	const KEY_TYPE_PAID_CURRENT = 'paid-current';
	const KEY_TYPE_PAID_EXPIRED = 'paid-expired';
	const KEY_TYPE_PAID_DELETED = 'paid-deleted';
	
	public $lastHTTPStatus = '';
	public $lastCurlErrorNo = '';
	private $curlContent = 0;
	private $APIKey = '';
	private $wordpressVersion = '';

	public function __construct($apiKey, $wordpressVersion) {
		$this->APIKey = $apiKey;
		$this->wordpressVersion = $wordpressVersion;
	}

	public function getStaticURL($url) { // In the form '/something.bin' without quotes
		return $this->getURL(rtrim($this->getAPIURL(), '/') . '/' . ltrim($url, '/'));
	}

	public function call($action, $getParams = array(), $postParams = array(), $forceSSL = false, $timeout = 900) {
		$apiURL = $this->getAPIURL();
		//Sanity check. Developer should call wfAPI::SSLEnabled() to check if SSL is enabled before forcing SSL and return a user friendly msg if it's not.
		if ($forceSSL && (!preg_match('/^https:/i', $apiURL))) {
			//User's should never see this message unless we aren't calling SSLEnabled() to check if SSL is enabled before using call() with forceSSL
			throw new wfAPICallSSLUnavailableException(__("SSL is not supported by your web server and is required to use this function. Please ask your hosting provider or site admin to install cURL with openSSL to use this feature.", 'wordfence'));
		}
		$json = $this->getURL(rtrim($apiURL, '/') . '/v' . WORDFENCE_API_VERSION . '/?' . $this->makeAPIQueryString() . '&' . self::buildQuery(
				array_merge(
					array('action' => $action),
					$getParams
				)), $postParams, $timeout);
		if (!$json) {
			throw new wfAPICallInvalidResponseException(sprintf(/* translators: API call/action/endpoint. */__("We received an empty data response from the Wordfence scanning servers when calling the '%s' function.", 'wordfence'), $action));
		}

		$dat = json_decode($json, true);
		if (isset($dat['_isPaidKey'])) {
			wfConfig::set('keyExpDays', $dat['_keyExpDays']);
			if ($dat['_keyExpDays'] > -1) {
				wfConfig::set('isPaid', 1);
			}
			else if ($dat['_keyExpDays'] < 0) {
				wfConfig::set('isPaid', '');
			}
			
			if (!isset($dat['errorMsg'])) {
				if ($dat['_keyExpDays'] > -1) {
					wfConfig::set('keyType', self::KEY_TYPE_PAID_CURRENT);
				}
				else if ($dat['_keyExpDays'] < 0) {
					wfConfig::set('keyType', self::KEY_TYPE_PAID_EXPIRED);
				}
				
				if (isset($dat['_autoRenew'])) { wfConfig::set('premiumAutoRenew', wfUtils::truthyToInt($dat['_autoRenew'])); } else { wfConfig::remove('premiumAutoRenew'); }
				if (isset($dat['_nextRenewAttempt'])) { wfConfig::set('premiumNextRenew', time() + $dat['_nextRenewAttempt'] * 86400); } else { wfConfig::remove('premiumNextRenew'); } 
				if (isset($dat['_paymentExpiring'])) { wfConfig::set('premiumPaymentExpiring', wfUtils::truthyToInt($dat['_paymentExpiring'])); } else { wfConfig::remove('premiumPaymentExpiring'); }
				if (isset($dat['_paymentExpired'])) { wfConfig::set('premiumPaymentExpired', wfUtils::truthyToInt($dat['_paymentExpired'])); } else { wfConfig::remove('premiumPaymentExpired'); }
				if (isset($dat['_paymentMissing'])) { wfConfig::set('premiumPaymentMissing', wfUtils::truthyToInt($dat['_paymentMissing'])); } else { wfConfig::remove('premiumPaymentMissing'); }
				if (isset($dat['_paymentHold'])) { wfConfig::set('premiumPaymentHold', wfUtils::truthyToInt($dat['_paymentHold'])); } else { wfConfig::remove('premiumPaymentHold'); }
			}
		}
		
		$hasKeyConflict = false;
		if (isset($dat['_hasKeyConflict'])) {
			$hasKeyConflict = ($dat['_hasKeyConflict'] == 1);
			if ($hasKeyConflict) {
				new wfNotification(null, wfNotification::PRIORITY_HIGH_CRITICAL, '<a href="' . wfUtils::wpAdminURL('admin.php?page=Wordfence&subpage=global_options') . '">' . esc_html__('The Wordfence license you\'re using does not match this site\'s address. Premium features are disabled.', 'wordfence') . '</a>', 'wfplugin_keyconflict', null, array(array('link' => 'https://www.wordfence.com/manage-wordfence-api-keys/', 'label' => 'Manage Keys')));
				wfConfig::set('hasKeyConflict', 1);
			}
		}
		
		$keyNoLongerValid = false;
		if (isset($dat['_keyNoLongerValid'])) {
			$keyNoLongerValid = ($dat['_keyNoLongerValid'] == 1);
			if ($keyNoLongerValid) {
				wfConfig::set('keyType', self::KEY_TYPE_PAID_DELETED);
				wfConfig::set('isPaid', '');
			}
		}
		
		if (!$hasKeyConflict) {
			wfConfig::remove('hasKeyConflict');
			$n = wfNotification::getNotificationForCategory('wfplugin_keyconflict');
			if ($n !== null) {
				wordfence::status(1, 'info', 'Idle');
				$n->markAsRead();
			}
		}
		
		if (isset($dat['_touppChanged'])) {
			wfConfig::set('touppPromptNeeded', wfUtils::truthyToBoolean($dat['_touppChanged']));
		}

		if (!is_array($dat)) {
			throw new wfAPICallInvalidResponseException(sprintf(/* translators: API call/action/endpoint. */ __("We received a data structure that is not the expected array when contacting the Wordfence scanning servers and calling the '%s' function.", 'wordfence'), $action));
		}
		if (is_array($dat) && isset($dat['errorMsg'])) {
			throw new wfAPICallErrorResponseException($dat['errorMsg']);
		}
		return $dat;
	}

	protected function getURL($url, $postParams = array(), $timeout = 900) {
		wordfence::status(4, 'info', sprintf(/* translators: API version. */ __("Calling Wordfence API v%s:", 'wordfence'), WORDFENCE_API_VERSION) . $url);

		if (!function_exists('wp_remote_post')) {
			require_once(ABSPATH . WPINC . 'http.php');
		}

		$ssl_verify = (bool) wfConfig::get('ssl_verify');
		$args = array(
			'timeout'    => $timeout,
			'user-agent' => "Wordfence.com UA " . (defined('WORDFENCE_VERSION') ? WORDFENCE_VERSION : '[Unknown version]'),
			'body'       => $postParams,
			'sslverify'  => $ssl_verify,
			'headers'	 => array('Referer' => false),
		);
		if (!$ssl_verify) {
			// Some versions of cURL will complain that SSL verification is disabled but the CA bundle was supplied.
			$args['sslcertificates'] = false;
		}

		$response = wp_remote_post($url, $args);

		$this->lastHTTPStatus = (int) wp_remote_retrieve_response_code($response);

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			if ($error_message) {
				$apiExceptionMessage = sprintf(/* translators: Error message. */ __('There was an error connecting to the Wordfence scanning servers: %s', 'wordfence'), $error_message);
			} else {
				$apiExceptionMessage = __('There was an unknown error connecting to the Wordfence scanning servers.', 'wordfence');
			}

			throw new wfAPICallFailedException($apiExceptionMessage);
		}
		
		$dateHeader = @$response['headers']['date'];
		if (!empty($dateHeader) && (time() - wfConfig::get('timeoffset_wf_updated', 0) > 3600)) {
			if (function_exists('date_create_from_format')) {
				$dt = DateTime::createFromFormat('D, j M Y G:i:s O', $dateHeader);
				$timestamp = $dt->getTimestamp();
			}
			else {
				$timestamp = strtotime($dateHeader);
			}
			$offset = $timestamp - time();
			wfConfig::set('timeoffset_wf', $offset);
			wfConfig::set('timeoffset_wf_updated', time());
		}

		if (!empty($response['response']['code'])) {
			$this->lastHTTPStatus = (int) $response['response']['code'];
		}

		if (200 != $this->lastHTTPStatus) {
			throw new wfAPICallFailedException(sprintf(/* translators: HTTP status code. */__("The Wordfence scanning servers are currently unavailable. This may be for maintenance or a temporary outage. If this still occurs in an hour, please contact support. [%s]", 'wordfence'), $this->lastHTTPStatus));
		}

		$content = wp_remote_retrieve_body($response);
		return $content;
	}

	public function binCall($func, $postData) {
		$url = rtrim($this->getAPIURL(), '/') . '/v' . WORDFENCE_API_VERSION . '/?' . $this->makeAPIQueryString() . '&action=' . $func;

		$data = $this->getURL($url, $postData);

		if (preg_match('/\{.*errorMsg/', $data)) {
			$jdat = @json_decode($data, true);
			if (is_array($jdat) && $jdat['errorMsg']) {
				throw new Exception($jdat['errorMsg']);
			}
		}
		return array('code' => $this->lastHTTPStatus, 'data' => $data);
	}

	public function makeAPIQueryString() {
		$cv = null;
		$cs = null;
		if (function_exists('curl_version')) {
			$curl = curl_version();
			$cv = $curl['version'];
			$cs = $curl['ssl_version'];
		}
		
		$values = array(
			'wp' => $this->wordpressVersion,
			'wf' => WORDFENCE_VERSION,
			'ms' => (is_multisite() ? get_blog_count() : false),
			'h' => wfUtils::wpHomeURL(),
			'sslv' => function_exists('openssl_verify') && defined('OPENSSL_VERSION_NUMBER') ? OPENSSL_VERSION_NUMBER : null,
			'pv' => phpversion(),
			'pt' => php_sapi_name(),
			'cv' => $cv,
			'cs' => $cs,
			'sv' => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null),
			'dv' => wfConfig::get('dbVersion', null),
			'lang' => get_site_option('WPLANG'),
		);
		
		return self::buildQuery(array(
			'k' => $this->APIKey,
			's' => wfUtils::base64url_encode(json_encode($values)),
			'betaFeed'  => (int) wfConfig::get('betaThreatDefenseFeed'),
		));
	}

	private function buildQuery($data) {
		if (version_compare(phpversion(), '5.1.2', '>=')) {
			return http_build_query($data, '', '&'); //arg_separator parameter was only added in PHP 5.1.2. We do this because some PHP.ini's have arg_separator.output set to '&amp;'
		} else {
			return http_build_query($data);
		}
	}

	private function getAPIURL() {
		return self::SSLEnabled() ? WORDFENCE_API_URL_SEC : WORDFENCE_API_URL_NONSEC;
	}

	public static function SSLEnabled() {
		if (!function_exists('wp_http_supports')) {
			require_once(ABSPATH . WPINC . 'http.php');
		}
		return wp_http_supports(array('ssl'));
	}
	
	public function getTextImageURL($text) {
		$apiURL = $this->getAPIURL();
		return rtrim($apiURL, '/') . '/v' . WORDFENCE_API_VERSION . '/?' . $this->makeAPIQueryString() . '&' . self::buildQuery(array('action' => 'image', 'txt' => base64_encode($text)));
	}
}

class wfAPICallSSLUnavailableException extends Exception {
}

class wfAPICallFailedException extends Exception {
}

class wfAPICallInvalidResponseException extends Exception {
}

class wfAPICallErrorResponseException extends Exception {
}