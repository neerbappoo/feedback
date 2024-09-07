<?php

/**
 * Class CFS_feedback_system_settings
 *
 * Handles the creation rules for feedback entry points,
 * including the options page for CRUD operations.
 *
 * @since 1.0.0
 */
class CFS_feedback_system_settings
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_feedback_cpts'));
        add_action('add_meta_boxes', array($this, 'add_feedback_meta_boxes'));
        add_action('save_post', array($this, 'save_feedback_meta_box_data'));
        add_action('wp_ajax_crud_feedback_cpt', array($this, 'crud_feedback_cpt'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook)
    {
        error_log('Current hook: ' . $hook);

        // Only enqueue on the company options page
        if ('client-feedback-system_page_cfs-feedback-system-settings-options' !== $hook) {
            return;
        }

        // Get the plugin's root directory URL
        $plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));

        error_log('Plugin URL: ' . $plugin_url);

        $css_url = $plugin_url . 'includes/modules/feedback-system-settings/css/feedback-system-settings.css';
        $js_url = $plugin_url . 'includes/modules/feedback-system-settings/js/feedback-system-settings.js';

        error_log('CSS URL: ' . $css_url);
        error_log('JS URL: ' . $js_url);

        // Enqueue CSS
        wp_enqueue_style(
            'feedback-system-settings-css',
            $css_url,
            array(),
            '1.0.0'
        );

        // Enqueue JS
        wp_enqueue_script(
            'feedback-system-settings-js',
            $js_url,
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function render_options_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/feedback-system-settings-options-page.php';
    }

    private function render_cpt_table($post_type)
    {
        $posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1));
