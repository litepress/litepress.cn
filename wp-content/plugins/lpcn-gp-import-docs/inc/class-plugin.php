<?php

namespace LitePress\GlotPress\Import_Docs;

use GP;
use GP_Project;
use LitePress\Logger\Logger;
use PO;
use Translation_Entry;
use function LitePress\Helper\html_split;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

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

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_action( 'lpcn_gp_doc_import', array( $this, 'job' ), 10, 3 );
	}

	public function job( string $name, string $slug, string $content ) {
		if ( empty( $name ) || empty( $slug ) || empty( $content ) ) {
			Logger::error( 'DOC_POT', '传入了空的参数', array(
				'name'    => $name,
				'slug'    => $slug,
				'content' => $content,
			) );

			return;
		}

		$section_strings = html_split( $content );

		$pot = new PO();
		$pot->set_header( 'MIME-Version', '1.0' );
		$pot->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
		$pot->set_header( 'Content-Transfer-Encoding', '8bit' );

		foreach ( $section_strings as $text ) {
			$pot->add_entry( new Translation_Entry( [
				'singular' => $text,
			] ) );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'doc-pot' );
		$pot_file  = "$temp_file.pot";
		rename( $temp_file, $pot_file );

		$exported = $pot->export_to_file( $pot_file );
		if ( ! $exported ) {
			Logger::error( 'DOC_POT', '从文档内容创建 POT 文件失败', array(
				'name' => $name,
				'slug' => $slug
			) );

			return;
		}

		$project = $this->update_gp_project( $name, $slug );
		if ( empty( $project ) ) {
			Logger::error( 'DOC_POT', '获取 GlotPress 项目失败', array(
				'name' => $name,
				'slug' => $slug
			) );

			return;
		}

		$format    = gp_get_import_file_format( 'po', '' );
		$originals = $format->read_originals_from_file( $pot_file, $project );
		// 当读取了 pot 文件后删除临时文件
		unlink( $pot_file );

		if ( empty( $originals ) ) {
			Logger::error( 'DOC_POT', '无法从通过文档内容生成的 POT 文件中加载原文', array(
				'name' => $name,
				'slug' => $slug
			) );

			return;
		}

		GP::$original->import_for_project( $project, $originals );
	}

	/**
	 * 更新 GlotPress 上的项目，并返回子项目的 ID
	 *
	 * @param $name
	 * @param $slug
	 *
	 * @return \GP_Project
	 */
	private function update_gp_project( $name, $slug ): GP_Project {
		// 检查项目是否已存在
		$exist = GP::$project->find_one( array( 'path' => "docs/$slug/body" ) );
		if ( ! empty( $exist ) ) {
			return $exist;
		}

		// 创建父项目
		$args           = array(
			'name'                => $name,
			'author'              => '',
			'slug'                => $slug,
			'path'                => "docs/$slug",
			'description'         => '',
			'parent_project_id'   => 4,
			'source_url_template' => '',
			'active'              => 1
		);
		$parent_project = GP::$project->create_and_select( $args );

		// 创建子项目
		$args        = array(
			'name'                => '文档主体',
			'author'              => '',
			'slug'                => 'body',
			'path'                => "docs/$slug/body",
			'description'         => '',
			'parent_project_id'   => $parent_project->id,
			'source_url_template' => '',
			'active'              => 1
		);
		$sub_project = GP::$project->create_and_select( $args );

		// 为子项目创建翻译集
		$args = array(
			'name'       => '简体中文',
			'slug'       => 'default',
			'project_id' => $sub_project->id,
			'locale'     => 'zh-cn',
		);
		GP::$translation_set->create( $args );

		return $sub_project;
	}

}
