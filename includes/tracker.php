<?php

add_action('template_redirect', 'pvc_track_post_view');

function pvc_track_post_view() {
    if (!is_singular()) return; // Only track single post/page/custom types

    global $post, $wpdb;

    if (!isset($post->ID)) return;

    // Ensure selected posts option is an array of integers
    $selected_posts = get_option('pvc_selected_posts', []);
    $selected_posts = array_map('intval', (array)$selected_posts);

    // Debug log to see what's being compared
    // error_log('Selected IDs: ' . implode(',', $selected_posts));
    // error_log('Current Post ID: ' . $post->ID);

    // Check if current page/post is in selected list
    if (!in_array((int)$post->ID, $selected_posts)) return;

    $user_id = get_current_user_id();
    $user_info = $user_id ? get_userdata($user_id) : null;
    $username = $user_info ? $user_info->user_login : 'Guest';
    $is_logged_in = is_user_logged_in() ? 1 : 0;

    $table = $wpdb->prefix . 'post_view_count';

    // Insert data
    $wpdb->insert($table, [
        'post_id'      => $post->ID,
        'post_title'   => $post->post_title,
        'post_status'  => $post->post_status,
        'user_id'      => $user_id,
        'user_name'    => $username,
        'is_logged_in' => $is_logged_in,
        'view_date'    => current_time('mysql'),
    ]);
}
