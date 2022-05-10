<?php
### Check Whether User Can Manage Polls
if( ! current_user_can( 'manage_polls' ) ) {
    die( 'Access Denied' );
}


### Variables
$max_records = 2000;

$exclude_registered = 0;
$exclude_comment = 0;
$exclude_guest = 0;

$users_voted_for = null;
$what_user_voted = null;

### Process Filters

if( ! empty( $_POST['do'] ) ) {
    check_admin_referer('poll-dude_logs');
    $registered_sql = '';
    $comment_sql = '';
    $guest_sql = '';
    $users_voted_for_sql = '';
    $what_user_voted_sql = '';
    $num_choices_sql = '';
    $num_choices_sign_sql = '';
    $order_by = '';
    $users_voted_for = (int) sanitize_key( $_POST['users_voted_for'] );
    $exclude_registered = isset( $_POST['exclude_registered'] ) && (int) sanitize_key( $_POST['exclude_registered'] ) === 1;
    $exclude_comment = isset( $_POST['exclude_comment'] ) && (int) sanitize_key( $_POST['exclude_comment'] ) === 1;
    $exclude_guest = isset( $_POST['exclude_guest'] ) && (int) sanitize_key( $_POST['exclude_guest'] ) === 1;
    $users_voted_for_sql = "AND pollip_aid = $users_voted_for";
    if($exclude_registered) {
        $registered_sql = 'AND pollip_userid = 0';
    }
    if($exclude_comment) {
        if(!$exclude_registered) {
            $comment_sql = 'AND pollip_userid > 0';
        } else {
            $comment_sql = 'AND pollip_user = \''.__('Guest', 'poll-dude').'\'';
        }
    }
    if($exclude_guest) {
        $guest_sql  = 'AND pollip_user != \''.__('Guest', 'poll-dude').'\'';
    }
    $order_by = 'pollip_timestamp DESC';
    $poll_ips = $wpdb->get_results("SELECT $wpdb->polldude_ip.* FROM $wpdb->polldude_ip WHERE pollip_qid = $poll_id $users_voted_for_sql $registered_sql $comment_sql $guest_sql $what_user_voted_sql $num_choices_sql ORDER BY $order_by");
} else {
    $poll_ips = $wpdb->get_results( $wpdb->prepare( "SELECT pollip_aid, pollip_ip, pollip_host, pollip_timestamp, pollip_user FROM $wpdb->polldude_ip WHERE pollip_qid = %d ORDER BY pollip_aid ASC, pollip_user ASC LIMIT %d", $poll_id, $max_records ) );
}

?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade">'.$poll_dude->utility->$poll_dude->utility->removeslashes($text).'</div>'; } else { echo '<div id="message" class="updated" style="display: none;"></div>'; } ?>
<div class="wrap">
    <h2><?php _e('Vote Logs', 'poll-dude'); ?></h2>
    <h3><?php echo $poll_question_text; ?></h3>
    <p>
        <?php printf(_n('Total <strong>%s</strong> records of this vote.', 'Total <strong>%s</strong> records of this vote.', $poll_totalrecorded, 'poll-dude'), number_format_i18n($poll_totalrecorded)); ?><br />
        <?php printf(_n('<strong>: : </strong> <strong>%s</strong> records are by registered users', '<strong>: : </strong> <strong>%s</strong> records are by registered users', $poll_registered, 'poll-dude'), number_format_i18n($poll_registered)); ?><br />
        <?php printf(_n('<strong>: : </strong> <strong>%s</strong> records are by guests', '<strong>: : </strong> <strong>%s</strong> records are by guests', $poll_guest, 'poll-dude'), number_format_i18n($poll_guest)); ?>
    </p>
</div>

