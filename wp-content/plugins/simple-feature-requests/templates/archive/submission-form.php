<?php
/**
 * The Template for displaying the submission form.
 *
 * @author        James Kemp
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $jck_sfr_requests;

$title       = empty( $_POST ) ? filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING ) : filter_input( INPUT_POST, 'jck-sfr-submission-title', FILTER_SANITIZE_STRING );
$description = filter_input( INPUT_POST, 'jck-sfr-submission-description', FILTER_SANITIZE_STRING );
$search      = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING );
$submission  = isset( $submission ) ? $submission : true;
?>

<form class="jck-sfr-form jck-sfr-form--submission" action="" method="post" autocomplete="off">
	<input style="display:none" type="text" name="jck-sfr-ignore-autocomplete" />
	<input style="display:none" type="password" name="jck-sfr-ignore-autocomplete-password" />

	<label for="jck-sfr-input-title" class="jck-sfr-form__row">
		<strong><?php _e( 'Your Request', 'simple-feature-requests' ); ?></strong>
		<div class="jck-sfr-search-field">
			<input id="jck-sfr-input-title" name="jck-sfr-submission-title" class="jck-sfr-form__field jck-sfr-form__field--input jck-sfr-form__title" type="text" placeholder="<?php esc_attr_e( 'Enter your request...', 'simple-feature-requests' ); ?>" value="<?php echo esc_attr( $title ); ?>" autocomplete="jck-sfr-ac-off">
			<i class="jck-sfr-search-field__icon jck-sfr-search-field__icon--loader"></i>
			<i class="jck-sfr-search-field__icon jck-sfr-search-field__icon--clear jck-sfr-js-clear-search-field" <?php if ( $search ) {
				echo 'style="display: block;"';
			} ?>></i>
		</div>
	</label>

	<?php if ( $submission ) { ?>
		<div class="jck-sfr-form__reveal" <?php if ( $search && $jck_sfr_requests->found_posts <= 0 ) {
			echo 'style="display: block;"';
		} ?>>
			<label for="jck-sfr-input-description" class="jck-sfr-form__row">
				<strong><?php _e( 'Description', 'simple-feature-requests' ); ?></strong>
				<textarea id="jck-sfr-input-description" name="jck-sfr-submission-description" class="jck-sfr-form__field jck-sfr-form__field--textarea"><?php echo $description; ?></textarea>
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
			<button class="jck-sfr-form__button" name="jck-sfr-submission" type="submit"><?php _e( 'Submit', 'simple-feature-requests' ); ?></button>
		</div>
		<div class="jck-sfr-form__choices" <?php if ( ! $search || $jck_sfr_requests->found_posts <= 0 ) {
			echo 'style="display: none;"';
		} ?>>
			<span class="jck-sfr-form__choices-vote"><?php printf( __( 'Vote for an existing request (%s)', 'simple-feature-requests' ), sprintf( '<span class="jck-sfr-form__choices-count">%s</span>', $jck_sfr_requests->found_posts ) ); ?></span>
			<span class="jck-sfr-form__choices-or">or</span>
			<a href="#" class="jck-sfr-form__choices-post"><?php _e( 'Post a new request', 'simple-feature-requests' ); ?></a>
		</div>
	<?php } ?>
</form>