<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Admin_Email' ) ) {

	class YITH_Tab_Manager_Admin_Email extends WC_Email {


		public function __construct() {
			$this->id = 'yith_tab_manager_send_info';

			$this->title       = __( '[YITH Tab Manager] Product Info request', 'yith-woocommerce-tab-manager' );
			$this->description = __( 'This email is sent when a customer click on "send" on the tab manager contact form', 'yith-woocommerce-tab-manager' );

			$this->template_base = YWTM_TEMPLATE_PATH . '/';
			$this->template_html = 'emails/ask-product-info.php';


			$this->enable_cc  = $this->get_option( 'enable_cc' );
			$this->enable_cc  = $this->enable_cc == 'yes';
			$this->heading = __( 'Ask for {product_name}', 'yith-woocommerce-tab-manager' );
			parent::__construct();

			$this->email_type = 'html';
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

			global $woocommerce_wpml;

			$is_wpml_configured = apply_filters( 'wpml_setting', false, 'setup_complete' );
			if ( $is_wpml_configured && defined( 'WCML_VERSION' ) && $woocommerce_wpml ) {
				add_action( 'send_tab_manager_email_notification', array( $this, 'refresh_email_lang' ), 10, 1 );
			}

			add_action( 'send_tab_manager_email_notification', array( $this, 'trigger' ), 15, 1 );

		}

		/**
		 * @param $order_id
		 */
		function refresh_email_lang( $args ){
			global $sitepress;

			if ( ! empty( $args['language'] ) ) {
				$lang = $args['language'];

				$sitepress->switch_lang($lang,true);
			}

		}


		public function trigger( $args ) {

			if ( $this->is_enabled() && !empty( $args ) ) {

				$this->from = !empty( $args['user_email'] ) ? $args['user_email'] : false;
				if( $this->from ){

					$this->args = array(
						'product_id' => $args['product_id'],
						'user_info' => array(
							'web_address' => $args['web_address'],
							'username' => $args['username'],
							'user_email' => $args['user_email'],
							'message' => $args['message']
						)
					);


					$this->subject = !empty( $args['subject'] ) ? $args['subject'] : $this->get_subject();

					$this->placeholders['{username}'] = !empty( $args['username'] )  ? $args['username']  : $this->from ;

					$product = wc_get_product( $args['product_id'] );
					$this->placeholders['{product_name}'] = $product  ? $product->get_formatted_name() : '';
					$this->email_type = 'html';

					$recipients = (array) $this->get_recipient();
					if ( $this->enable_cc ) {
						$recipients[] = $this->from;
					}
					$recipients = implode( ',', $recipients );
					// remove spaces for avoiding problems on multi-recipients emails
					$recipients = str_replace( ' ', '', $recipients );

					$return = $this->send( $recipients, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

					if( $args['is_ajax'] ){

						if( $return ){

							$message = "<span class='message_send'>".__( 'Message sent!' , 'yith-woocommerce-tab-manager' )."</span>";
						}else{
							$message = "<span class='error_message'>".__( 'Error, Please Try Again!' , 'yith-woocommerce-tab-manager' )."<br /></span>";
						}

						wp_send_json( $message );
					}
				}
			}

		}

		/**
		 * @return string
		 */
		public function get_default_subject() {
			return  __('Request from {username}','yith-woocommerce-tab-manager' );
		}


		public function get_headers() {
			$headers = "Reply-to: " . $this->from . "\r\n";

			if ( $this->enable_cc ) {
				$headers .= "Cc: " . $this->from . "\r\n";
			}

			$headers .= "Content-Type: " . $this->get_content_type() . "\r\n";

			return apply_filters( 'woocommerce_email_headers', $headers, $this->id, $this->object );
		}

		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail
		 * @since  1.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function get_content_html() {
			ob_start();

			wc_get_template( $this->template_html, array(
				'contact_info'          => $this->args,
				'email_heading'     => $this->get_heading(),
				'email_description' => $this->format_string( $this->get_option( 'email-description' ) ),
				'sent_to_admin'     => true,
				'plain_text'        => false,
				'email'             => $this
			), '', $this->template_base );



			return ob_get_clean();
		}


		/**
		 * Init form fields to display in WC admin pages
		 *
		 * @return void
		 * @since  1.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'   => array(
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-tab-manager' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'yith-woocommerce-tab-manager' ),
					'default' => 'yes'
				),
				'recipient' => array(
					'title'       => __( 'Recipient(s)', 'yith-woocommerce-tab-manager' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (separated by commas) for this email. Defaults to <code>%s</code>', 'yith-woocommerce-tab-manager' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => ''
				),

				'heading'           => array(
					'title'       => __( 'Email Heading', 'yith-woocommerce-tab-manager' ),
					'type'        => 'text',
					'description' => sprintf( __( 'This field lets you change the main heading in email notification. Leave it blank to use default heading type: <code>%s</code>.', 'yith-woocommerce-tab-manager' ), $this->heading ),
					'placeholder' => '',
					'default'     => ''
				),
				'enable_cc'         => array(
					'title'       => __( 'Send CC copy', 'yith-woocommerce-tab-manager' ),
					'type'        => 'checkbox',
					'description' => __( 'Send a carbon copy to the user', 'yith-woocommerce-tab-manager' ),
					'default'     => 'no'
				),
				'email-description' => array(
					'title'       => __( 'Email Description', 'yith-woocommerce-tab-manager' ),
					'type'        => 'textarea',
					'placeholder' => '',
					'default'     => __( 'Hi administrator, you have just received an email about the following product:', 'yith-woocommerce-tab-manager' ),

				),
			);
		}
	}
}

return new YITH_Tab_Manager_Admin_Email();