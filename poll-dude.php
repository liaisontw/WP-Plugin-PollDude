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

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
//require plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude.php';

### Function: Poll Administration Menu


add_action( 'admin_menu', 'polldude_menu' );
function polldude_menu() {
	//$hook = add_menu_page(

	$capability = 'edit_posts';
	//$function   = '';

	//$icon_encoded = 'PHN2ZyBpZD0iY29udGVudCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMjg4IDIyMCI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNGRkZGRkY7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5pY29uLWJsdWU8L3RpdGxlPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTI2Mi40MSw4MC4xYy04LjQ3LTIyLjU1LTE5LjA1LTQyLjgzLTI5Ljc5LTU3LjFDMjIwLjc0LDcuMjQsMjEwLC41NywyMDEuNDcsMy43OWExMi4zMiwxMi4zMiwwLDAsMC0zLjcyLDIuM2wtLjA1LS4xNUwxNiwxNzMuOTRsOC4yLDE5LjEyLDMwLjU2LTEuOTJ2MTMuMDVhMTIuNTcsMTIuNTcsMCwwLDAsMTIuNTgsMTIuNTZjLjMzLDAsLjY3LDAsMSwwbDU4Ljg1LTQuNzdhMTIuNjUsMTIuNjUsMCwwLDAsMTEuNTYtMTIuNTNWMTg1Ljg2bDEyMS40NS03LjY0YTEzLjg4LDEzLjg4LDAsMCwwLDIuMDkuMjYsMTIuMywxMi4zLDAsMCwwLDQuNDEtLjhDMjg1LjMzLDE3MC43LDI3OC42MywxMjMuMzEsMjYyLjQxLDgwLjFabS0yLjI2LDg5Ljc3Yy0xMC40OC0zLjI1LTMwLjQ0LTI4LjE1LTQ2LjY4LTcxLjM5LTE1LjcyLTQxLjktMTcuNS03My4yMS0xMi4zNC04My41NGE2LjUyLDYuNTIsMCwwLDEsMy4yMi0zLjQ4LDMuODIsMy44MiwwLDAsMSwxLjQxLS4yNGMzLjg1LDAsMTAuOTQsNC4yNiwyMC4zMSwxNi43MUMyMzYuMzYsNDEuNTksMjQ2LjU0LDYxLjE1LDI1NC43NCw4M2MxOC40NCw0OS4xMiwxNy43NCw4My43OSw5LjEzLDg3QTUuOTMsNS45MywwLDAsMSwyNjAuMTUsMTY5Ljg3Wk0xMzAuNiwxOTkuNDFhNC40LDQuNCwwLDAsMS00LDQuMzdsLTU4Ljg1LDQuNzdBNC4zOSw0LjM5LDAsMCwxLDYzLDIwNC4xOVYxOTAuNjJsNjcuNjEtNC4yNVoiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik02LDE4NS4yNmExMC4yNSwxMC4yNSwwLDAsMCwxMC4yNSwxMC4yNSwxMC4wNSwxMC4wNSwwLDAsMCw0LjM0LTFsLTcuOTQtMTguNzNBMTAuMiwxMC4yLDAsMCwwLDYsMTg1LjI2WiIvPjwvc3ZnPgo=';
	add_menu_page(
		__( 'Poll Dude', 'poll-dude-domain' ),
		__( 'Poll Dude', 'poll-dude-domain' ),
		$capability,
		'poll-dude-options',
		'',
		'dashicons-chart-bar'
	);
	
}
//add_action( "admin_menu", array( &$this, 'management_page_load' ) );


/*
add_action( 'admin_menu', 'polldude_menu' );
function polldude_menu() {
	add_menu_page( 
		__( 'Poll Dude', 'poll-dude' ), 
		__( 'Polls', 'poll dude' ), 
		'manage_polls', 
		'wp-polls/polls-manager.php', 
		'', 
		'dashicons-chart-bar' );

	add_submenu_page( 
		'feedback', 
		$page_title, 
		$page_title, 
		$capability, 
		$menu_slug, 
		$function );

	add_submenu_page( 
		plugin_dir_path( __FILE__ ) . '/includes/polls-manager.php', 
		__( 'Manage Polls', 'poll-dude'), 
		__( 'Manage Polls', 'poll-dude' ), 
		'manage_polls', 
		'/includes/polls-manager.php' );
}
*/
// Load Shortcodes
require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-shortcodes.php');

// Check if admin and include admin scripts
if ( is_admin() ) {
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
