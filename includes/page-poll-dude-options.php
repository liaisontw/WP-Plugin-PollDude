<h2><?php _e('Poll Options', 'poll-dude-domain'); ?></h2>

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
        <!--
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="ab37bcdfe8">
        <input type="hidden" name="_wp_http_referer" value="/mywordpress/wp-admin/admin.php?page=polls&amp;action=options">      
        <input type="hidden" name="action" value="import-account">
        <input type="hidden" name="account" value="import">
        -->
        <input type="submit" class="button-primary" value="Set Key" disabled>
        </p>
    </form>
</div>
<p>&nbsp;</p>