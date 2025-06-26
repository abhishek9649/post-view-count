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

    echo '<div class="wrap"><h1>Post View Count</h1>';

    
    echo '<form method="post">';
    echo '<label for="pvc_selected_page"><strong>Select a page/post:</strong></label><br>';
    echo '<select name="pvc_selected_page" id="pvc_selected_page">';
    echo '<option value="0">-- Select --</option>';
    foreach ($all_pages as $page) {
        $selected = ($page->ID == $selected_page) ? 'selected' : '';
        echo '<option value="' . $page->ID . '" ' . $selected . '>' . esc_html($page->post_title) . ' (' . $page->post_type . ')</option>';
    }
    echo '</select> <input type="submit" class="button button-primary" value="View">';
    echo '</form><br>';

   
    if ($selected_page) {
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d ORDER BY view_date DESC",
            $selected_page
        ));

        if ($results) {
            $view_count = count($results);
            $page_info = get_post($selected_page);
            echo '<h2>Total Views: ' . $view_count . '</h2>';

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
                <th>Post ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>User</th>
                <th>Logged In</th>
                <th>Last Viewed</th>
                <th>View Count</th>
            </tr></thead><tbody>';

            foreach ($grouped as $row) {
                echo '<tr>
                    <td>' . esc_html($selected_page) . '</td>
                    <td>' . esc_html($page_info->post_title) . '</td>
                    <td>' . esc_html($page_info->post_status) . '</td>
                    <td>' . esc_html($row['user_name']) . '</td>
                    <td>' . ($row['is_logged_in'] ? 'Yes' : 'No') . '</td>
                    <td>' . esc_html($row['last_date']) . '</td>
                    <td>' . esc_html($row['count']) . '</td>
                </tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>No views recorded yet for this page.</p>';
        }
    }

    echo '</div>';
}
