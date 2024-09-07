<?php

/**
 * Class CFS_Main_Options
 *
 * Handles the creation and management of the main options page for the Client Feedback System plugin.
 *
 * @since 1.0.0
 */
class CFS_Main_Options
{

    private $options;
    // private $client_manager;
    private $client_management;
    private $company_manager;
    private $feedback_settings;
    private $form_manager;
    private $landing_pages;
    private $dispatch;
    private $feedback_collection;

    public function __construct($client_management, $company_manager, $feedback_settings, $form_manager, $landing_pages, $feedback_collection)
    {
        // $this->client_manager = $client_manager;
        $this->client_management = new ClientManagement();
        // $this->company_manager = $company_manager;
        $this->company_manager = new CFS_Company();
        $this->feedback_settings = new CFS_feedback_system_settings();
        $this->form_manager = $form_manager;
        $this->landing_pages = $landing_pages;
        $this->dispatch = new dispatch();
        $this->feedback_collection = $feedback_collection;
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page()
    {
        add_menu_page(
            'Client Feedback System',
            'Client Feedback System',
            'manage_options',
            'cfs-main-options',
            array($this, 'create_admin_page'),
            'dashicons-feedback',
            80
        );

        add_submenu_page(
            'cfs-main-options',
            'Global Settings',
            'Global Settings',
            'manage_options',
            'cfs-main-options'
        );

        add_submenu_page(
            'cfs-main-options',
            'Manage Companies',
            'Manage Companies',
            'manage_options',
            'cfs-company-options',
            array($this->company_manager, 'render_options_page')
        );

        add_submenu_page(
            'cfs-main-options',
            'Manage Feedback Seetings',
            'Manage Feedback Seetings',
            'manage_options',
            'cfs-feedback-system-settings-options',
            array($this->feedback_settings, 'render_options_page')
        );

        add_submenu_page(
            'cfs-main-options',
            'Client Management',
            'Client Management',
            'manage_options',
            'cfs-client-management',
            array($this->client_management, 'render_options_page')
        );

        add_submenu_page(
            'cfs-main-options',
            'Landing Pages',
            'Landing Pages',
            'manage_options',
            'cfs-landing-page',
            array($this->landing_pages, 'render_options_page')
        );

        add_submenu_page(
            'cfs-main-options',
            'Manage Form',
            'Manage Form',
            'manage_options',
            'cfs-form-options',
            array($this->form_manager, 'render_options_page')
        );

        add_submenu_page(
            'cfs-main-options',
            'Dispatch',
            'Dispatch',
            'manage_options',
            'cfs-dispatch-options',
            array($this->dispatch, 'render_options_page')
        );
        add_submenu_page(
            'cfs-main-options',
            'Feedback Collection',
            'Feedback Collection',
            'manage_options',
            'cfs-feedback-collection',
            array($this->feedback_collection, 'render_options_page')
        );
    }

    public function create_admin_page()
    {
        $this->options = get_option('cfs_main_options');
?>
        <div class="wrap">
            <h1>Client Feedback System Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('cfs_main_option_group');
                do_settings_sections('cfs-main-options');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function page_init()
    {
        register_setting(
            'cfs_main_option_group',
            'cfs_main_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'cfs_main_setting_section',
            'Global Settings',
            array($this, 'print_section_info'),
            'cfs-main-options'
        );

        add_settings_field(
            'preferred_contact_methods',
            'Preferred Contact Methods',
            array($this, 'preferred_contact_methods_callback'),
            'cfs-main-options',
            'cfs_main_setting_section'
        );
    }

    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['preferred_contact_methods']))
            $new_input['preferred_contact_methods'] = sanitize_text_field($input['preferred_contact_methods']);
        return $new_input;
    }

    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    public function preferred_contact_methods_callback()
    {
        $value = isset($this->options['preferred_contact_methods']) ? $this->options['preferred_contact_methods'] : '';
        echo '<textarea id="preferred_contact_methods" name="cfs_main_options[preferred_contact_methods]" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">Enter preferred contact methods, one per line. These will be available as options when managing clients.</p>';
    }
}
