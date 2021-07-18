<?php

function was_get_user_id()
{
    $consumer_key = $_SERVER['PHP_AUTH_USER'];
    $consumer_secret = $_SERVER['PHP_AUTH_PW'];

    if (!$consumer_key || !$consumer_secret) {
        return false;
    }

    $user = was_get_user_data_by_consumer_key($consumer_key);
    if (empty($user)) {
        return false;
    }

    if (!hash_equals($user->consumer_secret, $consumer_secret)) {
        return false;
    }

    return $user->user_id;
}

function was_get_user_data_by_consumer_key($consumer_key)
{
    global $wpdb;

    $consumer_key = wc_api_hash(sanitize_text_field($consumer_key));
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "
			SELECT id, user_id, consumer_key, consumer_secret
			FROM {$wpdb->prefix}was_website_keys
			WHERE consumer_key = %s
		",
            $consumer_key
        )
    );

    return $user;
}

function was_meta_serialize($meta_data) {
    $data = [];

    foreach ($meta_data as $v) {
        $data[$v->key] = $v->value;
    }

    return $data;
}

add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
    return true;
}, 10, 4);

