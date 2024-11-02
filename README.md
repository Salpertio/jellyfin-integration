Jellyfin Integration
Jellyfin Integration Plugin for WordPress

The Jellyfin Integration Plugin for WordPress allows you to display the currently playing media of a specific Jellyfin user on your WordPress site using a simple shortcode. Built with PHP, CSS, and JavaScript, this plugin is easy to install and configure.

Foreword
This plugin was created with the assistance of GPT. The efforts of our LLM overlords have been instrumental in its development.

Features
Now Playing Display: Show the currently playing media of a specific Jellyfin user.
YouTube Search Link: Optional link to search for the currently playing media on YouTube.
Mixed Content Support: Handles different protocol settings between Jellyfin and WordPress.
Customizable Settings: Easily set API key, server URL, and user ID.
<details> <summary>Optional Features</summary>
YouTube Search Link

You can enable a YouTube search link for the currently playing media. This link searches for the song and artist on YouTube.

Go to Settings > Jellyfin in your WordPress dashboard.
Check the box for Enable YouTube Link.
Mixed Content

If your Jellyfin server is over HTTP and your WordPress site over HTTPS, enable mixed content to avoid issues:

Go to Settings > Jellyfin.
Check the box for Allow Mixed Content.
</details>
Security
Securing the API Key

The plugin uses AJAX to securely fetch data without exposing the API key client-side. Here’s why:

Server-Side Requests: AJAX lets the server handle sensitive requests (like the API key) without exposing them in the client’s browser.
Minimized Exposure: The API key is only used server-side and isn’t included in HTML or JavaScript sent to the client.
<details> <summary>Installation</summary>
Download the Plugin:

Zip the plugin files into jellyfin-integration.zip.
Install the Plugin:

Go to your WordPress dashboard.
Navigate to Plugins > Add New.
Click Upload Plugin and upload jellyfin-integration.zip.
Click Install Now, then Activate.
Configure the Plugin:

After activation, go to Settings > Jellyfin.
Enter your Jellyfin server URL, API key, and user ID.
</details> <details> <summary>Usage</summary>
Use the following shortcode to display the now playing information:

plaintext
Copy code
[jellyfin_now_playing]
Getting Your User ID
To retrieve your Jellyfin User ID, authenticate with your API key using this command:

bash
Copy code
curl -X GET "http://your-jellyfin-server-address:8096/Users" -H "X-Emby-Token: your_api_key"
Replace your-jellyfin-server-address with your Jellyfin server’s address and your_api_key with your actual API key. Find the ID corresponding to your username in the returned list.

</details> <details> <summary>Known Issues</summary>
Some album covers were missing (fixed in 1.5).
</details>
License: This plugin is released under GPL v3, so feel free to modify or extend it.

We hope you find this plugin useful! We look forward to your feedback and suggestions for future improvements. (P.S. Not planning to go full-time with this one!)
