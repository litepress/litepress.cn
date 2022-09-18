=== Payment gateway for WooCommerce - Woo Alipay ===
Contributors: frogerme
Tags: alipay, alibaba, payments, payment gateway, 支付宝, 阿里巴巴
Requires at least: 4.9.5
Tested up to: 5.4
Stable tag: trunk
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Alipay China payment gateway for WooCommerce.

== Description ==

Woo Alipay adds an Alipay China payment gateway to WooCommerce-powered websites.
This Mainland China gateway allows customers to pay both in mobile browsers or from their computer.

### Requirements

* A [China Mainland Alipay merchant account](https://b.alipay.com/).
* The Payment products "支付产品" Computer website payment "电脑网站支付" and Mobile website payment "手机网站支付" enabled.

### Important Notes

* Does NOT support cross-border payments: many other gateways are already providing this feature ; this is for Mainland China-hosted websites dealing with transactions within China.
* Make sure to read the "TROUBLESHOOT, FEATURE REQUESTS AND 3RD PARTY INTEGRATION" section below and [the full documentation](https://github.com/froger-me/woo-alipay/blob/master/README.md) before contacting the author.

### Overview

This plugin adds the following major features to WooCommerce:

* **Payment of WooCommerce orders in mobile web browser app:** calls the Alipay mobile app for a seamless experience.
* **Payment of WooCommerce orders standard in web browser:** authentication performed via credentials or QR code on desktop/laptop.
* **Refund of WooCommerce orders:** possibility to refund orders manually in a few clicks, and support for automatic refund in case the transaction failed.
* **Multi-currency support:** using an exchange rate against Chinese Yuan configured in the settings.

Compatible with [WooCommerce Multilingual](https://wordpress.org/plugins/woocommerce-multilingual/), [WPML](http://wpml.org/), [Ultimate Member](https://wordpress.org/plugins/ultimate-member/), and any caching plugin compatible with WooCommerce.

### Troubleshoot, feature requests and 3rd party integration

Unlike most Alipay Mainland China integration plugins, Woo Alipay is provided for free.  

Woo Alipay is regularly updated, and bug reports are welcome, preferably on [Github](https://github.com/froger-me/woo-alipay/issues). Each bug report will be addressed in a timely manner, but issues reported on WordPress may take significantly longer to receive a response.  

Woo Alipay has been tested with the latest version of WordPress and WooCommerce - in case of issue, please ensure you are able to reproduce it with a default installation of WordPress, WooCommerce plugin, and Storefront theme and any of the aforementioned supported plugins if used before reporting a bug.  

Feature requests (such as "it would be nice to have XYZ") or 3rd party integration requests (such as "it is not working with XYZ plugin" or "it is not working with my theme") will be considered only after receiving a red envelope (红包) of a minimum RMB 500 on WeChat (guarantee of best effort, no guarantee of result). 

To add the author on WeChat, click [here](https://froger.me/wp-content/uploads/2018/04/wechat-qr.png), scan the WeChat QR code, and add "Woo Alipay" as a comment in your contact request.  

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/woo-alipay` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Edit plugin settings

= 1.1.3 =
* Strip `%` from data sent to Alipay
* WC tested up to: 4.0.1
* WordPress tested up to: 5.4

= 1.1.2 =
* WC tested up to: 4.0

= 1.1.1 =
* Adjust price parameters for high values, and use the `maybe_convert_amount` method throughout
* WC tested up to: 3.9.2

= 1.1 =
* Add translations
* Make sure parameters send to Alipay stay within string length requirements
* Fix floating point arithmetic
* WC tested up to: 3.9.1

= 1.0 =
* First version