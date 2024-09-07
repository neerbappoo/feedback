<?php

function create_cpt_entry_points()
{
    register_post_type(
        'entry_point',
        array(
            'labels' => array(
                'name' => __('Entry Points'),
                'singular_name' => __('Entry Point')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor')
        )
    );
}
add_action('init', 'create_cpt_entry_points');

function create_cpt_mode_of_contact()
{
    register_post_type(
        'mode_of_contact',
        array(
            'labels' => array(
                'name' => __('Modes of Contact'),
                'singular_name' => __('Mode of Contact')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor')
        )
    );
}
add_action('init', 'create_cpt_mode_of_contact');

function create_cpt_feedback_model()
{
    register_post_type(
        'feedback_model',
        array(
            'labels' => array(
                'name' => __('Feedback Models'),
                'singular_name' => __('Feedback Model')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor')
        )
    );
}
add_action('init', 'create_cpt_feedback_model');
function add_entry_point_meta_box()
{
    add_meta_box(
        'entry_point_meta_box',
        'Modes of Contact',
        'render_entry_point_meta_box',
        'entry_point',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_entry_point_meta_box');

function render_entry_point_meta_box($post)
{
    $modes_of_contact = get_post_meta($post->ID, '_modes_of_contact', true);
    $args = array(
        'post_type' => 'mode_of_contact',
        'posts_per_page' => -1
    );
    $contact_modes = get_posts($args);
?>
    <select name="modes_of_contact[]" multiple>
        <?php foreach ($contact_modes as $mode) : ?>
            <option value="<?php echo $mode->ID; ?>"
                <?php echo in_array($mode->ID, (array)$modes_of_contact) ? 'selected' : ''; ?>>
                <?php echo $mode->post_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
}

function save_entry_point_meta_box($post_id)
{
    if (isset($_POST['modes_of_contact'])) {
        update_post_meta($post_id, '_modes_of_contact', $_POST['modes_of_contact']);
    }
}
add_action('save_post_entry_point', 'save_entry_point_meta_box');

function add_mode_of_contact_meta_box()
{
    add_meta_box(
        'mode_of_contact_meta_box',
        'Feedback Models',
        'render_mode_of_contact_meta_box',
        'mode_of_contact',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_mode_of_contact_meta_box');

function render_mode_of_contact_meta_box($post)
{
    $feedback_models = get_post_meta($post->ID, '_feedback_models', true);
    $args = array(
        'post_type' => 'feedback_model',
        'posts_per_page' => -1
    );
    $models = get_posts($args);
?>
    <select name="feedback_models[]" multiple>
        <?php foreach ($models as $model) : ?>
            <option value="<?php echo $model->ID; ?>"
                <?php echo in_array($model->ID, (array)$feedback_models) ? 'selected' : ''; ?>>
                <?php echo $model->post_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
}

function save_mode_of_contact_meta_box($post_id)
{
    if (isset($_POST['feedback_models'])) {
        update_post_meta($post_id, '_feedback_models', $_POST['feedback_models']);
    }
}
add_action('save_post_mode_of_contact', 'save_mode_of_contact_meta_box');

function add_feedback_settings_page()
{
    add_menu_page(
        'Feedback Settings',
        'Feedback Settings',
        'manage_options',
        'feedback-settings',
        'render_feedback_settings_page'
    );
}
add_action('admin_menu', 'add_feedback_settings_page');

function render_feedback_settings_page()
{
?>
    <div class="wrap">
        <h1>Feedback Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('feedback_settings_group');
            do_settings_sections('feedback-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

function feedback_settings_init()
{
    register_setting('feedback_settings_group', 'feedback_settings');

    add_settings_section(
        'feedback_settings_section',
        'Entry Points and Modes of Contact',
        'feedback_settings_section_callback',
        'feedback-settings'
    );

    add_settings_field(
        'entry_points_field',
        'Entry Points',
        'entry_points_field_callback',
        'feedback-settings',
        'feedback_settings_section'
    );

    add_settings_field(
        'modes_of_contact_field',
        'Modes of Contact',
        'modes_of_contact_field_callback',
        'feedback-settings',
        'feedback_settings_section'
    );
}
add_action('admin_init', 'feedback_settings_init');

function feedback_settings_section_callback()
{
    echo 'Configure the entry points and modes of contact.';
}

function entry_points_field_callback()
{
    $entry_points = get_option('feedback_settings')['entry_points'] ?? array();
    $args = array(
        'post_type' => 'entry_point',
        'posts_per_page' => -1
    );
    $points = get_posts($args);
?>
    <select name="feedback_settings[entry_points][]" multiple>
        <?php foreach ($points as $point) : ?>
            <option value="<?php echo $point->ID; ?>"
                <?php echo in_array($point->ID, (array)$entry_points) ? 'selected' : ''; ?>>
                <?php echo $point->post_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
}

function modes_of_contact_field_callback()
{
    $modes_of_contact = get_option('feedback_settings')['modes_of_contact'] ?? array();
    $args = array(
        'post_type' => 'mode_of_contact',
        'posts_per_page' => -1
    );
    $modes = get_posts($args);
?>
    <select name="feedback_settings[modes_of_contact][]" multiple>
        <?php foreach ($modes as $mode) : ?>
            <option value="<?php echo $mode->ID; ?>"
                <?php echo in_array($mode->ID, (array)$modes_of_contact) ? 'selected' : ''; ?>>
                <?php echo $mode->post_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
}

function populate_mode_of_contact_options($entry_point_id)
{
    $modes_of_contact = get_post_meta($entry_point_id, '_modes_of_contact', true);
    if (!empty($modes_of_contact)) {
        $args = array(
            'post_type' => 'mode_of_contact',
            'post__in' => $modes_of_contact
        );
        $contact_modes = get_posts($args);
        foreach ($contact_modes as $mode) {
            echo '<option value="' . $mode->ID . '">' . $mode->post_title . '</option>';
        }
    }
}
