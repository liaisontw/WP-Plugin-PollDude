<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_polls')) {
    die('Access Denied');
}

### Variables Variables Variables
$base_name = plugin_basename(__FILE__);
$base_page = 'admin.php?page='.$base_name;
$current_page = 'admin.php?page='.$plugin_name.'/includes/'.basename(__FILE__);
$mode       = ( isset( $_GET['mode'] ) ? sanitize_key( trim( $_GET['mode'] ) ) : '' );
$poll_id    = ( isset( $_GET['id'] ) ? (int) sanitize_key( $_GET['id'] ) : 0 );
$poll_aid   = ( isset( $_GET['aid'] ) ? (int) sanitize_key( $_GET['aid'] ) : 0 );
$text = '';



### Form Processing
if(!empty($_POST['do'])) {
    // Decide What To Do
    switch($_POST['do']) {
        // Edit Poll
        case __('Edit Poll', 'poll-dude-domain'):
            check_admin_referer( 'wp-polls_edit-poll' );
            $text = poll_dude_poll_config('edit');
            break;
    }
}

### Determines Which Mode It Is
switch($mode) {
    // Poll Logging
    case 'logs':
        require('polls-logs.php');
        break;
    // Edit A Poll
    case 'edit':
        $last_col_align = is_rtl() ? 'right' : 'left';
        $poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_question, pollq_timestamp, pollq_totalvotes, pollq_active, pollq_expiry, pollq_multiple, pollq_totalvoters FROM $wpdb->pollsq WHERE pollq_id = %d", $poll_id ) );
        $poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_answers, polla_votes FROM $wpdb->pollsa WHERE polla_qid = %d ORDER BY polla_aid ASC", $poll_id ) );
        $poll_noquestion = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(polla_aid) FROM $wpdb->pollsa WHERE polla_qid = %d", $poll_id ) );
        $poll_question_text = removeslashes($poll_question->pollq_question);
        $poll_totalvotes = (int) $poll_question->pollq_totalvotes;
        $poll_timestamp = $poll_question->pollq_timestamp;
        $poll_active = (int) $poll_question->pollq_active;
        $poll_expiry = trim($poll_question->pollq_expiry);
        $poll_multiple = (int) $poll_question->pollq_multiple;
        $poll_totalvoters = (int) $poll_question->pollq_totalvoters;

        require_once('page-poll-dude-poll-profile.php');

        break;
    // Main Page
    default:
        $polls = $wpdb->get_results( "SELECT * FROM $wpdb->pollsq  ORDER BY pollq_timestamp DESC" );
        $total_ans =  $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->pollsa" );
        $total_votes = 0;
        $total_voters = 0;
?>
        <!-- Last Action -->
        <div id="message" class="updated" style="display: none;"></div>

        <!-- Manage Polls -->
        <div class="wrap">
            <h2><?php _e('Manage Polls', 'poll-dude-domain'); ?></h2>
            <h3><?php _e('Polls', 'poll-dude-domain'); ?></h3>
            <br style="clear" />
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'poll-dude-domain'); ?></th>
                        <th><?php _e('Question', 'poll-dude-domain'); ?></th>
                        <th><?php _e('Total Voters', 'poll-dude-domain'); ?></th>
                        <th><?php _e('Start Date/Time', 'poll-dude-domain'); ?></th>
                        <th><?php _e('End Date/Time', 'poll-dude-domain'); ?></th>
                        <th><?php _e('Status', 'poll-dude-domain'); ?></th>
                        <th colspan="3"><?php _e('Action', 'poll-dude-domain'); ?></th>
                    </tr>
                </thead>
                <tbody id="manage_polls">
                    <?php
                        if($polls) {
                            $i = 0;
                            $current_poll = (int) get_option('poll_currentpoll');
                            $latest_poll = (int) get_option('poll_latestpoll');
                            foreach($polls as $poll) {
                                $poll_id = (int) $poll->pollq_id;
                                $poll_question = removeslashes($poll->pollq_question);
                                $poll_date = mysql2date(sprintf(__('%s @ %s', 'poll-dude-domain'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll->pollq_timestamp));
                                $poll_totalvotes = (int) $poll->pollq_totalvotes;
                                $poll_totalvoters = (int) $poll->pollq_totalvoters;
                                $poll_active = (int) $poll->pollq_active;
                                $poll_expiry = trim($poll->pollq_expiry);
                                if(empty($poll_expiry)) {
                                    $poll_expiry_text  = __('No Expiry', 'poll-dude-domain');
                                } else {
                                    $poll_expiry_text = mysql2date(sprintf(__('%s @ %s', 'poll-dude-domain'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_expiry));
                                }
                                if($i%2 == 0) {
                                    $style = 'class="alternate"';
                                }  else {
                                    $style = '';
                                }
                                if($current_poll > 0) {
                                    if($current_poll === $poll_id) {
                                        $style = 'class="highlight"';
                                    }
                                } elseif($current_poll === 0) {
                                    if($poll_id === $latest_poll) {
                                        $style = 'class="highlight"';
                                    }
                                }
                                echo "<tr id=\"poll-$poll_id\" $style>\n";
                                echo '<td><strong>'.number_format_i18n($poll_id).'</strong></td>'."\n";
                                echo '<td>';
                                if($current_poll > 0) {
                                    if($current_poll === $poll_id) {
                                        echo '<strong>'.__('Displayed:', 'poll-dude-domain').'</strong> ';
                                    }
                                } elseif($current_poll === 0) {
                                    if($poll_id === $latest_poll) {
                                        echo '<strong>'.__('Displayed:', 'poll-dude-domain').'</strong> ';
                                    }
                                }
                                echo wp_kses_post( $poll_question )."</td>\n";
                                echo '<td>'.number_format_i18n($poll_totalvoters)."</td>\n";
                                echo "<td>$poll_date</td>\n";
                                echo "<td>$poll_expiry_text</td>\n";
                                echo '<td>';
                                if($poll_active === 1) {
                                    _e('Open', 'poll-dude-domain');
                                } elseif($poll_active === -1) {
                                    _e('Future', 'poll-dude-domain');
                                } else {
                                    _e('Closed', 'poll-dude-domain');
                                }
                                echo "</td>\n";
                                echo "<td><a href=\"$base_page&amp;mode=logs&amp;id=$poll_id\" class=\"edit\">".__('Logs', 'poll-dude-domain')."</a></td>\n";
                                echo "<td><a href=\"$base_page&amp;mode=edit&amp;id=$poll_id\" class=\"edit\">".__('Edit', 'poll-dude-domain')."</a></td>\n";
                                echo "<td><a href=\"#DeletePoll\" onclick=\"delete_poll_dev($poll_id, '".sprintf(esc_js(__('You are about to delete this poll, \'%s\'.', 'poll-dude-domain')), esc_js($poll_question))."', '".wp_create_nonce('wp-polls_delete-poll')."');\" class=\"delete\">".__('Delete', 'poll-dude-domain')."</a></td>\n";
                                echo '</tr>';
                                $i++;
                                $total_votes+= $poll_totalvotes;
                                $total_voters+= $poll_totalvoters;

                            }
                        } else {
                            echo '<tr><td colspan="9" align="center"><strong>'.__('No Polls Found', 'poll-dude-domain').'</strong></td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <p>&nbsp;</p>

        <!-- Polls Stats -->
        <div class="wrap">
            <h3><?php _e('Polls Stats:', 'poll-dude-domain'); ?></h3>
            <br style="clear" />
            <table class="widefat">
            <tr>
                <th><?php _e('Total Polls:', 'poll-dude-domain'); ?></th>
                <td><?php echo number_format_i18n($i); ?></td>
            </tr>
            <tr class="alternate">
                <th><?php _e('Total Polls\' Answers:', 'poll-dude-domain'); ?></th>
                <td><?php echo number_format_i18n($total_ans); ?></td>
            </tr>
            <tr>
                <th><?php _e('Total Votes Cast:', 'poll-dude-domain'); ?></th>
                <td><?php echo number_format_i18n($total_votes); ?></td>
            </tr>
            <tr class="alternate">
                <th><?php _e('Total Voters:', 'poll-dude-domain'); ?></th>
                <td><?php echo number_format_i18n($total_voters); ?></td>
            </tr>
            </table>
        </div>
        <p>&nbsp;</p>

        <!-- Delete Polls Logs -->
        <div class="wrap">
            <h3><?php _e('Polls Logs', 'poll-dude-domain'); ?></h3>
            <br style="clear" />
            <div align="center" id="poll_logs">
            <?php
                $poll_ips = (int) $wpdb->get_var( "SELECT COUNT(pollip_id) FROM $wpdb->pollsip" );
                if($poll_ips > 0) {
            ?>
                <strong><?php _e('Are You Sure You Want To Delete All Polls Logs?', 'poll-dude-domain'); ?></strong><br /><br />
                <input type="checkbox" name="delete_logs_yes" id="delete_logs_yes" value="yes" />&nbsp;<label for="delete_logs_yes"><?php _e('Yes', 'poll-dude-domain'); ?></label><br /><br />
                <input type="button" value="<?php _e('Delete All Logs', 'poll-dude-domain'); ?>" class="button" onclick="delete_poll_logs('<?php echo esc_js(__('You are about to delete all poll logs. This action is not reversible.', 'poll-dude-domain')); ?>', '<?php echo wp_create_nonce('wp-polls_delete-polls-logs'); ?>');" />
            <?php
                } else {
                    _e('No poll logs available.', 'poll-dude-domain');
                }
            ?>
            </div>
            <p><?php _e('Note: If your logging method is by IP and Cookie or by Cookie, users may still be unable to vote if they have voted before as the cookie is still stored in their computer.', 'poll-dude-domain'); ?></p>
        </div>
<?php
} // End switch($mode)