<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.1
 */

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product;

$attachment_ids = $product->get_gallery_image_ids();

if ( empty( $attachment_ids ) ) {
	return;
}

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	)
);
?>
<h4>屏幕截图</h4>
<section class="screenshots">
    <!-- Swiper -->
    <div class="swiper-container gallery-top">
        <div class="swiper-wrapper">
			<?php
			foreach ( $attachment_ids as $attachment_id ) {
				//echo Image instead of URL
				echo '<div class="swiper-slide"><div class="img-box">' . wp_get_attachment_image( $attachment_id, 'full' ) . '</div></div>';
			}
			?>

        </div>
        <div class="swiper-pagination"></div>
        <!-- Add Arrows -->
        <div class="swiper-button-next hidden-xs"></div>
        <div class="swiper-button-prev hidden-xs"></div>
    </div>
    <div class="swiper-container gallery-thumbs">
        <div class="swiper-wrapper">
			<?php
			global $product;
			$attachment_ids = $product->get_gallery_image_ids();
			foreach ( $attachment_ids as $attachment_id ) {
				echo '<div class="swiper-slide"><div class="img-box"><img   src="' . $shop_thumbnail_image_url = wp_get_attachment_image_src( $attachment_id, 512 )[0] . '" alt=""></div></div>';
			}
			?>
        </div>
    </div>
</section>

<script>
    var galleryTop = new Swiper('.screenshots .gallery-top', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.screenshots .swiper-button-next',
            prevEl: '.screenshots .swiper-button-prev',
        },
        thumbs: {
            swiper: {
                el: '.screenshots .gallery-thumbs',
                spaceBetween: 5,
                slidesPerView: 4.01,
                freeMode: true,
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
            }
        },
        pagination: {
            el: '.screenshots .swiper-pagination',
        },
    });
</script>

