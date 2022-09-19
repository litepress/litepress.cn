<?php

namespace LitePress\GlotPress\Memory_Edit;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	public $customers_obj;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * 创建机器翻译填充任务
	 */
	public function add_menu() {
		$hook = add_menu_page(
			'记忆库编辑',
			'记忆库编辑',
			'administrator',
			'lp-gp-memory-edit',
			array( $this, 'plugin_settings_page' ),
			'dashicons-admin-network',
			50
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );
	}

	public function plugin_settings_page() {
		if ( 'post' === strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
			$id = $_POST['id'];
			$method = $_POST['method'];

			$priority = $method === 'high' ? 100 : 0;

			global $wpdb;

			$wpdb->update(
				$wpdb->prefix . 'gp_memory',
				array(
					'priority' => $priority,
				),
				array(
					'id' => $id,
				)
			);
		}

		?>
        <div class="wrap">
            <h2>记忆库编辑</h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
		<?php
	}

	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers_obj = new Manage();
	}

}
