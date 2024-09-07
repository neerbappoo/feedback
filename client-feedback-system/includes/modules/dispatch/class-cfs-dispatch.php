<?php


class dispatch
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('wp_ajax_cfs_filter_clients', array($this, 'filter_clients'));
        add_action('wp_ajax_cfs_send_feedback', array($this, 'send_feedback'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook)
    {
        error_log('Current hook: ' . $hook);

        // Only enqueue on the company options page
        if ('client-feedback-system_page_cfs-dispatch-options' !== $hook) {
            return;
        }

        // Get the plugin's root directory URL
        $plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));

        error_log('Plugin URL: ' . $plugin_url);

        $css_url = $plugin_url . 'includes/modules/dispatch/css/dispatch.css';
        $js_url = $plugin_url . 'includes/modules/dispatch/js/dispatch.js';

        error_log('CSS URL: ' . $css_url);
        error_log('JS URL: ' . $js_url);

        // Enqueue CSS
        wp_enqueue_style(
            'cfs-dispatch-css',
            $css_url,
            array(),
            '1.0.0'
        );

        // Enqueue JS
        wp_enqueue_script(
            'cfs-dispatch-js',
            $js_url,
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function render_options_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/dispatch-options-page.php';
    }

    public function filter_clients()
    {
        $company_id = isset($_POST['company_id']) ? sanitize_text_field($_POST['company_id']) : '';
        $contact_mode_id = isset($_POST['contact_mode_id']) ? intval($_POST['contact_mode_id']) : 0;

        $args = array(
            'post_type' => 'client',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'client_feedback',
                    'value' => 'Yes',
                    'compare' => '='
                )
            )
        );

        if ($company_id) {
            $args['meta_query'][] = array(
                'key' => 'client_company',
                'value' => $company_id,
                'compare' => '='
            );
        }

        $clients = get_posts($args);
        $formatted_clients = array();

        foreach ($clients as $client) {
            $client_entry_points = get_post_meta($client->ID, 'client_entry_points', true);
            $client_feedback_data = get_post_meta($client->ID, 'client_feedback_data', true);
            $client_feedback_data = json_decode($client_feedback_data, true);

            if (!is_array($client_entry_points)) {
                $client_entry_points = array();
            }

            if (!is_array($client_feedback_data)) {
                $client_feedback_data = array();
            }

            $contact_modes = array();
            $feedback_models = array();

            foreach ($client_entry_points as $entry_point) {
                if (isset($client_feedback_data[$entry_point])) {
                    $contact_mode = $client_feedback_data[$entry_point]['contact_mode'];
                    $feedback_model = $client_feedback_data[$entry_point]['feedback_model'];

                    if ($contact_mode_id && $contact_mode != $contact_mode_id) {
                        continue;
                    }

                    $contact_modes[] = array(
                        'id' => $contact_mode,
                        'name' => get_the_title($contact_mode)
                    );

                    $feedback_model_link = get_permalink($feedback_model);
                    $feedback_model_link = add_query_arg(array(
                        'client_name' => urlencode(str_replace(' ', '_', $client->post_title)),
                        'client_email' => urlencode(get_post_meta($client->ID, 'client_email', true)),
                        'client_company' => urlencode(str_replace(' ', '_', get_post_meta($client->ID, 'client_company', true)))
                    ), $feedback_model_link);

                    $feedback_models[] = array(
                        'id' => $feedback_model,
                        'name' => get_the_title($feedback_model),
                        'link' => $feedback_model_link
                    );
                }
            }

            if ($contact_mode_id && empty($contact_modes)) {
                continue;
            }

            $formatted_clients[] = array(
                'ID' => $client->ID,
                'name' => $client->post_title,
                'email' => get_post_meta($client->ID, 'client_email', true),
                'phone' => get_post_meta($client->ID, 'client_phone', true),
                'company' => get_post_meta($client->ID, 'client_company', true),
                'contact_modes' => $contact_modes,
                'feedback_models' => $feedback_models,
            );
        }

        wp_send_json_success($formatted_clients);
    }

    public function send_feedback()
    {
        $client_ids = isset($_POST['client_ids']) ? array_map('intval', $_POST['client_ids']) : array();

        if (empty($client_ids)) {
            wp_send_json_error('No clients selected.');
        }

        foreach ($client_ids as $client_id) {
            $client_email = get_post_meta($client_id, 'client_email', true);
            $client_name = get_the_title($client_id);
            $client_entry_points = get_post_meta($client_id, 'client_entry_points', true);
            $client_feedback_data = get_post_meta($client_id, 'client_feedback_data', true);
            $client_feedback_data = json_decode($client_feedback_data, true);

            if (!is_array($client_entry_points)) {
                $client_entry_points = array();
            }

            if (!is_array($client_feedback_data)) {
                $client_feedback_data = array();
            }

            $message = "Dear {$client_name},\n\n";
            $message .= "Please find below the links to the feedback forms:\n\n";

            foreach ($client_entry_points as $entry_point) {
                if (isset($client_feedback_data[$entry_point])) {
                    $feedback_model = $client_feedback_data[$entry_point]['feedback_model'];
                    $feedback_model_link = get_permalink($feedback_model);
                    $feedback_model_link = add_query_arg(array(
                        'client_name' => urlencode(str_replace(' ', '_', $client_name)),
                        'client_email' => urlencode($client_email),
                        'client_company' => urlencode(str_replace(' ', '_', get_post_meta($client_id, 'client_company', true)))
                    ), $feedback_model_link);

                    $message .= "- " . get_the_title($entry_point) . ": " . $feedback_model_link . "\n";
                } else {
                    $message .= "- " . get_the_title($entry_point) . ": Link not available\n";
                }
            }

            $message .= "\nThank you for your cooperation.\n\nBest regards,\nYour Company Name";

            $subject = "Feedback Request";
            $headers = array('Content-Type: text/plain; charset=UTF-8');

            wp_mail($client_email, $subject, $message, $headers);
        }

        wp_send_json_success('Feedback sent successfully to selected clients.');
    }
}
