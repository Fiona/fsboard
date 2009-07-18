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
 * User title administration related functions
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
 * Select a single user title.
 *
 * @var int $title_id ID of the title we want
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the title.
 */
function titles_get_title_by_id($title_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "user_titles",
			"where" => "`id` = ".(int)$title_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a user title for us based on data given
 *
 * @var array $title_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function titles_add_title($title_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "user_titles",
			"data" => $title_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_titles_insert_error']);
		return False;
	}

	// Update cache
	$cache -> update_cache("user_titles");

	return True;

}


/**
 * This will update a user title for us based on data given
 *
 * @var int $title_id ID number of the title to update.
 * @var array $title_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function titles_edit_title($title_id, $title_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "user_titles",
			"data" => $title_data,
			"where" => "id = ".(int)$title_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['edit_titles_error_editing']);
		return $lang['edit_titles_error_editing'];
	}

	// Update cache
	$cache -> update_cache("user_titles");

	return True;

}


/**
 * This will delete a title for us given an id
 *
 * @var int $title_id ID of the title to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function titles_delete_title($title_id, $suppress_errors = False)
{

	global $db, $cache, $lang, $output;

	// Remove title
	save_undelete_data(
		"user_titles",
		"Deleted title ID ".$title_id,
		"id = ".(int)$title_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "user_titles",
			"where" => "`id` = ".(int)$title_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['titles_delete_fail']);
		return $lang['titles_delete_fail'];
	}

	// Update cache
	$cache -> update_cache("user_titles");

	return True;

}


?>