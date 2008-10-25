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
*       Login Page              *
*       Started by Fiona        *
*       03rd Aug 2005           *
*********************************
*       Last edit by Fiona      *
*       27th Mar 2006           *
*********************************

Lets the user login, logout and retrieve a lost password.
*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


$template_login = load_template_class("template_login");

load_language_group("login");


//***********************************************
// What are we doing?
//***********************************************
$_GET['m2'] = (isset($_GET['m2'])) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];


switch ($secondary_mode)
{

        case "loginform":

                show_login_form();
                $output -> page_title = $lang['login_page_title'];
        	break;

        case "login":

                login();
                $output -> page_title = $lang['login_page_title'];
        	break;

        case "logout":

                logout();
                $output -> page_title = $lang['logout_page_title'];
        	break;

        case "passwordform":

                show_lost_password_form();
                $output -> page_title = $lang['password_page_title'];
        	break;

        case "newpasswordemail":

                send_new_password_email();
                $output -> page_title = $lang['password_page_title'];
        	break;

        case "passwordform2":

                show_lost_password_form2();
                $output -> page_title = $lang['password_page_title'];
        	break;

        case "resetpassword":

                reset_password();
                $output -> page_title = $lang['password_page_title'];
        	break;

        default:

                show_login_form($template_login);
                $output -> page_title = $lang['login_page_title'];
        	break;
        
}


//***********************************************
// Show the login form.
//***********************************************
function show_login_form($template_login = NULL)
{

		if($template_login === NULL)
        	global $template_login;

        global $template_global, $output, $lang, $user;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return;
        
        }

        $entered_data = array(
        	"username" 			=> (isset($_POST['username'])) 			? $_POST['username'] 	: "",
        	"stay_logged_in" 	=> (isset($_POST['stay_logged_in']))	? "checked" 			: "",
        	"invisible" 	 	=> (isset($_POST['invisible'])) 		? "checked" 			: ""
        );

        // Fix message
        $output -> replace_number_tags($lang['enter_login_info'], array(ROOT));
        
        $output -> add($template_login -> login_form($entered_data));
        
}


//***********************************************
// Try to login to the forum
//***********************************************
function login()
{

        global $template_login, $template_global, $output, $lang, $user, $db;

        // **************************
        // If we are logged in, we need a error
        // **************************
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                break;
        
        }
        

        // **************************
        // grab user from DB
        // **************************
        $select_user = $db -> basic_select("users", "password, id, need_validate, username, user_group", "lower(username) = '".$db -> escape_string(_strtolower($_POST['username']))."'"); 
        
        // See if it exists
        if ($db -> num_rows($select_user) < 1)
        {
        
                $output -> add($template_global -> normal_error($lang['error_no_user']));
                show_login_form();
                return false;
                
        }
        
        // Grab the full info
        $user_array = $db -> fetch_array($select_user);
        
        
        // **************************
        // Check password
        // **************************
        if($user_array['password'] != md5($_POST['password']))
        {

                // Fix message
                $output -> replace_number_tags($lang['error_wrong_password'], array(ROOT));
        
                $output -> add($template_global -> normal_error($lang['error_wrong_password']));
                show_login_form();
                
                return false;
        
        }


        // **************************
        // Check validation
        // **************************
        if ($user_array['need_validate'] != "0")
        {
        
                $output -> add($template_global -> normal_error($lang['error_need_validation']));
                show_login_form();

                return false;
                        
        }
        
        
        // **************************
        // Update last login and IP
        // **************************
        $update_last_login = array(
			"last_active" => TIME,
			"ip_address" => user_ip(),
			"reset_password" => "0"
        	);
        
        if(!$db -> basic_update("users", $update_last_login, "id='".$user_array['id']."'"))
        {
        
                $output -> add($template_global -> critical_error($lang['error_logging_in']));
                show_login_form();

                return false;
                
        }                        

        // **************************
        // Process login
        // **************************
        if ($_POST['stay_logged_in'] == "")
                $_POST['stay_logged_in'] == 0;

        // Write cookies                        
        fs_setcookie("user_id", $user_array['id'], $_POST['stay_logged_in']);
        fs_setcookie("password", $user_array['password'], $_POST['stay_logged_in']);                                
        
        if ($_POST['invisible'])
                fs_setcookie("annonymous", "1", $_POST['stay_logged_in']);                                


        // **************************
        // Deal with session stuff
        // **************************
        $s = $user -> get_cookie("session");
        $session_id = ($s) ? $s : 0;
        
        // Already have a session! So update it.
        if($session_id)
        {

                // Delete older sessions, kay?        
                $db -> save_shutdown_query("delete from ".$db -> table_prefix."sessions where ip_address='".user_ip()."' and id <> '".$session_id."'");
        
                // Update older session.
                $db -> save_shutdown_query(
                        $db -> basic_update(
                                "sessions",
                                array(
                                        "user_id" => $user_array['id'],
                                        "username" => $user_array['username'],
                                        "user_group" => $user_array['user_group'],
                                        "last_active" => TIME,
                                        "ip_address" => user_ip(),
                                        "browser" => $_SERVER['HTTP_USER_AGENT'],
                                        "location" => '0'
                                ),
                                "id = '".$session_id."'",
                                true
                        )
                );
                                
        }
        // No session cookie found. So create new one.
        else
        {

                // Delete older ones..
                $db -> save_shutdown_query("delete from ".$db -> table_prefix."sessions where ip_address='".user_ip()."'");

                // Generate session id
                $session_id = md5(uniqid(rand(), true));

                $db -> save_shutdown_query(
                        $db -> basic_insert(
                                "sessions",
                                array(
                                        "id" => $session_id,
                                        "user_id" => $user_array['id'],
                                        "username" => $user_array['username'],
                                        "user_group" => $user_array['user_group'],
                                        "last_active" => TIME,
                                        "ip_address" => user_ip(),
                                        "browser" => $_SERVER['HTTP_USER_AGENT'],
                                        "location" => 0
                                ),
                                true
                        )
                );
                        
        }

        fs_setcookie("session", $session_id, false);
        
        $user -> session = $session_id;
        $user -> user_id = $user_array['id'];


        // **************************
        // Redirect the user
        // **************************
        $output -> redirect(ROOT."index.php", $lang['logged_in']);
        
}


