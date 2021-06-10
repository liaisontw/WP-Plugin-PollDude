<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade">'.$poll_dude->utility->removeslashes($text).'</div>'; } else { echo '<div id="message" class="updated" style="display: none;"></div>'; } ?>
<form method="post" action="<?php echo 
	('edit' == $mode)? admin_url($current_page.'&amp;mode=edit&amp;id='.$poll_id)
					 : admin_url($current_page);
?>">
<?php ('edit' == $mode)? wp_nonce_field('polldude_edit-poll') : wp_nonce_field('polldude_add-poll');?>
<input type="hidden" name="pollq_id" value="<?php echo $poll_id; ?>" />
<input type="hidden" name="pollq_active" value="<?php echo $poll_active; ?>" />
<input type="hidden" name="poll_timestamp_old" value="<?php echo $poll_timestamp; ?>" />
<div class="wrap">
	<h2><?php ('edit' == $mode)? _e('Edit Poll', 'poll-dude'): _e('Add Poll', 'poll-dude'); ?></h2>
	<!-- Poll Question -->
	<h3><?php _e('Poll Question', 'poll-dude'); ?></h3>
	<input type="hidden" name="polldude_recaptcha" id="polldude_recaptcha" value="0"/>
	<input type="checkbox" name="polldude_recaptcha" id="polldude_recaptcha" value="1" <?php echo ($poll_recaptcha)? "checked":""; ?>/>
	<strong><?php _e('Enable reCaptcha', 'poll-dude'); ?></strong>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Question', 'poll-dude') ?></th>
			<td width="80%"><input type="text" size="70" name="pollq_question" value="<?php echo ('edit' != $mode) ? '': esc_attr( $poll_question_text ); ?>" /></td>
		</tr>
	</table>
	<!-- Poll Answers -->
	<h3><?php _e('Poll Answers', 'poll-dude'); ?></h3>
	<table class="form-table">
		<thead>
            <tr>
                <th width="60%" scope="row" valign="top" style="text-align: left"><?php _e('Answers and Colors', 'poll-dude') ?></th>
                <td width="20%" scope="row" valign="top" style="text-align: <?php echo $last_col_align; ?>;"><?php ('edit' != $mode)? _e('', 'poll-dude'): _e('No. Of Votes', 'poll-dude'); ?></td>
            </tr>
        </thead>
		<tbody id="poll_answers">
		<?php
			if ('edit' != $mode) { 
				$poll_noquestion = 2;
				for($i = 1; $i <= $poll_noquestion; $i++) {
					echo "<tr id=\"poll-answer-$i\">\n";
					echo "<td width=\"60%\"><input type=\"text\" size=\"45\" maxlength=\"200\" name=\"polla_answers[]\" /><input type=\"color\" id=\"color_picker\" name=\"color_picker[]\" value=\""; 
					echo get_option('pd_default_color');
					echo "\" >&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\"".__('Delete', 'poll-dude')."\" onclick=\"pd_remove_answer_add_form(".$i.");\" class=\"button\" /></td>\n";
					echo "</tr>\n";
				}
			} else {
				$i=1;
                $poll_actual_totalvotes = 0;
                if($poll_answers) {
                    $pollip_answers = array();
                    $pollip_answers[0] = __('Null Votes', 'poll-dude');
                    foreach($poll_answers as $poll_answer) {
                        $polla_aid = (int) $poll_answer->polla_aid;
                        $polla_answers = $poll_dude->utility->removeslashes($poll_answer->polla_answers);
                        $polla_votes = (int) $poll_answer->polla_votes;
                        $pollip_answers[$polla_aid] = $polla_answers;
						$poll_colors = $poll_answer->polla_colors;
                        echo "<tr id=\"poll-answer-$polla_aid\">\n";
                        echo "<td width=\"60%\">";
						echo "<input type=\"text\" size=\"45\" maxlength=\"200\" name=\"polla_aid-$polla_aid\" value=\"". esc_attr( $polla_answers ) . "\" />\n";
						echo "<input type=\"color\" id=\"color_picker\" name=\"color_picker[]\" value=\"$poll_colors\">";
						echo "&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\"".__('Delete', 'poll-dude')."\" onclick=\"pd_delete_ans($poll_id, $polla_aid, $polla_votes, '".sprintf(esc_js(__('You are about to delete this poll\'s answer \'%s\'.', 'poll-dude')), esc_js( esc_attr( $polla_answers ) ) ) . "', '".wp_create_nonce('polldude_delete-poll-answer')."');\" class=\"button\" />";
						echo "</td>\n";
                        echo "<td width=\"20%\" align=\"'.$last_col_align.'\">".number_format_i18n($polla_votes)." <input type=\"text\" size=\"4\" id=\"polla_votes-$polla_aid\" name=\"polla_votes-$polla_aid\" value=\"$polla_votes\" onblur=\"pd_totalvotes();\" /></td>\n</tr>\n";
                        $poll_actual_totalvotes += $polla_votes;
                        $i++;
                    }
				}
			}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td width="60%"><input type="button" value="<?php _e('Add Answer', 'poll-dude') ?>" onclick="<?php echo ('edit' != $mode)? 'pd_add_answer_add_form();' : 'pd_add_answer_edit();' ; ?>" class="button" /></td>
			</tr>
			<tr>
                <td width="30%" align="<?php ('edit' != $mode)? '': '$last_col_align'; ?>">
					<strong><?php ('edit' != $mode)? _e('', 'poll-dude'): _e('Total Votes:', 'poll-dude'); ?></strong> 
					<strong id="poll_total_votes">
						<?php echo ('edit' == $mode)? number_format_i18n($poll_actual_totalvotes): ''; ?>
					</strong> 
						<?php if ('edit' == $mode) { 
							echo '<input type="text" size="4" readonly="readonly" id="pollq_totalvotes" name="pollq_totalvotes" value="';
                            echo $poll_actual_totalvotes.'" onblur="pd_totalvotes();" />';
						} ?>
				</td>
				<td width="30%" align="<?php ('edit' != $mode)? '': $last_col_align; ?>">
					<strong><?php if ('edit' == $mode) { 
									_e('Total Voters:', 'poll-dude'); 
									echo number_format_i18n($poll_totalvoters); 
								}
					?></strong> 
					<?php if ('edit' == $mode) { 
						echo '<input type="text" size="4" name="pollq_totalvoters" value="'.$poll_totalvoters.'" />';
					}?>
				</td>
            </tr>
		</tfoot>
	</table>
	<!-- Poll Multiple Answers -->
	<h3><?php _e('Poll Multiple Answers', 'poll-dude') ?></h3>
	<table class="form-table">
		<tr>
			<th width="40%" scope="row" valign="top"><?php _e('Allows Users To Select More Than One Answer?', 'poll-dude'); ?></th>
			<td width="60%">
				<select name="pollq_multiple_yes" id="pollq_multiple_yes" size="1" onchange="pd_is_multiple_answer();">
					<option value="0"<?php if('edit'==$mode) { selected('0', $poll_multiple); }?>><?php _e('No', 'poll-dude'); ?></option>
                    <option value="1"<?php if('edit'==$mode) { if($poll_multiple > 0) { echo ' selected="selected"'; } } ?>><?php _e('Yes', 'poll-dude'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th width="40%" scope="row" valign="top"><?php _e('Maximum Number Of Selected Answers Allowed?', 'poll-dude') ?></th>
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
	<h3><?php _e('Poll Start/End Date', 'poll-dude'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Start Date/Time', 'poll-dude') ?></th>
			<td width="80%">
				<?php 
					if ('edit'!==$mode) {
						$poll_timestamp = current_time('timestamp');					
					}
					echo mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('time_format'), get_option('date_format')), gmdate('Y-m-d H:i:s', $poll_timestamp)).'<br />';
					echo '<input type="checkbox" name="edit_polltimestamp" id="edit_polltimestamp" value="1" onclick="pd_check_timestamp()" />&nbsp;<label for="edit_polltimestamp">';
					_e('Edit Start Date/Time', 'poll-dude'); 
					echo '</label><br />';
					$poll_dude->utility->time_select($poll_timestamp, 'pollq_timestamp', 'none');
				?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('End Date/Time', 'poll-dude') ?></th>
			<td width="80%">
				<?php
					if('edit'==$mode){
						if( empty($poll_expiry)) {
							_e('This Poll Will Not Expire', 'poll-dude');
						} else {
							echo mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_expiry));
						}
						echo '<br />';
					}
                ?>
				<input type="checkbox" name="pollq_expiry_no" id="pollq_expiry_no" value="1" onclick="pd_check_expiry();" <?php if(('edit'!=$mode) || empty($poll_expiry)) { echo 'checked="checked"'; } ?> />
                <label for="pollq_expiry_no"><?php _e('Do NOT Expire This Poll', 'poll-dude'); ?></label><br />
                <?php
					if(('edit'!=$mode) || empty($poll_expiry)) {
						$poll_dude->utility->time_select(current_time('timestamp'), 'pollq_expiry', 'none');
					} else {
						$poll_dude->utility->time_select($poll_expiry, 'pollq_expiry');
					}
                ?>
			</td>
		</tr>
	</table>
	<p style="text-align: center;">
	<input id="add_edit" type="submit" name="do" value="<?php ('edit' != $mode)? _e('Add Poll', 'poll-dude'): _e('Edit Poll', 'poll-dude'); ?>"  class="button-primary" />&nbsp;&nbsp;
	<?php
		if('edit'==$mode) {
			if($poll_active == 1) {
				$pd_open_display = 'none';
				$pd_close_display = 'inline';
			} else {
				$pd_open_display = 'inline';
				$pd_close_display = 'none';
			}
	?>
    <input type="button" class="button" name="do" id="close_poll" value="<?php _e('Close Poll', 'poll-dude'); ?>" onclick="pd_close_poll(<?php echo $poll_id; ?>, '<?php printf(esc_js(__('You are about to CLOSE this poll \'%s\'.', 'poll-dude')), esc_attr( esc_js( $poll_question_text ) ) ); ?>', '<?php echo wp_create_nonce('polldude_close-poll'); ?>');" style="display: <?php echo $pd_close_display; ?>;" />    
	<input type="button" class="button" name="do" id="open_poll" value="<?php _e('Open Poll', 'poll-dude'); ?>" onclick="pd_open_poll(<?php echo $poll_id; ?>, '<?php printf(esc_js(__('You are about to OPEN this poll \'%s\'.', 'poll-dude')), esc_attr( esc_js( $poll_question_text ) ) ); ?>', '<?php echo wp_create_nonce('polldude_open-poll'); ?>');" style="display: <?php echo $pd_open_display; ?>;" />
	<?php
		}
	?>		
	<input type="button" name="cancel" value="<?php _e('Cancel', 'poll-dude'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
</div>
</form>