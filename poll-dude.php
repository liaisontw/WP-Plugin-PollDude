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
$plugin_name = 'poll-dude';

if( ! function_exists( 'removeslashes' ) ) {
	function removeslashes( $string ) {
		$string = implode( '', explode( '\\', $string ) );
		return stripslashes( trim( $string ) );
	}
}

function poll_dude_time_make($fieldname /*= 'pollq_timestamp'*/) {

	$time_parse = array('_hour'   => 0, '_minute' => 0, '_second' => 0,
						'_day'    => 0, '_month'  => 0, '_year'   => 0
	);

	foreach($time_parse as $key => $value) {
		$poll_dude_time_stamp = $fieldname.$key;

		$time_parse[$key] = isset( $_POST[$poll_dude_time_stamp] ) ? 
				 (int) sanitize_key( $_POST[$poll_dude_time_stamp] ) : 0;
	}

	$return_timestamp = gmmktime( $time_parse['_hour']  , 
								  $time_parse['_minute'], 
								  $time_parse['_second'], 
								  $time_parse['_month'] , 
								  $time_parse['_day']   , 
								  $time_parse['_year']   );

	return 	$return_timestamp;					  
}


// poll_dude_time_select
function poll_dude_time_select($poll_dude_time, $fieldname = 'pollq_timestamp', $display = 'block') {
	
	$time_select = array(
		'_hour'   => array('unit'=>'H', 'min'=>0   , 'max'=>24  , 'padding'=>'H:'),
		'_minute' => array('unit'=>'i', 'min'=>0   , 'max'=>61  , 'padding'=>'M:'),
		'_second' => array('unit'=>'s', 'min'=>0   , 'max'=>61  , 'padding'=>'S@'),
		'_day'    => array('unit'=>'j', 'min'=>0   , 'max'=>32  , 'padding'=>'D&nbsp;'),
		'_month'  => array('unit'=>'n', 'min'=>0   , 'max'=>13  , 'padding'=>'M&nbsp;'),
		'_year'   => array('unit'=>'Y', 'min'=>2010, 'max'=>2030, 'padding'=>'Y')
	);

	echo '<div id="'.$fieldname.'" style="display: '.$display.'">'."\n";
	echo '<span dir="ltr">'."\n";

	foreach($time_select as $key => $value) {
		$time_value = (int) gmdate($value['unit'], $poll_dude_time);
		$time_stamp = $fieldname.$key;
		echo "<select name=\"$time_stamp\" size=\"1\">"."\n";
		for($i = $value['min']; $i < $value['max']; $i++) {
			if($time_value === $i) {
				echo "<option value=\"$i\" selected=\"selected\">$i</option>\n";
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}
		echo '</select>&nbsp;'.$value['padding']."\n";		
	}

	echo '</span>'."\n";
	echo '</div>'."\n";
}

function poll_dude_poll_config($mode) {
	global $wpdb;
	$text = '';
	
    // Poll Question
	$pollq_question = isset( $_POST['pollq_question'] ) ? wp_kses_post( trim( $_POST['pollq_question'] ) ) : '';

	if ( ! empty( $pollq_question ) ) {
		// Poll ID
		$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
		// Poll Total Votes
		$pollq_totalvotes = isset( $_POST['pollq_totalvotes'] ) ? (int) sanitize_key($_POST['pollq_totalvotes']) : 0;
		// Poll Total Voters
		$pollq_totalvoters = isset( $_POST['pollq_totalvotes'] ) ? (int) sanitize_key($_POST['pollq_totalvoters']) : 0;
		// Poll Active
		$pollq_active = isset( $_POST['pollq_active'] ) ? (int) sanitize_key($_POST['pollq_active']) : '';
		// Poll Start Date
		$pollq_timestamp = isset( $_POST['poll_timestamp_old'] ) ? $_POST['poll_timestamp_old'] : current_time( 'timestamp' );
		$edit_polltimestamp = isset( $_POST['edit_polltimestamp'] ) && (int) sanitize_key( $_POST['edit_polltimestamp'] ) === 1 ? 1 : 0;
		$pollq_expiry_no = isset( $_POST['pollq_expiry_no'] ) ? (int) sanitize_key( $_POST['pollq_expiry_no'] ) : 0;
		$pollq_multiple_yes = isset( $_POST['pollq_multiple_yes'] ) ? (int) sanitize_key( $_POST['pollq_multiple_yes'] ) : 0;

		if(('edit' !== $mode)||($edit_polltimestamp === 1)) {
			$pollq_timestamp = poll_dude_time_make('pollq_timestamp');
			if ( $pollq_timestamp > current_time( 'timestamp' ) ) {
				$pollq_active = -1;
			}else {
				$pollq_active = 1;
			}
		}

		// Poll End Date
		if ( $pollq_expiry_no === 1 ) {
			$pollq_expiry = 0;
		} else {			
			$pollq_expiry = poll_dude_time_make('pollq_expiry');
			if($pollq_expiry <= current_time('timestamp')) {
				$pollq_active = 0;
			}
			if($edit_polltimestamp === 1) {
				if($pollq_expiry < $pollq_timestamp) {
					$pollq_active = 0;
				}
			}
		}
		
		// Mutilple Poll
		$pollq_multiple = 0;
		if ( $pollq_multiple_yes === 1 ) {
			$pollq_multiple = isset( $_POST['pollq_multiple'] ) ? (int) sanitize_key( $_POST['pollq_multiple'] ) : 0;
		} else {
			$pollq_multiple = 0;
		}

		$pollq_data = array(
						'pollq_question'        => $pollq_question,
						'pollq_timestamp'       => $pollq_timestamp,
						'pollq_totalvotes'      => $pollq_totalvotes,
						'pollq_active'          => $pollq_active,
						'pollq_expiry'          => $pollq_expiry,
						'pollq_multiple'        => $pollq_multiple,
						'pollq_totalvoters'     => $pollq_totalvoters
						);
		$pollq_format = array(
							'%s',
							'%s',
							'%d',
							'%d',
							'%s',
							'%d',
							'%d'
						); 

		if('edit' !== $mode) {
			// Insert Poll		
			$add_poll_question = $wpdb->insert(
				$wpdb->pollsq,
				$pollq_data,
				$pollq_format
			);
			if ( ! $add_poll_question ) {
				$text .= '<p style="color: red;">' . sprintf(__('Error In Adding Poll \'%s\'.', 'poll-dude-domain'), $pollq_question) . '</p>';
			}
			$polla_answers_new = isset( $_POST['polla_answers'] ) ? $_POST['polla_answers'] : array();

			$polla_qid = (int) $wpdb->insert_id;
			if(empty($polla_answers_new)) {
				$text .= '<p style="color: red;">' . __( 'Poll\'s Answer is empty.', 'poll-dude-domain' ) . '</p>';
			}
		}else{
			// Update Poll's Question
			$edit_poll_question = $wpdb->update(
				$wpdb->pollsq,
				$pollq_data,
				array('pollq_id' => $pollq_id),
				$pollq_format,
				array('%d')
			);
			if( ! $edit_poll_question ) {
				$text = '<p style="color: blue">'.sprintf(__('No Changes Had Been Made To Poll\'s Question \'%s\'.', 'poll-dude-domain'), removeslashes($pollq_question)).'</p>';
			}
			// Update Polls' Answers
			$polla_aids = array();
			$get_polla_aids = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid FROM $wpdb->pollsa WHERE polla_qid = %d ORDER BY polla_aid ASC", $pollq_id ) );
			if($get_polla_aids) {
				foreach($get_polla_aids as $get_polla_aid) {
						$polla_aids[] = (int) $get_polla_aid->polla_aid;
				}
				foreach($polla_aids as $polla_aid) {
					$polla_answers = wp_kses_post( trim( $_POST['polla_aid-'.$polla_aid] ) );
					$polla_votes = (int) sanitize_key($_POST['polla_votes-'.$polla_aid]);
					$edit_poll_answer = $wpdb->update(
						$wpdb->pollsa,
						array(
							'polla_answers' => $polla_answers,
							'polla_votes'   => $polla_votes
						),
						array(
							'polla_qid' => $pollq_id,
							'polla_aid' => $polla_aid
						),
						array(
							'%s',
							'%d'
						),
						array(
							'%d',
							'%d'
						)
					);
					if( ! $edit_poll_answer ) {
						$text .= '<p style="color: blue">'.sprintf(__('No Changes Had Been Made To Poll\'s Answer \'%s\'.', 'poll-dude-domain'), $polla_answers ).'</p>';
					} else {
						$text .= '<p style="color: green">'.sprintf(__('Poll\'s Answer \'%s\' Edited Successfully.', 'poll-dude-domain'), $polla_answers ).'</p>';
					}
				}
			} else {
				$text .= '<p style="color: red">'.sprintf(__('Invalid Poll \'%s\'.', 'poll-dude-domain'), removeslashes($pollq_question)).'</p>';
			}
			$polla_answers_new = isset($_POST['polla_answers_new']) ? $_POST['polla_answers_new'] : array();
			$polla_qid = $pollq_id;
		}
		
		
		// Add Poll Answers (If Needed)
		if(!empty($polla_answers_new)) {
			$i = 0;
			$polla_answers_new_votes = isset($_POST['polla_answers_new_votes'])? $_POST['polla_answers_new_votes'] : array();
			
			foreach($polla_answers_new as $polla_answer_new) {
				$polla_answer_new = wp_kses_post( trim( $polla_answer_new ) );
				$polla_answer_new_vote = ('edit' !== $mode)? 0 : (int) sanitize_key( $polla_answers_new_votes[$i] );
					
				$add_poll_answers = $wpdb->insert(
					$wpdb->pollsa,
					array(
						'polla_qid'      => $polla_qid,
						'polla_answers'  => $polla_answer_new,
						'polla_votes'    => $polla_answer_new_vote
					),
					array(
						'%d',
						'%s',
						'%d'
					)
				);
				
				if( ! $add_poll_answers ) {
					$text .= '<p style="color: red;">'.sprintf(__('Error In Adding Poll\'s Answer \'%s\'.', 'poll-dude-domain'), $polla_answer_new).'</p>';
				} else {
					if ('edit' === $mode) {
						$text .= '<p style="color: green;">'.sprintf(__('Poll\'s Answer \'%s\' Added Successfully.', 'poll-dude-domain'), $polla_answer_new).'</p>';
					}
				}
				
				$i++;
			}
		}

		
		// Update Lastest Poll ID To Poll Options
		$latest_pollid = poll_dude_latest_id();
		$update_latestpoll = update_option('poll_latestpoll', $latest_pollid);
		
		if ('edit' !== $mode) {
			global $poll_dude_base;
			$base_name = $poll_dude_base;
			$base_page = 'admin.php?page='.$base_name;
			// If poll starts in the future use the correct poll ID
			$latest_pollid = ( $latest_pollid < $polla_qid ) ? $polla_qid : $latest_pollid;
			if ( empty( $text ) ) {
				$text = '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) added successfully. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'poll-dude-domain' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />', $base_page ) . '</p>';
			} else {
				if ( $add_poll_question ) {
					$text .= '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) (Shortcode: %s) added successfully, but there are some errors with the Poll\'s Answers. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'poll-dude-domain' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />', '<input type="text" value=\'[poll id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />', $base_page ) .'</p>';
				}
			}
			do_action( 'wp_polls_add_poll', $latest_pollid );
		} else {
			if(empty($text)) {
				$text = '<p style="color: green">'.sprintf(__('Poll \'%s\' Edited Successfully.', 'poll-dude-domain'), removeslashes($pollq_question)).'</p>';
			}
			do_action( 'wp_polls_update_poll', $pollq_id );
		}
		
		//cron_polls_place();
		
	}else{
		$text .= '<p style="color: red;">' . __( 'Poll Question is empty.', 'poll-dude-domain' ) . '</p>';
	}

    return $text;
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
	$menu_slug   = plugin_dir_path(__FILE__) . '/includes/page-poll-dude-add-form.php';
	//$menu_slug   = plugin_dir_path(__FILE__) . '/includes/page-poll-dude-control-panel.php';

	
	add_submenu_page( 
		$parent_slug, 
		$page_title, 
		$menu_title, 
		$capability, 
		$menu_slug 
	);

	$parent_slug = 'poll_dude_manager';
	$page_title  = __( 'Control Panel', 'poll-dude-domain' );
	$menu_title  = __( 'Control Panel', 'poll-dude-domain' );
	$capability  = 'manage_options';
	$menu_slug   = plugin_dir_path(__FILE__) . '/includes/page-poll-dude-control-panel.php';

	
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

