<?php namespace Premmerce\SDK\V2\Plugin;


interface PluginInterface{

	/**
	 * Plugin entry point
	 */
	public function run();

	/**
	 * Fired when the plugin is activated
	 */
	public function activate();


	/**
	 * Fired when the plugin is deactivated
	 */
	public function deactivate();


	/**
	 * Fired when the plugin is uninstalled
	 */
	public static function uninstall();

}