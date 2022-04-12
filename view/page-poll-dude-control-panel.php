<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_polls')) {
    die('Access Denied');
}

### Variables Variables Variables
global $poll_dude;
$base_name = plugin_basename( __FILE__ );
$base_page = 'admin.php?page='.$base_name;
$option_page = 'admin.php?page='.$poll_dude->get_plugin_name().'/view/page-poll-dude-options.php';
$current_page = 'admin.php?page='.$poll_dude->get_plugin_name().'/view/'.basename(__FILE__);
$mode       = ( isset( $_GET['mode'] ) ? sanitize_key( trim( $_GET['mode'] ) ) : '' );
$poll_id    = ( isset( $_GET['id'] ) ? (int) sanitize_key( $_GET['id'] ) : 0 );
$poll_aid   = ( isset( $_GET['aid'] ) ? (int) sanitize_key( $_GET['aid'] ) : 0 );
$text = '';




### Form Processing
if(!empty($_POST['do'])) {
    // Decide What To Do
    switch($_POST['do']) {
        // Edit Poll
        case __('Edit Poll', 'poll-dude'):
            check_admin_referer( 'polldude_edit-poll' );

            $text = $poll_dude->admin->poll_config('edit', $base_name);
            break;
    }
}

if (isset($_POST['bulk_delete'])) {
    global $poll_dude, $wpdb;

    check_admin_referer( 'polldude_bulk-delete' );
    for($i=0; $i<count($_POST['pollq']); $i++){
        $pollq_id = (int) sanitize_key($_POST['pollq'][$i]);
        
        $pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->polldude_q WHERE pollq_id = %d", $pollq_id ) );
        $poll_question_text = wp_kses_post( $poll_dude->utility->removeslashes($pollq_question));        
        $delete_poll_question = $wpdb->delete( $wpdb->polldude_q, array( 'pollq_id' => $pollq_id ), array( '%d' ) );
        $delete_poll_answers =  $wpdb->delete( $wpdb->polldude_a, array( 'polla_qid' => $pollq_id ), array( '%d' ) );
        $delete_poll_ip =	   $wpdb->delete( $wpdb->polldude_ip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
        $poll_option_lastestpoll = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'pd_latestpoll'");        
        
        $error = false;
        if(!$delete_poll_question) {
            echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll \'%s\' Question', 'poll-dude'), wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
            $error = true;
        }
        if(!$error) {
            echo '<p style="color: green;">'.sprintf(__('Poll \'%d\' \'%s\' Deleted Successfully', 'poll-dude'), $pollq_id, wp_kses_post( $poll_dude->utility->removeslashes( $pollq_question ) ) ).'</p>';
        }
                
        update_option( 'pd_latestpoll', $poll_dude->utility->latest_poll() );
    }

}

