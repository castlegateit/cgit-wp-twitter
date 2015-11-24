# Castlegate IT WP Twitter Feed #

Castlegate IT WP Twitter Feed is a Twitter feed plugin for WordPress. It provides functions for displaying a Facebook feed on a page, supporting caching. It requires at least PHP 5.3 and the Twitter oAuth resources.

## Basic usage ##

The plugin requires some setup. In wp-config, the following constants must be defined:

*CGIT_TWITTER_USER

*CGIT_TWITTER_KEY

*CGIT_TWITTER_SECRET

*CGIT_TWITTER_TOKEN

*CGIT_TWITTER_TOKEN_SECRET


You can use the test tokens I set up for this purpose or set up your own app on twitter.
(Ask me for test tokens, giving out our secret keys is a bad idea.)

However, the important one is our user, which is the feed it will display:

CGIT_TWITTER_USER

This needs to be set to the 'Screen Name' of the profile to display (not necessarily their @username.)
For example, we at CGIT are "@castlegateIT" and our screen name is 'CastlegateIT'.
Jollydays are @JollydaysCamping but their screen name is 'JollydaysCampin' [sic]

The function `get_cached_twitter_feed()` can be used to fetch a Twitter feed, using a cached file to store results for 10 minutes to prevent excessive API calls.

## Parameters ##

The function `get_twitter_feed()` can be called directly if you wish to bypass the cache for some reason. It takes the same arguments.

The function has only one optional argument at this time.
$softLimit will allow you to specify a number of posts to be returned. It will default to 5.

    get_cached_twitter_feed($softLimit);

You can also use these arguments with the shortcode:

    [twitter_feed limit="example"]
