<?php

class Poll_Dude_Shortcode {

	public function __construct($utility)
    {
		$this->utility = $utility;
		add_shortcode('poll_dude'                             , array($this, 'poll_dude_shortcode'));
		add_shortcode('page_polls'                            , array($this, 'poll_dude_page_shortcode'));
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

    ### Function: Short Code For Inserting Polls Archive Into Page
	public function poll_page_shortcode($atts) {
		return $this->polls_archive();
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
			return $this->get_poll( $id, false );
		} elseif( $attributes['type'] === 'result' ) {
			return $this->display_pollresult( $id );
		}	
	}

	### Function: Get Poll
	public function get_poll($temp_poll_id = 0, $display = true) {
		global $wpdb, $polls_loaded;
		// Poll Result Link
		if(isset($_GET['pollresult'])) {
			$pollresult_id = (int) $_GET['pollresult'];
		} else {
			$pollresult_id = 0;
		}
		$temp_poll_id = (int) $temp_poll_id;
		// Check Whether Poll Is Disabled
		if((int) get_option('poll_currentpoll') === -1) {
			if($display) {
				echo $this->removeslashes(get_option('poll_template_disable'));
				return '';
			}

			return $this->removeslashes(get_option('poll_template_disable'));
		// Poll Is Enabled
		} else {
			do_action('wp_polls_get_poll');
			// Hardcoded Poll ID Is Not Specified
			switch($temp_poll_id) {
				// Random Poll
				case -2:
					$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY RAND() LIMIT 1");
					break;
				// Latest Poll
				case 0:
					// Random Poll
					if((int) get_option('poll_currentpoll') === -2) {
						$random_poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY RAND() LIMIT 1");
						$poll_id = (int) $random_poll_id;
						if($pollresult_id > 0) {
							$poll_id = $pollresult_id;
						} elseif((int) $_POST['poll_id'] > 0) {
							$poll_id = (int) $_POST['poll_id'];
						}
					// Current Poll ID Is Not Specified
					} elseif((int) get_option('poll_currentpoll') === 0) {
						// Get Lastest Poll ID
						$poll_id = (int) get_option('poll_latestpoll');
					} else {
						// Get Current Poll ID
						$poll_id = (int) get_option('poll_currentpoll');
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
				echo $this->display_pollresult($poll_id);
			} else {
				return $this->display_pollresult($poll_id);
			}
		// Check Whether User Has Voted
		} else {
			$poll_active = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_active FROM $wpdb->pollsq WHERE pollq_id = %d", $poll_id ) );
			$poll_active = (int) $poll_active;
			$is_voted = $this->is_voted( $poll_id );
			$poll_close = 0;
			if( $poll_active === 0 ) {
				$poll_close = (int) get_option( 'poll_close' );
			}
			if( $poll_close === 2 ) {
				if( $display ) {
					echo '';
				} else {
					return '';
				}
			}
			if( $poll_close === 1 || (int) $is_voted > 0 || ( is_array( $is_voted ) && count( $is_voted ) > 0 ) ) {
				if($display) {
					echo $this->display_pollresult($poll_id, $is_voted);
				} else {
					return $this->display_pollresult($poll_id, $is_voted);
				}
			} elseif( $poll_close === 3 || ! $this->vote_allow() ) {
				$disable_poll_js = '<script type="text/javascript">jQuery("#polls_form_'.$poll_id.' :input").each(function (i){jQuery(this).attr("disabled","disabled")});</script>';
				if($display) {
					echo $this->display_pollvote($poll_id).$disable_poll_js;
				} else {
					return $this->display_pollvote($poll_id).$disable_poll_js;
				}
			} elseif( $poll_active === 1 ) {
				if($display) {
					echo $this->display_pollvote($poll_id);
				} else {
					return $this->display_pollvote($poll_id);
				}
			}
		}
	}

	
	### Function: Display Voting Form
	public function display_pollvote($poll_id, $display_loading = true) { 
		do_action('wp_polls_display_pollvote');
		global $wpdb, $poll_dude;
		
		// Temp Poll Result
		$temp_pollvote = '';
		// Get Poll Question Data
		$poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters, pollq_recaptcha FROM $wpdb->pollsq WHERE pollq_id = %d LIMIT 1", $poll_id ) );

		// Poll Question Variables
		$poll_question_text = wp_kses_post( $this->removeslashes( $poll_question->pollq_question ) );
		$poll_question_id = (int) $poll_question->pollq_id;
		$poll_question_totalvotes = (int) $poll_question->pollq_totalvotes;
		$poll_question_totalvoters = (int) $poll_question->pollq_totalvoters;
		$poll_start_date = mysql2date(sprintf(__('%s @ %s', 'wp-polls'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_question->pollq_timestamp));
		$poll_expiry = trim($poll_question->pollq_expiry);
		if(empty($poll_expiry)) {
			$poll_end_date  = __('No Expiry', 'wp-polls');
		} else {
			$poll_end_date  = mysql2date(sprintf(__('%s @ %s', 'wp-polls'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $poll_expiry));
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
		
		$template_question = "";
		$template_question .="<p style=\"text-align: center;\"><strong>$poll_question_text</strong></p>";
		$template_question .="<div id=\"polls-$poll_question_id-ans\" class=\"wp-polls-ans\">";
		$template_question .="<ul class=\"wp-polls-ul\">";


		// Get Poll Answers Data
		//list($order_by, $sort_order) = _polls_get_ans_sort();
		//$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_qid, polla_answers, polla_votes FROM $wpdb->pollsa WHERE polla_qid = %d ORDER BY $order_by $sort_order", $poll_question_id ) );
		$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_qid, polla_answers, polla_votes FROM $wpdb->pollsa WHERE polla_qid = %d ORDER BY 'polla_aid' 'desc'", $poll_question_id ) );
		// If There Is Poll Question With Answers
		
		if($poll_question && $poll_answers) {
			// Display Poll Voting Form
			$temp_pollvote .= "<div id=\"polls-$poll_question_id\" class=\"wp-polls\">\n";
			$temp_pollvote .= "\t<form id=\"polls_form_$poll_question_id\" class=\"wp-polls-form\" action=\"" . sanitize_text_field( $_SERVER['SCRIPT_NAME'] ) ."\" method=\"post\">\n";
			$temp_pollvote .= "\t\t<p style=\"display: none;\"><input type=\"hidden\" id=\"poll_{$poll_question_id}_nonce\" name=\"wp-polls-nonce\" value=\"".wp_create_nonce('poll_'.$poll_question_id.'-nonce')."\" /></p>\n";
			$temp_pollvote .= "\t\t<p style=\"display: none;\"><input type=\"hidden\" name=\"poll_id\" value=\"$poll_question_id\" /></p>\n";
			if($poll_question->pollq_multiple > 0) {
				$temp_pollvote .= "\t\t<p style=\"display: none;\"><input type=\"hidden\" id=\"poll_multiple_ans_$poll_question_id\" name=\"poll_multiple_ans_$poll_question_id\" value=\"$poll_multiple_ans\" /></p>\n";
			}
			// Print Out Voting Form Header Template
			$temp_pollvote .= "\t\t$template_question\n";
			foreach ( $poll_answers as $poll_answer ) {
				// Poll Answer Variables
				$poll_answer_id = (int) $poll_answer->polla_aid;
				$poll_answer_text = wp_kses_post( $this->removeslashes( $poll_answer->polla_answers ) );
				$poll_answer_votes = (int) $poll_answer->polla_votes;
				$poll_answer_percentage = $poll_question_totalvotes > 0 ? round( ( $poll_answer_votes / $poll_question_totalvotes ) * 100 ) : 0;
				$poll_multiple_answer_percentage = $poll_question_totalvoters > 0 ? round( ( $poll_answer_votes / $poll_question_totalvoters ) * 100 ) : 0;

				$template_answer = "<li><input type=\"$ans_select\" id=\"poll-answer-$poll_answer_id\" name=\"poll_$poll_question_id\" value=\"$poll_answer_id\" /> <label for=\"poll-answer-$poll_answer_id\">$poll_answer_text</label></li>";

				// Print Out Voting Form Body Template
				$temp_pollvote .= "\t\t$template_answer\n";
			}
			
			// Determine Poll Result URL
			$poll_result_url = esc_url_raw( $_SERVER['REQUEST_URI'] );
			$poll_result_url = preg_replace('/pollresult=(\d+)/i', 'pollresult='.$poll_question_id, $poll_result_url);
			if(isset($_GET['pollresult']) && (int) $_GET['pollresult'] === 0) {
				if(strpos($poll_result_url, '?') !== false) {
					$poll_result_url = "$poll_result_url&amp;pollresult=$poll_question_id";
				} else {
					$poll_result_url = "$poll_result_url?pollresult=$poll_question_id";
				}
			}

			
			if($poll_recaptcha){         
				$template_footer = "</ul><p style=\"text-align: center;\"><input type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude-domain')."   \" class=\"Buttons\" onclick=\"polldude_recaptcha($poll_question_id);\" /></p>";
			}else{
				$template_footer = "</ul><p style=\"text-align: center;\"><input type=\"button\" name=\"vote\" value=\"   ".__('Vote', 'poll-dude-domain')."   \" class=\"Buttons\" onclick=\"poll_vote($poll_question_id);\" /></p>";
			}
			
			$template_footer .= "<p style=\"text-align: center;\"><a href=\"#ViewPollResults\" onclick=\"poll_result($poll_question_id); return false;\" title=\"'.__('View Results Of This Poll', 'poll-dude-domain').'\">".__('View Results', 'poll-dude-domain')."</a></p></div>";
			if($poll_recaptcha){
				$template_footer .= "<div class=\"g-recaptcha\" data-sitekey=\"".get_option('pd_recaptcha_sitekey')."\"></div>";
			}

			// Print Out Voting Form Footer Template
			$temp_pollvote .= "\t\t$template_footer\n";
			$temp_pollvote .= "\t</form>\n";
			$temp_pollvote .= "</div>\n";
			if($poll_recaptcha){
				$temp_pollvote .= "<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
			}
			
			if($display_loading) {
				$poll_ajax_style = get_option('poll_ajax_style');
				if((int) $poll_ajax_style['loading'] === 1) {
					$temp_pollvote .= "<div id=\"polls-$poll_question_id-loading\" class=\"wp-polls-loading\"><img src=\"".plugins_url('wp-polls/images/loading.gif')."\" width=\"16\" height=\"16\" alt=\"".__('Loading', 'wp-polls')." ...\" title=\"".__('Loading', 'wp-polls')." ...\" class=\"wp-polls-image\" />&nbsp;".__('Loading', 'wp-polls')." ...</div>\n";
				}
			}
			
		} else {
			$temp_pollvote .= $this->removeslashes(get_option('poll_template_disable'));
		}
		
		// Return Poll Vote Template
		return $temp_pollvote;
	}

	### Function: Display Results Form
	public function display_pollresult( $poll_id, $user_voted = array(), $display_loading = true ) {
		global $wpdb;
		do_action( 'wp_polls_display_pollresult', $poll_id, $user_voted );
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
		$poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_active, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters FROM $wpdb->pollsq WHERE pollq_id = %d LIMIT 1", $poll_id ) );
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
		$poll_start_date = mysql2date( sprintf( __( '%s @ %s', 'wp-polls' ), get_option( 'date_format' ), get_option( 'time_format' ) ), gmdate( 'Y-m-d H:i:s', $poll_question->pollq_timestamp ) );
		$poll_expiry = trim( $poll_question->pollq_expiry );
		if ( empty( $poll_expiry ) ) {
			$poll_end_date  = __( 'No Expiry', 'wp-polls' );
		} else {
			$poll_end_date  = mysql2date( sprintf( __( '%s @ %s', 'wp-polls' ), get_option( 'date_format' ), get_option( 'time_format' ) ), gmdate( 'Y-m-d H:i:s', $poll_expiry ) );
		}
		$poll_multiple_ans = (int) $poll_question->pollq_multiple > 0 ? $poll_question->pollq_multiple : 1;

		$template_question  = "<p style=\"text-align: center;\"><strong>$poll_question_text</strong></p>";
		$template_question .= "<div id=\"polls-$poll_question_id-ans\" class=\"wp-polls-ans\">";
		$template_question .= "<ul class=\"wp-polls-ul\">";

		// Get Poll Answers Data
		$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_qid, polla_answers, polla_votes FROM $wpdb->pollsa WHERE polla_qid = %d ORDER BY 'polla_aid' 'desc'", $poll_question_id ) );
		//list( $order_by, $sort_order ) = _polls_get_ans_result_sort();
		//$poll_answers = $wpdb->get_results( $wpdb->prepare( "SELECT polla_aid, polla_answers, polla_votes FROM $wpdb->pollsa WHERE polla_qid = %d ORDER BY $order_by $sort_order", $poll_question_id ) );
		// If There Is Poll Question With Answers
		if ( $poll_question && $poll_answers ) {
			// Store The Percentage Of The Poll
			$poll_answer_percentage_array = array();
			// Is The Poll Total Votes or Voters 0?
			$poll_totalvotes_zero = $poll_question_totalvotes <= 0;
			$poll_totalvoters_zero = $poll_question_totalvoters <= 0;
			// Print Out Result Header Template
			$temp_pollresult .= "<div id=\"polls-$poll_question_id\" class=\"wp-polls\">\n";
			$temp_pollresult .= "\t\t$template_question\n";
			foreach ( $poll_answers as $poll_answer ) {
				// Poll Answer Variables
				$poll_answer_id = (int) $poll_answer->polla_aid;
				$poll_answer_text = wp_kses_post( $this->removeslashes( $poll_answer->polla_answers ) );
				$poll_answer_votes = (int) $poll_answer->polla_votes;
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
				// Make Sure That Total Percentage Is 100% By Adding A Buffer To The Last Poll Answer
				$round_percentage = apply_filters( 'wp_polls_round_percentage', false );
				if ( $round_percentage && $poll_question->pollq_multiple === 0 ) {
					$poll_answer_percentage_array[] = $poll_answer_percentage;
					if ( count( $poll_answer_percentage_array ) === count( $poll_answers ) ) {
						$percentage_error_buffer = 100 - array_sum( $poll_answer_percentage_array );
						$poll_answer_percentage += $percentage_error_buffer;
						if ( $poll_answer_percentage < 0 ) {
							$poll_answer_percentage = 0;
						}
					}
				}

				//$poll_answer_imagewidth = 35;

				// Let User See What Options They Voted
				if ( in_array( $poll_answer_id, $user_voted, true ) ) {
					// Results Body Variables
					//$template_answer = $this->removeslashes( get_option( 'poll_template_resultbody2' ) );
					$template_answer = "<li><strong><i>$poll_answer_text <small>($poll_answer_percentage %".", ".number_format_i18n( $poll_answer_votes )." ".__('Votes', 'poll-dude-domain').")</small></i></strong><div class=\"wp-polls-pollbar\" style=\"width: $poll_answer_imagewidth%;\" title=\"".__('You Have Voted For This Choice', 'poll-dude-domain')." - ".htmlspecialchars( wp_strip_all_tags( $poll_answer_text ) )." ($poll_answer_percentage % | ".number_format_i18n( $poll_answer_votes ).__('Votes', 'poll-dude-domain').")\"></div></li>";
				} else {
					// Results Body Variables
					//$template_answer = $this->removeslashes (get_option( 'poll_template_resultbody' ) );
					$template_answer = "<li>$poll_answer_text <small>($poll_answer_percentage %".", ".number_format_i18n( $poll_answer_votes )." ".__('Votes', 'poll-dude-domain').")</small><div class=\"wp-polls-pollbar\" style=\"width: $poll_answer_imagewidth%;\" title=\" ".htmlspecialchars( wp_strip_all_tags( $poll_answer_text ) )." ($poll_answer_percentage % | ".number_format_i18n( $poll_answer_votes ).__('Votes', 'poll-dude-domain').")\"></div></li>";
					
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
				$template_footer  = "</ul><p style=\"text-align: center;\">".__('Total Voters', 'poll-dude-domain').": <strong>".number_format_i18n( $poll_question_totalvoters )."</strong></p></div>";
			}else{
				$template_footer  = "</ul><p style=\"text-align: center;\">".__('Total Voters', 'poll-dude-domain').": <strong>".number_format_i18n( $poll_question_totalvoters )."</strong></p>";
				$template_footer .= "<p style=\"text-align: center;\"><a href=\"#VotePoll\" onclick=\"poll_booth($poll_question_id); return false;\" title=\"".__('Vote For This Poll', 'poll-dude-domain')."">"".__('Vote', 'poll-dude-domain')."</a></p></div>";
			}

			// Print Out Results Footer Template
			$temp_pollresult .= "\t\t$template_footer\n";
			$temp_pollresult .= "\t\t<input type=\"hidden\" id=\"poll_{$poll_question_id}_nonce\" name=\"wp-polls-nonce\" value=\"".wp_create_nonce('poll_'.$poll_question_id.'-nonce')."\" />\n";
			$temp_pollresult .= "</div>\n";
			
			if ( $display_loading ) { $poll_ajax_style = get_option( 'poll_ajax_style' );
				if ( (int) $poll_ajax_style['loading'] === 1 ) {
					$temp_pollresult .= "<div id=\"polls-$poll_question_id-loading\" class=\"wp-polls-loading\"><img src=\"".plugins_url('wp-polls/images/loading.gif')."\" width=\"16\" height=\"16\" alt=\"".__('Loading', 'wp-polls')." ...\" title=\"".__('Loading', 'wp-polls')." ...\" class=\"wp-polls-image\" />&nbsp;".__('Loading', 'wp-polls')." ...</div>\n";
				}
			}
			
		} else {
			$temp_pollresult .= $this->removeslashes( get_option ('poll_template_disable' ) );
		}
		// Return Poll Result
		return apply_filters( 'wp_polls_result_markup', $temp_pollresult );
	}

	public function vote_poll_process($poll_id, $poll_aid_array = [])
	{
		global $wpdb, $user_identity, $user_ID, $poll_dude;

		do_action('wp_polls_vote_poll');

		$polla_aids = $wpdb->get_col( $wpdb->prepare( "SELECT polla_aid FROM $wpdb->pollsa WHERE polla_qid = %d", $poll_id ) );
		$is_real = count( array_intersect( $poll_aid_array, $polla_aids ) ) === count( $poll_aid_array );

		if( !$is_real ) {
			throw new InvalidArgumentException(sprintf(__('Invalid Answer to Poll ID #%s', 'wp-polls'), $poll_id));
		}

		if (!$poll_dude->utility->vote_allow()) {
			throw new InvalidArgumentException(sprintf(__('User is not allowed to vote for Poll ID #%s', 'wp-polls'), $poll_id));
		}

		if (empty($poll_aid_array)) {
			throw new InvalidArgumentException(sprintf(__('No anwsers given for Poll ID #%s', 'wp-polls'), $poll_id));
		}

		if($poll_id === 0) {
			throw new InvalidArgumentException(sprintf(__('Invalid Poll ID. Poll ID #%s', 'wp-polls'), $poll_id));
		}

		$is_poll_open = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->pollsq WHERE pollq_id = %d AND pollq_active = 1", $poll_id ) );

		if ($is_poll_open === 0) {
			throw new InvalidArgumentException(sprintf(__( 'Poll ID #%s is closed', 'wp-polls' ), $poll_id ));
		}

		//$check_voted = check_voted($poll_id);
		$is_voted = $poll_dude->utility->is_voted($poll_id);
		if ( !empty( $is_voted ) ) {
			throw new InvalidArgumentException(sprintf(__('You Had Already Voted For This Poll. Poll ID #%s', 'wp-polls'), $poll_id));
		}

		if (!empty($user_identity)) {
			$pollip_user = $user_identity;
		} elseif ( ! empty( $_COOKIE['comment_author_' . COOKIEHASH] ) ) {
			$pollip_user = $_COOKIE['comment_author_' . COOKIEHASH];
		} else {
			$pollip_user = __('Guest', 'wp-polls');
		}

		$pollip_user = sanitize_text_field( $pollip_user );
		$pollip_userid = $user_ID;
		$pollip_ip = $poll_dude->utility->get_ipaddr();
		$pollip_host = $poll_dude->utility->get_hostname();
		$pollip_timestamp = current_time('timestamp');
		$poll_logging_method = (int) get_option('poll_logging_method');

		// Only Create Cookie If User Choose Logging Method 1 Or 3
		if ( $poll_logging_method === 1 || $poll_logging_method === 3 ) {
			$cookie_expiry = (int) get_option('poll_cookielog_expiry');
			if ($cookie_expiry === 0) {
				$cookie_expiry = YEAR_IN_SECONDS;
			}
			setcookie( 'voted_' . $poll_id, implode(',', $poll_aid_array ), $pollip_timestamp + $cookie_expiry, apply_filters( 'wp_polls_cookiepath', SITECOOKIEPATH ) );
		}

		$i = 0;
		foreach ($poll_aid_array as $polla_aid) {
			$update_polla_votes = $wpdb->query( "UPDATE $wpdb->pollsa SET polla_votes = (polla_votes + 1) WHERE polla_qid = $poll_id AND polla_aid = $polla_aid" );
			if (!$update_polla_votes) {
				unset($poll_aid_array[$i]);
			}
			$i++;
		}

		$vote_q = $wpdb->query("UPDATE $wpdb->pollsq SET pollq_totalvotes = (pollq_totalvotes+" . count( $poll_aid_array ) . "), pollq_totalvoters = (pollq_totalvoters + 1) WHERE pollq_id = $poll_id AND pollq_active = 1");
		if (!$vote_q) {
			throw new InvalidArgumentException(sprintf(__('Unable To Update Poll Total Votes And Poll Total Voters. Poll ID #%s', 'wp-polls'), $poll_id));
		}

		foreach ($poll_aid_array as $polla_aid) {
			// Log Ratings In DB If User Choose Logging Method 2, 3 or 4
			if ( $poll_logging_method > 1 ){
				$wpdb->insert(
					$wpdb->pollsip,
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
		do_action( 'wp_polls_vote_poll_success' );

		return $this->display_pollresult($poll_id, $poll_aid_array, false);
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
			$response = file_get_contents($url);
			$responseKeys = json_decode($response,true);
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
				_e('Invalid Poll ID', 'wp-polls');
				exit();
			}

			// Verify Referer
			if( ! check_ajax_referer( 'poll_'.$poll_id.'-nonce', 'poll_'.$poll_id.'_nonce', false ) ) {
				_e('Failed To Verify Referrer', 'wp-polls');
				exit();
			}

			// Which View
			switch( sanitize_key( $_REQUEST['view'] ) ) {
				case 'recaptcha':
					try {
						$this->polldude_recaptcha();
					} catch (Exception $e) {
						echo $e->getMessage();
					}
					break;
				// Poll Vote
				case 'process':			
					try {
						$poll_aid_array = array_unique( array_map('intval', array_map('sanitize_key', explode( ',', $_POST["poll_$poll_id"] ) ) ) );
						echo $this->vote_poll_process($poll_id, $poll_aid_array);
					} catch (Exception $e) {
						echo $e->getMessage();
					}
					break;
				// Poll Result
				case 'result':					
					echo $this->display_pollresult($poll_id, 0, false);
					break;
				// Poll Booth Aka Poll Voting Form
				case 'booth':
					echo $this->display_pollvote($poll_id, false);
					break;
			} 
		} 
		exit();
	}

}

