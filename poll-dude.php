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


/*
function poll_dude_poll_config($mode, $base_name) {
	global $wpdb, $poll_dude;
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
			$pollq_timestamp = $poll_dude->utility->time_make('pollq_timestamp');
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
			$pollq_expiry = $poll_dude->utility->time_make('pollq_expiry');
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
				$text = '<p style="color: blue">'.sprintf(__('No Changes Had Been Made To Poll\'s Question \'%s\'.', 'poll-dude-domain'), $poll_dude->utility->removeslashes($pollq_question)).'</p>';
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
				$text .= '<p style="color: red">'.sprintf(__('Invalid Poll \'%s\'.', 'poll-dude-domain'), $poll_dude->utility->removeslashes($pollq_question)).'</p>';
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
		//$latest_pollid = poll_dude_latest_id();
		$latest_pollid = $poll_dude->utility->latest_poll();
		$update_latestpoll = update_option('poll_latestpoll', $latest_pollid);
		
		if ('edit' !== $mode) {
			//global $poll_dude;
			//$base_name = $poll_dude->get_plugin_base();
			$base_page = 'admin.php?page='.$base_name;
			// If poll starts in the future use the correct poll ID
			$latest_pollid = ( $latest_pollid < $polla_qid ) ? $polla_qid : $latest_pollid;
			if ( empty( $text ) ) {
				$text = '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) added successfully. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'poll-dude-domain' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll_dude id="' . $latest_pollid . '"]\' readonly="readonly" size="20" />', $base_page ) . '</p>';
			} else {
				if ( $add_poll_question ) {
					$text .= '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) (Shortcode: %s) added successfully, but there are some errors with the Poll\'s Answers. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'poll-dude-domain' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll-dude id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />', '<input type="text" value=\'[poll id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />', $base_page ) .'</p>';
				}
			}
			do_action( 'wp_polls_add_poll', $latest_pollid );
		} else {
			if(empty($text)) {
				$text = '<p style="color: green">'.sprintf(__('Poll \'%s\' Edited Successfully.', 'poll-dude-domain'), $poll_dude->utility->removeslashes($pollq_question)).'</p>';
			}
			do_action( 'wp_polls_update_poll', $pollq_id );
		}
		
		//cron_polls_place();
		
	}else{
		$text .= '<p style="color: red;">' . __( 'Poll Question is empty.', 'poll-dude-domain' ) . '</p>';
	}

    return $text;
}
*/

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


// Check if admin and include admin scripts



