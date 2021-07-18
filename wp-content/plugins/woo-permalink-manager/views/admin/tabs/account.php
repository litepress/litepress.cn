<?php

if ( ! defined('WPINC')) {
    die;
}

if (function_exists('premmerce_wpm_fs') && premmerce_wpm_fs()->is_registered()) {
    premmerce_wpm_fs()->add_filter('hide_account_tabs', '__return_true');
    premmerce_wpm_fs()->_account_page_load();
    premmerce_wpm_fs()->_account_page_render();
}