//***********************************************
// Try to logout. :(
//***********************************************
function logout()
{

        global $db, $user, $output, $template_global, $lang;
        
        // If we are not logged in, we need a error
        if($user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_not_logged_in']));
                return(false);
        
        }

        // Invalidate Cookies
        fs_setcookie("user_id", "", false, TIME - 36000);
        fs_setcookie("password", "", false, TIME - 36000);
        fs_setcookie("annonymous", "", false, TIME - 36000);                                

        // Go away admin
        if(isset($_SESSION["fsboard_".$db -> table_prefix.'admin_area_session']))
                unset($_SESSION["fsboard_".$db -> table_prefix.'admin_area_session']);        

        // Guestify session
        $s = $user -> get_cookie("session");
        $session_id = ($s) ? $s : 0;

        if($session_id)
                $db -> save_shutdown_query(
                        $db -> basic_update(
                                "sessions",
                                array(
                                        "user_id" => 0,
                                        "username" => 0,
                                        "user_group" => 4,
                                        "last_active" => TIME,
                                        "ip_address" => user_ip(),
                                        "browser" => $_SERVER['HTTP_USER_AGENT'],
                                        "location" => '0'
                                ),
                                "id = '".$session_id."'",
                                true
                        )
                );
                
        // Redirect the user
        $output -> redirect(ROOT."index.php", $lang['logged_out']);

}


//***********************************************
// If people with no memory have forgotten their password
// They come to this form, and try to retrieve it.
//***********************************************
function show_lost_password_form()
{

        global $template_login, $template_global, $output, $lang, $user;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return(false);
        
        }

        $output -> add($template_login -> lost_password_form());

}


