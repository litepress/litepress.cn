<?php
/**
 * 手机扫脸完成后的回调页面
 *
 * @package WP_REAL_PERSON_VERIFY
 */

use WCY\WC_Product_Vendor_Registration\Src\Service\Face_Verify;

$user_id = (int)$_GET['user_id'];
$task_id = sanitize_key( $_GET['task_id'] );
if ( isset( $user_id ) && $user_id !== 0 ) {
	$face_verify_service = new Face_Verify( $user_id );
	$face_verify_service->done( $task_id );
}
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel='stylesheet' id='bootstrap-css'  href='/wp-content/themes/ui/assets/css/bootstrap.min.css' type='text/css' media='all' />
<link rel='stylesheet' id='bootstrap-css'  href='/wp-content/plugins/woocommerce-product-vendor-registration/assets/css/wcpvr.css' type='text/css' media='all' />
<link rel='stylesheet' id='fontawesome-css'  href='/wp-content/themes/ui/assets/fontawesome/css/all.min.css' type='text/css' media='all' />
<main class="step-main" style="height: 100%;align-items: center;
    display: flex;">


    <article class="container step-page step-complete">
        <div class="row  text-center justify-content-around ">
            <div class="col-xl-3">
                <div class="card  rounded-3 theme-boxshadow">
                    <div class="card-body">
                        <i class="fad fa-check-circle"></i>
                        <h1 class="title">认证通过</h1>
                        <p class="text-center" >
                            认证完成，请返回电脑端查看认证结果
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </article>

</main>