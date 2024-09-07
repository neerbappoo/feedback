<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://serviqual.com/
 * @since      1.0.0
 *
 * @package    Client_Feedback_System
 * @subpackage Client_Feedback_System/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Client_Feedback_System
 * @subpackage Client_Feedback_System/includes
 * @author     ServiQual <contact@serviqual.com>
 */
class Client_Feedback_System
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Client_Feedback_System_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('CLIENT_FEEDBACK_SYSTEM_VERSION')) {
			$this->version = CLIENT_FEEDBACK_SYSTEM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'client-feedback-system';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		//$this->setup_admin_menus();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Client_Feedback_System_Loader. Orchestrates the hooks of the plugin.
	 * - Client_Feedback_System_i18n. Defines internationalization functionality.
	 * - Client_Feedback_System_Admin. Defines all hooks for the admin area.
	 * - Client_Feedback_System_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-client-feedback-system-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-client-feedback-system-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-client-feedback-system-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-client-feedback-system-public.php';

		/**
		 * The class responsible for handling the Client custom post type and fields.
		 */
		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-client.php';

		/**
		 * The class responsible for handling the Client custom post type and fields.
		 */
		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-client-crud.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/modules/client/class-cfs-client.php';

		/**
		 * The class responsible for handling the Client custom post type and fields.
		 */
		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-client-crud.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/modules/dispatch/class-cfs-dispatch.php';

		/**
		 * The class responsible for handling the Company custom post type and fields.
		 */
		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-company.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/modules/company/class-cfs-company.php';

		/**
		 * The class responsible for handling the Company custom post type and fields.
		 */
		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-company.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/modules/feedback-system-settings/class-cfs-feedback-system-settings.php';

		/**
		 * The class responsible for handling the Global options.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-form-manager.php';

		/**
		 * The class responsible for handling the Global options.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-landing-pages.php';

		/**
		 * The class responsible for handling the Global options.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-feedback-collection.php';

		/**
		 * The class responsible for handling the Global options.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cfs-main-options.php';

		$this->loader = new Client_Feedback_System_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Client_Feedback_System_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Client_Feedback_System_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Client_Feedback_System_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		// $cfs_client = new CFS_Client();
		$cfs_dispatch = new dispatch();
		$cfs_client_management = new ClientManagement();
		$cfs_company = new CFS_Company();
		$cfs_feedback_settings = new CFS_feedback_system_settings();
		$cfs_form = new CFS_Form_Manager();
		$cfs_landing = new CFS_Landing_Page();
		$cfs_feedback_collection = new CFS_Feedback_Collection();

		$cfs_main_options = new CFS_Main_Options($cfs_client_management, $cfs_dispatch, $cfs_company, $cfs_feedback_settings, $cfs_form, $cfs_landing, $cfs_feedback_collection);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Client_Feedback_System_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'localize_ajax_url');
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Client_Feedback_System_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
