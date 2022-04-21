<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_polls')) {
    die('Access Denied');
}

### Variables Variables Variables
global $poll_dude;
$base_name = plugin_basename( __FILE__ );
$base_page = 'admin.php?page='.$base_name;
$current_page = 'admin.php?page='.$poll_dude->get_plugin_name().'/view/'.basename(__FILE__);
//$pd_recaptcha_sitekey   = sanitize_key( get_option('pd_recaptcha_sitekey'));
$pd_recaptcha_sitekey    = preg_replace( '/[^a-zA-Z0-9_\-]/', '', get_option('pd_recaptcha_sitekey') );
$pd_recaptcha_secretkey  = preg_replace( '/[^a-zA-Z0-9_\-]/', '', get_option('pd_recaptcha_secretkey'));
$pd_recaptcha_enable     = get_option( 'pd_recaptcha_enable');
$pd_default_color        = get_option( 'pd_default_color');
//$pd_default_color_array  = get_option( 'pd_default_color_array');
$pd_close                = get_option( 'pd_close');
$pd_allowtovote          = get_option( 'pd_allowtovote' );
$pd_ans_sortby           = get_option( 'pd_ans_sortby' );
$pd_ans_sortorder        = get_option( 'pd_ans_sortorder' );
$pd_ans_result_sortby    = get_option( 'pd_ans_result_sortby' );
$pd_ans_result_sortorder = get_option( 'pd_ans_result_sortorder' );



if( isset($_POST['Submit']) ) {
    $update_pd_options          = array();
    $update_pd_text             = array();

    switch($_POST['Submit']) {
        case __('Set Color', 'poll-dude'):
            check_admin_referer('polldude_color');
            $pd_default_color_array  = get_option( 'pd_default_color_array');
            if ( isset( $_POST['default_color_array'] ) ) {
                foreach ( $_POST['default_color_array'] as $default_color ) {
                    $default_color_array[] = sanitize_hex_color( $default_color );
                }
            } else {
                $default_color_array = $pd_default_color_array;
            }
            $update_pd_options[]    = update_option('pd_default_color_array', $default_color_array);
            $update_pd_text[]       = __('Default Voted Bar Color', 'poll-dude');
        
            break;
	    case __('Set Keys', 'poll-dude'):
            check_admin_referer('polldude_recaptcha');
            
            $pd_recaptcha_sitekey   = isset( $_POST['sitekey'] ) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '',  $_POST['sitekey'] ) : $pd_recaptcha_sitekey;
            $pd_recaptcha_secretkey = isset( $_POST['secretkey'] ) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '',  $_POST['secretkey'] ) : $pd_recaptcha_secretkey;
            $update_pd_options[]    = update_option('pd_recaptcha_sitekey', $pd_recaptcha_sitekey);
            $update_pd_options[]    = update_option('pd_recaptcha_secretkey', $pd_recaptcha_secretkey);
            $update_pd_text[]       = __('reCaptcha Sitekey', 'poll-dude');
            $update_pd_text[]       = __('reCaptcha Secretkey', 'poll-dude');
        
            break;
        case __('Set Close Poll', 'poll-dude'):
            check_admin_referer('polldude_close');

            $pd_default_close   = isset( $_POST['pd_close'] ) ? absint($_POST['pd_close']) : $pd_close;
            $update_pd_options[]    = update_option('pd_close', $pd_default_close);
            $update_pd_text[]       = __('Show Close Poll', 'poll-dude');
        
            break;
        case __('Set Allow to Vote', 'poll-dude'):
            check_admin_referer('polldude_allowtovote');

            $pd_default_allowtovote   = isset( $_POST['pd_allowtovote'] ) ? absint($_POST['pd_allowtovote']) : $pd_allowtovote;
            $update_pd_options[]    = update_option('pd_allowtovote', $pd_default_allowtovote);
            $update_pd_text[]       = __('Set Allow to Vote', 'poll-dude');
        
            break;
        case __('Set Poll Answer Order', 'poll-dude'):
            check_admin_referer('polldude_sort_poll_answers');

            $pd_default_ans_sortby    = isset( $_POST['poll_ans_sortby'] ) ? sanitize_text_field($_POST['poll_ans_sortby']) : $pd_ans_sortby;
            $update_pd_options[]      = update_option('pd_ans_sortby', $pd_default_ans_sortby);
            $pd_default_ans_sortorder = isset( $_POST['poll_ans_sortorder'] ) ? sanitize_text_field($_POST['poll_ans_sortorder']) : $pd_ans_sortorder;
            $update_pd_options[]      = update_option('pd_ans_sortorder', $pd_default_ans_sortorder);
            $update_pd_text[]         = __('Set Poll Answer Order', 'poll-dude');
        
            break;
        case __('Set Poll Result Order', 'poll-dude'):
            check_admin_referer('polldude_sort_poll_answers_result');

            $pd_default_ans_result_sortby    = isset( $_POST['poll_ans_result_sortby'] ) ? sanitize_text_field($_POST['poll_ans_result_sortby']) : $pd_ans_result_sortby;
            $update_pd_options[]             = update_option('pd_ans_result_sortby', $pd_default_ans_result_sortby);
            $pd_default_ans_result_sortorder = isset( $_POST['poll_ans_result_sortorder'] ) ? sanitize_text_field($_POST['poll_ans_result_sortorder']) : $pd_ans_result_sortorder;
            $update_pd_options[]             = update_option('pd_ans_result_sortorder', $pd_default_ans_result_sortorder);
            $update_pd_text[]                = __('Set Poll Result Order', 'poll-dude');
        
            break;
    }

    $i=0;
	$text = '';
	foreach($update_pd_options as $update_pd_option) {
		if($update_pd_options) {
			$text .= '<p style="color: green;">'.$update_pd_text[$i].' '.__('Updated', 'poll-dude').'</p>';
		}
		$i++;
	}
	if(empty($text)) {
		$text = '<p style="color: red;">'.__('No Option Updated', 'poll-dude').'</p>';
	}
    
    $poll_dude->admin->cron_activate();   
}

