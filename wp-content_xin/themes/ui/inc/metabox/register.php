<?php

namespace WCY\Inc\MetaBox;

require_once 'class-sidebar.php';
require_once 'class-app-release.php';

$sidebar = new Sidebar();
$sidebar->register_hook();

$app_release = new App_Release();
$app_release->register_hook();