?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <?php if ($post_type === 'entry_point'): ?>
                        <th>Associated Modes of Contact</th>
                    <?php elseif ($post_type === 'contact_mode'): ?>
                        <th>Preferred Feedback Forms</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post) :
                    $meta_value = '';
                    $metaboxes = array();

                    if ($post_type === 'entry_point') {
                        $associated_modes = get_post_meta($post->ID, '_associated_modes', true);
                        if (!empty($associated_modes)) {
                            $mode_titles = array_map(function ($mode_id) {
                                return get_the_title($mode_id) . ' (ID: ' . $mode_id . ')';
                            }, $associated_modes);
                            $meta_value = implode(', ', $mode_titles);
                            $metaboxes[] = array(
                                'id' => '_associated_modes',
                                'title' => 'Associated Modes',
                                'value' => $meta_value
                            );
                        }
                    } elseif ($post_type === 'contact_mode') {
                        $preferred_forms = get_post_meta($post->ID, '_preferred_forms', true);
                        if (!empty($preferred_forms)) {
                            $form_titles = array_map(function ($form_id) {
                                return get_the_title($form_id) . ' (ID: ' . $form_id . ')';
                            }, $preferred_forms);
                            $meta_value = implode(', ', $form_titles);
                            $metaboxes[] = array(
                                'id' => '_preferred_forms',
                                'title' => 'Preferred Forms',
                                'value' => $meta_value
                            );
                        }
                    }
                ?>
                    <tr>
                        <td><?php echo esc_html($post->post_title); ?></td>
                        <?php if ($post_type === 'entry_point' || $post_type === 'contact_mode'): ?>
                            <td>
                                <?php foreach ($metaboxes as $metabox): ?>
                                    <div>
                                        <strong><?php echo esc_html($metabox['title']); ?> (ID:
                                            <?php echo esc_html($metabox['id']); ?>)</strong>:
                                        <?php echo esc_html($metabox['value']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <button class="edit-cpt" data-id="<?php echo $post->ID; ?>"
                                data-type="<?php echo $post_type; ?>">Edit</button>
                            <button class="delete-cpt" data-id="<?php echo $post->ID; ?>"
                                data-type="<?php echo $post_type; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="add-cpt" data-type="<?php echo $post_type; ?>">Add New</button>

<?php
    }

    public function crud_feedback_cpt()
    {
        if (!isset($_POST['cfs_feedback_settings_nonce']) || !wp_verify_nonce($_POST['cfs_feedback_settings_nonce'], 'cfs_feedback_settings')) {
            error_log('Invalid nonce in crud_feedback_cpt');
            $this->send_json_response(false, 'Invalid nonce');
            return;
        }

        // Validate and sanitize input
        $action = isset($_POST['crud_action']) ? sanitize_text_field($_POST['crud_action']) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

        error_log("Received request - Action: $action, Post Type: $post_type, Post ID: $post_id, Title: $title");

        if (empty($action) || empty($post_type)) {
            error_log('Missing required fields in crud_feedback_cpt');
            $this->send_json_response(false, 'Missing required fields');
            return;
        }

        switch ($action) {
            case 'create':
                if (empty($title)) {
                    error_log('Empty title for create action');
                    $this->send_json_response(false, 'Title cannot be empty');
                    return;
                }

                $post_data = array(
                    'post_title'  => $title,
                    'post_type'   => $post_type,
                    'post_status' => 'publish',
                );

                error_log('Attempting to create post: ' . json_encode($post_data));

                $post_id = wp_insert_post($post_data, true);

                if (is_wp_error($post_id)) {
                    error_log('Error creating post: ' . $post_id->get_error_message());
                    $this->send_json_response(false, $post_id->get_error_message());
                    return;
                }

                // Save metadata
                if ($post_type === 'entry_point' && isset($_POST['associated_modes'])) {
                    update_post_meta($post_id, '_associated_modes', array_map('intval', $_POST['associated_modes']));
                } elseif ($post_type === 'contact_mode' && isset($_POST['preferred_forms'])) {
                    update_post_meta($post_id, '_preferred_forms', array_map('intval', $_POST['preferred_forms']));
                }

                error_log('Post created successfully: ' . $post_id);
                $this->send_json_response(true, 'Post created successfully', array('post_id' => $post_id));
                break;
            case 'update':
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $title,
                ));

                // Update metadata
                if ($post_type === 'entry_point' && isset($_POST['associated_modes'])) {
                    update_post_meta($post_id, '_associated_modes', array_map('intval', $_POST['associated_modes']));
                } elseif ($post_type === 'contact_mode' && isset($_POST['preferred_forms'])) {
                    update_post_meta($post_id, '_preferred_forms', array_map('intval', $_POST['preferred_forms']));
                }

                $this->send_json_response(true, 'Post updated successfully');
                break;
            case 'delete':
                wp_delete_post($post_id, true);
                $this->send_json_response(true, 'Post deleted successfully');
                break;
            case 'read':
                $post = get_post($post_id);
                $data = array(
                    'title' => $post->post_title,
                );

                if ($post_type === 'entry_point') {
                    $data['associated_modes'] = get_post_meta($post_id, '_associated_modes', true);
                } elseif ($post_type === 'contact_mode') {
                    $data['preferred_forms'] = get_post_meta($post_id, '_preferred_forms', true);
                }

                $this->send_json_response(true, 'Post data retrieved successfully', $data);
                break;
        }
    }
    private function send_json_response($success, $message, $data = array())
    {
        $response = array(
            'success' => $success,
            'message' => $message,
            'data' => $data
        );

        error_log('Sending JSON response: ' . json_encode($response));

        wp_send_json($response);
        exit; // Ensure that the script stops executing after sending the response
    }
    // Register Custom Post Types
    public function register_feedback_cpts()
    {
        // Entry Points CPT
        register_post_type('entry_point', [
            'labels' => [
                'name' => 'Entry Points',
                'singular_name' => 'Entry Point',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title'],
        ]);

        // Modes of Contact CPT
        register_post_type('contact_mode', [
            'labels' => [
                'name' => 'Modes of Contact',
                'singular_name' => 'Mode of Contact',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title'],
        ]);

        // Feedback Forms CPT is assumed to be already registered
    }

    // Add Meta Boxes
    public function add_feedback_meta_boxes()
    {
        add_meta_box('entry_point_modes', 'Associated Modes of Contact', array($this, 'entry_point_modes_callback'), 'entry_point', 'normal', 'default');
        add_meta_box('contact_mode_forms', 'Preferred Feedback Forms', array($this, 'contact_mode_forms_callback'), 'contact_mode', 'normal', 'default');
    }

    // Entry Point Meta Box Callback
    public function entry_point_modes_callback($post)
    {
        wp_nonce_field('entry_point_modes_nonce', 'entry_point_modes_nonce');
        $associated_modes = get_post_meta($post->ID, '_associated_modes', true);
        $all_modes = get_posts(['post_type' => 'contact_mode', 'numberposts' => -1]);

        echo '<select name="associated_modes[]" multiple>';
        foreach ($all_modes as $mode) {
            $selected = in_array($mode->ID, (array)$associated_modes) ? 'selected' : '';
            echo "<option value='{$mode->ID}' {$selected}>{$mode->post_title} (ID: {$mode->ID})</option>";
        }
        echo '</select>';
    }

    // Contact Mode Meta Box Callback
    public function contact_mode_forms_callback($post)
    {
        wp_nonce_field('contact_mode_forms_nonce', 'contact_mode_forms_nonce');
        $preferred_forms = get_post_meta($post->ID, '_preferred_forms', true);
        $all_forms = get_posts(['post_type' => 'landing_page', 'numberposts' => -1]);

        echo '<select name="preferred_forms[]" multiple>';
        foreach ($all_forms as $form) {
            $selected = in_array($form->ID, (array)$preferred_forms) ? 'selected' : '';
            echo "<option value='{$form->ID}' {$selected}>{$form->post_title} (ID: {$form->ID})</option>";
        }
        echo '</select>';
    }

    // Save Meta Box Data
    public function save_feedback_meta_box_data($post_id)
    {
        error_log('Saving meta box data for post ID: ' . $post_id);

        if (
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
        ) {
            error_log('Autosave detected, skipping meta box data save');
            return;
        }

        if (isset($_POST['entry_point_modes_nonce']) && wp_verify_nonce($_POST['entry_point_modes_nonce'], 'entry_point_modes_nonce')) {
            $associated_modes = isset($_POST['associated_modes']) ? array_map('intval', $_POST['associated_modes']) : [];
            error_log('Updating associated modes: ' . json_encode($associated_modes));
            update_post_meta(
                $post_id,
                '_associated_modes',
                $associated_modes
            );
        } else {
            error_log('Invalid nonce for entry point modes');
        }

        if (isset($_POST['contact_mode_forms_nonce']) && wp_verify_nonce($_POST['contact_mode_forms_nonce'], 'contact_mode_forms_nonce')) {
            $preferred_forms = isset($_POST['preferred_forms']) ? array_map('intval', $_POST['preferred_forms']) : [];
            error_log('Updating preferred forms: ' . json_encode($preferred_forms));
            update_post_meta(
                $post_id,
                '_preferred_forms',
                $preferred_forms
            );
        } else {
            error_log('Invalid nonce for contact mode forms');
        }
    }
}
