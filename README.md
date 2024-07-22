# jellyfin-integration
Jellyfin Integration Plugin

Description

The Jellyfin Integration Plugin for WordPress allows you to display the currently playing media of a specific Jellyfin user on your WordPress site using a simple shortcode. This plugin is built using PHP, CSS, and JavaScript and is designed to be easy to install and configure.
Foreword

This plugin was created with the assistance of GPT (Generative Pre-trained Transformer). The efforts and wisdom of our LLM (Language Model) overlords have been instrumental in its development.

Features

    Display now playing information of a specific Jellyfin user.
    Optional YouTube search link for the currently playing media.
    Support for mixed content to handle different protocol settings.
    Customizable settings for API key, server URL, and user ID.

Installation

    Download the Plugin:
        Download the plugin files and zip them into a folder named jellyfin-integration.zip.

    Install the Plugin:
        Go to your WordPress dashboard.
        Navigate to Plugins > Add New.
        Click on Upload Plugin and upload the jellyfin-integration.zip file.
        Click on Install Now and then Activate.

    Configure the Plugin:
        After activation, go to Settings > Jellyfin in your WordPress dashboard.
        Enter your Jellyfin server URL, API key, and user ID.

Usage

Use the following shortcode to display the now playing information on your WordPress site:

[jellyfin_now_playing]

Getting Your User ID

To get your Jellyfin User ID, you need to authenticate against the Jellyfin server with your API key. Here is the command to retrieve the user ID:

curl -X GET "http://your-jellyfin-server-address:8096/Users" -H "X-Emby-Token: your_api_key"

Replace your-jellyfin-server-address with the address of your Jellyfin server and your_api_key with your actual API key. The command will return a list of users with their IDs. Find the ID corresponding to your username.

Optional Features
YouTube Search Link

You can enable a YouTube search link for the currently playing media. This link will search for the song and artist on YouTube. To enable this feature:

    Go to Settings > Jellyfin.
    Check the box for Enable YouTube Link.

Mixed Content

If you are accessing your Jellyfin server over HTTP and your WordPress site over HTTPS, you might encounter issues due to mixed content. You can allow mixed content by enabling this feature:

    Go to Settings > Jellyfin.
    Check the box for Allow Mixed Content.

Security

Securing the API Key

To prevent the API key from being exposed client-side, the plugin uses AJAX to fetch data securely from the server. Here's why using AJAX is more secure:

    Server-Side Requests: AJAX allows the server to handle requests that require sensitive information, such as the API key. This prevents the API key from being exposed in the client's browser.

    Minimized Exposure: By using AJAX, the API key is only used on the server side and is never included in the HTML or JavaScript that is sent to the client's browser.


issues...
right now only certain album covers will show up (clueless as to why) something to do with the ones that work are cached already (i cleared the cache, weirdge) 🧠

tried to add an auto-get user id to settings. (refused to get user, doesnt exist) :ok buddy:🃏

p.s HELLO INTERNET 

its on GPL v3 so feel free to add or migrate idrc. 


We hope you find this plugin useful and look forward to your feedback and suggestions for future improvements.
p.s. definitely not going fulltime with this one holy hell.
