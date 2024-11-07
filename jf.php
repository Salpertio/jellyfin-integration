<?php
/*
Plugin Name: Jellyfin Integration
Plugin URI: https://github.com/Salpertio
Description: Retrieves and displays currently playing media from Jellyfin.
Version: 2.0
Author: salpertia
Author URI: https://github.com/Salpertio
*/

defined('ABSPATH') or die('Direct script access disallowed.');

// Enqueue scripts and localize the AJAX URL
function jellyfin_enqueue_scripts() {
    wp_enqueue_script('jellyfin-live-updates', plugin_dir_url(__FILE__) . 'js/jellyfin-updates.js', array('jquery'), '1.0.0', true);
    wp_localize_script('jellyfin-live-updates', 'ajaxurl', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'jellyfin_enqueue_scripts');

// Deregister YouTube iframe_api.js globally
function deregister_youtube_iframe_api() {
    global $wp_scripts;

    // Deregister any YouTube-related scripts
    foreach ($wp_scripts->registered as $script) {
        if (strpos($script->src, 'youtube.com/iframe_api') !== false || strpos($script->src, 'youtube.com/www-widgetapi') !== false) {
            wp_deregister_script($script->handle);
            wp_dequeue_script($script->handle);
            error_log("Forcefully removed YouTube script: " . $script->src);
        }
    }
}
add_action('wp_print_scripts', 'deregister_youtube_iframe_api', 100);

// Block YouTube API scripts via JavaScript
function block_youtube_api_scripts() {
    ?>
    <script>
        // Block YouTube's iframe_api.js and www-widgetapi.js if loaded
        document.addEventListener('DOMContentLoaded', function() {
            function blockYouTubeAPIs() {
                let ytScript = document.querySelector('script[src*="youtube.com/iframe_api"]');
                let ytWidgetApi = document.querySelector('script[src*="youtube.com/www-widgetapi"]');

                if (ytScript) {
                    ytScript.remove();  // Remove iframe_api.js if loaded
                    console.log("Blocked YouTube iframe_api.js");
                }

                if (ytWidgetApi) {
                    ytWidgetApi.remove();  // Remove www-widgetapi.js if loaded
                    console.log("Blocked YouTube www-widgetapi.js");
                }
            }

            blockYouTubeAPIs();  // Block initially

            // Check every 2 seconds in case the scripts are injected dynamically
            setInterval(blockYouTubeAPIs, 2000);
        });
    </script>
    <?php
}
add_action('wp_footer', 'block_youtube_api_scripts', 100);

// Register AJAX actions for both logged-in and non-logged-in users
add_action('wp_ajax_fetch_jellyfin_now_playing', 'jellyfin_ajax_handler');
add_action('wp_ajax_nopriv_fetch_jellyfin_now_playing', 'jellyfin_ajax_handler');
add_action('wp_ajax_serve_jellyfin_image', 'serve_jellyfin_image');
add_action('wp_ajax_nopriv_serve_jellyfin_image', 'serve_jellyfin_image');

// Register the shortcode to display now playing info
add_shortcode('jellyfin_now_playing', 'display_jellyfin_now_playing');

// AJAX handler for fetching now playing data
function jellyfin_ajax_handler() {
    // Set headers to prevent caching
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fetch_jellyfin_now_playing();
    wp_send_json_success($output);
    wp_die();
}

// Fetch the now playing data from Jellyfin and construct the HTML output
function fetch_jellyfin_now_playing() {
    $jellyfinServer = esc_url(get_option('jellyfin_server_url'));
    $apiKey = get_option('jellyfin_api_key');
    $userId = get_option('jellyfin_user_id');
    $displayOrder = get_option('jellyfin_display_order', 'artist_first'); // Default to 'artist_first'
    $leftSymbol = get_option('jellyfin_left_symbol', '🎤'); // Default to '🎤'
    $rightSymbol = get_option('jellyfin_right_symbol', '🎤'); // Default to '🎤'
    $enableYouTubeLink = get_option('jellyfin_enable_youtube_link');

    if (empty($jellyfinServer) || empty($apiKey) || empty($userId)) {
        error_log('Jellyfin settings are not configured properly.');
        return 'Jellyfin settings are not configured properly.';
    }

    $url = "{$jellyfinServer}/Sessions?api_key={$apiKey}";

    // Allow mixed content if option is enabled
    if (get_option('jellyfin_allow_mixed_content')) {
        add_filter('https_ssl_verify', '__return_false');
    }

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return 'Error connecting to server';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if there's any playing session
    foreach ($data as $session) {
        if ($session['UserId'] == $userId && !empty($session['NowPlayingItem'])) {
            $nowPlayingItem = $session['NowPlayingItem'];
            $albumId = isset($nowPlayingItem['AlbumId']) ? $nowPlayingItem['AlbumId'] : $nowPlayingItem['Id'];
            $albumArtUrl = admin_url('admin-ajax.php?action=serve_jellyfin_image&item_id=' . urlencode($albumId));
            $sessionId = $session['Id']; // Get the session ID

            $trackName = esc_html($nowPlayingItem['Name']);
            $artistName = esc_html($nowPlayingItem['AlbumArtist']);
            $youtubeSearchUrl = 'https://www.youtube.com/results?search_query=' . urlencode($trackName . ' ' . $artistName);

            ob_start();
            ?>
            <div class="jellyfin-now-playing">
                <div class="jellyfin-session-id" style="display: none;"><?php echo esc_html($sessionId); ?></div>
                <div class="jellyfin-album-cover">
                    <img src="<?php echo esc_url($albumArtUrl); ?>" alt="Album Art">
                </div>
                <div class="jellyfin-track-info">
                    <?php if ($displayOrder === 'artist_first'): ?>
                        <span class="jellyfin-artist-name"><?php echo esc_html($leftSymbol . ' ' . $artistName . ' ' . $rightSymbol); ?></span><br>
                        <span class="jellyfin-track-title"><?php echo esc_html($trackName); ?></span>
                    <?php else: ?>
                        <span class="jellyfin-track-title"><?php echo esc_html($trackName); ?></span><br>
                        <span class="jellyfin-artist-name"><?php echo esc_html($leftSymbol . ' ' . $artistName . ' ' . $rightSymbol); ?></span>
                    <?php endif; ?>
                    <?php if ($enableYouTubeLink == '1'): ?>
                        <br><a href="<?php echo esc_url($youtubeSearchUrl); ?>" target="_blank" rel="noopener noreferrer">Watch on YouTube</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    }

    return 'The webmaster is not listening to music right now.';
}

// Display now playing data via shortcode
function display_jellyfin_now_playing() {
    ob_start();
    echo '<div id="jellyfin-now-playing-container">' . fetch_jellyfin_now_playing() . '</div>';
    return ob_get_clean();
}

// Serve the Jellyfin image securely
function serve_jellyfin_image() {
    $itemId = sanitize_text_field($_GET['item_id']);
    $apiKey = get_option('jellyfin_api_key');
    $jellyfinServer = esc_url(get_option('jellyfin_server_url'));
    $url = "{$jellyfinServer}/Items/{$itemId}/Images/Primary?api_key={$apiKey}";

    // Set headers to prevent caching
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Log the image URL being fetched
    error_log("Fetching image from: $url");

    $imageResponse = wp_remote_get($url);
    if (is_wp_error($imageResponse)) {
        error_log("Error fetching image: " . $imageResponse->get_error_message());
        status_header(404);
        exit('Image not found.');
    }

    $responseCode = wp_remote_retrieve_response_code($imageResponse);
    if ($responseCode != 200) {
        error_log("Image fetch failed with status code: $responseCode, URL: $url");
        status_header(404);
        exit('Image not found.');
    }

    $contentType = wp_remote_retrieve_header($imageResponse, 'content-type');
    if (!$contentType) {
        error_log("Content-Type header missing for URL: $url");
        status_header(404);
        exit('Image not found.');
    }

    header('Content-Type: ' . $contentType);
    echo wp_remote_retrieve_body($imageResponse);
    exit;
}

// Include your CSS styling
function jellyfin_enqueue_styles() {
    wp_enqueue_style('jellyfin-styles', plugin_dir_url(__FILE__) . 'css/jellyfin-styles.css');
}
add_action('wp_enqueue_scripts', 'jellyfin_enqueue_styles');

// Create plugin settings page
function jellyfin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Jellyfin Integration Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('jellyfin-settings-group');
            do_settings_sections('jellyfin-settings-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Jellyfin Server URL</th>
                    <td><input type="text" name="jellyfin_server_url" value="<?php echo esc_url(get_option('jellyfin_server_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="jellyfin_api_key" value="<?php echo esc_attr(get_option('jellyfin_api_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">User ID</th>
                    <td><input type="text" name="jellyfin_user_id" value="<?php echo esc_attr(get_option('jellyfin_user_id')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable YouTube Link</th>
                    <td><input type="checkbox" name="jellyfin_enable_youtube_link" value="1" <?php checked(1, get_option('jellyfin_enable_youtube_link'), true); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Allow Mixed Content</th>
                    <td><input type="checkbox" name="jellyfin_allow_mixed_content" value="1" <?php checked(1, get_option('jellyfin_allow_mixed_content'), true); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Display Order</th>
                    <td>
                        <select name="jellyfin_display_order">
                            <option value="artist_first" <?php selected(get_option('jellyfin_display_order'), 'artist_first'); ?>>Artist Name First</option>
                            <option value="title_first" <?php selected(get_option('jellyfin_display_order'), 'title_first'); ?>>Song Title First</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Left Symbol</th>
                    <td><input type="text" name="jellyfin_left_symbol" value="<?php echo esc_attr(get_option('jellyfin_left_symbol', '🎤')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Right Symbol</th>
                    <td><input type="text" name="jellyfin_right_symbol" value="<?php echo esc_attr(get_option('jellyfin_right_symbol', '🎤')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <h3>How to get your User ID:</h3>
        <p>To get your Jellyfin User ID, you can use the following command in your terminal:</p>
        <pre>
curl -X GET "http://your-jellyfin-server-address:8096/Users" -H "X-Emby-Token: your_api_key"
        </pre>
        <p>Replace <strong>your-jellyfin-server-address</strong> with the address of your Jellyfin server and <strong>your_api_key</strong> with your actual API key. The command will return a list of users with their IDs. Find the ID corresponding to your username.</p>
    </div>
    <?php
}

// Register plugin settings
function jellyfin_register_settings() {
    register_setting('jellyfin-settings-group', 'jellyfin_server_url');
    register_setting('jellyfin-settings-group', 'jellyfin_api_key');
    register_setting('jellyfin-settings-group', 'jellyfin_user_id');
    register_setting('jellyfin-settings-group', 'jellyfin_enable_youtube_link');
    register_setting('jellyfin-settings-group', 'jellyfin_allow_mixed_content');
    register_setting('jellyfin-settings-group', 'jellyfin_display_order');
    register_setting('jellyfin-settings-group', 'jellyfin_left_symbol');
    register_setting('jellyfin-settings-group', 'jellyfin_right_symbol');
}
add_action('admin_menu', 'jellyfin_create_menu');

function jellyfin_create_menu() {
    add_menu_page('Jellyfin Settings', 'Jellyfin', 'administrator', __FILE__, 'jellyfin_settings_page');
    add_action('admin_init', 'jellyfin_register_settings');
}
?>
