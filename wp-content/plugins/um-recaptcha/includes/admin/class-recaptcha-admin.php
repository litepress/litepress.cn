<?php
namespace um_ext\um_recaptcha\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Recaptcha_Admin
 *
 * @package um_ext\um_recaptcha\admin
 */
class Recaptcha_Admin {


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {
		add_action( 'um_admin_create_notices', array( &$this, 'add_admin_notice' ) );
		add_action( 'um_admin_custom_register_metaboxes', array( &$this, 'add_metabox_register' ), 10, 1 );
		add_action( 'um_admin_custom_login_metaboxes', array( &$this, 'add_metabox_login' ), 10, 1 );
		add_filter( 'um_settings_structure', array( &$this, 'add_settings' ), 10, 1 );
	}


	public function add_admin_notice() {
		$status    = UM()->options()->get( 'g_recaptcha_status' );
		$sitekey   = UM()->options()->get( 'g_recaptcha_sitekey' ) || UM()->options()->get( 'g_reCAPTCHA_site_key' );
		$secretkey = UM()->options()->get( 'g_recaptcha_secretkey' ) || UM()->options()->get( 'g_reCAPTCHA_secret_key' );

		if ( ! $status || ( $sitekey && $secretkey ) ) {
			return;
		}

		ob_start(); ?>

		<p><?php _e( 'Google reCAPTCHA is active on your site. However you need to fill in both your <strong>site key and secret key</strong> to start protecting your site against spam.', 'um-recaptcha' ); ?></p>

		<p>
			<a href="<?php echo admin_url( 'admin.php?page=um_options&tab=extensions&section=recaptcha' ) ?>" class="button button-primary"><?php _e( 'I already have the keys', 'um-recaptcha' ) ?></a>&nbsp;
			<a href="http://google.com/recaptcha" class="button-secondary" target="_blank"><?php _e( 'Generate your site and secret key', 'um-recaptcha' ) ?></a>
		</p>

		<?php $message = ob_get_clean();

		UM()->admin()->notices()->add_notice(
			'um_recaptcha_notice',
			array(
				'class'       => 'updated',
				'message'     => $message,
				'dismissible' => true,
			),
		10
		);
	}


	/**
	 * @param $action
	 */
	public function add_metabox_register( $action ) {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_meta_box(
			"um-admin-form-register_recaptcha{" . um_recaptcha_path . "}",
			__('Google reCAPTCHA'),
			array( UM()->metabox(), 'load_metabox_form'),
			'um_form',
			'side',
			'default'
		);
	}


	/**
	 * @param $action
	 */
	public function add_metabox_login( $action ) {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_meta_box(
			"um-admin-form-login_recaptcha{" . um_recaptcha_path . "}",
			__( 'Google reCAPTCHA', 'um-recaptcha' ),
			array( UM()->metabox(), 'load_metabox_form' ),
			'um_form',
			'side',
			'default'
		);
	}


