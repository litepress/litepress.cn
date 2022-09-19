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

<div class="theme-boxshadow bg-white jck-sfr-sidebar-widget jck-sfr-sidebar-widget--login">
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
			<button class="jck-sfr-form__button btn btn-primary" name="jck-sfr-login" type="submit">
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
<section class="mt-3 d-grid">
    <button   class="btn btn-primary d-block" role="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
        <i class="fad fa-paper-plane"></i>
        <span class="uabb-button-text uabb-creative-button-text">发布申请</span>
    </button>
</section>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">发布申请</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="jck-sfr-form jck-sfr-form--submission" action="" method="post" autocomplete="off">
                    <input style="display:none" type="text" name="jck-sfr-ignore-autocomplete" />
                    <input style="display:none" type="password" name="jck-sfr-ignore-autocomplete-password" />

                    <label for="jck-sfr-input-title" class="jck-sfr-form__row">
                        <strong><?php _e( 'Your Request', 'simple-feature-requests' ); ?></strong>
                        <div class="jck-sfr-search-field">
                            <input id="jck-sfr-input-title" name="jck-sfr-submission-title" class="form-control" type="text" placeholder="<?php esc_attr_e( 'Enter your request...', 'simple-feature-requests' ); ?>" value="<?php echo esc_attr( $title ); ?>" autocomplete="jck-sfr-ac-off">
                            <i class="jck-sfr-search-field__icon jck-sfr-search-field__icon--loader"></i>
                            <i class="jck-sfr-search-field__icon jck-sfr-search-field__icon--clear jck-sfr-js-clear-search-field" <?php if ( $search ) {
                                echo 'style="display: block;"';
                            } ?>></i>
                        </div>
                    </label>


                        <div class="jck-sfr-form__reveal" style="display: block;" <?php if ( $search && $jck_sfr_requests->found_posts <= 0 ) {
                            echo 'style="display: block;"';
                        } ?>>
                            <label for="jck-sfr-input-description" class="jck-sfr-form__row">
                                <strong><?php _e( 'Description', 'simple-feature-requests' ); ?></strong>
                                <textarea id="jck-sfr-input-description" name="jck-sfr-submission-description" class="form-control jck-sfr-form__field jck-sfr-form__field--textarea"><?php echo $description; ?></textarea>
                            </label>

                            <?php
                            /**
                             * jck_sfr_submission_form hook.
                             *
                             * @hooked JCK_SFR_Template_Hooks::login_form_fields() - 20
                             */
                            do_action( 'jck_sfr_submission_form' );
                            ?>

                            <?php wp_nonce_field( 'jck-sfr-submission', 'jck-sfr-submission-nonce' ); ?>
                            </div>
                        <div class="jck-sfr-form__choices" <?php if ( ! $search || $jck_sfr_requests->found_posts <= 0 ) {
                            echo 'style="display: none;"';
                        } ?>>
                            <span class="jck-sfr-form__choices-vote"><?php printf( __( 'Vote for an existing request (%s)', 'simple-feature-requests' ), sprintf( '<span class="jck-sfr-form__choices-count">%s</span>', $jck_sfr_requests->found_posts ) ); ?></span>
                            <span class="jck-sfr-form__choices-or">or</span>
                            <a href="#" class="jck-sfr-form__choices-post"><?php _e( 'Post a new request', 'simple-feature-requests' ); ?></a>
                        </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                <button class="jck-sfr-form__button btn btn-primary" name="jck-sfr-submission" type="submit"><?php _e( 'Submit', 'simple-feature-requests' ); ?></button>
            </div>
            </form>
        </div>
    </div>
</div>