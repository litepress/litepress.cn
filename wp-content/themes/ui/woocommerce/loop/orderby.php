<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<section class="woo-ordering">
    <div class="f-sort">
            <!--<a href="javascript:;" class="sort_menu_order"
               onclick="Url.updateSearchParam('orderby', 'menu_order');window.location.href=Url.getLocation();"
               title="默认排序" rel="menu_order"><span class="fs-tit">综合</span><em class="fs-down"><i class="fas fa-long-arrow-alt-down"></i></em></a>-->
            <a href="javascript:;" class="sort_popularity"
                   onclick="Url.updateSearchParam('orderby', 'popularity');window.location.href=Url.getLocation();"
                   title="按销量排序" rel="popularity"><span class="fs-tit">销量</span><em class="fs-down"><i
                                class="fas fa-long-arrow-alt-down"></i></em></a>
            <a href="javascript:;" class="sort_rating"
                   onclick="Url.updateSearchParam('orderby', 'rating');window.location.href=Url.getLocation();"
                   title="按好评度排序" rel="rating"><span class="fs-tit">好评度</span><em class="fs-down"><i
                                class="fas fa-long-arrow-alt-down"></i></em></a>
            <a href="javascript:;" class="sort_date"
                   onclick="Url.updateSearchParam('orderby', 'date');window.location.href=Url.getLocation();"
                   title="按最新内容排序" rel="date"><span class="fs-tit">新品</span><em class="fs-down"><i
                                class="fas fa-long-arrow-alt-down"></i></em></a>
            <a href="javascript:;" class="sort_price"
                   onclick="Url.updateSearchParam('orderby', 'price');window.location.href=Url.getLocation();"
                   title="按价格从低到高排序" rel="price"><span class="fs-tit">价格</span><em class="fs-up"><i
                                class="fad fa-sort-amount-up"></i>
                        <!--<i class="fas fa-caret-down"></i>--></em></a>
            <a href="javascript:;" class="sort_price-desc"
                   onclick="Url.updateSearchParam('orderby', 'price-desc');window.location.href=Url.getLocation();"
                   title="按价格从高到低排序" rel="price-desc"><span class="fs-tit">价格</span><em class="fs-down">
                        <!--<i class="fas fa-caret-up"></i>--><i class="fad fa-sort-amount-down"></i></em></a>
    </div>
  
  

