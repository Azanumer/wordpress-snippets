<?php
/**
 * WordPress Customization Snippets
 * Small UX touches. Paste into child theme functions.php.
 */

// 1. Custom excerpt length (default is 55 words).
add_filter('excerpt_length', function () {
    return 25;
});

// 2. Custom "read more" text.
add_filter('excerpt_more', function () {
    return '… <a href="' . get_permalink() . '">Continue reading</a>';
});

// 3. Login page logo links to your site instead of wordpress.org.
add_filter('login_headerurl', function () {
    return home_url();
});

// 4. Remove "Howdy," from the admin bar greeting.
add_filter('admin_bar_menu', function ($wp_admin_bar) {
    $node = $wp_admin_bar->get_node('my-account');
    if ($node) {
        $wp_admin_bar->add_node([
            'id'    => 'my-account',
            'title' => str_replace('Howdy,', 'Welcome,', $node->title),
        ]);
    }
}, 25);

// 5. Custom admin footer text.
add_filter('admin_footer_text', function () {
    return 'Built with WordPress — managed by ' . get_bloginfo('name');
});

// 6. Allow SVG uploads for administrators only.
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('administrator')) {
        $mimes['svg'] = 'image/svg+xml';
    }
    return $mimes;
});
// WARNING: SVGs can carry scripts. Only enable if you trust every admin.
