<?php

class wfCentralAPIRequest {
	/**
	 * @var string
	 */
	private $endpoint;
	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var null
	 */
	private $token;
	/**
	 * @var array
	 */
	private $body;
	/**
	 * @var array
	 */
	private $args;


	/**
	 * @param string $endpoint
	 * @param string $method
	 * @param string|null $token
	 * @param array $body
	 * @param array $args
	 */
	public function __construct($endpoint, $method = 'GET', $token = null, $body = array(), $args = array()) {
		$this->endpoint = $endpoint;
		$this->method = $method;
		$this->token = $token;
		$this->body = $body;
		$this->args = $args;
	}

	public function execute() {
		$args = array(
			'timeout' => 10,
		);
		$args = wp_parse_args($this->getArgs(), $args);
		$args['method'] = $this->getMethod();
		if (empty($args['headers'])) {
			$args['headers'] = array();
		}

		$token = $this->getToken();
		if ($token) {
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}
		if ($this->getBody()) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body'] = json_encode($this->getBody());
		}

		$http = _wp_http_get_object();
		$response = $http->request(WORDFENCE_CENTRAL_API_URL_SEC . $this->getEndpoint(), $args);

		if (!is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);
			$statusCode = wp_remote_retrieve_response_code($response);

			// Check if site has been disconnected on Central's end, but the plugin is still trying to connect.
			if ($statusCode === 404 && strpos($body, 'Site has been disconnected') !== false) {
				// Increment attempt count.
				$centralDisconnectCount = get_site_transient('wordfenceCentralDisconnectCount');
				set_site_transient('wordfenceCentralDisconnectCount', ++$centralDisconnectCount, 86400);

				// Once threshold is hit, disconnect Central.
				if ($centralDisconnectCount > 3) {
					wfRESTConfigController::disconnectConfig();
				}
			}
		}

		return new wfCentralAPIResponse($response);
	}

	/**
	 * @return string
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * @param string $endpoint
	 */
	public function setEndpoint($endpoint) {
		$this->endpoint = $endpoint;
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @param string $method
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * @return null
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @param null $token
	 */
	public function setToken($token) {
		$this->token = $token;
	}

	/**
	 * @return array
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param array $body
	 */
	public function setBody($body) {
		$this->body = $body;
	}

	/**
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * @param array $args
	 */
	public function setArgs($args) {
		$this->args = $args;
	}
}

class wfCentralAPIResponse {

	public static function parseErrorJSON($json) {
		$data = json_decode($json, true);
		if (is_array($data) && array_key_exists('message', $data)) {
			return $data['message'];
		}
		return $json;
	}

	/**
	 * @var array|null
	 */
	private $response;

	/**
	 * @param array $response
	 */
	public function __construct($response = null) {
		$this->response = $response;
	}

	public function getStatusCode() {
		return wp_remote_retrieve_response_code($this->getResponse());
	}

	public function getBody() {
		return wp_remote_retrieve_body($this->getResponse());
	}

	public function getJSONBody() {
		return json_decode($this->getBody(), true);
	}

	public function isError() {
		if (is_wp_error($this->getResponse())) {
			return true;
		}
		$statusCode = $this->getStatusCode();
		return !($statusCode >= 200 && $statusCode < 300);
	}

	public function returnErrorArray() {
		return array(
			'err'      => 1,
			'errorMsg' => sprintf(
				/* translators: 1. HTTP status code. 2. Error message. */
				__('HTTP %1$d received from Wordfence Central: %2$s', 'wordfence'),
				$this->getStatusCode(), $this->parseErrorJSON($this->getBody())),
		);
	}

	/**
	 * @return array|null
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param array|null $response
	 */
	public function setResponse($response) {
		$this->response = $response;
	}
}


class wfCentralAuthenticatedAPIRequest extends wfCentralAPIRequest {

	private $retries = 3;

	/**
	 * @param string $endpoint
	 * @param string $method
	 * @param array $body
	 * @param array $args
	 */
	public function __construct($endpoint, $method = 'GET', $body = array(), $args = array()) {
		parent::__construct($endpoint, $method, null, $body, $args);
	}

	/**
	 * @return mixed|null
	 * @throws wfCentralAPIException
	 */
	public function getToken() {
		$token = parent::getToken();
		if ($token) {
			return $token;
		}

		$token = get_transient('wordfenceCentralJWT' . wfConfig::get('wordfenceCentralSiteID'));
		if ($token) {
			return $token;
		}

		for ($i = 0; $i < $this->retries; $i++) {
			try {
				$token = $this->fetchToken();
				break;
			} catch (wfCentralAPIException $e) {
				continue;
			}
		}
		if (empty($token)) {
			if (isset($e)) {
				throw $e;
			} else {
				throw new wfCentralAPIException(__('Unable to authenticate with Wordfence Central.', 'wordfence'));
			}
		}
		$tokenContents = wfJWT::extractTokenContents($token);

		if (!empty($tokenContents['body']['exp'])) {
			set_transient('wordfenceCentralJWT' . wfConfig::get('wordfenceCentralSiteID'), $token, $tokenContents['body']['exp'] - time());
		}
		return $token;
	}

