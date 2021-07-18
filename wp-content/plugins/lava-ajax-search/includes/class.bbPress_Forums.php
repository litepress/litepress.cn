<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_bbPress_Forums')):

	class Lava_Ajax_Search_bbPress_Forums extends Lava_Ajax_Search_bbPress {
		public $type = 'forum';

		public static function instance() {
			static $instance = null;

			if (null === $instance) {
				$instance = new Lava_Ajax_Search_bbPress_Forums();
			}

			return $instance;
		}
		private function __construct() {}
	}
endif;