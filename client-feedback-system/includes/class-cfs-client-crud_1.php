<?php
class ClientManagement
{
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('wp_ajax_cfs_create_client', array($this, 'create_client'));
        add_action('wp_ajax_cfs_delete_client', array($this, 'delete_client'));
        add_action('wp_ajax_cfs_get_client_data', array($this, 'get_client_data'));
        add_action('wp_ajax_cfs_update_client', array($this, 'update_client'));
    }

    /**
     * Register the Client custom post type.
     *
     * @since 1.0.0
     */
    public function register_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'Clients',
                'singular_name' => 'Client',
                'menu_name' => 'Clients',
                'all_items' => 'All Clients',
                'edit_item' => 'Edit Client',
                'view_item' => 'View Client',
                'view_items' => 'View Clients',
                'add_new_item' => 'Add New Client',
                'add_new' => 'Add New Client',
                'new_item' => 'New Client',
                'parent_item_colon' => 'Parent Client:',
                'search_items' => 'Search Clients',
                'not_found' => 'No clients found',
                'not_found_in_trash' => 'No clients found in Trash',
                'archives' => 'Client Archives',
                'attributes' => 'Client Attributes',
                'insert_into_item' => 'Insert into client',
                'uploaded_to_this_item' => 'Uploaded to this client',
                'filter_items_list' => 'Filter clients list',
                'filter_by_date' => 'Filter clients by date',
                'items_list_navigation' => 'Clients list navigation',
                'items_list' => 'Clients list',
                'item_published' => 'Client published.',
                'item_published_privately' => 'Client published privately.',
                'item_reverted_to_draft' => 'Client reverted to draft.',
                'item_scheduled' => 'Client scheduled.',
                'item_updated' => 'Client updated.',
                'item_link' => 'Client Link',
                'item_link_description' => 'A link to a client',
            ),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-users',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'delete_with_user' => false,
        );

        register_post_type('client', $args);
    }

    public function render_option_page()
    {
        $create_nonce = wp_create_nonce('cfs_crud_client');
        $edit_nonce = wp_create_nonce('cfs_edit_client');
?>
<div class="wrap">
    <h1>Client Management</h1>
    <form id="client-form">
        <input type="hidden" name="nonce" value="<?php echo $create_nonce; ?>">
        <input type="hidden" name="action" value="cfs_create_client">
        <input type="hidden" name="client_id" value="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        <div class="form-group">
            <select id="company" name="company">
                <option value="">Select Company</option>
                <?php
                        $companies = get_posts(array('post_type' => 'company', 'posts_per_page' => -1));
                        foreach ($companies as $company) {
                            $is_authorized = get_post_meta($company->ID, 'authorized', true);
                            if ($is_authorized === 'yes') {
                                echo '<option value="' . esc_attr($company->ID) . '">' . esc_html($company->post_title) . '</option>';
                            }
                        }
                        ?>
            </select>
        </div>
        <div class="form-group">
            <label for="feedback">Agreed to Give Feedback</label>
            <select id="feedback" name="feedback">
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>

        <div class="form-group feedback-options">
            <label>Entry Points</label>
            <?php foreach ($entry_points = array('Ticket close', 'Invoice sent', 'Support Desk Call', 'Client Meeting') as $point): ?>
            <div>
                <input type="checkbox" name="entry_points[]" value="<?php echo $point; ?>" id="<?php echo $point; ?>">
                <label for="<?php echo $point; ?>"><?php echo $point; ?></label>
                <select name="contact_mode[<?php echo $point; ?>]" data-point="<?php echo $point; ?>" disabled>
                    <option value="Email">Email</option>
                    <option value="SMS">SMS</option>
                    <option value="In-person">In-person</option>
                    <option value="None">None</option>
                </select>
                <select name="feedback_model[<?php echo $point; ?>]" data-point="<?php echo $point; ?>" disabled>
                    <option value="Simple Rating scale">Simple Rating scale (5 mins)</option>
                    <option value="Text Entry">Text Entry (10 mins)</option>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Create Client">
        </p>
    </form>
    <?php
            $args = array(
                'post_type' => 'client',
                'posts_per_page' => -1,
            );
            $clients = get_posts($args);

            // Display clients
            foreach ($clients as $client):
                $client_id = $client->ID;
                $client_name = $client->post_title;
                $client_email = get_post_meta($client_id, 'client_email', true);
                $client_phone = get_post_meta($client_id, 'client_phone', true);
                $client_feedback = get_post_meta($client_id, 'client_feedback', true);
                $entry_points = get_post_meta($client_id, 'client_entry_points', true);
                $feedback_data = get_post_meta($client_id, 'client_feedback_data', true);

                if (!is_array($entry_points)) {
                    $entry_points = array();
                }

                $feedback_data = json_decode($feedback_data, true);
                if (!is_array($feedback_data)) {
                    $feedback_data = array();
                }
            ?>
    <div class="client-accordion">
        <button class="accordion"><?php echo esc_html($client_name); ?></button>
        <div class="panel">
            <p>Email: <?php echo esc_html($client_email); ?></p>
            <p>Phone: <?php echo esc_html($client_phone); ?></p>
            <p>Agreed to Give Feedback: <?php echo esc_html($client_feedback); ?></p>
            <p>Entry Points:</p>
            <ul>
                <?php foreach ($entry_points as $entry_point): ?>
                <li>
                    <?php echo esc_html($entry_point); ?>
                    <?php if (isset($feedback_data[$entry_point])): ?>
                    <br>Contact Mode: <?php echo esc_html($feedback_data[$entry_point]['contact_mode']); ?>
                    <br>Feedback Model: <?php echo esc_html($feedback_data[$entry_point]['feedback_model']); ?>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="actions">
                <a href="#" class="edit-client" data-client-id="<?php echo esc_attr($client->ID); ?>">Edit</a> |
                <a href="#" class="delete-client" data-client-id="<?php echo esc_attr($client->ID); ?>"
                    data-nonce="<?php echo esc_attr(wp_create_nonce('cfs_delete_client_' . $client->ID)); ?>"
                    data-confirm-message="Are you sure you want to delete this client?">Delete</a>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <dialog id="edit-client-dialog">
        <div class="modal-content" id="modal-content">
            <form id="edit-client-form">
                <input type="hidden" name="nonce" value="<?php echo $edit_nonce; ?>">
                <input type="hidden" name="action" value="cfs_update_client">
                <input type="hidden" name="client_id" value="">
                <table class="form-table">

                    <tr>
                        <th><label for="edit-name">Name</label></th>
                        <td><input type="text" id="edit-name" name="name" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-email">Email</label></th>
                        <td><input type="email" id="edit-email" name="email" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-phone">Phone</label></th>
                        <td><input type="tel" id="edit-phone" name="phone" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-feedback">Agreed to Give Feedback</label></th>
                        <td>
                            <select id="edit-feedback" name="feedback">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="edit-feedback-options">
                        <th><label>Entry Points</label></th>
                        <td>
                            <?php foreach ($entry_points = array('Ticket close', 'Invoice sent', 'Support Desk Call', 'Client Meeting') as $point): ?>
                            <div>
                                <input type="checkbox" name="entry_points[]" value="<?php echo $point; ?>"
                                    id="edit-<?php echo $point; ?>">
                                <label for="edit-<?php echo $point; ?>"><?php echo $point; ?></label>
                                <select name="contact_mode[<?php echo $point; ?>]" data-point="<?php echo $point; ?>">
                                    <option value="Email">Email</option>
                                    <option value="SMS">SMS</option>
                                    <option value="In-person">In-person</option>
                                    <option value="None">None</option>
                                </select>
                                <select name="feedback_model[<?php echo $point; ?>]" data-point="<?php echo $point; ?>">
                                    <option value="Simple Rating scale">Simple Rating scale (5 mins)</option>
                                    <option value="Text Entry">Text Entry (10 mins)</option>
                                </select>
                            </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                <div class="submit-wrapper">
                    <input type="submit" name="submit" id="edit-submit" class="button button-primary"
                        value="Update Client">
                    <button type="button" id="edit-cancel" class="button">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>
</div>
<?php
    }

    public function create_client()
    {
        $data = $_POST;

        // Sanitize and validate data
        $client_id = isset($data['client_id']) ? intval($data['client_id']) : 0;
        $name = sanitize_text_field($data['company_name']);
        $name = sanitize_text_field($data['name']);
        $email = sanitize_email($data['email']);
        $phone = sanitize_text_field($data['phone']);
        $feedback = sanitize_text_field($data['feedback']);
        $entry_points = isset($data['entry_points']) ? array_map('sanitize_text_field', $data['entry_points']) : array();
        $contact_mode = isset($data['contact_mode']) ? array_map('sanitize_text_field', $data['contact_mode']) : array();
        $feedback_model = isset($data['feedback_model']) ? array_map('sanitize_text_field', $data['feedback_model']) : array();

        // Prepare feedback data
        $feedback_data = array();
        foreach ($entry_points as $point) {
            $feedback_data[$point] = array(
                'contact_mode' => isset($contact_mode[$point]) ? $contact_mode[$point] : '',
                'feedback_model' => isset($feedback_model[$point]) ? $feedback_model[$point] : ''
            );
        }

        // Prepare post data
        $post_data = array(
            'post_title' => $name,
            'post_type' => 'client',
            'post_status' => 'publish',
            'meta_input' => array(
                'client_email' => $email,
                'client_phone' => $phone,
                'client_feedback' => $feedback,
                'client_entry_points' => $entry_points,
                'client_feedback_data' => json_encode($feedback_data),
            ),
        );

        if ($client_id) {
            $post_data['ID'] = $client_id;
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }

        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
        } else {
            wp_send_json_success(array('message' => 'Client saved successfully.', 'client_id' => $post_id));
        }
    }
    public function update_client()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_edit_client', 'nonce', false)) {
            wp_send_json_error(array('message' => 'You do not have permission to update clients.'));
        }

        $this->create_client(); // Reuse the create_client method for updating
    }

    /**
     * Delete a client via AJAX.
     *
     * @since 1.0.0
     */
    public function delete_client()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_delete_client_' . $_POST['client_id'], 'nonce', false)) {
            wp_send_json_error('You do not have permission to delete clients.');
        }

        $client_id = intval($_POST['client_id']);

        if (wp_delete_post($client_id, true)) {
            wp_send_json_success('Client deleted successfully.');
        } else {
            wp_send_json_error('Error deleting client.');
        }
    }

    public function get_client_data()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_edit_client', 'nonce', false)) {
            wp_send_json_error('You do not have permission to edit clients.');
        }

        $client_id = intval($_POST['client_id']);
        $client = get_post($client_id);

        if (!$client || $client->post_type !== 'client') {
            wp_send_json_error('Client not found.');
        }

        $client_data = array(
            'id' => $client->ID,
            'name' => $client->post_title,
            'company_name' => get_post_meta($client_id, 'company_name', true),
            'email' => get_post_meta($client_id, 'client_email', true),
            'phone' => get_post_meta($client_id, 'client_phone', true),
            'feedback' => get_post_meta($client_id, 'client_feedback', true),
            'entry_points' => get_post_meta($client_id, 'client_entry_points', true),
            'feedback_data' => json_decode(get_post_meta($client_id, 'client_feedback_data', true), true)
        );

        wp_send_json_success($client_data);
    }
}