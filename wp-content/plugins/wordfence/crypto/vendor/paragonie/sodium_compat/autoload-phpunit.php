<?php
// phpcs:ignoreFile -- compatibility library for PHP 5-7.1

require_once (dirname(__FILE__) . '/vendor/autoload.php');

if (PHP_VERSION_ID >= 50300) {
    require_once (dirname(__FILE__) . '/tests/phpunit-shim.php');
}
