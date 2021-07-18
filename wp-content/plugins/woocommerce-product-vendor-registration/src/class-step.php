<?php

namespace WCY\WC_Product_Vendor_Registration\Src;

class Step {

	public function __construct() {
		add_shortcode( 'vendor-registration', function () {
			require_once '../templates/step-1.php';
		} );
	}

}
