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
 * Admin custom profile field related functions
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
 * Select all profile fields.
 *
 * @return array An array of fields, with ids against info
 */
function profile_fields_get_fields()
{

	global $db;

	$fields = array();

	$db -> basic_select(
		array(
			"table" => "profile_fields",
			"order" => "`order`", 
			"direction" => "asc"
			)
		);

	if(!$db -> num_rows())
		return $fields;

	while($f_array = $db -> fetch_array())
		$fields[$f_array['id']] = $f_array;

	return $fields;

}


/**
 * Select a single profile field.
 *
 * @var int $field_id ID of the profile field we want
 *
 * @return bool|array Either false on failure or an array of containing info
 * about the fields.
 */
function profile_fields_get_field_by_id($field_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "profile_fields",
			"where" => "`id` = ".(int)$field_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a profile field for us based on data given
 *
 * @var array $field_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function profile_fields_add_field($field_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "profile_fields",
			"data" => $field_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_field_error']);
		return False;
	}

	// We need to add a column to the profile_fields_data table so we can save data
	$new_id = $db -> insert_id();
	$db -> add_column("profile_fields_data", "field_".(int)$new_id, "text", "NULL");

	// Update cache
	$cache -> update_cache("profile_fields");

	return True;

}


/**
 * This will update a profile field for us based on data given
 *
 * @var int ID of the field to edit.
 * @var array $field_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function profile_fields_edit_field($field_id, $field_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "profile_fields", 
			"data" => $field_data,
			"where" => "id = ".(int)$field_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_editing_field']);
		return $lang['error_editing_field'];
	}

	// Update cache
	$cache -> update_cache("profile_fields");

	return True;

}


/**
 * This will delete a profile field for us given an id
 *
 * @var int ID of the field to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function profile_fields_delete_field($field_id, $suppress_errors = False)
{

	global $db, $cache, $lang;

	// Remove profile field
	save_undelete_data(
		"profile_fields",
		"Deleted profile field ID ".$field_id, 
		"id = ".(int)$field_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "profile_fields",
			"where" => "`id` = ".(int)$field_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['field_delete_fail']);
		return $lang['field_delete_fail'];
	}

	// Remove column from data table
	$q = $db -> remove_column("profile_fields_data", "field_".(int)$field_id);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['field_delete_database_fail']);
		return $lang['field_delete_database_fail'];
	}

	// Update cache
	$cache -> update_cache("profile_fields");

	return True;

}

?>