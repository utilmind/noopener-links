<?php
/*
Plugin Name: Noopener Links
Description: Process Links in posts. Add rel="noopener" and target="_blank" for external links; Remove rel="noopener" and target="_blank" from local links; Detect local domains to strip them from the URL.
Version: 0.1
Author: utilmind
*/

//require_once(plugin_dir_path(__FILE__) . 'functions.php');
require_once(__DIR__.'/noopener-links.php');


// Process post links
function process_post_links($content) {
    $domains = [];

    // SERVER_NAME specified in Apache/Nginx/IIS config
    foreach (['SERVER_NAME', 'HTTP_HOST'] as $key) {
        if (isset($_SERVER[$key])) {
            $domains[] = $_SERVER[$key]; // domain name w/o protocol:// prefix. It's okay, then we'll check both "https://", "http://" and even "://".
        }
    }

    // NOTE: $content has escaped quotes (\"). But fortunately the following function can deal with it like with regular unescaped text.
    return noopener_links($content, $domains);

    // TODO: add more functions to clear garbage.
}

// If you'd like to modify content before displaying
//add_filter('the_content', 'process_post_links');

// Process links before saving to db.
add_filter('content_save_pre', 'process_post_links');
