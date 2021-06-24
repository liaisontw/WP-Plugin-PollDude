=== Poll-Dude ===
Contributors: Liaison  
Donate link:   
Tags: poll, polls, vote, post, page, shortcode, widget    
Requires at least: 5.7.2  
Tested up to: 5.7.2  
Stable tag: 5.7.2  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  


## Description  
Create the polls by generating shortcodes embedded in your posts.   
Showing one selected poll as a widget.  
With reCaptchaV2 client side to prevent robots accessing.   
The color of each answer voted-bar can be set differently.  

### Build Status  


### Development  
[Poll Dude](https://github.com/liaisontw/poll-dude)  


== Installation ==  

1. Upload 'poll-dude' folder to the '/wp-content/plugins/' directory  
2. Activate 'poll-dude' through the 'Plugins' or 'Installed Plugins' menu in WordPress Dashboard  
3. Set reCaptchaV2 keys (if reCaptcha enabled in the poll)  
    3.1 Go [Google reCaptcha](https://www.google.com/recaptcha/about/)  
    3.2 Click 'Admin Console' on the page top  
    3.3 Choose reCaptchaV2 (Suggest use the most easy " I'm not a robot " checkbox)  
    3.4 Get sitekey & secretkey  
    3.5 Paste sitekey & secretkey in 'Poll Dude (Poll Options)' page under 'Poll Dude' plugin menu in WordPress Dashboard  
    3.6 Click 'Set Key' button  

4. Add a New Poll  
    4.1 Go 'New Poll' page under 'Poll Dude' plugin menu in WordPress Dashboard  
        or Click 'Add New Poll' button on 'Control Panel' page under 'Poll Dude' plugin menu in WordPress Dashboard  
    4.2 (Option) If 'enable reCaptcha' checked please make sure Step #3: 'to Set reCaptchaV2 keys' completed  
    4.3 (Must) Filled poll question  
    4.4 Fill the answers & pickup the colors  
    4.5 (Option) Allow Multiple Answers  
    4.6 Click 'New Poll' button  
    4.7 (Option) Get the shortcode on the top of 'New Poll' page  

5. Get the shortcode of a poll  
    5.1 Get the shortcode when add a new poll as step #4.7 or  
    5.2 Choose 'Shortcode' from the 'Action' drop-down menu of each poll  
        (Copy from prompt message after shortcode action seleted)  

6. Show the Poll on your wordpress website.   
    6.1 First way, add 'Poll Dude' in your widget list to appear & Select a Poll  
    6.2 Second way, paste the shortcode in your post or page  

7. Delete Polls  
    7.1 Click 'Delete' button of the poll to be deleted  
    7.2 Check the checkboxes of the polls to be deleted then click 'Bulk Delete'  

8. Edit a poll  
    8.1 Choose 'Edit' from the 'Action' drop-down menu of each poll  
    8.2 Click 'Edit Poll' button to save the change  


== Frequently Asked Questions ==  



== Screenshots ==  

1. Dashboard - Poll Dude Option (`/assets/1_Poll-Dude-Option.png`)
2. Dashboard - New Poll Page (`/assets/2_New-Poll.png`)
3. Dashboard - Control Panel Page, Get Shortcode (`/assets/3_Control-Panel-Get-Shortcode.png`)
4. Dashboard - Edit Poll (`/assets/4_Edit-Poll.png`)
5. Poll Display - with reCaptcha, Single Answer (`/assets/5-Poll-Display-reCaptcha-Single-Answer.png`)
6. Poll Display - with reCaptcha, Multiple Answers (`/assets/6-Poll-Display-reCaptcha-Multiple-Answers.png`)
7. Poll Display - No reCaptcha, Single Answer (`/assets/7-Poll-Display-No-reCaptcha-Single-Answer.png`)
8. Poll Display - Poll Results (`/assets/8-Poll-Display-Poll-Results.png`)

== Changelog ==

= 1.0 =
* First release


== Upgrade Notice ==

