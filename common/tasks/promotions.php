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
 * This runs about every hour and works out user group
 * promotions and does them automatically
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Common
 * 
 * @started 06 Feb 2007
 * @edited 29 Apr 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


$db -> query($db -> special_queries -> query_task_promotions());


$update_primary = array();
$update_secondary = array();

// **************************
// Go through all the found entries
// **************************
while($promotion_info = $db -> fetch_array())
{

	// -------------
	// Only got user, no promotion
	// -------------
	if(!$promotion_info['promotion_group_to_id'])
		continue;

	$promotion_info['user_secondary_user_group'] = explode(",", $promotion_info['user_secondary_user_group']);
	$promotion_info['user_secondary_user_group'] = array_map("trim", $promotion_info['user_secondary_user_group']);
	
	$promotion_info['user_register_date'] = (TIME - $promotion_info['user_register_date'] / 86400);

	// -------------
	// Check that we're not a member
	// -------------
	if(
		(in_array($promotion_info['promotion_group_to_id'], $promotion_info['user_secondary_user_group'])
		&& $promotion_info['promotion_type'] == 1)
		||
		($promotion_info['promotion_group_to_id'] == $promotion_info['user_user_group']
		&& $promotion_info['promotion_type'] == 0)
	)
		continue;

	$match_criteria = false;
		
	// -------------
	// Check posts
	// -------------
	if($promotion_info['promotion_use_posts'] && ($promotion_info['user_posts'] >= $promotion_info['promotion_posts']) )
		$match_criteria = true;

	// -------------
	// Check days registered	
	// -------------
	if($promotion_info['promotion_use_days_registered'] && ($promotion_info['user_register_date'] >= $promotion_info['promotion_days_registered']) )
		$match_criteria = true;

	// -------------
	// Check reputation (greater or equal)
	// -------------
	if($promotion_info['promotion_use_reputation'] && intval($promotion_info['promotion_reputation_comparison']) == 0)
		if(($promotion_info['user_register_date'] >= $promotion_info['promotion_days_registered']) )
			$match_criteria = true;
	// less than
	elseif($promotion_info['promotion_use_reputation'] && intval($promotion_info['promotion_reputation_comparison']) == 1)
		if(($promotion_info['user_register_date'] < $promotion_info['promotion_days_registered']) )
			$match_criteria = true;

	// -------------
	// If we shouldn't be adding to a group...
	// -------------
	if($match_criteria == false)
		continue;
		
	// -------------
	// We're okay to add, let's carry on
	// -------------
	// Primary
	if(intval($promotion_info['promotion_type']) == 0)
		$update_primary[ $promotion_info['promotion_group_to_id'] ][] = $promotion_info['user_id']; 	
	// Secondary
	elseif(intval($promotion_info['promotion_type']) == 1)
		$update_secondary[ $promotion_info['promotion_group_to_id'] ][] = $promotion_info['user_id']; 	

}

$final_count = 0;

// **************************
// Got to update primaries?
// **************************
if(count($update_primary) > 0)
{

	foreach($update_primary as $group_id => $users)
	{

		$user_id_array = array();

		if(count($users) > 0)
			foreach($users as $user_id)
				$user_id_array[] = "'".$user_id."'";
	
		if(count($user_id_array) > 0)
			$db -> basic_update_in("users", array("user_group" => $group_id), "id", $user_id_array);
		
		$final_count += count($user_id_array);
		
	}
	
}


// **************************
// Got to update secondaries?
// **************************
if(count($update_secondary) > 0)
{

	foreach($update_secondary as $group_id => $users)
	{

		$user_id_array = array();

		if(count($users) > 0)
			foreach($users as $user_id)
				$user_id_array[] = "'".$user_id."'";
	
		if(count($user_id_array) > 0)
			$db -> query($db -> special_queries -> query_task_promotions_update_secondary($group_id, $user_id_array));
		
		$final_count += count($user_id_array);
		
	}
		
}

$common_task_log = "Changed user groups of ".$final_count." users.";

?>