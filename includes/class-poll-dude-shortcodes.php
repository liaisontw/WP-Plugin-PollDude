<?php
/**
 *
 * This class defines all code necessary to run for the plugin's shortcode display.
 *
 * @link       https://github.com/liaisontw/poll-dude
 * @since      1.0.0
 * @package    poll-dude
 * @subpackage poll-dude/includes
 * @author     Liaison Chang
 */

class Poll_Dude_Shortcode {

	public function __construct($utility)
    {
		$this->utility = $utility;
		add_shortcode('poll_dude'                             , array($this, 'poll_dude_shortcode'));
		add_action(   'wp_ajax_poll-dude'                     , array($this, 'poll_dude_vote'));
		add_action(   'wp_ajax_nopriv_poll-dude'              , array($this, 'poll_dude_vote'));
    }

	public function removeslashes( $string ) {
		return $this->utility->removeslashes( $string );
	}

	public function is_voted($poll_id) {
		return $this->utility->is_voted($poll_id);
	}

	public function vote_allow() {
		return $this->utility->vote_allow();
	}

	public function poll_template_vote_markup( $template, $object, $variables ) {
		return str_replace( array_keys( $variables ), array_values( $variables ), $template ) ;
	}

	### Function: Short Code For Inserting Polls Into Posts
	public function poll_dude_shortcode( $atts ) {
		$attributes = shortcode_atts( array( 'id' => 0, 'type' => 'vote' ), $atts );
		
		$id = (int) $attributes['id'];

		// To maintain backward compatibility with [poll=1]. Props @tz-ua
		if( ! $id && isset( $atts[0] ) ) {
			$id = (int) trim( $atts[0], '="\'' );
		}

		if( $attributes['type'] === 'vote' ) {
			return $this->poll_query( $id, false, true );
		} elseif( $attributes['type'] === 'result' ) {
			return $this->show_vote_result( $id );
		}	
	}

	### Function: Poll Query
	public function poll_query($temp_poll_id = 0, $display = true, $recaptcha = true) {
		global $wpdb, $polls_loaded;
		// Poll Result Link
		if(isset($_GET['pollresult'])) {
			$pollresult_id = (int) $_GET['pollresult'];
		} else {
			$pollresult_id = 0;
		}
		$temp_poll_id = (int) $temp_poll_id;
		// Check Whether Poll Is Disabled
		if((int) get_option('pd_currentpoll') === -1) {

			return $this->removeslashes(get_option('poll_template_disable'));
		// Poll Is Enabled
		} else {
			// Hardcoded Poll ID Is Not Specified
			switch($temp_poll_id) {
				// Random Poll
				case -2:
					$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->polldude_q WHERE pollq_active = 1 ORDER BY RAND() LIMIT 1");
					break;
				// Latest Poll
				case 0:
					// Random Poll
					if((int) get_option('pd_currentpoll') === -2) {
						$random_poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->polldude_q WHERE pollq_active = 1 ORDER BY RAND() LIMIT 1");
						$poll_id = (int) $random_poll_id;
						if($pollresult_id > 0) {
							$poll_id = $pollresult_id;
						} elseif((int) $_POST['poll_id'] > 0) {
							$poll_id = (int) $_POST['poll_id'];
						}
					// Current Poll ID Is Not Specified
					} elseif((int) get_option('pd_currentpoll') === 0) {
						// Get Lastest Poll ID
						$poll_id = (int) get_option('pd_latestpoll');
					} else {
						// Get Current Poll ID
						$poll_id = (int) get_option('pd_currentpoll');
					}
					break;
				// Take Poll ID From Arguments
				default:
					$poll_id = $temp_poll_id;
			}
		}

		// Assign All Loaded Poll To $polls_loaded
		if(empty($polls_loaded)) {
			$polls_loaded = array();
		}
		if(!in_array($poll_id, $polls_loaded, true)) {
			$polls_loaded[] = $poll_id;
		}

		// User Click on View Results Link
		if($pollresult_id === $poll_id) {
			if($display) {
				echo wp_kses_post($this->show_vote_result($poll_id));
			} else {
				return $this->show_vote_result($poll_id);
			}
		// Check Whether User Has Voted
		} else {
			$poll_active = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_active FROM $wpdb->polldude_q WHERE pollq_id = %d", $poll_id ) );
			$poll_active = (int) $poll_active;
			$is_voted = $this->is_voted( $poll_id );
			$pd_close = 0;

			if( $poll_active === 0 ) {
				$pd_close = (int) get_option( 'pd_close' );
			} else {
				return $this->show_vote_form($poll_id, $display, $recaptcha, $poll_active);
			}

			if( $pd_close === 2 ) {
				return '';
			}
			if( $pd_close === 1 || (int) $is_voted > 0 || ( is_array( $is_voted ) && count( $is_voted ) > 0 ) ) {
				if($display) {
					echo wp_kses_post($this->show_vote_result($poll_id, $is_voted));
				} else {
					return $this->show_vote_result($poll_id, $is_voted);
				}
			} elseif( $pd_close === 3 || ! $this->vote_allow() ) {
				return $this->show_vote_form($poll_id, $display, $recaptcha, $poll_active);
			} 
		}
	}