	/**
	 * Extend settings
	 *
	 * @param $settings
	 * @return mixed
	 */
	public function add_settings( $settings ) {
		$key = ! empty( $settings['extensions']['sections'] ) ? 'recaptcha' : '';

		$settings['extensions']['sections'][ $key ] = array(
			'title'  => __( 'Google reCAPTCHA', 'um-recaptcha' ),
			'fields' => array(
				array(
					'id'      => 'g_recaptcha_status',
					'type'    => 'checkbox',
					'label'   => __( 'Enable Google reCAPTCHA', 'um-recaptcha' ),
					'tooltip' => __( 'Turn on or off your Google reCAPTCHA on your site registration and login forms by default.', 'um-recaptcha' ),
				),
				array(
					'id'          => 'g_recaptcha_version',
					'type'        => 'select',
					'label'       => __( 'reCAPTCHA type', 'um-recaptcha' ),
					'tooltip'     => __( 'Choose the type of reCAPTCHA for this site key. A site key only works with a single reCAPTCHA site type.', 'um-recaptcha' ),
					'options'     => array(
						'v2' => __( 'reCAPTCHA v2', 'um-recaptcha' ),
						'v3' => __( 'reCAPTCHA v3', 'um-recaptcha' ),
					),
					'size'        => 'medium',
					'description' => __( 'See <a href="https://g.co/recaptcha/sitetypes" target="_blank">Site Types</a> for more details.', 'um-recaptcha' ),
					'conditional' => array( 'g_recaptcha_status', '=', 1 ),
				),
				/* reCAPTCHA v3 */
				array(
					'id'          => 'g_reCAPTCHA_site_key',
					'type'        => 'text',
					'label'       => __( 'Site Key', 'um-recaptcha' ),
					'tooltip'     => __( 'You can register your site and generate a site key via <a href="https://www.google.com/recaptcha/">Google reCAPTCHA</a>', 'um-recaptcha' ),
					'size'        => 'medium',
					'conditional' => array( 'g_recaptcha_version', '=', 'v3' ),
				),
				array(
					'id'          => 'g_reCAPTCHA_secret_key',
					'type'        => 'text',
					'label'       => __( 'Secret Key', 'um-recaptcha' ),
					'tooltip'     => __( 'Keep this a secret. You can get your secret key via <a href="https://www.google.com/recaptcha/">Google reCAPTCHA</a>', 'um-recaptcha' ),
					'size'        => 'medium',
					'conditional' => array( 'g_recaptcha_version', '=', 'v3' ),
				),
				array(
					'id'          => 'g_reCAPTCHA_score',
					'type'        => 'text',
					'label'       => __( 'reCAPTCHA Score', 'um-recaptcha' ),
					'tooltip'     => __( 'Consider answers with a score >= to the specified as safe. Set the score in the 0 to 1 range. E.g. 0.5', 'um-recaptcha' ),
					'size'        => 'small',
					'conditional' => array( 'g_recaptcha_version', '=', 'v3' ),
				),
				/* reCAPTCHA v2 */
				array(
					'id'          => 'g_recaptcha_sitekey',
					'type'        => 'text',
					'label'       => __( 'Site Key', 'um-recaptcha' ),
					'tooltip'     => __( 'You can register your site and generate a site key via <a href="https://www.google.com/recaptcha/">Google reCAPTCHA</a>', 'um-recaptcha' ),
					'size'        => 'medium',
					'conditional' => array( 'g_recaptcha_version', '=', 'v2' ),
				),
				array(
					'id'          => 'g_recaptcha_secretkey',
					'type'        => 'text',
					'label'       => __( 'Secret Key', 'um-recaptcha' ),
					'tooltip'     => __( 'Keep this a secret. You can get your secret key via <a href="https://www.google.com/recaptcha/">Google reCAPTCHA</a>', 'um-recaptcha' ),
					'size'        => 'medium',
					'conditional' => array( 'g_recaptcha_version', '=', 'v2' ),
				),
				array(
					'id'          => 'g_recaptcha_type',
					'type'        => 'select',
					'label'       => __( 'Type', 'um-recaptcha' ),
					'tooltip'     => __( 'The type of reCAPTCHA to serve.', 'um-recaptcha' ),
					'options'     => array(
						'audio' => __( 'Audio', 'um-recaptcha' ),
						'image' => __( 'Image', 'um-recaptcha' ),
					),
					'size'        => 'small',
					'conditional' => array( 'g_recaptcha_version', '=', 'v2' ),
				),
				array(
					'id'          => 'g_recaptcha_language_code',
					'type'        => 'select',
					'label'       => __( 'Language', 'um-recaptcha' ),
					'tooltip'     => __( 'Select the language to be used in your reCAPTCHA.', 'um-recaptcha' ),
					'options'     => array(
						'ar'     => 'Arabic',
						'af'     => 'Afrikaans',
						'am'     => 'Amharic',
						'hy'     => 'Armenian',
						'az'     => 'Azerbaijani',
						'eu'     => 'Basque',
						'bn'     => 'Bengali',
						'bg'     => 'Bulgarian',
						'ca'     => 'Catalan',
						'zh-HK'  => 'Chinese (Hong Kong)',
						'zh-CN'  => 'Chinese (Simplified)',
						'zh-TW'  => 'Chinese (Traditional)',
						'hr'     => 'Croatian',
						'cs'     => 'Czech',
						'da'     => 'Danish',
						'nl'     => 'Dutch',
						'en-GB'  => 'English (UK)',
						'en'     => 'English (US)',
						'et'     => 'Estonian',
						'fil'    => 'Filipino',
						'fi'     => 'Finnish',
						'fr'     => 'French',
						'fr-CA'  => 'French (Canadian)',
						'gl'     => 'Galician',
						'ka'     => 'Georgian',
						'de'     => 'German',
						'de-AT'  => 'German (Austria)',
						'de-CH'  => 'German (Switzerland)',
						'el'     => 'Greek',
						'gu'     => 'Gujarati',
						'iw'     => 'Hebrew',
						'hi'     => 'Hindi',
						'hu'     => 'Hungarain',
						'is'     => 'Icelandic',
						'id'     => 'Indonesian',
						'it'     => 'Italian',
						'ja'     => 'Japanese',
						'kn'     => 'Kannada',
						'ko'     => 'Korean',
						'lo'     => 'Laothian',
						'lv'     => 'Latvian',
						'lt'     => 'Lithuanian',
						'ms'     => 'Malay',
						'ml'     => 'Malayalam',
						'mr'     => 'Marathi',
						'mn'     => 'Mongolian',
						'no'     => 'Norwegian',
						'fa'     => 'Persian',
						'pl'     => 'Polish',
						'pt'     => 'Portuguese',
						'pt-BR'  => 'Portuguese (Brazil)',
						'pt-PT'  => 'Portuguese (Portugal)',
						'ro'     => 'Romanian',
						'ru'     => 'Russian',
						'sr'     => 'Serbian',
						'si'     => 'Sinhalese',
						'sk'     => 'Slovak',
						'sl'     => 'Slovenian',
						'es'     => 'Spanish',
						'es-419' => 'Spanish (Latin America)',
						'sw'     => 'Swahili',
						'sv'     => 'Swedish',
						'ta'     => 'Tamil',
						'te'     => 'Telugu',
						'th'     => 'Thai',
						'tr'     => 'Turkish',
						'uk'     => 'Ukrainian',
						'ur'     => 'Urdu',
						'vi'     => 'Vietnamese',
						'zu'     => 'Zulu'
					),
					'size'        => 'small',
					'conditional' => array( 'g_recaptcha_version', '=', 'v2' ),
				),
				array(
					'id'          => 'g_recaptcha_theme',
					'type'        => 'select',
					'label'       => __( 'Theme', 'um-recaptcha' ),
					'tooltip'     => __( 'Select a color theme of the widget.', 'um-recaptcha' ),
					'options'     => array(
						'dark'  => __( 'Dark', 'um-recaptcha' ),
						'light' => __( 'Light', 'um-recaptcha' ),
					),
					'size'        => 'small',
					'conditional' => array( 'g_recaptcha_version', '=', 'v2' ),
				),
				array(
					'id'          => 'g_recaptcha_size',
					'type'        => 'select',
					'label'       => __( 'Size', 'um-recaptcha' ),
					'tooltip'     => __( 'The type of reCAPTCHA to serve.', 'um-recaptcha' ),
					'options'     => array(
						'compact'   => __( 'Compact', 'um-recaptcha' ),
						'normal'    => __( 'Normal', 'um-recaptcha' ),
						'invisible' => __( 'Invisible', 'um-recaptcha' ),
					),
					'size'        => 'small',
					'conditional' => array( 'g_recaptcha_version', '=', 'v2' ),
				),
				/* Forms */
				array(
					'id'          => 'g_recaptcha_password_reset',
					'type'        => 'checkbox',
					'label'       => __( 'Enable Google reCAPTCHA on password reset form', 'um-recaptcha' ),
					'tooltip'     => __( 'Display the google Google reCAPTCHA on password reset form.', 'um-recaptcha' ),
					'conditional' => array( 'g_recaptcha_status', '=', 1 ),
				),
			),
		);

		return $settings;
	}
}
