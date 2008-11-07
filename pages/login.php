<?php
/*
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2007 Fiona Burrows (fiona@fsboard.net)

SBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License.
See gpl.txt for a full copy of this license.
--------------------------------------------------------------------------
*/

/**
 * Login page
 * Lets the user login, logout and retrieve a lost password.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


$template_login = load_template_class("template_login");

load_language_group("login");


//***********************************************
// What are we doing?
//***********************************************
$secondary_mode = (isset($page_matches['mode'])) ? $page_matches['mode'] : "";

switch($secondary_mode)
{
	
	case "logout":
		do_logout();
		$output -> page_title = $lang['logout_page_title'];
		break;

	case "lost_password":
		form_lost_password();
		$output -> page_title = $lang['password_page_title'];
		break;

	case "lost_password_step_2":
		form_lost_password_step2();
		$output -> page_title = $lang['password_page_title'];
		break;

	default:
		form_login($template_login);
		$output -> page_title = $lang['login_page_title'];
		break;
        
}


/**
 * Display the login form
 *
 * @param object $template_login
 */
function form_login($template_login = NULL)
{

	// This was necessary because the file being included from
	// the regstration file was causing the $template_login var
	// to not be registered as a global. If everything was built
	// as a class this wouldn't be an issue - but that's a refactor
	// for another day
	if($template_login === NULL)
        global $template_login;

	global $template_global, $output, $lang, $user;

	// If we are logged in, we need a error
	if(!$user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_already_logged_in']));
		return;
	}

	
	$form = new form(array(
        "meta" => array(
			"name" => "login",
        	"title" => $lang['login_page_title'],
        	"description" => $output -> replace_number_tags($lang['enter_login_info'], l("register/")),
			"validation_func" => "form_login_validate",
			"complete_func" => "form_login_complete"	
        ),
        
        "#username" => array(
        	"type" => "text",
        	"name" => $lang['login_username'],
        	"required" => True
        ),
        "#password" => array(
        	"type" => "password",
        	"name" => $lang['login_password'],
        	"description" => "<a href=\"".l("login/lost_password/")."\">".$lang['login_forgot_password']."</a>",
        	"required" => True
        ),
        "#stay_logged_in" => array(
        	"type" => "checkbox",
        	"name" => $lang['stay_logged_in']
        ),
        "#invisible" => array(
        	"type" => "checkbox",
        	"name" => $lang['login_invisible']
        ),
		"#submit" => array(
			"type" => "submit",
			"value" => $lang['login_submit']
		)        
	));	
	
	$output -> add($form -> render());
	
}


/*
 * Validation function for the login form
 * 
 * @param object $form
 */
function form_login_validate($form)
{
	
	global $db, $lang, $output;
	
	if(!$form -> form_state['#username']['value'] || !$form -> form_state['#password']['value'])
		return;
		
	// Check user exists
	$db -> basic_select(array(
		"table" => "users",
		"what" => "`password`, `id`, `need_validate`, `username`, `user_group`",
		"where" => "LOWER(`username`) = '".$db -> escape_string(_strtolower($form -> form_state['#username']['value']))."'",
		"limit" => 1
	));
	
	if(!$db -> num_rows())
	{
		$form -> set_error("username", $lang['error_no_user']);        
		return;
	}

	$form -> form_state['user_data'] = $db -> fetch_array();
        
	// Check password
	if($form -> form_state['user_data']['password'] != md5($form -> form_state['#password']['value']))
	{
		$lang['error_wrong_password'] = $output -> replace_number_tags($lang['error_wrong_password'], l("login/lost_password/"));
		$form -> set_error("password", $lang['error_wrong_password']);        
		return;
	}

	
	// Check if account is waiting for validation
	if($form -> form_state['user_data']['need_validate'])
	{
		$form -> set_error("username", $lang['error_need_validation']);        
		return;
	}
	
}


/**
 * Completing login procedure
 *
 * @param unknown_type $form
 */