//***********************************************
// After visitng the previous form, this function sends the mail
// That includes a link they have to click on which sends them to the next form
//***********************************************
function send_new_password_email()
{

        global $cache, $user, $db, $output, $template_global, $lang;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return(false);
        
        }

        $username = $_POST['username'];

        $select_user = $db -> query("select id, email, need_validate from ".$db -> table_prefix."users where username='".$username."'");
        
        // See if it exists
        if ($db -> num_rows($select_user) > 0)
        {

                // Grab the full info
                $user_array = $db -> fetch_array($select_user);
                
                // Check it needs activating
                if ($user_array['need_validate'] == 0)
                {

                        // Generate a unique code        
                        $validate_code = _substr(md5(uniqid(rand(), true)), 0, 13); 
                        
                        // Set we're allowed to retrieve password, and the code
                        $update_password = array("reset_password" => "1", "validate_id" => $validate_code);
			$db -> basic_update("users", $update_password, "id='".$user_array['id']."'");
               
                        // Get the message from the language file
                        $email_message = $lang['email_lost_password'];
                        
                        // We need to replace certain things so the email sends the right info. Kay?
                        $email_message = str_replace('<username>', $username, $email_message);
                        $email_message = str_replace('<forum_name>', $cache -> cache['config']['board_name'], $email_message);
                        $email_message = str_replace('<password_url>', $cache -> cache['config']['board_url']."/index.php?m=login&m2=passwordform2&user=".$user_array['id']."&code=".$validate_code, $email_message);
                        $email_message = str_replace('<password_form_url>', $cache -> cache['config']['board_url']."/index.php?m=login&m2=passwordform2", $email_message);
                        $email_message = str_replace('<validate_code>', $validate_code, $email_message);
                        $email_message = str_replace('<user_id>', $user_array['id'], $email_message);

                        // Send the e-mail
                        $mail = new email;
                        $mail -> send_mail($user_array['email'], $lang['email_lost_password_subject'], $email_message);

                        // Print message telling user what to do
                        $output -> add($template_global -> message($lang['password_page_title'], $lang['password_sent_mail']));        

                }
                else
                {
                        $output -> add($template_global -> normal_error($lang['error_not_activated']));
                        show_lost_password_form();
                }        
                
        }
        else
        {
                $output -> add($template_global -> normal_error($lang['error_no_user']));
                show_lost_password_form();
        }

}


//***********************************************
// When getting to this form, users have to now input a new password 
//***********************************************
function show_lost_password_form2()
{

        global $cache, $user, $db, $output, $template_global, $template_login, $lang;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return(false);
        
        }

        // Check to see if we're using the URL version
        if(trim($_GET['user'] == '') || trim($_GET['code'] == ''))
        {
        
                // Show the long form
                $output -> add($template_login -> password_form2_long());
        
        }
        else
        {
                        
                $entered_data['userid'] = $_GET['user'];
                $entered_data['code'] = $_GET['code'];
                $output -> add($template_login -> password_form2_short($entered_data));
        
        }

}


//***********************************************
// After going through all that other crap, this is the final
// step to resetting a lost password.
//***********************************************
function reset_password()
{

        global $cache, $user, $db, $output, $template_global, $lang;

        // If we are logged in, we need a error
        if(!$user -> is_guest)
        {

                $output -> add($template_global -> normal_error($lang['error_already_logged_in']));
                return(false);
        
        }

        $select_user = $db -> query("select id, need_validate, reset_password, validate_id from ".$db -> table_prefix."users where id='".trim($_POST['userid'])."'");
        
        // See if it exists
        if ($db -> num_rows($select_user) > 0)
        {

                // Grab the full info
                $user_array = $db -> fetch_array($select_user);
                
                // Check it needs activating
                if ($user_array['need_validate'] == 0)
                {

                        // Check if this user is actually in the process of password reset
                        if ($user_array['reset_password'] == 1)
                        {

                                // Check if the validation code is okay
                                if ($user_array['validate_id'] == trim($_POST['code']))
                                {


                                        // Check the passwords entered are okay
                                        if (trim($_POST['password']) == trim($_POST['password2']))
                                        {
                                        
                                                // Reset password
                                                $reset_password_query = array("reset_password" => 0, "password" => md5($_POST['password']));

                                                if(!$db -> basic_update("users", $reset_password_query, "id='".$user_array['id']."'"))
                                                        $output -> add($template_global -> critical_error($lang['error_resetting_password']));
                                                
                                                else
                                                {
                                                        // Fix message
                                                        $lang['password_reset'] = $output -> replace_number_tags($lang['password_reset'], array(ROOT));
                                                        
                                                        $output -> add($template_global -> message($lang['password_page_title'], $lang['password_reset']));        
                                                }
                                                                                        
                                        }
                                        else
                                        {
                                                $output -> add($template_global -> normal_error($lang['error_password_not_matched']));
                                                show_lost_password_form2();
                                        }                

                                }
                                else
                                {
                                        $output -> add($template_global -> normal_error($lang['error_code_not_right']));
                                        show_lost_password_form2();
                                }                

                        }
                        else
                                $output -> add($template_global -> normal_error($lang['error_not_reseting_password']));
                
                }
                else
                {
                        $output -> add($template_global -> normal_error($lang['error_not_activated']));
                        show_lost_password_form2();
                }                
                                
        }
        else
        {
                $output -> add($template_global -> normal_error($lang['error_no_user']));
                show_lost_password_form2();
        }
                
}

?>
