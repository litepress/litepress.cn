<?php namespace Premmerce\UrlManager\Addons;

class YoastBreadcrumbsAddon extends BreadcrumbsAddon
{
  /**
   * Is active
   * @return boolean
   */
  public function isActive()
  {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( ! empty( $this->options['breadcrumbs'] ) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
      return true;
    }

    return false;
  }

  /**
   * Init reformat breadcrumbs
   */
  public function init()
  {
    add_filter( 'wpseo_breadcrumb_links', [ $this, 'createBreadcrumbs' ], 999 );
  }

  /**
   * Get breadcrumbs
   *
   * @return array
   */
  public function getBreadcrumbs()
  {
    return array_map( [ $this, 'reformatBreadcrumbs' ], $this->breadcrumbs );
  }

  /**
   * Reformat breadcrumbs
   *
   * @return array
   */
  protected function reformatBreadcrumbs( $arr )
  {
    return [
      'text' => $arr[0],
      'url'  => $arr[1],
    ];
  }
}