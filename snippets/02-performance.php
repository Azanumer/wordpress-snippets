<?php
/**
 * WordPress Performance Snippets
 * Removes bloat most sites never use. Paste into child theme functions.php.
 */

// 1. Disable emojis (saves an extra JS + CSS request on every page).
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', function ($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });
});

// 2. Disable oEmbed (prevents others embedding your posts + extra JS).
add_action('init', function () {
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}, 9999);

// 3. Limit Heartbeat API to post edit screens (reduces admin-ajax.php load).
add_filter('heartbeat_settings', function ($settings) {
    $settings['interval'] = 60; // seconds
    return $settings;
});
add_action('init', function () {
    global $pagenow;
    if ($pagenow !== 'post.php' && $pagenow !== 'post-new.php') {
        wp_deregister_script('heartbeat');
    }
}, 1);

// 4. Remove query strings from static resources (helps some caches).
add_filter('script_loader_src', 'wpsnip_remove_query_strings', 15);
add_filter('style_loader_src', 'wpsnip_remove_query_strings', 15);
function wpsnip_remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

// 5. Disable self-pingbacks.
add_action('pre_ping', function (&$links) {
    $home = get_option('home');
    foreach ($links as $l => $link) {
        if (strpos($link, $home) === 0) {
            unset($links[$l]);
        }
    }
});
