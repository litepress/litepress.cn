<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Helpers.
 */
class JCK_SFR_Helpers {
	/**
	 * Count nested items in array.
	 *
	 * @param array $array
	 *
	 * @return int
	 */
	public static function count_nested( $array = array() ) {
		$count = 0;

		if ( empty( $array ) ) {
			return $count;
		}

		foreach ( $array as $array_item ) {
			$count += count( $array_item );
		}

		return $count;
	}
}