?>


<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.wp_kses_data($text).'</p></div>'; } ?>
<h2><?php _e('Poll Options', 'poll-dude'); ?></h2>
<div class="wrap">
    <form  id="recaptcha_key" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
        <?php wp_nonce_field('polldude_recaptcha'); ?>
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="sitekey">
                        <?php _e('reCaptcha Site Key', 'poll-dude'); ?>            
                        </label>
                    </th>
                    <td>
                        <input type="text" name="sitekey" id="sitekey" aria-required="true" size="40" value="<?php echo get_option('pd_recaptcha_sitekey'); ?>" >
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="secretkey">
                        <?php _e('reCaptcha Secret Key', 'poll-dude'); ?>            
                        </label>
                    </th>
                    <td>
                        <input type="text" name="secretkey" id="secretkey" aria-required="true" size="40" value="<?php echo get_option('pd_recaptcha_secretkey'); ?>" >
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Set Keys', 'poll-dude'); ?>"/>
        </p>        
    </form>
    <form  id="default_color" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
        <?php wp_nonce_field('polldude_color'); ?>
        <?php $pd_default_color_array  = get_option( 'pd_default_color_array'); ?>
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="default_color">
                        <?php _e('Default Voted Bar Color', 'poll-dude'); ?>
                        </label>
                    </th>
                    <td>
                        1&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_0" value="<?php echo $pd_default_color_array[0]; ?>" >&nbsp;&nbsp;&nbsp;
                        2&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_1" value="<?php echo $pd_default_color_array[1]; ?>" >&nbsp;&nbsp;&nbsp;
                        3&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_2" value="<?php echo $pd_default_color_array[2]; ?>" >&nbsp;&nbsp;&nbsp;
                        4&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_3" value="<?php echo $pd_default_color_array[3]; ?>" >&nbsp;&nbsp;&nbsp;
                        5&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_4" value="<?php echo $pd_default_color_array[4]; ?>" >&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="default_color">
                        </label>
                    </th>
                    <td>
                        6&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_5" value="<?php echo $pd_default_color_array[5]; ?>" >&nbsp;&nbsp;&nbsp;
                        7&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_6" value="<?php echo $pd_default_color_array[6]; ?>" >&nbsp;&nbsp;&nbsp;
                        8&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_7" value="<?php echo $pd_default_color_array[7]; ?>" >&nbsp;&nbsp;&nbsp;
                        9&nbsp;&nbsp;&nbsp;<input type="color" name="default_color_array[]" id="default_color_8" value="<?php echo $pd_default_color_array[8]; ?>" >&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Set Color', 'poll-dude'); ?>"/>
        </p>        
    </form>
    <form  id="default_close" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
        <?php wp_nonce_field('polldude_close'); ?>
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="default_close">
                        <?php _e('How to Show the Close Poll?', 'poll-dude'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="pd_close" size="1">
                            <option value="1"<?php selected(1, get_option('pd_close')); ?>><?php _e('Show Poll\'s Results', 'poll-dude'); ?></option>
                            <option value="2"<?php selected(2, get_option('pd_close')); ?>><?php _e('Not Show Poll In Post/Sidebar', 'poll-dude'); ?></option>
                            <option value="3"<?php selected(3, get_option('pd_close')); ?>><?php _e('Show Disabled Poll\'s Voting Form', 'poll-dude'); ?></option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Set Close Poll', 'poll-dude'); ?>"/>
        </p>        
    </form>
    <form  id="default_allowtovote" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
        <?php wp_nonce_field('polldude_allowtovote'); ?>
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="default_allowtovote">
                        <?php _e('Who Can Vote?', 'poll-dude'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="pd_allowtovote" size="1">
                            <option value="1"<?php selected('1', get_option('pd_allowtovote')); ?>><?php _e('Guests Only', 'poll-dude'); ?></option>
                            <option value="2"<?php selected('2', get_option('pd_allowtovote')); ?>><?php _e('Registered Users Only', 'poll-dude'); ?></option>
                            <option value="3"<?php selected('3', get_option('pd_allowtovote')); ?>><?php _e('Registered Users And Guests', 'poll-dude'); ?></option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Set Allow to Vote', 'poll-dude'); ?>"/>
        </p>        
    </form>

    <!-- Sorting Of Poll Answers -->
    <form  id="default_sort_poll_answers" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
        <?php wp_nonce_field('polldude_sort_poll_answers'); ?>
        <table class="form-table">
            <tr>
                <th scope="row" valign="top"><?php _e('Sort Answers by:', 'poll-dude'); ?></th>
                <td>
                    <select name="poll_ans_sortby" size="1">
                        <option value="polla_votes"<?php selected('polla_votes', get_option('pd_ans_sortby')); ?>><?php _e('Number of Votes', 'poll-dude'); ?></option>
                        <option value="polla_aid"<?php selected('polla_aid', get_option('pd_ans_sortby')); ?>><?php _e('ID', 'poll-dude'); ?></option>
                        <option value="polla_answers"<?php selected('polla_answers', get_option('pd_ans_sortby')); ?>><?php _e('Name Alphabetic', 'poll-dude'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><?php _e('Sort Order of Answers:', 'poll-dude'); ?></th>
                <td>
                    <select name="poll_ans_sortorder" size="1">
                        <option value="asc"<?php selected('asc', get_option('pd_ans_sortorder')); ?>><?php _e('Ascending', 'poll-dude'); ?></option>
                        <option value="desc"<?php selected('desc', get_option('pd_ans_sortorder')); ?>><?php _e('Descending', 'poll-dude'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Set Poll Answer Order', 'poll-dude'); ?>"/>
        </p>        
    </form>

	<!-- Sorting Of Poll Results -->
	<form  id="default_sort_poll_result" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
    <?php wp_nonce_field('polldude_sort_poll_answers_result'); ?>
        <table class="form-table">
            <tr>
                <th scope="row" valign="top"><?php _e('Sort Results by:', 'poll-dude'); ?></th>
                <td>
                    <select name="poll_ans_result_sortby" size="1">
                        <option value="polla_votes"<?php selected('polla_votes', get_option('pd_ans_result_sortby')); ?>><?php _e('Number of Votes', 'poll-dude'); ?></option>
                        <option value="polla_aid"<?php selected('polla_aid', get_option('pd_ans_result_sortby')); ?>><?php _e('ID', 'poll-dude'); ?></option>
                        <option value="polla_answers"<?php selected('polla_answers', get_option('pd_ans_result_sortby')); ?>><?php _e('Name Alphabetic', 'poll-dude'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><?php _e('Sort Order of Results:', 'poll-dude'); ?></th>
                <td>
                    <select name="poll_ans_result_sortorder" size="1">
                        <option value="asc"<?php selected('asc', get_option('pd_ans_result_sortorder')); ?>><?php _e('Ascending', 'poll-dude'); ?></option>
                        <option value="desc"<?php selected('desc', get_option('pd_ans_result_sortorder')); ?>><?php _e('Descending', 'poll-dude'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Set Poll Result Order', 'poll-dude'); ?>"/>
        </p>        
    </form>
</div>

<p>&nbsp;</p>


