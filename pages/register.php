<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2007 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License.
See gpl.txt for a full copy of this license.
--------------------------------------------------------------------------
*/

/**
 * Registration page
 * Deals with creating new accounts validation of them by the user
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//$template_register = load_template_class("template_register");

load_language_group("register");


//***********************************************
// What are we doing?
//***********************************************
$secondary_mode = (isset($page_matches['mode'])) ? $page_matches['mode'] : "";

$output -> page_title = $lang['register_page_title'];

switch($secondary_mode)
{

	case "activateform":
		form_activation();
        break;

	case "activate":
		activate_account_url();
        break;

	default:
		form_register();
        
}


/**
 * Display the account registration form
 */
function form_register()
{

	global $cache, $template_register, $template_global, $output, $lang, $user;

	// If we are logged in, we need a error
	if(!$user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_already_logged_in']));
		return;
	}

	// Check if the admin has disabled registration
	if($cache -> cache['config']['reg_disable_new'])
	{
		$output -> add($template_global -> normal_error($lang['error_no_register']));
		return;
	}
        
	// If admin has rules turned on, we need to show them
	if($cache -> cache['config']['rules_on'])
		$before = $template_global -> message($cache -> cache['config']['rules_title'], $cache -> cache['config']['rules_text']);
	else
		$before = "";
        
	// Plugin
	//hook_register_before_reg_form($entered_data);

		
	// ------------------
	// Our form
	// ------------------
	$form = new form(array(
        "meta" => array(
			"name" => "register",
        	"title" => $lang['new_user_registration'],
        	"description" => $lang['register_notice'],
			"before" => $before,
			"validation_func" => "form_register_validate",
			"complete_func" => "form_register_complete"	
        ),
        
        "#username" => array(
        	"type" => "text",
        	"name" => $lang['desired_username'],
        	"description" => $lang['reg_username_notice'],
        	"required" => True
        ),
        "#email" => array(
        	"type" => "text",
        	"name" => $lang['email_address'],
        	"required" => True
        ),
        "#password" => array(
        	"type" => "password",
        	"name" => $lang['desired_password'],
        	"description" => $lang['reg_password_notice'],
        	"required" => True
        ),
        "#password2" => array(
        	"type" => "password",
        	"name" => $lang['desired_password2'],
        	"description" => $lang['reg_password_notice2'],
        	"identical_to" => "#password"
        )
	));
	
	
	// ------------------
	// Custom profile fields
	// ------------------
	if(count($cache -> cache['profile_fields']) > 0)
	{
        
		// We have some fields, go through them...
		foreach($cache -> cache['profile_fields'] as $key => $f_array)
		{
			
			// show on form?
			if(!$f_array['show_on_reg'] || $f_array['admin_only_field'])
				continue;

			$form -> form_state["#field_".$key] = array(
				"name" => $f_array['name'],
				"description" => $f_array['description'],
			);

			if($f_array['field_type'] != "yesno" && $f_array['size'])
				$form -> form_state["#field_".$key]['size'] = $f_array['size'];
			
			// What input?
			switch($f_array['field_type'])
			{
					
				case "yesno":
					$form -> form_state["#field_".$key]['type'] = "yesno";
					break;

				case "textbox":
					$form -> form_state["#field_".$key]['type'] = "textarea";
					break;

				case "dropdown":
					$dropdown_values = explode('|', $f_array['dropdown_values']);
					$dropdown_text = explode('|', $f_array['dropdown_text']);

					$options = array();
                                        
					foreach($dropdown_values as $key2 => $val)
						$options[trim($val)] = trim($dropdown_text[$key2]);

					$form -> form_state["#field_".$key]['type'] = "dropdown";
					$form -> form_state["#field_".$key]['options'] = $options;
					break;
					
				case "text":
				default:
					$form -> form_state["#field_".$key]['type'] = "text";
					
			}

			if($f_array['must_be_filled'])
				$form -> form_state["#field_".$key]['required'] = True;
			
		}
		
	}
	
	
	// Chuck submit on the end
	$form -> form_state["#submit"] = array(
		"type" => "submit",
		"value" => $lang['register_submit']
	);
	
	$output -> add($form -> render());

	// Plugin
	//hook_register_after_reg_form($entered_data);

}


