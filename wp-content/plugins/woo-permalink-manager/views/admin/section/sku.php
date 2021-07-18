<?php

if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * @var bool $free
 * @var string $sku
 * @var string $product
 * @var string $productPath
 */
use  Premmerce\UrlManager\Admin\Settings ;
#premmerce_clear
$free = true;
#/premmerce_clear
switch ( $product ) {
    case 'category_slug':
        $productPath = '/category';
        break;
    case 'hierarchical':
        $productPath = 'parent-category/category';
        break;
    default:
        $productPath = '';
        break;
}
?>
<table class="form-table">
    <tbody class="<?php 
echo  ( $free ? 'is-free' : '' ) ;
?>">
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php 
echo  Settings::OPTIONS ;
?>[sku]"
                       value="" <?php 
checked( '', $sku );
?>>
                <span>
				<?php 
esc_html_e( 'Use WooCommerce settings', 'premmerce-url-manager' );
?>
                </span>

            </label>
        </th>
    </tr>
    <tr>
        <th>
            <label class="flex-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="radio" name="<?php 
echo  Settings::OPTIONS ;
?>[sku]"
                       value="sku" <?php 
checked( 'sku', $sku );
?>>
				<?php 
esc_html_e( 'Replace product slug', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
            </p>
        </th>
        <td>
            <code><?php 
echo  home_url( $productPath . '/SKU' ) ;
?></code>
        </td>
    </tr>
    </tbody>
</table>
