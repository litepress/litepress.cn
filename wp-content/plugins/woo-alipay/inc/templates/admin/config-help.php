<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="woo-alipay-config-help stuffbox closed">
	<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Gateway configuration help', 'woo-alipay' ); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>
	<h2 class="handle">
		<?php esc_html_e( 'Gateway configuration help', 'woo-alipay' ); ?>
	</h2>
	<div class="inside">
		<h3>
			<?php esc_html_e( 'Configuration process overview:', 'woo-alipay' ); ?>
		</h3>
		<ul>
			<li>
				<?php
				printf(
					// translators: %1$s is <a target="_blank" href="[url]">the Alipay Open Platform</a>
					esc_html__( 'Go to the %1$s and log in.', 'woo-alipay' ),
					sprintf(
						'<a target="_blank" href="%s">%s</a>',
						'https://openhome.alipay.com/platform/developerIndex.htm',
						esc_html__( 'Alipay Open Platform', 'woo-alipay' )
					)
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url</code>
					esc_html__( 'If necessary, create a new app by following the options under the create application "创建应用" menu: website & mobile application "网页&移动应用" > payment access "支付接入". The application type "应用类型" should be web page application "网页应用" and the website URL "网址url" should be %1$s. Creating an app will require one or two working day(s) audit of your website content by Alipay.', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						esc_html( trailingslashit( get_home_url() ) )
					)
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url with placeholder</code>, %2$s is <code>placeholder</code>
					esc_html__( 'Once the app is created, go to the app information page - accessible directly via %1$s (replace %2$s with the App ID to be used by Woo Alipay).', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						'https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/appInfo'
					),
					sprintf(
						'<code>%s</code>',
						'[[YOUR_APP_ID]]'
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Activate payment features and configure the app with the gateway information (see "Register features, website URL and callback" below).', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'If necessary, generate the application public and private keys, register the application public key in the Alipay Open Platform, and generate the Alipay public key (see "Key management" below).', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Fill in the configuration fields below.', 'woo-alipay' ); ?>
			</li>
		</ul>
		<h3>
			<?php esc_html_e( 'Register features, website URL and callback', 'woo-alipay' ); ?>
		</h3>
		<p>
			<?php esc_html_e( 'To communicate with the payment gateway, Alipay needs some features activated, to know your website URL, and to be aware of the WooCommerce gateway callback endpoint.', 'woo-alipay' ); ?>
		</p>
		<ul>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url with placeholder</code>, %2$s is <code>placeholder</code>
					esc_html__( 'Go to the app overview page - accessible via %1$s (replace %2$s with the App ID to be used by Woo Alipay).', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						'https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]]/overview'
					),
					sprintf(
						'<code>%s</code>',
						'[[YOUR_APP_ID]]'
					)
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <a target="_blank" href="[url]">Computer website payment "电脑网站支付"</a>, %2$s is <a target="_blank" href="[url]">Mobile website payment "手机网站支付"</a>
					esc_html__( 'Click the add feature button "添加功能" and add the computer website payment "电脑网站支付" and the mobile website payment "手机网站支付" features - these features need to have been activated beforehand (process not describe in this guide - see the Payment products "支付产品" %1$s and %2$s).', 'woo-alipay' ),
					sprintf(
						'<a target="_blank" href="%s">%s</a>',
						'https://b.alipay.com/signing/productDetailV2.htm?productId=I1011000290000001000',
						esc_html__( 'Computer website payment "电脑网站支付"', 'woo-alipay' )
					),
					sprintf(
						'<a target="_blank" href="%s">%s</a>',
						'https://b.alipay.com/signing/productDetailV2.htm?productId=I1011000290000001001',
						esc_html__( 'Mobile website payment "手机网站支付"', 'woo-alipay' )
					)
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url with placeholder</code>, %2$s is <code>placeholder</code>
					esc_html__( 'Go to the app information page - accessible via %1$s (replace %2$s with the App ID to be used by Woo Alipay).', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						'https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]/appInfo'
					),
					sprintf(
						'<code>%s</code>',
						'[[YOUR_APP_ID]]'
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Click the setup link "设置" (or modify link "修改") of the application gateway "应用网关" field.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url</code>
					esc_html__( 'In the application gateway "应用网关" modal, enter %1$s and click the confirm "确定" button.', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						esc_html( $url_root )
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Click the setup link "设置" (or modify link "修改") of the authorization callback address "授权回调地址" field.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>scheme</code>, %2$s is <code>url</code>
					esc_html__( 'In the authorization callback address "授权回调地址" modal, enter %1$s in the callback address type "回调地址类型" field, %2$s in the callback address "回调地址" field, and leave the verify domain only "只验证域名" checkbox unchecked for added security, then click the confirm "确定" button.', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						esc_html( $scheme )
					),
					sprintf(
						'<code>%s</code>',
						esc_html( $wc_callback )
					)
				);
				?>
			</li>
		</ul>
		<h3>
			<?php esc_html_e( 'Key management', 'woo-alipay' ); ?>
		</h3>
		<h4>
			<?php esc_html_e( '1 - Generating keys:', 'woo-alipay' ); ?>
		</h4>
		<p>
			<?php
			printf(
				// translators: %1$s is <strong>application public key</strong>, %2$s is <strong>application private key</strong>, %3$s is <strong>Alipay public key</strong>
				esc_html__( 'To complete the payment gateway configuration, you need to generate an %1$s, an %2$s and an %3$s.', 'woo-alipay' ),
				sprintf(
					'<strong>%s</strong>',
					esc_html__( 'application public key', 'woo-alipay' )
				),
				sprintf(
					'<strong>%s</strong>',
					esc_html__( 'application private key', 'woo-alipay' )
				),
				sprintf(
					'<strong>%s</strong>',
					esc_html__( 'Alipay public key', 'woo-alipay' )
				)
			);
			?>
		<p>
		<hr/>
		<p>
			<?php
			printf(
				// translators: %1$s is <strong>(option 1)</strong>
				esc_html__( 'Generate the application keys with the Alipay Open Platform Development Assistant application %1$s:', 'woo-alipay' ),
				sprintf(
					'<strong>%s</strong>',
					esc_html__( '(option 1)', 'woo-alipay' )
				)
			);
			?>
		</p>
		<ul>
			<li>
				<?php
				printf(
					// translators: %1$s is <a target="_blank" href="[url]">Alipay tools documentation page</a>
					esc_html__( 'Go to the %1$s and download the Alipay Open Platform Development Assistant application for the operating system of your choice (Windows or MacOS).', 'woo-alipay' ),
					sprintf(
						'<a target="_blank" href="%s">%s</a>',
						'https://docs.open.alipay.com/291/106097/',
						esc_html__( 'Alipay tools documentation page', 'woo-alipay' )
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Within the Alipay Open Platform Development Assistant application, in the "生成密钥" section (default screen when opening the Assistant), select "UTF-8" and "PKCS1非Java使用" options and click the "生成密钥" button to populate the text areas.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>alipay_app_public_key.txt</code>
					esc_html__( 'Copy the application public key displayed in the "应用公钥" text area in a text file on your computer (referred to as %1$s below).', 'woo-alipay' ),
					'<code>alipay_app_public_key.txt</code>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>alipay_app_private_key.txt</code>, %2$s is <strong>DO NOT SHARE THIS VALUE WITH ANYONE</strong>
					esc_html__( 'Copy the application private key displayed in the "应用私钥" text area in a text file on your computer (referred to as %1$s below) - %2$s.', 'woo-alipay' ),
					'<code>alipay_app_private_key.txt</code>',
					sprintf(
						'<strong>%s</strong>',
						esc_html__( 'DO NOT SHARE THIS VALUE WITH ANYONE', 'woo-alipay' )
					)
				);
				?>
			</li>
		</ul>
		<hr/>
		<p>
			<?php
			printf(
				// translators: %1$s is <code>openssl</code>, %2$s is <strong>(option 2)</strong>
				esc_html__( 'Generate the application keys with %1$s command line in a terminal %2$s:', 'woo-alipay' ),
				'<code>openssl</code>',
				sprintf(
					'<strong>%s</strong>',
					esc_html__( '(option 2)', 'woo-alipay' )
				)
			);
			?>
		</p>
		<ul>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>openssl</code>
					esc_html__( 'Type %1$s to open the OpenSSL command line tool.', 'woo-alipay' ),
					'<code>openssl</code>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>genrsa -out alipay_app_private_key.txt 2048</code>, %2$s is <code>alipay_app_private_key.txt</code>, %3$s is <strong>DO NOT SHARE THIS VALUE WITH ANYONE</strong>
					esc_html__( 'Type %1$s to generate the application private key file (referred to as %2$s below) - %3$s.', 'woo-alipay' ),
					'<code>genrsa -out alipay_app_private_key.txt 2048</code>',
					'<code>alipay_app_private_key.txt</code>',
					sprintf(
						'<strong>%s</strong>',
						esc_html__( 'DO NOT SHARE THIS VALUE WITH ANYONE', 'woo-alipay' )
					)
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>rsa -in alipay_app_private_key.txt -pubout -out alipay_app_public_key.txt</code>, %2$s is <code>alipay_app_public_key.txt</code>
					esc_html__( 'Type %1$s to generate the application public key file (referred to as %2$s below).', 'woo-alipay' ),
					'<code>rsa -in alipay_app_private_key.txt -pubout -out alipay_app_public_key.txt</code>',
					'<code>alipay_app_public_key.txt</code>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>openssl</code>
					esc_html__( 'Type %1$s to quit the command line tool.', 'woo-alipay' ),
					'<code>exit</code>'
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Open both files with a text editor, remove all the header, footer, space and carriage return characters to have each key as a single-line long string, and save the files.', 'woo-alipay' ); ?>
			</li>
		</ul>
		<hr/>
		<p>
			<?php
			printf(
				// translators: %1$s is <strong>(requires completing option 1 or option 2 above)</strong>
				esc_html__( 'Register the application public key in Alipay Open Platform and generate the Alipay public key %1$s:', 'woo-alipay' ),
				sprintf(
					'<strong>%s</strong>',
					esc_html__( '(requires completing option 1 or option 2 above)', 'woo-alipay' )
				)
			);
			?>
		</p>
		<ul>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url with placeholder</code>, %2$s is <code>placeholder</code>
					esc_html__( 'Go to the app information page - accessible via %1$s (replace %2$s with the App ID to be used by Woo Alipay).', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						'https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]/appInfo'
					),
					sprintf(
						'<code>%s</code>',
						'[[YOUR_APP_ID]]'
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Click the link "接口加签方式" > "设置/查看" to open the configuration modal.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Use the associated phone number or password to authenticate.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'In the signature key configuration form (加签管理 | 1 - 加签内容配置), select the public key option "公钥".', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>alipay_app_public_key.txt</code>
					esc_html__( 'Paste the content of the previously saved file %1$s in the "填写公钥字符" text area.', 'woo-alipay' ),
					'<code>alipay_app_public_key.txt</code>'
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Click the "保存设置" button to register the application public key and generate the Alipay public key.', 'woo-alipay' ); ?>
			</li>
		</ul>
		<hr/>
		<h4>
			<?php esc_html_e( '2 - Using the application private key and finding the Alipay public key:', 'woo-alipay' ); ?>
		</h4>
		<ul>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>url with placeholder</code>, %2$s is <code>placeholder</code>
					esc_html__( 'Go to the app information page - accessible via %1$s (replace %2$s with the App ID to be used by Woo Alipay).', 'woo-alipay' ),
					sprintf(
						'<code>%s</code>',
						'https://openhome.alipay.com/platform/appManage.htm#/app/[[YOUR_APP_ID]/appInfo'
					),
					sprintf(
						'<code>%s</code>',
						'[[YOUR_APP_ID]]'
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Click the link "接口加签方式" > "设置/查看" to open the configuration modal.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'If necessary, use the associated Alipay account\'s phone number or password to authenticate.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'In the signature completed configuration form (加签管理 | 2 - 加签配置完成), copy the Alipay public key displayed under "支付宝公钥".', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Paste the Alipay public key in the "Alipay public key" field below.', 'woo-alipay' ); ?>
			</li>
			<li>
				<?php
				printf(
					// translators: %1$s is <code>alipay_app_private_key.txt</code>
					esc_html__( 'Paste the content of the previously saved file %1$s in the "Alipay merchant application private key" field below.', 'woo-alipay' ),
					'<code>alipay_app_private_key.txt</code>'
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Click the "Save changes" button.', 'woo-alipay' ); ?>
			</li>
		</ul>
	</div>
</div>
