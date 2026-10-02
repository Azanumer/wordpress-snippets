<?php
/**
 * WordPress Security Snippets
 * Paste into child theme functions.php (unless marked wp-config.php).
 */

// 1. Disable XML-RPC — a common brute-force / DDoS vector.
add_filter('xmlrpc_enabled', '__return_false');

// 2. Hide the WordPress version from page source.
remove_action('wp_head', 'wp_generator');

// 3. Disable theme/plugin file editing from the dashboard.
// Put this in wp-config.php instead:
// define('DISALLOW_FILE_EDIT', true);

// 4. Don't reveal whether the username or password was wrong.
add_filter('login_errors', function () {
    return 'Invalid login credentials.';
});

// 5. Block REST API user enumeration for logged-out visitors.
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users']);
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

// 6. Force strong passwords is core since WP 4.3 — additionally,
// disable application passwords if you don't use them:
// add_filter('wp_is_application_passwords_available', '__return_false');
