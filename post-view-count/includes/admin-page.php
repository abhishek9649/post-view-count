<?php
add_action('admin_menu', 'pvc_add_admin_menu');

function pvc_add_admin_menu() {
    add_menu_page(
        'Post View Count',
        'Post Views',
        'manage_options',
        'post-view-count',
        'pvc_render_admin_page',
        'dashicons-visibility',
        20
    );
}

function pvc_render_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_view_count';

    $selected_page = isset($_POST['pvc_selected_page']) ? intval($_POST['pvc_selected_page']) : 0;

    $all_pages = get_posts([
        'post_type' => ['post', 'page'],
        'numberposts' => -1,
        'post_status' => 'publish'
    ]);

    echo '<div class="wrap"><h1>' . esc_html__('Post View Count', 'post-view-count') . '</h1>';
    echo '<form method="post">';
    wp_nonce_field('pvc_view_form_action', 'pvc_view_nonce');
    echo '<label for="pvc_selected_page"><strong>' . esc_html__('Select a page/post:', 'post-view-count') . '</strong></label><br>';
    echo '<select name="pvc_selected_page" id="pvc_selected_page">';
    echo '<option value="0">' . esc_html__('-- Select --', 'post-view-count') . '</option>';
    echo '<option value="-1"' . selected($selected_page, -1, false) . '>' . esc_html__('Home Page (Latest Posts)', 'post-view-count') . '</option>';

    foreach ($all_pages as $page) {
        $selected = selected($page->ID, $selected_page, false);
        echo '<option value="' . esc_attr($page->ID) . '" ' . esc_attr($selected) . '>' . esc_html($page->post_title . ' (' . $page->post_type . ')') . '</option>';
    }

    echo '</select> <input type="submit" class="button button-primary" value="' . esc_attr__('View', 'post-view-count') . '">';
    echo '</form><br>';

    $nonce = isset($_POST['pvc_view_nonce']) ? sanitize_text_field(wp_unslash($_POST['pvc_view_nonce'])) : '';
    if ($selected_page && wp_verify_nonce($nonce, 'pvc_view_form_action')) {

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}post_view_count WHERE post_id = %d ORDER BY view_date DESC",
                $selected_page
            )
        );

        if ($results) {
            $view_count = count($results);
            $page_info = ($selected_page == -1) ? (object)[
                'post_title' => 'Home Page',
                'post_status' => 'dynamic'
            ] : get_post($selected_page);

            echo '<h2>' . esc_html__('Total Views:', 'post-view-count') . ' ' . esc_html($view_count) . '</h2>';

            $grouped = [];
            foreach ($results as $row) {
                $key = $row->user_name . '|' . $row->is_logged_in;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'user_name' => $row->user_name,
                        'is_logged_in' => $row->is_logged_in,
                        'count' => 1,
                        'last_date' => $row->view_date
                    ];
                } else {
                    $grouped[$key]['count'] += 1;
                    if (strtotime($row->view_date) > strtotime($grouped[$key]['last_date'])) {
                        $grouped[$key]['last_date'] = $row->view_date;
                    }
                }
            }

          
            echo '<table class="widefat fixed striped"><thead><tr>
                <th>' . esc_html__('Post ID', 'post-view-count') . '</th>
                <th>' . esc_html__('Title', 'post-view-count') . '</th>
                <th>' . esc_html__('Status', 'post-view-count') . '</th>
                <th>' . esc_html__('User', 'post-view-count') . '</th>
                <th>' . esc_html__('Logged In', 'post-view-count') . '</th>
                <th>' . esc_html__('Last Viewed', 'post-view-count') . '</th>
                <th>' . esc_html__('View Count', 'post-view-count') . '</th>
            </tr></thead><tbody>';

            foreach ($grouped as $row) {
                echo '<tr>
                    <td>' . esc_html($selected_page) . '</td>
                    <td>' . esc_html($page_info->post_title) . '</td>
                    <td>' . esc_html($page_info->post_status) . '</td>
                    <td>' . esc_html($row['user_name']) . '</td>
                    <td>' . ($row['is_logged_in'] ? esc_html__('Yes', 'post-view-count') : esc_html__('No', 'post-view-count')) . '</td>
                    <td>' . esc_html($row['last_date']) . '</td>
                    <td>' . esc_html($row['count']) . '</td>
                </tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>' . esc_html__('No views recorded yet for this page.', 'post-view-count') . '</p>';
        }
    }

    echo '</div>';
}
