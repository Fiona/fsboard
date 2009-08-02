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
 * Attachment administration related functions
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
 * Select a single filetype.
 *
 * @var int $filetype_id ID of the filetype we want
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the filetype.
 */
function attachments_filetypes_get_filetype_by_id($filetype_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "filetypes",
			"where" => "`id` = ".(int)$filetype_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a filetype for us based on data given
 *
 * @var array $filetype_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function attachments_filetypes_add_filetype($filetype_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "filetypes",
			"data" => $filetype_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_filetype_error']);
		return False;
	}

	// Update cache
	$cache -> update_cache("filetypes");

	return True;

}


/**
 * This will update a filetype for us based on data given
 *
 * @var int $filetype_id ID number of the filetype to update.
 * @var array $filetype_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function attachments_filetypes_edit_filetype($filetype_id, $filetype_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "filetypes",
			"data" => $filetype_data,
			"where" => "id = ".(int)$filetype_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_editing_filetype']);
		return $lang['error_editing_filetype'];
	}

	// Update cache
	$cache -> update_cache("filetypes");

	return True;

}


/**
 * This will delete a filetype for us given an id
 *
 * @var int $filetype_id ID of the filetype to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function attachments_filetypes_delete_filetype($filetype_id, $suppress_errors = False)
{

	global $db, $cache, $lang, $output;

	// Remove filetype
	save_undelete_data(
		"filetypes",
		"Deleted filetype ID ".$filetype_id,
		"id = ".(int)$filetype_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "filetypes",
			"where" => "`id` = ".(int)$filetype_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['delete_filetype_fail']);
		return $lang['delete_filetype_fail'];
	}

	// Update cache
	$cache -> update_cache("filetypes");

	return True;

}

?>