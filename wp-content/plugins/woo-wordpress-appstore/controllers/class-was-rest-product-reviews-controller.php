<?php
/**
 * REST API Product Reviews Controller
 *
 * Handles requests to /products/reviews.
 *
 * @package WooCommerce\RestApi
 * @since   3.5.0
 */
namespace WCWPAS\Controllers;

use WC_REST_Product_Reviews_Controller;
use WP_REST_Server;

class WAS_REST_Product_Reviews_Controller extends WC_REST_Product_Reviews_Controller
{

    protected $namespace = 'was/v1';
    protected $rest_base = 'products/reviews';

    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->rest_base, array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
            )
        );
    }

    public function prepare_item_for_response($review, $request)
    {
        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = array();
        $data['id'] = (int)$review->comment_ID;
        $data['datetime'] = str_replace('T', ' ',wc_rest_prepare_date_response($review->comment_date));
        $data['author'] = $review->comment_author;
        $data['avatar'] = rest_get_avatar_urls($review->comment_author_email)['24'];
        $data['rating'] = (int)get_comment_meta($review->comment_ID, 'rating', true);
        $data['content'] = 'view' === $context ? strip_tags(wpautop($review->comment_content)) : strip_tags($review->comment_content);

        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, $context);

        $response = rest_ensure_response($data);

        return $response;
    }

}
