<?php

namespace WordfenceLS;

abstract class Model_Asset {
	public static function js($file) {
		return self::_pluginBaseURL() . 'js/' . self::_versionedFileName($file);
	}
	
	public static function css($file) {
		return self::_pluginBaseURL() . 'css/' . self::_versionedFileName($file);
	}
	
	public static function img($file) {
		return self::_pluginBaseURL() . 'img/' . $file;
	}
	
	protected static function _pluginBaseURL() {
		return plugins_url('', WORDFENCE_LS_FCPATH) . '/';
	}
	
	protected static function _versionedFileName($subpath) {
		$version = WORDFENCE_LS_BUILD_NUMBER;
		if ($version != 'WORDFENCE_LS_BUILD_NUMBER' && preg_match('/^(.+?)(\.[^\.]+)$/', $subpath, $matches)) {
			$prefix = $matches[1];
			$suffix = $matches[2];
			return $prefix . '.' . $version . $suffix;
		}
		
		return $subpath;
	}
}