function form_login_complete($form)
{

	global $db, $lang, $output, $user;
	
	// Update last login and IP address
	$res = $db -> basic_update(array(
		"table" => "users",
		"where" => "id = ".(int)$form -> form_state['user_data']['id'],
		"data" => array(
			"last_active" => TIME,
			"ip_address" => user_ip(),
			"reset_password" => "0"
		),
		"limit" => 1
	));
	
	if(!$res)
	{
		$output -> add($template_global -> normal_error($lang['error_logging_in']));
		return;	
	}
	

	// Playin' wit cookies
	if(!$form -> form_state['#stay_logged_in']['value'])
		$form -> form_state['#stay_logged_in']['value'] = 0;
		
	fs_setcookie("user_id", $form -> form_state['user_data']['id'], (int)$form -> form_state['#stay_logged_in']['value']);
	fs_setcookie("password", $form -> form_state['user_data']['password'], (int)$form -> form_state['#stay_logged_in']['value']);                                
        
	if($form -> form_state['#invisible']['value'])
		fs_setcookie("anonymous", "1", (int)$form -> form_state['#stay_logged_in']['value']);                                

		
	// Deal with session stuff
	$s = $user -> get_cookie("session");
	$session_id = ($s) ? $s : 0;

	// Build data that will go into the session
	$session_update_info = array(
			"user_id" => $form -> form_state['user_data']['id'],
			"username" => $form -> form_state['user_data']['username'],
			"user_group" => $form -> form_state['user_data']['user_group'],	
			"last_active" => TIME,
			"ip_address" => user_ip(),
			"browser" => $_SERVER['HTTP_USER_AGENT'],
			"location" => 0
		);
	
	$del_extra = "";
	
	if(!$session_id)
		$session_update_info['id'] = md5(uniqid(rand(), true));
	else
		$del_extra =  "and id <> '".$db -> escape_string($session_id)."'";
									
	// remove old sessions
	$db -> save_shutdown_query(
		$db -> basic_delete(array(
			"table" => "sessions",
			"where" => "`ip_address` = '".user_ip()."'".$del_extra,
			"just_return" => True
		))
	);
	
	// We have a sess so update the older one
	if($session_id)
		$db -> save_shutdown_query(
			$db -> basic_update(array(
				"table" => "sessions",
				"data" => $session_update_info,
				"where" => "id = '".$db -> escape_string($session_id)."'",
				"just_return" => true
			))
		);
	// Otherwise add a new one
	else
		$db -> save_shutdown_query(
			$db -> basic_insert(array(
				"table" => "sessions",
				"data" => $session_update_info,
				"just_return" => true						
			))
		);			


	fs_setcookie("session", $session_id, false);
        
	$user -> session = $session_id;
	$user -> user_id = $form -> form_state['user_data']['id'];


	// Redirect the user
	$form -> form_state['meta']['redirect'] = array(
		"url" => l("/"),
		"message" => $lang['logged_in']
	);
	
}



/**
 * Try to log out
 */
function do_logout()
{

	global $db, $user, $output, $template_global, $lang;
        
	// If we are not logged in, we need a error
	if($user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_not_logged_in']));
		return;
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
			$db -> basic_update(array(
				"table" => "sessions",
				"where" => "id = '".$db -> escape_string($session_id)."'",
				"data" => array(
					"user_id" => 0,
					"username" => 0,
					"user_group" => USERGROUP_GUEST,
					"last_active" => TIME,
					"ip_address" => user_ip(),
					"browser" => $_SERVER['HTTP_USER_AGENT'],
					"location" => '0'				
				),
				"just_return" => True 
			))
		);
		                
	// Redirect the user
	$output -> redirect(l("/"), $lang['logged_out']);

}



/**
 * If people with no memory have forgotten their password
 * they come to this form, and try to retrieve it.
 */
function form_lost_password()
{

	global $template_global, $output, $lang, $user;

	// If we are logged in, we need a error
	if(!$user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_already_logged_in']));
		return;
	}	

	$form = new form(array(
        "meta" => array(
			"name" => "lost_password",
        	"title" => $lang['password_page_title'],
        	"description" => $lang['reset_password_form_notice'],
			"validation_func" => "form_lost_password_validate",
			"complete_func" => "form_lost_password_complete"	
        ),
        
        "#username" => array(
        	"type" => "text",
        	"name" => $lang['password_form_username'],
        	"required" => True
        ),
		"#submit" => array(
			"type" => "submit",
			"value" => $lang['password_form_submit']
		)        
	));	
	
	$output -> add($form -> render());	

}


/**
 * Lost password form validation function
 *
 * @param object $form
 */
function form_lost_password_validate($form)
{
	
	global $db, $lang;
	
	if(!$form -> form_state['#username']['value'])
		return;
	
	$db -> basic_select(array(
		"table" => "users",
		"what" => "id, username, email, need_validate",
		"where" => "lower(username) = '"._strtolower($db -> escape_string($form -> form_state['#username']['value']))."'",
		"limit" => 1
	));
	
	if(!$db -> num_rows())
	{
		$form -> set_error("username", $lang['error_no_user']);        
		return;		
	}
	
	$form -> form_state['user_data'] = $db -> fetch_array(); 

	if($form -> form_state['user_data']['need_validate'])
		$form -> set_error("username", $lang['error_not_activated']);        

}


/**
 * Lost password form completion function
 *
 * @param object $form
 */
function form_lost_password_complete($form)
{

	global $db, $lang, $cache, $output, $template_global;
	
	// Generate a unique code        
	$validate_code = _substr(md5(uniqid(rand(), true)), 0, 13); 
	
                        
	// Set we're allowed to retrieve password, and the code
	$db -> basic_update(array(
		"table" => "users",
		"data" => array(
			"reset_password" => "1",
			"validate_id" => $validate_code
		),
		"where" => "id = ".$form -> form_state['user_data']['id']
	));


	// Prepare the email
	$lang['email_lost_password'] = str_replace(
		array(
			"<username>",
			"<forum_name>",
			"<password_url>",
			"<password_form_url>",
			"<validate_code>",
			"<user_id>"
		),
		array(
			$form -> form_state['user_data']['username'],
			$cache -> cache['config']['board_name'],
			$cache -> cache['config']['board_url']."/login/lost_password_step_2/".$form -> form_state['user_data']['id']."/".$validate_code."/",
			$cache -> cache['config']['board_url']."/login/lost_password_step_2/",
			$validate_code,
			$form -> form_state['user_data']['id']
		),
		$lang['email_lost_password']
	);
	

	// Send the e-mail
	$mail = new email;
	$mail -> send_mail($form -> form_state['user_data']['email'], $lang['email_lost_password_subject'], $lang['email_lost_password']);

	$output -> add($template_global -> message($lang['password_page_title'], $lang['password_sent_mail']));
	        	
}



