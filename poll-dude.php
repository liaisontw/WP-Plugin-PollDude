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
//define( 'POLL_DUDE_DOMAIN', 'POLL-DUDE' );
//define( 'POLL_DUDE_NAME_SPACE', 'POLL_DUDE' );



// polldude Table Name
global $wpdb;
$wpdb->pollsq   = $wpdb->prefix.'pollsq';
$wpdb->pollsa   = $wpdb->prefix.'pollsa';
$wpdb->pollsip  = $wpdb->prefix.'pollsip';

require_once plugin_dir_path(__FILE__) . '/includes/class-poll-dude.php';
global $poll_dude;
$poll_dude = new Poll_Dude();


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
//require plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude.php';

### Function: Poll Administration Menu

### Function: Manage Polls


/*
add_action('admin_enqueue_scripts','poll_dude_scripts_admin');
function poll_dude_scripts_admin($hook_suffix){
	$poll_admin_pages = array('poll-dude/poll-dude.php', 'poll-dude/includes/page-poll-dude-add-form.php', 'poll-dude/includes/page-poll-dude-control-panel.php');
	if(in_array($hook_suffix, $poll_admin_pages, true)) {
		wp_enqueue_style('poll-dude-admin', plugin_dir_url( __FILE__ ) . 'admin/css/poll-dude-admin-css.css', false, POLL_DUDE_VERSION, 'all');
		wp_enqueue_script('poll-dude-admin', plugin_dir_url( __FILE__ ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);
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
*/

### Function: Enqueue Polls JavaScripts/CSS
add_action('wp_enqueue_scripts', 'poll_dude_scripts');
function poll_dude_scripts() {
	wp_enqueue_style('poll-dude', plugins_url('poll-dude/public/css/poll-dude-public.css'), false, POLL_DUDE_VERSION, 'all');
	
	$pollbar = get_option( 'poll_bar' );
	if( $pollbar['style'] === 'use_css' ) {
		$pollbar_css = '.wp-polls .pollbar {'."\n";
		$pollbar_css .= "\t".'margin: 1px;'."\n";
		$pollbar_css .= "\t".'font-size: '.($pollbar['height']-2).'px;'."\n";
		$pollbar_css .= "\t".'line-height: '.$pollbar['height'].'px;'."\n";
		$pollbar_css .= "\t".'height: '.$pollbar['height'].'px;'."\n";
		$pollbar_css .= "\t".'background: #'.$pollbar['background'].';'."\n";
		$pollbar_css .= "\t".'border: 1px solid #'.$pollbar['border'].';'."\n";
		$pollbar_css .= '}'."\n";
	} else {
		$pollbar_css = '.wp-polls .pollbar {'."\n";
		$pollbar_css .= "\t".'margin: 1px;'."\n";
		$pollbar_css .= "\t".'font-size: '.($pollbar['height']-2).'px;'."\n";
		$pollbar_css .= "\t".'line-height: '.$pollbar['height'].'px;'."\n";
		$pollbar_css .= "\t".'height: '.$pollbar['height'].'px;'."\n";
		$pollbar_css .= "\t".'background-image: url(\''.plugins_url('wp-polls/images/'.$pollbar['style'].'/pollbg.gif').'\');'."\n";
		$pollbar_css .= "\t".'border: 1px solid #'.$pollbar['border'].';'."\n";
		$pollbar_css .= '}'."\n";
	}
	wp_add_inline_style( 'poll-dude', $pollbar_css );
	$poll_ajax_style = get_option('poll_ajax_style');
	wp_enqueue_script('poll-dude', plugins_url('poll-dude/public/js/poll-dude-public.js'), array('jquery'), POLL_DUDE_VERSION, true);
	wp_localize_script('poll-dude', 'pollsL10n', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'text_wait' => __('Your last request is still being processed. Please wait a while ...', 'wp-polls'),
		'text_valid' => __('Please choose a valid poll answer.', 'wp-polls'),
		'text_multiple' => __('Maximum number of choices allowed: ', 'wp-polls'),
		'show_loading' => (int) $poll_ajax_style['loading'],
		'show_fading' => (int) $poll_ajax_style['fading']
	));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-poll-dude-activator.php
 */
function poll_dude_activate_init($network_wide) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-activator.php';
	Poll_Dude_Activator::activate($network_wide);
}

### Function: Activate Plugin
register_activation_hook( __FILE__, 'poll_dude_activate_init' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-poll-dude-deactivator.php
 */
/*
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}
*/






