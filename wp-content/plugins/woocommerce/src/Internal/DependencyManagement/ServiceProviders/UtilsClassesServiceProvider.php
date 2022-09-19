<?php
/**
 * UtilsClassesServiceProvider class file.
 */

namespace Automattic\WooCommerce\Internal\DependencyManagement\ServiceProviders;

use Automattic\WooCommerce\Internal\DependencyManagement\AbstractServiceProvider;
use Automattic\WooCommerce\Internal\Utilities\DatabaseUtil;
use Automattic\WooCommerce\Internal\Utilities\HtmlSanitizer;

/**
 * Service provider for the non-static utils classes in the Automattic\WooCommerce\src namespace.
 */
class UtilsClassesServiceProvider extends AbstractServiceProvider {

	/**
	 * The classes/interfaces that are serviced by this service provider.
	 *
	 * @var array
	 */
	protected $provides = array(
		DatabaseUtil::class,
		HtmlSanitizer::class,
	);

	/**
	 * Register the classes.
	 */
	public function register() {
		$this->share( DatabaseUtil::class );
		$this->share( HtmlSanitizer::class );
	}
}
