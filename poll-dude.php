<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/liaisontw/poll-dude
 * @since             1.0.0
 * @package           Poll Dude
 *
 * @wordpress-plugin
 * Plugin Name:       Poll Dude
 * Plugin URI:        https://github.com/liaisontw/poll-dude
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Liaison Chang
 * Author URI:        https://github.com/liaisontw/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       poll-dude
 * Domain Path:       /languages
 */

// Exit If Accessed Directly
if(!defined('ABSPATH')){
    exit;
}
 

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'POLL_DUDE_VERSION', '1.0.0' );

// Create Text Domain For Translations
//add_action( 'plugins_loaded', 'polldude_textdomain' );
add_action( 'admin_menu', 'polldude_textdomain' );
function polldude_textdomain() {
	load_plugin_textdomain( 'poll-dude-domain' );
}


// polldude Table Name
global $wpdb;
$wpdb->pollsq   = $wpdb->prefix.'pollsq';
$wpdb->pollsa   = $wpdb->prefix.'pollsa';
$wpdb->pollsip  = $wpdb->prefix.'pollsip';

global $poll_dude_base;
$poll_dude_base = plugin_basename(__FILE__);

if( ! function_exists( 'removeslashes' ) ) {
	function removeslashes( $string ) {
		$string = implode( '', explode( '\\', $string ) );
		return stripslashes( trim( $string ) );
	}
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
//require plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude.php';

### Function: Poll Administration Menu


add_action( 'admin_menu', 'polldude_menu' );
function polldude_menu() {
	$page_title = __( 'Poll Dude', 'poll-dude-domain' );
	$menu_title = __( 'Poll Dude', 'poll-dude-domain' );
	$capability = 'manage_options';
	$menu_slug  = 'poll_dude_manager';

	add_menu_page(
		$page_title,
		$menu_title,
		$capability,
		$menu_slug,
		'',
		'dashicons-chart-bar'
	);

	$parent_slug = 'poll_dude_manager';
	$page_title  = __( 'Add Poll', 'poll-dude-domain' );
	$menu_title  = __( 'Add Poll', 'poll-dude-domain' );
	$capability  = 'manage_options';
	$menu_slug   = plugin_dir_path(__FILE__) . '/admin/page-poll-dude-add-form.php';

	
	add_submenu_page( 
		$parent_slug, 
		$page_title, 
		$menu_title, 
		$capability, 
		$menu_slug 
	);

	$parent_slug = 'poll_dude_manager';
	$page_title  = __( 'Poll Setting', 'poll-dude-domain' );
	$menu_title  = __( 'Poll Setting', 'poll-dude-domain' );
	$capability  = 'manage_options';
	$menu_slug   = 'setting_polls';

	add_submenu_page( 
		$parent_slug, 
		$page_title, 
		$menu_title, 
		$capability, 
		$menu_slug 
	);
	

}




// Load Shortcodes
require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-shortcodes.php');

// Check if admin and include admin scripts
add_action('admin_init','poll_dude_scripts_admin');
function poll_dude_scripts_admin(){
	//wp_enqueue_script('poll-dude-admin', plugin_dir_url( __FILE__ ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);
	wp_enqueue_script('poll-dude-admin', plugin_dir_url( __FILE__ ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ));

	wp_localize_script('poll-dude-admin', 'pollsAdminL10n', array(
			'admin_ajax_url' => admin_url('admin-ajax.php'),
			'text_direction' => is_rtl() ? 'right' : 'left',
			'text_delete_poll' => __('Delete Poll', 'wp-polls'),
			'text_no_poll_logs' => __('No poll logs available.', 'wp-polls'),
			'text_delete_all_logs' => __('Delete All Logs', 'wp-polls'),
			'text_checkbox_delete_all_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs.', 'wp-polls'),
			'text_delete_poll_logs' => __('Delete Logs For This Poll Only', 'wp-polls'),
			'text_checkbox_delete_poll_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs for this poll ONLY.', 'wp-polls'),
			'text_delete_poll_ans' => __('Delete Poll Answer', 'wp-polls'),
			'text_open_poll' => __('Open Poll', 'wp-polls'),
			'text_close_poll' => __('Close Poll', 'wp-polls'),
			'text_answer' => __('Answer', 'wp-polls'),
			'text_remove_poll_answer' => __('Remove', 'wp-polls')
		));

}


if ( is_admin() ) {
	//add_action('admin_enqueue_scripts', 'poll_dude_scripts_admin');
	
	
	// Load Custom Post Type
	//require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-custom-post-type.php');

	// Load Settings
	//require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-settings.php');
	
	// Load Post Fields
	//require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-fields.php');
}


	

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-poll-dude-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-activator.php';
	Plugin_Name_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-poll-dude-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Plugin_Name();
	$plugin->run();

}
//run_plugin_name();
