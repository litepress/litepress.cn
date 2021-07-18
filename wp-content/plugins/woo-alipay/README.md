# Payment gateway for WooCommerce - Woo Alipay

* [General Description](#user-content-general-description)
	* [Requirements](#user-content-requirements)
	* [Overview](#user-content-overview)
* [Settings](#user-content-settings)
	* [Gateway settings](#user-content-gateway-settings)
* [Gateway configuration help](#user-content-gateway-configuration-help)
	* [Configuration process overview](#user-content-configuration-process-overview)
	* [Register features, website URL and callback](#user-content-register-features-website-url-and-callback)
	* [Key management](#user-content-key-management
	)
* [Templates](#user-content-templates)

## General Description

Woo Alipay adds an Alipay China payment gateway to WooCommerce-powered websites.
This Mainland China gateway allows customers to pay both in mobile browsers or from their computer.

### Requirements

* A [China Mainland Alipay merchant account](https://b.alipay.com/).
* The Payment products "支付产品" Computer website payment "电脑网站支付" and Mobile website payment "手机网站支付" enabled.

### Overview

This plugin adds the following major features to WooCommerce:

* **Payment of WooCommerce orders in mobile web browser app:** calls the Alipay mobile app for a seamless experience.
* **Payment of WooCommerce orders standard in web browser:** authentication performed via credentials or QR code on desktop/laptop.
* **Refund of WooCommerce orders:** possibility to refund orders manually in a few clicks, and support for automatic refund in case the transaction failed.
* **Multi-currency support:** using an exchange rate against Chinese Yuan configured in the settings.

Compatible with [WooCommerce Multilingual](https://wordpress.org/plugins/woocommerce-multilingual/), [WPML](http://wpml.org/), [Ultimate Member](https://wordpress.org/plugins/ultimate-member/), and any caching plugin compatible with WooCommerce.

## Settings

The settings below are added to WooCommerce and WP Weixin when the plugin is active.

### Gateway settings

The following settings can be accessed in WooCommerce > Settings > Payments > Alipay:

| Name                                    | Type     | Description                                                                                                                                                                                                     |
| --------------------------------------- |:--------:| --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Enable/Disable                          | checkbox | Used to enable/disable the payment gateway.                                                                                                                                                                     |
| Checkout page title                     | text     | Title displayed for the payment gateway on the checkout page.                                                                                                                                                   |
| Checkout page description               | text     | Description displayed for the payment gateway on the checkout page.                                                                                                                                             |
| Alipay App ID                           | text     | The App ID found in Alipay Open Platform.                                                                                                                                                                       |
| Alipay public key                       | textarea | The Alipay public key generated in the Alipay Open Platform ("支付宝公钥").                                                                                                                                      |
| Alipay Merchant application private key | textarea | The private key generated with the provided Alipay tool application or the `openssl` command line.<br/>This key is secret and is not recorded in Alipay Open Platform - **DO NOT SHARE THIS VALUE WITH ANYONE**.|
| Sandbox                                 | checkbox | If checked, Run Alipay in sandbox mode, with the settings found in [https://openhome.alipay.com/platform/appDaily.htm](https://openhome.alipay.com/platform/appDaily.htm).                                      |
| Exchange Rate                           | number   | Exchange rate against Chinese Yuan (shows if the store currency is not set to Chinese Yuan)                                                                                                                     |

## Gateway configuration help

### Configuration process overview

* Go to the [Alipay Open Platform](https://openhome.alipay.com/platform/developerIndex.htm) and log in.
* If necessary, create a new app by following the options under the create application "创建应用" menu: website & mobile application "网页&移动应用" > payment access "支付接入". The application type "应用类型" should be web page application "网页应用" and the website URL "网址url" should be `https://[[your-homepage-url]]` where `[[your-homepage-url]]` is the home URL of your website. Creating an app will require one or two working day(s) audit of your website content by Alipay.
* Once the app is created, go to the app information page - accessible directly via `https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/appInfo` (replace `[[YOUR_APP_ID]]` with the App ID to be used by Woo Alipay).
* Activate payment features and configure the app with the gateway information (see ["Register features, website URL and callback"](#user-content-register-features-website-url-and-callback) below).
* If necessary, generate the application public and private keys, register the application public key in the Alipay Open Platform, and generate the Alipay public key (see ["Key management"](#user-content-key-management) below).
* Fill in the configuration fields below.


### Register features, website URL and callback
To communicate with the payment gateway, Alipay needs some features activated, to know your website URL, and to be aware of the WooCommerce gateway callback endpoint.  

* Go to the app overview page - accessible via `https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/overview` (replace `[[YOUR_APP_ID]]` with the App ID to be used by Woo Alipay).
* Click the add feature button "添加功能" and add the computer website payment "电脑网站支付" and the mobile website payment "手机网站支付" features - these features need to have been activated beforehand (process not describe in this guide - see the Payment products "支付产品" [Computer website payment "电脑网站支付"](https://b.alipay.com/signing/productDetailV2.htm?productId=I1011000290000001000) and [Mobile website payment "手机网站支付"](https://b.alipay.com/signing/productDetailV2.htm?productId=I1011000290000001001)).
* Go to the app information page - accessible via `https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/appInfo` (replace `[[YOUR_APP_ID]]` with the App ID to be used by Woo Alipay).
* Click the setup link "设置" (or modify link "修改") of the application gateway "应用网关" field.
* In the application gateway "应用网关" modal, enter `https://[[your-domain.com]]` (replace `[[your-domain.com]]` with the domain or subdomain running WordPress and Woo Alipay) and click the confirm "确定" button.
* Click the setup link "设置" (or modify link "修改") of the authorization callback address "授权回调地址" field.
* In the authorization callback address "授权回调地址" modal, enter `https` in the callback address type "回调地址类型" field, `https://[[your-homepage-url]]/wc-api/WC_Alipay/]]` in the callback address "回调地址" field (replace `[[your-homepage-url]]` with the home URL of your website), and leave the verify domain only "只验证域名" checkbox unchecked for added security, then click the confirm "确定" button.

### Key management

#### 1 - Generating keys:

To complete the payment gateway configuration, you need to generate an application public key, an application private key and an Alipay public key.

___

##### Generate the application keys with the Alipay Open Platform Development Assistant application (option 1):

* Go to the [Alipay tools documentation page](https://docs.open.alipay.com/291/106097/) and download the Alipay Open Platform Development Assistant application for the operating system of your choice (Windows or MacOS).
* Within the Alipay Open Platform Development Assistant application, in the "生成密钥" section (default screen when opening the Assistant), select "UTF-8" and "PKCS1非Java使用" options and click the "生成密钥" button to populate the text areas.
* Copy the application public key displayed in the "应用公钥" text area in a text file on your computer (referred to as `alipay_app_public_key.txt` below).
* Copy the application private key displayed in the "应用私钥" text area in a text file on your computer (referred to as `alipay_app_private_key.txt` below) - **DO NOT SHARE THIS VALUE WITH ANYONE**.

___

##### Generate the application keys with openssl command line in a terminal (option 2):

* Type `openssl` to open the OpenSSL command line tool.
* Type `genrsa -out alipay_app_private_key.txt 2048` to generate the application private key file (referred to as `alipay_app_private_key.txt` below) - **DO NOT SHARE THIS VALUE WITH ANYONE**.
* Type `rsa -in alipay_app_private_key.txt -pubout -out alipay_app_public_key.txt` to generate the application public key file (referred to as `alipay_app_public_key.txt` below).
* Type `exit` to quit the command line tool.
* Open both files with a text editor, remove all the header, footer, space and carriage return characters to have each key as a single-line long string, and save the files.

___

##### Register the application public key in Alipay Open Platform and generate the Alipay public key (requires completing option 1 or option 2 above):

* Go to the app information page - accessible via `https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/appInfo` (replace `[[YOUR_APP_ID]]` with the App ID to be used by Woo Alipay).
* Click the link "接口加签方式" > "设置/查看" to open the configuration modal.
* Use the associated phone number or password to authenticate.
* In the signature key configuration form (加签管理 | 1 - 加签内容配置), select the public key option "公钥".
* Paste the content of the previously saved file `alipay_app_public_key.txt` in the "填写公钥字符" text area.
* Click the "保存设置" button to register the application public key and generate the Alipay public key.

___

#### 2 - Using the application private key and finding the Alipay public key:

* Go to the app information page - accessible via `https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/appInfo` (replace `[[YOUR_APP_ID]]` with the App ID to be used by Woo Alipay).
* Click the link "接口加签方式" > "设置/查看" to open the configuration modal.
* If necessary, use the associated Alipay account's phone number or password to authenticate.
* In the signature completed configuration form (加签管理 | 2 - 加签配置完成), copy the Alipay public key displayed under "支付宝公钥".
* Paste the Alipay public key in the "Alipay public key" field below.
* Paste the content of the previously saved file `alipay_app_private_key.txt` in the "Alipay merchant application private key" field below.
* Click the "Save changes" button.

## Templates

The following template file is selected using the `locate_template()` and included with `load_template()` functions provided by WordPress. This means it can be overloaded in the active WordPress theme. Developers may place their custom template file in the following directories under the theme's folder (in order of selection priority):

* `plugins/woo-alipay/`
* `woo-alipay/`
* `woocommerce/woo-alipay/`
* at the root of the theme's folder

___

### redirected-pay

```
redirected-pay.php
```  

**Description**  
The template of the page displayed when users are redirected before payment. The template acts as a placeholder before showing the Alipay payment UI.  

**Associated style enqueued with key:**  
`woo-alipay-main-style`  
