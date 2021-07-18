<?php

namespace WCWPAS\Src;

class WAS_Auth
{

    public function __construct()
    {
        // Add query vars.
        add_filter('query_vars', [$this, 'add_query_vars'], 0);

        // Register auth endpoint.
        add_action('init', [__CLASS__, 'add_endpoint'], 0);

        // Handle auth requests.
        add_action('template_redirect', [$this, 'handle_auth_requests']);
    }

    public static function add_endpoint()
    {
        add_rewrite_rule('^was-auth/v([1]{1})/(.*)?', 'index.php?was-auth-version=$matches[1]&was-auth-route=$matches[2]', 'top');
    }

    public function handle_auth_requests()
    {
        $route = get_query_var('was-auth-route');

        if ('login' === $route) {
            if (is_user_logged_in()) {
                wp_redirect(
                    $this->build_url(wp_unslash($_REQUEST), 'authorize')
                );
            }
            $this->login_page();
            exit;
        } elseif ('authorize' === $route) {
            if (!is_user_logged_in()) {
                wp_redirect(
                    $this->build_url(wp_unslash($_REQUEST), 'login')
                );
            }
            $this->authorize_page();
            exit;
        } elseif ('access_granted' === $route) {
            $this->access_granted();
            exit;
        }
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'was-auth-version';
        $vars[] = 'was-auth-route';
        return $vars;
    }

    private function login_page()
    {
        require_once WAS_ROOT_PATH . '/templates/auth/form-login.php';
    }

    private function authorize_page()
    {
        // 这些看似未被引用的变量实际上是在模板中引用的
        $app_name = wc_clean(wp_unslash($_GET['app_name']));
        $user = get_currentuserinfo();
        $logout_url = wp_logout_url(home_url());
        $return_url = wc_clean(wp_unslash($_GET['return_url']));
        $granted_url = wp_nonce_url($this->build_url(wp_unslash($_REQUEST), 'access_granted'), 'was_auth_grant_access', 'was_auth_nonce');
        require_once WAS_ROOT_PATH . '/templates/auth/form-grant-access.php';
    }

    private function access_granted()
    {
        $user = get_currentuserinfo();

        if (empty($user) || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['was_auth_nonce'])), 'was_auth_grant_access')) {
            wp_die('非法请求');
        }

        global $wpdb;

        $website = '';
        $website_array = explode('/', $this->get_formatted_url($_GET['callback_url']));
        for ($i = 0; $i < 3; $i++) {
            $website .= $website_array[$i] .'/';
        }

        $res = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'was_website_keys WHERE user_id=%d,website=%s;', [$user->ID, $website]));
        $is_exist = false;
        foreach ($res as $v) {
            $is_exist = true;
        }

        if ($is_exist) {
            wp_die('当前站点已经被授权');
        }

        $consumer_key = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $wpdb->insert(
            $wpdb->prefix . 'was_website_keys',
            [
                'user_id' => $user->ID,
                'website' => $website,
                'consumer_key' => wc_api_hash($consumer_key),
                'consumer_secret' => $consumer_secret
            ], [
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );

        wp_redirect(
            esc_url_raw(
                add_query_arg([
                    'consumer_key' => $consumer_key,
                    'consumer_secret' => $consumer_secret
                ],
                    $this->get_formatted_url($_GET['return_url'])
                )
            )
        );
    }

    private function build_url($data, $endpoint)
    {
        $url = wc_get_endpoint_url('was-auth/v1', $endpoint, home_url('/'));

        return add_query_arg(
            array(
                'app_name' => wc_clean($data['app_name']),
                'user_id' => wc_clean($data['user_id']),
                'return_url' => rawurlencode($this->get_formatted_url($data['return_url'])),
                'callback_url' => rawurlencode($this->get_formatted_url($data['callback_url'])),
            ), $url
        );
    }

    private function get_formatted_url($url)
    {
        $url = urldecode($url);

        if (!strstr($url, '://')) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    private function post_consumer_data($consumer_data, $url)
    {
        $params = array(
            'body' => wp_json_encode($consumer_data),
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            ),
        );

        $response = wp_safe_remote_post(esc_url_raw($url), $params);

        if (is_wp_error($response)) {
            return false;
        }

        return true;
    }

}
