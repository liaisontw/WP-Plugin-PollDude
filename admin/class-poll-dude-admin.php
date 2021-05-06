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
	private $name;

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
		$this->name = $plugin_name;
		$this->version = $version;
		add_action( 'admin_menu',                array($this, 'admin_menu') );
		add_action( 'admin_enqueue_scripts',     array($this, 'admin_scripts') );
		add_action( 'wp_ajax_poll-dude-control', array($this, 'control_panel') );
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

	public function admin_scripts($hook_suffix){
		$admin_pages = array($this->name.'/poll-dude.php', $this->name.'/includes/page-poll-dude-add-form.php', $this->name.'/includes/page-poll-dude-control-panel.php');
		if(in_array($hook_suffix, $admin_pages, true)) {
			wp_enqueue_style('poll-dude-admin', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/poll-dude-admin-css.css', false, POLL_DUDE_VERSION, 'all');
			wp_enqueue_script('poll-dude-admin', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);
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

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		//wp_enqueue_style('poll-dude', plugins_url('poll-dude/admin/css/poll-dude-admin.css'), false, POLL_DUDE_VERSION, 'all');
		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/poll-dude-admin.css', array(), $this->version, 'all' );
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
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), $this->version, false );
		//wp_enqueue_script('poll-dude', plugins_url('poll-dude/admin/js/poll-dude-admin.js'), array('jquery'), POLL_DUDE_VERSION, true);
	}

}
