<?php

namespace Premmerce\UrlManager\Frontend;

use  Premmerce\UrlManager\Admin\Settings ;
use  WP_Post ;
use  WP_Term ;
/**
 * Class Frontend
 *
 * @package Premmerce\UrlManager
 */
class Frontend
{
    const  WOO_PRODUCT = 'product' ;
    const  WOO_CATEGORY = 'product_cat' ;
    /**
     * Options
     * @var array
     */
    protected  $options = array() ;
    /**
     * Frontend constructor.
     */
    public function __construct()
    {
        $options = get_option( Settings::OPTIONS );
        $this->options = $options;
        if ( !empty($options['product']) || !empty($options['sku']) ) {
            add_action( 'request', [ $this, 'replaceRequest' ], 11 );
        }
        if ( !empty($options['canonical']) ) {
            add_action( 'wp_head', [ $this, 'addCanonical' ] );
        }
        #/premmerce_clear
        $isGetParamUrlFormat = apply_filters( 'wpml_setting', 0, 'language_negotiation_type' ) == '3';
        if ( class_exists( 'SitePress' ) && $isGetParamUrlFormat ) {
            add_filter( 'icl_ls_languages', [ $this, 'modifyWpmlLanguageSwitcher' ], 20 );
        }
    }
    
    /**
     * Modify wpml Language Switcher
     *
     * @param array $languages
     *
     * @return array
     */
    public function modifyWpmlLanguageSwitcher( $languages )
    {
        global  $sitepress ;
        foreach ( $languages as $key => $val ) {
            $switcherLink = $val['url'];
            $parsedLink = parse_url( $switcherLink );
            if ( isset( $parsedLink['query'] ) ) {
                $switcherLink = str_replace( '?' . $parsedLink['query'], '', $switcherLink );
            }
            
            if ( $key != $sitepress->get_default_language() ) {
                $languages[$key]['url'] = $switcherLink . '?lang=' . $key;
            } else {
                $languages[$key]['url'] = $switcherLink;
            }
        
        }
        return $languages;
    }
    
    /**
     * Replace request if product found
     *
     * @param array $request
     *
     * @return array
     */
    public function replaceRequest( $request )
    {
        global  $wp, $wpdb ;
        if ( $this->checkIfWooCategoryExists( $request ) ) {
            return $request;
        }
        $url = $wp->request;
        
        if ( !empty($url) ) {
            $url = explode( '/', $url );
            $slug = array_pop( $url );
            $replace = [];
            
            if ( $slug === 'feed' ) {
                $replace['feed'] = $slug;
                $slug = array_pop( $url );
            }
            
            
            if ( $slug === 'amp' ) {
                $replace['amp'] = $slug;
                $slug = array_pop( $url );
            }
            
            $commentsPosition = strpos( $slug, 'comment-page-' );
            
            if ( $commentsPosition === 0 ) {
                $replace['cpage'] = substr( $slug, strlen( 'comment-page-' ) );
                $slug = array_pop( $url );
            }
            
            $sql = "SELECT COUNT(ID) as count_id FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s";
            $query = $wpdb->prepare( $sql, [ $slug, self::WOO_PRODUCT ] );
            $num = intval( $wpdb->get_var( $query ) );
            
            if ( $num > 0 ) {
                $replace['page'] = '';
                $replace['post_type'] = self::WOO_PRODUCT;
                $replace['product'] = $slug;
                $replace['name'] = $slug;
                return $replace;
            }
        
        }
        
        return $request;
    }
    
    protected function removeSuffix( $url, $suffix )
    {
        $length = mb_strlen( $suffix );
        if ( $length == 0 ) {
            return true;
        }
        // Ends with
        if ( substr( $url, -$length ) === $suffix ) {
            $url = str_replace( $suffix, '', $url );
        }
        return $url;
    }
    
    public function addCanonical()
    {
        //avoid canonicals duplication
        
        if ( !defined( 'WPSEO_VERSION' ) && !get_queried_object() instanceof WP_Post ) {
            $canonical = apply_filters( 'premmerce_permalink_manager_canonical', $this->getCanonical() );
            if ( !empty($canonical) ) {
                echo  '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n" ;
            }
        }
    
    }
    
    private function getCanonical( $useCommentsPagination = false )
    {
        global  $wp_rewrite ;
        $qo = get_queried_object();
        $canonical = null;
        
        if ( $qo instanceof WP_Term ) {
            $canonical = get_term_link( $qo );
            $paged = get_query_var( 'paged' );
            if ( $paged > 1 ) {
                $canonical = trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . $paged;
            }
        } elseif ( $qo instanceof WP_Post ) {
            $canonical = get_permalink( $qo );
            
            if ( $useCommentsPagination ) {
                $page = get_query_var( 'cpage' );
                if ( $page > 1 ) {
                    $canonical = trailingslashit( $canonical ) . $wp_rewrite->comments_pagination_base . '-' . $page;
                }
            }
        
        }
        
        if ( $canonical ) {
            return user_trailingslashit( $canonical );
        }
    }
    
    /**
     * Find current slug by product SKU
     *
     * @param string $sku
     *
     * @return string
     */
    protected function findSlugBySku( $sku )
    {
        global  $wpdb ;
        $sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value = %s AND meta_key = '_sku'";
        $query = $wpdb->prepare( $sql, [ $sku ] );
        $skuId = $wpdb->get_row( $query, ARRAY_A );
        
        if ( isset( $skuId['post_id'] ) ) {
            $postSlug = get_post_field( 'post_name', $skuId['post_id'] );
            if ( $postSlug != '' ) {
                return $postSlug;
            }
        }
        
        return $sku;
    }
    
    /**
     * Check if woocommerce category exists in request
     *
     * @param array $request
     *
     * @return boolean
     */
    protected function checkIfWooCategoryExists( $request )
    {
        if ( !empty($this->options['category']) && in_array( $this->options['product'], [ 'category_slug', 'hierarchical' ] ) ) {
            if ( array_key_exists( self::WOO_CATEGORY, $request ) ) {
                return true;
            }
        }
        return false;
    }

}