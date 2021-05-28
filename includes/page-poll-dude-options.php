<?php
### Check Whether User Can Manage Polls
if(!current_user_can('manage_polls')) {
    die('Access Denied');
}

### Variables Variables Variables
global $poll_dude;
$base_name = plugin_basename( __FILE__ );
$base_page = 'admin.php?page='.$base_name;
$current_page = 'admin.php?page='.$poll_dude->get_plugin_name().'/includes/'.basename(__FILE__);
$pd_recaptcha_sitekey   = get_option('pd_recaptcha_sitekey');
$pd_recaptcha_secretkey = get_option('pd_recaptcha_secretkey');



if( isset($_POST['Submit']) && $_POST['Submit'] ) {
	check_admin_referer('polldude_options');
    $update_pd_options          = array();
	$update_pd_text             = array();
    
    $pd_recaptcha_sitekey   = isset( $_POST['sitekey'] ) ? $poll_dude->utility->removeslashes( $_POST['sitekey'] ) : $pd_recaptcha_sitekey;
    $pd_recaptcha_secretkey = isset( $_POST['secretkey'] ) ? $poll_dude->utility->removeslashes( $_POST['secretkey'] ) : $pd_recaptcha_secretkey;
    $update_pd_options[]    = update_option('pd_recaptcha_sitekey', $pd_recaptcha_sitekey);
    $update_pd_options[]    = update_option('pd_recaptcha_secretkey', $pd_recaptcha_secretkey);
    $update_pd_text[]       = __('reCaptcha Sitekey', 'poll-dude');
    $update_pd_text[]       = __('reCaptcha Secretkey', 'poll-dude');

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


<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<h2><?php _e('Poll Options', 'poll-dude-domain'); ?></h2>
<div class="wrap">
    <input type="checkbox" name="enable_recaptcha" id="enable_recaptcha" value="1" onclick="check_recaptcha();" />
    <label for="enable_recaptcha"><?php _e('Enable reCaptcha', 'poll-dude-domain'); ?></label>
    <br style="clear" />
    <form id="recaptcha_key" method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
        <?php wp_nonce_field('polldude_options'); ?>
        <table class="form-table">
        
            <tbody>
                <div id="recaptcha_key" style="display: block">
                    <tr class="form-field form-required">
                        <th valign="top" scope="row">
                            <label for="sitekey">
                            reCaptcha Site Key            
                            </label>
                        </th>
                        <td>
                        <input type="text" name="sitekey" id="sitekey" aria-required="true" size="40" value="<?php echo get_option('pd_recaptcha_sitekey'); ?>" disabled>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th valign="top" scope="row">
                            <label for="secretkey">
                            reCaptcha Secret Key            
                            </label>
                        </th>
                        <td>
                        <input type="text" name="secretkey" id="secretkey" aria-required="true" size="40" value="<?php echo get_option('pd_recaptcha_secretkey'); ?>" disabled>
                        </td>
                    </tr>
                    
                </div>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'poll-dude-domain'); ?>" disabled/>
        </p>
    </form>
</div>

<p>&nbsp;</p>





<!--






<div class="wrap">
    <input type="checkbox" name="enable_recaptcha" id="enable_recaptcha" value="1" onclick="check_recaptcha();" />
    <label for="enable_recaptcha">
        <?php 
            _e('Enable reCaptcha', 'poll-dude-domain'); 
        ?>
    </label>
    <br style="clear" />
    
    <form action="" method="post" id="recaptcha_key" style="display: block">
        <table class="form-table">
        <tbody>
            <tr class="form-field form-required">
                <th valign="top" scope="row">
                    <label for="sitekey">
                    reCaptcha Site Key            
                    </label>
                </th>
                <td>
                <input type="text" name="sitekey" id="sitekey" aria-required="true" size="20" value="" disabled>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th valign="top" scope="row">
                    <label for="secretkey">
                    reCaptcha Secret Key            
                    </label>
                </th>
                <td>
                <input type="text" name="secretkey" id="secretkey" aria-required="true" size="20" value="" disabled>
                </td>
            </tr>
        </tbody>
        </table>
        <p class="submit">
    
        <input type="submit" class="button-primary" value="Set Key" disabled>
        </p>
    </form>
</div>
