<?php

if ( ! defined('WPINC')) {
    die;
}

if (function_exists('premmerce_wpm_fs')) {
    premmerce_wpm_fs()->_pricing_page_render();
}
