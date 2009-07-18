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
 * Insignia administration related functions
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
 * Select a single user insignia.
 *
 * @var int $insignia_id ID of the insignia we want
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the title.
 */
function insignia_get_insignia_by_id($insignia_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "user_insignia",
			"where" => "`id` = ".(int)$insignia_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create an insignia for us based on data given
 *
 * @var array $insignia_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function insignia_add_insignia($insignia_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "user_insignia",
			"data" => $insignia_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_insignia_insert_error']);
		return False;
	}

	// Update cache
	$cache -> update_cache("user_insignia");

	return True;

}


/**
 * This will update an insignia for us based on data given
 *
 * @var int $insignia_id ID number of the insignia to update.
 * @var array $insignia_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function insignia_edit_insignia($insignia_id, $insignia_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "user_insignia",
			"data" => $insignia_data,
			"where" => "id = ".(int)$insignia_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['edit_insignia_error']);
		return $lang['edit_insignia_error'];
	}

	// Update cache
	$cache -> update_cache("user_insignia");

	return True;

}


/**
 * This will delete an insignia for us given an id
 *
 * @var int $insignia_id ID of the insignia to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function insignia_delete_insignia($insignia_id, $suppress_errors = False)
{

	global $db, $cache, $lang;

	// Remove title
	save_undelete_data(
		"user_insignia",
		"Deleted insignia ID ".$insignia_id,
		"id = ".(int)$insignia_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "user_insignia",
			"where" => "`id` = ".(int)$insignia_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['insignia_delete_fail']);
		return $lang['insignia_delete_fail'];
	}

	// Update cache
	$cache -> update_cache("user_insignia");

	return True;

}


?>