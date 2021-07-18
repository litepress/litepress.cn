<?php namespace Premmerce\UrlManager;

use Premmerce\UrlManager\Admin\Settings;

class Updater
{

    const CURRENT_VERSION = '2.0';

    const DB_OPTION = 'premmerce_permalink_manager_db_version';

    public function checkForUpdates()
    {
        return $this->compare(self::CURRENT_VERSION);
    }

    private function compare($version)
    {
        $dbVersion = get_option(self::DB_OPTION, 1.1);

        return version_compare($dbVersion, $version, '<');
    }

    public function update()
    {
        if ($this->checkForUpdates()) {
            foreach ($this->getUpdates() as $version => $callback) {
                if ($this->compare($version)) {
                    call_user_func($callback);
                }
            }

            update_option(Settings::OPTION_FLUSH, true);
        }
    }

    public function getUpdates()
    {
        return [
            '2.0' => [$this, 'update2_0'],
        ];
    }

    public function update2_0()
    {

        $options   = get_option('premmerce_url_manager_options', []);
        $options12 = [
            'use_primary_category' => 'on',
        ];

        $wc = get_option('woocommerce_permalinks');

        $showProductCats = false;
        if ( ! empty($wc['product_base'])) {
            $showProductCats = strpos($wc['product_base'], '%product_cat%') !== false;
        }


        if ( ! empty($options['remove_product_base'])) {
            $options12['product'] = 'slug';
            if ($showProductCats) {
                $options12['product'] = 'hierarchical';
            }
        }
        if ( ! empty($options['remove_category_base'])) {
            $options12['category'] = 'hierarchical';
            if ( ! empty($options['remove_category_parent_slugs'])) {
                $options12['category'] = 'slug';

            }
        }

        update_option('premmerce_permalink_manager', $options12);
        delete_option('premmerce_url_manager_options');
        delete_option(Settings::OPTION_DISABLED);
        update_option(self::DB_OPTION, '2.0');
    }

}