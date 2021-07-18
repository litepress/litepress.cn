<?php

namespace WCWPAS\Controllers;

use WC_REST_Products_Controller;
use WC_Product_Vendors_Utils;
use WP_REST_Server;

class WAS_REST_Products_Controller extends WC_REST_Products_Controller
{
    protected $namespace = 'was/v1';
    protected $rest_base = 'products';

    /**
     * Register the routes for products.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_items'),
                    'permission_callback' => array($this, 'get_items_permissions_check'),
                    'args' => $this->get_collection_params(),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_item'),
                    'permission_callback' => array($this, 'create_item_permissions_check'),
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type' => 'integer',
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_item'),
                    'permission_callback' => array($this, 'get_item_permissions_check'),
                    'args' => array(
                        'context' => $this->get_context_param(
                            array(
                                'default' => 'view',
                            )
                        ),
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'update_item'),
                    'permission_callback' => array($this, 'update_item_permissions_check'),
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array($this, 'delete_item'),
                    'permission_callback' => array($this, 'delete_item_permissions_check'),
                    'args' => array(
                        'force' => array(
                            'default' => false,
                            'description' => __('Whether to bypass trash and force deletion.', 'woocommerce'),
                            'type' => 'boolean',
                        ),
                    ),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/batch',
            array(
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'batch_items'),
                    'permission_callback' => array($this, 'batch_items_permissions_check'),
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                ),
                'schema' => array($this, 'get_public_batch_schema'),
            )
        );
    }

    public function __construct() {
        parent::__construct();

        // 使产品查询时支持以销量为依据进行过滤，Woo自带了一个名为popularity的排序字段，字面意思是销量。不过排出来的结果相当的迷惑人。
        // 同时Woo的API限制了只能通过某几个字段排序，实在懒得找自定义方式，于是就拿后面几乎不可能用到的relevance字段改成了查询销量
        add_filter('woocommerce_get_catalog_ordering_args', function ($args, $orderby, $order) {
            if ('include' === $orderby) {
                $args['orderby'] = 'meta_value_num';
                $args['order'] = $order;
                $args['meta_key'] = 'total_sales';
            }

            return $args;
        }, 10, 3);
    }

    public function get_items_permissions_check($request)
    {
        return true;
    }

    protected function prepare_objects_query($request) {
        $args = parent::prepare_objects_query($request);

        if (!empty( $request['vendor'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'wcpv_product_vendors',
                'field'    => 'term_id',
                'terms'    => [$request['vendor']],
            ];
        }

        $args['post_status'] = 'publish';

        return $args;
    }

    public function prepare_object_for_response($object, $request)
    {
        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = $this->get_product_data($object, $context);

        // Add variations to variable products.
        if ($object->is_type('variable') && $object->has_child()) {
            $data['variations'] = $object->get_children();
        }

        // Add grouped products data.
        if ($object->is_type('grouped') && $object->has_child()) {
            $data['grouped_products'] = $object->get_children();
        }

        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, $context);
        $response = rest_ensure_response($data);
        // $response->add_links($this->prepare_links($object, $request));

        return $response;
    }

    protected function get_product_data($product, $context = 'view')
    {
        $img = $this->get_images($product);
        $meta_data = was_meta_serialize($product->get_meta_data());

        $data = [
            'id' => $product->get_id(),
            'name' => $product->get_name($context),
            'slug' => $product->get_slug( $context ),
            'author' => $meta_data['author'],
            'price' => $product->get_price($context),
            'short_description' => $product->get_short_description(),
            'description' => $meta_data['51_default_editor'],
            'change_log' => $meta_data['47_default_editor'],
            'install' => $meta_data['365_default_editor'],
            'faqs' => $meta_data['46_custom_list_faqs'],
            'thumbnail' => empty($img) ? 'https://avatar.ibadboy.net/avatar/'.md5(rand()).'?d=identicon' : $img[0]['src'],
            'images' => $this->get_images($product),
            'banner' => $meta_data['_banner'],
            'sold_by' => WC_Product_Vendors_Utils::get_sold_by_link($product->get_id())['name'],
            'average_rating' => 'view' === $context ? wc_format_decimal($product->get_average_rating(), 2) : $product->get_average_rating($context),
            'total_count' => $product->get_total_sales($context),
            'date_modified' => $this->get_date_diff(wc_rest_prepare_date_response($product->get_date_modified($context))),
            'spinning' => false,
            'no_auth_download' => $meta_data['_no_auth_download'],
            'download_url' => $meta_data['_download_url'],
            'app_version' => $meta_data['_api_new_version'],
            'app_wp_version_required' => $meta_data['_api_version_required'],
            'app_wp_tested_up_to' => $meta_data['_api_tested_up_to'],
            'app_wp_requires_php' => $meta_data['_api_requires_php'],
            'preview_url' => $meta_data['preview_url'],
            // 'meta_data' => $product->get_meta_data(),
            /*TODO:这里这个更新日期得去api manage那读取，wooc这个修改时间就连提交个评论都会跟着更新*/
            // 'meta_data' => $product->get_meta_data(),
            /*
            'slug'                  => $product->get_slug( $context ),
            'permalink'             => $product->get_permalink(),
            'date_created'          => wc_rest_prepare_date_response( $product->get_date_created( $context ), false ),
            'date_created_gmt'      => wc_rest_prepare_date_response( $product->get_date_created( $context ) ),
            'date_modified'         => wc_rest_prepare_date_response( $product->get_date_modified( $context ), false ),
            'date_modified_gmt'     => wc_rest_prepare_date_response( $product->get_date_modified( $context ) ),
            'type'                  => $product->get_type(),
            'status'                => $product->get_status( $context ),
            'featured'              => $product->is_featured(),
            'catalog_visibility'    => $product->get_catalog_visibility( $context ),
            'description'           => 'view' === $context ? wpautop( do_shortcode( $product->get_description() ) ) : $product->get_description( $context ),
            'short_description'     => 'view' === $context ? apply_filters( 'woocommerce_short_description', $product->get_short_description() ) : $product->get_short_description( $context ),
            'sku'                   => $product->get_sku( $context ),
            'price'                 => $product->get_price( $context ),
            'regular_price'         => $product->get_regular_price( $context ),
            'sale_price'            => $product->get_sale_price( $context ) ? $product->get_sale_price( $context ) : '',
            'date_on_sale_from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from( $context ), false ),
            'date_on_sale_from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from( $context ) ),
            'date_on_sale_to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to( $context ), false ),
            'date_on_sale_to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to( $context ) ),
            'price_html'            => $product->get_price_html(),
            'on_sale'               => $product->is_on_sale( $context ),
            'purchasable'           => $product->is_purchasable(),
            'total_sales'           => $product->get_total_sales( $context ),
            'virtual'               => $product->is_virtual(),
            'downloadable'          => $product->is_downloadable(),
            'downloads'             => $this->get_downloads( $product ),
            'download_limit'        => $product->get_download_limit( $context ),
            'download_expiry'       => $product->get_download_expiry( $context ),
            'external_url'          => $product->is_type( 'external' ) ? $product->get_product_url( $context ) : '',
            'button_text'           => $product->is_type( 'external' ) ? $product->get_button_text( $context ) : '',
            'tax_status'            => $product->get_tax_status( $context ),
            'tax_class'             => $product->get_tax_class( $context ),
            'manage_stock'          => $product->managing_stock(),
            'stock_quantity'        => $product->get_stock_quantity( $context ),
            'in_stock'              => $product->is_in_stock(),
            'backorders'            => $product->get_backorders( $context ),
            'backorders_allowed'    => $product->backorders_allowed(),
            'backordered'           => $product->is_on_backorder(),
            'sold_individually'     => $product->is_sold_individually(),
            'weight'                => $product->get_weight( $context ),
            'dimensions'            => array(
                'length' => $product->get_length( $context ),
                'width'  => $product->get_width( $context ),
                'height' => $product->get_height( $context ),
            ),
            'shipping_required'     => $product->needs_shipping(),
            'shipping_taxable'      => $product->is_shipping_taxable(),
            'shipping_class'        => $product->get_shipping_class(),
            'shipping_class_id'     => $product->get_shipping_class_id( $context ),
            'reviews_allowed'       => $product->get_reviews_allowed( $context ),
            'average_rating'        => 'view' === $context ? wc_format_decimal( $product->get_average_rating(), 2 ) : $product->get_average_rating( $context ),
            'rating_count'          => $product->get_rating_count(),
            'related_ids'           => array_map( 'absint', array_values( wc_get_related_products( $product->get_id() ) ) ),
            'upsell_ids'            => array_map( 'absint', $product->get_upsell_ids( $context ) ),
            'cross_sell_ids'        => array_map( 'absint', $product->get_cross_sell_ids( $context ) ),
            'parent_id'             => $product->get_parent_id( $context ),
            'purchase_note'         => 'view' === $context ? wpautop( do_shortcode( wp_kses_post( $product->get_purchase_note() ) ) ) : $product->get_purchase_note( $context ),
            'categories'            => $this->get_taxonomy_terms( $product ),
            'tags'                  => $this->get_taxonomy_terms( $product, 'tag' ),
            'images'                => $this->get_images( $product ),
            'attributes'            => $this->get_attributes( $product ),
            'default_attributes'    => $this->get_default_attributes( $product ),
            'variations'            => array(),
            'grouped_products'      => array(),
            'menu_order'            => $product->get_menu_order( $context ),
            'meta_data'             => $product->get_meta_data(),
            */
        ];

        return $data;
    }

    /**
     * 计算传入的时间与当前时间的时间差，返回的格式为：x小时、x天、x周、x月、x年）
     * @param $datetime
     * @return string
     */
    private function get_date_diff($datetime)
    {
        $diff_date = explode('|', date('Y|m|d|H|i|s', time() - strtotime($datetime)));

        if ($diff_date[0] - 1970 > 0) {
            return (string)($diff_date[0] - 1970) . '年';
        } elseif ($diff_date[1] - 1 > 0) {
            return (string)($diff_date[1] - 1) . '月';
        } elseif ($diff_date[2] - 1 > 0) {
            return (string)($diff_date[2] - 1) . '日';
        } elseif ($diff_date[3] > 0) {
            return (string)((int)$diff_date[3]) . '小时';
        } elseif ($diff_date[4] > 0) {
            return (string)((int)$diff_date[4]) . '分钟';
        } else {
            return (string)((int)$diff_date[5]) . '秒';
        }
    }

    private function append_product_sorting_table_join( $sql ) {
        global $wpdb;

        if ( ! strstr( $sql, 'wc_product_meta_lookup' ) ) {
            $sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
        }
        return $sql;
    }
}