/**
 * Validation function for the registration form
 *
 * @param object $form
 */
function form_register_validate($form)
{
	
	global $lang, $db, $cache;

			
	// ----------------
	// Check username
	// ----------------
	if(!isset($form -> form_state['#username']['error']))
	{
		
		// length
		if(_strlen($form -> form_state['#username']['value']) < 2 || _strlen($form -> form_state['#username']['value']) > 25)
			$form -> set_error("username", $lang['error_username_too_long']);        
			
		// Check for reserved characters in username
		foreach(array("'", "\"", "<!--", "\\") as $char)
			if(strstr($form -> form_state['#username']['value'], $char))
				$form -> set_error("username", $lang['error_username_reserved_chars']);        

        // Check username has been taken
        $db -> basic_select(array(
        	"table" => "users",
        	"what" => "username",
        	"where" => "lower(username)='".$db -> escape_string(_strtolower($form -> form_state['#username']['value']))."'",
        	"limit" => 1
       	)); 
        
        if($db -> num_rows())
			$form -> set_error("username", $lang['error_username_exists']);        

	}
	
	
	// ----------------
	// Check email
	// ----------------
	if(!isset($form -> form_state['#email']['error']))
	{
	
		// Sort out invalid e-mail characters
		$form -> form_state['#email']['value'] = str_replace(" ", "", $form -> form_state['#email']['value']);
		$form -> form_state['#email']['value'] = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $form -> form_state['#email']['value']);

			
		// Check e-mail is valid
		if(!isset($form -> form_state['#email']['error']))
			if(!preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $form -> form_state['#email']['value']))
			$form -> set_error("email", $lang['error_invalid_email']);        
		
		
		// Check if e-mail is taken
		if(!$cache -> cache['config']['reg_duplicate_emails'])
        {

	        $db -> basic_select(array(
	        	"table" => "users",
	        	"what" => "email",
	        	"where" => "lower(email)='".$db -> escape_string(_strtolower($form -> form_state['#email']['value']))."'",
	        	"limit" => 1
	       	)); 
                
			if($db -> num_rows())
				$form -> set_error("email", $lang['error_email_exists']);        
       
        }
        
	}
  	
	
	// ----------------
	// Check password length
	// ----------------
	if(!isset($form -> form_state['#password']['error']))
		if(_strlen($form -> form_state['#password']['value']) < 4 || _strlen($form -> form_state['#password']['value']) > 14)
			$form -> set_error("password", $lang['error_password_too_long']);        
	
}



/**
 * The completion function for the registration form
 *
 * @param object $form
 */
