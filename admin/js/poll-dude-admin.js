
var global_poll_id = 0, global_poll_aid = 0, global_poll_aid_votes = 0, count_poll_answer_new = 0, count_poll_answer = 3; 
function pd_open_poll(a, c, d) { 
	if (open_poll_confirm = confirm(c)) 
	global_poll_id = a, 
	jQuery(document).ready(function (b) { 
		b.ajax({ type: "POST", 
				 url: pdAdminL10n.admin_ajax_url, 
				 data: "do=" + pdAdminL10n.text_open_poll + "&pollq_id=" + a + "&action=poll-dude-control&_ajax_nonce=" + d, 
				 cache: !1, 
				 success: function (a) { 
					 b("#message").html(a); 
					 b("#message").show(); 
					 b("#open_poll").hide(); 
					 b("#close_poll").show() 
	} }) }) 
}
function pd_close_poll(a, c, d) { 
	if (close_poll_confirm = confirm(c)) 
	global_poll_id = a, 
	jQuery(document).ready(function (b) { 
		b.ajax({ type: "POST", 
				 url: pdAdminL10n.admin_ajax_url, 
				 data: "do=" + pdAdminL10n.text_close_poll + "&pollq_id=" + a + "&action=poll-dude-control&_ajax_nonce=" + d, 
				 cache: !1, 
				 success: function (a) { 
					 b("#message").html(a); 
					 b("#message").show(); 
					 b("#open_poll").show(); 
					 b("#close_poll").hide() 
				} }) }) 
}
function pd_reorder_answer() { jQuery(document).ready(function (a) { var c = a("#pollq_multiple"), d = c.val(), b = a("> option", c).size(); c.empty(); a("#poll_answers tr > th").each(function (b) { a(this).text(pdAdminL10n.text_answer + " " + (b + 1)); a(c).append('<option value="' + (b + 1) + '">' + (b + 1) + "</option>") }); if (1 < d) { var e = a("> option", c).size(); d <= e ? a("> option", c).eq(d - 1).attr("selected", "selected") : d == b && a("> option", c).eq(e - 1).attr("selected", "selected") } }) }
function pd_totalvotes() { temp_vote_count = 0; jQuery(document).ready(function (a) { a("#poll_answers tr td input[size=4]").each(function (c) { temp_vote_count = isNaN(a(this).val()) ? temp_vote_count + 0 : temp_vote_count + parseInt(a(this).val()) }); a("#pollq_totalvotes").val(temp_vote_count) }) }

