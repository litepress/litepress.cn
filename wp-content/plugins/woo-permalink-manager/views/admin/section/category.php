<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

use Premmerce\UrlManager\Admin\Settings;

?>
<table class="form-table">
    <tbody>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[category]"
                       value="" <?php checked( '', $category ); ?>>
				<?php esc_html_e( 'Use WooCommerce settings', 'premmerce-url-manager' ); ?>
            </label>
        </th>
    </tr>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[category]"
                       value="slug" <?php checked( 'slug', $category ); ?>>
				<?php esc_html_e( 'Category slug', 'premmerce-url-manager' ) ?>
            </label>
        </th>
        <td>
            <code><?php echo home_url( '/category' ); ?></code>
        </td>
    </tr>
    <tr>
        <th>
            <label class="flex-label">
                <input type="radio" name="<?php echo Settings::OPTIONS; ?>[category]"
                       value="hierarchical" <?php checked( 'hierarchical', $category ); ?>>
				<?php esc_html_e( 'Full category path', 'premmerce-url-manager' ); ?>
            </label>
        </th>
        <td>
            <code><?php echo home_url( 'parent-category/category' ); ?></code>
        </td>
    </tr>
    </tbody>
</table>