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
 * Promotion administration related functions
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
 * Select a single promotion.
 *
 * @var int $promotion_id ID of the promotion we want
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the promotion.
 */
function promotions_get_promotion_by_id($promotion_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "promotions",
			"where" => "`id` = ".(int)$promotion_id,
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a promotion for us based on data given
 *
 * @var array $promotion_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function promotions_add_promotion($promotion_data, $suppress_errors = False)
{

	global $db, $output, $lang;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "promotions",
			"data" => $promotion_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_promotions_insert_error']);
		return False;
	}

	return True;

}


/**
 * This will update a promotion for us based on data given
 *
 * @var array $promotion_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function promotions_edit_promotion($promotion_id, $promotion_data, $suppress_errors = False)
{

	global $db, $output, $lang;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "promotions",
			"data" => $promotion_data,
			"where" => "id = ".(int)$promotion_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['edit_promotions_error_editing']);
		return $lang['edit_promotions_error_editing'];
	}

	return True;

}


/**
 * This will delete a promotion for us given an id
 *
 * @var int $promotion_id ID of the promotion to delete.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string Either true or a string containing an error.
 */
function promotions_delete_promotion($promotion_id, $suppress_errors = False)
{

	global $db, $cache, $lang;

	// Remove promotion
	save_undelete_data(
		"promotions",
		"Deleted promotion ID ".$promotion_id,
		"id = ".(int)$promotion_id,
		array("limit" => 1)
		);
	$q = $db -> basic_delete(
		array(
			"table" => "promotions",
			"where" => "`id` = ".(int)$promotion_id,
			"limit" => 1
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['promotion_delete_fail']);
		return $lang['promotion_delete_fail'];
	}

	return True;

}


?>