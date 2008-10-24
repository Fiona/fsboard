<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2006 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

FSBoard is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
--------------------------------------------------------------------------

*********************************
*       FSBoard                 *
*       by Fiona 2006           *
*********************************
*       Registration Page       *
*       Started by Fiona        *
*       02nd Aug 2005           *
*********************************
*       Last edit by Fiona      *
*       24th Feb 2005           *
*********************************

As well as handling user registration,
this also deals with account validation
*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


$template_register = load_template_class("template_register");

load_language_group("register");


//***********************************************
// What are we doing?
//***********************************************
$_GET['m2'] = (isset($_GET['m2'])) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];

$output -> page_title = $lang['register_page_title'];

switch ($secondary_mode)
{

        case "form":

            show_registration_form();
        	break;

        case "create":

            create_account();
        	break;

        case "activateform":

            show_activation_form();
        	break;

        case "activate":

            activate_account();
        	break;

        case "main":

            show_registration_form();
        
}


//***********************************************
// Show the registration form. Exciting.
//***********************************************
function show_registration_form($entered_data = "")
{

        global $cache, $template_register, $template_global, $output, $lang, $user;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return;
        
        }

        // Check if the admin has disabled registration
        if ($cache -> cache['config']['reg_disable_new'])
        {
        
                $output -> add($template_global -> normal_error($lang['error_no_register']));
                return;

        }
        
        // If admin has rules turned on, we need to show them
        if ($cache -> cache['config']['rules_on'])
        {

                $output -> add($template_register -> forum_rules());
        
        }

        // **************************
        // Custom profile fields!
        // **************************
        $custom_profile_fields = "";
        $javascript_custom = "";

        if(count($cache -> cache['profile_fields']) > 0)
        {
        
                // We have some fields, go through them...
                foreach($cache -> cache['profile_fields'] as $key => $f_array)
                {

                        // show on form?
                        if(!$f_array['show_on_reg'] || $f_array['admin_only_field'])
                                continue;

                        // What input?
                        switch($f_array['field_type'])
                        {
                        
                                case "text":
                                        $input =  $template_register -> reg_custom_text("field_".$key, $entered_data["field_".$key], $f_array['size'], $f_array['max_length']);
                                        break;
                        
                                case "textbox":
                                        $input =  $template_register -> reg_custom_textbox("field_".$key, $entered_data["field_".$key], $f_array['size']);
                                        break;
                        
                                case "yesno":
                                        $input =  $template_register -> reg_custom_yesno("field_".$key, $entered_data["field_".$key]);
                                        break;
                        
                                case "dropdown":
                                        $dropdown_values = explode('|', $f_array['dropdown_values']);
                                        $dropdown_text = explode('|', $f_array['dropdown_text']);

                                        $options = array();
                                        
                                        foreach($dropdown_values as $key2 => $val)
                                                $options[trim($val)] = trim($dropdown_text[$key2]);

                                        $input =  $template_register -> reg_custom_dropdown("field_".$key, $f_array['size'], $options);
                                        break;
                        
                        }
                                        
                        // Stick it in
                        $custom_profile_fields .= $template_register -> reg_custom_row($f_array['name'], $f_array['description'], $input);

                        // Javavscript validation...
                        if($f_array['must_be_filled'])
                                $javascript_custom .= "if (document.regform.field_".$key.".value == '') { Check = 1; };\n ";
                        
                }
        
        }    

        if($entered_data === "")
			$entered_data = array(
				"username" 	=> "",
				"email" 	=> ""
			);

		// Plugin
		hook_register_before_reg_form($entered_data);

		
        // **************************
        // Do the form!
        // **************************
        $output -> add($template_register -> registration_form($entered_data, $custom_profile_fields, $javascript_custom));

		// Plugin
		hook_register_after_reg_form($entered_data);

}


