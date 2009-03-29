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
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 *
 * @started 01 Jun 2007
 * @edited 01 Jun 2007
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

	// Check username length
	if(_strlen($username) < 2 || _strlen($username) > 25)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_username_too_long']);
		return $lang['error_username_too_long'];
	}


	// Check for reserved characters in username
	$invalid_chars = array("'", "\"", "<!--", "\\");
	foreach ($invalid_chars as $char)
	{
		if(strstr($username, $char))
		{
			if(!$suppress_errors)
				$output -> set_error_message($lang['error_username_reserved_chars']);
			return $lang['error_username_reserved_chars'];
		}
	}

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