	public function fetchToken() {
		require_once(WORDFENCE_PATH . '/crypto/vendor/paragonie/sodium_compat/autoload-fast.php');

		$defaultArgs = array(
			'timeout' => 6,
		);
		$siteID = wfConfig::get('wordfenceCentralSiteID');
		if (!$siteID) {
			throw new wfCentralAPIException(__('Wordfence Central site ID has not been created yet.', 'wordfence'));
		}
		$secretKey = wfConfig::get('wordfenceCentralSecretKey');
		if (!$secretKey) {
			throw new wfCentralAPIException(__('Wordfence Central secret key has not been created yet.', 'wordfence'));
		}

		// Pull down nonce.
		$request = new wfCentralAPIRequest(sprintf('/site/%s/login', $siteID), 'GET', null, array(), $defaultArgs);
		$nonceResponse = $request->execute();
		if ($nonceResponse->isError()) {
			$errorArray = $nonceResponse->returnErrorArray();
			throw new wfCentralAPIException($errorArray['errorMsg']);
		}
		$body = $nonceResponse->getJSONBody();
		if (!is_array($body) || !isset($body['nonce'])) {
			throw new wfCentralAPIException(__('Invalid response received from Wordfence Central when fetching nonce.', 'wordfence'));
		}
		$nonce = $body['nonce'];

		// Sign nonce to pull down JWT.
		$data = $nonce . '|' . $siteID;
		$signature = ParagonIE_Sodium_Compat::crypto_sign_detached($data, $secretKey);
		$request = new wfCentralAPIRequest(sprintf('/site/%s/login', $siteID), 'POST', null, array(
			'data'      => $data,
			'signature' => ParagonIE_Sodium_Compat::bin2hex($signature),
		), $defaultArgs);
		$authResponse = $request->execute();
		if ($authResponse->isError()) {
			$errorArray = $authResponse->returnErrorArray();
			throw new wfCentralAPIException($errorArray['errorMsg']);
		}
		$body = $authResponse->getJSONBody();
		if (!is_array($body)) {
			throw new wfCentralAPIException(__('Invalid response received from Wordfence Central when fetching token.', 'wordfence'));
		}
		if (!isset($body['jwt'])) { // Possible authentication error.
			throw new wfCentralAPIException(__('Unable to authenticate with Wordfence Central.', 'wordfence'));
		}
		return $body['jwt'];
	}
}

class wfCentralAPIException extends Exception {

}

class wfCentral {

	/**
	 * @return bool
	 */
	public static function isSupported() {
		return function_exists('register_rest_route') && version_compare(phpversion(), '5.3', '>=');
	}

	/**
	 * @return bool
	 */
	public static function isConnected() {
		return self::isSupported() && ((bool) self::_isConnected());
	}

	/**
	 * @return bool
	 */
	public static function isPartialConnection() {
		return !self::_isConnected() && wfConfig::get('wordfenceCentralSiteID');
	}

	public static function _isConnected($forceUpdate = false) {
		static $isConnected;
		if (!isset($isConnected) || $forceUpdate) {
			$isConnected = wfConfig::get('wordfenceCentralConnected', false);
		}
		return $isConnected;
	}

	/**
	 * @param array $issue
	 * @return bool|wfCentralAPIResponse
	 */
	public static function sendIssue($issue) {
		return self::sendIssues(array($issue));
	}

	/**
	 * @param $issues
	 * @return bool|wfCentralAPIResponse
	 */
	public static function sendIssues($issues) {
		$data = array();
		foreach ($issues as $issue) {
			$issueData = array(
				'type'       => 'issue',
				'attributes' => $issue,
			);
			if (array_key_exists('id', $issueData)) {
				$issueData['id'] = $issue['id'];
			}
			$data[] = $issueData;
		}

		$siteID = wfConfig::get('wordfenceCentralSiteID');
		$request = new wfCentralAuthenticatedAPIRequest('/site/' . $siteID . '/issues', 'POST', array(
			'data' => $data,
		));
		try {
			$response = $request->execute();
			return $response;
		} catch (wfCentralAPIException $e) {
			error_log($e);
		}
		return false;
	}

	/**
	 * @param int $issueID
	 * @return bool|wfCentralAPIResponse
	 */
	public static function deleteIssue($issueID) {
		return self::deleteIssues(array($issueID));
	}

