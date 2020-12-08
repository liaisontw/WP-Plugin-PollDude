<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

### Poll Manager
$base_name = $poll_dude_base;
$base_page = 'admin.php?page='.$base_name;

/*
$wpdb->pollsq,
array(
	'pollq_question'    => $pollq_question,
	'pollq_timestamp'   => $pollq_timestamp,
	'pollq_totalvotes'  => 0,
	'pollq_active'      => $pollq_active,
	'pollq_expiry'      => $pollq_expiry,
	'pollq_multiple'    => $pollq_multiple,
	'pollq_totalvoters' => 0
),

	$_POST['do']

	'pollq_question'    => $pollq_question,
	$_POST['pollq_question']

	'pollq_timestamp'   => $pollq_timestamp,
	$_POST['pollq_timestamp_day']
	$_POST['pollq_timestamp_month']
	$_POST['pollq_timestamp_year']
	$_POST['pollq_timestamp_hour']
	$_POST['pollq_timestamp_minute']
	$_POST['pollq_timestamp_second']
	$_POST['pollq_expiry_no']
	
	'pollq_expiry'      => $pollq_expiry,
	$_POST['pollq_expiry_day'] 
	$_POST['pollq_expiry_month']
	$_POST['pollq_expiry_year']
	$_POST['pollq_expiry_hour']
	$_POST['pollq_expiry_minute']
	$_POST['pollq_expiry_second']
	$_POST['pollq_multiple_yes']
	$_POST['pollq_multiple']
	$_POST['polla_answers']

*/

### Funcion: Get Latest Poll ID
function polls_latest_id() {
	global $wpdb;
	$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY pollq_timestamp DESC LIMIT 1");
	return (int) $poll_id;
}

function poll_dude_time_make($fieldname /*= 'pollq_timestamp'*/) {

	$time_parse = array('_hour'   => 0, '_minute' => 0, '_second' => 0,
						'_day'    => 0, '_month'  => 0, '_year'   => 0
	);

	foreach($time_parse as $key => $value) {
		$poll_dude_time_stamp = $fieldname.$key;
		//echo "<br>".$poll_dude_time_stamp;
		//echo "<br>".$_POST[$poll_dude_time_stamp];

		$time_parse[$key] = isset( $_POST[$poll_dude_time_stamp] ) ? 
				 (int) sanitize_key( $_POST[$poll_dude_time_stamp] ) : 0;
		//echo "<br>".$time_parse[$key];
	}

	$return_timestamp = gmmktime( $time_parse['_hour']  , 
								  $time_parse['_minute'], 
								  $time_parse['_second'], 
								  $time_parse['_month'] , 
								  $time_parse['_day']   , 
								  $time_parse['_year']   );

	return 	$return_timestamp;					  
}

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
			//echo "<br>".$_POST['pollq_question'];
			if ( ! empty( $pollq_question ) ) {
				// Poll Start Date
				$timestamp_sql = '';			
				$pollq_timestamp = poll_dude_time_make('pollq_timestamp');
				echo "<br>".$pollq_timestamp;
				if ( $pollq_timestamp > current_time( 'timestamp' ) ) {
					$pollq_active = -1;
				} else {
					$pollq_active = 1;
				}
				// Poll End Date
				/*
				echo "<br>".$_POST['pollq_expiry_no'];
				$pollq_expiry_no = isset( $_POST['pollq_expiry_no'] ) ? (int) sanitize_key( $_POST['pollq_expiry_no'] ) : 0;
				
				if ( $pollq_expiry_no === 1 ) {
					$pollq_expiry = 0;
				} else {
				*/
					$pollq_expiry = poll_dude_time_make('pollq_expiry');
					echo "<br>".$pollq_expiry;
					if ( $pollq_expiry <= current_time( 'timestamp' ) ) {
						$pollq_active = 0;
					}
				//}
				/*
				// Mutilple Poll
				$pollq_multiple_yes = isset( $_POST['pollq_multiple_yes'] ) ? (int) sanitize_key( $_POST['pollq_multiple_yes'] ) : 0;
				$pollq_multiple = 0;
				if ( $pollq_multiple_yes === 1 ) {
					$pollq_multiple = isset( $_POST['pollq_multiple'] ) ? (int) sanitize_key( $_POST['pollq_multiple'] ) : 0;
				} else {
					$pollq_multiple = 0;
				}
				*/
				
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
				
				$latest_pollid = polls_latest_id();
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



### Add Poll Form
$poll_noquestion = 2;
$count = 0;

?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade">'.removeslashes($text).'</div>'; } ?>
<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
<?php wp_nonce_field('wp-polls_add-poll'); ?>
<div class="wrap">
	<h2><?php _e('Add Poll', 'wp-polls'); ?></h2>
	<!-- Poll Question -->
	<h3><?php _e('Poll Question', 'wp-polls'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Question', 'wp-polls') ?></th>
			<td width="80%"><input type="text" size="70" name="pollq_question" value="" /></td>
		</tr>
	</table>
	<!-- Poll Answers -->
	<h3><?php _e('Poll Answers', 'wp-polls'); ?></h3>
	<table class="form-table">
		<tfoot>
			<tr>
				<td width="20%">&nbsp;</td>
				<td width="80%"><input type="button" value="<?php _e('Add Answer', 'wp-polls') ?>" onclick="add_poll_answer_add();" class="button" /></td>
			</tr>
		</tfoot>
		<tbody id="poll_answers">
		<?php
			for($i = 1; $i <= $poll_noquestion; $i++) {
				echo "<tr id=\"poll-answer-$i\">\n";
				echo "<th width=\"20%\" scope=\"row\" valign=\"top\">".sprintf(__('Answer %s', 'wp-polls'), number_format_i18n($i))."</th>\n";
				echo "<td width=\"80%\"><input type=\"text\" size=\"50\" maxlength=\"200\" name=\"polla_answers[]\" />&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\"".__('Remove', 'wp-polls')."\" onclick=\"remove_poll_answer_add(".$i.");\" class=\"button\" /></td>\n";
				echo "</tr>\n";
				$count++;
			}
		?>
		</tbody>
	</table>
	<!-- Poll Start/End Date -->
	<h3><?php _e('Poll Start/End Date', 'wp-polls'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Start Date/Time', 'wp-polls') ?></th>
			<td width="80%"><?php poll_dude_time_select(current_time('timestamp')); ?></td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('End Date/Time', 'wp-polls') ?></th>
			<td width="80%"><?php poll_dude_time_select(current_time('timestamp'), 'pollq_expiry'); ?></td>
		</tr>
	</table>
	<p style="text-align: center;"><input type="submit" name="do" value="<?php _e('Add Poll', 'wp-polls'); ?>"  class="button-primary" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-polls'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
</div>
</form>