function form_register_complete($form)
{
	
	global $cache, $db, $lang, $output, $template_global;
	
	// --------------
	// Generate a unique activation code        
	// --------------
	$validate_code = _substr(md5(uniqid(rand(), true)), 0, 13); 

	if($cache -> cache['config']['reg_validation'])
		$user_group = USERGROUP_VALIDATING;
	else
		$user_group = USERGROUP_MEMBERS;

		
	// --------------
	// Attempt to add this account
	// --------------
	$res = $db -> basic_insert(array(
		"table" => "users",
		"data" => array(
			"username" => $form -> form_state['#username']['value'],
			"user_group" => $user_group,
			"ip_address" => user_ip(),
			"password" => md5($form -> form_state['#password']['value']),
			"email" => $form -> form_state['#email']['value'],
			"registered" => TIME,
			"last_active" => TIME,
			"validate_id" => $validate_code,
			"need_validate" => $cache -> cache['config']['reg_validation']
		)	
	));

	if(!$res)
	{
		$output -> add($template_global -> normal_error($lang['error_registration_add']));
		return;
	}

	$user_id = $db -> insert_id();

	
	// --------------
	// Custom profile fields
	// --------------
	if(count($cache -> cache['profile_fields']) > 0)
	{
                
		$custom_stuff = array();
                        
		// We have some fields, go through them...
		foreach($cache -> cache['profile_fields'] as $key => $f_array)
		{
        
			// show on form?
			if(!$f_array['show_on_reg'] || $f_array['admin_only_field'])
				continue;

			$custom_stuff["field_".$key] = $form -> form_state["#field_".$key]['value'];

		}
                        
		// Got some?
		if(count($custom_stuff))
		{
                               
			// Check if the entry exists yet (it shouldn't, but whatever)
			$db -> basic_select(array(
				"table" => "profile_fields_data",
				"what" => "member_id",
				"where" => "member_id = ".(int)$user_id,
				"limit" => 1
			));			
			
			if($db -> num_rows())
				$db -> basic_update(array(
					"table" => "profile_fields_data",
					"where" => "member_id = ".(int)$user_id,
					"data" => $custom_stuff,
					"limit" => 1
				));
			else
			{
				$custom_stuff['member_id'] = $user_id;
				$db -> basic_insert(array("table" => "profile_fields_data", "data" => $custom_stuff));
			}
			                                
		}
		
	}


	// --------------
	// Send validation e-mail if necessary
	// --------------
	if($cache -> cache['config']['reg_validation'])
	{        
                
		// Get the message from the language file
		$email_message = $lang['email_activate'];
                        
		// We need to replace certain things so the email sends the right info. Kay?
		$email_message = str_replace(
			array(
				'<forum_name>',
				'<activate_url>',
				'<activate_form_url>',
				'<activate_code>',
				'<user_id>'
			),
			array(
				$cache -> cache['config']['board_name'],
				$cache -> cache['config']['board_url']."/register/activate/".$user_id."/".$validate_code."/",
				$cache -> cache['config']['board_url']."/register/activate/",
				$validate_code,
				$user_id
			),
			$email_message
		);

		// Send the e-mail
		$mail = new email;
		$mail -> send_mail($form -> form_state['#email']['value'], $lang['email_activate_subject'], $email_message);

		// Fix message
		$lang['reg_sent_mail'] = $output -> replace_number_tags($lang['reg_sent_mail'], l("login/"));

		// Print message telling user what to do
		$output -> add($template_global -> message($lang['account_registration'], $lang['reg_sent_mail']));        

	}
	else
	{
		$output -> add($template_global -> message($lang['account_registration'], $lang['reg_completed']));        
		include ROOT."pages/login.php";
	}

	
	// --------------
	// Inform administrator if necessary
	// --------------
	if($cache -> cache['config']['reg_notify_admin'])
	{

		// Get the message from the language file
		$email_message = $lang['email_admin_registration'];
                        
		// We need to replace certain things so the email sends the right info. Kay?
		$email_message = str_replace(
			array(
				'<forum_name>',
				'<new_username>'
			),
			array(
				 $cache -> cache['config']['board_name'],
				 $form -> form_state['#username']['value']
			),
			$email_message
		);

		// Send the e-mail
		$mail = new email;
		$mail -> send_mail($cache -> cache['config']['admin_email'], $lang['email_admin_registration_subject'], $email_message);
                        
	}
                	
}


/**
 * Doing account validation by URL
 */
function activate_account_url()
{

	global $user, $lang, $output, $template_global, $db;
        

	// If we are logged in, we need a error
	if(!$user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_already_logged_in']));
		return;
	}

	// If user ID isn't given, set it to one from the URL
	if(!isset($_GET['user']) || !$_GET['user'] || !isset($_GET['code']) || !$_GET['code'])
	{
		form_activation();
		return;	
	}
	
	$user_id = (int)$_GET['user'];
	$validation_code = trim($_GET['code']);
	
	$db -> basic_select(array(
		"table" => "users",
		"what" => "id, need_validate, validate_id",
		"where" => "id = ".(int)$user_id,
		"limit" => 1
	));

	// Check if user exists
	if(!$db -> num_rows())
	{
		$output -> add($template_global -> normal_error($lang['error_no_user']));
		return;
	}

	$user_array = $db -> fetch_array();
                
	// Check it needs activating
	if(!$user_array['need_validate'])
	{
		$output -> add($template_global -> normal_error($lang['error_already_activated']));
		include ROOT."pages/login.php";
		return;
	}

	// Check if the code is right
	if($user_array['validate_id'] != $validation_code)
	{
		$output -> add($template_global -> normal_error($lang['error_bad_activation_code']));                                
		form_activation($user_id);
		return;
	}

	activate_account($user_id);
        
}



