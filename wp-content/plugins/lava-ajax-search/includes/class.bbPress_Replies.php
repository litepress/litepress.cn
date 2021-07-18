<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_bbPress_Replies')):

	class Lava_Ajax_Search_bbPress_Replies extends Lava_Ajax_Search_bbPress {
		public $type = 'reply';
		
		public static function instance() {
			static $instance = null;

			if (null === $instance) {
				$instance = new Lava_Ajax_Search_bbPress_Replies();
			}
			return $instance;
		}
		
		private function __construct() {}
	}

endif;
?>