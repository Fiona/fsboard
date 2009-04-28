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
 * Admin user functions
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */



// -----------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");





/**
 * Sanitise an e-mail address, will preemptively pull special chars out of an
 * address and return back the address.
 *
 * @var string $email The address to be sanitised.
 *
 * @return string The address after having been sanitised.
 */
function users_sanitise_email_address($email)
{

	$email = str_replace(" ", "", $email);
	$email = preg_replace(
		"#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#",
		"",
		$email
		);

	return $email;

}


/**
 * Does some basic validation on a username. (length and reserved characters)
 *
 * @var string $username The username to check
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_verify_username($username, $suppress_errors = False)
{

	global $db, $output, $lang;

	// Check username length
	if(_strlen($username) < 2 || _strlen($username) > 25)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_username_too_long']);
		return $lang['error_username_too_long'];
	}


	// Check for reserved characters in username
	$invalid_chars = array("'", "\"", "<!--", "\\");
	foreach($invalid_chars as $char)
	{
		if(strstr($username, $char))
		{
			if(!$suppress_errors)
				$output -> set_error_message($lang['error_username_reserved_chars']);
			return $lang['error_username_reserved_chars'];
		}
	}

	return True;

}


/**
 * Check that a username can be used for a new users.
 *
 * @var string $username The username to check
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_add_verify_username($username, $suppress_errors = False)
{

	global $db, $output, $lang;

	if(($error = users_verify_username($username, $suppress_errors)) !== True)
		return $error;

	// Check username has not been taken
	$db -> basic_select(
		array(
			"table" => "users",
			"what" => "username",
			"where" => ("LOWER(username) = '".
						$db -> escape_string(_strtolower($username))."'"),
			"limit" => 1
			)
		);

	if($db -> num_rows())
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_username_exists']);
		return $lang['error_username_exists'];
	}

	return True;

}


/**
 * Check that a username can be used if we're planning on changing
 * a username.
 *
 * @var string $current_username Old username to compare to
 * @var string $new_username The username we're changing to
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_edit_username_verify_username(
	$current_username,
	$new_username,
	$suppress_errors = False
	)
{

	global $db, $output, $lang;

	if(($error = users_verify_username($new_username, $suppress_errors)) !== True)
		return $error;

	// Check username is the same
	if($current_username == $new_username)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_username_is_same']);
		return $lang['error_username_is_same'];
	}

	// Check username has not been taken
	$db -> basic_select(
		array(
			"table" => "users",
			"what" => "username",
			"where" => ("LOWER(username) = '".
						$db -> escape_string(_strtolower($new_username))."'"),
			"limit" => 1
			)
		);

	if($db -> num_rows())
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_username_exists']);
		return $lang['error_username_exists'];
	}

	return True;

}



/**
 * Add a new user given some needed info. Should have already checked the info
 * using the provided validation function
 *
 * @var string $username Users desired username to check.
 * @var string $password The password that this user will use to log
 *   in. (Must be the unhashed value.)
 * @var string $email The e-mail address that will be assigned to this user.
 * @var int $usergroup The ID of the primary group that this user will be a
 *   member of.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|int Either False on failure or an int containing the new
 *   users ID
 */
function users_add_user(
	$username,
	$password,
	$email,
	$usergroup,
	$suppress_errors = False
	)
{

	global $db, $lang;

	$insert = $db -> basic_insert(
		array(
			"table" => "users",
			"data" => array(
				"username" => $username,
				"user_group" => $usergroup,
				"ip_address" => user_ip(),
				"password" => md5($password),
				"email" => $email,
				"registered" => TIME,
				"last_active" => TIME,
				"validate_id" => '0',
				"need_validate" => '0'
				)
			)
		);

	if(!$insert)
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['error_user_add']);
		return False;
	}

	// Get the ID number of the account just inserted
	$user_id = $db -> insert_id();

	// Add user to admin settings table
	$db -> basic_insert(
		array(
			"table" => "users_admin_settings",
			"data" => array("user_id" => $user_id)
			)
		);

	// Fix statistics data
	include_once ROOT."admin/common/funcs/stats.funcs.php";
	stats_update_single_stat("total_members", True);
	stats_update_single_stat("newest_member", True);

	return $user_id;

}