### Funcion: Get Latest Poll ID
/*
function polls_latest_id() {
	global $wpdb;
	$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY pollq_timestamp DESC LIMIT 1");
	return (int) $poll_id;
}
*/

function poll_dude_latest_id() {
	global $wpdb;
	$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY pollq_timestamp DESC LIMIT 1");
	return (int) $poll_id;
}


### Function: Manage Polls
add_action('wp_ajax_poll-dude-control', 'poll_dude_control_panel');
function poll_dude_control_panel() {
	global $wpdb;
	
	### Form Processing
	if( isset( $_POST['action'] ) && sanitize_key( $_POST['action'] ) === 'poll-dude-control' ) {
	//if( isset( $_POST['action'] ) && sanitize_key( $_POST['action'] ) === 'poll-dude' ) {
		if( ! empty( $_POST['do'] ) ) {
			// Set Header
			header('Content-Type: text/html; charset='.get_option('blog_charset').'');

			// Decide What To Do
			switch($_POST['do']) {
				// Delete Polls Logs
				case __('Delete All Logs', 'poll-dude-domain'):
					check_ajax_referer('wp-polls_delete-polls-logs');
					if( sanitize_key( trim( $_POST['delete_logs_yes'] ) ) === 'yes') {
						$delete_logs = $wpdb->query("DELETE FROM $wpdb->pollsip");
						if($delete_logs) {
							echo '<p style="color: green;">'.__('All Polls Logs Have Been Deleted.', 'poll-dude-domain').'</p>';
						} else {
							echo '<p style="color: red;">'.__('An Error Has Occurred While Deleting All Polls Logs.', 'poll-dude-domain').'</p>';
						}
					}
					break;
				// Delete Poll Logs For Individual Poll
				case __('Delete Logs For This Poll Only', 'poll-dude-domain'):
					check_ajax_referer('wp-polls_delete-poll-logs');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					if( sanitize_key( trim( $_POST['delete_logs_yes'] ) ) === 'yes') {
						$delete_logs = $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
						if( $delete_logs ) {
							echo '<p style="color: green;">'.sprintf(__('All Logs For \'%s\' Has Been Deleted.', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('An Error Has Occurred While Deleting All Logs For \'%s\'', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
						}
					}
					break;
				// Delete Poll's Answer
				case __('Delete Poll Answer', 'poll-dude-domain'):
					check_ajax_referer('wp-polls_delete-poll-answer');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$polla_aid = (int) sanitize_key( $_POST['polla_aid'] );
					$poll_answers = $wpdb->get_row( $wpdb->prepare( "SELECT polla_votes, polla_answers FROM $wpdb->pollsa WHERE polla_aid = %d AND polla_qid = %d", $polla_aid, $pollq_id ) );
					$polla_votes = (int) $poll_answers->polla_votes;
					$polla_answers = wp_kses_post( removeslashes( trim( $poll_answers->polla_answers ) ) );
					$delete_polla_answers = $wpdb->delete( $wpdb->pollsa, array( 'polla_aid' => $polla_aid, 'polla_qid' => $pollq_id ), array( '%d', '%d' ) );
					$delete_pollip = $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id, 'pollip_aid' => $polla_aid ), array( '%d', '%d' ) );
					$update_pollq_totalvotes = $wpdb->query( "UPDATE $wpdb->pollsq SET pollq_totalvotes = (pollq_totalvotes - $polla_votes) WHERE pollq_id = $pollq_id" );
					if($delete_polla_answers) {
						//echo '<p style="color: green;">'.sprintf(__('Poll Answer \'%s\' Deleted Successfully.', 'poll-dude-domain'), $polla_answers).'</p>';
						echo '<p style="color: green;">'.sprintf(__('Poll Answer Deleted Successfully.', 'poll-dude-domain')).'</p>';
					} else {
						echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll Answer \'%s\'.', 'poll-dude-domain'), $polla_answers).'</p>';
					}
					break;
				// Open Poll
				case __('Open Poll', 'poll-dude-domain'):
					check_ajax_referer('wp-polls_open-poll');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					$open_poll = $wpdb->update(
						$wpdb->pollsq,
						array(
							'pollq_active' => 1
						),
						array(
							'pollq_id' => $pollq_id
						),
						array(
							'%d'
						),
						array(
							'%d'
						)
					);
					if( $open_poll ) {
						echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Opened', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					} else {
						echo '<p style="color: red;">'.sprintf(__('Error Opening Poll \'%s\'', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					break;
				// Close Poll
				case __('Close Poll', 'poll-dude-domain'):
					check_ajax_referer('wp-polls_close-poll');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					$close_poll = $wpdb->update(
						$wpdb->pollsq,
						array(
							'pollq_active' => 0
						),
						array(
							'pollq_id' => $pollq_id
						),
						array(
							'%d'
						),
						array(
							'%d'
						)
					);
					if( $close_poll ) {
						echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Closed', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					} else {
						echo '<p style="color: red;">'.sprintf(__('Error Closing Poll \'%s\'', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					break;
				// Delete Poll
				case __('Delete Poll', 'poll-dude-domain'):
					check_ajax_referer('wp-polls_delete-poll');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					$delete_poll_question = $wpdb->delete( $wpdb->pollsq, array( 'pollq_id' => $pollq_id ), array( '%d' ) );
					$delete_poll_answers =  $wpdb->delete( $wpdb->pollsa, array( 'polla_qid' => $pollq_id ), array( '%d' ) );
					$delete_poll_ip =	   $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
					$poll_option_lastestpoll = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'poll_latestpoll'");
					if(!$delete_poll_question) {
						echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll \'%s\' Question', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					if(empty($text)) {
						echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Deleted Successfully', 'poll-dude-domain'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					
					// Update Lastest Poll ID To Poll Options
					//update_option( 'poll_latestpoll', polls_latest_id() );
					update_option( 'poll_latestpoll', poll_dude_latest_id() );
					do_action( 'wp_polls_delete_poll', $pollq_id );
					//die('Debug');
					
					break;
			}
			exit();
		}
	}
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
function activate_poll_dude($network_wide) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-activator.php';
	Poll_Dude_Activator::activate($network_wide);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-poll-dude-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

//register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

### Function: Activate Plugin
register_activation_hook( __FILE__, 'activate_poll_dude' );

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
