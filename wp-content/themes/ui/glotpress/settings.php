<?php
/**
 * The settings page
 *
 * Displays the settings page for a user.
 *
 * @link http://glotpress.org
 *
 * @package GlotPress
 * @since 2.0.0
 */

gp_title( __( '翻译偏好 - LitePress翻译平台', 'glotpress' ) );
gp_breadcrumb( array( __( '翻译偏好', 'glotpress' ) ) );
gp_tmpl_header();

$per_page = (int) get_user_option( 'gp_per_page' );
if ( 0 === $per_page ) {
	$per_page = 15;
}

$default_sort = get_user_option( 'gp_default_sort' );
if ( ! is_array( $default_sort ) ) {
	$default_sort = array(
		'by'  => 'priority',
		'how' => 'desc',
	);
}
?>


  
  <div class="container notice-container">
      <div class="row">
    <div class="notice" id="help-notice">第一次翻译吗？你可以阅读<a href="https://make.wordpress.org/polyglots/handbook/tools/glotpress-translate-wordpress-org/" target="_blank" one-link-mark="yes">《翻译员手册》</a> 获得帮助^_^<a id="hide-help-notice" class="secondary" style="float: right;" href="" one-link-mark="yes">×</a> </div></div>
</div>
<div class="container setting">


<form action="" method="post">
	<?php require_once __DIR__ . '/settings-edit.php'; ?>
	<br>
	<?php gp_route_nonce_field( 'update-settings_' . get_current_user_id() ); ?>

	<input class="btn btn-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'glotpress' ); ?>">
</form>
</div>
<style>
    .fl-builder-content.fl-builder-content-1386{
    position: fixed;
    bottom: 0;
    right: 0;
    left: 0;
}
</style>
<?php
gp_tmpl_footer();
