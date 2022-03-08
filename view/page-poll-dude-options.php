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
$pd_recaptcha_sitekey   = preg_replace( '/[^a-zA-Z0-9_\-]/', '', get_option('pd_recaptcha_sitekey') );
$pd_recaptcha_secretkey = preg_replace( '/[^a-zA-Z0-9_\-]/', '', get_option('pd_recaptcha_secretkey'));
$pd_recaptcha_enable    = get_option('pd_recaptcha_enable');
$pd_default_color       = get_option('$pd_default_color');





if( isset($_POST['Submit']) ) {
    $update_pd_options          = array();
    $update_pd_text             = array();

    switch($_POST['Submit']) {
        case __('Set Color', 'poll-dude'):
            check_admin_referer('polldude_color');

            $pd_default_color   = isset( $_POST['default_color'] ) ? sanitize_hex_color($_POST['default_color']) : $pd_default_color;
            $update_pd_options[]    = update_option('pd_default_color', $pd_default_color);
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

            $pd_default_close   = isset( $_POST['default_close'] ) ? absint($_POST['default_close']) : $pd_close;
            $update_pd_options[]    = update_option('pd_close', $pd_close);
            $update_pd_text[]       = __('Show Close Poll', 'poll-dude');
        
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
                        reCaptcha Site Key            
                        </label>
                    </th>
                    <td>
                        <input type="text" name="sitekey" id="sitekey" aria-required="true" size="40" value="<?php echo get_option('pd_recaptcha_sitekey'); ?>" >
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="secretkey">
                        reCaptcha Secret Key            
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
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="default_color">
                        Default Voted Bar Color
                        </label>
                    </th>
                    <td>
                        <input type="color" name="default_color" id="default_color" value="<?php echo get_option('pd_default_color'); ?>" >
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
                        Close Poll Display
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
</div>

<p>&nbsp;</p>





