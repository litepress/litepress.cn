<?php

namespace WordPressdotorg\GlotPress\TranslationSuggestions;

use GP;
use GP_Locales;
use LitePress\Chinese_Format\Chinese_Format;
use function LitePress\Helper\is_chinese;

class Plugin {

	const TM_UPDATE_EVENT = 'wporg_translate_tm_update';

	/**
	 * @var Plugin The singleton instance.
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $queue = [];

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_action( 'template_redirect', [ $this, 'register_routes' ], 5 );
		add_action( 'gp_pre_tmpl_load', [ $this, 'pre_tmpl_load' ], 10, 2 );
		add_action( 'wporg_translate_suggestions', [ $this, 'extend_translation_suggestions' ] );

		// wp.org本来是禁止了该这仨钩子在命令行下执行，但因为lp.cn的corn是由命令行触发的，所以必须放开条件
		//if ( 'cli' !== PHP_SAPI ) {
			add_action( 'gp_translation_created', [ $this, 'translation_updated' ], 3 );
			add_action( 'gp_translation_saved', [ $this, 'translation_updated' ], 3 );

			// DB Writes are delayed until shutdown to bulk-update the stats during imports.
			add_action( 'shutdown', [ $this, 'schedule_tm_update' ], 3 );
		//}

		add_action( self::TM_UPDATE_EVENT, [ Translation_Memory_Client::class, 'update' ] );
	}

	/**
	 * Adds a translation in queue when a translation was created
	 * or updated.
	 *
	 * @param \GP_Translation $translation Created/updated translation.
	 */
	public function translation_updated( $translation ) {
		if ( ! $translation->user_id || 'current' !== $translation->status ) {
			return;
		}

		$this->queue[ $translation->original_id ] = $translation->id;
	}

	/**
	 * Schedules a single event to update translation memory for new translations.
	 */
	public function schedule_tm_update() {
		remove_action( 'gp_translation_created', [ $this, 'translation_updated' ], 3 );
		remove_action( 'gp_translation_saved', [ $this, 'translation_updated' ], 3 );

		if ( ! $this->queue ) {
			return;
		}

		// Translation_Memory_Client::update( $this->queue );

		/**
		 * 将翻译队列切割为以200为单位的小块，录入计划任务
		 */
		for ( $i = 0; true; $i += 200 ) {
			$item = array_slice( $this->queue, $i, 200, true );
			if ( empty( $item ) ) {
				break;
			}

			//do_action(self::TM_UPDATE_EVENT , $item);
			wp_schedule_single_event( time() + 60, self::TM_UPDATE_EVENT, [ 'translations' => $item ] );
		}
	}

	/**
	 * Registers custom routes.
	 */
	public function register_routes() {
		$dir      = '([^_/][^/]*)';
		$path     = '(.+?)';
		$projects = 'projects';
		$project  = $projects . '/' . $path;
		$locale   = '(' . implode( '|', wp_list_pluck( GP_Locales::locales(), 'slug' ) ) . ')';
		$set      = "$project/$locale/$dir";

		GP::$router->prepend( "/$set/-get-tm-suggestions", [
			__NAMESPACE__ . '\Routes\Translation_Memory',
			'get_suggestions'
		] );
		GP::$router->prepend( "/$set/-get-other-language-suggestions", [
			__NAMESPACE__ . '\Routes\Other_Languages',
			'get_suggestions'
		] );
	}

	/**
	 * Enqueue custom styles and scripts.
	 */
	public function pre_tmpl_load( $template, $args ) {
		if ( 'translations' !== $template || ! isset( $args['translation_set']->id ) || ! GP::$permission->current_user_can( 'edit', 'translation-set', $args['translation_set']->id ) ) {
			return;
		}

		wp_register_style(
			'gp-translation-suggestions',
			plugins_url( 'css/translation-suggestions.css', PLUGIN_FILE ),
			[],
			'20200301'
		);
		gp_enqueue_style( 'gp-translation-suggestions' );

		wp_register_script(
			'gp-translation-suggestions',
			plugins_url( './js/translation-suggestions.js', PLUGIN_FILE ),
			[ 'gp-editor' ],
			'20190510'
		);

		gp_enqueue_script( 'gp-translation-suggestions' );

		wp_add_inline_script(
			'gp-translation-suggestions',
			sprintf(
				"window.WPORG_TRANSLATION_MEMORY_API_URL = %s;\nwindow.WPORG_OTHER_LANGUAGES_API_URL = %s;",
				wp_json_encode( gp_url_project( $args['project'], gp_url_join( $args['locale_slug'], $args['translation_set_slug'], '-get-tm-suggestions' ) ) ),
				wp_json_encode( gp_url_project( $args['project'], gp_url_join( $args['locale_slug'], $args['translation_set_slug'], '-get-other-language-suggestions' ) ) )
			)
		);
	}

	/**
	 * Extends the suggestions container for Translation Memory and
	 * Other Languages.
	 *
	 * @param object $entry Current translation row entry.
	 */
	public function extend_translation_suggestions( $entry ) {
		if ( ! isset( $entry->translation_set_id ) || ! GP::$permission->current_user_can( 'edit', 'translation-set', $entry->translation_set_id ) ) {
			return;
		}

		?>
        <details open class="suggestions__translation-memory"
                 data-nonce="<?php echo esc_attr( wp_create_nonce( 'translation-memory-suggestions-' . $entry->original_id ) ); ?>">
            <summary>来自翻译记忆库的建议</summary>
            <p class="suggestions__loading-indicator">加载中 <span aria-hidden="true"
                                                                class="suggestions__loading-indicator__icon"><span></span><span></span><span></span></span>
            </p>
        </details>
		<?php
	}
}

if ( isset( $_GET['sa'] ) ) {
	global $wpdb;

	$sql = <<<SQL
select * from wp_4_gp_memory
SQL;
	$r   = $wpdb->get_results( $sql );

	$a = Chinese_Format::get_instance();

	foreach ( $r as $item ) {
		$target = $a->convert( (string) $item->target );

		$wpdb->update( 'wp_4_gp_memory',
			array(
				'id'     => md5( $item->source . '|' . $target ),
				'target' => $target
			),
			array(
				'id' => $item->id
			)
		);
	}

	exit;
}

if ( isset( $_GET['as'] ) ) {
	global $wpdb;

	$sql = <<<SQL
select o.singular as source, t.translation_0 as target
from wp_4_gp_translations as t
         join wp_4_gp_originals as o on t.original_id = o.id
SQL;

	$r = $wpdb->get_results( $sql );

	$memory = array();
	foreach ( $r as $item ) {
		$source = strtolower( trim( $item->source ) );
		$target = trim( $item->target );
		$id     = md5(
			$source
			. '|'
			. $target
		);

		$s = $source;
		$t = $target;

		$is_empty = function ( $str ) {
			return empty( str_replace( array( '\n', '\r', '\n\r', ' ', '&nbsp;', '&#160;' ), '', $str ) );
		};

		if ( $is_empty( $s ) || $is_empty( $t ) ) {
			continue;
		}

		if ( ! is_chinese( $t ) ) {
			continue;
		}

		$memory[ $id ] = $wpdb->prepare( "( '%s', '%s', '%s' )", $id, $source, $target );
	}

	$data = join( ',', $memory );

	$sql = <<<SQL
REPLACE INTO wp_4_gp_memory ( id, source, target )
VALUES 
$data;
SQL;

	$wpdb->query( $sql );
	var_dump( $wpdb->last_error );

	echo count( $memory );
	exit;
}
