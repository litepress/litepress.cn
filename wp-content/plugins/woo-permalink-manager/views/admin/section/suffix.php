<?php

if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * @var bool $free
 * @var string $suffix
 * @var string $enable_suffix_products
 * @var string $enable_suffix_categories
 * @var string $category
 *
 */
use  Premmerce\UrlManager\Admin\Settings ;
#premmerce_clear
$free = true;
#/premmerce_clear
?>

<script>
    jQuery(document).ready(function ($) {
        jQuery(":input").inputmask();

        $('#permalink_url_suffix').on('input', function () {
            $('.permalink_url_suffix').text($(this).val());
        });
    });
</script>

<table class="form-table">
    <tbody class="<?php 
echo  ( $free ? 'is-free' : '' ) ;
?>">
    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="text"
                                                             id="permalink_url_suffix"
                                                             name="<?php 
echo  Settings::OPTIONS ;
?>[suffix]"
                                                             value="<?php 
echo  $suffix ;
?>"
                                                             data-inputmask="'mask': '.[*{1,10}]'"
                >
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
				<?php 
esc_html_e( 'Specify suffix for your urls. For example ', 'premmerce-url-manager' );
?> <b>.html</b>
            </p>
        </th>
    </tr>

    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="checkbox"
                                                             name="<?php 
echo  Settings::OPTIONS ;
?>[enable_suffix_products]"
					<?php 
checked( 'on', $enable_suffix_products );
?>>
				<?php 
esc_html_e( 'Enable for products', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
                <br>
				<?php 

if ( $product == 'slug' ) {
    ?>

                    <code>
						<?php 
    echo  home_url( '/sample-product' ) ;
    ?><span class="permalink_url_suffix"><?php 
    echo  $suffix ;
    ?></span>
                    </code>

				<?php 
} elseif ( $product === 'category_slug' ) {
    ?>

                    <code>
						<?php 
    echo  home_url( '/category/sample-product' ) ;
    ?><span class="permalink_url_suffix"><?php 
    echo  $suffix ;
    ?></span>
                    </code>

				<?php 
} elseif ( $product === 'hierarchical' ) {
    ?>

                    <code>
						<?php 
    echo  home_url( 'parent-category/category/sample-product' ) ;
    ?><span
                                class="permalink_url_suffix"><?php 
    echo  $suffix ;
    ?></span>
                    </code>

				<?php 
}

?>
            </p>
        </th>
    </tr>

    <tr>
        <th>
            <label class="premium-only-label">
                <input <?php 
echo  ( $free ? 'disabled' : '' ) ;
?> type="checkbox"
                                                             name="<?php 
echo  Settings::OPTIONS ;
?>[enable_suffix_categories]"
					<?php 
checked( 'on', $enable_suffix_categories );
?>>
				<?php 
esc_html_e( 'Enable for categories', 'premmerce-url-manager' );
?>
            </label>
            <p class="description">
                <span class="premium-only-feature"><?php 
esc_html_e( 'Available only in premium version', 'premmerce-url-manager' );
?></span>
                <br>
				<?php 

if ( $category == 'slug' ) {
    ?>

                    <code>
						<?php 
    echo  home_url( '/category' ) ;
    ?><span class="permalink_url_suffix"><?php 
    echo  $suffix ;
    ?></span>
                    </code>


				<?php 
} elseif ( $category === 'hierarchical' ) {
    ?>
                    <code>
						<?php 
    echo  home_url( 'parent-category/category' ) ;
    ?><span class="permalink_url_suffix"><?php 
    echo  $suffix ;
    ?></span>
                    </code>

				<?php 
}

?>
            </p>
        </th>
    </tr>
    </tbody>
</table>