function pd_add_answer_add_form() {
	jQuery(document).ready(function ($) {
		$('#poll_answers').append('<tr id="poll-answer-' + count_poll_answer + '">\
			<td width="60%"><input type="text" size="45" maxlength="200" name="polla_answers[]" />\
			<input type="color" id="color_picker" name="color_picker[]" value="' + pdAdminL10n.default_color + '" >&nbsp;&nbsp;&nbsp;\
			<input type="button" value="' + pdAdminL10n.text_delete_poll_answer + '" \
			onclick="pd_remove_answer_add_form(' + count_poll_answer + ');" class="button" /></td></tr>');
		count_poll_answer++;
		pd_reorder_answer();
	});
}
function pd_remove_answer_add_form(a) { jQuery(document).ready(function (c) { c("#poll-answer-" + a).remove(); pd_reorder_answer(); }) }
function pd_remove_answer_edit(a) { jQuery(document).ready(function (c) { c("#poll-answer-new-" + a).remove(); pd_totalvotes(); pd_reorder_answer();  }) }
// Add Poll's Answer In Edit Poll Page
function pd_add_answer_edit() {
	jQuery(document).ready(function ($) {
		$('#poll_answers').append('<tr id="poll-answer-new-' + count_poll_answer_new + '">\
			<td width="60%"><input type="text" size="45" maxlength="200" name="polla_answers_new[]" />\
			<input type="color" id="color_picker" name="color_picker[]" value="' + pdAdminL10n.default_color + '" >&nbsp;&nbsp;&nbsp;\
			<input type="button" value="' + pdAdminL10n.text_delete_poll_answer + '" \
			onclick="pd_remove_answer_edit(' + count_poll_answer_new + ');" class="button" /></td>\
			<td width="20%" align="' + pdAdminL10n.text_direction + '">0 \
			<input type="text" size="4" name="polla_answers_new_votes[]" value="0" onblur="pd_totalvotes();" /></td>\
			</tr>');
		count_poll_answer_new++;
		pd_reorder_answer();
	});
}
function pd_remove_answer_edit(a) { jQuery(document).ready(function (c) { c("#poll-answer-new-" + a).remove(); pd_totalvotes(); pd_reorder_answer() }) }
function pd_is_multiple_answer() { jQuery(document).ready(function (a) { 1 == parseInt(a("#pollq_multiple_yes").val()) ? a("#pollq_multiple").attr("disabled", !1) : (a("#pollq_multiple").val(1), a("#pollq_multiple").attr("disabled", !0)) }) } 
function pd_check_timestamp() { jQuery(document).ready(function (a) { a("#edit_polltimestamp").is(":checked") ? a("#pollq_timestamp").show() : a("#pollq_timestamp").hide() }) };
function pd_check_recaptcha() { jQuery(document).ready(function (a) { a("#enable_recaptcha").is(":checked") ? a("#recaptcha_key input").prop('disabled', false) : a("#recaptcha_key input").prop('disabled', true); }) };
function pd_check_expiry() { jQuery(document).ready(function (a) { a("#pollq_expiry_no").is(":checked") ? a("#pollq_expiry").hide() : a("#pollq_expiry").show() }) };
// Delete Poll
function pd_delete_poll(poll_id, poll_confirm, nonce) {
	delete_poll_confirm = confirm(poll_confirm);
	if (delete_poll_confirm) {
		global_poll_id = poll_id;
		jQuery(document).ready(function ($) {
			$.ajax({
				type: 'POST', url: pdAdminL10n.admin_ajax_url, data: 'do=' + pdAdminL10n.text_delete_poll + '&pollq_id=' + poll_id + '&action=poll-dude-control&_ajax_nonce=' + nonce, cache: false, success: function (data) {
					$('#message').html(data);
					$('#message').show();
					$('#poll-' + global_poll_id).remove();
				}
			});
		});
	}
}
// Delete Poll Answer
function pd_delete_ans(poll_id, poll_aid, poll_aid_vote, poll_confirm, nonce) {
	delete_poll_ans_confirm = confirm(poll_confirm);
	if (delete_poll_ans_confirm) {
		global_poll_id = poll_id;
		global_poll_aid = poll_aid;
		global_poll_aid_votes = poll_aid_vote;
		temp_vote_count = 0;
		jQuery(document).ready(function ($) {
			$.ajax({
				type: 'POST', 
				url: pdAdminL10n.admin_ajax_url, 
				data: 'do=' + pdAdminL10n.text_delete_poll_ans + '&pollq_id=' + poll_id + '&polla_aid=' + poll_aid + '&action=poll-dude-control&_ajax_nonce=' + nonce, 
				cache: false, 
				success: function (data) {
					$('#message').html(data);
					$('#message').show();
					$('#poll_total_votes').html((parseInt($('#poll_total_votes').html()) - parseInt(global_poll_aid_votes)));
					$('#pollq_totalvotes').val(temp_vote_count);
					$('#poll-answer-' + global_poll_aid).remove();
					pd_totalvotes();
					pd_reorder_answer();
				}
			});
		});
	}
}

function pd_checkall_top() { 
	jQuery(document).ready(function (a) { 
		if (a("#delete_all").prop("checked")) {
			a("#delete_all2").prop("checked", true);
			a("input[name='pollq[]']").each(function() {
				a(this).prop("checked", true);
			});
		} else {
			a("#delete_all2").prop("checked", false);
			a("input[name='pollq[]']").each(function() {
				a(this).prop("checked", false);
			});
		}
	}) 
};

function pd_checkall_bottom() { 
	jQuery(document).ready(function (a) { 
		if (a("#delete_all2").prop("checked")) {
			a("#delete_all").prop("checked", true);
			a("input[name='pollq[]']").each(function() {
				a(this).prop("checked", true);
			});
		} else {
			a("#delete_all").prop("checked", false);
			a("input[name='pollq[]']").each(function() {
				a(this).prop("checked", false);
			});
		}
	}) 
};

function pd_select_action(value) {
	var selectBox = document.getElementById("selectBox");
	var selectedValue = selectBox.options[selectBox.selectedIndex].value;
	if (selectedValue === "shortcode") {
		prompt("Please press ctrl+c to copy the shortcode", "[poll_dude id=\"" + value + "\"]")
	} else {
		location.href = selectedValue;
	} 
}