/**
 * Check that a password is valid.
 *
 * @var string $password The password to check
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_verify_password($password, $suppress_errors = False)
{

	global $lang, $output;

	// Check password length
	if(_strlen($password) < 4 || _strlen($password) > 14)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_password_too_long']);
		return $lang['error_password_too_long'];
	}

	return True;

}


/**
 * Check that an email is valid.
 *
 * @var string $email The email address to check
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_verify_email($email, $suppress_errors = False)
{

	global $lang, $output;

	// Check e-mail is valid
	if(
		!preg_match(
			"/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email
			)
		)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_invalid_email']);
		return $lang['error_invalid_email'];
	}

	return True;

}


/**
 * Get a users info based on their ID number.
 *
 * @var int $user_id User ID for the wanted user
 *
 * @return bool|array Either False on failure or an array containing the users
 *   data.
 */
function users_get_user_by_id($user_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "users u",
			"what" => "u.*, p.*",
			"where" => "u.id = ".(int)$user_id,
			"limit" => "1",

			"join" => "profile_fields_data p",
			"join_type" => "LEFT",
			"join_on" => "p.member_id = u.id"
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * Given a user id and an array of data this will update a user's info in the
 *   database.
 *
 * @var int $user_id ID for the user we're updating.
 * @var array $user_info Array of data. Keys are the column names.
 *	 will also accept custom profile field data. The key for custom fields should
 *   be in the format 'field_0' where 0 is the ID of the profile field.
 * @var array $custom_fields Optional array of info about custom fields. If not
 *   set the function will find the data out on it's own. (This is to just cut
 *   down on a query if it's not really necessary.)
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function users_update_user(
	$user_id,
	$user_info,
	$custom_fields = NULL,
	$suppress_errors = False
	)
{

	global $db, $output, $lang;

	// Get custom profile field info if we haven't provided
	if($custom_fields === NULL)
	{
		include_once ROOT."admin/common/funcs/profilefields.funcs.php";
		$custom_fields = profilefields_get_fields();
	}

	// Sort out any custom field data we have
	$custom_field_data = array();

	if(is_array($custom_fields) && count($custom_fields) > 0)
	{
		foreach($custom_fields as $key => $f_array)
		{
			if(isset($user_info['field_'.$key]))
			{
				// Store it for later so we can put it in after main info
				$custom_field_data["field_".$key] = $user_info['field_'.$key];
				// We have to get rid of them in the main query
				unset($user_info['field_'.$key]);
			}
		}
	}

	// Secondary groups should be flattened
	if(
		isset($user_info['secondary_user_group']) &&
		is_array($user_info['secondary_user_group'])
		)
		$user_info['secondary_user_group'] = implode(
			",", $user_info['secondary_user_group']
			);

	// Check if birthday year is out of acceptable bounds
	if(
		isset($user_info['birthday_year']) &&
		($user_info['birthday_year'] < 1901 ||
		 $user_info['birthday_year'] > date('Y'))
		)
		$user_info['birthday_year'] = "";

	// Need leading 0 on birthday month and day
	if(isset($user_info['birthday_month']) && $user_info['birthday_month'] < 10)
		$user_info['birthday_month'] = "0".$user_info['birthday_month'];

	if(isset($user_info['birthday_day']) && $user_info['birthday_day'] < 10)
		$user_info['birthday_day'] = "0".$user_info['birthday_day'];

	// Update the profile
	$q = $db -> basic_update(
		array(
			"table" => "users",
			"data" => $user_info,
			"where" =>  "id = ".(int)$user_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_updating_user']);
		return False;
	}

	// Deal with any custom field data we have left
	if(count($custom_field_data) > 0)
	{

		// We should get all the current data so we know if we should insert or update
		$current_custom_fields = array();

		$db -> basic_select(
			array(
				"table" => "profile_fields_data",
				"what" => "`member_id`",
				"where" => "member_id=".(int)$user_id,
				"limit" => 1
				)
			);
		
		// If there's something there update, otherwise insert
		if($db -> num_rows())
		{
			$update_query = $db -> basic_update(
				array(
					"table" => "profile_fields_data",
					"data" => $custom_field_data,
					"where" => "member_id=".(int)$user_id,
					"limit" => 1
					)
				);
		}
		else
		{
			$custom_field_data['member_id'] = $user_id;
			$update_query = $db -> basic_insert(
				array(
					"table" => "profile_fields_data",
					"data" => $custom_field_data
					)
				);
		}

		// Error if something went wrong
		if(!$update_query)
		{
			if(!$suppress_errors)
				$output -> set_error_message($lang['error_updating_user_profile_fields']);
			return False;
		}

	}


	// We might have to update the newest member if we've updated
	// the registration date.
	if(isset($user_info['registered']))
	{
		include_once ROOT."admin/common/funcs/stats.funcs.php";
		stats_update_single_stat("newest_member", True);
	}

	return True;

}


/**
 * Will change a specific users name to something else.
 *
 * @var id $user_id ID of the user whose username we're changing
 * @var string $current_username The current users username
 * @var string $new_username The username we're changing to
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_update_username(
	$user_id,
	$current_username,
	$new_username,
	$suppress_errors = False
	)
{
	
	global $db, $lang, $cache, $output;

	// Update the main user table
	$update_result = $db -> basic_update(
		array(
			"table" => "users", 
			"data" => array("username" => $new_username),
			"where" => "id = ".(int)$user_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_updating_user_username']);
		return $lang['error_updating_user_username'];
	}

	// Update the moderators table
	$update_result = $db -> basic_update(
		array(
			"table" => "moderators",
			"data" => array("username" => $new_username),
			"where" => "user_id = ".(int)$user_id
			)
		);
	
	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_updating_user_username']);
		return $lang['error_updating_user_username'];
	}

	// Update moderator cache
	$cache -> update_cache("moderators");

	// We might need to change the username saved in stats
	include_once ROOT."admin/common/funcs/stats.funcs.php";
	stats_update_single_stat("newest_member", True);

	return True;

}



/**
 * Will change a specific users password.
 *
 * @var id $user_id ID of the user whose username we're changing
 * @var string $new_password The desired password. (Raw unhashed password.)
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_update_password(
	$user_id,
	$new_password,
	$suppress_errors = False
	)
{
	
	global $db, $lang, $cache, $output;

	$update_result = $db -> basic_update(
		array(
			"table" => "users", 
			"data" => array("password" => md5($new_password)),
			"where" => "id = ".(int)$user_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_updating_user_password']);
		return $lang['error_updating_user_password'];
	}

	return True;

}


/**
 * Completely delete a user.
 *
 * @var id $user_id ID of the user whose username we're deleting.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_delete_user($user_id, $suppress_errors = False)
{
	
	global $db, $lang, $cache, $output;
	
	// Remove avatar
	// ....
        
	// Convert postings to guest
	// ....
        
	// Remove PM's
	// ....

	// Remove user info and profile data
	save_undelete_data(
		"users",
		"Deleted user ID ".$user_id." (Main data)", 
		"id = ".(int)$user_id,
		array("limit" => 1)
		);
	$db -> basic_delete(
		array(
			"table" => "users",
			"where" => "id = ".(int)$user_id,
			"limit" => 1
			)
		);

	save_undelete_data(
		"profile_fields_data",
		"Deleted user ID ".$user_id." (Custom profile fields data)",
		"member_id = ".(int)$user_id,
		array("limit" => 1)
		);
	$db -> basic_delete(
		array(
			"table" => "profile_fields_data",
			"where" => "member_id = ".(int)$user_id,
			"limit" => 1
			)
		);

	// Remove moderator info for this user
	save_undelete_data(
		"moderators",
		"Deleted user ID ".$user_id." (Moderator data)", 
		"user_id = ".(int)$user_id
		);
	$db -> basic_delete(
		array(
			"table" => "moderators",
			"where" => "user_id = ".(int)$user_id
			)
		);
        
	$cache -> update_cache("moderators");
        
	// Fix statistics data
	include_once ROOT."admin/common/funcs/stats.funcs.php";
	stats_update_single_stat("total_members", True);
	stats_update_single_stat("newest_member", True);

	return True;

}


/**
 * Helper function - This will take any started $form
 * object and add custom form fields to it.
 *
 * @param $form The form object.
 * @param $use_cache If False the function will go to the
 *   database for the custom field information.
 * @param $honour_required If a field is marked as required and
 *   this is true then the resulting field will check for being
 *   filled in.
 */
function users_add_custom_profile_form_fields(
	&$form,
	$use_cache = True,
	$honour_required = True
	)
{

	global $cache, $lang;

	if($use_cache)
		$fields = $cache -> cache['profile_fields'];
	else
	{
		include ROOT."admin/common/funcs/profilefields.funcs.php";
		$fields = profilefields_get_fields();
	}

	$form -> form_state['meta']['data_custom_fields'] = $fields;

	if(count($fields) > 0)
	{
        
		$form -> form_state['custom_fields_title'] = array(
			"title" => $lang['edit_user_custom_fields_title'],
			"type" => "message"
			);

		// We have some fields, go through them...
		foreach($fields as $key => $f_array)
		{

			$form -> form_state["#field_".$key] = array(
				"name" => $f_array['name'],
				"description" => $f_array['description'],
			);

			// Set value if we has
			if(isset($user_info['field_'.$key]))
				$form -> form_state['#field_'.$key]['value'] = $user_info['field_'.$key];

			// Set size if necessary
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

			if($f_array['must_be_filled'] && $honour_required)
				$form -> form_state["#field_".$key]['required'] = True;
			
		}
		
	}

}


/**
 * Takes field values and chucks it all in a handy query
 * array that is desigined to be passed to users_search_users. In reality
 * it is really just the info that gets given to $db -> basic_select.
 *
 * @param array $search_data Stuff to search by. The following entries are
 *   valid search fields for bulding the query -
 *   'username' : Usernames.
 *   'username_search' : Specify what type of username search to do.
 *     Setting to '1' specifies an exact match. '2' ends with. '3' begins with.
 *     Any other value or omitting this searching instead within usernames.
 *   'email' : E-mail addresses.
 *   'usergroup' : Primary user groups.
 *   'usergroup_secondary' : Secondary user groups.
 *   'title' : User defined titles.
 *   'signature' : Post signatures.
 *   'homepage' : Users homepage URL.
 *   'posts_g' : Users with a post count higher than this number.
 *   'posts_l' : Users with a post count lower than this number.
 *   'register_b' : Users with a registration date before this. (UNIX timestamp)
 *   'register_a' : Users with a registration date after this. (UNIX timestamp)
 *   'last_active_b' : Users who were active before this date. (UNIX timestamp)
 *   'last_active_a' : Users who were active after this date. (UNIX timestamp)
 *   'last_post_b' : Users who posted before this date. (UNIX timestamp)
 *   'last_post_a' : Users who posted after this date. (UNIX timestamp)
 * @param array $user_groups All usergroup info, should have been
 *   got from usergroups_get_groups()
 * @return array Finished array to be inserted into a query
 */
function users_build_user_search_query_array($search_data, $user_groups)
{

	global $db;

	// The basics of our query array
	$query_array = array(
		"what" => "u.*, u.id as user_id",
		"table" => "users u"
		);

	// We're going to build up a large where clause, we'll do this
	// by putting all the elements of the clause into an array and then
	// just imploding them with AND at the end.
	$where_clause = array();

	// Username
	if(isset($search_data['username']) && $search_data['username'])
	{

		$search_data['username'] = $db -> escape_string($search_data['username']);

		$search_info['username_search'] = (
			isset($search_info['username_search']) ?
			$search_info['username_search'] :
			NULL
			);

		// Different types of searching by username
		switch($search_info['username_search'])
		{
			// Exact match
			case "1":
				$where_clause[] = "u.`username` = '".$search_data['username']."'";
				break;

			// Ends with
			case "2":
				$where_clause[] = "u.`username` LIKE '%".$search_data['username']."'";
				break;

			// Begins with
			case "3":
				$where_clause[] = "u.`username` LIKE '".$search_data['username']."%'";
				break;

			// Contains
			default:
				$where_clause[] = "u.`username` LIKE '%".$search_data['username']."%'";
		}

	}


	// E-mail address
	if(isset($search_data['email']) && $search_data['email'])
		$where_clause[] = "u.`email` LIKE '%".
			$db -> escape_string($search_data['email'])."%'";

	// User group
	if(
		isset($search_data['usergroup']) &&
		$search_data['usergroup'] &&
		isset($user_groups[$search_data['usergroup']])
		)
		$where_clause[] = "u.`user_group` = ".intval($search_data['usergroup']);

	// Secondary user group
	if(
		isset($search_data['usergroup_secondary']) &&
		is_array($search_data['usergroup_secondary']) &&
		count($search_data['usergroup_secondary'])
		)
	{
		foreach($search_data['usergroup_secondary'] as $group_id)
			$where_clause[] = ("find_in_set(".
							   intval($group_id).
							   ", `secondary_user_group`)");
	}

	// Title
	if(isset($search_data['title']) && $search_data['title'])
		$where_clause[] = ("u.`title` LIKE '%".
						   $db -> escape_string($search_data['title'])."%'");

	// Signature
	if(isset($search_data['signature']) && $search_data['signature'])
		$where_clause[] = ("u.`signature` LIKE '%".
						   $db -> escape_string($search_data['signature'])."%'");
	// Homepage
	if(isset($search_data['homepage']) && $search_data['homepage'])
		$where_clause[] = ("u.`homepage` LIKE '%".
						   $db -> escape_string($search_data['homepage'])."%'");

	// Posts
	if(isset($search_data['posts_g']) && $search_data['posts_g'])
		$where_clause[] = "u.`posts` > ".intval($search_data['posts_g']);

	if(isset($search_data['posts_l']) && $search_data['posts_l'])
		$where_clause[] = "u.`posts` < ".intval($search_data['posts_l']);

	// Registration date
	if(isset($search_data['register_b']) && $search_data['register_b'])
		$where_clause[] = "u.`registered` < ".intval($search_data['register_b']);

	if(isset($search_data['register_a']) && $search_data['register_a'])
		$where_clause[] = "u.`registered` > ".intval($search_data['register_a']);

	// Last active date
	if(isset($search_data['last_active_b']) && $search_data['last_active_b'])
		$where_clause[] = "u.`last_active` < ".intval($search_data['last_active_b']);

	if(isset($search_data['last_active_a']) && $search_data['last_active_a'])
		$where_clause[] = "u.`last_active` > ".intval($search_data['last_active_a']);

	// Last post date
	if(isset($search_data['last_post_b']) && $search_data['last_post_b'])
		$where_clause[] = "u.`last_post_time` < ".intval($search_data['last_post_b']);

	if(isset($search_data['last_post_a']) && $search_data['last_post_a'])
		$where_clause[] = "u.`last_post_time` > ".intval($search_data['last_post_a']);

	// Save our built where clause
	$query_array['where'] = implode(" AND ", $where_clause);

	return $query_array;

}


/**
 * Will search for users given the query data to use.
 *
 * @var array $query_array Array to pass to $db -> basic_select for the query.
 *   Designed to have come from users_build_user_search_query_array.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return array|string Either an array of users or a string containing an error
 */
function users_search_users($query_array, $suppress_errors = False)
{

	global $db, $lang, $output;

	$q = $db -> basic_select($query_array);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['invalid_search']);
		return $lang['invalid_search'];
	}

	$users = array();

	while($u = $db -> fetch_array($q))
		$users[$u['id']] = $u;

	return $users;

}


