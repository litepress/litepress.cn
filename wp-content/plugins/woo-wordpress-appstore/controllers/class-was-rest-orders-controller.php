<?php
namespace WCWPAS\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class WAS_REST_Orders_Controller
{
    protected $namespace = 'was/v1';
    protected $rest_base = 'orders';

    public function register_routes()
    {
        register_rest_route($this->namespace, '/'. $this->rest_base, [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_order'],
                'permission_callback' => function() {
                    return true;
                },
            ]
        );
    }

    public function create_order(WP_REST_Request $request)
    {
        $response = wp_remote_post(sprintf('https://%s/wp-json/wc/v3/orders?consumer_key=%s&consumer_secret=%s', $request->get_header("host"), MALL_WRITE_CK, MALL_WRITE_CS), array('body' => [
            'customer_id' => was_get_user_id(),
            'payment_method' => $request['payment_method'],
            'payment_method_title' => $request['payment_method_title'],
            'line_items' => $request['line_items']
        ]));

        return new WP_REST_Response(json_decode($response['body']), 200);
    }
}
