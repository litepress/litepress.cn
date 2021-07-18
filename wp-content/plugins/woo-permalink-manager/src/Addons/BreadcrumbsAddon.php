<?php namespace Premmerce\UrlManager\Addons;

use Premmerce\UrlManager\Admin\Settings;

class BreadcrumbsAddon implements AddonInterface
{

  /**
   * Breadcrumbs
   * @var array
   */
  protected $breadcrumbs = [];

  /**
   * Options
   * @var array
   */
  protected $options = [];

  /**
   * constructor
   */
  public function __construct()
  {
    $options       = get_option( Settings::OPTIONS );
    $this->options = $options;
  }

  /**
   * Is active
   * @return boolean
   */
  public function isActive()
  {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( ! empty( $this->options['breadcrumbs'] ) && is_plugin_active( 'woocommerce/woocommerce.php') ) {
      return true;
    }

    return false;
  }

  /**
   * Init reformat breadcrumbs
   */
  public function init()
  {
    add_filter( 'woocommerce_get_breadcrumb', [ $this, 'createBreadcrumbs' ], 999 );
  }

  /**
   * Init reformat breadcrumbs
   *
   * @param array $crumbs
   *
   * @return array|void
   */
  public function createBreadcrumbs( $crumbs )
  {
    if( ! is_product() && ! is_product_category() ) {
      return $crumbs;
    }

    $this->addCrumb( apply_filters( 'premmerce_permalink_home_breadcrumb_default', __( 'Home', 'premmerce-url-manager' ) ), get_site_url() );

    if( empty( $this->options['br_remove_shop'] ) ) {
      $this->addCrumb( apply_filters( 'premmerce_permalink_shop_breadcrumb_default', __( 'Shop', 'premmerce-url-manager' ) ), get_permalink( wc_get_page_id( 'shop' ) ) );
    }


    if( is_product() ) {

      if ( ! empty( $this->options['product'] ) ) {
        global $post;

        $terms = wc_get_product_terms(
          $post->ID,
          'product_cat',
          array(
            'orderby' => 'parent',
            'order'   => 'DESC',
          )
        );

        if( $terms ) {
          $mainTerm = $terms[0];
          if( $this->options['product'] == 'category_slug' ) {
            $this->addCrumb( $mainTerm->name, get_term_link( $mainTerm ) );
          } elseif( $this->options['product'] == 'hierarchical' ) {
            $this->addCrumbAncestors( $mainTerm->term_id, 'product_cat' );
            $this->addCrumb( $mainTerm->name, get_term_link( $mainTerm ) );
          }
        }

        $this->addCrumb( get_the_title( $post ), get_permalink( $post ) );
      }

    } elseif( is_product_category() ) {

      if ( ! empty( $this->options['category'] ) ) {

        $currentTerm = $GLOBALS['wp_query']->get_queried_object();

        if( $this->options['category'] == 'hierarchical' ) {
          $this->addCrumbAncestors( $currentTerm->term_id, 'product_cat' );
        }

        $this->addCrumb( $currentTerm->name, get_term_link( $currentTerm, 'product_cat' ) );
      }
    }

    if( ! empty( $this->breadcrumbs ) ) {
      return $this->getBreadcrumbs();
    }

    return $crumbs;
  }

  /**
   * Get breadcrumbs
   *
   * @return array
   */
  protected function getBreadcrumbs() {
    return $this->breadcrumbs;
  }

  /**
   * Add crumbs for a term
   *
   * @param int $termId Term ID
   * @param string $taxonomy Taxonomy
   */
  protected function addCrumbAncestors( $termId, $taxonomy )
  {
    $ancestors = get_ancestors( $termId, $taxonomy );
    $ancestors = array_reverse( $ancestors );

    foreach ( $ancestors as $ancestor ) {
      $ancestor = get_term( $ancestor, $taxonomy );

      if ( ! is_wp_error( $ancestor ) && $ancestor ) {
        $this->addCrumb( $ancestor->name, get_term_link( $ancestor ) );
      }
    }
  }

  /**
   * Add a crumb
   *
   * @param string $name Name
   * @param string $link Link
   */
  protected function addCrumb( $name, $link = '' )
  {
    $this->breadcrumbs[] = array(
      wp_strip_all_tags( $name ),
      $link,
    );
  }
}