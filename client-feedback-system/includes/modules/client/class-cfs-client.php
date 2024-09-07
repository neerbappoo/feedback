<?php


class ClientManagement
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('wp_ajax_cfs_create_client', array($this, 'create_client'));
        add_action('wp_ajax_cfs_delete_client', array($this, 'delete_client'));
        add_action('wp_ajax_cfs_get_client_data', array($this, 'get_client_data'));
        add_action('wp_ajax_cfs_update_client', array($this, 'update_client'));
        add_action('wp_ajax_cfs_get_feedback_forms', array($this, 'get_feedback_forms'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook)
    {
        error_log('Current hook: ' . $hook);

        // Only enqueue on the company options page
        if ('client-feedback-system_page_cfs-client-management' !== $hook) {
            return;
        }

        // Get the plugin's root directory URL
        $plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));

        error_log('Plugin URL: ' . $plugin_url);

        $css_url = $plugin_url . 'includes/modules/client/css/client.css';
        $js_url = $plugin_url . 'includes/modules/client/js/client.js';

        error_log('CSS URL: ' . $css_url);
        error_log('JS URL: ' . $js_url);

        // Enqueue CSS
        wp_enqueue_style(
            'cfs-client-css',
            $css_url,
            array(),
            '1.0.0'
        );

        // Enqueue JS
        wp_enqueue_script(
            'cfs-client-js',
            $js_url,
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function render_options_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/client-options-page.php';
    }

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


    public function create_client()
    {
        $data = $_POST;

        // Sanitize and validate data
        $client_id = isset($data['client_id']) ? intval($data['client_id']) : 0;
        $company = sanitize_text_field($data['company']);
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
                'client_company' => $company,
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

    public function get_feedback_forms()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
        }

        $mode_id = isset($_POST['mode_id']) ? intval($_POST['mode_id']) : 0;

        if (!$mode_id) {
            wp_send_json_error('Invalid mode ID.');
        }

        $preferred_forms = get_post_meta($mode_id, '_preferred_forms', true);

        if (empty($preferred_forms)) {
            wp_send_json_error('No preferred forms found for this mode.');
        }

        $forms = get_posts(array(
            'post_type' => 'landing_page',
            'include' => $preferred_forms,
            'numberposts' => -1,
        ));

        wp_send_json_success($forms);
    }
}
