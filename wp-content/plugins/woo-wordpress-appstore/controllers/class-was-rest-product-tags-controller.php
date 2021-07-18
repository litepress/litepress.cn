<?php

namespace WCWPAS\Controllers;

use WC_REST_Product_Tags_Controller;
use WP_REST_Server;

class WAS_REST_Product_Tags_Controller extends WC_REST_Product_Tags_Controller
{
    protected $namespace = 'was/v1';
    protected $rest_base = 'products/tags';

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
    }

    public function get_items_permissions_check($request)
    {
        return true;
    }
}
