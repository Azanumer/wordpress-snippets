<?php
/**
 * Snippet: Log all outgoing WordPress emails to the database.
 *
 * Drop-in for a must-use plugin (wp-content/mu-plugins/mail-logger.php) or
 * your child theme's functions.php. Creates a wp_mail_log table on first run,
 * records every wp_mail() call (recipients, subject, headers, success/fail),
 * and adds a "Mail Log" page under Tools → Mail Log with a clear-log button.
 *
 * Useful for debugging contact forms, WooCommerce order emails, and password
 * resets on a new server (e.g. right after setting up Postfix).
 */

// 1. Create the table if it doesn't exist.
add_action('init', function () {
    global $wpdb;
    $table = $wpdb->prefix . 'mail_log';
    $charset = $wpdb->get_charset_collate();
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared — table name is trusted prefix.
    $wpdb->query(
        "CREATE TABLE IF NOT EXISTS `{$table}` (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            recipient TEXT NOT NULL,
            subject VARCHAR(255) NOT NULL DEFAULT '',
            headers TEXT NOT NULL,
            status VARCHAR(12) NOT NULL DEFAULT 'sent',
            error TEXT NOT NULL,
            PRIMARY KEY (id),
            KEY sent_at (sent_at)
        ) {$charset};"
    );
});

// 2. Capture the mail arguments before sending.
add_filter('wp_mail', function ($args) {
    $GLOBALS['mail_logger_args'] = $args;
    return $args;
});

// 3. Record success / failure after wp_mail runs.
add_action('wp_mail_succeeded', function ($mail_data) {
    mail_logger_record('sent', '', $mail_data);
});
add_action('wp_mail_failed', function ($wp_error) {
    $data = $wp_error->get_error_data('wp_mail_failed');
    $msg  = is_wp_error($wp_error) ? $wp_error->get_error_message() : '';
    mail_logger_record('failed', $msg, is_array($data) ? $data : array());
});

/**
 * Insert one row into the mail log table.
 */
function mail_logger_record($status, $error, $mail_data) {
    global $wpdb;
    $args = isset($GLOBALS['mail_logger_args']) ? $GLOBALS['mail_logger_args'] : array();

    $to = isset($args['to']) ? $args['to'] : (isset($mail_data['to']) ? $mail_data['to'] : '');
    if (is_array($to)) {
        $to = implode(', ', $to);
    }

    $headers = isset($args['headers']) ? $args['headers'] : '';
    if (is_array($headers)) {
        $headers = implode("\n", $headers);
    }

    $wpdb->insert(
        $wpdb->prefix . 'mail_log',
        array(
            'recipient' => (string) $to,
            'subject'   => isset($args['subject']) ? (string) $args['subject'] : '',
            'headers'   => (string) $headers,
            'status'    => $status,
            'error'     => (string) $error,
        ),
        array('%s', '%s', '%s', '%s', '%s')
    );
}

// 4. Admin page under Tools → Mail Log.
add_action('admin_menu', function () {
    add_management_page(
        'Mail Log',
        'Mail Log',
        'manage_options',
        'mail-logger',
        'mail_logger_admin_page'
    );
});

function mail_logger_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mail_log';

    // Clear log action.
    if (isset($_POST['mail_logger_clear']) && check_admin_referer('mail_logger_clear')) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared — trusted prefix table.
        $wpdb->query("TRUNCATE TABLE `{$table}`");
        echo '<div class="notice notice-success"><p>Mail log cleared.</p></div>';
    }

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared — trusted prefix table.
    $rows = $wpdb->get_results("SELECT * FROM `{$table}` ORDER BY id DESC LIMIT 200");

    echo '<div class="wrap"><h1>Mail Log</h1>';
    echo '<form method="post">';
    wp_nonce_field('mail_logger_clear');
    submit_button('Clear log', 'delete', 'mail_logger_clear');
    echo '</form>';

    if (empty($rows)) {
        echo '<p>No emails logged yet.</p></div>';
        return;
    }

    echo '<table class="widefat striped"><thead><tr>'
        . '<th>Time</th><th>To</th><th>Subject</th><th>Status</th><th>Error</th>'
        . '</tr></thead><tbody>';
    foreach ($rows as $r) {
        printf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td><strong>%s</strong></td><td>%s</td></tr>',
            esc_html($r->sent_at),
            esc_html($r->recipient),
            esc_html($r->subject),
            esc_html($r->status),
            esc_html($r->error)
        );
    }
    echo '</tbody></table></div>';
}
