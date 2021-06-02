<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

### Poll Manager
global $poll_dude;
$base_name = plugin_basename( __FILE__ );
$base_page = 'admin.php?page='.$base_name;
$current_page = 'admin.php?page='.$poll_dude->get_plugin_name().'/view/'.basename(__FILE__);
$mode       = ( isset( $_GET['mode'] ) ? sanitize_key( trim( $_GET['mode'] ) ) : '' );
$poll_id    = ( isset( $_GET['id'] ) ? (int) sanitize_key( $_GET['id'] ) : 0 );
$poll_aid   = ( isset( $_GET['aid'] ) ? (int) sanitize_key( $_GET['aid'] ) : 0 );
$poll_active = 0;
$poll_timestamp = 0;
$last_col_align = is_rtl() ? 'right' : 'left';
$poll_recaptcha = 1;


### Form Processing
if ( ! empty($_POST['do'] ) ) {
	// Decide What To Do
	switch ( $_POST['do'] ) {
		// Add Poll
		case __( 'Add Poll', 'poll-dude-domain' ):
			check_admin_referer( 'wp-polls_add-poll' );
			
			$text = $poll_dude->admin->poll_config('add', $base_name);
			break;
	}
}




### Add Poll Form

require_once('page-poll-dude-poll-profile.php');
?>




