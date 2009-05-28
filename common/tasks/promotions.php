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
 * COMMON TASK
 * Promotions
 * 
 * This task tries to work out who is eligible for automatic promotions.
 * It is a relatively heavy script for what sounds like such a simple task and
 * could probably be very optimised. But it is designed to only run every hour
 * which should minimise the impact of it. (Plus if a board has no automatic
 * promotions then this will do very little, although that's admittedly a
 * really poor excuse.)
 *
 * Yeah whatever, this could be better.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Common
 */



// -----------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/*
                return "SELECT user.id as user_id, user.username as username,
                        user.user_group as user_user_group,user.registered as user_register_date,
                        user.posts as user_posts, user.reputation as user_reputation,
                        promotions.group_to_id as promotion_group_to_id, promotions.promotion_type as promotion_type,
                        promotions.use_posts as promotion_use_posts, promotions.use_reputation as promotion_use_reputation,
                        promotions.use_days_registered as promotion_use_days_registered,   
                        promotions.posts as promotion_posts, promotions.reputation as promotion_reputation,
                        promotions.days_registered as promotion_days_registered,
                        promotions.reputation_comparison as promotion_reputation_comparison
                        FROM ".$db -> table_prefix."users as user
                        JOIN ".$db -> table_prefix."promotions as promotions ON(user.user_group = promotions.group_id)
                        LEFT JOIN ".$db -> table_prefix."user_groups as user_groups ON(user_groups.id = promotions.group_to_id)
                        WHERE user.last_active > ".(TIME - (60*60*24*7));

$db -> query($db -> special_queries -> query_task_promotions());
*/

// We select all of the promotion information and join it to all users who may
// eligible. We base this on which group the promotion is for and if they've
// been active in the last week.
//
// (Last week is just a time scale I picked out of thin air, it stops us
// grabbing a million and one users that have not logged in for years,
// unfortunately this does not stop us selecting a load of users if the message
// board is extremely popular. Oh well.)
$db -> basic_select(
	array(
		"table" => "users as user",
		"what" => (
			"user.id as user_id, user.username as username, user.user_group as user_user_group, ".
			"user.registered as user_register_date, user.posts as user_posts, ".
			"user.reputation as user_reputation, promotions.group_to_id as promotion_group_to_id, ".
			"promotions.promotion_type as promotion_type, promotions.use_posts as promotion_use_posts, ".
			"promotions.use_reputation as promotion_use_reputation, ".
			"promotions.use_days_registered as promotion_use_days_registered, ".
			"promotions.posts as promotion_posts, promotions.reputation as promotion_reputation, ".
			"promotions.days_registered as promotion_days_registered,".
			"promotions.reputation_comparison as promotion_reputation_comparison"
			),
		"join" => array(
			array(
				"join" => "promotions as promotions",
				"join_on" => "user.user_group = promotions.group_id"
				),
			array(
				"join" => "user_groups as user_groups",
				"join_type" => "LEFT",
				"join_on" => "user_groups.id = promotions.group_to_id"
				)
			),
//		"where" => "user.last_active > ".(TIME - (60*60*24*7))
		)
	);


// These arrays will hold which users we need to update for each group
$update_primary = array();
$update_secondary = array();


// We go through all the entries we got from the database, what we have is users
// who have been active in the last week and the information about a promotion
// they may be eligible for. We'll check all the requirements for promotion and
// save whoever is eligible.
while($promotion_info = $db -> fetch_array())
{

	// If we didn't get any promotion information then skip this entry.
	// I haven't come across a result set that actually required this check but
	// I'm doing it for paranoia reasons.
	if(!$promotion_info['promotion_group_to_id'])
		continue;
	
	// Check that we're not already a member of this group
	if(
		($promotion_info['promotion_group_to_id'] == $promotion_info['user_user_group'])
		&&
		$promotion_info['promotion_type'] == PROMOTION_TYPE_PRI
		)
		continue;

	// We'll change this to true if any criteria for promotion matches.
	$match_criteria = False;
		
	// Check promotions based on post count
	if(
		$promotion_info['promotion_use_posts'] &&
		($promotion_info['user_posts'] >= $promotion_info['promotion_posts'])
		)
		$match_criteria = True;

	// Check the the amount of days user has been registered
	$promotion_info['user_days_registered'] = (TIME - $promotion_info['user_register_date'] / 86400);

	if(
		$promotion_info['promotion_use_days_registered'] &&
		($promotion_info['user_days_registered'] >= $promotion_info['promotion_days_registered'])
		)
		$match_criteria = true;

	// Check reputation based promotions
	if(
		$promotion_info['promotion_use_reputation'] &&
		$promotion_info['promotion_reputation_comparison'] == PROMOTION_REPUTATION_GT
		)
	{
		// If the reputation check is based on greater than a particular value
		if($promotion_info['user_reputation'] >= $promotion_info['promotion_promotion'])
			$match_criteria = true;
	}
	elseif(
		$promotion_info['promotion_use_reputation'] &&
		$promotion_info['promotion_reputation_comparison'] == PROMOTION_REPUTATION_LT
		)
	{
		// If reputation check is based on less than a particular value (used for demotions)
		if(($promotion_info['user_register_date'] < $promotion_info['promotion_days_registered']) )
			$match_criteria = true;
	}

	// If we didn't match any critera at this pount then we know this user is not
	// eligible yet.
	if($match_criteria == false)
		continue;

	$prom_group_id = $promotion_info['promotion_group_to_id'];
		
	// Save the user id againts this group id if we're updating primary group
	if($promotion_info['promotion_type'] == PROMOTION_TYPE_PRI)
	{
		if(!isset($update_primary[$prom_group_id]))
			$update_primary[$prom_group_id] = array();

		$update_primary[$prom_group_id][] = $promotion_info['user_id'];
	}
	// Do the same if adding to secondary groups
	elseif(intval($promotion_info['promotion_type']) == PROMOTION_TYPE_SEC)
	{
		if(!isset($update_secondary[$prom_group_id]))
			$update_secondary[$prom_group_id] = array();

		$update_secondary[$prom_group_id][] = $promotion_info['user_id'];
	}

}

// We need to log how many we got later so start counting
$final_count = 0;


// If we have any primary groups to update then go through them
if(count($update_primary) > 0)
{

	// $users is an array of user IDs that need to be put into the group
	foreach($update_primary as $group_id => $users)
	{

		if(!count($users))
			continue;

		// Builds a query like:
		// UPDATE users SET user_group = 4 WHERE id IN(2, 4, 5)
		$db -> basic_update_in(
			array(
				"table" => "users",
				"data" => array("user_group" => $group_id),
				"in_col" => "id",
				"where" => $users
				)
			);

		$final_count += count($users);
		
	}
	
}


// If we need to update any secondary groups
if(count($update_secondary) > 0)
{

	foreach($update_secondary as $group_id => $users)
	{

		if(!count($users))
			continue;
	
		// I go through each user ID, select the secondary ID to check if they're a
		// current member or not. If they're not I insert it.
		// Yeah shut up - I know this can be a lot of queries.
		foreach($users as $user_id)
		{

			$db -> basic_select(
				array(
					"table" => "users_secondary_groups",
					"where" => "user_id = ".$user_id." AND group_id = ".$group_id,
					"limit" => 1
					)
				);

			if($db -> num_rows())
				continue;

			$db -> basic_insert(
				array(
					"table" => "users_secondary_groups",
					"data" => array(
						"user_id" => $user_id,
						"group_id" => $group_id
						)
					)
				);

		}

		$final_count += count($users);
		
	}
		
}

$common_task_log = "Changed user groups of ".$final_count." users.";

?>