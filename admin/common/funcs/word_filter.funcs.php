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
 * Word filter related administration functions
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
 * Select a single word filter via ID number.
 *
 * @var int $id The ID number we're looking for.
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the filter.
 */
function word_filter_get_word_filter_by_id($id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "wordfilter",
			"where" => "`id` = ".(int)$id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * Select a single word filter via word.
 *
 * @var string $tag The tag we're looking for.
 * @var int $ignore_id If not NULL any word filter with this ID will not be 
 *   taken into account when selecting.
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the filter.
 */
function word_filter_get_word_filter_by_word($word, $ignore_id = NULL)
{

	global $db;

	$extra_where = ($ignore_id !== NULL ? " AND `id` <> ".(int)$ignore_id : "");

	$db -> basic_select(
		array(
			"table" => "wordfilter",
			"where" => "`word` = '".$db -> escape_string($word)."'".$extra_where,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a new word filter for us based on data given
 *
 * @var array $word_filter_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|int False on failure or the ID of the new word filter.
 */
function word_filter_add_word_filter($word_filter_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "wordfilter",
			"data" => $word_filter_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_wordfilter_insert_error']);
		return False;
	}

	$new_id = $db -> insert_id();

	// Update cache
	$cache -> update_cache("wordfilter");

	return $new_id;

}



/**
 * This will update an existing word filter for us.
 *
 * @var int $filter_id ID number of the word filter to update.
 * @var array $word_filter_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string True on success or a string containing an error.
 */
function word_filter_edit_word_filter($filter_id, $word_filter_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try updating
	$q = $db -> basic_update(
		array(
			"table" => "wordfilter",
			"data" => $word_filter_data,
			"where" => "id = ".(int)$filter_id
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['error_editing_wordfilter']);
		return $lang['error_editing_wordfilter'];
	}

	// Update cache
	$cache -> update_cache("wordfilter");

	return True;

}


/**
 * This will delete a word filter for us given an id
 *
 * @var int $filter_id ID of the word filter to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function word_filter_delete_word_filter($filter_id, $suppress_errors = False)
{

	global $db, $cache, $lang, $output;

	// Remove title
	save_undelete_data(
		"wordfilter",
		"Deleted word filter ID ".$filter_id,
		"id = ".(int)$filter_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "wordfilter",
			"where" => "`id` = ".(int)$filter_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['wordfilter_delete_fail']);
		return $lang['wordfilter_delete_fail'];
	}

	// Update cache
	$cache -> update_cache("wordfilter");

	return True;

}

?>