/**
 * The user search form in the admin uses GET for search and the results page is
 * paginated, as a result all these GET parameters have to be passed over again.
 * This is a function designed to only be used in the admin, it will generate
 * the extra parameters appended to the URLs when you use pagination.
 *
 * @return string The built parameters.
 */
function users_build_user_search_url()
{

	$params = array(
		"username_search" => $_GET['username_search'],
		"username" => $_GET['username'],
		"email" => $_GET['email'],
		"usergroup" => $_GET['usergroup'],
		"title" => $_GET['title'],
		"signature" => $_GET['signature'],
		"homepage" => $_GET['homepage'],
		"posts_g" => $_GET['posts_g'],
		"posts_l" => $_GET['posts_l'],

		"register_b[day]" => $_GET['register_b']['day'],
		"register_b[month]" => $_GET['register_b']['month'],
		"register_b[year]" => $_GET['register_b']['year'],

		"register_a[day]" => $_GET['register_a']['day'],
		"register_a[month]" => $_GET['register_a']['month'],
		"register_a[year]" => $_GET['register_a']['year'],

		"last_active_b[day]" => $_GET['last_active_b']['day'],
		"last_active_b[month]" => $_GET['last_active_b']['month'],
		"last_active_b[year]" => $_GET['last_active_b']['year'],

		"last_active_a[day]" => $_GET['last_active_a']['day'],
		"last_active_a[month]" => $_GET['last_active_a']['month'],
		"last_active_a[year]" => $_GET['last_active_a']['year'],

		"last_post_a[day]" => $_GET['last_post_a']['day'],
		"last_post_a[month]" => $_GET['last_post_a']['month'],
		"last_post_a[year]" => $_GET['last_post_a']['year'],

		"last_post_b[day]" => $_GET['last_post_b']['day'],
		"last_post_b[month]" => $_GET['last_post_b']['month'],
		"last_post_b[year]" => $_GET['last_post_b']['year'],

		"submit" => $_GET['submit'],
		"form_user_search" => $_GET['form_user_search']
		);

	$params = array_map("urlencode", $params);
	$params_second_pass = array();
	
	foreach($params as $param_name => $value)
		$params_second_pass[] = $param_name."=".$value;

	return implode("&", $params_second_pass);

}


