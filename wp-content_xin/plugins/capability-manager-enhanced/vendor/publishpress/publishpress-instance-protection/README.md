# PublishPress Instance Protection

This is a library for protecting WordPress plugins to run twice instances at the same time.

## Installation

This library should be added as a composer requirement.

First you need to include the repository, adding the following code to the `composer.json` file:

```json
{
    "repositories": [
        {
        "type": "git",
        "url": "https://github.com/publishpress/publishpress-instance-protection"
        }
    ]
}
```

Then require the library running the command:

```shell
composer require publishpress/publishpress-instance-protection
```

## How to use it

### Making sure the plugin do not break with more running instances

The first step, before even requiring this library, is making sure your plugin do not break if it has two or more instances
running at the same time on the site.

This is easy to accomplish using constants defined after the plugin is loaded, and checking it before loading the plugin.
If the constant is defined, we do not load the plugin again (and not even require its libraries).

This verification has to be out of any hook.

Make sure to rename the variables and constant according to your plugin. This example copy the PublishPress Authors code:

#### Free plugin example

```php
if (! defined('PP_AUTHORS_LOADED')) {
    // include libraries, and dependencies
    // instantiate the plugin, hooks, etc

    define('PP_AUTHORS_LOADED', true);
}
```

#### Pro plugin example

```php
if (! defined('PP_AUTHORS_PRO_LOADED') && ! defined('PP_AUTHORS_LOADED')) {
    // include libraries, and dependencies
    // instantiate the plugin, hooks, etc
    // initialize the free plugin

    define('PP_AUTHORS_PRO_LOADED', true);
}
```

Please note that the Pro plugin checks two constants: its own constant, and the one defined in the free plugin. This
way the Pro won't run if the free is already running as a stand alone plugin.

#### Functions

Before defining functions on the global escope always add it inside a conditional using `function_exists`. If they are defined on a speicifc `includes.php` file for example, you can use only one conditional before including it, instead of adding the conditional to every function.

#### Classes

Before declaring classes, follow the same approach on the previous topic, but using `class_exists`.

### Adding the admin notices library

This library do not use composer's autoloader because it needs to be loaded before everything else in the plugins. But
it has its own autoloader inside it, following the PSR-4 pattern.

You should always check if the vendor folder is on the expected path before trying to include anything from inside it.
If not on the standard folder, make sure to give an option to the use to define a constant that gives the custom path of
the vendor directory.

**Check the following example on how to include and instantiate the library, just make sure to add this code as the
first thing that will be executed in your plugin, on the global escope, out of any hook.**

#### Free Plugin example

```php
<?php
$includeFilebRelativePath = '/publishpress/publishpress-instance-protection/include.php';
if (file_exists(__DIR__ . '/vendor' . $includeFilebRelativePath)) {
    require_once __DIR__ . '/vendor' . $includeFilebRelativePath;
} else if (defined('PP_AUTHORS_VENDOR_PATH') && file_exists(PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath)) {
    require_once PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath;
}

if (class_exists('PublishPressInstanceProtection\\Config')) {
    $pluginCheckerConfig = new PublishPressInstanceProtection\Config();
    $pluginCheckerConfig->pluginSlug = 'publishpress-authors';
    $pluginCheckerConfig->pluginName = 'PublishPress Authors';
    $pluginCheckerConfig->pluginFolder = 'publishpress-authors'; // Only required if the folder is different from the slug.

    $pluginChecker = new PublishPressInstanceProtection\InstanceChecker($pluginCheckerConfig);
}
```

### Pro Plugin example

```php
<?php
$includeFilebRelativePath = '/publishpress/publishpress-instance-protection/include.php';
if (file_exists(__DIR__ . '/vendor' . $includeFilebRelativePath)) {
    require_once __DIR__ . '/vendor' . $includeFilebRelativePath;
} else if (defined('PP_AUTHORS_VENDOR_PATH') && file_exists(PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath)) {
    require_once PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath;
}

if (class_exists('PublishPressInstanceProtection\\Config')) {
    $pluginCheckerConfig = new PublishPressInstanceProtection\Config();
    $pluginCheckerConfig->pluginSlug = 'publishpress-authors-pro';
    $pluginCheckerConfig->pluginName = 'PublishPress Authors Pro';
    $pluginCheckerConfig->pluginFolder = 'publishpress-authors-pro'; // Only required if the folder is different from the slug.
    $pluginCheckerConfig->isProPlugin = true;
    $pluginCheckerConfig->freePluginName = 'PublishPress Authors';

    $pluginChecker = new PublishPressInstanceProtection\InstanceChecker($pluginCheckerConfig);
}
```

The only required change is that you need to include two more configurations: `isProPlugin` and `freePluginName`.

### Final example

The final code should looks something like:

```php
$includeFilebRelativePath = '/publishpress/publishpress-instance-protection/include.php';
if (file_exists(__DIR__ . '/vendor' . $includeFilebRelativePath)) {
    require_once __DIR__ . '/vendor' . $includeFilebRelativePath;
} else if (defined('PP_AUTHORS_VENDOR_PATH') && file_exists(PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath)) {
    require_once PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath;
}

if (class_exists('PublishPressInstanceProtection\\Config')) {
    // ....
}

if (! defined('PP_AUTHORS_LOADED')) {
    // include libraries, and dependencies
    // instantiate the plugin, hooks, etc

    define('PP_AUTHORS_LOADED', true);
}
```