	public function echo_or_aggregate($echo, $html){
		if($echo & ($html !== '')){
			echo $html;
		}else{
			return $html;
		}
	}
	
	
	### Function: Show Vote Form
	public function show_vote_form($poll_id, $display = true, $recaptcha = true, $poll_active) { 
		global $wpdb, $poll_dude;
		
		// Get Poll Question Data
		$poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters, pollq_recaptcha FROM $wpdb->polldude_q WHERE pollq_id = %d LIMIT 1", $poll_id ) );

		// Poll Question Variables
		$poll_question_text = wp_kses_post( $this->removeslashes( $poll_question->pollq_question ) );
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
			// Temp Poll Result
			$temp = '';
			// If There Is Poll Question With Answers
			// Display Poll Voting Form
			$temp_pollvote =  $this->echo_or_aggregate($display, $temp); $temp = "<div id=\"polls-".esc_attr($poll_question_id)."\" >\n";
			$temp_pollvote .= $this->echo_or_aggregate($display, $temp); $temp = "\t<form id=\"polls_form_".esc_attr($poll_question_id)."\" action=\"" . sanitize_text_field( $_SERVER['SCRIPT_NAME'] ) ."\" method=\"post\">\n";
			$temp_pollvote .= $this->echo_or_aggregate($display, $temp); $temp = "\t\t<p style=\"display: none;\"><input type=\"hidden\" id=\"poll_".esc_attr($poll_question_id)."_nonce\" name=\"poll-dude-nonce\" value=\"".wp_create_nonce('poll_'.$poll_question_id.'-nonce')."\" /></p>\n";
			$temp_pollvote .= $this->echo_or_aggregate($display, $temp); $temp = "\t\t<p style=\"display: none;\"><input type=\"hidden\" name=\"poll_id\" value=\"".esc_attr($poll_question_id)."\" /></p>\n";
			if($poll_question->pollq_multiple > 0) {
				$temp_pollvote .= $this->echo_or_aggregate($display, $temp); $temp = "\t\t<p style=\"display: none;\"><input type=\"hidden\" id=\"poll_multiple_ans_".esc_attr($poll_question_id)."\" name=\"poll_multiple_ans_".esc_attr($poll_question_id)."\" value=\"".esc_attr($poll_multiple_ans)."\" /></p>\n";
			}
			// Print Out Voting Form Header Template
			$temp = '';
			$template_question =  $this->echo_or_aggregate($display, $temp); $temp = "<p style=\"text-align: center;\"><strong>".esc_attr($poll_question_text)."</strong></p>";
			$template_question .= $this->echo_or_aggregate($display, $temp); $temp = "<div id=\"polls-".esc_attr($poll_question_id)."-ans\" class=\"poll-dude-ans\">";
			$template_question .= $this->echo_or_aggregate($display, $temp); $temp = "<ul class=\"poll-dude-ul\">";
			$temp_pollvote .= "\t\t$template_question\n";
			foreach ( $poll_answers as $poll_answer ) {
				// Poll Answer Variables
				$poll_answer_id = (int) $poll_answer->polla_aid;
				$poll_answer_text = wp_kses_post( $this->removeslashes( $poll_answer->polla_answers ) );
				$poll_answer_votes = (int) $poll_answer->polla_votes;
				$poll_answer_percentage = $poll_question_totalvotes > 0 ? round( ( $poll_answer_votes / $poll_question_totalvotes ) * 100 ) : 0;
				$poll_multiple_answer_percentage = $poll_question_totalvoters > 0 ? round( ( $poll_answer_votes / $poll_question_totalvoters ) * 100 ) : 0;

				$temp = "<li><input type=\"".esc_attr($ans_select)."\" id=\"poll-answer-".esc_attr($poll_answer_id)."\" name=\"poll_".esc_attr($poll_question_id)."\" value=\"".esc_attr($poll_answer_id)."\" /><label for=\"poll-answer-".esc_attr($poll_answer_id)."\">".esc_textarea($poll_answer_text)."</label></li>";
				$template_answer = $this->echo_or_aggregate($display, $temp); 

				// Print Out Voting Form Body Template
				$temp_pollvote .= "\t\t$template_answer\n";
			}
			
			if($poll_recaptcha){         
				if($recaptcha){
					$temp = "</ul><p style=\"text-align: center;\"><input id=\"vote_recaptcha\" type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude')."   \" class=\"Buttons\" onclick=\"polldude_recaptcha($poll_question_id);\" disabled/></p>";
					$template_footer =  $this->echo_or_aggregate($display, $temp); 
				}
			}else{
				if ( 1 === $poll_active ) {
					$temp = "</ul><p style=\"text-align: center;\"><input id=\"vote_no_recaptcha\" type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude')."   \" class=\"Buttons\" onclick=\"polldude_vote($poll_question_id);\" /></p>";
				} else {
					$temp = "</ul><p style=\"text-align: center;\"><input id=\"vote_no_recaptcha\" type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude')."   \" class=\"Buttons\" onclick=\"polldude_vote($poll_question_id);\" disabled/></p>";
				}
				$template_footer =  $this->echo_or_aggregate($display, $temp); 
			}
			
			if($recaptcha){
				$temp = "<p style=\"text-align: center;\"><a href=\"#ViewPollResults\" onclick=\"polldude_result($poll_question_id); return false;\" title=\"'.__('View Results Of This Poll', 'poll-dude').'\">".__('View Results', 'poll-dude')."</a></p></div>";
				$template_footer .= $this->echo_or_aggregate($display, $temp); 
				if($poll_recaptcha){
					$temp = "<div class=\"g-recaptcha\" data-sitekey=\"".get_option('pd_recaptcha_sitekey')."\" data-callback=\"polldude_button_enable\"></div>";
					$template_footer .=  $this->echo_or_aggregate($display, $temp); 
				}
			}

			// Print Out Voting Form Footer Template
			$temp_pollvote .= "\t\t$template_footer\n";
			$temp = "\t</form>\n";
			$temp_pollvote .= $this->echo_or_aggregate($display, $temp); $temp = "</div>\n";
			$temp_pollvote .= $this->echo_or_aggregate($display, $temp);			
		} 
		
