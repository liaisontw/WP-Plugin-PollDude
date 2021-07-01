<?php
		
// Get Poll Question Data
$poll_question = ($wpdb->get_row( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters, pollq_recaptcha FROM $wpdb->polldude_q WHERE pollq_id = %d LIMIT 1", $poll_id ) ));

//$poll_question = $wpdb->get_results( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters, pollq_recaptcha FROM $wpdb->polldude_q WHERE pollq_id = %d LIMIT 1", $poll_id ), ARRAY_A);

// Poll Question Variables
var_dump($poll_question);
$poll_question_text = $poll_dude->utility->removeslashes($poll_question->pollq_question);
//$poll_question_text = wp_kses_post( $poll_dude->utility->removeslashes( $poll_question->pollq_question ) );
$poll_question_id = (int) $poll_question->pollq_id;
$poll_question_totalvotes = (int) $poll_question->pollq_totalvotes;
$poll_question_totalvoters = (int) $poll_question->pollq_totalvoters;
$poll_start_date = mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_question->pollq_timestamp));
$poll_expiry = trim($poll_question->pollq_expiry);
if(empty($poll_expiry)) {
    $poll_end_date  = __('No Expiry', 'poll-dude');
} else {
    $poll_end_date  = mysql2date(sprintf(__('%s @ %s', 'poll-dude'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_expiry));
}
if( (int)$poll_question->pollq_multiple ) {
    $poll_multiple_ans = (int)$poll_question->pollq_multiple;
    $ans_select = 'checkbox';
} else {
    $poll_multiple_ans = 1;
    $ans_select = 'radio';
}
//$poll_multiple_ans = (int) $poll_question->pollq_multiple > 0 ? $poll_question->pollq_multiple : 1;
$poll_recaptcha = $poll_question->pollq_recaptcha;
if($poll_recaptcha && $recaptcha){
    wp_add_inline_script('jquery', 'https://www.google.com/recaptcha/api.js');
}

// Get Poll Answers Data
$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_qid, polla_answers, polla_votes FROM $wpdb->polldude_a WHERE polla_qid = %d ORDER BY 'polla_aid' 'desc'", $poll_question_id ) );


if($poll_question && $poll_answers) {
?>
<div id="polls-<?php echo esc_attr($poll_question_id); ?>" >
<form id="polls_form_<?php echo esc_attr($poll_question_id); ?>" action="<?php echo sanitize_text_field( $_SERVER['SCRIPT_NAME'] ); ?>" method="post">
<p style="display: none;"><input type="hidden" id="poll_<?php echo esc_attr($poll_question_id); ?>_nonce" name="poll-dude-nonce" value="<?php wp_create_nonce('poll_'.$poll_question_id.'-nonce'); ?>" /></p>
<p style="display: none;"><input type="hidden" name="poll_id" value="<?php echo esc_attr($poll_question_id); ?>" /></p>
<p style="display: none;"><input type="hidden" id="poll_multiple_ans_<?php echo esc_attr($poll_question_id); ?>" name="poll_multiple_ans_<?php echo esc_attr($poll_question_id); ?>" value="<?php echo esc_attr($poll_multiple_ans); ?>" /></p>
<?php
if($poll_question->pollq_multiple > 0) {
    $temp_pollvote = "\t\t<p style=\"display: none;\"><input type=\"hidden\" id=\"poll_multiple_ans_$poll_question_id\" name=\"poll_multiple_ans_$poll_question_id\" value=\"$poll_multiple_ans\" /></p>\n";
    echo esc_html($temp_pollvote);
}
?>
<p style="text-align: center;"><strong><?php echo esc_attr($poll_question_text); ?></strong></p>
<div id="polls-<?php echo esc_attr($poll_question_id); ?>-ans" class="poll-dude-ans">"
<ul class="poll-dude-ul">
<?php
foreach ( $poll_answers as $poll_answer ) {
    // Poll Answer Variables
    $poll_answer_id = (int) $poll_answer->polla_aid;
    $poll_answer_text = wp_kses_post( $poll_dude->utility->removeslashes( $poll_answer->polla_answers ) );
    $poll_answer_votes = (int) $poll_answer->polla_votes;
    $poll_answer_percentage = $poll_question_totalvotes > 0 ? round( ( $poll_answer_votes / $poll_question_totalvotes ) * 100 ) : 0;
    $poll_multiple_answer_percentage = $poll_question_totalvoters > 0 ? round( ( $poll_answer_votes / $poll_question_totalvoters ) * 100 ) : 0;

    $template_answer = "<li><input type=\"$ans_select\" id=\"poll-answer-$poll_answer_id\" name=\"poll_$poll_question_id\" value=\"$poll_answer_id\" /><label for=\"poll-answer-$poll_answer_id\">$poll_answer_text</label></li>";
    echo esc_html($template_answer);
}

if($poll_recaptcha){         
    if($recaptcha){
        $template_footer = "</ul><p style=\"text-align: center;\"><input id=\"vote_recaptcha\" type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude')."   \" class=\"Buttons\" onclick=\"polldude_recaptcha($poll_question_id);\" disabled/></p>";
    }
}else{
    $template_footer = "</ul><p style=\"text-align: center;\"><input id=\"vote_no_recaptcha\" type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude')."   \" class=\"Buttons\" onclick=\"polldude_vote($poll_question_id);\" /></p>";
}
echo esc_html($template_footer);

if($recaptcha){
    $template_footer = "<p style=\"text-align: center;\"><a href=\"#ViewPollResults\" onclick=\"polldude_result($poll_question_id); return false;\" title=\"'.__('View Results Of This Poll', 'poll-dude').'\">".__('View Results', 'poll-dude')."</a></p></div>";
    echo esc_html($template_footer);
    if($poll_recaptcha){
        $template_footer = "<div class=\"g-recaptcha\" data-sitekey=\"".get_option('pd_recaptcha_sitekey')."\" data-callback=\"polldude_button_enable\"></div>";
        echo esc_html($template_footer);
    }
}
?>
</form>
</div>
<?php
}
?>