	/**
	 * @param $issues
	 * @return bool|wfCentralAPIResponse
	 */
	public static function deleteIssues($issues) {
		$siteID = wfConfig::get('wordfenceCentralSiteID');
		$request = new wfCentralAuthenticatedAPIRequest('/site/' . $siteID . '/issues', 'DELETE', array(
			'data' => array(
				'type'       => 'issue-list',
				'attributes' => array(
					'ids' => $issues,
				)
			),
		));
		try {
			$response = $request->execute();
			return $response;
		} catch (wfCentralAPIException $e) {
			error_log($e);
		}
		return false;
	}

	/**
	 * @return bool|wfCentralAPIResponse
	 */
	public static function deleteNewIssues() {
		$siteID = wfConfig::get('wordfenceCentralSiteID');
		$request = new wfCentralAuthenticatedAPIRequest('/site/' . $siteID . '/issues', 'DELETE', array(
			'data' => array(
				'type'       => 'issue-list',
				'attributes' => array(
					'status' => 'new',
				)
			),
		));
		try {
			$response = $request->execute();
			return $response;
		} catch (wfCentralAPIException $e) {
			error_log($e);
		}
		return false;
	}

	/**
	 * @param array $types Array of issue types to delete
	 * @param string $status Issue status to delete
	 * @return bool|wfCentralAPIResponse
	 */
	public static function deleteIssueTypes($types, $status = 'new') {
		$siteID = wfConfig::get('wordfenceCentralSiteID');
		$request = new wfCentralAuthenticatedAPIRequest('/site/' . $siteID . '/issues', 'DELETE', array(
			'data' => array(
				'type'       => 'issue-list',
				'attributes' => array(
					'types' => $types,
					'status' => $status,
				)
			),
		));
		try {
			$response = $request->execute();
			return $response;
		} catch (wfCentralAPIException $e) {
			error_log($e);
		}
		return false;
	}

	public static function requestConfigurationSync() {
		if (! wfCentral::isConnected() || !self::$syncConfig) {
			return;
		}

		$endpoint = '/site/'.wfConfig::get('wordfenceCentralSiteID').'/config';
		$args = array('timeout' => 0.01, 'blocking'  => false);
		$request = new wfCentralAuthenticatedAPIRequest($endpoint, 'POST', array(), $args);

		try {
			$request->execute();
		} catch (Exception $e) {
			// We can safely ignore an error here for now.
		}
	}

	protected static $syncConfig = true;

	public static function preventConfigurationSync() {
		self::$syncConfig = false;
	}

	/**
	 * @param $scan
	 * @param $running
	 * @return bool|wfCentralAPIResponse
	 */
	public static function updateScanStatus($scan = null) {
		if ($scan === null) {
			$scan = wfConfig::get_ser('scanStageStatuses');
			if (!is_array($scan)) {
				$scan = array();
			}
		}

		$siteID = wfConfig::get('wordfenceCentralSiteID');
		$running = wfScanner::shared()->isRunning();
		$request = new wfCentralAuthenticatedAPIRequest('/site/' . $siteID . '/scan', 'PATCH', array(
			'data' => array(
				'type'       => 'scan',
				'attributes' => array(
					'running'      => $running,
					'scan'         => $scan,
					'scan-summary' => wfConfig::get('wf_summaryItems'),
				),
			),
		));
		try {
			$response = $request->execute();
			return $response;
		} catch (wfCentralAPIException $e) {
			error_log($e);
		}
		return false;
	}

	/**
	 * @param string $event
	 * @param array $data
	 * @param callable|null $alertCallback
	 */
	public static function sendSecurityEvent($event, $data = array(), $alertCallback = null) {
		$alerted = false;
		if (!self::pluginAlertingDisabled() && is_callable($alertCallback)) {
			call_user_func($alertCallback);
			$alerted = true;
		}

		$siteID = wfConfig::get('wordfenceCentralSiteID');
		$request = new wfCentralAuthenticatedAPIRequest('/site/' . $siteID . '/security-events', 'POST', array(
			'data' => array(
				array(
					'type'       => 'security-event',
					'attributes' => array(
						'type'       => $event,
						'data'       => $data,
						'event_time' => microtime(true),
					),
				),
			),
		));
		try {
			// Attempt to send the security event to Central.
			$response = $request->execute();
		} catch (wfCentralAPIException $e) {
			// If we didn't alert previously, notify the user now in the event Central is down.
			if (!$alerted && is_callable($alertCallback)) {
				call_user_func($alertCallback);
			}
		}
	}

	/**
	 * @param $event
	 * @param array $data
	 * @param callable|null $alertCallback
	 */
	public static function sendAlertCallback($event, $data = array(), $alertCallback = null) {
		if (is_callable($alertCallback)) {
			call_user_func($alertCallback);
		}
	}

	public static function pluginAlertingDisabled() {
		if (!self::isConnected()) {
			return false;
		}

		return wfConfig::get('wordfenceCentralPluginAlertingDisabled', false);
	}
}
