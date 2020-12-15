<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

### Poll Manager
$base_name = $poll_dude_base;
$base_page = 'admin.php?page='.$base_name;
$mode       = ( isset( $_GET['mode'] ) ? sanitize_key( trim( $_GET['mode'] ) ) : '' );

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




### Add Poll Form
$poll_noquestion = 2;
$count = 0;

?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade">'.removeslashes($text).'</div>'; } ?>
<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
<?php wp_nonce_field('wp-polls_add-poll'); ?>
<div class="wrap">
	<h2><?php ('edit' != $mode)? _e('Add Poll', 'wp-polls'): _e('Edit Poll', 'wp-polls'); ?></h2>
	<!-- Poll Question -->
	<h3><?php _e('Poll Question', 'wp-polls'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Question', 'wp-polls') ?></th>
			<td width="80%"><input type="text" size="70" name="pollq_question" value="<?php echo ('edit' != $mode) ? '': esc_attr( $poll_question_text ); ?>" /></td>
		</tr>
	</table>
	<!-- Poll Answers -->
	<h3><?php _e('Poll Answers', 'wp-polls'); ?></h3>
	<table class="form-table">
		<thead>
            <tr>
                <th width="20%" scope="row" valign="top"><?php _e('Answer No.', 'wp-polls') ?></th>
                <th width="60%" scope="row" valign="top"><?php _e('Answer Text', 'wp-polls') ?></th>
                <th width="20%" scope="row" valign="top" style="text-align: <?php echo $last_col_align; ?>;"><?php ('edit' != $mode)? _e('', 'wp-polls'): _e('No. Of Votes', 'wp-polls'); ?></th>
            </tr>
        </thead>
		<tfoot>
			<tr>
				<td width="20%">&nbsp;</td>
				<td width="60%"><input type="button" value="<?php _e('Add Answer', 'wp-polls') ?>" onclick="add_poll_answer_add();" class="button" /></td>
				<td width="20%" align="<?php echo ('edit' != $mode)? '': $last_col_align; ?>">
					<strong><?php ('edit' != $mode)? _e('', 'wp-polls'): _e('Total Votes:', 'wp-polls'); ?></strong> 
					<strong id="poll_total_votes">
						<?php echo ('edit' != $mode)? '': number_format_i18n($poll_actual_totalvotes); ?>
					</strong> 
						<?php if ('edit' == $mode) { 
							echo '<input type="text" size="4" readonly="readonly" id="pollq_totalvotes" name="pollq_totalvotes" value="$poll_actual_totalvotes" onblur="check_totalvotes();" />';
						} ?>
				</td>
			</tr>
		</tfoot>
		<tbody id="poll_answers">
		<?php
			if ('edit' != $mode) { 
				for($i = 1; $i <= $poll_noquestion; $i++) {
					echo "<tr id=\"poll-answer-$i\">\n";
					echo "<th width=\"20%\" scope=\"row\" valign=\"top\">".sprintf(__('Answer %s', 'wp-polls'), number_format_i18n($i))."</th>\n";
					echo "<td width=\"60%\"><input type=\"text\" size=\"50\" maxlength=\"200\" name=\"polla_answers[]\" />&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\"".__('Remove', 'wp-polls')."\" onclick=\"remove_poll_answer_add(".$i.");\" class=\"button\" /></td>\n";
					echo "</tr>\n";
					$count++;
				}
			} else {
				$i=1;
                $poll_actual_totalvotes = 0;
                if($poll_answers) {
                    $pollip_answers = array();
                    $pollip_answers[0] = __('Null Votes', 'wp-polls');
                    foreach($poll_answers as $poll_answer) {
                        $polla_aid = (int) $poll_answer->polla_aid;
                        $polla_answers = removeslashes($poll_answer->polla_answers);
                        $polla_votes = (int) $poll_answer->polla_votes;
                        $pollip_answers[$polla_aid] = $polla_answers;
                        echo "<tr id=\"poll-answer-$polla_aid\">\n";
                        echo '<th width="20%" scope="row" valign="top">'.sprintf(__('Answer %s', 'wp-polls'), number_format_i18n($i)).'</th>'."\n";
                        echo "<td width=\"60%\"><input type=\"text\" size=\"50\" maxlength=\"200\" name=\"polla_aid-$polla_aid\" value=\"". esc_attr( $polla_answers ) . "\" />&nbsp;&nbsp;&nbsp;";
                        echo "<input type=\"button\" value=\"".__('Delete', 'wp-polls')."\" onclick=\"delete_poll_ans_dev($poll_id, $polla_aid, $polla_votes, '".sprintf(esc_js(__('You are about to delete this poll\'s answer \'%s\'.', 'wp-polls')), esc_js( esc_attr( $polla_answers ) ) ) . "', '".wp_create_nonce('wp-polls_delete-poll-answer')."');\" class=\"button\" /></td>\n";
                        echo '<td width="20%" align="'.$last_col_align.'">'.number_format_i18n($polla_votes)." <input type=\"text\" size=\"4\" id=\"polla_votes-$polla_aid\" name=\"polla_votes-$polla_aid\" value=\"$polla_votes\" onblur=\"check_totalvotes();\" /></td>\n</tr>\n";
                        $poll_actual_totalvotes += $polla_votes;
                        $i++;
                    }
				}
			}
		?>
		</tbody>
	</table>
	<!-- Poll Multiple Answers -->
	<h3><?php _e('Poll Multiple Answers', 'wp-polls') ?></h3>
	<table class="form-table">
		<tr>
			<th width="40%" scope="row" valign="top"><?php _e('Allows Users To Select More Than One Answer?', 'wp-polls'); ?></th>
			<td width="60%">
				<select name="pollq_multiple_yes" id="pollq_multiple_yes" size="1" onchange="check_pollq_multiple();">
					<option value="0"<?php if('edit'==$mode) { selected('0', $poll_multiple); }?>><?php _e('No', 'wp-polls'); ?></option>
                    <option value="1"<?php if('edit'==$mode) { if($poll_multiple > 0) { echo ' selected="selected"'; } } ?>><?php _e('Yes', 'wp-polls'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th width="40%" scope="row" valign="top"><?php _e('Maximum Number Of Selected Answers Allowed?', 'wp-polls') ?></th>
			<td width="60%">
				<select name="pollq_multiple" id="pollq_multiple" size="1" <?php if(('edit'!=$mode)||($poll_multiple == 0)) { echo 'disabled="disabled"'; } ?>>
					<?php
						for($i = 1; $i <= $poll_noquestion; $i++) {
							if(isset($poll_multiple) && $poll_multiple > 0 && $poll_multiple == $i) {
                                echo "<option value=\"$i\" selected=\"selected\">".number_format_i18n($i)."</option>\n";
                            } else {
								echo "<option value=\"$i\">".number_format_i18n($i)."</option>\n";
							}
						}
					?>
				</select>
			</td>
		</tr>
	</table>
	<!-- Poll Start/End Date -->
	<h3><?php _e('Poll Start/End Date', 'wp-polls'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Start Date/Time', 'wp-polls') ?></th>
			<td width="80%">
				<?php 
				if ('edit'==$mode) {
					echo mysql2date(sprintf(__('%s @ %s', 'wp-polls'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_timestamp)).'<br />';
					echo '<input type="checkbox" name="edit_polltimestamp" id="edit_polltimestamp" value="1" onclick="check_polltimestamp()" />&nbsp;<label for="edit_polltimestamp">';
					_e('Edit Start Date/Time', 'wp-polls'); 
					echo '</label><br />';
				}
				poll_dude_time_select(current_time('timestamp'));
				?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('End Date/Time', 'wp-polls') ?></th>
			<td width="80%">
				<?php
					if('edit'==$mode){
						if( empty($poll_expiry)) {
							_e('This Poll Will Not Expire', 'wp-polls');
						} else {
							echo mysql2date(sprintf(__('%s @ %s', 'wp-polls'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_expiry));
						}
						echo '<br />';
					}
                ?>
				<input type="checkbox" name="pollq_expiry_no" id="pollq_expiry_no" value="1" checked="checked" onclick="check_pollexpiry();" />&nbsp;&nbsp;<label for="pollq_expiry_no"><?php _e('Do NOT Expire This Poll', 'wp-polls'); ?></label>
				<?php poll_dude_time_select(current_time('timestamp'), 'pollq_expiry', 'none'); ?>
			</td>
		</tr>
	</table>
	<p style="text-align: center;"><input type="submit" name="do" value="<?php _e('Add Poll', 'wp-polls'); ?>"  class="button-primary" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-polls'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
</div>
</form>
