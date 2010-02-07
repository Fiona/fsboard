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
 * Custom BBCode related administration functions
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
 * Select a single custom bbcode via ID number.
 *
 * @var int $id The ID number we're looking for.
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the tag.
 */
function custom_bbcode_get_bbcode_by_id($id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "bbcode",
			"where" => "`id` = ".(int)$id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * Select a single custom bbcode via tag.
 *
 * @var string $tag The tag we're looking for.
 * @var int $ignore_id If not NULL any tag with this ID will not be 
 *   taken into account when selecting.
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the tag.
 */
function custom_bbcode_get_bbcode_by_tag($tag, $ignore_id = NULL)
{

	global $db;

	$extra_where = ($ignore_id !== NULL ? " AND `id` <> ".(int)$ignore_id : "");

	$db -> basic_select(
		array(
			"table" => "bbcode",
			"where" => "`tag` = '".$db -> escape_string($tag)."'".$extra_where,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a new custom bbcode for us based on data given
 *
 * @var array $bbcode_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|int False on failure or the ID of the new custom bbcode.
 */
function custom_bbcode_add_bbcode($bbcode_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "bbcode",
			"data" => $bbcode_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_bbcode_error']);
		return False;
	}

	$new_id = $db -> insert_id();

	// Update cache
	$cache -> update_cache("custom_bbcode");

	return $new_id;

}



/**
 * This will update an existing custom bbcode for us.
 *
 * @var int $bbcode_id ID number of the bbcode to update.
 * @var array $bbcode_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string True on success or a string containing an error.
 */
function custom_bbcode_edit_bbcode($bbcode_id, $bbcode_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try updating
	$q = $db -> basic_update(
		array(
			"table" => "bbcode",
			"data" => $bbcode_data,
			"where" => "id = ".(int)$bbcode_id
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_editing_bbcode']);
		return $lang['error_editing_bbcode'];
	}

	// Update cache
	$cache -> update_cache("custom_bbcode");

	return True;

}


/**
 * This will delete a custom bbcode for us given an id
 *
 * @var int $bbcode_id ID of the BBCode to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function custom_bbcode_delete_bbcode($bbcode_id, $suppress_errors = False)
{

	global $db, $cache, $lang, $output;

	// Remove title
	save_undelete_data(
		"bbcode",
		"Deleted bbcode ID ".$bbcode_id,
		"id = ".(int)$bbcode_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "bbcode",
			"where" => "`id` = ".(int)$bbcode_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['bbcode_delete_fail']);
		return $lang['bbcode_delete_fail'];
	}

	// Update cache
	$cache -> update_cache("custom_bbcode");

	return True;

}

?>