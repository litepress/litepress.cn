<?php

if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * @var bool $free
 * @var string $tag
 * @var string $canonical
 * @var string $redirect
 * @var string $use_primary_category
 * @var string $breadcrumbs
 * @var string $br_remove_shop
 */
use  Premmerce\UrlManager\Admin\Settings ;
#premmerce_clear
$free = true;
#/premmerce_clear
?>

<table class="form-table">
    <tbody class="<?php 
echo  ( $free ? 'is-free' : '' ) ;
?>">
    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="checkbox" name="<?php 
echo  Settings::OPTIONS ;
?>[tag]"
                                                             value="slug" <?php 
checked( 'slug', $tag );
?>>
				<?php 
esc_html_e( 'Remove product tag base', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
            </p>
        </th>
    </tr>
    <tr>
        <th>
            <label>
                <input type="checkbox" name="<?php 
echo  Settings::OPTIONS ;
?>[use_primary_category]"
					<?php 
checked( 'on', $use_primary_category );
?>>
				<?php 
esc_html_e( 'Use primary category', 'premmerce-url-manager' );
?>
            </label>
            <p class="description"><?php 
esc_html_e( "Use 'Yoast SEO' primary category to build product path", 'premmerce-url-manager' );
?></p>
        </th>
    </tr>
    <tr>
        <th>
            <label>
                <input type="checkbox" name="<?php 
echo  Settings::OPTIONS ;
?>[canonical]"
					<?php 
checked( 'on', $canonical );
?>>
				<?php 
esc_html_e( 'Add canonicals', 'premmerce-url-manager' );
?>
            </label>
            <p class="description"><?php 
esc_html_e( 'Add canonical meta tag to duplicated pages', 'premmerce-url-manager' );
?></p>
        </th>
    </tr>
    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="checkbox" name="<?php 
echo  Settings::OPTIONS ;
?>[redirect]"
					<?php 
checked( 'on', $redirect );
?>>
				<?php 
esc_html_e( 'Create redirects', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
				<?php 
esc_html_e( 'Create 301 redirect from duplicated pages', 'premmerce-url-manager' );
?>
            </p>
        </th>
    </tr>
    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="checkbox" name="<?php 
echo  Settings::OPTIONS ;
?>[breadcrumbs]"
					<?php 
checked( 'on', $breadcrumbs );
?>>
				<?php 
esc_html_e( 'Support breadcrumbs', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
				<?php 
esc_html_e( 'Enable breadcrumbs support', 'premmerce-url-manager' );
?>
            </p>
        </th>
    </tr>
    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="checkbox" name="<?php 
echo  Settings::OPTIONS ;
?>[br_remove_shop]"
					<?php 
checked( 'on', $br_remove_shop );
?>>
				<?php 
esc_html_e( 'Remove Shop', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
				<?php 
esc_html_e( 'Remove "Shop" from breadcrumbs', 'premmerce-url-manager' );
?>
            </p>
        </th>
    </tr>

    </tbody>
</table>