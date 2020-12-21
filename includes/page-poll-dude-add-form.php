<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

### Poll Manager
$base_name = $poll_dude_base;
$base_page = 'admin.php?page='.$base_name;
$current_page = 'admin.php?page='.$plugin_name.'/includes/'.basename(__FILE__);
$mode       = ( isset( $_GET['mode'] ) ? sanitize_key( trim( $_GET['mode'] ) ) : '' );


### Form Processing
if ( ! empty($_POST['do'] ) ) {
	// Decide What To Do
	switch ( $_POST['do'] ) {
		// Add Poll
		case __( 'Add Poll', 'wp-polls' ):
			check_admin_referer( 'wp-polls_add-poll' );

			$text = poll_dude_poll_content_config('add');
			break;
	}
}




### Add Poll Form
$poll_noquestion = 2;
$count = 0;

require_once('page-poll-dude-poll-profile.php');
?>