/*
 * Manually activate with a form
 * 
 * @param int $user_id
 */
function form_activation($user_id = NULL)
{

	global $user, $lang, $output, $template_global, $template_register, $db, $page_matches;

	// Check if we actually want the url version
	if(isset($page_matches['user_id']) && $page_matches['user_id'] && isset($page_matches['activate_code']) && $page_matches['activate_code'])
	{

	}

	// If we are logged in, we need a error
	if(!$user -> is_guest)
	{
		$output -> add($template_global -> normal_error($lang['error_already_logged_in']));
		return;
	}

	// If user ID isn't given, set it to one from the URL
	if(!$user_id)
		$user_id = (isset($_GET['user'])) ? (int)$_GET['user'] : NULL;
	
	if($user_id !== NULL)
	{
		
		$db -> basic_select(array(
			"table" => "users",
			"what" => "id, need_validate, validate_id",
			"where" => "id = ".(int)$user_id,
			"limit" => 1
		));
	
		// Check if user exists
		if(!$db -> num_rows())
		{
			$output -> add($template_global -> normal_error($lang['error_no_user']));
			return;
		}
	
		$user_array = $db -> fetch_array();
	                
		// Check it needs activating
		if(!$user_array['need_validate'])
		{
			$output -> add($template_global -> normal_error($lang['error_already_activated']));
			include ROOT."pages/login.php";
			return;
		}

	}
	
	// Activation form
	$form = new form(array(
        "meta" => array(
			"name" => "account_activation",
        	"title" => $lang['user_activation'],
        	"description" => $lang['activate_notice'],
			"validation_func" => "form_activation_validate",
			"complete_func" => "form_activation_complete"	
        ),
        
        "#userid" => array(
        	"type" => "int",
        	"name" => $lang['user_id'],
        	"required" => True,
        	"value" => $user_id
        ),
        "#code" => array(
        	"type" => "text",
        	"name" => $lang['activation_code'],
        	"required" => True
        ),
        "#submit" => array(
        	"type" => "submit",
        	"value" => $lang['activate_submit']
        )
	));
	
	$output -> add($form -> render());	

}



/**
 * Validation function for the activation form
 *
 * @param object $form
 */
function form_activation_validate($form)
{
	
	global $lang, $db;
	
	$db -> basic_select(array(
		"table" => "users",
		"what" => "validate_id",
		"where" => "id = ".(int)$form -> form_state['#userid']['value'],
		"limit" => 1
	));

	$wanted_code = $db -> result();
	
	if($form -> form_state['#code']['value'] != $wanted_code)
		$form -> set_error("code", $lang['error_bad_activation_code_form']);
	
}



/**
 * Completion function for the activation form
 *
 * @param object $form
 */
function form_activation_complete($form)
{
	activate_account($form -> form_state['#userid']['value']);
}



/**
 * Simple function will activate accounts for us
 *
 * @param int $account_id
 */
function activate_account($account_id)
{

	global $db, $lang,$output, $template_global;
	
	$validate_query = array('need_validate' => 0, 'user_group' => 3);

	$res = $db -> basic_update(array(
		"table" => "users",
		"where" => "id = ".(int)$account_id,
		"limit" => 1,
		"data" => array(
			"need_validate" => 0,
			"user_group" => USERGROUP_MEMBERS
		)
	));

	if(!$res)
	{
		$_POST = array();
		$output -> add($template_global -> normal_error($lang['error_activation_failed']));                                
		form_activation($account_id);                                
	}                                
	else
	{
		$output -> add($template_global -> message($lang['account_registration'], $lang['reg_completed']));
		include ROOT."pages/login.php";         
	}
	
}


?>