<div class="wrap">
    <h3><?php _e('Vote Logs', 'poll-dude'); ?></h3>
    <div id="poll_logs_display">
        <?php
            if($poll_ips) {
                echo '<table class="widefat">'."\n";
                echo "<tr class=\"highlight\"><td colspan=\"4\">". $poll_question_text . "</td></tr>";
                $k = 1;
                $j = 0;
                $poll_last_aid = -1;
                $temp_pollip_user = null;
                if(isset($_POST['filter']) && (int) sanitize_key( $_POST['filter'] ) > 1) {
                    echo "<tr class=\"thead\">\n";
                    echo "<th>".__('Answer', 'poll-dude')."</th>\n";
                    echo "<th>".__('IP', 'poll-dude')."</th>\n";
                    echo "<th>".__('Host', 'poll-dude')."</th>\n";
                    echo "<th>".__('Date', 'poll-dude')."</th>\n";
                    echo "</tr>\n";
                    foreach($poll_ips as $poll_ip) {
                        $pollip_aid = (int) $poll_ip->pollip_aid;
                        $pollip_user = $poll_dude->utility->removeslashes($poll_ip->pollip_user);
                        $pollip_ip = $poll_ip->pollip_ip;
                        $pollip_host = $poll_ip->pollip_host;
                        $pollip_date = mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_ip->pollip_timestamp));

                        $i = 0;
                        if($i % 2 === 0) {
                            $style = '';
                        }  else {
                            $style = 'class="alternate"';
                        }
                        if($pollip_user != $temp_pollip_user) {
                            echo '<tr class="highlight">'."\n";
                            echo "<td colspan=\"4\"><strong>".__('User', 'poll-dude')." ".number_format_i18n($k).": $pollip_user</strong></td>\n";
                            echo '</tr>';
                            $k++;
                        }
                        echo "<tr $style>\n";
                        echo "<td>{$pollip_answers[$pollip_aid]}</td>\n";
                        echo "<td>$pollip_ip</td>\n";
                        echo "<td>$pollip_host</td>\n";
                        echo "<td>$pollip_date</td>\n";
                        echo "</tr>\n";
                        $temp_pollip_user = $pollip_user;
                        $i++;
                        $j++;
                    }
                } else {
                    foreach($poll_ips as $poll_ip) {
                        $pollip_aid = (int) $poll_ip->pollip_aid;
                        //$pollip_user = apply_filters( 'wp_polls_log_secret_ballot', $poll_dude->utility->removeslashes( $poll_ip->pollip_user ) );
                        $pollip_user = $poll_dude->utility->removeslashes( $poll_ip->pollip_user );
                        $pollip_ip = $poll_ip->pollip_ip;
                        $pollip_host = $poll_ip->pollip_host;
                        $pollip_date = mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_ip->pollip_timestamp));
                        if($pollip_aid != $poll_last_aid) {
                            if($pollip_aid == 0) {
                                echo "<tr class=\"highlight\">\n<td colspan=\"4\"><strong>$pollip_answers[$pollip_aid]</strong></td>\n</tr>\n";
                            } else {
                                $polla_answer = ! empty( $pollip_answers[$pollip_aid] ) ? $pollip_answers[ $pollip_aid ] : $poll_answers_data[ $k-1 ]->polla_answers;
                                echo "<tr class=\"highlight\">\n<td colspan=\"4\"><strong>".__('Answer', 'poll-dude')." ".number_format_i18n($k).": " . $polla_answer . "</strong></td>\n</tr>\n";
                                $k++;
                            }
                            echo "<tr class=\"thead\">\n";
                            echo "<th>".__('No.', 'poll-dude')."</th>\n";
                            echo "<th>".__('User', 'poll-dude')."</th>\n";
                            echo "<th>".__('Hashed IP / Host', 'poll-dude')."</th>\n";
                            echo "<th>".__('Date', 'poll-dude')."</th>\n";
                            echo "</tr>\n";
                            $i = 1;
                        }
                        if($i%2 == 0) {
                            $style = '';
                        }  else {
                            $style = 'class="alternate"';
                        }
                        echo "<tr $style>\n";
                        echo "<td>".number_format_i18n($i)."</td>\n";
                        echo "<td>$pollip_user</td>\n";
                        echo "<td>$pollip_ip / $pollip_host</td>\n";
                        echo "<td>$pollip_date</td>\n";
                        echo "</tr>\n";
                        $poll_last_aid = $pollip_aid;
                        $i++;
                        $j++;
                    }
                }
                echo '</table>'."\n";
            }
        ?>
    </div>
</div>
<p>&nbsp;</p>

<!-- Delete Poll Logs -->
<div class="wrap">
    <h3><?php _e('Delete Poll Logs', 'poll-dude'); ?></h3>
    <br class="clear" />
    <div align="center" id="poll_logs">
        <?php if($poll_logs_count) { ?>
            <strong><?php _e('Are You Sure You Want To Delete Logs For This Poll Only?', 'poll-dude'); ?></strong><br /><br />
            <input type="checkbox" id="delete_logs_yes" name="delete_logs_yes" value="yes" />&nbsp;<label for="delete_logs_yes"><?php _e('Yes', 'poll-dude'); ?></label><br /><br />
            <input type="button" name="do" value="<?php _e('Delete Logs For This Poll Only', 'poll-dude'); ?>" class="button" onclick="pd_delete_one_poll_logs(<?php echo $poll_id; ?>, '<?php printf( esc_js( __( 'You are about to delete poll logs for this poll \'%s\' ONLY. This action is not reversible.', 'poll-dude' ) ), esc_js( esc_attr( $poll_question_text ) ) ); ?>', '<?php echo wp_create_nonce('poll-dude_delete-poll-logs'); ?>');" />
        <?php
            } else {
                _e('No poll logs available for this poll.', 'poll-dude');
            }
        ?>
    </div>
</div>
