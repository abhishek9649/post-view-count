<?php
/**
 * Plugin Name: Post View Count
 * Description: Tracks selected page/post views with user details.
 * Version: 1.0.0
 * Author: Robust Decoders  
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/tracker.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';

register_activation_hook(__FILE__, 'pvc_create_table');

function pvc_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_view_count';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        post_title TEXT,
        post_status VARCHAR(20),
        user_id BIGINT(20),
        user_name VARCHAR(255),
        is_logged_in TINYINT(1),
        view_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";


    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
