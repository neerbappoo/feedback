<?php

/**
 * Class CFS_Client
 *
 * Handles the creation and management of the Client custom post type,
 * including the options page for CRUD operations.
 *
 * @since 1.0.0
 */
class CFS_Client
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // add_action('init', array($this, 'register_post_type'));
        add_action('wp_ajax_cfs_create_client', array($this, 'create_client'));
        add_action('wp_ajax_cfs_delete_client', array($this, 'delete_client'));
        add_action('wp_ajax_cfs_update_client', array($this, 'update_client'));
        add_action('wp_ajax_cfs_bulk_assign_page', array($this, 'bulk_assign_page'));
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

    /**
     * Render the options page.
     *
     * @since 1.0.0
     */
    public function render_options_page()
    {
?>
        <div class="wrap">
            <h1>Client Options</h1>
            <form method="get">
                <input type="hidden" name="page" value="cfs-client-options">
                <select name="filter_contact_method">
                    <option value="">All Contact Methods</option>
                    <?php
                    $contact_methods = array(
                        'Declined' => 'Declined',
                        'SMS' => 'SMS',
                        'Email' => 'Email',
                        'In-Person' => 'In-Person'
                    );
                    foreach ($contact_methods as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '" ' . selected($_GET['filter_contact_method'], $value, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
                </select>
                <select name="filter_company" id="filter-company">
                    <option value="">All Companies</option>
                    <?php
                    $companies = get_posts(array('post_type' => 'company', 'posts_per_page' => -1));
                    foreach ($companies as $company) {
                        echo '<option value="' . esc_attr($company->ID) . '" ' . selected($_GET['filter_company'], $company->ID, false) . '>' . esc_html($company->post_title) . '</option>';
                    }
                    ?>
                </select>
                <input type="submit" value="Filter">
            </form>
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th scope="col"><input type="checkbox" id="select-all-clients" name="select-all-clients"></th>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Company</th>
                        <th scope="col">Preferred Contact Method</th>
                        <th scope="col">Assigned Page</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $args = array(
                        'post_type' => 'client',
                        'posts_per_page' => -1,
                    );

                    if (!empty($_GET['filter_contact_method'])) {
                        $args['meta_query'][] = array(
                            'key' => 'preferred_contact_method',
                            'value' => sanitize_text_field($_GET['filter_contact_method']),
                            'compare' => '=',
                        );
                    }

                    if (!empty($_GET['filter_company'])) {
                        $args['meta_query'][] = array(
                            'key' => 'company',
                            'value' => intval($_GET['filter_company']),
                            'compare' => '=',
                        );
                    }

                    $clients = get_posts($args);

                    foreach ($clients as $client) {
                        $company_id = get_post_meta($client->ID, 'company', true);
                        $company_name = $company_id ? get_the_title($company_id) : 'N/A';
                        $email = get_post_meta($client->ID, 'email', true);
                        $phone_number = get_post_meta($client->ID, 'phone_number', true);
                        $preferred_contact_method = get_post_meta($client->ID, 'preferred_contact_method', true);
                        $assigned_page = get_post_meta($client->ID, 'assigned_page', true);
                        $assigned_page_title = $assigned_page ? get_the_title($assigned_page) : 'N/A';
                        $assigned_page_permalink = $assigned_page ? get_permalink($assigned_page) : '';
                    ?>
                        <tr>
                            <td><input type="checkbox" name="client_ids[]" value="<?php echo esc_attr($client->ID); ?>"></td>
                            <td><?php echo esc_html($client->ID); ?></td>
                            <td><?php echo esc_html(get_the_title($client->ID)); ?></td>
                            <td><?php echo esc_html($email); ?></td>
                            <td><?php echo esc_html($phone_number); ?></td>
                            <td><?php echo esc_html($company_name); ?></td>
                            <td><?php echo esc_html($preferred_contact_method); ?></td>
                            <td data-page-id="<?php echo esc_attr($assigned_page); ?>">
                                <?php echo $assigned_page ? '<a href="' . esc_url($assigned_page_permalink) . '" target="_blank">' . esc_html($assigned_page_title) . ' (ID: ' . esc_html($assigned_page) . ')</a>' : 'N/A'; ?>
                            </td>
                            <td>
                                <a href="#" class="edit-client" data-client-id="<?php echo esc_attr($client->ID); ?>">Edit</a> |
                                <a href="#" class="delete-client" data-client-id="<?php echo esc_attr($client->ID); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('cfs_delete_client_' . $client->ID)); ?>"
                                    data-confirm-message="Are you sure you want to delete this client?">Delete</a>
                                <?php
                                $landing_page_id = get_post_meta($client->ID, 'assigned_page', true);
                                if ($landing_page_id) {
                                    $client_landing_page_url = $this->get_client_landing_page_url($client->ID);
                                    echo ' | <a href="' . esc_url($client_landing_page_url) . '" target="_blank">Send</a>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <div id="bulk-actions-container">
                <select id="bulk-actions">
                    <option value="">Bulk Actions</option>
                    <option value="assign_page">Assign Page</option>
                </select>
                <div id="assign-page-dropdown" style="display: none;">
                    <select id="assigned-page">
                        <option value="">Select a page</option>
                        <?php
                        $pages = get_posts(array('post_type' => 'landing_page', 'posts_per_page' => -1));
                        foreach ($pages as $page) {
                            echo '<option value="' . $page->ID . '">' . $page->post_title . ' (ID: ' . $page->ID . ', Permalink: ' . get_permalink($page->ID) . ')</option>';
                        }
                        ?>
                    </select>
                    <button id="apply-bulk-action">Apply</button>
                </div>
                <?php wp_nonce_field('cfs_bulk_actions', 'cfs-bulk-actions-nonce'); ?>
            </div>
            <dialog id="edit-client-form">
                <div class="modal-content" id="modal-content">
                    <h2>Edit Client</h2>
                    <form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
                        <input type="hidden" name="action" value="cfs_update_client">
                        <input type="hidden" name="client_id" id="edit-client-id">
                        <?php wp_nonce_field('cfs_update_client', 'cfs_update_client_nonce'); ?>
                        <p>
                            <label for="edit-name">Name:</label>
                            <input type="text" id="edit-name" name="name" required>
                        </p>
                        <p>
                            <label for="edit-email">Email:</label>
                            <input type="email" id="edit-email" name="email" required>
                        </p>
                        <p>
                            <label for="edit-phone">Phone Number:</label>
                            <input type="tel" id="edit-phone" name="phone_number" required>
                        </p>
                        <p>
                            <label for="edit-company">Company:</label>
                            <select id="edit-company" name="company">
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
                        </p>
                        <p>
                            <label for="edit-preferred-contact-method">Preferred Contact Method:</label>
                            <select id="edit-preferred-contact-method" name="preferred_contact_method">
                                <?php
                                foreach ($contact_methods as $value => $label) {
                                    echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                                }
                                ?>
                            </select>
                        </p>
                        <p>
                            <label for="edit-assigned-page">Assigned Page:</label>
                            <select id="edit-assigned-page" name="assigned_page">
                                <option value="">N/A</option>
                                <?php
                                $pages = get_posts(array('post_type' => 'landing_page', 'posts_per_page' => -1));
                                foreach ($pages as $page) {
                                    $selected = ($assigned_page == $page->ID) ? 'selected' : '';
                                    echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . ' (ID: ' . $page->ID . ', Permalink: ' . get_permalink($page->ID) . ')</option>';
                                }
                                ?>
                            </select>
                        </p>
                        <input type="submit" value="Update Client">
                    </form>
                    <p class="error"></p>
                    <p class="success"></p>
                    <p class="debug"></p>
                    <button id="close-edit-client-form" class="close-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="currentColor"
                            class="icon icon-tabler icons-tabler-filled icon-tabler-square-rounded-x">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M12 2l.324 .001l.318 .004l.616 .017l.299 .013l.579 .034l.553 .046c4.785 .464 6.732 2.411 7.196 7.196l.046 .553l.034 .579c.005 .098 .01 .198 .013 .299l.017 .616l.005 .642l-.005 .642l-.017 .616l-.013 .299l-.034 .579l-.046 .553c-.464 4.785 -2.411 6.732 -7.196 7.196l-.553 .046l-.579 .034c-.098 .005 -.198 .01 -.299 .013l-.616 .017l-.642 .005l-.642 -.005l-.616 -.017l-.299 -.013l-.579 -.034l-.553 -.046c-4.785 -.464 -6.732 -2.411 -7.196 -7.196l-.046 -.553l-.034 -.579a28.058 28.058 0 0 1 -.013 -.299l-.017 -.616c-.003 -.21 -.005 -.424 -.005 -.642l.001 -.324l.004 -.318l.017 -.616l.013 -.299l.034 -.579l.046 -.553c.464 -4.785 2.411 -6.732 7.196 -7.196l.553 -.046l.579 -.034c.098 -.005 .198 -.01 .299 -.013l.616 -.017c.21 -.003 .424 -.005 .642 -.005zm-1.489 7.14a1 1 0 0 0 -1.218 1.567l1.292 1.293l-1.292 1.293l-.083 .094a1 1 0 0 0 1.497 1.32l1.293 -1.292l1.293 1.292l.094 .083a1 1 0 0 0 1.32 -1.497l-1.292 -1.293l1.292 -1.293l.083 -.094a1 1 0 0 0 -1.497 -1.32l-1.293 1.292l-1.293 -1.292l-.094 -.083z"
                                fill="currentColor" stroke-width="0" />
                        </svg></button>
                </div>
            </dialog>
            <h2>Create a new client:</h2>
            <form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">
                <input type="hidden" name="action" value="cfs_create_client">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required><br><br>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required><br><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>
                <label for="phone_number">Phone Number:</label>
                <input type="tel" id="phone_number" name="phone_number" required><br><br>
                <label for="company">Company:</label>
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
                </select><br><br>
                <label for="preferred_contact_method">Preferred Contact Method:</label>
                <select id="preferred_contact_method" name="preferred_contact_method">
                    <?php
                    foreach ($contact_methods as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                    }
                    ?>
                </select><br><br>
                <select id="assigned_page" name="assigned_page">
                    <option value="">Select a page</option>
                    <?php
                    $pages = get_posts(array('post_type' => 'landing_page', 'posts_per_page' => -1));
                    foreach ($pages as $page) {
                        echo '<option value="' . $page->ID . '">' . $page->post_title . ' (ID: ' . $page->ID . ', Permalink: ' . get_permalink($page->ID) . ')</option>';
                    }
                    ?>
                </select>
                <?php wp_nonce_field('cfs_create_client', 'cfs_create_client_nonce'); ?>
                <input type="submit" value="Create Client">
            </form>
        </div>
<?php
    }

    /**
     * Create a new client via AJAX.
     *
     * @since 1.0.0
     */
    public function create_client()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_create_client_nonce'], 'cfs_create_client')) {
            wp_die('You do not have permission to create clients.');
        }

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $company = intval($_POST['company']);
        $preferred_contact_method = sanitize_text_field($_POST['preferred_contact_method']);
        $assigned_page = intval($_POST['assigned_page']);

        $client = array(
            'post_type' => 'client',
            'post_title' => $first_name . ' ' . $last_name,
            'post_status' => 'publish',
        );

        $client_id = wp_insert_post($client);

        if ($client_id) {
            update_post_meta($client_id, 'first_name', $first_name);
            update_post_meta($client_id, 'last_name', $last_name);
            update_post_meta($client_id, 'email', $email);
            update_post_meta($client_id, 'phone_number', $phone_number);
            update_post_meta($client_id, 'company', $company);
            update_post_meta($client_id, 'preferred_contact_method', $preferred_contact_method);
            update_post_meta($client_id, 'assigned_page', $assigned_page);

            wp_redirect(admin_url('admin.php?page=cfs-client-options'));
            exit;
        } else {
            wp_die('Error creating client.');
        }
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

    /**
     * Update a client via AJAX.
     *
     * @since 1.0.0
     */
    public function update_client()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_update_client_nonce'], 'cfs_update_client')) {
            wp_send_json_error('You do not have permission to update clients.');
            return;
        }

        $client_id = intval($_POST['client_id']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $company = intval($_POST['company']);
        $preferred_contact_method = sanitize_text_field($_POST['preferred_contact_method']);
        $assigned_page = intval($_POST['assigned_page']);

        // Validate email
        if (!is_email($email)) {
            wp_send_json_error('Invalid email address.');
            return;
        }

        // Validate phone number (simple validation, you might want to use a more robust method)
        if (!preg_match('/^[0-9\-\(\)\/\+\s]*$/', $phone_number)) {
            wp_send_json_error('Invalid phone number.');
            return;
        }

        // Update client
        $updated_post = array(
            'ID' => $client_id,
            'post_title' => $name,
        );
        wp_update_post($updated_post);

        update_post_meta($client_id, 'email', $email);
        update_post_meta($client_id, 'phone_number', $phone_number);
        update_post_meta($client_id, 'company', $company);
        update_post_meta($client_id, 'preferred_contact_method', $preferred_contact_method);
        update_post_meta($client_id, 'assigned_page', $assigned_page);

        wp_send_json_success('Client updated successfully.');
    }

    public function bulk_assign_page()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'cfs_bulk_actions')) {
            wp_send_json_error('You do not have permission to perform this action.');
            return;
        }

        $client_ids = array_map('intval', $_POST['client_ids']);
        $page_id = intval($_POST['page_id']);

        foreach ($client_ids as $client_id) {
            update_post_meta($client_id, 'assigned_page', $page_id);
        }

        wp_send_json_success('Clients have been assigned to the selected page.');
    }

    public function get_client_landing_page_url($post_id)
    {
        $client = get_post($post_id);
        $first_name = get_post_meta($post_id, 'first_name', true);
        $last_name = get_post_meta($post_id, 'last_name', true);
        $email = get_post_meta($post_id, 'email', true);
        $company = get_post_meta($post_id, 'company', true);
        $company_name = get_the_title($company);

        // Log: Getting client landing page URL for post ID $post_id
        error_log("Getting client landing page URL for post ID $post_id");

        $landing_page_id = get_post_meta($post_id, 'assigned_page', true);
        if ($landing_page_id) {
            $url = add_query_arg(
                array(
                    'cid' => $post_id,
                    'name' => sanitize_title($first_name . '_' . $last_name),
                    'email' => sanitize_email($email),
                    'company' => sanitize_title($company_name),
                ),
                get_permalink($landing_page_id)
            );
            // Log: Generated URL for client landing page: $url
            error_log("Generated URL for client landing page: $url");
            return $url;
        } else {
            // Log: No landing page assigned to post ID $post_id
            error_log("No landing page assigned to post ID $post_id");
        }
        return '';
    }
}
