<?php

/**
 * Class CFS_Feedback_Collection
 *
 * Handles the creation and management of the Feedback Collection custom post type,
 * including the options page for CRUD operations.
 *
 * @since 1.0.0
 */
class CFS_Feedback_Collection
{

    public function __construct()
    {

        add_action('wp_ajax_cfs_save_survey_results', array($this, 'save_survey_results'));
        add_action('wp_ajax_cfs_delete_feedback', array($this, 'delete_feedback'));
        add_action('wp_ajax_cfs_update_feedback', array($this, 'update_feedback'));
        add_action('wp_ajax_cfs_get_form_results', array($this, 'get_form_results'));
    }

    /**
     * Render the Feedback Collection options page.
     *
     * @since 1.0.0
     */
    public function render_options_page()
    {
?>
        <div class="wrap">
            <h1>Feedback Collection</h1>
            <div id="form-list">
                <h2>Forms with Received Results</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Form Title</th>
                            <th>Responses</th>
                            <th>Leading</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $forms = $this->get_forms_with_results();
                        foreach ($forms as $form_title => $form_data) {
                        ?>
                            <tr>
                                <td><?php echo esc_html($form_title); ?></td>
                                <td><?php echo esc_html($form_data['response_count']); ?></td>
                                <td><?php echo esc_html($form_data['leading_client']); ?></td>
                                <td><a href="#" class="view-form-results"
                                        data-form-title="<?php echo esc_attr($form_title); ?>">View Results</a></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div id="leaderboard">
                <h2>Leaderboard</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Client Name</th>
                            <th>Client ID</th>
                            <th>Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $leaderboard = $this->get_leaderboard();
                        foreach ($leaderboard as $rank => $client) {
                        ?>
                            <tr>
                                <td><?php echo esc_html($rank + 1); ?></td>
                                <td><?php echo esc_html($client['name']); ?></td>
                                <td><?php echo esc_html($client['id']); ?></td>
                                <td><?php echo esc_html($client['total_points']); ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div id="form-results" style="display: none;">
                <h2>Form Results: <span id="current-form-title"></span></h2>
                <a href="#" id="back-to-forms">← Back to Forms List</a>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Survey Results</th>
                            <th>Points</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="form-results-body">
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    private function get_forms_with_results()
    {
        $forms = array();
        $clients = get_posts(array(
            'post_type' => 'client',
            'posts_per_page' => -1,
        ));

        foreach ($clients as $client) {
            $form_title = get_post_meta($client->ID, 'form_title', true);
            $points = intval(get_post_meta($client->ID, 'points', true));
            if (!empty($form_title)) {
                if (!isset($forms[$form_title])) {
                    $forms[$form_title] = array(
                        'response_count' => 0,
                        'leading_client' => '',
                        'max_points' => 0
                    );
                }
                $forms[$form_title]['response_count']++;
                if ($points > $forms[$form_title]['max_points']) {
                    $forms[$form_title]['max_points'] = $points;
                    $forms[$form_title]['leading_client'] = get_the_title($client->ID) . " (ID: {$client->ID})";
                }
            }
        }

        return $forms;
    }

    private function get_leaderboard()
    {
        $clients = get_posts(array(
            'post_type' => 'client',
            'posts_per_page' => -1,
        ));

        $leaderboard = array();
        foreach ($clients as $client) {
            $points = intval(get_post_meta($client->ID, 'points', true));
            $leaderboard[] = array(
                'id' => $client->ID,
                'name' => get_the_title($client->ID),
                'total_points' => $points
            );
        }

        usort($leaderboard, function ($a, $b) {
            return $b['total_points'] - $a['total_points'];
        });

        return array_slice($leaderboard, 0, 10); // Return top 10 clients
    }

    public function get_form_results()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to view form results.');
        }

        $form_title = sanitize_text_field($_POST['form_title']);
        $clients = get_posts(array(
            'post_type' => 'client',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'form_title',
                    'value' => $form_title,
                    'compare' => '='
                )
            )
        ));

        ob_start();
        foreach ($clients as $client) {
            $survey_results = get_post_meta($client->ID, 'survey_results', true);
            $points = get_post_meta($client->ID, 'points', true);
            $company_id = get_post_meta($client->ID, 'company', true);
            $company_name = $company_id ? get_the_title($company_id) : 'N/A';
        ?>
            <tr>
                <td><?php echo esc_html(get_the_title($client->ID)); ?></td>
                <td><?php echo esc_html(get_post_meta($client->ID, 'email', true)); ?></td>
                <td><?php echo esc_html($company_name); ?></td>
                <td><?php echo $this->json_to_table(json_decode($survey_results, true)); ?></td>
                <td><?php echo esc_html($points); ?></td>
                <td>
                    <a href="#" class="edit-feedback" data-client-id="<?php echo $client->ID; ?>">Edit</a> |
                    <a href="#" class="delete-feedback" data-client-id="<?php echo $client->ID; ?>"
                        data-nonce="<?php echo wp_create_nonce('cfs_delete_feedback_' . $client->ID); ?>"
                        data-confirm-message="Are you sure you want to delete this feedback?">Delete</a>
                </td>
            </tr>
<?php
        }
        $html = ob_get_clean();
        wp_send_json_success($html);
    }
    private function json_to_table($json, $table = '')
    {
        $table .= '<table>';
        foreach ($json as $key => $value) {
            $table .= '<tr>';
            $table .= '<th>' . ucfirst($key) . '</th>';
            if (is_array($value) || is_object($value)) {
                $table .= '<td>' . $this->json_to_table($value) . '</td>';
            } else {
                $table .= '<td>' . $value . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';
        return $table;
    }
    /**
     * Save the survey results via AJAX.
     *
     * @since 1.0.0
     */
    public function save_survey_results()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to save survey results.');
        }

        $client_id = intval($_POST['client_id']);
        $form_id = intval($_POST['form_id']);
        $form_title = sanitize_text_field($_POST['form_title']);
        $points = sanitize_text_field($_POST['points']);
        $survey_results = sanitize_text_field($_POST['survey_results']);

        // Update the client's survey results
        update_post_meta($client_id, 'survey_results', $survey_results);
        update_post_meta($client_id, 'form_title', $form_title);
        update_post_meta($client_id, 'points', $points);
        wp_send_json_success('Survey results saved successfully.');
    }

    /**
     * Delete feedback via AJAX.
     *
     * @since 1.0.0
     */
    public function delete_feedback()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_delete_feedback_' . $_POST['client_id'], 'nonce', false)) {
            wp_send_json_error('You do not have permission to delete feedback.');
        }

        $client_id = intval($_POST['client_id']);

        // Remove the survey results from the client's post meta
        delete_post_meta($client_id, 'survey_results');

        wp_send_json_success('Feedback deleted successfully.');
    }

    /**
     * Update feedback via AJAX.
     *
     * @since 1.0.0
     */
    public function update_feedback()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_update_feedback_nonce'], 'cfs_update_feedback')) {
            wp_send_json_error('You do not have permission to update feedback.');
            return;
        }

        $client_id = intval($_POST['client_id']);
        $survey_results = sanitize_text_field($_POST['survey_results']);

        // Update the client's survey results
        update_post_meta($client_id, 'survey_results', $survey_results);

        wp_send_json_success('Feedback updated successfully.');
    }
}
