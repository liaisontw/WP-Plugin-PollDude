<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Poll_Dude_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	/*
	function polldude_textdomain() {
	load_plugin_textdomain( 'poll-dude-domain' );
}
*/
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'poll-dude-domain',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		wp_localize_script('poll-dude-admin', 'pollsAdminL10n', array(
				'admin_ajax_url' => admin_url('admin-ajax.php'),
				'text_direction' => is_rtl() ? 'right' : 'left',
				'text_delete_poll' => __('Delete Poll', 'poll-dude-domain'),
				'text_no_poll_logs' => __('No poll logs available.', 'poll-dude-domain'),
				'text_delete_all_logs' => __('Delete All Logs', 'poll-dude-domain'),
				'text_checkbox_delete_all_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs.', 'poll-dude-domain'),
				'text_delete_poll_logs' => __('Delete Logs For This Poll Only', 'poll-dude-domain'),
				'text_checkbox_delete_poll_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs for this poll ONLY.', 'poll-dude-domain'),
				'text_delete_poll_ans' => __('Delete Poll Answer', 'poll-dude-domain'),
				'text_open_poll' => __('Open Poll', 'poll-dude-domain'),
				'text_close_poll' => __('Close Poll', 'poll-dude-domain'),
				'text_answer' => __('Answer', 'poll-dude-domain'),
				'text_remove_poll_answer' => __('Remove', 'poll-dude-domain')
			));

	}



}
