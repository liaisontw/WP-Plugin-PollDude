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
			$text = '';
			// Poll Question
			$pollq_question = isset( $_POST['pollq_question'] ) ? wp_kses_post( trim( $_POST['pollq_question'] ) ) : '';

			if ( ! empty( $pollq_question ) ) {
				// Poll Start Date
				$timestamp_sql = '';			
				$pollq_timestamp = poll_dude_time_make('pollq_timestamp');
				
				if ( $pollq_timestamp > current_time( 'timestamp' ) ) {
					$pollq_active = -1;
				} else {
					$pollq_active = 1;
				}
				// Poll End Date
				
				
				$pollq_expiry_no = isset( $_POST['pollq_expiry_no'] ) ? (int) sanitize_key( $_POST['pollq_expiry_no'] ) : 0;
				
				if ( $pollq_expiry_no === 1 ) {
					$pollq_expiry = 0;
				} else {
				
					$pollq_expiry = poll_dude_time_make('pollq_expiry');
					echo "<br>".$pollq_expiry;
					if ( $pollq_expiry <= current_time( 'timestamp' ) ) {
						$pollq_active = 0;
					}
				}
				
				
				// Mutilple Poll
				$pollq_multiple_yes = isset( $_POST['pollq_multiple_yes'] ) ? (int) sanitize_key( $_POST['pollq_multiple_yes'] ) : 0;
				$pollq_multiple = 0;
				if ( $pollq_multiple_yes === 1 ) {
					$pollq_multiple = isset( $_POST['pollq_multiple'] ) ? (int) sanitize_key( $_POST['pollq_multiple'] ) : 0;
				} else {
					$pollq_multiple = 0;
				}
				
				
				// Insert Poll
				$add_poll_question = $wpdb->insert(
					$wpdb->pollsq,
					array(
						'pollq_question'    => $pollq_question,
						'pollq_timestamp'   => $pollq_timestamp,
						'pollq_totalvotes'  => 0,
						'pollq_active'      => $pollq_active,
						'pollq_expiry'      => $pollq_expiry,
						//'pollq_multiple'    => $pollq_multiple,
						'pollq_multiple'    => 0,
						'pollq_totalvoters' => 0
					),
					array(
						'%s',
						'%s',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d'
					)
				);
				if ( ! $add_poll_question ) {
					$text .= '<p style="color: red;">' . sprintf(__('Error In Adding Poll \'%s\'.', 'wp-polls'), $pollq_question) . '</p>';
				}
				
				// Add Poll Answers
				$polla_answers = isset( $_POST['polla_answers'] ) ? $_POST['polla_answers'] : array();
				//foreach ( $polla_answers as $polla_answer ) {echo "<br>".$polla_answer}
				
				$polla_qid = (int) $wpdb->insert_id;
				foreach ( $polla_answers as $polla_answer ) {
					$polla_answer = wp_kses_post( trim( $polla_answer ) );
					if ( ! empty( $polla_answer ) ) {
						$add_poll_answers = $wpdb->insert(
							$wpdb->pollsa,
							array(
								'polla_qid'	  => $polla_qid,
								'polla_answers'  => $polla_answer,
								'polla_votes'	=> 0
							),
							array(
								'%d',
								'%s',
								'%d'
							)
						);
						if ( ! $add_poll_answers ) {
							$text .= '<p style="color: red;">' . sprintf(__('Error In Adding Poll\'s Answer \'%s\'.', 'wp-polls'), $polla_answer) . '</p>';
						}
					} else {
						$text .= '<p style="color: red;">' . __( 'Poll\'s Answer is empty.', 'wp-polls' ) . '</p>';
					}
				}
				

				// Update Lastest Poll ID To Poll Options
				
				$latest_pollid = poll_dude_latest_id();
				$update_latestpoll = update_option( 'poll_latestpoll', $latest_pollid );
				// If poll starts in the future use the correct poll ID
				$latest_pollid = ( $latest_pollid < $polla_qid ) ? $polla_qid : $latest_pollid;

				if ( empty( $text ) ) {
					$text = '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) added successfully. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'wp-polls' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />', $base_page ) . '</p>';
				} else {
					if ( $add_poll_question ) {
						$text .= '<p style="color: green;">' . sprintf( __( 'Poll \'%s\' (ID: %s) (Shortcode: %s) added successfully, but there are some errors with the Poll\'s Answers. Embed this poll with the shortcode: %s or go back to <a href="%s">Manage Polls</a>', 'wp-polls' ), $pollq_question, $latest_pollid, '<input type="text" value=\'[poll id="' . $latest_pollid . '"]\' readonly="readonly" size="10" />' ) .'</p>';
					}
				}
				do_action( 'wp_polls_add_poll', $latest_pollid );
				//cron_polls_place();			
			} else {
				$text .= '<p style="color: red;">' . __( 'Poll Question is empty.', 'wp-polls' ) . '</p>';
			}

			break;
	}
}




### Add Poll Form
$poll_noquestion = 2;
$count = 0;

require_once('page-poll-dude-poll-profile.php');
?>




