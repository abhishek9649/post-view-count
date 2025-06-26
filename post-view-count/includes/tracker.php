
<?php 

add_action('template_redirect', 'pvc_track_post_view');

function pvc_track_post_view() {
    if (!is_singular()) return;

    global $post, $wpdb;
    if (!isset($post->ID)) return;
    if ($post->post_status !== 'publish') return;

    $user_id = get_current_user_id();
    $user_info = $user_id ? get_userdata($user_id) : null;
    $username = $user_info ? $user_info->user_login : 'Guest';
    $is_logged_in = is_user_logged_in() ? 1 : 0;

    $table = $wpdb->prefix . 'post_view_count';

    if (!session_id()) session_start();
    $key = 'pvc_viewed_' . $post->ID;
    if (isset($_SESSION[$key])) return;
    $_SESSION[$key] = true;

    $wpdb->insert(
        $table,
        [
            'post_id'      => $post->ID,
            'post_title'   => $post->post_title,
            'post_status'  => $post->post_status,
            'user_id'      => $user_id,
            'user_name'    => $username,
            'is_logged_in' => $is_logged_in,
            'view_date'    => current_time('mysql'),
        ],
        [
            '%d', '%s', '%s', '%d', '%s', '%d', '%s'
        ]
    );
}
