jQuery(document).ready(function($) {
    let currentSessionId = '';
    let currentImageUrl = '';
    let currentTrackName = '';  // Track the current song

    // Remove YouTube's iframe_api.js and www-widgetapi.js if loaded
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

    function fetchNowPlaying() {
        console.log("Fetching now playing data..."); // Debugging information

        $.ajax({
            url: ajaxurl.ajax_url + '?_=' + new Date().getTime(),  // Append timestamp to prevent caching
            type: 'post',
            data: {
                action: 'fetch_jellyfin_now_playing'
            },
            cache: false,  // Disable caching in jQuery
            success: function(response) {
                console.log("AJAX response:", response); // Debugging information
                if (response.success) {
                    // Extract the session ID, image URL, and track name from the response data
                    let newSessionId = $(response.data).find('.jellyfin-session-id').text();
                    let newImageUrl = $(response.data).find('.jellyfin-album-cover img').attr('src');
                    let newTrackName = $(response.data).find('.jellyfin-track-title').text();  // Get the track name

                    // Check if the session ID, image URL, or track name has changed
                    if (newSessionId !== currentSessionId || newImageUrl !== currentImageUrl || newTrackName !== currentTrackName) {
                        currentSessionId = newSessionId;
                        currentImageUrl = newImageUrl;
                        currentTrackName = newTrackName;  // Update track name
                        $('#jellyfin-now-playing-container').html(response.data);  // Update the HTML with new data
                    }
                } else {
                    console.log("Error: " + response.data); // Debugging information
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX error:", error); // Debugging information
            }
        });

        blockYouTubeAPIs();  // Ensure YouTube APIs are blocked on each fetch
    }

    fetchNowPlaying();
    setInterval(fetchNowPlaying, 15000); // Refresh every 15 seconds (15,000 milliseconds)
});
