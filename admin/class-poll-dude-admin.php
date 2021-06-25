<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/liaisontw/poll-dude
 * @since      1.0.0
 * @package    poll-dude
 * @subpackage poll-dude/includes
 * @author     Liaison Chang
 */
class Poll_Dude_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->cron_activate();
		add_action( 'admin_menu',                	array($this, 'admin_menu') );
		add_action( 'admin_enqueue_scripts',     	array($this, 'admin_scripts') );
		add_action( 'wp_ajax_poll-dude-control', 	array($this, 'control_panel') );
		add_action(	'poll_dude_cron', 				array($this, 'cron_update') );
	}

	public function admin_scripts($hook_suffix){
		$admin_pages = array($this->plugin_name.'/poll-dude.php', $this->plugin_name.'/view/page-poll-dude-add-form.php', $this->plugin_name.'/view/page-poll-dude-control-panel.php', $this->plugin_name.'/view/page-poll-dude-options.php');
		if(in_array($hook_suffix, $admin_pages, true)) {			
			$this->enqueue_scripts();
			$this->enqueue_styles();
			wp_localize_script('poll-dude', 'pdAdminL10n', array(
					'default_color' => get_option('pd_default_color'),
					'admin_ajax_url' => admin_url('admin-ajax.php'),
					'text_direction' => is_rtl() ? 'right' : 'left',
					'text_delete_poll' => __('Delete Poll', 'poll-dude'),
					'text_no_poll_logs' => __('No poll logs available.', 'poll-dude'),
					'text_delete_all_logs' => __('Delete All Logs', 'poll-dude'),
					'text_checkbox_delete_all_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs.', 'poll-dude'),
					'text_delete_poll_logs' => __('Delete Logs For This Poll Only', 'poll-dude'),
					'text_checkbox_delete_poll_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs for this poll ONLY.', 'poll-dude'),
					'text_delete_poll_ans' => __('Delete Poll Answer', 'poll-dude'),
					'text_open_poll' => __('Open Poll', 'poll-dude'),
					'text_close_poll' => __('Close Poll', 'poll-dude'),
					'text_answer' => __('Ans', 'poll-dude'),
					'text_remove_poll_answer' => __('Remove', 'poll-dude'),
					'text_delete_poll_answer' => __('Delete', 'poll-dude')
			));
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/poll-dude-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/poll-dude-admin.js', array( 'jquery' ), $this->version, true );
	}

	public function admin_menu() {
		add_menu_page(
			/* $page_title */__( 'Poll Dude', 'poll-dude' ),
			/* $menu_title */__( 'Poll Dude', 'poll-dude' ),
			/* $capability */'manage_options',
			/* $menu_slug  */plugin_dir_path( dirname( __FILE__ ) ) . '/view/page-poll-dude-options.php',
			/* $function   */'',
			'dashicons-yes'
		);


		add_submenu_page( 
			/* $parent_slug  */plugin_dir_path( dirname( __FILE__ ) ) . '/view/page-poll-dude-options.php',
			/* $page_title  */ __( 'New Poll', 'poll-dude' ), 
			/* $menu_title  */ __( 'New Poll', 'poll-dude' ), 
			/* $capability  */ 'manage_options', 
			/* $menu_slug   */ plugin_dir_path( dirname( __FILE__ ) ) . '/view/page-poll-dude-add-form.php'
		);

		add_submenu_page( 
			/* $parent_slug  */plugin_dir_path( dirname( __FILE__ ) ) . '/view/page-poll-dude-options.php',
			/* $page_title  */ __( 'Control Panel', 'poll-dude' ), 
			/* $menu_title  */ __( 'Control Panel', 'poll-dude' ), 
			/* $capability  */ 'manage_options', 
			/* $menu_slug   */ plugin_dir_path( dirname( __FILE__ ) ) . '/view/page-poll-dude-control-panel.php'
		);
	}

	

	public function control_panel() {
		global $wpdb, $poll_dude;
		
		### Form Processing
		if( isset( $_POST['action'] ) && sanitize_key( $_POST['action'] ) === 'poll-dude-control' ) {
			if( ! empty( $_POST['do'] ) ) {
				// Set Header
				header('Content-Type: text/html; charset='.get_option('blog_charset').'');

				// Decide What To Do
				switch($_POST['do']) {
					// Delete Poll's Answer
					case __('Delete Poll Answer', 'poll-dude'):
						check_ajax_referer('polldude_delete-poll-answer');
						$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
						$polla_aid = (int) sanitize_key( $_POST['polla_aid'] );
						$poll_answers = $wpdb->get_row( $wpdb->prepare( "SELECT polla_votes, polla_answers FROM $wpdb->polldude_a WHERE polla_aid = %d AND polla_qid = %d", $polla_aid, $pollq_id ) );
						$polla_votes = (int) $poll_answers->polla_votes;
						$polla_answers = wp_kses_post( $poll_dude->utility->removeslashes( trim( $poll_answers->polla_answers ) ) );
						$delete_polla_answers = $wpdb->delete( $wpdb->polldude_a, array( 'polla_aid' => $polla_aid, 'polla_qid' => $pollq_id ), array( '%d', '%d' ) );
						$delete_pollip = $wpdb->delete( $wpdb->polldude_ip, array( 'pollip_qid' => $pollq_id, 'pollip_aid' => $polla_aid ), array( '%d', '%d' ) );
						$update_pollq_totalvotes = $wpdb->query( "UPDATE $wpdb->polldude_q SET pollq_totalvotes = (pollq_totalvotes - $polla_votes) WHERE pollq_id = $pollq_id" );
						if($delete_polla_answers) {
							echo '<p style="color: green;">'.sprintf(__('Poll Answer Deleted Successfully.', 'poll-dude')).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll Answer \'%s\'.', 'poll-dude'), $polla_answers).'</p>';
						}
						break;
					// Open Poll
					case __('Open Poll', 'poll-dude'):
						check_ajax_referer('polldude_open-poll');
						$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
						$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->polldude_q WHERE pollq_id = %d", $pollq_id ) );
						$open_poll = $wpdb->update(
							$wpdb->polldude_q,
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
							echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Opened', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('Error Opening Poll \'%s\'', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
						break;
					// Close Poll
					case __('Close Poll', 'poll-dude-'):
						check_ajax_referer('polldude_close-poll');
						$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
						$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->polldude_q WHERE pollq_id = %d", $pollq_id ) );
						$close_poll = $wpdb->update(
							$wpdb->polldude_q,
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
							echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Closed', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('Error Closing Poll \'%s\'', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
						break;
					// Delete Poll
					case __('Delete Poll', 'poll-dude'):
						check_ajax_referer('polldude_delete-poll');
						$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
						$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->polldude_q WHERE pollq_id = %d", $pollq_id ) );
						$delete_poll_question = $wpdb->delete( $wpdb->polldude_q, array( 'pollq_id' => $pollq_id ), array( '%d' ) );
						$delete_poll_answers =  $wpdb->delete( $wpdb->polldude_a, array( 'polla_qid' => $pollq_id ), array( '%d' ) );
						$delete_poll_ip =	   $wpdb->delete( $wpdb->polldude_ip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
						$poll_option_lastestpoll = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'pd_latestpoll'");
						if(!$delete_poll_question) {
							echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll \'%s\' Question', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
						if(empty($text)) {
							echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Deleted Successfully', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
								
						update_option( 'pd_latestpoll', $poll_dude->utility->latest_poll() );
						
						break;
				}
				exit();
			}
		}
	}

	public function poll_config($mode, $base_name) {
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
			$polldude_recaptcha = isset( $_POST['polldude_recaptcha'] ) ? (int) sanitize_key( $_POST['polldude_recaptcha'] ) : 0;

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
							'pollq_totalvoters'     => $pollq_totalvoters,
							'pollq_recaptcha'       => $polldude_recaptcha
							);
			$pollq_format = array(
								'%s',
								'%s',
								'%d',
								'%d',
								'%s',
								'%d',
								'%d',
								'%d'
							); 

			if('edit' !== $mode) {
				// Insert Poll		
				$add_poll_question = $wpdb->insert(
					$wpdb->polldude_q,
					$pollq_data,
					$pollq_format
				);
				if ( ! $add_poll_question ) {
					$text .= '<p style="color: red;">' . sprintf(__('Error In Adding Poll \'%s\'.', 'poll-dude'), $pollq_question) . '</p>';
				}
				$polla_answers_new = isset( $_POST['polla_answers'] ) ? $_POST['polla_answers'] : array();
				
				$polla_qid = (int) $wpdb->insert_id;
				if(empty($polla_answers_new)) {
					$text .= '<p style="color: red;">' . __( 'Poll\'s Answer is empty.', 'poll-dude' ) . '</p>';
				}
			}else{
				// Update Poll's Question
				$edit_poll_question = $wpdb->update(
					$wpdb->polldude_q,
					$pollq_data,
					array('pollq_id' => $pollq_id),
					$pollq_format,
					array('%d')
				);
				if( ! $edit_poll_question ) {
					$text = '<p style="color: blue">'.sprintf(__('No Changes Had Been Made To Poll\'s Question \'%s\'.', 'poll-dude'), $poll_dude->utility->removeslashes($pollq_question)).'</p>';
				}
				// Update Polls' Answers
				$polla_aids = array();
				$get_polla_aids = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid FROM $wpdb->polldude_a WHERE polla_qid = %d ORDER BY polla_aid ASC", $pollq_id ) );
				if($get_polla_aids) {
					foreach($get_polla_aids as $get_polla_aid) {
							$polla_aids[] = (int) $get_polla_aid->polla_aid;
					}
					$i = 0;
					foreach($polla_aids as $polla_aid) {
						$polla_answers = wp_kses_post( trim( $_POST['polla_aid-'.$polla_aid] ) );
						$polla_votes = (int) sanitize_key($_POST['polla_votes-'.$polla_aid]);
						$polla_color = sanitize_hex_color($_POST['color_picker'][$i]);
						$text .= '<p style="color: green;">'.sprintf(__('Poll\'s Color \'%s\' Picked Successfully.', 'poll-dude'), $polla_color).'</p>';

						$edit_poll_answer = $wpdb->update(
							$wpdb->polldude_a,
							array(
								'polla_answers' => $polla_answers,
								'polla_votes'   => $polla_votes,
								'polla_colors'  => $polla_color
							),
							array(
								'polla_qid' => $pollq_id,
								'polla_aid' => $polla_aid
							),
							array(
								'%s',
								'%d',
								'%s'
							),
							array(
								'%d',
								'%d'
							)
						);
						if( ! $edit_poll_answer ) {
							$text .= '<p style="color: blue">'.sprintf(__('No Changes Had Been Made To Poll\'s Answer \'%s\'.', 'poll-dude'), $polla_answers ).'</p>';
						} else {
							$text .= '<p style="color: green">'.sprintf(__('Poll\'s Answer \'%s\' Edited Successfully.', 'poll-dude'), $polla_answers ).'</p>';
						}
						$i++;
					}
				} else {
					$text .= '<p style="color: red">'.sprintf(__('Invalid Poll \'%s\'.', 'poll-dude'), $poll_dude->utility->removeslashes($pollq_question)).'</p>';
				}
				$polla_answers_new = isset($_POST['polla_answers_new']) ? wp_kses_post( trim($_POST['polla_answers_new'])) : array();
				$polla_qid = $pollq_id;
			}
			
			
			// Add Poll Answers (If Needed)
			
			if(!empty($polla_answers_new)) {
				$i = 0;
				$polla_answers_new_votes = isset($_POST['polla_answers_new_votes'])? (int) sanitize_key($_POST['polla_answers_new_votes']) : array();
				
				foreach($polla_answers_new as $polla_answer_new) {
					$polla_answer_new = wp_kses_post( trim( $polla_answer_new ) );
					if ( ! empty( $polla_answer_new ) ) {
						$polla_answer_new_vote = ('edit' !== $mode)? 0 : (int) sanitize_key( $polla_answers_new_votes[$i] );
						$polla_color = sanitize_hex_color($_POST['color_picker'][$i]);
							
						$add_poll_answers = $wpdb->insert(
							$wpdb->polldude_a,
							array(
								'polla_qid'      => $polla_qid,
								'polla_answers'  => $polla_answer_new,
								'polla_votes'    => $polla_answer_new_vote,
								'polla_colors'   => $polla_color
							),
							array(
								'%d',
								'%s',
								'%d',
								'%s'
							)
						);
						
						if( ! $add_poll_answers ) {
							$text .= '<p style="color: red;">'.sprintf(__('Error In Adding Poll\'s Answer \'%s\'.', 'poll-dude'), $polla_answer_new).'</p>';
						} else {
							if ('edit' === $mode) {
								$text .= '<p style="color: green;">'.sprintf(__('Poll\'s Answer \'%s\' Added Successfully.', 'poll-dude'), $polla_answer_new).'</p>';
								$text .= '<p style="color: green;">'.sprintf(__('Poll\'s Color \'%s\' Picked Successfully.', 'poll-dude'), $polla_color).'</p>';
							}
						}
						
						$i++;
					}else {
						$text .= '<p style="color: red;">' . __( 'Poll\'s Answer is empty.', 'poll-dude' ) . '</p>';
					}
				}
			}
			

			
			// Update Lastest Poll ID To Poll Options
			$latest_pollid = $poll_dude->utility->latest_poll();
			$update_latestpoll = update_option('pd_latestpoll', $latest_pollid);
		
			
			if ('edit' !== $mode) {
				$base_page = 'admin.php?page='.$base_name;
				// If poll starts in the future use the correct poll ID
				$latest_pollid = ( $latest_pollid < $polla_qid ) ? $polla_qid : $latest_pollid;
				
				if ( empty( $text ) ) {
					$text = '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) added successfully. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'poll-dude' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll_dude id="' . $latest_pollid . '"]\' readonly="readonly" size="20" />', $base_page ) . '</p>';
				} 
			} else {
				if(empty($text)) {
					$text = '<p style="color: green">'.sprintf(__('Poll \'%s\' Edited Successfully.', 'poll-dude'), $poll_dude->utility->removeslashes($pollq_question)).'</p>';
				}
			}
			
			$this->cron_activate();
		}else{
			$text .= '<p style="color: red;">' . __( 'Poll Question is empty.', 'poll-dude' ) . '</p>';
		}

		return $text;
	}

	### Function: Cron Activate
	public function cron_activate() {
		wp_clear_scheduled_hook('poll_dude_cron');
		if (!wp_next_scheduled('poll_dude_cron')) {
			wp_schedule_event(time(), 'hourly', 'poll_dude_cron');
		}
	}

	### Funcion: Check All Polls Status To Check If It Expires
	
	public function cron_update() {
		global $wpdb;
		// Close Poll
		$close_polls = $wpdb->query("UPDATE $wpdb->polldude_q SET pollq_active = 0 WHERE pollq_expiry < '".current_time('timestamp')."' AND pollq_expiry != 0 AND pollq_active != 0");
		// Open Future Polls
		$active_polls = $wpdb->query("UPDATE $wpdb->polldude_q SET pollq_active = 1 WHERE pollq_timestamp <= '".current_time('timestamp')."' AND pollq_active = -1");
		// Update Latest Poll If Future Poll Is Opened
		if($active_polls) {
			$update_latestpoll = update_option('pd_latestpoll', polls_latest_id());
		}
		return;
	}	

}
