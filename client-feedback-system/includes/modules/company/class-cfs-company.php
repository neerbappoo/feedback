<?php

/**
 * Class CFS_Company
 *
 * Handles the creation and management of the Company custom post type,
 * including the options page for CRUD operations.
 *
 * @since 1.0.0
 */
class CFS_Company
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('save_post', array($this, 'save_company_details'));
        add_action('wp_ajax_cfs_create_company', array($this, 'create_company'));
        add_action('wp_ajax_cfs_delete_company', array($this, 'delete_company'));
        add_action('wp_ajax_cfs_bulk_trash_companies', array($this, 'bulk_trash_companies'));
        add_action('wp_ajax_cfs_update_company', array($this, 'update_company'));
        add_action('before_delete_post', array($this, 'delete_company_clients'), 10, 1);
        add_action('updated_post_meta', array($this, 'update_client_preferences'), 10, 4);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook)
    {
        error_log('Current hook: ' . $hook);

        // Only enqueue on the company options page
        if ('client-feedback-system_page_cfs-company-options' !== $hook) {
            return;
        }

        // Get the plugin's root directory URL
        $plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));

        error_log('Plugin URL: ' . $plugin_url);

        $css_url = $plugin_url . 'includes/modules/company/css/company.css';
        $js_url = $plugin_url . 'includes/modules/company/js/company.js';

        error_log('CSS URL: ' . $css_url);
        error_log('JS URL: ' . $js_url);

        // Enqueue CSS
        wp_enqueue_style(
            'cfs-company-css',
            $css_url,
            array(),
            '1.0.0'
        );

        // Enqueue JS
        wp_enqueue_script(
            'cfs-company-js',
            $js_url,
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function render_options_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/company-options-page.php';
    }
    /**
     * Save the company details.
     *
     * @param int $post_id The post ID.
     * @since 1.0.0
     */
    public function save_company_details($post_id)
    {
        // Check if our nonce is set and verify it
        if (!isset($_POST['company_details_nonce']) || !wp_verify_nonce($_POST['company_details_nonce'], 'company_details_nonce')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize and save the field values
        if (isset($_POST['authorized'])) {
            update_post_meta($post_id, 'authorized', sanitize_text_field($_POST['authorized']));
        }
    }
    /**
     * Register the Company custom post type.
     *
     * @since 1.0.0
     */
    public function register_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'Companies',
                'singular_name' => 'Company',
                // ... (rest of the labels)
            ),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-building',
            'supports' => array('title', 'editor', 'custom-fields'),
            'delete_with_user' => false,
        );

        register_post_type('company', $args);
    }

    /**
     * Create a new company via AJAX.
     *
     * @since 1.0.0
     */
    public function create_company()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_create_company_nonce'], 'cfs_create_company')) {
            wp_die('You do not have permission to create companies.');
        }

        $company_name = sanitize_text_field($_POST['company_name']);
        $authorized = sanitize_text_field($_POST['authorized']);

        $company = array(
            'post_type' => 'company',
            'post_title' => $company_name,
            'post_status' => 'publish',
        );

        $company_id = wp_insert_post($company);

        if ($company_id) {
            update_post_meta($company_id, 'authorized', $authorized);

            wp_redirect(admin_url('admin.php?page=cfs-company-options'));
            exit;
        } else {
            wp_die('Error creating company.');
        }
    }

    /**
     * Delete a company via AJAX.
     *
     * @since 1.0.0
     */
    public function delete_company()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_delete_company_' . $_POST['company_id'], 'nonce', false)) {
            wp_send_json_error('You do not have permission to delete companies.');
        }

        $company_id = intval($_POST['company_id']);

        if (wp_delete_post($company_id, true)) {
            wp_send_json_success('Company deleted successfully.');
        } else {
            wp_send_json_error('Error deleting company.');
        }
    }

    /**
     * Bulk trash companies via AJAX.
     *
     * @since 1.0.0
     */
    public function bulk_trash_companies()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_bulk_action', 'nonce', false)) {
            wp_send_json_error('You do not have permission to perform this action.');
        }

        $company_ids = isset($_POST['company_ids']) ? array_map('intval', $_POST['company_ids']) : array();

        if (empty($company_ids)) {
            wp_send_json_error('No companies selected.');
        }

        $trashed_count = 0;
        foreach ($company_ids as $company_id) {
            if (wp_trash_post($company_id)) {
                $trashed_count++;
            }
        }

        wp_send_json_success("Successfully trashed {$trashed_count} companies.");
    }

    /**
     * Update a company via AJAX.
     *
     * @since 1.0.0
     */

    public function update_company()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_update_company_nonce'], 'cfs_update_company')) {
            wp_send_json_error('You do not have permission to update companies.');
        }

        $company_id = intval($_POST['company_id']);
        $name = sanitize_text_field($_POST['name']);
        $authorized = sanitize_text_field($_POST['authorized']);

        // Validate authorized value
        if (!in_array($authorized, ['yes', 'no'])) {
            wp_send_json_error('Invalid authorized value.');
        }

        // Update company
        $updated_post = array(
            'ID' => $company_id,
            'post_title' => $name,
        );
        wp_update_post($updated_post);

        update_post_meta($company_id, 'authorized', $authorized);

        wp_send_json_success('Company updated successfully.');
    }

    /**
     * Delete all clients associated with a company when the company is deleted.
     *
     * @param int $post_id The post ID of the company being deleted.
     * @since 1.0.0
     */
    public function delete_company_clients($post_id)
    {
        if (get_post_type($post_id) !== 'company') {
            return;
        }

        $clients = $this->get_company_employees($post_id);

        foreach ($clients as $client) {
            wp_delete_post($client->ID, true);
        }
    }

    /**
     * Update client preferences when company authorized status changes.
     *
     * @param int    $meta_id    ID of updated metadata entry.
     * @param int    $object_id  Object ID.
     * @param string $meta_key   Meta key.
     * @param mixed  $meta_value Meta value.
     * @since 1.0.0
     */
    public function update_client_preferences($meta_id, $object_id, $meta_key, $meta_value)
    {
        if (get_post_type($object_id) !== 'company' || $meta_key !== 'authorized') {
            return;
        }

        $clients = $this->get_company_employees($object_id);

        foreach ($clients as $client) {
            if ($meta_value === 'no') {
                update_post_meta($client->ID, 'preferred_contact_method', 'Declined');
            } else {
                update_post_meta($client->ID, 'preferred_contact_method', 'to be defined');
            }
        }
    }

    /**
     * Get all employees (clients) associated with a company.
     *
     * @param int $company_id The company ID.
     * @return array An array of client post objects.
     * @since 1.0.0
     */
    private function get_company_employees($company_id)
    {
        $args = array(
            'post_type' => 'client',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'company',
                    'value' => $company_id,
                    'compare' => '=',
                ),
            ),
        );

        return get_posts($args);
    }
}
