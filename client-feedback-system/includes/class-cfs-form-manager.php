<?php

/**
 * Class CFS_Form_Manager
 *
 * Handles the creation and management of the Feedback Form custom post type,
 * including the options page for CRUD operations.
 *
 * @since 1.0.0
 */
class CFS_Form_Manager
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_form_meta_box'));
        add_action('save_post', array($this, 'save_form_meta'));
        //add_action('admin_menu', array($this, 'add_options_page'));
        add_action('wp_ajax_cfs_create_form', array($this, 'create_form'));
        add_action('wp_ajax_cfs_delete_form', array($this, 'delete_form'));
        add_action('wp_ajax_cfs_update_form', array($this, 'update_form'));
        add_shortcode('cfs_form', array($this, 'render_form_shortcode'));
    }

    /**
     * Register the Feedback Form custom post type.
     *
     * @since 1.0.0
     */
    public function register_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'Feedback Forms',
                'singular_name' => 'Feedback Form',
                'menu_name' => 'Feedback Forms',
                'all_items' => 'All Feedback Forms',
                'edit_item' => 'Edit Feedback Form',
                'view_item' => 'View Feedback Form',
                'view_items' => 'View Feedback Forms',
                'add_new_item' => 'Add New Feedback Form',
                'add_new' => 'Add New Feedback Form',
                'new_item' => 'New Feedback Form',
                'parent_item_colon' => 'Parent Feedback Form:',
                'search_items' => 'Search Feedback Forms',
                'not_found' => 'No feedback forms found',
                'not_found_in_trash' => 'No feedback forms found in Trash',
                'archives' => 'Feedback Form Archives',
                'attributes' => 'Feedback Form Attributes',
                'insert_into_item' => 'Insert into feedback form',
                'uploaded_to_this_item' => 'Uploaded to this feedback form',
                'filter_items_list' => 'Filter feedback forms list',
                'filter_by_date' => 'Filter feedback forms by date',
                'items_list_navigation' => 'Feedback Forms list navigation',
                'items_list' => 'Feedback Forms list',
                'item_published' => 'Feedback form published.',
                'item_published_privately' => 'Feedback form published privately.',
                'item_reverted_to_draft' => 'Feedback form reverted to draft.',
                'item_scheduled' => 'Feedback form scheduled.',
                'item_updated' => 'Feedback form updated.',
                'item_link' => 'Feedback Form Link',
                'item_link_description' => 'A link to a feedback form',
            ),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-feedback',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'delete_with_user' => false,
        );

        register_post_type('feedback_form', $args);
    }

    /**
     * Add meta box for form details.
     *
     * @since 1.0.0
     */
    public function add_form_meta_box()
    {
        add_meta_box(
            'form_details',
            'Form Details',
            array($this, 'render_form_meta_box'),
            'feedback_form',
            'normal',
            'default'
        );
    }

    /**
     * Render the form details meta box.
     *
     * @param WP_Post $post The post object.
     * @since 1.0.0
     */
    public function render_form_meta_box($post)
    {
        // Add nonce for security
        wp_nonce_field('form_details_nonce', 'form_details_nonce');

        // Retrieve current values
        $form_json = get_post_meta($post->ID, '_form_json', true);

        // Output the form fields
?>
<p>
    <label for="form_json">Form JSON:</label>
    <textarea id="form_json" name="form_json"
        style="width: 100%; height: 300px;"><?php echo esc_textarea($form_json); ?></textarea>
</p>
<?php
    }

    /**
     * Save the form details.
     *
     * @param int $post_id The post ID.
     * @since 1.0.0
     */
    public function save_form_meta($post_id)
    {
        // Check if our nonce is set and verify it
        if (!isset($_POST['form_details_nonce']) || !wp_verify_nonce($_POST['form_details_nonce'], 'form_details_nonce')) {
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
        if (isset($_POST['form_json'])) {
            $form_json = json_encode(wp_kses_post($_POST['form_json']));
            update_post_meta($post_id, '_form_json', $form_json);
        }
    }

    /**
     * Add an options page to the WordPress admin menu.
     *
     * @since 1.0.0
     */
    public function add_options_page()
    {
        add_menu_page(
            'Feedback Form Options',
            'Feedback Forms',
            'manage_options',
            'cfs-form-options',
            array($this, 'render_options_page')
        );
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
    <h1>Feedback Form Options</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Form JSON</th>
                <th>Shortcode</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
                    $args = array(
                        'post_type' => 'feedback_form',
                        'posts_per_page' => -1,
                    );

                    $forms = get_posts($args);

                    foreach ($forms as $form) {
                        $form_json = get_post_meta($form->ID, '_form_json', true);
                        $shortcode = '[cfs_form id="' . $form->ID . '"]';
                    ?>
            <tr>
                <td><?php echo $form->ID; ?></td>
                <td><?php echo get_the_title($form->ID); ?></td>
                <td><?php echo esc_html(substr($form_json, 0, 50) . '...'); ?></td>
                <td><?php echo esc_html($shortcode); ?></td>
                <td>
                    <a href="#" class="edit-form" data-form-id="<?php echo $form->ID; ?>"
                        data-form-name="<?php echo esc_attr(get_the_title($form->ID)); ?>"
                        data-form-json="<?php echo htmlspecialchars(json_encode($form_json), ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                    |
                    <a href="#" class="delete-form" data-form-id="<?php echo $form->ID; ?>"
                        data-nonce="<?php echo wp_create_nonce('cfs_delete_form_' . $form->ID); ?>"
                        data-confirm-message="Are you sure you want to delete this form?">Delete</a>
                </td>
            </tr>
            <?php
                    }
                    ?>
        </tbody>
    </table>
    <dialog id="edit-form-form">
        <div class="modal-content" id="modal-content">
            <h2>Edit Form</h2>
            <form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
                <input type="hidden" name="action" value="cfs_update_form">
                <input type="hidden" name="form_id" id="edit-form-id">
                <?php wp_nonce_field('cfs_update_form', 'cfs_update_form_nonce'); ?>
                <label for="edit-form-name">Form Name:</label><br>
                <input type="text" id="edit-form-name" name="form_name" required><br>
                <label for="form-json">Form JSON:</label>
                <textarea id="edit-form-json" name="form_json" style="width: 100%; height: 300px;"></textarea>
                <input type="submit" value="Update Form">
            </form>
            <p class="error"></p>
            <p class="success"></p>
            <p class="debug"></p>
            <button id="close-edit-form-form" class="close-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="currentColor"
                    class="icon icon-tabler icons-tabler-filled icon-tabler-square-rounded-x">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M12 2l.324 .001l.318 .004l.616 .017l.299 .013l.579 .034l.553 .046c4.785 .464 6.732 2.411 7.196 7.196l.046 .553l.034 .579c.005 .098 .01 .198 .013 .299l.017 .616l.005 .642l-.005 .642l-.017 .616l-.013 .299l-.034 .579l-.046 .553c-.464 4.785 -2.411 6.732 -7.196 7.196l-.553 .046l-.579 .034c-.098 .005 -.198 .01 -.299 .013l-.616 .017l-.642 .005l-.642 -.005l-.616 -.017l-.299 -.013l-.579 -.034l-.553 -.046c-4.785 -.464 -6.732 -2.411 -7.196 -7.196l-.046 -.553l-.034 -.579a28.058 28.058 0 0 1 -.013 -.299l-.017 -.616c-.003 -.21 -.005 -.424 -.005 -.642l.001 -.324l.004 -.318l.017 -.616l.013 -.299l.034 -.579l.046 -.553c.464 -4.785 2.411 -6.732 7.196 -7.196l.553 -.046l.579 -.034c.098 -.005 .198 -.01 .299 -.013l.616 -.017c.21 -.003 .424 -.005 .642 -.005zm-1.489 7.14a1 1 0 0 0 -1.218 1.567l1.292 1.293l-1.292 1.293l-.083 .094a1 1 0 0 0 1.497 1.32l1.293 -1.292l1.293 1.292l.094 .083a1 1 0 0 0 1.32 -1.497l-1.292 -1.293l1.292 -1.293l.083 -.094a1 1 0 0 0 -1.497 -1.32l-1.293 1.292l-1.293 -1.292l-.094 -.083z"
                        fill="currentColor" stroke-width="0" />
                </svg></button>
        </div>
    </dialog>
    <h2>Create a new form:</h2>
    <form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">
        <input type="hidden" name="action" value="cfs_create_form">
        <label for="form-name">Form Name:</label>
        <input type="text" id="form-name" name="form_name" required> <br>
        <label for="form-json">Form JSON:</label>
        <textarea id="form-json" name="form_json" style="width: 100%; height: 300px;"></textarea>
        <?php wp_nonce_field('cfs_create_form', 'cfs_create_form_nonce'); ?>
        <input type="submit" value="Create Form">
    </form>
</div>
<?php
    }

    /**
     * Create a new form via AJAX.
     *
     * @since 1.0.0
     */
    public function create_form()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_create_form_nonce'], 'cfs_create_form')) {
            wp_die('You do not have permission to create forms.');
        }

        $form_name = sanitize_text_field($_POST['form_name']);
        $form_json = wp_kses_post($_POST['form_json']);

        $form = array(
            'post_type' => 'feedback_form',
            'post_title' => $form_name,
            'post_status' => 'publish',
        );

        $form_id = wp_insert_post($form);

        if ($form_id) {
            update_post_meta($form_id, '_form_json', $form_json);

            wp_redirect(admin_url('admin.php?page=cfs-form-options'));
            exit;
        } else {
            wp_die('Error creating company.');
        }
    }

    /**
     * Delete a form via AJAX.
     *
     * @since 1.0.0
     */
    public function delete_form()
    {
        if (!current_user_can('manage_options') || !check_ajax_referer('cfs_delete_form_' . $_POST['form_id'], 'nonce', false)) {
            wp_send_json_error('You do not have permission to delete forms.');
        }

        $form_id = intval($_POST['form_id']);

        if (wp_delete_post($form_id, true)) {
            wp_send_json_success('Form deleted successfully.');
        } else {
            wp_send_json_error('Error deleting form.');
        }
    }

    /**
     * Update a form via AJAX.
     *
     * @since 1.0.0
     */
    public function update_form()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['cfs_update_form_nonce'], 'cfs_update_form')) {
            wp_send_json_error('You do not have permission to update forms.');
        }

        $form_id = intval($_POST['form_id']);
        $form_name = sanitize_text_field($_POST['form_name']);
        $form_json = wp_kses_post($_POST['form_json']);

        $updated_post = array(
            'ID' => $form_id,
            'post_title' => $form_name,
        );

        wp_update_post($updated_post);

        update_post_meta($form_id, '_form_json', $form_json);

        wp_send_json_success('Form updated successfully.');
    }

    /**
     * Render the form shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output of the form.
     */
    public function render_form_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'cfs_form');

        $form_id = intval($atts['id']);

        if (!$form_id) {
            return 'Invalid form ID.';
        }

        $form_json = get_post_meta($form_id, '_form_json', true);
        $form_title = get_the_title($form_id);
        //var_dump($form_json);

        if (empty($form_json)) {
            error_log('Form JSON not found for ID: ' . $form_id);
            return 'Form configuration not found.';
        }

        $form_json = json_decode($form_json, true);
        //var_dump($form_json);

        if (is_null($form_json)) {
            $json_error_msg = json_last_error_msg();
            error_log('Error decoding form JSON: ' . $json_error_msg);
            return 'Error loading form configuration.';
        }

        $survey_container_id = 'cfs-survey-' . $form_id;

        wp_localize_script('jquery', 'cfsFormData', array(
            'formId' => $form_id,
            'formTitle' => $form_title,
            'surveyContainerId' => $survey_container_id,
            'formJson' => $form_json,
        ));

        return "<div id='{$survey_container_id}' class='cfs-survey-container' data-form-id='{$form_id}'></div>";
    }
}