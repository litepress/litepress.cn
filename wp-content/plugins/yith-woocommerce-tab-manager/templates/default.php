<?php
/**
 * This template show the tab content
 *
 * @package YITH WooCommerce Tab Manager\Templates
 */

$content = wpautop( $content );
?>

<div class="tab-editor-container ywtm_content_tab"> <?php echo do_shortcode( $content ); ?></div>