//***********************************************
// When getting to this form, users have to now input a new password 
//***********************************************
function form_lost_password_step2()
{

	global $template_global, $output, $lang, $user, $page_matches;

	// If we are logged in, we need a error
	if(!$user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_already_logged_in']));
		return;
	}	

	$form = new form(array(
        "meta" => array(
			"name" => "lost_password_step2",
        	"title" => $lang['password_page_title'],
        	"description" => $lang['reset_password_form2_notice'],
			"validation_func" => "form_lost_password_step2_validate",
			"complete_func" => "form_lost_password_step2_complete"	
        )
	));
        
	if(!isset($page_matches['user_id']) || !isset($page_matches['activate_code']) || !trim($page_matches['user_id']) || !trim($page_matches['activate_code']))
	{
		 
		$form -> form_state["meta"]["description"] = $lang['reset_password_form2_long_notice'];
		
        $form -> form_state["#userid"] = array(
        	"type" => "int",
        	"name" => $lang['password_form_userid'],
        	"required" => True
        );
        $form -> form_state["#code"] = array(
        	"type" => "text",
        	"name" => $lang['password_form_code'],
        	"required" => True
        );        
        $form -> form_state["submsg"] = array(
        	"type" => "message",
        	"title" => $lang['reset_password_form2_subtitle'],
        	"description" => $lang['reset_password_form2_notice']
        );
        
	}
	
	$form -> form_state["#password"] = array(
        "type" => "password",
        "name" => $lang['password_form_password'],
        "required" => True
	);
	$form -> form_state["#password2"] = array(
        "type" => "password",
        "name" => $lang['password_form_password2'],
        "identical_to" => "#password"
	);
        
	$form -> form_state["#submit"] = array(
		"type" => "submit",
		"value" => $lang['password_form2_submit']
	);
	
	$output -> add($form -> render());
	
}


/**
 * This is the validation function for last step of lost passwords
 * 
 * @param $form object 
 */
function form_lost_password_step2_validate($form)
{

	global $db, $lang, $page_matches;
	
	if(!$form -> form_state['#password']['value'] || !$form -> form_state['#password2']['value'])
		return;
		
	// Get the user ID and code
	if(!isset($page_matches['user_id']) || !isset($page_matches['activate_code']) || !trim($page_matches['user_id']) || !trim($page_matches['activate_code']))
	{
		if(!$form -> form_state['#userid']['value'] || !$form -> form_state['#code']['value'])
			return;
			
		$uid = $form -> form_state['#userid']['value'];
		$code = $form -> form_state['#code']['value'];
	}	
	else
	{
		$uid = trim($page_matches['user_id']);
		$code = trim($page_matches['activate_code']);
	}
	
	// check user exists
	$db -> basic_select(array(
		"table" => "users",
		"what" => "id, need_validate, reset_password, validate_id",
		"where" => "id = ".(int)$uid,
		"limit" => 1
	));
	
	if(!$db -> num_rows())
	{
		$form -> set_error(null, $lang['error_no_user']);        
		return;			
	}

	$form -> form_state['user_data'] = $db -> fetch_array();
                
	// Check validation
	if($form -> form_state['user_data']['need_validate'])
	{
		$form -> set_error(null, $lang['error_not_activated']);        
		return;			
	}

	// Check we're actually resetting
	if(!$form -> form_state['user_data']['reset_password'])
	{
		$form -> set_error(null, $lang['error_not_reseting_password']);        
		return;			
	}

	// Compare validation code
	if($form -> form_state['user_data']['validate_id'] != $code)
		$form -> set_error(null, $lang['error_code_not_right']);        
                          	
}


/**
 * This is the completion function for last step of lost passwords
 * 
 * @param $form object 
 */
function form_lost_password_step2_complete($form)
x{

	global $db, $lang, $template_global, $output;

	$res = $db -> basic_update(array(
		"table" => "users",
		"where" => "id = ".(int)$form -> form_state['user_data']['id'],
		"data" => array(
			"reset_password" => 0,
			"password" => md5($form -> form_state['#password']['value'])
		),
		"limit" => 1
	));
	
	if(!$res)
	{
		$output -> add($template_global -> normal_error($lang['error_resetting_password']));
		return;		
	}

	$lang['password_reset'] = $output -> replace_number_tags($lang['password_reset'], l("login/"));
	$output -> add($template_global -> message($lang['password_page_title'], $lang['password_reset']));
                                                	
}

?>