function vote_poll_process($poll_id, $poll_aid_array = [])
{
	global $wpdb, $user_identity, $user_ID, $poll_dude;

	do_action('wp_polls_vote_poll');

	$polla_aids = $wpdb->get_col( $wpdb->prepare( "SELECT polla_aid FROM $wpdb->pollsa WHERE polla_qid = %d", $poll_id ) );
	$is_real = count( array_intersect( $poll_aid_array, $polla_aids ) ) === count( $poll_aid_array );

	if( !$is_real ) {
		throw new InvalidArgumentException(sprintf(__('Invalid Answer to Poll ID #%s', 'wp-polls'), $poll_id));
	}

	if (!$poll_dude->utility->vote_allow()) {
		throw new InvalidArgumentException(sprintf(__('User is not allowed to vote for Poll ID #%s', 'wp-polls'), $poll_id));
	}

	if (empty($poll_aid_array)) {
		throw new InvalidArgumentException(sprintf(__('No anwsers given for Poll ID #%s', 'wp-polls'), $poll_id));
	}

	if($poll_id === 0) {
		throw new InvalidArgumentException(sprintf(__('Invalid Poll ID. Poll ID #%s', 'wp-polls'), $poll_id));
	}

	$is_poll_open = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->pollsq WHERE pollq_id = %d AND pollq_active = 1", $poll_id ) );

	if ($is_poll_open === 0) {
		throw new InvalidArgumentException(sprintf(__( 'Poll ID #%s is closed', 'wp-polls' ), $poll_id ));
	}

	//$check_voted = check_voted($poll_id);
	$is_voted = $poll_dude->utility->is_voted($poll_id);
	if ( !empty( $is_voted ) ) {
		throw new InvalidArgumentException(sprintf(__('You Had Already Voted For This Poll. Poll ID #%s', 'wp-polls'), $poll_id));
	}

	if (!empty($user_identity)) {
		$pollip_user = $user_identity;
	} elseif ( ! empty( $_COOKIE['comment_author_' . COOKIEHASH] ) ) {
		$pollip_user = $_COOKIE['comment_author_' . COOKIEHASH];
	} else {
		$pollip_user = __('Guest', 'wp-polls');
	}

	$pollip_user = sanitize_text_field( $pollip_user );
	$pollip_userid = $user_ID;
	//$pollip_ip = poll_get_ipaddress();
	//$pollip_host = poll_get_hostname();
	$pollip_ip = $poll_dude->utility->get_ipaddr();
	$pollip_host = $poll_dude->utility->get_hostname();
	$pollip_timestamp = current_time('timestamp');
	$poll_logging_method = (int) get_option('poll_logging_method');

	// Only Create Cookie If User Choose Logging Method 1 Or 3
	if ( $poll_logging_method === 1 || $poll_logging_method === 3 ) {
		$cookie_expiry = (int) get_option('poll_cookielog_expiry');
		if ($cookie_expiry === 0) {
			$cookie_expiry = YEAR_IN_SECONDS;
		}
		setcookie( 'voted_' . $poll_id, implode(',', $poll_aid_array ), $pollip_timestamp + $cookie_expiry, apply_filters( 'wp_polls_cookiepath', SITECOOKIEPATH ) );
	}

	$i = 0;
	foreach ($poll_aid_array as $polla_aid) {
		$update_polla_votes = $wpdb->query( "UPDATE $wpdb->pollsa SET polla_votes = (polla_votes + 1) WHERE polla_qid = $poll_id AND polla_aid = $polla_aid" );
		if (!$update_polla_votes) {
			unset($poll_aid_array[$i]);
		}
		$i++;
	}

	$vote_q = $wpdb->query("UPDATE $wpdb->pollsq SET pollq_totalvotes = (pollq_totalvotes+" . count( $poll_aid_array ) . "), pollq_totalvoters = (pollq_totalvoters + 1) WHERE pollq_id = $poll_id AND pollq_active = 1");
	if (!$vote_q) {
		throw new InvalidArgumentException(sprintf(__('Unable To Update Poll Total Votes And Poll Total Voters. Poll ID #%s', 'wp-polls'), $poll_id));
	}

	foreach ($poll_aid_array as $polla_aid) {
		// Log Ratings In DB If User Choose Logging Method 2, 3 or 4
		if ( $poll_logging_method > 1 ){
			$wpdb->insert(
				$wpdb->pollsip,
				array(
					'pollip_qid'       => $poll_id,
					'pollip_aid'       => $polla_aid,
					'pollip_ip'        => $pollip_ip,
					'pollip_host'      => $pollip_host,
					'pollip_timestamp' => $pollip_timestamp,
					'pollip_user'      => $pollip_user,
					'pollip_userid'    => $pollip_userid
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d'
				)
			);
		}
	}
	do_action( 'wp_polls_vote_poll_success' );

	return $poll_dude->shortcode->display_pollresult($poll_id, $poll_aid_array, false);
}


### Function: Vote Poll
add_action('wp_ajax_poll-dude', 'poll_dude_vote');
add_action('wp_ajax_nopriv_poll-dude', 'poll_dude_vote');
function poll_dude_vote() {
	global $wpdb, $user_identity, $user_ID;
	global $poll_dude;


	if( isset( $_REQUEST['action'] ) && sanitize_key( $_REQUEST['action'] ) === 'poll-dude') {
		// Load Headers
		//polldude_textdomain();
		header('Content-Type: text/html; charset='.get_option('blog_charset').'');

		// Get Poll ID
		$poll_id = (isset($_REQUEST['poll_id']) ? (int) sanitize_key( $_REQUEST['poll_id'] ) : 0);

		// Ensure Poll ID Is Valid
		if($poll_id === 0) {
			_e('Invalid Poll ID', 'wp-polls');
			exit();
		}

		// Verify Referer
		if( ! check_ajax_referer( 'poll_'.$poll_id.'-nonce', 'poll_'.$poll_id.'_nonce', false ) ) {
			_e('Failed To Verify Referrer', 'wp-polls');
			exit();
		}

		// Which View
		switch( sanitize_key( $_REQUEST['view'] ) ) {
			// Poll Vote
			case 'process':
				
				try {
					$poll_aid_array = array_unique( array_map('intval', array_map('sanitize_key', explode( ',', $_POST["poll_$poll_id"] ) ) ) );
					echo vote_poll_process($poll_id, $poll_aid_array);
				} catch (Exception $e) {
					echo $e->getMessage();
				}
				break;
			// Poll Result
			case 'result':
				
				echo $poll_dude->shortcode->display_pollresult($poll_id, 0, false);
				break;
			// Poll Booth Aka Poll Voting Form
			case 'booth':
				echo $poll_dude->shortcode->display_pollvote($poll_id, false);
				break;
		} // End switch($_REQUEST['view'])
	} // End if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'polls')
	exit();
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






