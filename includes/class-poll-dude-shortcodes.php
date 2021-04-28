<?php
//namespace poll_dude;

class Poll_Dude_Shortcode {
	public function __construct()
    {
		add_shortcode('poll_dude', array($this, 'poll_dude_shortcode'));
		add_shortcode('page_polls', array($this, 'poll_dude_page_shortcode'));
		add_filter( 'wp_polls_template_voteheader_markup'   , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_votebody_markup'     , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_votefooter_markup'   , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_resultheader_markup' , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_resultbody_markup'   , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_resultbody2_markup'  , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_resultfooter_markup' , array($this, 'poll_template_vote_markup'), 10, 3);
		add_filter( 'wp_polls_template_resultfooter2_markup', array($this, 'poll_template_vote_markup'), 10, 3);
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
			return display_pollresult( $id );
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
				echo removeslashes(get_option('poll_template_disable'));
				return '';
			}

			return removeslashes(get_option('poll_template_disable'));
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
				echo display_pollresult($poll_id);
			} else {
				return display_pollresult($poll_id);
			}
		// Check Whether User Has Voted
		} else {
			$poll_active = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_active FROM $wpdb->pollsq WHERE pollq_id = %d", $poll_id ) );
			$poll_active = (int) $poll_active;
			$check_voted = $this->check_voted( $poll_id );
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
			if( $poll_close === 1 || (int) $check_voted > 0 || ( is_array( $check_voted ) && count( $check_voted ) > 0 ) ) {
				if($display) {
					echo display_pollresult($poll_id, $check_voted);
				} else {
					return display_pollresult($poll_id, $check_voted);
				}
			} elseif( $poll_close === 3 || ! $this->check_allowtovote() ) {
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

	### Function: Check Who Is Allow To Vote
	public function check_allowtovote() {
		global $user_ID;
		$user_ID = (int) $user_ID;
		$allow_to_vote = (int) get_option( 'poll_allowtovote' );
		switch($allow_to_vote) {
			// Guests Only
			case 0:
				if($user_ID > 0) {
					return false;
				}
				return true;
				break;
			// Registered Users Only
			case 1:
				if($user_ID === 0) {
					return false;
				}
				return true;
				break;
			// Registered Users And Guests
			case 2:
			default:
				return true;
		}
	}


	### Funcrion: Check Voted By Cookie Or IP
	public function check_voted($poll_id) {
		$poll_logging_method = (int) get_option( 'poll_logging_method' );
		switch($poll_logging_method) {
			// Do Not Log
			case 0:
				return 0;
				break;
			// Logged By Cookie
			case 1:
				return $this->check_voted_cookie($poll_id);
				break;
			// Logged By IP
			case 2:
				return $this->check_voted_ip($poll_id);
				break;
			// Logged By Cookie And IP
			case 3:
				$check_voted_cookie = $this->check_voted_cookie($poll_id);
				if(!empty($check_voted_cookie)) {
					return $check_voted_cookie;
				}
				return $this->check_voted_ip($poll_id);
				break;
			// Logged By Username
			case 4:
				return $this->check_voted_username($poll_id);
				break;
		}
	}


	### Function: Check Voted By Cookie
	public function check_voted_cookie( $poll_id ) {
		$get_voted_aids = 0;
		if ( ! empty( $_COOKIE[ 'voted_' . $poll_id ] ) ) {
			$get_voted_aids = explode( ',', $_COOKIE[ 'voted_' . $poll_id ] );
			$get_voted_aids = array_map( 'intval', array_map( 'sanitize_key', $get_voted_aids ) );
		}
		return $get_voted_aids;
	}


	### Function: Check Voted By IP
	public function check_voted_ip( $poll_id ) {
		global $wpdb;
		$log_expiry = (int) get_option( 'poll_cookielog_expiry' );
		$log_expiry_sql = '';
		if( $log_expiry > 0 ) {
			$log_expiry_sql = ' AND (' . current_time('timestamp') . '-(pollip_timestamp+0)) < ' . $log_expiry;
		}
		// Check IP From IP Logging Database
		$get_voted_aids = $wpdb->get_col( $wpdb->prepare( "SELECT pollip_aid FROM $wpdb->pollsip WHERE pollip_qid = %d AND (pollip_ip = %s OR pollip_ip = %s)", $poll_id, poll_get_ipaddress(), get_ipaddress() ) . $log_expiry_sql );
		if( $get_voted_aids ) {
			return $get_voted_aids;
		}

		return 0;
	}


	### Function: Check Voted By Username
	public function check_voted_username($poll_id) {
		global $wpdb, $user_ID;
		// Check IP If User Is Guest
		if ( ! is_user_logged_in() ) {
			return 1;
		}
		$pollsip_userid = (int) $user_ID;
		$log_expiry = (int) get_option( 'poll_cookielog_expiry' );
		$log_expiry_sql = '';
		if( $log_expiry > 0 ) {
			$log_expiry_sql = 'AND (' . current_time('timestamp') . '-(pollip_timestamp+0)) < ' . $log_expiry;
		}
		// Check User ID From IP Logging Database
		$get_voted_aids = $wpdb->get_col( $wpdb->prepare( "SELECT pollip_aid FROM $wpdb->pollsip WHERE pollip_qid = %d AND pollip_userid = %d", $poll_id, $pollsip_userid ) . $log_expiry_sql );
		if($get_voted_aids) {
			return $get_voted_aids;
		} else {
			return 0;
		}
	}

	### Function: Display Voting Form
	public function display_pollvote($poll_id, $display_loading = true) { 
		do_action('wp_polls_display_pollvote');
		global $wpdb;
		
		// Temp Poll Result
		$temp_pollvote = '';
		// Get Poll Question Data
		$poll_question = $wpdb->get_row( $wpdb->prepare( "SELECT pollq_id, pollq_question, pollq_totalvotes, pollq_timestamp, pollq_expiry, pollq_multiple, pollq_totalvoters FROM $wpdb->pollsq WHERE pollq_id = %d LIMIT 1", $poll_id ) );

		// Poll Question Variables
		$poll_question_text = wp_kses_post( removeslashes( $poll_question->pollq_question ) );
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
		$poll_multiple_ans = (int) $poll_question->pollq_multiple;

		
		$template_question = removeslashes(get_option('poll_template_voteheader'));
		
		$template_question = apply_filters( 'wp_polls_template_voteheader_markup', $template_question, $poll_question, array(
			'%POLL_QUESTION%' => $poll_question_text,
			'%POLL_ID%' => $poll_question_id,
			'%POLL_TOTALVOTES%' => $poll_question_totalvotes,
			'%POLL_TOTALVOTERS%' => $poll_question_totalvoters,
			'%POLL_START_DATE%' => $poll_start_date,
			'%POLL_END_DATE%' => $poll_end_date,
			'%POLL_MULTIPLE_ANS_MAX%' => $poll_multiple_ans > 0 ? $poll_multiple_ans : 1
		) );

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
			if($poll_multiple_ans > 0) {
				$temp_pollvote .= "\t\t<p style=\"display: none;\"><input type=\"hidden\" id=\"poll_multiple_ans_$poll_question_id\" name=\"poll_multiple_ans_$poll_question_id\" value=\"$poll_multiple_ans\" /></p>\n";
			}
			// Print Out Voting Form Header Template
			$temp_pollvote .= "\t\t$template_question\n";
			foreach ( $poll_answers as $poll_answer ) {
				// Poll Answer Variables
				$poll_answer_id = (int) $poll_answer->polla_aid;
				$poll_answer_text = wp_kses_post( removeslashes( $poll_answer->polla_answers ) );
				$poll_answer_votes = (int) $poll_answer->polla_votes;
				$poll_answer_percentage = $poll_question_totalvotes > 0 ? round( ( $poll_answer_votes / $poll_question_totalvotes ) * 100 ) : 0;
				$poll_multiple_answer_percentage = $poll_question_totalvoters > 0 ? round( ( $poll_answer_votes / $poll_question_totalvoters ) * 100 ) : 0;
				$template_answer = removeslashes( get_option( 'poll_template_votebody' ) );

				$template_answer = apply_filters( 'wp_polls_template_votebody_markup', $template_answer, $poll_answer, array(
					'%POLL_ID%' => $poll_question_id,
					'%POLL_ANSWER_ID%' => $poll_answer_id,
					'%POLL_ANSWER%' => $poll_answer_text,
					'%POLL_ANSWER_VOTES%' => number_format_i18n( $poll_answer_votes ),
					'%POLL_ANSWER_PERCENTAGE%' => $poll_answer_percentage,
					'%POLL_MULTIPLE_ANSWER_PERCENTAGE%' => $poll_multiple_answer_percentage,
					'%POLL_CHECKBOX_RADIO%' => $poll_multiple_ans > 0 ? 'checkbox' : 'radio'
				) );

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

			// Voting Form Footer Variables
			$template_footer = removeslashes(get_option('poll_template_votefooter'));

			$template_footer = apply_filters( 'wp_polls_template_votefooter_markup', $template_footer, $poll_question, array(
				'%POLL_ID%' => $poll_question_id,
				'%POLL_RESULT_URL%' => $poll_result_url,
				'%POLL_START_DATE%' => $poll_start_date,
				'%POLL_END_DATE%' => $poll_end_date,
				'%POLL_MULTIPLE_ANS_MAX%' => $poll_multiple_ans > 0 ? $poll_multiple_ans : 1
			) );

			// Print Out Voting Form Footer Template
			$temp_pollvote .= "\t\t$template_footer\n";
			$temp_pollvote .= "\t</form>\n";
			$temp_pollvote .= "</div>\n";
			
			if($display_loading) {
				$poll_ajax_style = get_option('poll_ajax_style');
				if((int) $poll_ajax_style['loading'] === 1) {
					$temp_pollvote .= "<div id=\"polls-$poll_question_id-loading\" class=\"wp-polls-loading\"><img src=\"".plugins_url('wp-polls/images/loading.gif')."\" width=\"16\" height=\"16\" alt=\"".__('Loading', 'wp-polls')." ...\" title=\"".__('Loading', 'wp-polls')." ...\" class=\"wp-polls-image\" />&nbsp;".__('Loading', 'wp-polls')." ...</div>\n";
				}
			}
			
		} else {
			$temp_pollvote .= removeslashes(get_option('poll_template_disable'));
		}
		
		// Return Poll Vote Template
		return $temp_pollvote;
	}


}