//***********************************************
// Take the $_post data and make sure it's Okie Doke.
// Then create the account itself and send e-mails blah blah...
//***********************************************
function create_account()
{

        global $template_global, $output, $_POST, $cache, $db, $lang, $user;

        // If we are logged in, we need a error
        if (!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return;
        
        }

        // Check if the admin has disabled registration
        if ($cache -> cache['config']['reg_disable_new'])
        {
        
                $output -> add($template_global -> normal_error($lang['error_no_register']));
                return;

        }

        // Turn POST stuff into our own array
        $entered_data = array();

        $entered_data['username'] = trim(stripslashes($_POST['username']));
        $entered_data['email'] = trim($_POST['email']);
        $entered_data['password'] = $_POST['password'];
        $entered_data['password2'] = $_POST['password2'];

        if(count($cache -> cache['profile_fields']) > 0)
        {
        
                // We have some fields, go through them...
                foreach($cache -> cache['profile_fields'] as $key => $f_array)
                {
                
                        if(!$f_array['show_on_reg'])
                                continue;
                 
                        $entered_data['field_'.$key] = $_POST['field_'.$key];

                }
                
        }
        
        // Make sure everything is okay for including...
        if(check_account_info($entered_data))
        {
                
                // ********************************
                // Generate a unique activation code        
                // ********************************
                $validate_code = _substr(md5(uniqid(rand(), true)), 0, 13); 

                if($cache -> cache['config']['reg_validation'])
                        $user_group = "5";
                else
                        $user_group = "3";
                
                // ********************************
                // Registraion was okay, so let's attempt to add this account
                // ********************************
                $query_string = "INSERT INTO ".$db -> table_prefix."users
                (username, user_group, ip_address, password, email, registered, 
                last_active, validate_id, need_validate)
                VALUES('".$entered_data['username']."', '".$user_group."', '".user_ip()."', '".md5($entered_data['password'])."', '".$entered_data['email']."', '".TIME."',
                '".TIME."', '".$validate_code."', '".$cache -> cache['config']['reg_validation']."')";

                // Execute the query and check if it died.
                if (!$db -> query($query_string))
                {

                        $output -> add($template_global -> critical_error($lang['error_registration_add']));        
                        return(false);
                
                }

                // Get the ID number of the account just inserted
                $account_id = $db -> insert_id();

                // ********************************
                // Sort out custom profile fields!!
                // ********************************
                if(count($cache -> cache['profile_fields']) > 0)
                {
                
                        $custom_stuff = array();
                        
                        // We have some fields, go through them...
                        foreach($cache -> cache['profile_fields'] as $key => $f_array)
                        {
        
                                // show on form?
                                if(!$f_array['show_on_reg'] || $f_array['admin_only_field'])
                                        continue;

                                $custom_stuff["field_".$key] = trim($entered_data["field_".$key]);

                        }
                        
                        // Got some?
                        if($custom_stuff)
                        {
                               
                                // Check the entry exists for SOME REASON (never know)
                                $fields = $db -> query("select member_id from ".$db -> table_prefix."profile_fields_data where member_id='".$account_id."'");
                                
                                // Insert or update
                                if($db -> num_rows($fields) == 0)
                                {
                                        $custom_stuff['member_id'] = $account_id;
                                        $db -> basic_insert("profile_fields_data", $custom_stuff);
                                }
                                else
					$db -> basic_update("profile_fields_data", $custom_stuff, "member_id='".$account_id."'");
                                                        
                        }
                        
                }
                                
                // ********************************
                // If we need to send validation e-mail, do it.
                // ********************************
                if($cache -> cache['config']['reg_validation'])
                {        
                
                        // Get the message from the language file
                        $email_message = $lang['email_activate'];
                        
                        // We need to replace certain things so the email sends the right info. Kay?
                        $email_message = str_replace('<forum_name>', $cache -> cache['config']['board_name'], $email_message);
                        $email_message = str_replace('<activate_url>', $cache -> cache['config']['board_url']."/index.php?m=reg&m2=activate&user=".$account_id."&code=".$validate_code, $email_message);
                        $email_message = str_replace('<activate_form_url>', $cache -> cache['config']['board_url']."/index.php?m=reg&m2=activateform&user=".$account_id, $email_message);
                        $email_message = str_replace('<activate_code>', $validate_code, $email_message);
                        $email_message = str_replace('<user_id>', $account_id, $email_message);

                        // Send the e-mail
                        $mail = new email;
                        $mail -> send_mail($entered_data['email'], $lang['email_activate_subject'], $email_message);

                        // Fix message
                        $output -> replace_number_tags($lang['reg_sent_mail'], array(ROOT));

                        // Print message telling user what to do
                        $output -> add($template_global -> message($lang['account_registration'], $lang['reg_sent_mail']));        

                }
                else
                {

                        // Fix message
                        $output -> replace_number_tags($lang['reg_completed'], array(ROOT));
                        
                        // Print message telling user they can login now
                        $output -> add($template_global -> message($lang['account_registration'], $lang['reg_completed']));        

                }

                // ********************************
                // If we need to tell the admin
                // ********************************
                if ($cache -> cache['config']['reg_notify_admin'])
                {

                        // Get the message from the language file
                        $email_message = $lang['email_admin_registration'];
                        
                        // We need to replace certain things so the email sends the right info. Kay?
                        $email_message = str_replace('<forum_name>', $cache -> cache['config']['board_name'], $email_message);
                        $email_message = str_replace('<new_username>', $entered_data['username'], $email_message);

                        // Send the e-mail
                        $mail = new email;
                        $mail -> send_mail($cache -> cache['config']['admin_email'], $lang['email_admin_registration_subject'], $email_message);
                        
                }
                
        }
        else
                show_registration_form($entered_data);
                
}


