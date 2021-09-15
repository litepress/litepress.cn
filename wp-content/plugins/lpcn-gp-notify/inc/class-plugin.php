<?php

namespace LitePress\GlotPress\Notify;

use GP_Translation;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		//add_action( 'gp_translation_saved', array( $this, 'gp_translation_saved' ), 10, 2 );
		//add_action('gp_original_saved');

		add_action( 'lpcn_gp_notify', array( $this, 'send' ) );
	}

	/**
	 * 用户保存单条翻译时触发
	 *
	 * @param \GP_Translation $translation
	 * @param \GP_Translation $translation_before
	 *
	 * @return bool
	 */
	public function gp_translation_saved( GP_Translation $translation, GP_Translation $translation_before ): bool {
		if ( 'current' === $translation->status || 517 === $translation->user_id ) {
			return false;
		}


		var_dump( $translation );
		exit;
	}

	/**
	 * 发送邮件
	 *
	 * @param string $title
	 * @param string $content
	 * @param array $to_emails
	 *
	 * @return bool
	 */
	public function send( string $title, string $content, array $to_emails ): bool {
		foreach ( $to_emails as $email ) {
			$headers[] = "From: LitePress 翻译平台 <$email>";

			return (bool) wp_mail( $email, $title, $content, $headers );
		}

		return true;
	}

	private function add_cron( string $title, string $content, array $to_emails ) {
		$timestamp = time();

		//do_action( 'lpcn_gp_notify', $avatar_url, $avatar_hash, $email_hash );
		$args = array(
			'title'     => $title,
			'content'   => $content,
			'to_emails' => $to_emails,
		);
		$next = wp_next_scheduled( 'lpcn_gp_notify', $args );
		if ( empty( $next ) ) { // 如果未安排任务或者任务过期就安排之
			wp_schedule_single_event( time() + 3600, 'lpcn_gp_notify', $args );
		} elseif ( ( $next - $timestamp ) < 600 ) { // 如果距离过期不足十分钟，则删除并重新安排
			wp_clear_scheduled_hook( 'lpcn_gp_notify', $args );
			wp_schedule_single_event( time() + 3600, 'lpcn_gp_notify', $args );
		}
	}

}
