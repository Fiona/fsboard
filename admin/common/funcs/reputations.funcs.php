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
 * Reputation administration related functions
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
 * Select a single reputation.
 *
 * @var int $reputation_id ID of the rep we want
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the reputation..
 */
function reputations_get_reputation_by_id($reputation_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "user_reputations",
			"where" => "`id` = ".(int)$reputation_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a reputation for us based on data given
 *
 * @var array $reputation_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function reputations_add_reputation($reputation_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "user_reputations",
			"data" => $reputation_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_reputations_insert_error']);
		return False;
	}

	// Update cache
	$cache -> update_cache("user_reputations");

	return True;

}


/**
 * This will update a reputation for us based on data given
 *
 * @var int $reputation_id ID number of the rep to update.
 * @var array $reputation_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function reputations_edit_reputation($reputation_id, $reputation_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "user_reputations",
			"data" => $reputation_data,
			"where" => "id = ".(int)$reputation_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['edit_reputations_error_editing']);
		return $lang['edit_reputations_error_editing'];
	}

	// Update cache
	$cache -> update_cache("user_reputations");

	return True;

}


/**
 * This will delete a reputation for us given an id
 *
 * @var int $reputation_id ID of the reputation to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function reputations_delete_reputation($reputation_id, $suppress_errors = False)
{

	global $db, $cache, $lang, $output;

	// Remove title
	save_undelete_data(
		"user_reputations",
		"Deleted reputation ID ".$reputation_id,
		"id = ".(int)$reputation_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "user_reputations",
			"where" => "`id` = ".(int)$reputation_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['reputation_delete_fail']);
		return $lang['reputation_delete_fail'];
	}

	// Update cache
	$cache -> update_cache("user_reputations");

	return True;

}


?>