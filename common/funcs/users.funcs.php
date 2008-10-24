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
 * User functions file
 * 
 * Global functions pertaining to users.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 * 
 * @started 12 Jun 2007
 * @edited 12 Jun 2007
 */

// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");




// ----------------------------------------------------------------------------------------------------------------------




/**
 * Give a set of permission flags for a certain set of user groups.
 * 
 * @param int $primary_group ID of primary group.
 * @param mixed $secondary_groups Comma delimitered string, array of vals or false if none.
 * @return array Permissions, perm name is key, true/false is value
 */
function return_user_perm_array($primary_group, $secondary_groups = false)
{

	global $cache;
	
	$perms_array = array();
	
	// Fall back on guest fail-safe if we can't find the primary group'.
	if(!isset($cache -> cache['user_groups'][$primary_group]))
		$primary_group = USERGROUP_GUEST;

	// Get global group perms
	foreach($cache -> cache['user_groups'][$primary_group] as $key => $val)
		$perms_array[$key] = $val;

	// Secondary user groups set up
	if($secondary_groups === false)
		$secondary_groups = array();
	elseif(!is_array($secondary_groups) && strlen($secondary_groups) > 0)		
		$secondary_groups = explode(",", $secondary_groups);
	
	// Override perms from secondary groups if better than primary
	if(is_array($secondary_groups) && count($secondary_groups) > 0)
		foreach($secondary_groups as $key => $val)
			if(is_numeric($val))
				foreach($cache -> cache['user_groups'][$val] as $key2 => $val2)
					if(substr($key2, 0, 5) == "perm_" && $perms_array[$key2] == 0)
						$perms_array[$key2] = $val2;	

	return $perms_array;
		
}


/**
 * Takes a couple of vars from the user entry in the database
 * and chucks back a URL to be shoved into an img tag innit
 * 
 * @param array $user_info Bunch of crap like avatar address and whatnot
 * @param array $user_groups IDs of all the user's groups
 * @param array $user_perms Permission flags for this user
 * @return string URL of the avatar, or false if none.
 */
function return_avatar_url($user_info, $user_groups, $user_perms)
{
	
	global $cache;
	
	if(!$user_perms['perm_avatar_allow'] || !$cache -> cache['config']['avatars_on'])
		return false;
	
	switch($user_info['avatar_type'])
	{
		
		// *****************
		// No avatar at all
		// *****************
		case "no":
			return false;
			

		// *****************
		// Avatar from the gallery
		// *****************
		case "gallery":

			if(!is_numeric($user_info['avatar_address']))
				return false;

			$avatar_info = $cache -> cache['avatars'][$user_info['avatar_address']];				
			
			if(!is_array($avatar_info))
				return false;
				
			// Check global and group permission
			if(!$user_perms['perm_avatar_allow_gallery'] || !$cache -> cache['config']['avatar_gallery_on'])
				return false;
				
			// Check post count is allowed
			if($user_info['posts'] !== false && $avatar_info['min_posts'] > $user_info['posts'])
				return false;

			// User group permissions for the avatar category				
			if($user_groups !== false)
			{
				
				if(is_array($cache -> cache['small_image_cats_perms']) && count($cache -> cache['small_image_cats_perms']) > 0)
				{
					
					$cats_perms_array = array();
					
					// Get all the permissions saved up
					foreach($cache -> cache['small_image_cats_perms'] as $cat_perm_val)
						if(is_array($cat_perm_val) && $cat_perm_val['cat_id'] == $avatar_info['cat_id'])
							$cats_perms_array[] = $cat_perm_val['user_group_id'];
					
					// There are permissions that apply to our category
					if(count($cats_perms_array) > 0)
					{
						
						$allow = false;
						
						// Go through all of our user groups for this user
						foreach($user_groups as $group_id)
						{
						
							// Check we haven't been disallowed for this group
							if(!in_array($group_id, $cats_perms_array))
							{
								$allow = true;
								break;
							}
							
						}
						
						if(!$allow)
							return false;
						
					}
					
				}
				
			}
			
			// Check the file actually exists
			if(!file_exists(ROOT.$avatar_info['filename']))
				return false;
			
			// All that's done, send the filename back, everything is okay	
			return ROOT.$avatar_info['filename'];
														
			break;
			
		
		// *****************
		// Uploaded by user to the server
		// *****************
		case "upload":

			// Check global and group permission
			if(!$user_perms['perm_avatar_allow_upload'] || !$cache -> cache['config']['avatar_gallery_on'])
				return false;

			// Check the file exists
			if(!file_exists(ROOT.$user_info['avatar_address']))
				return false;
			
			// Send the filename back
			return ROOT.$user_info['avatar_address'];
						
			break;
			

		// *****************
		// Off-site avatar
		// *****************
		case "external":

			if(!$user_perms['perm_avatar_allow_external'])
				return false;

			// Send the filename back
			return $user_info['avatar_address'];
					
			break;
			

		// *****************
		// Nothing defined for some reason
		// *****************
		default:
			return false;			
		
	}
	
}


/**
 * Gives the display text for a user title
 * 
 * @param array $user_info Array of stuff from the database about the user
 * @param array $user_perms Array of permission values
 * @return string The text of the title.
 */
function return_display_user_title($user_info, $user_perms)
{
	
	global $cache;
	
	// ***************
	// Custom title
	// ***************
	if($user_perms['perm_custom_user_title'] && trim($user_info['title']) != "")
	{
		
		// clean it up
		$parser_clean = new parser(true, false, false, false, false, true);
		$title = $parser_clean -> do_parser(trim($user_info['title']));
        
		return $title;		
		
	}
	

	// ***************
	// Post dependant title
	// ***************
	if($cache -> cache['user_groups'][$user_info['user_group']]['override_user_title'])
	{
		
		if(is_array($cache -> cache['user_titles']) && count($cache -> cache['user_titles']) > 0)
		{
			
			$sort_titles = array();
			
			foreach($cache -> cache['user_titles'] as $t)
				if($t['min_posts'] <= $user_info['posts'])
					$sort_titles[$t['min_posts']] = $t['title'];
		
			if(count($sort_titles) > 0)
			{

				krsort($sort_titles, SORT_NUMERIC);
				reset($sort_titles);
				
				$title = current($sort_titles);
				
				return $title;

			}			
			
		}
		
	}


	// ***************
	// User group setting title
	// ***************
	if(trim($cache -> cache['user_groups'][$user_info['user_group']]['display_user_title']) != "")
	{

		$title = $cache -> cache['user_groups'][$user_info['user_group']]['display_user_title'];
		return $title;
		
	}

	$title = $cache -> cache['user_groups'][$user_info['user_group']]['name'];
	return $title;
	 				
}

	
/**
 * Function will return an array of small image categories that we are NOT
 * allowed to access.
 * 
 * @param array $user_groups Array of usergroups that apply to us
 * @return array The category IDs we're not allowed, so we can do in_arrays on it.
 */
function return_small_image_perms($user_groups)
{

	global $cache;
	
	$disallowed_cats = array();
	
	if(is_array($cache -> cache['small_image_cats_perms']) && count($cache -> cache['small_image_cats_perms']) > 0)
	{
		
		// Go through all cats and see if it applies
		foreach($cache -> cache['small_image_cats_perms'] as $cat_perm_val)
			if(is_array($cat_perm_val) && in_array($cat_perm_val['user_group_id'], $user_groups))
				$disallowed_cats[] = $cat_perm_val['cat_id'];
				
	}
	
	return $disallowed_cats;
					
}

?>