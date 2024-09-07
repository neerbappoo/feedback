<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://serviqual.com/
 * @since      1.0.0
 *
 * @package    Client_Feedback_System
 * @subpackage Client_Feedback_System/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Client_Feedback_System
 * @subpackage Client_Feedback_System/includes
 * @author     ServiQual <contact@serviqual.com>
 */
class Client_Feedback_System_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'client-feedback-system',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
