<?php namespace Premmerce\UrlManager\Admin;

use Premmerce\SDK\V2\FileManager\FileManager;
use Premmerce\UrlManager\Admin\Tabs\Base\TabInterface;

class BundleAndSave implements TabInterface
{

    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
        $this->hooks();
    }

    public function init() {

    }

    public function hooks()
    {
        add_action( 'admin_enqueue_scripts', [$this, 'registerBundlesAssets'], 11 );
    }

    public function render()
    {
        $images = [
            'header_img'     => $this->fileManager->locateAsset('admin/img/premmerce_bundle.jpg'),
            'premmerce_logo' => $this->fileManager->locateAsset('admin/img/premmerce.png'),
            'permalink_img'  => $this->fileManager->locateAsset('admin/img/permalink.png'),
            'filter_img'     => $this->fileManager->locateAsset('admin/img/filter.png'),
            'variants_img'   => $this->fileManager->locateAsset('admin/img/variants.png'),
            'search_img'     => $this->fileManager->locateAsset('admin/img/search.png'),
            'riley_pearcy'   => $this->fileManager->locateAsset('admin/img/riley_pearcy.jpg'),
            'lian_perry'     => $this->fileManager->locateAsset('admin/img/lian_perry.jpg'),
            'rommie_mercer'  => $this->fileManager->locateAsset('admin/img/rommie_mercer.jpg'),
            'svg'            => $this->fileManager->locateAsset('admin/img/svg.svg'),
            'wp_logo'        => $this->fileManager->locateAsset('admin/img/wp.png'),
            'wp_rocket_logo' => $this->fileManager->locateAsset('admin/img/wp-rocket.jpg'),
            'learnwoo_logo'  => $this->fileManager->locateAsset('admin/img/learnwoo.png'),
            'wp_lift_logo'   => $this->fileManager->locateAsset('admin/img/wp-lift.png'),
            'mayor_logo'     => $this->fileManager->locateAsset('admin/img/mayor.png'),
            'managewp_logo'  => $this->fileManager->locateAsset('admin/img/managewp.png'),
            'thanks_img'     => $this->fileManager->locateAsset('admin/img/thanks.png'),
        ];

        //sections on Bundles tab
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/header.php', $images);
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/pricing.php', $images);
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/texts.php', $images);
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/testimonials.php', $images);
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/faq.php', $images);
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/featured-in.php', $images);
        $this->fileManager->includeTemplate('admin/tabs/bundle-and-save/thanks.php', $images);
    }

    /**
     * Register css and js only for Bundles tab
     *
     * @param $page
     */
    public function registerBundlesAssets($page)
    {
        if ($page === 'premmerce_page_premmerce-url-manager-admin') {
            //scripts and styles for bundle tab
            if (isset($_GET['tab']) && $_GET['tab'] === $this->getName()) {
                //css and js for bundle tab
                wp_enqueue_style('premmerce_url_manager_admin_premmerce_style', $this->fileManager->locateAsset('admin/css/premmerce.min.css'));
                wp_enqueue_script(
                    'premmerce_url_manager_admin_bundles_freemius_jquery_for_checkout_script',
                    'https://code.jquery.com/jquery-1.12.4.min.js'
                );
                wp_enqueue_script(
                    'premmerce_url_manager_admin_bundles_freemius_checkout_script',
                    'https://checkout.freemius.com/checkout.min.js'
                );
                wp_enqueue_script(
                    'premmerce_url_manager_admin_bundles_script',
                    $this->fileManager->locateAsset('admin/js/bundles.js')
                );
            }
        }
    }

    public function getLabel()
    {
        return __('Bundle & Save', 'premmerce-url-manager');
    }

    public function getName()
    {
        return 'bundle_and_save';
    }

    public function valid()
    {
        return true;
    }

    public static function premmerce_use_svg_symbol($svg_url, $name, $echo = true)
    {
        $ico = '<use xlink:href="' . $svg_url . '#svg-icon__' . $name . '"></use>';

        if ($echo) {
            echo $ico;
        } else {
            return $ico;
        }
    }
}