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
 * Admin user group related functions
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD")) 
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * Select all user groups.
 *
 * @return array An array of groups, with ids against info
 */
function user_groups_get_groups()
{

	global $db;

	$users = array();

	$db -> basic_select(
		array(
			"table" => "user_groups",
			"order" => "id", 
			"direction" => "asc"
			)
		);

	if($db -> num_rows())
		while($g_array = $db -> fetch_array())
			$users[$g_array['id']] = $g_array;

	return $users;

}


/**
 * Select a single user group.
 *
 * @var int $group_id ID of the user group we want
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the group.
 */
function user_groups_get_group_by_id($group_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "user_groups",
			"where" => "`id` = ".(int)$group_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a user group for us based on data given
 *
 * @var array $group_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function user_groups_add_group($group_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "user_groups",
			"data" => $group_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_group_error']);
		return False;
	}

	// Update cache
	$cache -> update_cache("user_groups");

	return True;

}



/**
 * This will update a user group for us based on data given
 *
 * @var int $group_id ID of the user group to edit.
 * @var array $group_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function user_groups_edit_group($group_id, $group_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "user_groups", 
			"data" => $group_data,
			"where" => "id = ".(int)$group_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_updating_usergroup']);
		return $lang['error_updating_usergroup'];
	}

	// Update cache
	$cache -> update_cache("user_groups");

	return True;

}


/**
 * This will delete a user group. We must also tell it where to move users who are
 * current members of the group.
 *
 * @var int $group_id ID of the user group to delete.
 * @var int $replace_group_id ID of the user group that all current members will 
 *   be moved to.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function user_groups_delete_group($group_id, $replace_group_id, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Delete the main group
	save_undelete_data(
		"user_groups",
		"Deleted user group ID ".$group_id,
		"id = ".(int)$group_id,
		array("limit" => 1)
		);

	if(
		!$db -> basic_delete(
		   array(
			   "table" => "user_groups",
			   "where" => "id = ".(int)$group_id,
			   "limit" => 1
			   )
		   )
		)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['usergroup_delete_fail']);
		return $lang['usergroup_delete_fail'];
	}
        
	// Update cache
	$cache -> update_cache("user_groups");        

	// Move users
	if(
		!$db -> basic_update(
			array(
				"table" => "users",
				"data" => array("user_group" => $replace_group_id),
				"where" => "user_group = ".(int)$group_id
				)
			)
		)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['usergroup_delete_move_fail']);
		return $lang['usergroup_delete_fail'];
	}


	// Delete all secondary group info
	save_undelete_data(
		"users_secondary_groups",
		"Deleted secondary group bindings for user group ID ".$group_id,
		"group_id = ".(int)$group_id
		);

	if(
		!$db -> basic_delete(
		   array(
			   "table" => "users_secondary_groups",
			   "where" => "group_id = ".(int)$group_id
			   )
		   )
		)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['usergroup_delete_move_fail']);
		return $lang['usergroup_delete_move_fail'];
	}


	// Delete related promotions
	save_undelete_data(
		"promotions",
		"Deleted promotion info for user group ID ".$group_id,
		"group_id = ".(int)$group_id
		);

	$db -> basic_delete(
		array(
			"table" => "promotions",
			"where" => "group_id = ".(int)$group_id." OR group_to_id = ".(int)$group_id
			)
		);


	// Delete related per-forum permissions
	save_undelete_data(
		"forums_perms",
		"Deleted per-forum permissions info for user group ID ".$group_id,
		"group_id = ".(int)$group_id
		);

	$db -> basic_delete(
		array(
			"table" => "forums_perms",
			"where" => "group_id = ".(int)$group_id
			)
		);

	$cache -> update_cache("forums_perms");        


	// Delete related moderator settings
	save_undelete_data(
		"moderators",
		"Deleted moderator info for user group ID ".$group_id,
		"group_id = ".(int)$group_id
		);

	$db -> basic_delete(
		array(
			"table" => "moderators",
			"where" => "group_id = ".(int)$group_id
			)
		);

	$cache -> update_cache("moderators");        

	return True;

}

?>