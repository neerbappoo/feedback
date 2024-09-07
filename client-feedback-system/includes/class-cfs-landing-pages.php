<?php
/**
 * Class CFS_Landing_Page
 *
 * Handles the creation and management of the Landing Page custom post type.
 *
 * @since 1.0.0
 */
class CFS_Landing_Page
{

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
    }

    /**
     * Register the Landing Page custom post type.
     *
     * @since 1.0.0
     */
    public function register_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'Landing Pages',
                'singular_name' => 'Landing Page',
                'menu_name' => 'Landing Pages',
                'all_items' => 'All Landing Pages',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Landing Page',
                'edit_item' => 'Edit Landing Page',
                'view_item' => 'View Landing Page',
                'search_items' => 'Search Landing Pages',
                'not_found' => 'No landing pages found',
                'not_found_in_trash' => 'No landing pages found in trash',
            ),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-users',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'delete_with_user' => false,
        );

        register_post_type('landing_page', $args);
    }

    public function render_options_page()
    {
        ?>
        <div class="wrap">
            <h1>Landing Pages</h1>
            <!-- Add your options page content here -->
            <p>This is the landing pages options page.</p>
        </div>
        <?php
    }

}