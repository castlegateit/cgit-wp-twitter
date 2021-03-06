<?php

/*

Plugin Name: Castlegate IT WP Twitter Feed
Plugin URI: http://github.com/castlegateit/cgit-wp-breadcrumb
Description: Twitter feed plugin for WordPress.
Version: 0.1
Author: Castlegate IT
Author URI: http://www.castlegateit.co.uk/
License: MIT

*/
require_once dirname( __FILE__ ) . '/functions.php';

if (! defined('CGIT_TWITTER_USER')||! defined('CGIT_TWITTER_KEY') ||! defined('CGIT_TWITTER_SECRET') ||! defined('CGIT_TWITTER_TOKEN') ||! defined('CGIT_TWITTER_TOKEN_SECRET')){
    add_action('admin_notices', 'cgit_twitter_notice_constants');
}

if(!file_exists(plugin_dir_path(__FILE__) . "twitteroauth/twitteroauth.php")){
    throw new Exception("Error: Please include twitteroauth at cgit-wp-twitter/twitteroauth/", 1);
}

require_once(plugin_dir_path(__FILE__) . "twitteroauth/twitteroauth.php"); //Path to twitteroauth library

function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}

// Function to get Twitter feed based on user, count, and method
function get_twitter_feed($user, $count) {
    $consumerkey = CGIT_TWITTER_KEY;
    $consumersecret = CGIT_TWITTER_SECRET;
    $accesstoken = CGIT_TWITTER_TOKEN;
    $accesstokensecret = CGIT_TWITTER_TOKEN_SECRET;

    $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

    $feed = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$user."&count=".$count."&exclude_replies=true");

    // Set array to hold tweets
    $tweets = array();

    // Add tweets to array

    //$json = json_decode($raw);
    foreach($feed as $item) {

        $tweets[] = array(
            'user' => isset($item->retweeted_status->user->screen_name) ? $item->retweeted_status->user->screen_name : $item->user->screen_name,
            'name' => isset($item->retweeted_status->user->name) ? $item->retweeted_status->user->name : $item->user->name,
            'text' => $item->text,
            'time' => $item->created_at,
            'image' => isset($item->retweeted_status->user->profile_image_url) ? $item->retweeted_status->user->profile_image_url : $item->user->profile_image_url
        );
    }
    // Assemble output
    $output = '';
    foreach($tweets as $tweet) {
        // Make timestamp
        $time = strtotime($tweet['time']);
        $since = time() - $time;
        $stamp = '';
        if($since < 60) {
            // seconds
            $stamp = 'Less than a minute ago';
        } elseif($since < (60 * 60)) {
            // minutes
            $mins = $since / 60;
            if(round($mins) == 1) {
                $stamp = 'About 1 minute ago';
            } else {
                $stamp = 'About ' . round($mins) . ' minutes ago';
            }
        } elseif($since < (60 * 60 * 24)) {
            // hours
            $hours = $since / (60 * 60);
            if(round($hours) == 1) {
                $stamp = 'About 1 hour ago';
            } else {
                $stamp = 'About ' . round($hours) . ' hours ago';
            }
        } else {
            // days
            $stamp = date('j F', $time);
        }
        // Add links to text
        $text = $tweet['text'];
        $text = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
        $text = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="https://twitter.com/$2" target="_blank">@$2</a>', $text);
        $text = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="https://search.twitter.com/search?q=%23$2" target="_blank">#$2</a>', $text);
        // Write output
        $output .= '<div class="tweet">';
        $output .= '<p>' . $text . '</p> ';
        $output .= '<span>' . $stamp . '</span>';
        $output .= '</div>';
    }
    $output .= '';
    // Return
    return $output;
}

function get_cached_twitter_feed($softLimit = 5)
{
    // General settings
    $user   = CGIT_TWITTER_USER;
    $count  = $softLimit;

    // Server cache settings
    $cache_file = WP_CONTENT_DIR . '/cgit-cache/cgit-wp-twitter/cache['.$softLimit.'].html';
    $cache_time = 600; // 10 minutes

    // Generate output based on settings
    $feed = '';
    // If recent cached version, use that
    if(file_exists($cache_file) && time() - filemtime($cache_file) < $cache_time)
    {
        $feed = file_get_contents($cache_file);
    }
    else
    {
        // Try to get feed from Twitter
        $feed = get_twitter_feed($user, $count, $method);

        // If feed available, use that
        if($feed)
        {
            // Check the directory exists first!
            if (!file_exists(WP_CONTENT_DIR . '/cgit-cache/cgit-wp-twitter'))
            {
                mkdir(WP_CONTENT_DIR . '/cgit-cache/cgit-wp-twitter', 0777, true);
                chmod(WP_CONTENT_DIR . '/cgit-cache/cgit-wp-twitter', 0777 )
            }

            // If there was already a file there but it wasn't a directory, best just knock caching on the head.
            if (is_dir(WP_CONTENT_DIR . '/cgit-cache/cgit-wp-twitter'))
            {
                // Fortunately, there wasn't.
                file_put_contents($cache_file, $feed);
            }
        }
        elseif (file_exists($cache_file))
        {
            // Check for any cached version
            $feed = file_get_contents($cache_file);
        }
    }

    return $feed;
}

function cgit_twitter_feed_shortcode ($atts) {

    $defaults = array(
        'limit'     => 5,
    );

    $atts = shortcode_atts($defaults, $atts);

    return get_cached_twitter_feed($atts['limit']);

}

add_shortcode('twitter_feed', 'cgit_twitter_feed_shortcode');

?>
