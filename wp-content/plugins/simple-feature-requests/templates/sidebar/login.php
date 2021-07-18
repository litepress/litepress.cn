<?php
/**
 * The Template for displaying the login form.
 *
 * @author        James Kemp
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="jck-sfr-sidebar-widget jck-sfr-sidebar-widget--login">
	<?php if ( ! is_user_logged_in() ) { ?>
		<form class="jck-sfr-form jck-sfr-form--login" action="" method="post">
			<?php
			/**
			 * jck_sfr_before_main_content hook.
			 *
			 * @hooked JCK_SFR_Template_Hooks::login_form_fields() - 10
			 */
			do_action( 'jck_sfr_login_form' );
			?>

			<?php wp_nonce_field( 'jck-sfr-login', 'jck-sfr-login-nonce' ); ?>
			<button class="jck-sfr-form__button" name="jck-sfr-login" type="submit">
				<span class="jck-sfr-js-toggle-register-login"><?php _e( 'Login', 'simple-feature-requests' ); ?></span>
				<span class="jck-sfr-js-toggle-register-login" style="display: none;"><?php _e( 'Register', 'simple-feature-requests' ); ?></span>
			</button>
		</form>
	<?php } else { ?>
		<?php
		global $current_user;
		$user = new JCK_SFR_User( $current_user->ID );
		?>
		<p class="jck-sfr-profile">
			<img src="<?php echo get_avatar_url( $current_user->ID, array( 'size' => 52 ) ); ?>" class="jck-sfr-profile__avatar">
			<strong class="jck-sfr-profile__username"><?php printf( __( 'Hey, %s.', 'simple-feature-requests' ), $user->get_username() ); ?></strong>
			<br>
			<a class="jck-sfr-profile__logout" href="<?php echo wp_logout_url( JCK_SFR_Post_Types::get_archive_url() ); ?>"><?php _e( 'Logout', 'simple-feature-requests' ); ?></a>
		</p>
	<?php } ?>
</div>