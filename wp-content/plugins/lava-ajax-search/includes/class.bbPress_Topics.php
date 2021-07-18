<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('Lava_Ajax_Search_bbPress_Topics')):

	class Lava_Ajax_Search_bbPress_Topics extends Lava_Ajax_Search_bbPress {
		public $type = 'topic';

		public static function instance() {
			static $instance = null;

			if (null === $instance) {
				$instance = new Lava_Ajax_Search_bbPress_Topics();
			}
			return $instance;
		}

		private function __construct() {}

	}

endif;