		// Return Poll Vote Template
		return $temp_pollvote;
	}

	### Function: Show Vote Results
	public function show_vote_result( $poll_id, $user_voted = array(), $display_loading = true ) {
		global $wpdb;
		$poll_id = (int) $poll_id;

		// User Voted
		if( empty( $user_voted ) ) {
			$user_voted = array();
		}
		if ( is_array( $user_voted ) ) {
			$user_voted = array_map( 'intval', $user_voted );
		} else {
			$user_voted = array( (int) $user_voted );
		}

		// Temp Poll Result
		$temp_pollresult = '';
		// Most/Least Variables
		$poll_most_answer = '';
		$poll_most_votes = 0;
		$poll_most_percentage = 0;
		$poll_least_answer = '';
		$poll_least_votes = 0;
		$poll_least_percentage = 0;
		// Get Poll Question Data
		$poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_active, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters FROM $wpdb->polldude_q WHERE pollq_id = %d LIMIT 1", $poll_id ) );
		// No poll could be loaded from the database
		if ( ! $poll_question ) {
			return $this->removeslashes( get_option( 'poll_template_disable' ) );
		}
		// Poll Question Variables
		$poll_question_text = wp_kses_post( $this->removeslashes( $poll_question->pollq_question ) );
		$poll_question_id = (int) $poll_question->pollq_id;
		$poll_question_totalvotes = (int) $poll_question->pollq_totalvotes;
		$poll_question_totalvoters = (int) $poll_question->pollq_totalvoters;
		$poll_question_active = (int) $poll_question->pollq_active;
		$poll_start_date = mysql2date( sprintf( __( '%s @ %s', 'poll-dude' ), get_option( 'date_format' ), get_option( 'time_format' ) ), gmdate( 'Y-m-d H:i:s', $poll_question->pollq_timestamp ) );
		$poll_expiry = trim( $poll_question->pollq_expiry );
		if ( empty( $poll_expiry ) ) {
			$poll_end_date  = __( 'No Expiry', 'poll-dude' );
		} else {
			$poll_end_date  = mysql2date( sprintf( __( '%s @ %s', 'poll-dude' ), get_option( 'date_format' ), get_option( 'time_format' ) ), gmdate( 'Y-m-d H:i:s', $poll_expiry ) );
		}
		$poll_multiple_ans = (int) $poll_question->pollq_multiple > 0 ? $poll_question->pollq_multiple : 1;

		$template_question  = "<p style=\"text-align: center;\"><strong>$poll_question_text</strong></p>";
		$template_question .= "<div id=\"polls-$poll_question_id-ans\" class=\"poll-dude-ans\">";
		$template_question .= "<ul class=\"poll-dude-ul\">";

		// Get Poll Answers Data
		$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_qid, polla_answers, polla_votes, polla_colors FROM $wpdb->polldude_a WHERE polla_qid = %d ORDER BY 'polla_aid' 'desc'", $poll_question_id ) );
		//list( $order_by, $sort_order ) = _polls_get_ans_result_sort();
		//$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_answers, polla_votes FROM $wpdb->polldude_a WHERE polla_qid = %d ORDER BY $order_by $sort_order", $poll_question_id ) );
		// If There Is Poll Question With Answers
		if ( $poll_question && $poll_answers ) {
			// Store The Percentage Of The Poll
			$poll_answer_percentage_array = array();
			// Is The Poll Total Votes or Voters 0?
			$poll_totalvotes_zero = $poll_question_totalvotes <= 0;
			$poll_totalvoters_zero = $poll_question_totalvoters <= 0;
			// Print Out Result Header Template
			$temp_pollresult .= "<div id=\"polls-$poll_question_id\" class=\"poll-dude\">\n";
			$temp_pollresult .= "\t\t$template_question\n";
			foreach ( $poll_answers as $poll_answer ) {
				// Poll Answer Variables
				$poll_answer_id = (int) $poll_answer->polla_aid;
				$poll_answer_text = wp_kses_post( $this->removeslashes( $poll_answer->polla_answers ) );
				$poll_answer_votes = (int) $poll_answer->polla_votes;
				$poll_answer_color = $poll_answer->polla_colors;
				// Calculate Percentage And Image Bar Width
				$poll_answer_percentage = 0;
				$poll_multiple_answer_percentage = 0;
				$poll_answer_imagewidth = 1;
				if ( ! $poll_totalvotes_zero && ! $poll_totalvoters_zero && $poll_answer_votes > 0 ) {
					$poll_answer_percentage = round( ( $poll_answer_votes / $poll_question_totalvotes ) * 100 );
					$poll_multiple_answer_percentage = round( ( $poll_answer_votes / $poll_question_totalvoters ) * 100 );
					$poll_answer_imagewidth = round( $poll_answer_percentage );
					if ( $poll_answer_imagewidth === 100 ) {
						$poll_answer_imagewidth = 99;
					}
				}
				
				$template_answer = "";
				// Let User See What Options They Voted
				if ( in_array( $poll_answer_id, $user_voted, true ) ) {
					// Results Body Variables
					$template_answer .= "<li><strong><i>$poll_answer_text <small>($poll_answer_percentage %".", ".number_format_i18n( $poll_answer_votes )." ".__('Votes', 'poll-dude').")</small></i></strong><div class=\"poll-dude-pollbar\" style=\"background: $poll_answer_color; width: $poll_answer_imagewidth%;\" title=\"".__('You Have Voted For This Choice', 'poll-dude')." - ".htmlspecialchars( wp_strip_all_tags( $poll_answer_text ) )." ($poll_answer_percentage % | ".number_format_i18n( $poll_answer_votes ).__('Votes', 'poll-dude').")\"></div></li>";
				} else {
					// Results Body Variables
					$template_answer .= "<li>$poll_answer_text <small>($poll_answer_percentage %".", ".number_format_i18n( $poll_answer_votes )." ".__('Votes', 'poll-dude').")</small><div class=\"poll-dude-pollbar\"  style=\"background: $poll_answer_color; width: $poll_answer_imagewidth%;\" title=\" ".htmlspecialchars( wp_strip_all_tags( $poll_answer_text ) )." ($poll_answer_percentage % | ".number_format_i18n( $poll_answer_votes ).__('Votes', 'poll-dude').")\"></div></li>";
					
				}


				// Print Out Results Body Template
				$temp_pollresult .= "\t\t$template_answer\n";

				// Get Most Voted Data
				if ( $poll_answer_votes > $poll_most_votes ) {
					$poll_most_answer = $poll_answer_text;
					$poll_most_votes = $poll_answer_votes;
					$poll_most_percentage = $poll_answer_percentage;
				}
				// Get Least Voted Data
				if ( $poll_least_votes === 0 ) {
					$poll_least_votes = $poll_answer_votes;
				}
				if ( $poll_answer_votes <= $poll_least_votes ) {
					$poll_least_answer = $poll_answer_text;
					$poll_least_votes = $poll_answer_votes;
					$poll_least_percentage = $poll_answer_percentage;
				}
			}
			// Results Footer Variables
			if ( ! empty( $user_voted ) || $poll_question_active === 0 || ! $this->vote_allow() ) {
				$template_footer  = "</ul><p style=\"text-align: center;\">".__('Total Votes', 'poll-dude').": <strong>".number_format_i18n( $poll_question_totalvotes )."</strong></p></div>";
			}else{
				$template_footer  = "</ul><p style=\"text-align: center;\">".__('Total Votes', 'poll-dude').": <strong>".number_format_i18n( $poll_question_totalvotes )."</strong></p>";
				$template_footer .= "<p style=\"text-align: center;\"><a href=\"#VotePoll\" onclick=\"polldude_booth($poll_question_id); return false;\" title=\"".__('Vote For This Poll', 'poll-dude')."">"".__('Vote', 'poll-dude')."</a></p></div>";
			}

			// Print Out Results Footer Template
			$temp_pollresult .= "\t\t$template_footer\n";
			$temp_pollresult .= "\t\t<input type=\"hidden\" id=\"poll_{$poll_question_id}_nonce\" name=\"poll-dude-nonce\" value=\"".wp_create_nonce('poll_'.$poll_question_id.'-nonce')."\" />\n";
			$temp_pollresult .= "</div>\n";
			
		} else {
			$temp_pollresult .= $this->removeslashes( get_option ('poll_template_disable' ) );
		}
		// Return Poll Result
		return $temp_pollresult;
	}

	public function vote_polldude_process($poll_id, $poll_aid_array = [])
	{
		global $wpdb, $user_identity, $user_ID, $poll_dude;


		$polla_aids = $wpdb->get_col( $wpdb->prepare( "SELECT polla_aid FROM $wpdb->polldude_a WHERE polla_qid = %d", $poll_id ) );
		$is_real = count( array_intersect( $poll_aid_array, $polla_aids ) ) === count( $poll_aid_array );

		if( !$is_real ) {
			throw new InvalidArgumentException(sprintf(__('Invalid Answer to Poll ID #%s', 'poll-dude'), $poll_id));
		}

		if (!$this->vote_allow()) {
			throw new InvalidArgumentException(sprintf(__('User is not allowed to vote for Poll ID #%s', 'poll-dude'), $poll_id));
		}

		if (empty($poll_aid_array)) {
			throw new InvalidArgumentException(sprintf(__('No anwsers given for Poll ID #%s', 'poll-dude'), $poll_id));
		}

		if($poll_id === 0) {
			throw new InvalidArgumentException(sprintf(__('Invalid Poll ID. Poll ID #%s', 'poll-dude'), $poll_id));
		}

		$is_poll_open = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->polldude_q WHERE pollq_id = %d AND pollq_active = 1", $poll_id ) );

		if ($is_poll_open === 0) {
			throw new InvalidArgumentException(sprintf(__( 'Poll ID #%s is closed', 'poll-dude' ), $poll_id ));
		}

		//$check_voted = check_voted($poll_id);
		$is_voted = $this->is_voted($poll_id);
		if ( !empty( $is_voted ) ) {
			throw new InvalidArgumentException(sprintf(__('You Had Already Voted For This Poll. Poll ID #%s', 'poll-dude'), $poll_id));
		}

		if (!empty($user_identity)) {
			$pollip_user = $user_identity;
		} elseif ( ! empty( $_COOKIE['comment_author_' . COOKIEHASH] ) ) {
			$pollip_user = sanitize_text_field($_COOKIE['comment_author_' . COOKIEHASH]);
		} else {
			$pollip_user = __('Guest', 'poll-dude');
		}

		$pollip_userid = $user_ID;
		$pollip_ip = $poll_dude->utility->get_ipaddr();
		$pollip_host = $poll_dude->utility->get_hostname();
		$pollip_timestamp = current_time('timestamp');
		$poll_logging_method = (int) get_option('poll_logging_method');

		// Only Create Cookie If User Choose Logging Method 1 Or 3
		if ( $poll_logging_method === 1 || $poll_logging_method === 3 ) {
			$cookie_expiry = (int) get_option('pd_cookielog_expiry');
			if ($cookie_expiry === 0) {
				$cookie_expiry = YEAR_IN_SECONDS;
			}
			setcookie( 'voted_' . $poll_id, implode(',', $poll_aid_array ), $pollip_timestamp + $cookie_expiry, SITECOOKIEPATH );
		}

		$i = 0;
		foreach ($poll_aid_array as $polla_aid) {
			$update_polla_votes = $wpdb->query( "UPDATE $wpdb->polldude_a SET polla_votes = (polla_votes + 1) WHERE polla_qid = $poll_id AND polla_aid = $polla_aid" );
			if (!$update_polla_votes) {
				unset($poll_aid_array[$i]);
			}
			$i++;
		}

		$vote_q = $wpdb->query("UPDATE $wpdb->polldude_q SET pollq_totalvotes = (pollq_totalvotes+" . count( $poll_aid_array ) . "), pollq_totalvoters = (pollq_totalvoters + 1) WHERE pollq_id = $poll_id AND pollq_active = 1");
		if (!$vote_q) {
			throw new InvalidArgumentException(sprintf(__('Unable To Update Poll Total Votes And Poll Total Voters. Poll ID #%s', 'poll-dude'), $poll_id));
		}

		foreach ($poll_aid_array as $polla_aid) {
			// Log Ratings In DB If User Choose Logging Method 2, 3 or 4
			if ( $poll_logging_method > 1 ){
				$wpdb->insert(
					$wpdb->polldude_ip,
					array(
						'pollip_qid'       => $poll_id,
						'pollip_aid'       => $polla_aid,
						'pollip_ip'        => $pollip_ip,
						'pollip_host'      => $pollip_host,
						'pollip_timestamp' => $pollip_timestamp,
						'pollip_user'      => $pollip_user,
						'pollip_userid'    => $pollip_userid
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d'
					)
				);
			}
		}

		return $this->show_vote_result($poll_id, $poll_aid_array, false);
	}

	public function polldude_recaptcha() {
		if(isset($_POST['g-recaptcha-response'])){
			$captcha=$_POST['g-recaptcha-response'];
		}else{
			throw new InvalidArgumentException(sprintf(__('Please click <I am not a robot>.', 'poll-dude')));
		}
		if(isset($captcha)){
			
			$secretKey = get_option('pd_recaptcha_secretkey');
			$ip = $_SERVER['REMOTE_ADDR'];
			// post request to server
			
			$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($secretKey).'&response='.urlencode($captcha)."&remoteip=".urlencode($ip);
			
			$response = wp_remote_get($url);
			$body     = wp_remote_retrieve_body( $response );
			var_dump($body);
			$responseKeys = json_decode($body,true);

			// should return JSON with success as true
			if($responseKeys["success"]) {
				_e('Recaptcha verify passed.', 'poll-dude');
			} else {
				_e('Recaptcha verify failed.', 'poll-dude');
			}

			unset($_POST['g-recaptcha-response']);
		}
	}

	### Function: Vote Poll
	
	public function poll_dude_vote() {
		global $wpdb, $user_identity, $user_ID;
		global $poll_dude;

		if( isset( $_REQUEST['action'] ) && sanitize_key( $_REQUEST['action'] ) === 'poll-dude') {
			// Load Headers
			header('Content-Type: text/html; charset='.get_option('blog_charset').'');

			// Get Poll ID
			$poll_id = (isset($_REQUEST['poll_id']) ? (int) sanitize_key( $_REQUEST['poll_id'] ) : 0);

			// Ensure Poll ID Is Valid
			if($poll_id === 0) {
				_e('Invalid Poll ID', 'poll-dude');
				exit();
			}

			// Verify Referer
			if( ! check_ajax_referer( 'poll_'.$poll_id.'-nonce', 'poll_'.$poll_id.'_nonce', false ) ) {
				_e('Failed To Verify Referrer', 'poll-dude');
				exit();
			}

			// Which View
			switch( sanitize_key( $_REQUEST['view'] ) ) {
				case 'recaptcha':
					try {
						$this->polldude_recaptcha();
					} catch (Exception $e) {
						echo wp_kses_post($e->getMessage());
					}
					break;
				// Poll Vote
				case 'process':			
					try {
						$poll_aid_array = array_unique( array_map('intval', explode( ',', $_POST["poll_$poll_id"] ) ) );
						echo wp_kses_post($this->vote_polldude_process($poll_id, $poll_aid_array));
					} catch (Exception $e) {
						echo wp_kses_post($e->getMessage());
					}
					break;
				// Poll Result
				case 'result':					
					echo wp_kses_post($this->show_vote_result($poll_id, 0, false));
					break;
				// Poll Booth
				case 'booth':
					echo wp_kses_post($this->show_vote_form($poll_id, false));
					break;
			} 
		} 
		exit();
	}

}