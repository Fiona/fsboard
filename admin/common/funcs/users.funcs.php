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



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");





/**
 * Sanitise an e-mail address, will preemptively pull special chars out of an address
 * and return back the address.
 *
 * @var string $email The address to be sanitised.
 *
 * @return string The address after having been sanitised.
 */
function users_sanitise_email_address($email)
{

	$email = str_replace( " ", "", $email);
	$email = preg_replace( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $email);

	return $email;

}


/**
 * Does some basic validation on a username. (length and reserved characters)
 *
 * @var string $username The username to check
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
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
			"where" => "LOWER(username) = '".$db -> escape_string(_strtolower($username))."'",
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_edit_username_verify_username($current_username, $new_username, $suppress_errors = False)
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
			"where" => "LOWER(username) = '".$db -> escape_string(_strtolower($new_username))."'",
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
 * Add a new user given some needed info. Should have already checked the info using
 * the provided validation function
 *
 * @var string $username Users desired username to check.
 * @var string $password The password that this user will use to log in. (Unhashed)
 * @var string $email The e-mail address that will be assigned to this user.
 * @var int $usergroup The ID of the primary group that this user will be a member of.
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|int Either False on failure or an int containing the new users ID
 */
function users_add_user($username, $password, $email, $usergroup, $suppress_errors = False)
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_verify_email($email, $suppress_errors = False)
{

	global $lang, $output;

	// Check e-mail is valid
	if(!preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email))
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
 * @return bool|array Either False on failure or an array containing the users data.
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
 * Given a user id and an array of data this will update a user's info in the database.
 *
 * @var int $user_id ID for the user we're updating.
 * @var array $user_info Array of data. Keys are the column names.
 *		Will also accept custom profile field data. The key for custom fields should be
 *		field_0 where 0 is the ID of the profile field.
 * @var array $custom_fields Optional array of info about custom fields. If not set the
 *		function will find the data out on it's own. (This is to just cut down on a query
 *		if it's not really necessary.)
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function users_update_user($user_id, $user_info, $custom_fields = NULL, $suppress_errors = False)
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
	if(isset($user_info['secondary_user_group']) && is_array($user_info['secondary_user_group']))
		$user_info['secondary_user_group'] = implode(",", $user_info['secondary_user_group']);

	// Check if birthday year is out of acceptable bounds
	if(isset($user_info['birthday_year']) && ($user_info['birthday_year'] < 1901 || $user_info['birthday_year'] > date('Y')))
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_update_username($user_id, $current_username, $new_username, $suppress_errors = False)
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function users_update_password($user_id, $new_password, $suppress_errors = False)
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
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
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
	$db -> basic_delete(
		array(
			"table" => "users",
			"where" => "id = ".(int)$user_id,
			"limit" => 1
			)
		);

	$db -> basic_delete(
		array(
			"table" => "profile_fields_data",
			"where" => "member_id = ".(int)$user_id,
			"limit" => 1
			)
		);

	// Remove moderator info for this user
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
 * Takes field values and chucks it all in a handy query
 * string for use when searching for users.
 *
 * @param array $search_info Stuff from the form to search.
 * @param array $extra Any other entries that need putting into the string.
 * @return string Finished string to be inserted into a query
 */
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
