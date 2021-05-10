<?php
//namespace POLL_DUDE_NAME_SPACE;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    poll-dude
 * @subpackage poll-dude/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    poll-dude
 * @subpackage poll-dude/admin
 * @author     Your Name <email@example.com>
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
		add_action( 'admin_menu',                array($this, 'admin_menu') );
		add_action( 'admin_enqueue_scripts',     array($this, 'admin_scripts') );
		add_action( 'wp_ajax_poll-dude-control', array($this, 'control_panel') );
	}

	public function admin_scripts($hook_suffix){
		$admin_pages = array($this->plugin_name.'/poll-dude.php', $this->plugin_name.'/includes/page-poll-dude-add-form.php', $this->plugin_name.'/includes/page-poll-dude-control-panel.php');
		if(in_array($hook_suffix, $admin_pages, true)) {
			
			$this->enqueue_scripts();
			$this->enqueue_styles();
			/*
			wp_enqueue_style('poll-dude-admin', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/poll-dude-admin-css.css', false, POLL_DUDE_VERSION, 'all');
			wp_enqueue_script('poll-dude-admin', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);
			
			wp_localize_script('poll-dude', 'pollsAdminL10n', array(
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
			*/
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/poll-dude-admin.css', array(), $this->version, 'all' );
		//wp_enqueue_style('poll-dude-admin',   plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/poll-dude-admin-css.css', false, POLL_DUDE_VERSION, 'all');
	
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */		

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/poll-dude-admin.js', array( 'jquery' ), $this->version, true );
		//wp_enqueue_script('poll-dude-admin',   plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);
	}

	public function admin_menu() {
		add_menu_page(
			/* $page_title */__( 'Poll Dude', 'poll-dude-domain' ),
			/* $menu_title */__( 'Poll Dude', 'poll-dude-domain' ),
			/* $capability */'manage_options',
			/* $menu_slug  */'poll_dude_manager',
			'',
			'dashicons-chart-bar'
		);

		add_submenu_page( 
			/* $parent_slug */ 'poll_dude_manager', 
			/* $page_title  */ __( 'Add Poll', 'poll-dude-domain' ), 
			/* $menu_title  */ __( 'Add Poll', 'poll-dude-domain' ), 
			/* $capability  */ 'manage_options', 
			/* $menu_slug   */ plugin_dir_path( dirname( __FILE__ ) ) . '/includes/page-poll-dude-add-form.php'
		);

		add_submenu_page( 
			/* $parent_slug */ 'poll_dude_manager', 
			/* $page_title  */ __( 'Control Panel', 'poll-dude-domain' ), 
			/* $menu_title  */ __( 'Control Panel', 'poll-dude-domain' ), 
			/* $capability  */ 'manage_options', 
			/* $menu_slug   */ plugin_dir_path( dirname( __FILE__ ) ) . '/includes/page-poll-dude-control-panel.php'
		);

		add_submenu_page( 
			/* $parent_slug */ 'poll_dude_manager'
			,/* $page_title  */ __( 'Poll Setting', 'poll-dude-domain' )
			,/* $menu_title  */ __( 'Poll Setting', 'poll-dude-domain' )
			,/* $capability  */ 'manage_options'
			,/* $menu_slug   */ 'setting_polls'
		);
	}

	

	public function control_panel() {
		global $wpdb, $poll_dude;
		
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
								echo '<p style="color: green;">'.sprintf(__('All Logs For \'%s\' Has Been Deleted.', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
							} else {
								echo '<p style="color: red;">'.sprintf(__('An Error Has Occurred While Deleting All Logs For \'%s\'', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
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
						$polla_answers = wp_kses_post( $poll_dude->utility->removeslashes( trim( $poll_answers->polla_answers ) ) );
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
							echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Opened', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('Error Opening Poll \'%s\'', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
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
							echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Closed', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('Error Closing Poll \'%s\'', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
						break;
					// Delete Poll
					case __('Delete Poll', 'poll-dude-domain'):
						check_ajax_referer('wp-polls_delete-poll');
						echo 'Delete Poll';
						$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
						$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
						$delete_poll_question = $wpdb->delete( $wpdb->pollsq, array( 'pollq_id' => $pollq_id ), array( '%d' ) );
						$delete_poll_answers =  $wpdb->delete( $wpdb->pollsa, array( 'polla_qid' => $pollq_id ), array( '%d' ) );
						$delete_poll_ip =	   $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
						$poll_option_lastestpoll = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'poll_latestpoll'");
						if(!$delete_poll_question) {
							echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll \'%s\' Question', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
						if(empty($text)) {
							echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Deleted Successfully', 'poll-dude-domain'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
						}
								
						update_option( 'poll_latestpoll', $poll_dude->utility->latest_poll() );
						do_action( 'wp_polls_delete_poll', $pollq_id );
						
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

	

}