/**
 * Takes field values and chucks it all in a handy query
 * string for use when searching for users.
 *
 * @param array $search_info Stuff from the form to search.
 * @param array $extra Any other entries that need putting into the string.
 * @return string Finished string to be inserted into a query
 */
/*
function create_user_search_string($search_info, $extra = "")
{

        global $db;

        $query = array();
        
        if($extra)
                $query = $extra;

        // Username
        if($search_info['username'])
        {
                switch($search_info['username_search'])
                {
                        case "1":
                                $query[] = "u.`username` = '".$search_info['username']."'";
                                break;
                        case "2":
                                $query[] = "u.`username` LIKE '%".$search_info['username']."'";
                                break;
                        case "3":
                                $query[] = "u.`username` LIKE '".$search_info['username']."%'";
                                break;
                        default:
                                $query[] = "u.`username` LIKE '%".$search_info['username']."%'";
                }
        }

        // E-mail
        if($search_info['email'])
                $query[] = "u.`email` LIKE '%".$search_info['email']."%'";

        // User group
        $db -> basic_select("user_groups", "id,name");

        while($g_array = $db -> fetch_array())
                 $user_group_array[$g_array['id']] = $g_array['name'];

        if($search_info['usergroup'])
                if($user_group_array[$search_info['usergroup']])
                        $query[] = "u.`user_group` = '".$search_info['usergroup']."' ";

        // Secondary user group
        if(is_array($search_info['usergroup_secondary']) && count($search_info['usergroup_secondary']) > 0)
        {
                foreach($search_info['usergroup_secondary'] as $key => $val)
                        if($user_group_array[$key])
                                       $query[] = "find_in_set('".$key."', `secondary_user_group`) ";
        }

        // Title
        if($search_info['title'])
                $query[] = "u.`title` LIKE '%".$search_info['title']."%'";

        // Signature
        if($search_info['signature'])
                $query[] = "u.`signature` LIKE '%".$search_info['signature']."%'";

        // Homepage
        if($search_info['homepage'])
                $query[] = "u.`homepage` LIKE '%".$search_info['homepage']."%'";

        // Posts
        if($search_info['posts_g'])
                $query[] = "u.`posts` > ".$search_info['posts_g'];

        if($search_info['posts_l'])
                $query[] = "u.`posts` < ".$search_info['posts_g'];

        // Registered date
        list($day, $month, $year) = explode("-", $search_info['register_b']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`registered` < ".mktime(0, 0, 0, $month, $day, $year);

        list($day, $month, $year) = explode("-", $search_info['register_a']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`registered` > ".mktime(0, 0, 0, $month, $day, $year);

        // Last active date
        list($day, $month, $year) = explode("-", $search_info['last_active_b']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_active` < ".mktime(0, 0, 0, $month, $day, $year);

        list($day, $month, $year) = explode("-", $search_info['last_active_a']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_active` > ".mktime(0, 0, 0, $month, $day, $year);

        // Last post date
        list($day, $month, $year) = explode("-", $search_info['last_post_b']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_post_time` < ".mktime(0, 0, 0, $month, $day, $year);

        list($day, $month, $year) = explode("-", $search_info['last_post_a']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_post_time` > ".mktime(0, 0, 0, $month, $day, $year);


        // *****************************
        // Custom profile fields! :D
        // *****************************
        $db -> basic_select("profile_fields", "id");

        while($p_array = $db -> fetch_array())
                if($_POST['field_'.$p_array['id']])
                        $query[] = "p.`field_".$p_array['id']."` LIKE '%".$_POST['field_'.$p_array['id']]."%'";

        // *****************************
        // Finish and send back
        // *****************************
        $query_string = "";
        
        if(count($query) > 0)
                $query_string = " WHERE ".implode(" AND ", $query);

        return $query_string;
        
}
*/