### Determines Which Mode It Is
switch($mode) {
    case 'add':
        require_once('page-poll-dude-add-form.php');
        break;
    // Edit A Poll
    case 'edit':
        $last_col_align = is_rtl() ? 'right' : 'left';
        $poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_question, pollq_timestamp, pollq_totalvotes, pollq_active, pollq_expiry, pollq_multiple, pollq_totalvoters, pollq_recaptcha FROM $wpdb->polldude_q WHERE pollq_id = %d", $poll_id ) );
        $poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_answers, polla_votes, polla_colors FROM $wpdb->polldude_a WHERE polla_qid = %d ORDER BY polla_aid ASC", $poll_id ) );
        $poll_noquestion = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(polla_aid) FROM $wpdb->polldude_a WHERE polla_qid = %d", $poll_id ) );
        $poll_question_text = $poll_dude->utility->removeslashes($poll_question->pollq_question);
        $poll_totalvotes = (int) $poll_question->pollq_totalvotes;
        $poll_timestamp = $poll_question->pollq_timestamp;
        $poll_active = (int) $poll_question->pollq_active;
        $poll_expiry = trim($poll_question->pollq_expiry);
        $poll_multiple = (int) $poll_question->pollq_multiple;
        $poll_totalvoters = (int) $poll_question->pollq_totalvoters;
        $poll_recaptcha = (int) $poll_question->pollq_recaptcha;
        
        require_once('page-poll-dude-poll-profile.php');
        break;
    case 'logs':
        $pollip_answers = array();
        $poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_question, pollq_timestamp, pollq_totalvotes, pollq_active, pollq_expiry, pollq_multiple, pollq_totalvoters, pollq_recaptcha FROM $wpdb->polldude_q WHERE pollq_id = %d", $poll_id ) );
        $poll_question_text = $poll_dude->utility->removeslashes($poll_question->pollq_question);
        $poll_totalvoters = (int) $poll_question->pollq_totalvoters;
        $poll_multiple = (int) $poll_question->pollq_multiple;
        $poll_registered = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(pollip_userid) FROM $wpdb->polldude_ip WHERE pollip_qid = %d AND pollip_userid > 0", $poll_id ) );
        $poll_comments = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(pollip_user) FROM $wpdb->polldude_ip WHERE pollip_qid = %d AND pollip_user != %s AND pollip_userid = 0", $poll_id, __( 'Guest', 'poll-dude' ) ) );
        $poll_guest = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(pollip_user) FROM $wpdb->polldude_ip WHERE pollip_qid = %d AND pollip_user = %s", $poll_id, __( 'Guest', 'poll-dude' ) ) );
        $poll_totalrecorded = ( $poll_registered + $poll_comments + $poll_guest );
        $poll_totalvotes = (int) $poll_question->pollq_totalvotes;
        list( $order_by, $sort_order ) = $poll_dude->utility->get_ans_sorted();
        $poll_answers_data = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_answers FROM $wpdb->polldude_a WHERE polla_qid = %d ORDER BY $order_by $sort_order", $poll_id ) );
        $poll_voters = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT pollip_user FROM $wpdb->polldude_ip WHERE pollip_qid = %d AND pollip_user != %s ORDER BY pollip_user ASC", $poll_id, __( 'Guest', 'poll-dude' ) ) );
        $poll_logs_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(pollip_id) FROM $wpdb->polldude_ip WHERE pollip_qid = %d", $poll_id ) );

        require_once('page-poll-dude-logs.php');
        break;
    // Main Page
    default:
        
        $polls = $wpdb->get_results( "SELECT * FROM $wpdb->polldude_q  ORDER BY pollq_timestamp DESC" );
        $total_ans =  $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->polldude_a" );
        $total_votes = 0;
        $total_voters = 0;
?>
        <script type="text/javascript">
            
        </script>
        <!-- Last Action -->
        <div id="message" class="updated" style="display: none;">
        
        </div>
        <!-- Manage Polls -->
        <div class="wrap">
            <h2><?php _e('Control Panel', 'poll-dude'); ?></h2>
            <br style="clear" />
            <form action="" method="post">
            <table class="widefat">
                <thead>
                    <tr>
                        <th></th>
                        <th><input id="delete_all" name="delete_all" type="checkbox" value="delete_all" onclick="pd_checkall_top();" style="width:10px; height:15px;" ></th>
                        <th></th>
                        <th colspan="2"><?php 
                            wp_nonce_field( 'polldude_bulk-delete' );
                            echo "<input class=\"button-secondary\" name=\"bulk_delete\" type=\"submit\" value=\"".__('Bulk Delete', 'poll-dude')." \" />\n";
                        ?></th>
                        <th colspan="3"><?php
                        echo "<a href=\"$option_page\" class=\"button-secondary\">".__('Set reCaptcha Key', 'poll-dude')."</a>\n";
                        ?></th>
                        <th colspan="2"><?php
                        echo "<a href=\"$base_page&amp;mode=add\" class=\"button-secondary\">".__('Add New Poll', 'poll-dude')."</a>\n";
                        ?></th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><?php _e('ID', 'poll-dude'); ?></th>
                        <th><?php _e('Question', 'poll-dude'); ?></th>
                        <th><?php _e('Total Voters', 'poll-dude'); ?></th>
                        <th><?php _e('reCaptcha', 'poll-dude'); ?></th>
                        <th><?php _e('Start Date/Time', 'poll-dude'); ?></th>
                        <th><?php _e('End Date/Time', 'poll-dude'); ?></th>
                        <th><?php _e('Status', 'poll-dude'); ?></th>
                        <th colspan="2"><?php _e('Action', 'poll-dude'); ?></th>
                    </tr>
                </thead>
                <tbody id="manage_polls">
                    <?php
                        if($polls) {
                            $i = 0;
                            $current_poll = (int) get_option('pd_currentpoll');
                            $latest_poll = (int) get_option('pd_latestpoll');
                            foreach($polls as $poll) {
                                $poll_id = (int) $poll->pollq_id;
                                $poll_question = $poll_dude->utility->removeslashes($poll->pollq_question);
                                $poll_date = mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll->pollq_timestamp));
                                $poll_totalvotes = (int) $poll->pollq_totalvotes;
                                $poll_totalvoters = (int) $poll->pollq_totalvoters;
                                $poll_active = (int) $poll->pollq_active;
                                $poll_expiry = trim($poll->pollq_expiry);
                                if(empty($poll_expiry)) {
                                    $poll_expiry_text  = __('No Expiry', 'poll-dude');
                                } else {
                                    $poll_expiry_text = mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_expiry));
                                }
                                $poll_recaptcha = $poll->pollq_recaptcha;
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
                                echo "<td><a href=\"#DeletePoll\" onclick=\"pd_delete_poll($poll_id, '".sprintf(esc_js(__('You are about to delete this poll, \'%s\'.', 'poll-dude')), esc_js($poll_question))."', '".wp_create_nonce('polldude_delete-poll')."');\" class=\"button\">".__('Delete', 'poll-dude')."</a></td>\n";
                                echo "<td><input id=\"cb-select-$poll_id\" type=\"checkbox\" name=\"pollq[]\" value=\"$poll_id\"  style=\"width:10px; height:15px;\"></td>\n";
                                echo '<td><strong>'.number_format_i18n($poll_id).'</strong></td>'."\n";
                                echo '<td>';
                                echo wp_kses_post( $poll_question )."</td>\n";
                                echo '<td>'.number_format_i18n($poll_totalvoters)."</td>\n";
                                ($poll_recaptcha)? $recaptcha_status = 'Enable' : $recaptcha_status = 'Disable';
                                echo "<td>$recaptcha_status</td>\n";
                                echo "<td>$poll_date</td>\n";
                                echo "<td>$poll_expiry_text</td>\n";
                                echo '<td>';
                                if($poll_active === 1) {
                                    _e('Open', 'poll-dude');
                                } elseif($poll_active === -1) {
                                    _e('Future', 'poll-dude');
                                } else {
                                    _e('Closed', 'poll-dude');
                                }
                                echo "</td>\n";
                                echo "<td><select id=\"selectBox\" name=\"forma\" onchange=\"pd_select_action($poll_id); \">";
                                echo "<option value=\"#\">".__('-Select-', 'poll-dude')."</option>\n";
                                echo "<option value=\"$base_page&amp;mode=edit&amp;id=$poll_id\">".__('Edit', 'poll-dude')."</option>";
                                echo "<option value=\"shortcode\" class=\"button\">".__('Shortcode', 'poll-dude')."</option>\n";
                                echo "<option value=\"$base_page&amp;mode=logs&amp;id=$poll_id\">".__('Logs', 'poll-dude')."</option>";
                                echo "</select></td>";
                                echo '</tr>';
                                $i++;
                                $total_votes+= $poll_totalvotes;
                                $total_voters+= $poll_totalvoters;

                            }
                        } else {
                            echo '<tr><td colspan="9" align="center"><strong>'.__('No Polls Found', 'poll-dude').'</strong></td></tr>';
                        }
                    ?>
                    <tr>
                        <th></th>
                        <th><input id="delete_all2" name="delete_all2" type="checkbox" value="delete_all2" onclick="pd_checkall_bottom();" style="width:10px; height:15px;" ></th>
                        <th></th>
                        <th><?php 
                            wp_nonce_field( 'polldude_bulk-delete' );
                            echo "<input class=\"button-secondary\" name=\"bulk_delete\" type=\"submit\" value=\"".__('Bulk Delete', 'poll-dude')." \" />\n";
                        ?></th>
                    </tr>
                </tbody>
            </form>         
        </div>
        <p>&nbsp;</p>
<?php
} 
