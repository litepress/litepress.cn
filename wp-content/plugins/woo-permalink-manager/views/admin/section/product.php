<?php

if ( ! defined('WPINC')) {
    die;
}

use Premmerce\UrlManager\Admin\Settings;

?>
<table class="form-table">
    <tbody>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[product]"
                       value="" <?php checked( '', $product ); ?>>
                <span>
				<?php esc_html_e( 'Use WooCommerce settings', 'premmerce-url-manager' ); ?>
                </span>

            </label>
        </th>
    </tr>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[product]"
                       value="slug" <?php checked( 'slug', $product ); ?>>
				<?php esc_html_e( 'Product slug', 'premmerce-url-manager' ); ?>
            </label>
        </th>
        <td>
            <code><?php echo home_url( '/sample-product' ); ?></code>
        </td>
    </tr>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[product]"
                       value="category_slug" <?php checked( 'category_slug', $product ); ?>>
				<?php esc_html_e( 'Product slug with primary category', 'premmerce-url-manager' ); ?>
            </label>
        </th>
        <td>
            <code><?php echo home_url( '/category/sample-product' ); ?></code>
        </td>
    </tr>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[product]"
                       value="hierarchical" <?php checked( 'hierarchical', $product ); ?>>
				<?php esc_html_e( 'Full product path', 'premmerce-url-manager' ); ?>
            </label>
        </th>
        <td>
            <code><?php echo home_url( 'parent-category/category/sample-product' ); ?></code>
        </td>
    </tr>
    </tbody>
</table>