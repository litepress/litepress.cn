<?php

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Core Functions
 *
 * @package     WooCommerce API Manager/includes/Core Functions
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @version     2.0
 */

/**
 * Class functions
 */
// Functions available only for Admin
if ( WCAM()->is_request( 'admin' ) ) {
	/**
	 * Returns the WC_AM_Admin_Notices class object
	 *
	 * @since 2.0
	 *
	 * @return \WC_AM_Admin_Notices
	 */
	function WC_AM_ADMIN_NOTICES() {
		return WC_AM_Admin_Notices::instance();
	}

	WC_AM_ADMIN_NOTICES();

	/**
	 * Returns the WC_AM_Admin_System_Status class object
	 *
	 * @since 2.1
	 *
	 * @return \WC_AM_Admin_System_Status
	 */
	function WC_AM_ADMIN_SYSTEM_STATUS() {
		return WC_AM_Admin_System_Status::instance();
	}

	WC_AM_ADMIN_SYSTEM_STATUS();

	/**
	 * Returns the WC_AM_Install class object
	 *
	 * @since 1.5
	 *
	 * @return \WC_AM_Install
	 */
	function WC_AM_INSTALL() {
		return WC_AM_Install::instance();
	}

	WC_AM_INSTALL();

	/**
	 * Returns the WC_AM_Order_Admin class object
	 *
	 * @since 1.5
	 *
	 * @return \WC_AM_Order_Admin
	 */
	function WC_AM_ORDER_ADMIN() {
		return WC_AM_Order_Admin::instance();
	}

	WC_AM_ORDER_ADMIN();

	/**
	 * Returns the WC_AM_Product_Admin class object
	 *
	 * @since 1.5
	 *
	 * @return \WC_AM_Product_Admin
	 */
	function WC_AM_PRODUCT_ADMIN() {
		return WC_AM_Product_Admin::instance();
	}

	WC_AM_PRODUCT_ADMIN();

	/**
	 * Returns the WC_AM_Settings_Admin class object
	 *
	 * @since 1.5
	 *
	 * @return \WC_AM_Settings_Admin
	 */
	function WC_AM_SETTINGS_ADMIN() {
		return WC_AM_Settings_Admin::instance();
	}

	WC_AM_SETTINGS_ADMIN();
}

/**
 * Returns the WC_AM_API_Requests class object.
 *
 * @since 2.0
 *
 * @param $request array
 *
 * @return \WC_AM_API_Requests
 */
function WC_AM_API_REQUESTS( $request ) {
	return WC_AM_API_Requests::instance( $request );
}

/**
 * Returns the WC_AM_API_Activation_Data_Store class object
 *
 * @since 2.0
 *
 * @return \WC_AM_API_Activation_Data_Store
 */
function WC_AM_API_ACTIVATION_DATA_STORE() {
	return WC_AM_API_Activation_Data_Store::instance();
}

WC_AM_API_ACTIVATION_DATA_STORE();

/**
 * Returns the WC_AM_API_Resource_Data_Store class object
 *
 * @since 2.0
 *
 * @return \WC_AM_API_Resource_Data_Store
 */
function WC_AM_API_RESOURCE_DATA_STORE() {
	return WC_AM_API_Resource_Data_Store::instance();
}

/**
 * Returns the WC_AM_Array class object
 *
 * @since 1.5
 *
 * @return \WC_AM_Array
 */
function WC_AM_ARRAY() {
	return WC_AM_Array::instance();
}

/**
 * Returns the WC_AM_Associated_API_Key_Data_Store class object
 *
 * @since 2.0
 *
 * @return \WC_AM_Associated_API_Key_Data_Store
 */
function WC_AM_ASSOCIATED_API_KEY_DATA_STORE() {
	return WC_AM_Associated_API_Key_Data_Store::instance();
}

/**
 * Returns the WC_AM_Smart_Cache class object
 *
 * @since 2.0.12
 *
 * @return \WC_AM_Smart_Cache
 */
function WC_AM_SMART_CACHE() {
	return WC_AM_Smart_Cache::instance();
}

WC_AM_SMART_CACHE();

/**
 * Returns the WC_AM_Download_Handler class object
 *
 * @since 2.0
 *
 * @return \WC_AM_Download_Handler
 */
function WC_AM_DOWNLOAD_HANDLER() {
	return WC_AM_Download_Handler::instance();
}

WC_AM_DOWNLOAD_HANDLER();

/**
 * Returns the WC_AM_Encryption class object
 *
 * @since 1.5
 *
 * @return \WC_AM_Encryption
 */
function WC_AM_ENCRYPTION() {
	return WC_AM_Encryption::instance();
}

/**
 * Returns the WC_AM_Encryption class object
 *
 * @since 2.0
 *
 * @return \WC_AM_Format
 */
function WC_AM_FORMAT() {
	return WC_AM_Format::instance();
}

/**
 * Returns the WC_AM_Hash class object
 *
 * @since 1.5
 *
 * @return \WC_AM_Hash
 */
function WC_AM_HASH() {
	return WC_AM_Hash::instance();
}

/**
 * Returns the WC_AM_Log class object
 *
 * @since 2.0
 *
 * @return \WC_AM_Log
 */
function WC_AM_Log() {
	return WC_AM_Log::instance();
}

/**
 * Returns the WC_AM_Order class object.
 *
 * @since 2.0
 *
 * @return \WC_AM_Order
 */
function WC_AM_ORDER() {
	return WC_AM_Order::instance();
}

WC_AM_ORDER();

/**
 * Returns the WC_AM_Order_Data_Store class object.
 *
 * @since 2.0
 *
 * @return \WC_AM_Order_Data_Store
 */
function WC_AM_ORDER_DATA_STORE() {
	return WC_AM_Order_Data_Store::instance();
}

WC_AM_ORDER_DATA_STORE();

/**
 * Returns the WC_AM_Product_Data_Store class object.
 *
 * @since 2.0
 *
 * @return \WC_AM_Product_Data_Store
 */
function WC_AM_PRODUCT_DATA_STORE() {
	return WC_AM_Product_Data_Store::instance();
}

WC_AM_PRODUCT_DATA_STORE();

/**
 * Returns the WC_AM_Subscription class object
 *
 * @since 1.5
 *
 * @return \WC_AM_Subscription
 */
function WC_AM_SUBSCRIPTION() {
	return WC_AM_Subscription::instance();
}

WC_AM_SUBSCRIPTION();

/**
 * Returns the WC_AM_URL class object.
 *
 * @since 2.0
 *
 * @return \WC_AM_URL
 */
function WC_AM_URL() {
	return WC_AM_URL::instance();
}

/**
 * Returns the WC_AM_User class object.
 *
 * @since 2.0
 *
 * @return \WC_AM_User
 */
function WC_AM_USER() {
	return WC_AM_User::instance();
}

WC_AM_USER();