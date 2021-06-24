pdPublicL10n.show_loading = parseInt(pdPublicL10n.show_loading);
pdPublicL10n.show_fading = parseInt(pdPublicL10n.show_fading);

function polldude_button_enable() {
	jQuery(document).ready(function (a) {
		a("form").each(function () {
			a(this).find(':input[type="button"]').prop('disabled', false);
		});
	});
}

function polldude_recaptcha(current_poll_id) {
	jQuery(document).ready(function ($) {
		poll_nonce = $('#poll_' + current_poll_id + '_nonce').val();
		$.ajax({
			type: 'POST',
			url: pdPublicL10n.ajax_url,
			data: 'action=poll-dude&view=recaptcha&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce + "&g-recaptcha-response=" + grecaptcha.getResponse(),
			cache: false,
			success: function (data) {
				//alert(data);
				polldude_vote(current_poll_id);
			}
		});
	});
}

// When User Vote For Poll
function polldude_vote(current_poll_id) {
	jQuery(document).ready(function ($) {
		poll_answer_id = '';
		poll_multiple_ans = 0;
		poll_multiple_ans_count = 0;
		if ($('#poll_multiple_ans_' + current_poll_id).length) {
			poll_multiple_ans = parseInt($('#poll_multiple_ans_' + current_poll_id).val());
		}
		$('#polls_form_' + current_poll_id + ' input:checkbox, #polls_form_' + current_poll_id + ' input:radio, #polls_form_' + current_poll_id + ' option').each(function (i) {
			if ($(this).is(':checked') || $(this).is(':selected')) {
				if (poll_multiple_ans > 0) {
					poll_answer_id = $(this).val() + ',' + poll_answer_id;
					poll_multiple_ans_count++;
				} else {
					poll_answer_id = parseInt($(this).val());
				}
			}
		});
		if (poll_multiple_ans > 0) {
			if (poll_multiple_ans_count > 0 && poll_multiple_ans_count <= poll_multiple_ans) {
				poll_answer_id = poll_answer_id.substring(0, (poll_answer_id.length - 1));
				polldude_process(current_poll_id, poll_answer_id);
			} else if (poll_multiple_ans_count == 0) {
				alert(pdPublicL10n.text_valid);
			} else {
				alert(pdPublicL10n.text_multiple + ' ' + poll_multiple_ans);
			}
		} else {
			if (poll_answer_id > 0) {
				polldude_process(current_poll_id, poll_answer_id);
			} else {
				alert(pdPublicL10n.text_valid);
			}
		}
	});
}

// Process Poll (User Click "Vote" Button)
function polldude_process(current_poll_id, poll_answer_id) {
	jQuery(document).ready(function ($) {
		poll_nonce = $('#poll_' + current_poll_id + '_nonce').val();
		if (pdPublicL10n.show_fading) {
			$('#polls-' + current_poll_id).fadeTo('def', 0);
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').show();
			}
			$.ajax({ type: 'POST', xhrFields: { withCredentials: true }, url: pdPublicL10n.ajax_url, data: 'action=poll-dude&view=process&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '=' + poll_answer_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce, cache: false, 
			         success: polldude_process_success(current_poll_id)
					});
		} else {
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').show();
			}
			$.ajax({ type: 'POST', xhrFields: { withCredentials: true }, url: pdPublicL10n.ajax_url, data: 'action=poll-dude&view=process&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '=' + poll_answer_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce, cache: false, 
					success: polldude_process_success(current_poll_id)
				});
		}
	});
}

// Poll's Result (User Click "View Results" Link)
function polldude_result(current_poll_id) {
	jQuery(document).ready(function ($) {
		poll_nonce = $('#poll_' + current_poll_id + '_nonce').val();
		if (pdPublicL10n.show_fading) {
			$('#polls-' + current_poll_id).fadeTo('def', 0);
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').show();
			}
			$.ajax({ type: 'POST', xhrFields: { withCredentials: true }, url: pdPublicL10n.ajax_url, data: 'action=poll-dude&view=result&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce, cache: false, 
					success: polldude_process_success(current_poll_id)
				});
		} else {
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').show();
			}
			$.ajax({ type: 'POST', xhrFields: { withCredentials: true }, url: pdPublicL10n.ajax_url, data: 'action=poll-dude&view=result&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce, cache: false, 
					success: polldude_process_success(current_poll_id)
				});
		}
	});
}

// Poll's Voting Booth  (User Click "Vote" Link)
function polldude_booth(current_poll_id) {
	jQuery(document).ready(function ($) {
		poll_nonce = $('#poll_' + current_poll_id + '_nonce').val();
		if (pdPublicL10n.show_fading) {
			$('#polls-' + current_poll_id).fadeTo('def', 0);
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').show();
			}
			$.ajax({ type: 'POST', xhrFields: { withCredentials: true }, url: pdPublicL10n.ajax_url, data: 'action=poll-dude&view=booth&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce, cache: false, 
					success: polldude_process_success(current_poll_id)
					});
		} else {
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').show();
			}
			$.ajax({ type: 'POST', xhrFields: { withCredentials: true }, url: pdPublicL10n.ajax_url, data: 'action=poll-dude&view=booth&poll_id=' + current_poll_id + '&poll_' + current_poll_id + '_nonce=' + poll_nonce, cache: false, 
					success: polldude_process_success(current_poll_id)
				 });
		}
	});
}

// Poll Process Successfully
function polldude_process_success(current_poll_id) {
	return function (data) {
		jQuery(document).ready(function ($) {
			$('#polls-' + current_poll_id).replaceWith(data);
			if (pdPublicL10n.show_loading) {
				$('#polls-' + current_poll_id + '-loading').hide();
			}
			if (pdPublicL10n.show_fading) {
				$('#polls-' + current_poll_id).fadeTo('def', 1);
			}
		});
	}
}

