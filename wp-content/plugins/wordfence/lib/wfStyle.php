<?php
class wfStyle {
	/**
	 * Returns the classes for the main content body of the page, adjusting for the paid status.
	 * 
	 * @return string
	 */
	public static function contentClasses() {
		if (wfConfig::get('isPaid')) {
			return 'wf-col-xs-12';
		}
		return 'wf-col-xs-12';
	}
}