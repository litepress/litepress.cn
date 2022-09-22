<?php

namespace WePublish\IPUA\Inc;

use JetBrains\PhpStorm\NoReturn;
use WePublish\IPUA\Inc\Setting as Setting;

class Plugin {

	/**
	 * Instantiates a new Plugin object.
	 */

	private $settings_api;

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		$this->settings_api = new Setting;
	}

	/**
	 * Initializes the plugin.
	 */
	#[NoReturn] public function plugins_loaded() {
		add_action( 'admin_init', array( $this, 'load_setting' ) );
		add_action( 'admin_menu', array( $this, 'load_setting_page' ) );
	}

	/**
	 * 挂载设置项
	 */
	public function load_setting() {
		$sections = array(
			array(
				'id'    => 'wp_ipua_setting',
				'title' => __( '设置', 'wp-ipua' )
			),
			array(
				'id'    => 'wp_ipua_about',
				'title' => __( '关于', 'wp-ipua' )
			)
		);

		$fields = array(
			'wp_ipua_setting' => array(
				array(
					'name'        => 'tx_key',
					'label'       => __( '腾讯位置服务 应用Key', 'wp-ipua' ),
					'desc'        => __( '输入腾讯位置服务的应用Key', 'wp-ipua' ),
					'type'        => 'text',
					'placeholder' => 'XXXXX-XXXXX-XXXXX-XXXXX-XXXXX-XXXXX',
					'default'     => ''
				),
				array(
					'name'    => 'cache',
					'label'   => __( 'IP位置缓存', 'wp-ipua' ),
					'desc'    => __( '为保证加载速度，强烈建议设置3个月及以上的缓存时间', 'wp-ipua' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'no'  => __( '不缓存', 'wp-ipua' ),
						'1'   => __( '1天', 'wp-ipua' ),
						'7'   => __( '7天', 'wp-ipua' ),
						'30'  => __( '1月', 'wp-ipua' ),
						'60'  => __( '2月', 'wp-ipua' ),
						'90'  => __( '3月', 'wp-ipua' ),
						'180' => __( '6月', 'wp-ipua' ),
						'365' => __( '1年', 'wp-ipua' ),
					)
				),
				array(
					'name'    => 'ip_format',
					'label'   => __( 'IP属地格式', 'wp-ipua' ),
					'desc'    => __( '设置IP属地输出的格式', 'wp-ipua' ),
					'type'    => 'select',
					'default' => 'npc',
					'options' => array(
						'npc'   => __( '国家省份城市', 'wp-ipua' ),
						'n-p-c' => __( '国家 省份 城市', 'wp-ipua' ),
						'np'    => __( '国家省份', 'wp-ipua' ),
						'n-p'   => __( '国家 省份', 'wp-ipua' ),
						'pc'    => __( '省份城市', 'wp-ipua' ),
						'p-c'   => __( '省份 城市', 'wp-ipua' ),
						'n'     => __( '国家', 'wp-ipua' ),
						'np2'   => __( '省份(海外显示国家)', 'wp-ipua' ),
						'p'     => __( '省份', 'wp-ipua' ),
						'c'     => __( '城市', 'wp-ipua' ),
					)
				),
				array(
					'name'    => 'ua_format',
					'label'   => __( 'User-Agent格式', 'wp-ipua' ),
					'desc'    => __( '设置User-Agent输出的格式', 'wp-ipua' ),
					'type'    => 'select',
					'default' => 'pb',
					'options' => array(
						'pb' => __( '平台名 浏览器名（Windows10 Chrome）', 'wp-ipua' ),
						'p'  => __( '平台名（Windows10）', 'wp-ipua' ),
						'b'  => __( '浏览器名（Chrome）', 'wp-ipua' ),
					)
				),
				array(
					'name'  => 'code',
					'label' => __( '代码集成', 'wp-ipua' ),
					'type'  => 'html',
					'html'  => __( '<p>请在主题的评论模板开头加入下方代码以引用<b>WP-IPUA</b>的函数:</p><code>use function WePublish\IPUA\Inc\get_ip_address;</code></br><code>use function WePublish\IPUA\Inc\get_ua_info;</code><p>然后在输出评论者的语句附近调用输出函数:</p><code>&lt;?php echo get_ip_address(); ?&gt;</code></br><code>&lt;?php echo get_ua_info(); ?&gt;</code><p>输出格式为纯文本，你可以自行调整输出内容的CSS样式。</p><p>目前已支持在评论循环中自动提取评论者IP和User-Agent，如果你在使用bbPress等非WordPress默认评论环境，请自行传入IP和User-Agent信息给函数。</p>', 'wp-ipua' )
				)
			),
			'wp_ipua_about'   => array(
				array(
					'name'  => 'about',
					'label' => __( '关于插件', 'wp-ipua' ),
					'type'  => 'html',
					'html'  => __( '<h4>官网：<a href="https://wepublish.cn" target="_blank">https://wepublish.cn</a> | 隐私协议：<a href="https://lbs.qq.com/userAgreements/agreements/terms" target="_blank">https://lbs.qq.com/userAgreements/agreements/terms</a></h4><p><b>WP-IPUA</b>是WordPress本土化的一部分，其作用是提供简易的方法为WordPress添加评论IP属地显示及User-Agent显示。</p><p>该插件由<b>WePublish@耗子</b>负责维护，使用问题与反馈交流QQ群：<a href="https://jq.qq.com/?_wv=1027&k=I1oJKSTH">12370907</a></p>', 'wp-ipua' )
				)
			)
		);


		//set sections and fields
		$this->settings_api->set_sections( $sections );
		$this->settings_api->set_fields( $fields );

		//initialize them
		$this->settings_api->admin_init();
	}

	/**
	 * 挂载设置页面
	 */
	public function load_setting_page() {
		add_options_page( esc_html__( 'WP-IPUA', 'wp-ipua' ), esc_html__( 'WP-IPUA', 'wp-ipua' ), 'manage_options', 'wp_ipua', array(
			$this,
			'plugin_page'
		) );
		add_filter( 'plugin_action_links', function ( $links, $file ) {
			if ( 'wp-ipua/wp-ipua.php' !== $file ) {
				return $links;
			}
			$settings_link = '<a href="' . add_query_arg( array( 'page' => 'wp_ipua' ), admin_url( 'options-general.php' ) ) . '">' . esc_html__( '设置', 'wp-ipua' ) . '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}, 10, 2 );
	}

	/**
	 * 设置页面模版
	 */
	public function plugin_page() {
		echo '<h1>WP-IPUA</h1><span style="float: right;">By: WePublish@耗子</span>';
		echo '<div class="wrap">';
		settings_errors();

		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();

		echo '</div>';
	}
}
