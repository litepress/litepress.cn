<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* plugin controller class
*/
class GPNotifyWaiting {

	protected $users;
	protected $from;
	protected $subject;
	protected $body;

	/**
	* initialise notification
	* @param array $users
	* @param string $from
	*/
	public function __construct($users, $from) {
		$this->users = $users;
		$this->from = $from;
	}

	/**
	* compose notification email
	* @param string $subject
	* @param array $translations
	*/
	public function compose($subject, $translations) {
		$title   = apply_filters('gnotify_waiting_title', $subject, $translations, $this->users);
		$subject = apply_filters('gnotify_waiting_subject', $subject, $translations, $this->users);

		$this->subject = $subject;

		$templatePath = locate_template('plugins/glotpress-notify/email-waiting.php');
		if (!$templatePath) {
			$templatePath = GPNOTIFY_PLUGIN_ROOT . 'templates/email-waiting.php';
		}

		ob_start();
		require $templatePath;
		$this->body = ob_get_clean();
	}

	/**
	* send emails
	*/
	public function send() {
		if (empty($this->from)) {
			$this->from = sprintf('%s <%s>', get_bloginfo('name'), get_bloginfo('admin_email'));
		}

		$headers = array(
			'From: ' . $this->from,
		);
		$headers = apply_filters('gpnotify_waiting_headers', $headers, $this->users);

		add_filter('wp_mail_content_type', array(__CLASS__, 'wpmailContentType'));

		foreach ($this->users as $user) {
			wp_mail($user->user_email, $this->subject, $this->body, $headers);
		}

		remove_filter('wp_mail_content_type', array(__CLASS__, 'wpmailContentType'));
	}

	/**
	* set content type to HTML
	* @param string $content_type
	* @return string
	*/
	public static function wpmailContentType($content_type) {
		return 'text/html';
	}

}
