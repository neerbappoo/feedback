<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://serviqual.com/
 * @since             1.0.0
 * @package           Client_Feedback_System
 *
 * @wordpress-plugin
 * Plugin Name:       Client Feedback System
 * Plugin URI:        https://serviqual.com/
 * Description:       A comprehensive client feedback management system for WordPress.
 * Version:           1.0.0
 * Author:            ServiQual
 * Author URI:        https://serviqual.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       client-feedback-system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('CFS_VERSION', '1.0.0');
define('CFS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CFS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-client-feedback-system-activator.php
 */
function activate_client_feedback_system()
{
	require_once CFS_PLUGIN_DIR . 'includes/class-client-feedback-system-activator.php';
	Client_Feedback_System_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-client-feedback-system-deactivator.php
 */
function deactivate_client_feedback_system()
{
	require_once CFS_PLUGIN_DIR . 'includes/class-client-feedback-system-deactivator.php';
	Client_Feedback_System_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_client_feedback_system');
register_deactivation_hook(__FILE__, 'deactivate_client_feedback_system');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once CFS_PLUGIN_DIR . 'includes/class-client-feedback-system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_client_feedback_system()
{
	$plugin = new Client_Feedback_System();
	$plugin->run();
}
run_client_feedback_system();