//***********************************************
// Works out if the stuff entered is okays
//***********************************************
function check_account_info($entered_data)
{
        
        global $template_global, $output, $lang, $db, $cache;

        // Sort out invalid e-mail characters
        $entered_data['email'] = str_replace( " ", "", $entered_data['email']);
        $entered_data['email'] = preg_replace( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $entered_data['email']);

        // Check username length
        if (_strlen($entered_data['username']) < 2 || _strlen($entered_data['username']) > 25)
        {
               $output -> add($template_global -> normal_error($lang['error_username_too_long']));        
               return(false);
        }
        
        // Check password length
        if (_strlen($entered_data['password']) < 4 || _strlen($entered_data['password']) > 14)
        {
               $output -> add($template_global -> normal_error($lang['error_password_too_long']));        
               return(false);
        }

        // Check passwords match
        if ($entered_data['password'] != $entered_data['password2'])
        {
               $output -> add($template_global -> normal_error($lang['error_password_match']));        
               return(false);
        }

        // Check e-mail is valid
        if (!preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $entered_data['email']))
        {
               $output -> add($template_global -> normal_error($lang['error_invalid_email']));        
               return(false);
        }

        // Check for reserved characters in username
        $invalid_chars = array("'", "\"", "<!--", "\\");
        foreach ($invalid_chars as $char)
        {

                if (strstr($entered_data['username'], $char))
                {
                       $output -> add($template_global -> normal_error($lang['error_username_reserved_chars']));        
                       return(false);
                }

        }

        // Check username is valid
        $check_username = $db -> query("select username from ".$db -> table_prefix."users where lower(username)='"._strtolower($entered_data['username'])."'");
        
        if ($db -> num_rows($check_username) > 0)
        {
               $output -> add($template_global -> normal_error($lang['error_username_exists']));        
               return(false);
        }

        // Check if e-mail is taken
        if ($cache -> cache['config']['reg_duplicate_emails'] == 0)
        {

                $check_email = $db -> query("select email from ".$db -> table_prefix."users where lower(email)='"._strtolower($entered_data['email'])."'");
                
                if ($db -> num_rows($check_email) > 0)
                {
                       $output -> add($template_global -> normal_error($lang['error_email_exists']));        
                       return(false);
                }
       
        }
        
        return(true);
        
}


//***********************************************
// Doing account validation, both from a single url or from the form
//***********************************************
function activate_account()
{

        global $user, $lang, $output, $template_global, $db;
        
        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));        
                return(false);
        
        }

        // If the user has used the activation form we need to get the data from posts
        if($_POST['activateform'] == "true")
        {
                
                // Get validation code and user id from post data
                $v_code = $_POST['code'];
                $user_id = $_POST['userid'];
                
        }
        else
        {

                // Get validation code and user id from url
                $v_code = $_GET['code'];
                $user_id = $_GET['user'];
        
        }
        
        // grab user from DB
        $select_user = $db -> query("select id, need_validate, validate_id from ".$db -> table_prefix."users where id='".$user_id."'");
        
        // See if it exists
        if ($db -> num_rows($select_user) > 0)
        {

                // Grab the full info
                $user_array = $db -> fetch_array($select_user);
                
                // Check it needs activating
                if ($user_array['need_validate'] == 1)
                {

                        // Check if the url code is right
                        if ($user_array['validate_id'] == trim($v_code))
                        {

                                // Validate the user in the database
				$validate_query = array('need_validate' => 0, 'user_group' => 3);
				
                                if(!$db -> basic_update("users", $validate_query, "id='".$user_array['id']."'"))
                                {

                                        $output -> add($template_global -> normal_error($lang['error_activation_failed']));                                
                                        show_activation_form($user_id);
                                
                                }                                
                                else
                                        // Print message telling user they can login now
                                        $output -> add($template_global -> message($lang['account_registration'], $lang['reg_completed']));        

                        }
                        else
                        {
                        
                                $output -> add($template_global -> normal_error($lang['error_bad_activation_code']));                                
                                show_activation_form($user_id);

                        }
                                                        
                }
                else
                {
                        // Fix message
                        $output -> replace_number_tags($lang['error_already_activated'], array(ROOT));

                        $output -> add($template_global -> normal_error($lang['error_already_activated']));        
                }
                        
        }
        else
                $output -> add($template_global -> normal_error($lang['error_no_user']));        
        
        return(true);
        
}


//***********************************************
// If from the URL didn't work, users can activate manually using a form
//***********************************************
function show_activation_form($user_id = "")
{

        global $user, $lang, $output, $template_global, $template_register, $db;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));        
                return(false);
        
        }

        // If user ID isn't given, set it to one from the URL
        if($user_id == "")
                $user_id = $_GET['user'];

        // grab user from DB
        $select_user = $db -> query("select id, need_validate, validate_id from ".$db -> table_prefix."users where id='".$user_id."'");
        
        // See if it exists
        if ($db -> num_rows($select_user) > 0)
        {

                // Grab the full info
                $user_array = $db -> fetch_array($select_user);
                
                // Check it needs activating
                if ($user_array['need_validate'] == 1)
                {

                        $output -> add($template_register -> activate_form($user_id));        

                }
                else
                        $output -> add($template_global -> normal_error($lang['error_already_activated']));        
                        
        }
        else
                $output -> add($template_global -> normal_error($lang['error_no_user']));        

}
?>
