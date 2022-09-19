<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Utility Class.
 *
 * All miscellanous convenient functions wrapper.
 *
 * @category Utility
 * @package  WooCommerce Product Vendors/Utils
 * @version  2.0.0
 */
class WC_Product_Vendors_Utils {
	/**
	 * Empty private constructor to prevent instantiation
	 *
	 * @access private
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Conditional check if current user is a vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $user_id id of the user to check
	 * @return bool
	 */
	public static function is_vendor( $user_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

			$user_id = $current_user->ID;
		}

		// check if user is a shop vendor
		if ( self::is_manager_vendor( $user_id ) ||
			self::is_admin_vendor( $user_id ) ||
			self::is_pending_vendor( $user_id )
		) {

			return true;
		}

		return false;
	}

	/**
	 * Conditional check if current user is a pending vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $user_id id of the user to check
	 * @return bool
	 */
	public static function is_pending_vendor( $user_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

		} else {
			$current_user = new WP_User( $user_id );
		}

		if ( is_object( $current_user ) && in_array( 'wc_product_vendors_pending_vendor', $current_user->roles ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Conditional check if current user is an admin vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $user_id id of the user to check
	 * @return bool
	 */
	public static function is_admin_vendor( $user_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

		} else {
			$current_user = new WP_User( $user_id );
		}

		if ( is_object( $current_user ) && in_array( 'wc_product_vendors_admin_vendor', $current_user->roles ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Conditional check if current user is a manager vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $user_id id of the user to check
	 * @return bool
	 */
	public static function is_manager_vendor( $user_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

		} else {
			$current_user = new WP_User( $user_id );
		}

		if ( is_object( $current_user ) && in_array( 'wc_product_vendors_manager_vendor', $current_user->roles ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Sanitizes multilevel array
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $item
	 * @param mix $key
	 * @return bool
	 */
	public static function sanitize_multi_array( $item, $key ) {
		$item = sanitize_text_field( $item );

		return $item;
	}

	/**
	 * Sanitizes commission
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $commission
	 * @return string $commission
	 */
	public static function sanitize_commission( $commission ) {
		if ( '' === trim( $commission ) || is_null( trim( $commission ) ) ) {
			return '';
		}

		// strip all percentages and make positive whole number
		return abs( str_replace( '%', '', trim( $commission ) ) );
	}

	/**
	 * Gets the data from a specific vendor the passed in user is managing
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $vendor_data
	 */
	public static function get_vendor_data_from_user() {
		return self::get_vendor_data_by_id( self::get_logged_in_vendor() );
	}

	/**
	 * Get sold by info for the vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $post_id
	 * @return array mixed
	 */
	public static function get_sold_by_link( $post_id = null ) {
		if ( null === $post_id ) {
			return;
		}

		$name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		$link = get_permalink( wc_get_page_id( 'shop' ) );

		$term = wp_get_post_terms( $post_id, WC_PRODUCT_VENDORS_TAXONOMY );

		if ( ! empty( $term ) ) {
			$link = get_term_link( $term[0], WC_PRODUCT_VENDORS_TAXONOMY );

			$name = $term[0]->name;
		}

		return array( 'link' => apply_filters( 'wcpv_sold_by_link', $link, $post_id, $term ), 'name' => apply_filters( 'wcpv_sold_by_link_name', $name, $post_id, $term ) );
	}

	/**
	 * Gets the data from a specific vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $vendor_id
	 * @return array $vendor_data
	 */
	public static function get_vendor_data_by_id( $vendor_id ) {
		$vendor_data = get_term_meta( absint( $vendor_id ), 'vendor_data', true );

		$vendor_term = get_term_by( 'id', $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );

		if ( $vendor_data && $vendor_term ) {
			$vendor_data['term_id']          = $vendor_term->term_id;
			$vendor_data['name']             = $vendor_term->name;
			$vendor_data['slug']             = $vendor_term->slug;
			$vendor_data['term_group']       = $vendor_term->term_group;
			$vendor_data['term_taxonomy_id'] = $vendor_term->term_taxonomy_id;
			$vendor_data['taxonomy']         = $vendor_term->taxonomy;
			$vendor_data['description']      = $vendor_term->description;
			$vendor_data['parent']           = $vendor_term->parent;
			$vendor_data['count']            = $vendor_term->count;
		}

		return apply_filters( 'wcpv_get_vendor_data_by_id', $vendor_data, $vendor_id );
	}

	/**
	 * Gets all vendor data the passed in user is managing
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.9
	 * @param int $user_id
	 * @return array $vendor_data
	 */
	public static function get_all_vendor_data( $user_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

			$user_id = $current_user->ID;
		}

		/**
		 * TODO:WCY
		 *
		 * 这傻逼插件获取用户管理的供应商时获取所有数据再遍历的操作太骚了。。。所以，如果存在user_id，也就是说如果是获取某个用户的供应商的话
		 * 就直接返回这个用户的激活供应商（从usermeta里查）这样快N倍
		 */
		//$terms = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, array( 'hide_empty' => false ) );
		$terms = array( get_term( get_user_meta( $user_id, '_wcpv_active_vendor', true ) ) );
		$vendor_data = array();

		$vendors = array();

		if ( ! is_array( $terms ) ) {
			return $vendors;
		}

		// loop through to see which one has assigned passed in user
		/**
		 * TODO:WCY
		 *
		 * 增加错误检查
		 */
		if ( is_wp_error( $terms[0] ?? array() ) ) {
			return $vendors;
		}

		foreach ( $terms as $term ) {
			$vendor_data = get_term_meta( $term->term_id, 'vendor_data', true );

			if ( ! empty( $vendor_data['admins'] ) ) {
				if ( version_compare( WC_VERSION, '3.0.0', '>=' ) && is_array( $vendor_data['admins'] ) ) {
					$admin_ids = array_map( 'absint', $vendor_data['admins'] );
				} else {
					if ( is_array( $vendor_data['admins'] ) ) {
						$admin_ids = array_filter( array_map( 'absint', $vendor_data['admins'] ) );
					} else {
						$admin_ids = array_filter( array_map( 'absint', explode( ',', $vendor_data['admins'] ) ) );
					}
				}

				if ( in_array( $user_id, $admin_ids ) ) {
					$vendor_data['term_id']          = $term->term_id;
					$vendor_data['name']             = $term->name;
					$vendor_data['slug']             = $term->slug;
					$vendor_data['term_group']       = $term->term_group;
					$vendor_data['term_taxonomy_id'] = $term->term_taxonomy_id;
					$vendor_data['taxonomy']         = $term->taxonomy;
					$vendor_data['description']      = $term->description;
					$vendor_data['parent']           = $term->parent;
					$vendor_data['count']            = $term->count;

					$vendors[ $term->term_id ] = $vendor_data;
				}
			}
		}

		return $vendors;
	}

	/**
	 * Authenticates if user is assigned to a vendor and can manage it
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.9
	 * @param int $user_id
	 * @param string $vendor_id
	 * @return bool
	 */
	public static function auth_vendor_user( $user_id = null, $vendor_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

			$user_id = $current_user->ID;
		}

		// if param not passed get from user meta.
		if ( null === $vendor_id ) {
			$vendor_id = self::get_user_active_vendor();
		}

		// if term does not exist
		if ( 0 === self::is_valid_vendor( $vendor_id ) || null === self::is_valid_vendor( $vendor_id ) ) {
			return false;
		}

		if ( self::is_admin_vendor( $user_id ) || self::is_manager_vendor( $user_id ) ) {
			$term = get_term_by( 'id', sanitize_text_field( $vendor_id ), WC_PRODUCT_VENDORS_TAXONOMY );

			if ( null === $term || false === $term ) {
				return false;
			}

			$vendor_data = get_term_meta( $term->term_id, 'vendor_data', true );

			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) && is_array( $vendor_data['admins'] ) ) {
				$admin_ids = array_map( 'absint', $vendor_data['admins'] );
			} else {
				if ( is_array( $vendor_data['admins'] ) ) {
					$admin_ids = array_filter( array_map( 'absint', $vendor_data['admins'] ) );
				} else {
					$admin_ids = array_filter( array_map( 'absint', explode( ',', $vendor_data['admins'] ) ) );
				}
			}

			// if user is listed as one of the admins
			if ( ! empty( $vendor_data['admins'] ) && in_array( $user_id, $admin_ids ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if vendor is valid
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.9
	 * @param int|string $vendor
	 * @return mixed
	 */
	public static function is_valid_vendor( $vendor = null ) {
		if ( 0 === intval( $vendor ) ) {
			return term_exists( $vendor, WC_PRODUCT_VENDORS_TAXONOMY );
		} else {
			return term_exists( intval( $vendor ), WC_PRODUCT_VENDORS_TAXONOMY );
		}
	}

	/**
	 * Checks if user can manage product
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $user_id
	 * @param int $product_id
	 * @return bool
	 */
	public static function can_user_manage_product( $user_id = null, $product_id = null ) {
		// if param not passed use current user
		if ( null === $user_id ) {
			$current_user = wp_get_current_user();

			$user_id = $current_user->ID;
		}

		// if param not passed use current post
		if ( null === $product_id ) {
			global $post;

			$product_id = is_object( $post ) ? $post->ID : null;
		}

		$product_terms = wp_get_object_terms( $product_id, WC_PRODUCT_VENDORS_TAXONOMY, array( 'fields' => 'ids' ) );

		if ( null === $product_terms || empty( $product_terms ) ) {
			return false;
		}

		if ( self::get_logged_in_vendor() === $product_terms[0] ) {
			return true;
		}

		return false;
	}

	/**
	 * Sets the active vendor a user is managing.
	 *
	 * @since 2.0.38
	 * @version 2.0.38
	 * @param int $vendor_id
	 * @return int|bool
	 */
	public static function set_user_active_vendor( $vendor_id = '' ) {
		return update_user_meta( get_current_user_id(), '_wcpv_active_vendor', absint( $vendor_id ) );
	}

	/**
	 * Gets the active vendor a user is managing.
	 *
	 * @since 2.0.38
	 * @param int $user_id Optional user id.
	 *
	 * @return string|bool
	 */
	public static function get_user_active_vendor( $user_id = null ) {
		$vendor_id = get_user_meta( $user_id ? $user_id : get_current_user_id(), '_wcpv_active_vendor', true );
		if ( ! empty( $vendor_id ) ) {
			return $vendor_id;
		}

		return self::get_user_default_vendor( $user_id );
	}

	/**
	 * Gets the users default active vendor.
	 *
	 * @since 2.0.38
	 * @param int $user_id Optional user id.
	 *
	 * @return string|bool
	 */
	public static function get_user_default_vendor( $user_id = null ) {
		$vendor_data = self::get_all_vendor_data( $user_id ? $user_id : get_current_user_id() );

		// Default vendor id.
		if ( ! empty( $vendor_data ) ) {
			return key( $vendor_data );
		}

		return false;
	}

	/**
	 * Get the vendor slug/id of the current user.
	 *
	 * @since 2.0.0
	 * @version 2.0.38
	 * @param string $type the type to return
	 * @return mixed
	 */
	public static function get_logged_in_vendor( $type = 'id' ) {
		$vendor_id = self::get_user_active_vendor();

		// if active vendor is set and user can manage this vendor
		if ( self::auth_vendor_user() ) {
			if ( 'slug' === $type ) {
				$term = get_term_by( 'id', $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );

				if ( is_object( $term ) ) {
					return $term->slug;
				}
			} elseif ( 'id' === $type ) {
				return intval( $vendor_id );

			} elseif ( 'name' === $type ) {
				$term = get_term_by( 'id', $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );

				if ( is_object( $term ) ) {
					return $term->name;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if the page is a edit product page
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public static function is_edit_product_page() {
		global $pagenow, $typenow;

		if ( 'product' !== $typenow && 'post.php' !== $pagenow && empty( $_GET['action'] ) && 'edit' !== $_GET['action'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Get all products that belong to a vendor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $term_id
	 * @return array $ids product ids
	 */
	public static function get_vendor_product_ids( $term_id = '' ) {
		$ids = array();

		if ( empty( $term_id ) ) {
			$term_id = self::get_logged_in_vendor();
		}

		if ( ! empty( $term_id ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'fields'    => 'ids',
				'tax_query' => array(
					array(
						'taxonomy' => WC_PRODUCT_VENDORS_TAXONOMY,
						'field'    => 'id',
						'terms'    => $term_id,
					),
				),
				// This call is low-level, so we need to suppress all filters.
				// The methods that use this will filter additionally as needed.
				// One example is WC_Product_Vendors_Bookings::filter_products_booking_list on pre_get_posts.
				// This breaks filtering on the WP_List_Table.
				'suppress_filters' => true,
			);

			$query = new WP_Query( $args );

			wp_reset_postdata();

			$ids = $query->posts;
		}

		return $ids;
	}

	/**
	 * Get vendor rating based on average of product ratings
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $term_id
	 * @return string $avg_rating
	 */
	public static function get_vendor_rating( $term_id ) {
		$product_ids = self::get_vendor_product_ids( $term_id );

		$avg_rating = 0;

		$product_count = 0;

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			// check if product has rating
			if ( $product->get_rating_count() > 0 ) {
				$avg_rating += $product->get_average_rating();

				$product_count++;
			}
		}

		if ( $product_count > 0 ) {
			$avg_rating = number_format( $avg_rating / $product_count, 2 );
		}

		return $avg_rating;
	}

	/**
	 * Get vendor rating html
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $term_id
	 * @return mixed
	 */


    /**
     * TODO:WCY
     *
     * 修改供应商结构
     *
     */

	public static function get_vendor_rating_html( $term_id ) {
		$rating = self::get_vendor_rating( $term_id );

		$rating_html = '<section class="supplier"><small>' . esc_html__( 'Average Vendor Rating', 'woocommerce-product-vendors' ) . '</small>';

		$rating_html .= '<div class="wcpv-star-rating star-rating" title="' . sprintf( esc_attr__( 'Rated %s out of 5', 'woocommerce-product-vendors' ), $rating ) . '">';

		$rating_html .= '<span style="width:' . ( ( $rating / 5 ) * 100 ) . '%"><strong class="rating">' . $rating . '</strong> ' . esc_html__( 'out of 5', 'woocommerce-product-vendors' ) . '</span>';

		$rating_html .= '</div></section>';

		return apply_filters( 'wcpv_vendor_get_rating_html', $rating_html, $rating );
	}

	/**
	 * Converts a GMT date into the correct format for the blog.
	 *
	 * Requires and returns a date in the Y-m-d H:i:s format. If there is a
	 * timezone_string available, the returned date is in that timezone, otherwise
	 * it simply adds the value of gmt_offset. Return format can be overridden
	 * using the $format parameter
	 *
	 * @since 2.0.16
	 * @version 2.0.16
	 * @param string $string The date to be converted.
	 * @param string $format The format string for the returned date (default is Y-m-d H:i:s)
	 * @return string Formatted date relative to the timezone / GMT offset.
	 */
	public static function get_date_from_gmt( $string, $format, $timezone_string ) {
		$tz = $timezone_string;

		if ( empty( $format ) ) {
			$format = 'Y-m-d H:i:s';
		}

		if ( empty( $timezone_string ) ) {
			$tz = get_option( 'timezone_string' );
		}

		if ( $tz && ( ! preg_match( '/UTC-/', $tz ) && ! preg_match( '/UTC+/', $tz ) ) ) {
			$datetime = date_create( $string, new DateTimeZone( 'UTC' ) );

			if ( ! $datetime ) {
				return date( $format, 0 );
			}

			$datetime->setTimezone( new DateTimeZone( $tz ) );
			$string_localtime = $datetime->format( $format );
		} else {
			if ( ! preg_match( '#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches ) ) {
				return date( $format, 0 );
			}

			$string_time = gmmktime( $matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1] );
			$string_localtime = gmdate( $format, $string_time + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		}

		return $string_localtime;
	}

	/**
	 * Gets the default timezone string from blog setting
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.16
	 * @return string $tzstring
	 */
	public static function get_default_timezone_string() {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		if ( 0 == $current_offset ) {
			$tzstring = 'UTC+0';
		} elseif ( $current_offset < 0 ) {
			$tzstring = 'UTC' . $current_offset;
		} else {
			$tzstring = 'UTC+' . $current_offset;
		}

		return $tzstring;
	}

	/**
	 * Formats the order and payout dates to be consistent
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.16
	 * @param string $sql_date
	 * @return string $date
	 */
	public static function format_date( $sql_date, $timezone = '' ) {
		$date = '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' !== $sql_date ) {
			$date = self::get_date_from_gmt( $sql_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timezone );
		}

		return apply_filters( 'wcpv_date_format', $date, $sql_date );
	}

	/**
	 * Gets the vendor id from product
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $product_id
	 * @return int $vendor_id
	 */
	public static function get_vendor_id_from_product( $product_id = null ) {
		if ( null === $product_id ) {
			return null;
		}

		$term = wp_get_object_terms( $product_id, WC_PRODUCT_VENDORS_TAXONOMY, array( 'fields' => 'ids' ) );

		if ( is_wp_error( $term ) || empty( $term ) ) {
			return null;
		}

		return $term[0];
	}

	/**
	 * Checks if the given product is a vendor product
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $product_id
	 * @return object $term
	 */
	public static function is_vendor_product( $product_id = null ) {
		if ( null === $product_id ) {
			return false;
		}

		$term = wp_get_object_terms( $product_id, WC_PRODUCT_VENDORS_TAXONOMY, array( 'fields' => 'all' ) );

		if ( ! empty( $term ) ) {
			return $term;
		}

		return false;
	}

	/**
	 * Gets the list of vendors
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array objects $vendors
	 */
	public static function get_vendors() {
		return get_terms( WC_PRODUCT_VENDORS_TAXONOMY );
	}

	/**
	 * Gets product settings related to vendors.
	 *
	 * @param WC_Product $product Product Object.
	 * @param array|null $vendor_data Vendor Data.
	 *
	 * @return array
	 */
	public static function get_product_vendor_settings( WC_Product $product, $vendor_data = null ) {

		// $pass_shipping_tax is a legacy setting, we fetch it to set the values of $pass_shipping and $pass_tax if ! empty
		$pass_shipping_tax = $product->get_meta( '_wcpv_product_default_pass_shipping_tax', true, 'edit' );
		$pass_shipping     = $product->get_meta( '_wcpv_product_pass_shipping', true, 'edit' );
		$taxes         = $product->get_meta( '_wcpv_product_taxes', true, 'edit' );

		if ( empty( $pass_shipping ) ) {
			// Default to vendor setting then legacy setting then to 'no'.
			$pass_shipping = ! empty( $vendor_data['pass_shipping'] ) ? $vendor_data['pass_shipping'] : ( empty( $pass_shipping_tax ) ? 'no' : $pass_shipping_tax );
		}

		if ( empty( $taxes ) ) {
			// Default to vendor setting then legacy setting then to 'keep-tax'.
			$taxes = ! empty( $vendor_data['taxes'] ) ? $vendor_data['taxes'] : ( ! empty( $pass_shipping_tax ) && 'yes' === $pass_shipping_tax ? 'pass-tax' : 'keep-tax' );
		}

		return array(
			'pass_shipping' => $pass_shipping,
			'taxes' => $taxes,
		);
	}

	/**
	 * Gets the commission for a product
	 * Search order is: product variation level -> product parent level -> vendor level -> general vendors level
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $product_id
	 * @param array $vendor_data
	 * @return array mixed
	 */
	public static function get_product_commission( $product_id, $vendor_data ) {
		$product = wc_get_product( $product_id );

		// check if product is a variation
		if ( 'variation' === $product->get_type() || $product->is_type( 'variable' ) ) {
			// look for variation commission first
			$commission = get_post_meta( $product_id, '_wcpv_product_commission', true );

			if ( ! empty( $commission ) || '0' == $commission ) {
				return array( 'commission' => $commission, 'type' => $vendor_data['commission_type'] );

				// try to get the commission from the parent product
			} else {
				$parent_id = wp_get_post_parent_id( $product_id );

				$commission = get_post_meta( $parent_id, '_wcpv_product_commission', true );

				if ( ! empty( $commission ) || '0' == $commission ) {
					return array( 'commission' => $commission, 'type' => $vendor_data['commission_type'] );
				}
			}
		} else {
			$commission = get_post_meta( $product_id, '_wcpv_product_commission', true );

			if ( ! empty( $commission ) || '0' == $commission ) {
				return array( 'commission' => $commission, 'type' => $vendor_data['commission_type'] );
			}
		}

		// if no commission is set in variation or parent product level
		// check commission from vendor level
		if ( isset( $vendor_data['commission'] ) && is_numeric( $vendor_data['commission'] ) ) {
			return array( 'commission' => $vendor_data['commission'], 'type' => $vendor_data['commission_type'] );
		}

		// if no commission is set in vendor level check store default commission
		$commission      = get_option( 'wcpv_vendor_settings_default_commission' );
		$commission_type = get_option( 'wcpv_vendor_settings_default_commission_type' );

		if ( ! empty( $commission ) || '0' == $commission ) {
			return array( 'commission' => $commission, 'type' => $commission_type );
		}

		// else return no commission
		return 0;
	}

	/**
	 * Gets the list of vendor data from an order
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $order
	 * @return array $vendor_data
	 */
	public static function get_vendors_from_order( $order = null ) {
		global $wpdb;

		if ( null === $order ) {
			return null;
		}

		$vendor_data = array();

		$items = $order->get_items( 'line_item' );

		if ( ! empty( $items ) ) {

			// get all product ids
			foreach ( $items as $item_id => $item ) {
				$sql = 'SELECT `meta_value`';
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_itemmeta";
				$sql .= ' WHERE `order_item_id` = %d';
				$sql .= ' AND `meta_key` = %s';

				// get the product id of the order item
				$product_id = $wpdb->get_var( $wpdb->prepare( $sql, $item_id, '_product_id' ) );

				// get vendor id from product id
				$vendor_id = self::get_vendor_id_from_product( $product_id );

				// get vendor data
				$vendor_data[ $vendor_id ] = self::get_vendor_data_by_id( $vendor_id );
			}
		}

		return apply_filters( 'wcpv_get_vendors_from_order', $vendor_data, $order );
	}

	/**
	 * Converts array to string comma delimited
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $query
	 * @return array $query
	 */
	public static function convert2string( $query ) {
		$query = array_map( function( $entry ) {
			return '"' . str_replace( '"', '""', $entry ) . '"';
		}, $query );

		return implode( ',', $query );
	}

	/**
	 * Unserializes the variation attributes
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $query
	 * @return array $query
	 */
	public static function unserialize_attributes( $query ) {
		if ( ! empty( $query['variation_attributes'] ) ) {
			$attributes = maybe_unserialize( $query['variation_attributes'] );

			$attr_names = '';

			foreach ( $attributes as $attr => $value ) {
				$attr_names .= $attr . ':' . $value . '  ';
			}

			$query['variation_attributes'] = $attr_names;
		}

		return $query;
	}

	/**
	 * Checks whether the commission table exists
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public static function commission_table_exists() {
		global $wpdb;

		if ( WC_PRODUCT_VENDORS_COMMISSION_TABLE !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', WC_PRODUCT_VENDORS_COMMISSION_TABLE ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the payout schedule frequency
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return string $frequency
	 */
	public static function payout_schedule_frequency() {
		$frequency = get_option( 'wcpv_vendor_settings_payout_schedule' );

		return $frequency;
	}

	/**
	 * Gets per product shipping matching rule
	 *
	 * @param mixed $product_id
	 * @param mixed $package
	 * @return false|null
	 */
	public static function get_pp_shipping_matching_rule( $product_id, $package, $standalone = true ) {
		global $wpdb;

		$product_id = apply_filters( 'wcpv_per_product_shipping_get_matching_rule_product_id', $product_id );

		$country  = $package['destination']['country'];
		$state    = $package['destination']['state'];
		$postcode = $package['destination']['postcode'];

		// Define valid postcodes
		$valid_postcodes = array( '', $postcode );

		// Work out possible valid wildcard postcodes
		$postcode_length   = strlen( $postcode );
		$wildcard_postcode = $postcode;

		for ( $i = 0; $i < $postcode_length; $i ++ ) {
			$wildcard_postcode = substr( $wildcard_postcode, 0, -1 );
			$valid_postcodes[] = $wildcard_postcode . '*';
		}

		// Rules array
		$rules = array();

		// Get rules matching product, country and state
	    $matching_rule = $wpdb->get_row(
	    	$wpdb->prepare(
	    		"
	    		SELECT * FROM " . WC_PRODUCT_VENDORS_PER_PRODUCT_SHIPPING_TABLE . "
	    		WHERE product_id = %d
	    		AND rule_country IN ( '', %s )
	    		AND rule_state IN ( '', %s )
	    		AND rule_postcode IN ( '" . implode( "','", $valid_postcodes ) . "' )
	    		ORDER BY rule_order
	    		LIMIT 1
	    		" , $product_id, strtoupper( $country ), strtoupper( $state )
	    	)
	    );

	    return $matching_rule;
	}

	/**
	 * Updates the user/customer meta to contain related vendors
	 * This comes from either vendor created user or a customer of the vendor
	 *
	 * @access public
	 * @since 2.1.0
	 * @version 2.1.0
	 * @param int $user_id
	 * @param int $vendor_id
	 * @return bool
	 */
	public static function update_user_related_vendors( $user_id = null, $vendor_id = null ) {
		if ( null === $user_id || null === $vendor_id ) {
			return false;
		}

		$vendors = get_user_meta( $user_id, '_wcpv_customer_of', true ); // array

		// if no vendors have been associated yet
		if ( empty( $vendors ) ) {
			$vendors = array();
		}

		// if vendor is not associated, add them
		if ( ! in_array( $vendor_id, $vendors ) ) {
			$vendors[] = absint( $vendor_id );
		}

		$vendors = array_unique( $vendors );

		update_user_meta( $user_id, '_wcpv_customer_of', $vendors );

		return true;
	}

	/**
	 * Get fulfillment status of an order item
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $order_item_id
	 * @return string $status
	 */
	public static function get_fulfillment_status( $order_item_id ) {
		global $wpdb;

		if ( empty( $order_item_id ) ) {
			return;
		}

		$sql = "SELECT `meta_value` FROM {$wpdb->prefix}woocommerce_order_itemmeta";
		$sql .= ' WHERE `order_item_id` = %d';
		$sql .= ' AND `meta_key` = %s';

		$status = $wpdb->get_var( $wpdb->prepare( $sql, $order_item_id, '_fulfillment_status' ) );

		return $status;
	}

	/**
	 * Set fulfillment status of an order item
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $order_item_id
	 * @param string $status
	 * @return bool
	 */
	public static function set_fulfillment_status( $order_item_id, $status ) {
		global $wpdb;

		$sql = "UPDATE {$wpdb->prefix}woocommerce_order_itemmeta";
		$sql .= ' SET `meta_value` = %s';
		$sql .= ' WHERE `order_item_id` = %d AND `meta_key` = %s';

		$status = $wpdb->get_var( $wpdb->prepare( $sql, $status, $order_item_id, '_fulfillment_status' ) );

		return true;
	}

	/**
	 * Trigger fulfillment status email
	 *
	 * @access public
	 * @since 2.0.16
	 * @version 2.0.16
	 * @param array $vendor_data
	 * @param string $status
	 * @param int $order_item_id
	 * @return bool
	 */
	public static function send_fulfill_status_email( $vendor_data = null, $status = '', $order_item_id = '' ) {
		$emails = WC()->mailer()->get_emails();

		if ( ! empty( $emails ) ) {
			$emails['WC_Product_Vendors_Order_Fulfill_Status_To_Admin']->trigger( $vendor_data, $status, $order_item_id );
		}

		return true;
	}
	/**
	 * Maybe set order to complete or back to processing when product vendor is fulfilled or unfulfilled.
	 *
	 * @since 2.1.30
	 * @param object $order The order.
	 * @param string $fulfillment_status 'fulfilled' or 'unfulfilled'.
	 * @return void
	 */
	public static function maybe_update_order( $order, $fulfillment_status ) {
		if ( ! is_a( $order, 'WC_Order' ) || empty( $fulfillment_status ) ) {
			return;
		}

		// Maybe update orders only if it is completed and product was unfulfilled or if it is processing and product was fulfilled.
		$order_status = $order->get_status();

		if ( 'completed' === $order_status && 'unfulfilled' === $fulfillment_status ) {
			// Update order to processing, trigger order events.
			$order->update_status( 'processing' );

		} elseif ( 'processing' === $order_status && 'fulfilled' === $fulfillment_status ) {
			$all_products_have_fulfillment_status = self::order_has_fulfillment_status( $order, $fulfillment_status );
			if ( $all_products_have_fulfillment_status ) {
				// Update order to complete, trigger order events.
				$order->update_status( 'completed' );
			}
		}
	}

	/**
	 * Returns true if all products in order have the specified fulfillment status.
	 * All products need to be product vendors.
	 *
	 * @since 2.1.30
	 * @param object $order The order.
	 * @param string $fulfillment_status 'fulfilled' or 'unfulfilled'.
	 * @return bool  True if all products are vendors and have the specified fulfillment status.
	 */
	public static function order_has_fulfillment_status( $order, $fulfillment_status ) {
		$has_fulfillment_status = false;
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		$items = $order->get_items( 'line_item' );
		foreach ( $items as $order_item_id => $item ) {
			$product = wc_get_product( $item['product_id'] );

			// Only check products.
			if ( ! is_object( $product ) ) {
				continue;
			}

			// If there is a product that is not a vendor product, return false.
			$vendor_id = self::get_vendor_id_from_product( $product->get_id() );
			if ( ! $vendor_id ) {
				return false;
			}
			$has_fulfillment_status = true;

			// If fulfillment status is different, return false.
			$product_fulfillment_status = self::get_fulfillment_status( $order_item_id );
			if ( $product_fulfillment_status !== $fulfillment_status ) {
				return false;
			}
		}

		return $has_fulfillment_status;
	}

	/**
	 * Gets an order by the order item id
	 *
	 * @access public
	 * @since 2.0.16
	 * @version 2.0.16
	 * @param int $order_item_id
	 * @return bool
	 */
	public static function get_order_by_order_item_id( $order_item_id = null ) {
		$order = $order_item_id;

		global $wpdb;

		$sql = "SELECT `order_id` FROM {$wpdb->prefix}woocommerce_order_items";
		$sql .= ' WHERE `order_item_id` = %d';

		$order_id = $wpdb->get_var( $wpdb->prepare( $sql, $order_item_id ) );

		if ( $order_id ) {
			return wc_get_order( $order_id );
		}

		return $order;
	}

	/**
	 * Gets the order item name by order item id
	 *
	 * @access public
	 * @since 2.0.16
	 * @version 2.0.16
	 * @param int $order_item_id
	 * @return string $order_item_name
	 */
	public static function get_order_item_name( $order_item_id = null ) {
		if ( null === $order_item_id  ) {
			return '';
		}

		global $wpdb;

		$sql = "SELECT `order_item_name` FROM {$wpdb->prefix}woocommerce_order_items";
		$sql .= ' WHERE `order_item_id` = %d';

		$order_item_name = $wpdb->get_var( $wpdb->prepare( $sql, $order_item_id ) );

		return $order_item_name;
	}

	/**
	 * Force save an order item's meta data to alleviate aggressive object caching.
	 *
	 * @since 2.1.33
	 * @param  int $order_item_id
	 * @return void
	 */
	public static function update_order_item_meta( $order_item_id ) {
		// Force save the order item's meta data which will clear out any existing object cache.
		$order_item = new WC_Order_Item_Product( $order_item_id );
		$order_item->save_meta_data();
	}

	/**
	 * Set new pending vendor to list to later be
	 * counted to show as a bubble count on menu.
	 *
	 * @since 2.0.31
	 * @version 2.0.31
	 * @param int $user_id
	 * @return void
	 */
	public static function set_new_pending_vendor( $user_id = null ) {
		if ( null === $user_id ) {
			return;
		}

		$pending_vendor_list = get_transient( 'wcpv_new_pending_vendor_list' );

		if ( false !== $pending_vendor_list ) {
			$pending_vendor_list[] = absint( $user_id );
		} else {
			$pending_vendor_list = array( absint( $user_id ) );
		}

		set_transient( 'wcpv_new_pending_vendor_list', array_unique( $pending_vendor_list ), WEEK_IN_SECONDS );
	}

	/**
	 * Deletes new pending vendor from list.
	 *
	 * @since 2.0.31
	 * @version 2.0.31
	 * @param int $user_id
	 * @return void
	 */
	public static function delete_new_pending_vendor( $user_id = null ) {
		if ( null === $user_id ) {
			return;
		}

		$pending_vendor_list = get_transient( 'wcpv_new_pending_vendor_list' );

		if ( false !== $pending_vendor_list && is_array( $pending_vendor_list ) ) {
			if ( false !== ( $key = array_search( $user_id, $pending_vendor_list ) ) ) {
			    unset( $pending_vendor_list[ $key ] );
			}

			set_transient( 'wcpv_new_pending_vendor_list', array_unique( $pending_vendor_list ), WEEK_IN_SECONDS );
		}
	}

	/**
	 * Clears all reports transients
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public static function clear_reports_transients() {
		global $wpdb;

		/**
		 * TODO:WCY
		 *
		 * 禁用每次保存文章时的清理瞬态的操作，这家伙太卡了
		 */
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wcpv_reports%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wcpv_unfulfilled_products%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%book_dr%'" );

		self::clear_low_stock_transient();
		self::clear_out_of_stock_transient();

		return true;
	}

	/**
	 * Clear low stock transient.
	 *
	 * @since 2.1.15
	 */
	public static function clear_low_stock_transient() {
		delete_transient( 'wcpv_reports_wg_lowstock_' . self::get_logged_in_vendor() );
	}

	/**
	 * Clear out of stock transient.
	 *
	 * @since 2.1.15
	 */
	public static function clear_out_of_stock_transient() {
		delete_transient( 'wcpv_reports_wg_nostock_' . self::get_logged_in_vendor() );
	}

	/**
	 * Deletes PayPal webhook id.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public static function delete_paypal_webhook_id() {
		$webhook_id = get_option( 'wcpv_webhook_id', '' );
		delete_option( 'wcpv_webhook_id' );

		if ( ! empty( $webhook_id ) ) {
			$webhook = new WC_Product_Vendors_Webhook_Handler();
			$webhook->delete_webhook( $webhook_id );
		}
	}

	/**
	 * Checks if bookings is enabled for this vendor
	 *
	 * @since 2.0.0
	 * @param int $user_id Optional user id.
	 *
	 * @return bool
	 */
	public static function is_bookings_enabled( $user_id = null ) {
		$vendor_data = get_term_meta( intval( self::get_user_active_vendor( $user_id ) ), 'vendor_data', true );

		if ( ! empty( $vendor_data['enable_bookings'] ) && 'yes' === $vendor_data['enable_bookings'] && class_exists( 'WC_Bookings' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Formats the order status for localization
	 *
	 * @since 2.0.21
	 * @version 2.0.21
	 * @param string $order_status
	 */
	public static function format_order_status( $order_status = '' ) {
		switch ( $order_status ) {
			case 'pending':
				$order_status = __( 'Pending', 'woocommerce-product-vendors' );
				break;
			case 'processing':
				$order_status = __( 'Processing', 'woocommerce-product-vendors' );
				break;
			case 'on-hold':
				$order_status = __( 'On-hold', 'woocommerce-product-vendors' );
				break;
			case 'completed':
				$order_status = __( 'Completed', 'woocommerce-product-vendors' );
				break;
			case 'cancelled':
				$order_status = __( 'Cancelled', 'woocommerce-product-vendors' );
				break;
			case 'refunded':
				$order_status = __( 'Refunded', 'woocommerce-product-vendors' );
				break;
			case 'failed':
				$order_status = __( 'Failed', 'woocommerce-product-vendors' );
				break;
			case 'pre-ordered':
				$order_status = __( 'Pre-ordered', 'woocommerce-product-vendors' );
				break;
			case 'trash':
				$order_status = __( 'Trash', 'woocommerce-product-vendors' );
				break;
			default:
				$order_status = __( 'Unknown', 'woocommerce-product-vendors' );
				break;
		}

		return $order_status;
	}

	/**
	 * Return the URL that should be used for Product Vendors PayPal Webhook Notifications
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @return string
	 */
	public static function get_paypal_webhook_notification_url() {
		return add_query_arg( 'wc-api', 'wc_product_vendors_paypal', trailingslashit( get_home_url() ) );
	}

	/**
	 * Check if Per-Product Shipping method is globally enabled.
	 * @since 2.1.36
	 * @return bool
	 */
	public static function is_wcpv_per_product_shipping_enabled() {
		$shipping_methods = WC()->shipping->get_shipping_methods();
		if ( array_key_exists( 'wcpv_per_product', $shipping_methods ) ) {
			$wcpv_shipping = $shipping_methods['wcpv_per_product'];
			return $wcpv_shipping->is_enabled();
		}
		return false;
	}
}
