<?php
// Basic stubs for WordPress functions used during tests.
if (!function_exists('add_action')) {
    function add_action(...$args) {}
}
if (!function_exists('add_shortcode')) {
    function add_shortcode(...$args) {}
}
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) { return ''; }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { return $path; }
}
if (!function_exists('wp_localize_script')) { function wp_localize_script(...$args) {} }
if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script(...$args) {} }
if (!function_exists('wp_enqueue_style')) { function wp_enqueue_style(...$args) {} }
if (!function_exists('add_filter')) { function add_filter(...$args) {} }
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
if (!function_exists('get_option')) {
    function get_option($name, $default = null) {
        $options = [
            'jellyfin_server_url' => 'http://example.com',
            'jellyfin_api_key' => 'key',
            'jellyfin_user_id' => '1',
            'jellyfin_display_order' => 'artist_first',
            'jellyfin_left_symbol' => '🎤',
            'jellyfin_right_symbol' => '🎤',
            'jellyfin_enable_youtube_link' => 0,
            'jellyfin_allow_mixed_content' => 0,
        ];
        return $options[$name] ?? $default;
    }
}
if (!function_exists('esc_url')) { function esc_url($url) { return $url; } }
if (!function_exists('is_wp_error')) { function is_wp_error($thing) { return false; } }
if (!function_exists('wp_remote_get')) { function wp_remote_get($url) { return ['body' => json_encode([])]; } }
if (!function_exists('wp_remote_retrieve_body')) { function wp_remote_retrieve_body($response) { return $response['body']; } }
if (!function_exists('wp_remote_retrieve_response_code')) { function wp_remote_retrieve_response_code($response) { return 200; } }
if (!function_exists('wp_remote_retrieve_header')) { function wp_remote_retrieve_header($response, $header) { return 'image/png'; } }
if (!function_exists('checked')) { function checked($checked, $current = true, $echo = true) {} }
if (!function_exists('selected')) { function selected($selected, $current = true, $echo = true) {} }
if (!function_exists('status_header')) { function status_header($code) {} }
if (!function_exists('wp_die')) { function wp_die($message = '') {} }
if (!function_exists('wp_send_json_success')) { function wp_send_json_success($data) { return $data; } }
require_once dirname(__DIR__) . '/jf.php';
