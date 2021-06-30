
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
    $poll_answer_text = wp_kses_post( $this->removeslashes( $poll_answer->polla_answers ) );
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