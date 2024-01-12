<?php

/**
 * Function to push new content to external sources
 */

namespace npdc\lib;

class Push {
    /**
     * Send content to service
     *
     * @param string $title short message
     * @param string $url url of content
     * @param string|null $text long message text (if any)
     * @return void
     */
    public static function send($title, $url, $text=null) {
        $mastodon = new Mastodon(
            \npdc\config::$mastodon['host'],
            \npdc\config::$mastodon['token']
        );
        $mastodon->post(strip_tags($title) . ' ' . $url);
    }
}