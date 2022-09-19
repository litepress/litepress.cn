<?php
/**
 * 步骤控制器基类
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Controller\Web;

class Verify {

	public function __construct() {

	}

	public function select_type() {
		wprpv_get_template( 'select-verify-type.php' );
	}

	public function personal() {
		wprpv_get_template( 'verify-for-personal.php' );
	}

	public function enterprise() {
		wprpv_get_template( 'verify-for-enterprise.php' );
	}

	public function complete() {
		wprpv_get_template( 'complete.php' );
	}

	public function job_face_verify() {
		wprpv_get_template( 'job-face-verify.php', true );
	}

	public function face_verify_complete() {
		wprpv_get_template( 'face-verify-complete.php', true );
	}

}
