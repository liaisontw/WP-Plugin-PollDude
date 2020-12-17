<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade">'.removeslashes($text).'</div>'; } else { echo '<div id="message" class="updated" style="display: none;"></div>'; } ?>
<form method="post" action="<?php echo 
	('edit' == $mode)? admin_url($current_page.'&amp;mode=edit&amp;id='.$poll_id)
					 : admin_url($current_page);
?>">
<?php ('edit' == $mode)? wp_nonce_field('wp-polls_edit-poll') : wp_nonce_field('wp-polls_add-poll');?>
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
							echo '<input type="text" size="4" readonly="readonly" id="pollq_totalvotes" name="pollq_totalvotes" value="';
                            echo $poll_actual_totalvotes.'" onblur="check_totalvotes();" />';
						} ?>
				</td>
			</tr>
		</tfoot>
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
					poll_dude_time_select($poll_timestamp, 'pollq_timestamp', 'none');
				}else{
					poll_dude_time_select(current_time('timestamp'));
				}
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
				<input type="checkbox" name="pollq_expiry_no" id="pollq_expiry_no" value="1" onclick="check_pollexpiry();" <?php if(('edit'!=$mode) || empty($poll_expiry)) { echo 'checked="checked"'; } ?> />
                <label for="pollq_expiry_no"><?php _e('Do NOT Expire This Poll', 'wp-polls'); ?></label><br />
                <?php
					if(('edit'!=$mode) || empty($poll_expiry)) {
						poll_dude_time_select(current_time('timestamp'), 'pollq_expiry', 'none');
					} else {
						poll_dude_time_select($poll_expiry, 'pollq_expiry');
					}
                ?>
			</td>
		</tr>
	</table>
	<p style="text-align: center;">
	<input type="submit" name="do" value="<?php ('edit' != $mode)? _e('Add Poll', 'wp-polls'): _e('Edit Poll', 'wp-polls'); ?>"  class="button-primary" />&nbsp;&nbsp;
	<?php
		if('edit'==$mode) {
			if($poll_active == 1) {
				$poll_open_display = 'none';
				$poll_close_display = 'inline';
			} else {
				$poll_open_display = 'inline';
				$poll_close_display = 'none';
			}
    
			echo '<input type="button" class="button" name="do" id="close_poll" value="';
			_e('Close Poll', 'wp-polls'); 
			echo '" onclick="closing_poll('.$poll_id.',';
			//echo $poll_id.',';
			printf(esc_js(__('You are about to CLOSE this poll \'%s\'.', 'wp-polls')), esc_attr( esc_js( $poll_question_text ) ) ); 
			echo ', '.wp_create_nonce('wp-polls_close-poll');
			//echo wp_create_nonce('wp-polls_close-poll');
			echo ');" style="display: '.$poll_close_display.';" />';
			//echo $poll_close_display;
			//echo ';" />';
			echo '<input type="button" class="button" name="do" id="open_poll" value="';
			_e('Open Poll', 'wp-polls');
			echo '" onclick="opening_poll(';
			echo $poll_id;
			echo ', ';
			printf(esc_js(__('You are about to OPEN this poll \'%s\'.', 'wp-polls')), esc_attr( esc_js( $poll_question_text ) ) );
			echo ', ';
			echo wp_create_nonce('wp-polls_open-poll');
			echo ');" style="display: ';
			echo $poll_open_display;
			echo '" />';
		}
		?>	
	<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-polls'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
</div>
</form>