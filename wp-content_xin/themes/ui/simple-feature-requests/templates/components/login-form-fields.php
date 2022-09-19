<?php
/**
 * The Template for displaying the login form fields.
 *
 * @author        James Kemp
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$username = filter_input( INPUT_POST, 'jck-sfr-login-username', FILTER_SANITIZE_STRING );
$email    = filter_input( INPUT_POST, 'jck-sfr-login-email', FILTER_SANITIZE_STRING );
?>

<p>
	<span class="jck-sfr-js-toggle-register-login">
		<?php _e( "Don't have an account?", 'simple-feature-requests' ); ?>
		<a href="javascript: void( 0 );" data-jck-sfr-toggle="register-login" data-jck-sfr-toggle-submission-user-type="register">
			<?php _e( "Register", 'simple-feature-requests' ); ?>
		</a>
	</span>
	<span class="jck-sfr-js-toggle-register-login" style="display: none;">
		<?php _e( "Already have an account?", 'simple-feature-requests' ); ?>
		<a href="javascript: void( 0 );" data-jck-sfr-toggle="register-login" data-jck-sfr-toggle-submission-user-type="login">
			<?php _e( "Login", 'simple-feature-requests' ); ?>
		</a>
	</span>
</p>

<label class="jck-sfr-form__row jck-sfr-js-toggle-register-login" for="jck-sfr-input-username" style="display: none;">
	<strong><?php _e( 'Username', 'simple-feature-requests' ); ?></strong>
	<input id="jck-sfr-input-username" name="jck-sfr-login-username" class="jck-sfr-form__field jck-sfr-form__field--input" type="text" value="<?php echo esc_attr( $username ); ?>">
</label>

<label class="jck-sfr-form__row" for="jck-sfr-input-email">
	<strong><?php _e( 'Email', 'simple-feature-requests' ); ?><span class="jck-sfr-js-toggle-register-login"> / <?php _e( 'Username', 'simple-feature-requests' ); ?></span></strong>
	<input id="jck-sfr-input-email" name="jck-sfr-login-email" class="form-control jck-sfr-form__field jck-sfr-form__field--input" type="text" value="<?php echo esc_attr( $email ); ?>">
</label>

<label class="jck-sfr-form__row" for="jck-sfr-input-password">
	<strong><?php _e( 'Password', 'simple-feature-requests' ); ?></strong>
	<input id="jck-sfr-input-password" name="jck-sfr-login-password" class="form-control jck-sfr-form__field jck-sfr-form__field--input" type="password">
</label>

<label class="jck-sfr-form__row jck-sfr-js-toggle-register-login" for="jck-sfr-input-repeat-password" style="display: none;">
	<strong><?php _e( 'Repeat Password', 'simple-feature-requests' ); ?></strong>
	<input id="jck-sfr-input-repeat-password" name="jck-sfr-login-repeat-password" class="form-control jck-sfr-form__field jck-sfr-form__field--input" type="password">
</label>

<input type="hidden" name="jck-sfr-login-user-type" value="login">