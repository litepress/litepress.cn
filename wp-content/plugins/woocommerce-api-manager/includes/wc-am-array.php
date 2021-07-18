<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Array Formatting Class
 *
 * @since       1.3.7
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Array Formatting
 */
class WC_AM_Array {

	/**
	 * Variables
	 */
	private $array_compare_by_key;

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Array
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Merges arrays recursively with an associative key
	 * To merge arrays that are numerically indexed use the PHP array_merge_recursive() function
	 *
	 * @since 1.1
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	public function array_merge_recursive_associative( $array1, $array2 ) {
		$merged_arrays = $array1;

		if ( is_array( $array2 ) ) {
			foreach ( $array2 as $key => $val ) {
				if ( is_array( $array2[ $key ] ) ) {
					$merged_arrays[ $key ] = ( isset( $merged_arrays[ $key ] ) && is_array( $merged_arrays[ $key ] ) ) ? $this->array_merge_recursive_associative( $merged_arrays[ $key ], $array2[ $key ] ) : $array2[ $key ];
				} else {
					$merged_arrays[ $key ] = $val;
				}
			}
		}

		return $merged_arrays;
	}

	/**
	 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
	 * keys to arrays rather than overwriting the value in the first array with the duplicate
	 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
	 * this happens (documented behavior):
	 *
	 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('org value', 'new value'));
	 *
	 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
	 * Matching keys' values in the second array overwrite those in the first array, as is the
	 * case with array_merge, i.e.:
	 *
	 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('new value'));
	 *
	 * Parameters are passed by reference, though only for performance reasons. They're not
	 * altered by this function.
	 *
	 * @since  1.1.1
	 *
	 * @param array $array2
	 *
	 * @param array $array1
	 *
	 * @return array
	 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
	 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
	 */
	public function array_merge_recursive_distinct( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				$merged [ $key ] = array_merge_recursive_distinct( $merged [ $key ], $value );
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Experimental
	 *
	 * @since 1.1.1
	 *
	 * @param mixed   $needle
	 * @param mixed   $haystack
	 * @param boolean $strict
	 *
	 * @return boolean
	 */
	public function in_array_recursive( $needle, $haystack, $strict = false ) {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && $this->in_array_recursive( $needle, $item, $strict ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes element from array based on key
	 *
	 * @since 1.1
	 * @return array New array minus removed elements
	 *
	 * For example:
	 *
	 * $fruit_inventory = array(
	 *      'apples' => 52,
	 *      'bananas' => 78,
	 *      'peaches' => 'out of season',
	 *      'pears' => 'out of season',
	 *      'oranges' => 'no longer sold',
	 *      'carrots' => 15,
	 *      'beets' => 15,
	 *    );
	 *
	 * $fruit_inventory = array_remove_by_key($fruit_inventory,
	 *                              "beets",
	 *                              "carrots");
	 */
	public function array_remove_by_key() {
		$args = func_get_args();

		return array_diff_key( $args[ 0 ], array_flip( array_slice( $args, 1 ) ) );
	}

	/**
	 * Removes element from array based on value
	 *
	 * @since 1.1
	 * @return array New array minus removed elements
	 *        For example:
	 *
	 * $fruit_inventory = array(
	 *      'apples' => 52,
	 *      'bananas' => 78,
	 *      'peaches' => 'out of season',
	 *      'pears' => 'out of season',
	 *      'oranges' => 'no longer sold',
	 *      'carrots' => 15,
	 *      'beets' => 15,
	 *    );
	 *
	 * $fruit_inventory = array_remove_by_value($fruit_inventory,
	 *                                 "out of season",
	 *                                 "no longer sold");
	 */
	public function array_remove_by_value() {
		$args = func_get_args();

		return array_diff( $args[ 0 ], array_slice( $args, 1 ) );
	}

	/**
	 * array_search_multi Finds if a value matched with a needle exists in a multidimensional array
	 *
	 * @since 1.1.1
	 *
	 * @param mixed $value  value to search for
	 * @param mixed $needle needle that needs to match value in array
	 *
	 * @param array $array  multidimensional array (for simple array use array_search)
	 *
	 * @return boolean
	 *
	 */
	public function array_search_multi( $array, $value, $needle ) {
		foreach ( $array as $index_key => $value_key ) {
			if ( $value_key[ $value ] === $needle ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Search one-dimensional array or multidimensional array
	 *
	 * if php >= 5.5 use: $key = array_search($needle, array_column($array, 'array_key'));
	 *
	 * @since  1.3.9.5
	 *
	 * @param mixed $needle
	 * @param array $haystack
	 *
	 * @return bool
	 */
	public function search_array( $needle, $haystack ) {
		if ( is_array( $haystack ) ) {
			if ( in_array( $needle, $haystack ) ) {
				return true;
			}

			foreach ( $haystack as $element ) {
				if ( is_array( $element ) && $this->search_array( $needle, $element ) ) {
					return true;
				}
			}

			return false;
		}

		return false;
	}

	/**
	 * get_array_search_multi Finds a key for a value matched by a needle in a multidimensional array
	 *
	 * @since 1.1.1
	 *
	 * @param mixed $value  value to search for
	 * @param mixed $needle needle that needs to match value in array
	 *
	 * @param array $array  multidimensional array (for simple array use array_search)
	 *
	 * @return mixed
	 *
	 */
	public function get_key_array_search_multi( $array, $value, $needle ) {
		foreach ( $array as $index_key => $value_key ) {
			if ( $value_key[ $value ] === $needle ) {
				return $value_key;
			}
		}

		return false;
	}

	/**
	 * Searches multidimensional array, and removes duplicate nested arrays.
	 *
	 * @since 1.3.7
	 *
	 * @param string $key   The nested array key to search for.
	 * @param string $sort  [description]
	 *
	 * @param array  $array The array to search
	 *
	 * @return array        A new array that has only unique nested arrays.
	 * @uses  array_compare_by_key()
	 *
	 */
	public function array_remove_duplicate_by_key( $array, $key, $sort = '' ) {
		$unique_array      = array(); // The results will be loaded into this array.
		$unique_index_keys = array(); // The list of keys will be added here.

		if ( ! is_array( $array ) || empty( $array ) || empty( $key ) ) {
			return array();
		}

		foreach ( $array as $nested_array ) { // Iterate through your array.
			if ( in_array( $nested_array[ $key ], $unique_index_keys ) ) { // Check to see if this is a key that's already been used before.
				continue; // Skip duplicates.
			} else {
				$unique_index_keys[] = $nested_array[ $key ]; // If the key hasn't been used before, add it into the list of keys.
				$unique_array[]      = $nested_array; // Add the nested array into the unique_array.
			}
		}

		if ( $sort == 'uasort' ) { // Sort an array with a user-defined comparison function and maintain index association
			$this->array_compare_by_key = $key;
			uasort( $unique_array, array( $this, 'array_compare_by_key' ) );
		}

		return $unique_array;
	}

	/**
	 * Sorts an array alphabetically by key
	 *
	 * @since 1.3.7
	 *
	 * @param string $key
	 *
	 * @param array  $array
	 *
	 * @return array
	 * @uses  array_compare_by_key()
	 *
	 */
	public function array_uasort_by_key( $array, $key ) {
		$this->array_compare_by_key = $key;

		uasort( $array, array( $this, 'array_compare_by_key' ) );

		return $array;
	}

	/**
	 * Callback method for sorting an array by key
	 *
	 * @since 1.3.7
	 *
	 * @param array $b
	 *
	 * @param array $a
	 *
	 * @return int
	 *
	 */
	public function array_compare_by_key( $a, $b ) {
		return strcmp( $a[ $this->array_compare_by_key ], $b[ $this->array_compare_by_key ] );
	}

	/**
	 * Flattens array using a $wpdb query. Moves all [0] elements one level higher, so each value can be accessed by key.
	 * HHVM compatible.
	 *
	 * @since 1.3.9.6
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function flatten_get_meta_array_query( $data ) {
		$array = array();

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( WC_AM_FORMAT()->count( $value ) == 1 ) {
					if ( is_array( $value ) ) {
						$array[ $value[ 'meta_key' ] ] = $value[ 'meta_value' ];
					} else {
						$array[ $value[ 'meta_key' ] ] = $value[ 'meta_value' ];
					}
				} elseif ( WC_AM_FORMAT()->count( $value ) > 1 ) {
					$array[ $value[ 'meta_key' ] ] = $value[ 'meta_value' ];
				}
			}
		}

		return $array;
	}

	/**
	 * Gets the post_meta data from any table that usess the default meta table structure,
	 * and returns the data as a single-dimensional, or one-dimensional, array.
	 *
	 * @since 1.3.9.6
	 *
	 * @param string $type
	 * @param int    $id examples: 'commentmet', 'postmeta', 'termmeta', 'usermeta', 'woocommerce_order_itemmeta'
	 *
	 * @return array|bool
	 */
	public function get_meta_query_flattened( $type = '', $id ) {
		global $wpdb;

		if ( ! empty( $type ) ) {
			$items = $wpdb->get_results( "
				SELECT 		meta_key, meta_value
				FROM 		$wpdb->prefix$type
				WHERE 		post_id = $id
			", ARRAY_A );

			return ! empty( $items ) ? $this->flatten_get_meta_array_query( $items ) : false;
		}

		return false;
	}

	/**
	 * Flattens array using get_post_meta function. Moves all [0] elements one level higher, so each value can be accessed by key.
	 * HHVM compatible.
	 *
	 * @since 1.3.9.6
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function flatten_array( $data ) {
		$array = array();

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( WC_AM_FORMAT()->count( $value ) == 1 ) {
					if ( is_array( $value ) ) {
						$array[ $key ] = $value[ 0 ];
					} else {
						$array[ $key ] = $value;
					}
				} elseif ( WC_AM_FORMAT()->count( $value ) > 1 ) {
					$array[ $key ] = $value;
				}
			}
		}

		return $array;
	}

	/**
	 * Returns a flattended post_meta, or user_meta, data array or false.
	 *
	 * @since 1.3.9.6
	 *
	 * @param string  $type post|user
	 * @param integer $id
	 * @param string  $key
	 * @param boolean $single
	 *
	 * @return bool|array
	 */
	public function get_meta_flattened( $type = '', $id, $key = '', $single = false ) {
		if ( ! empty( $type ) ) {
			if ( $type == 'post' ) {
				$meta = get_post_meta( $id, $key, $single );
			}

			if ( $type == 'user' ) {
				$meta = get_user_meta( $id, $key, $single );
			}

			if ( ! empty( $meta ) ) {
				return $this->flatten_array( $meta );
			}
		}

		return false;
	}

	/**
	 * Inserts a new key/value after the key in the array.
	 *
	 * @since 2.1.2
	 *
	 * @param string $needle    The array key to insert the element after
	 * @param array  $haystack  An array to insert the element into
	 * @param string $new_key   The key to insert
	 * @param string $new_value A value to insert
	 *
	 * @return array
	 */
	public function array_insert_before( $needle, $haystack, $new_key, $new_value ) {
		if ( array_key_exists( $needle, $haystack ) ) {
			$new_array = array();

			foreach ( $haystack as $key => $value ) {
				if ( $key === $needle ) {
					$new_array[ $new_key ] = $new_value;
				}

				$new_array[ $key ] = $value;
			}

			return $new_array;
		}

		return $haystack;
	}

	/**
	 * Inserts a new key/value after the key in the array.
	 *
	 * @since 1.4.4
	 *
	 * @param string $needle    The array key to insert the element after
	 * @param array  $haystack  An array to insert the element into
	 * @param string $new_key   The key to insert
	 * @param string $new_value A value to insert
	 *
	 * @return array
	 */
	public function array_insert_after( $needle, $haystack, $new_key, $new_value ) {
		if ( array_key_exists( $needle, $haystack ) ) {
			$new_array = array();

			foreach ( $haystack as $key => $value ) {
				$new_array[ $key ] = $value;

				if ( $key === $needle ) {
					$new_array[ $new_key ] = $new_value;
				}
			}

			return $new_array;
		}

		return $haystack;
	}

	/**
	 * Flattens meta object data.
	 *
	 * @since 2.0
	 *
	 * @param object|array $data
	 *
	 * @return array
	 */
	public function flatten_meta_object( $data ) {
		$array = array();

		if ( ! empty( $data ) ) {
			foreach ( (array) $data as $key => $value ) {
				// Skip empty meta values.
				if ( ! empty( $value->value ) ) {
					$array[ $value->key ] = $value->value;
				}
			}
		}

		return $array;
	}

	/**
	 * Take an array of objects, remove the duplicates, and return array of objects.
	 *
	 * @since 2.0
	 *
	 * @param array $array An array that contains an ojbect, or an array of objects.
	 *
	 * @return array An array that contains an ojbect, or an array of objects, without the duplicates.
	 */
	public function array_unique_object( $array ) {
		$array = array_map( 'json_encode', (array) $array );
		$array = array_unique( $array );

		return array_map( 'json_decode', $array );
	}

} // end class