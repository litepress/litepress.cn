<?php if ( ! defined( 'ABSPATH' ) ) exit;


$is_core = get_post_meta( get_the_ID(), '_um_core', true ); ?>

<div class="um-admin-boxed-links um-admin-ajaxlink <?php if ( $is_core ) echo 'is-core-form'; ?>">

	<?php if ( $is_core ) { ?>
		<p><?php _e( '<strong>Note:</strong> Form type cannot be changed for the default forms.', 'ultimate-member' ); ?></p>
	<?php } ?>

	<a href="javascript:void(0);" data-role="register"><?php _e( 'Registration Form', 'ultimate-member' ); ?></a>

	<a href="javascript:void(0);" data-role="profile"><?php _e('Profile Form', 'ultimate-member' ); ?></a>

	<a href="javascript:void(0);" data-role="login"><?php _e( 'Login Form', 'ultimate-member' ); ?></a>

	<input type="hidden" name="form[_um_mode]" id="form__um_mode" value="<?php echo esc_attr( UM()->query()->get_meta_value( '_um_mode', null, 'register' ) ); ?>" />

</div>
<div class="um-admin-clear"></div>