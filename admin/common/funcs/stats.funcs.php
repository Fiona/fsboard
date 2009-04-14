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
 * Admin area - Statistic related functions
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");




/**
 * Recalculates a single statistic by name and saves it.
 *
 * @var string $statistic Name of the stat to update and save. List of stats are as follows:
 *      "total_members" - Total number of users on the forum.
 *      "newest_member" - Last user account created.
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function stats_update_single_stat($statistic, $supress_errors = False)
{

	global $db;

	switch($statistic)
	{
		
		// --------------------
		// Total number of users on the forum
		// --------------------
		case "total_members":
			$db -> basic_select(
				array(
					"table" => "users",
					"what" => "count(`id`)",
					"where" => "user_group <> ".(int)USERGROUP_VALIDATING
					)
				);
			stats_update_single_stat_query("total_members", $db -> result());

			break;

		   
		// --------------------
 		// Last user account created.
		// --------------------
		case "newest_member":
			$db -> basic_select(
				array(
					"table" => "users",
					"what" => "`id`,`username`",
					"where" => "user_group <> ".(int)USERGROUP_VALIDATING,
					"order" => "`registered` DESC",
					"limit" => 1
					)
				);

			$new_user = $db -> fetch_array();
			stats_update_single_stat_query("newest_member_id", $new_user['id']);
			stats_update_single_stat_query("newest_member_username", $new_user['username']);

			break;
		
	}

}


/**
 * Helper function for stats_update_single_stat(). It does the query that updates
 * the statistics table.
 *
 * @var string $name Name of the stat to update.
 * @var mixed $value The value that we will be putting into the database.
 *
 * @return bool False on failure.
 */
function stats_update_single_stat_query($name, $value)
{

	global $db;

	$q = $db -> basic_update(
		array(
			"table" => "stats",
			"data" => array("stat_value" => $value),
			"where" => "stat_name = '".$name."'",
			"limit" => 1
			)
		);

	return ($q ? True : False);
		
}

?>