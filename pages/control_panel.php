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
 * User control panel
 *
 * This ... thing is the control panel for users.
 * Edit profile stuff, avatars, settings, blah de blah blah.
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


$template_control_panel = load_template_class("template_control_panel");

load_language_group("control_panel");

include ROOT."common/funcs/users.funcs.php";
include ROOT."common/funcs/control_panel.funcs.php";


//***********************************************
// Check permissions
//***********************************************
// Paranoia
$no_entry = false;


$cp_user_id = (isset($_GET['uid'])) ? (int)$_GET['uid'] : false;

// We have no id set in the url -- fallback on ourself
if($cp_user_id === false)
{

	// See if we can edit ourself
	if(!$user -> perms['perm_edit_own_profile'])
	{

		$output -> add(
			$template_global -> normal_error($lang['global_no_permission'])
		);
		$no_entry = true;

	}
	else
	{

		// it's us
		$cp_user_info = $user -> info;
		$cp_user_id = $user -> user_id;

	}

}
else
{

	// Check we're allowed to edit other users
	if(!$user -> perms['perm_admin_area'] && !$user -> perms['perm_global_mod'])
	{

		$output -> add(
			$template_global -> normal_error($lang['global_no_permission'])
		);
		$no_entry = true;

	}
	else
	{

		// See if the specified user exists
		$db -> basic_select("users", "*", "id = ".$cp_user_id, "", "1");

		// Got stuff?
		if($db -> num_rows())
		{

			// Get the full info
			$cp_user_info = $db -> fetch_array();
			$cp_user_id = $cp_user_info['id'];
			 
		}
		else
		{

			// No user  = error
			$output -> add(
				$template_global -> normal_error($lang['error_user_id_not_exist'])
			);
				
			$no_entry = true;
				
		}

	}
		
}


//***********************************************
// What are we doing?
//***********************************************
$_GET['m2'] = (isset($_GET['m2'])) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];

$output -> page_title = $lang['control_panel_page_title'];

switch ($secondary_mode)
{

	// ****************
	// Avatar
	// ****************
	case "avatar":
		cp_avatar();
		break;

	case "avatar_gallery":
		avatar_gallery();
		break;

	case "avatar_upload":
		avatar_upload();
		break;
		 
	case "avatar_external":
		avatar_external();
		break;

	case "avatar_update":
		
		avatar_update();
		break;
				
	// ****************
	// Front page
	// ****************
	case "save_notes":
		front_page_save_notes();
		break;
		 
	default:

		cp_front_page();

}



/*
 * The front page of the control panel
 */
function cp_front_page($input = array())
{

	global $db, $lang, $output, $cache, $user, $parser, $template_control_panel;
	global $no_entry, $cp_user_info, $cp_user_id;

	if($no_entry)
		return;

	$input['notepad'] = (isset($input['notepad'])) ? $input['notepad'] : "";
		
	$output -> add(
		$template_control_panel -> cp_front_page($cp_user_info, $input),
		$output -> buffer_2
	);

	finish_cp_output();

}


/*
 * Save submitted personal notes
 */
function front_page_save_notes()
{

	global $db, $lang, $output, $template_global;
	global $no_entry, $cp_user_id;

	if($no_entry)
		return;
		
	$input = array(
		"notepad" => trim(_htmlentities($_POST['notepad']))
	);

	if($input['notepad'] == "")
	{
		cp_front_page();
		return;
	}

	if(!$db -> basic_update("users", $input, "id = ".$cp_user_id))
	{

		$output -> add(
		$template_global -> normal_error($lang['error_updating_notepad']),
		$output -> buffer_2
		);

		cp_front_page($input);
		return;

	}

	$output -> redirect(ROOT."index.php?m=control", $lang['notepad_updated']);

}


/*
 * First avatar page, displays your current one and asks
 * what you want to do to choose another
 */
function cp_avatar()
{

	global $db, $lang, $output, $cache, $user, $parser, $template_global, $template_control_panel;
	global $no_entry, $cp_user_info, $cp_user_id;

	if($no_entry)
		return;

	// Get permission stuff
	if(!is_array($cp_user_info['secondary_user_group']))
		$cp_user_info['secondary_user_group'] = explode(",", $cp_user_info['secondary_user_group']);
	
	$user_perm_info = return_user_perm_array($cp_user_info['user_group'], $cp_user_info['secondary_user_group']);

	// are we allowed an avatar?
	if(!$user_perm_info['perm_avatar_allow'] || !$cache -> cache['config']['avatars_on'])
	{
		$output -> add(
			$template_global -> normal_error($lang['avatar_no_permission']),
			$output -> buffer_2			
		);
		
		finish_cp_output();
		return;		
	}
	
	// Nab the current avatar
	$cp_user_info['avatar_address'] = return_avatar_url($cp_user_info, $cp_user_info['secondary_user_group'], $user_perm_info);
	
	// Output the page
	$output -> add(
		$template_control_panel -> cp_avatar_main($cp_user_info),
		$output -> buffer_2
	);

	finish_cp_output();

}



/*
 * Gallery page for avatars
 */
function avatar_gallery()
{

	global $db, $lang, $output, $cache, $user, $template_global, $template_control_panel;
	global $no_entry, $cp_user_info, $cp_user_id;

	if($no_entry)
		return;

	// ****************
	// Get permission stuff
	// ****************
	if(!is_array($cp_user_info['secondary_user_group']))
		$cp_user_info['secondary_user_group'] = explode(",", $cp_user_info['secondary_user_group']);
	
	$user_perm_info = return_user_perm_array($cp_user_info['user_group'], $cp_user_info['secondary_user_group']);
		
	// *******************
	// Check global and group permission
	// *******************
	if(!$user_perm_info['perm_avatar_allow_gallery'] || !$cache -> cache['config']['avatar_gallery_on'])
	{
		
		$output -> add(
			$template_global -> normal_error($lang['avatar_gallery_no_perm']),
			$output -> buffer_2			
		);
		
		cp_avatar();
		return;
		
	}
	
	// *******************
	// are there any avatars in the cache even?
	// *******************	
	if(!isset($cache -> cache['avatars']) || count($cache -> cache['avatars']) < 1)
	{
		
		$output -> add(
			$template_global -> normal_error($lang['avatar_gallery_empty']),
			$output -> buffer_2			
		);
		
		cp_avatar();
		return;
		
	}

	
	// *******************	
	// Cats we're not allowed
	// *******************
	$user_groups = $cp_user_info['secondary_user_group'];
	$user_groups[] = $cp_user_info['user_group'];
		
	$not_a_cat = return_small_image_perms($user_groups); 
	
	// Shoop through all the categories and put them in an array
	$categories = array();

	foreach($cache -> cache['small_image_cats'] as $cat_id => $cat_info)
	{
		if($cat_info['type'] != "avatars" || in_array($cat_id, $not_a_cat))
			continue;
		
		$categories[$cat_id] = $cat_info;
	}
	
	// Not allowed into any categories apparently
	if(count($categories) < 1)
	{
		
		$output -> add(
			$template_global -> normal_error($lang['avatar_gallery_empty']),
			$output -> buffer_2			
		);
		
		cp_avatar();
		return;
		
	}
	

	// *******************	
	// Get whatever the current category is	
	// *******************
	sort_array_by_member($categories, "order");
	reset($categories); 
	
	if(isset($_POST['cat_id']))
	{
		
		$_POST['cat_id'] = (int)$_POST['cat_id'];

		if(array_key_exists($_POST['cat_id'], $categories))
			$selected_category = $_POST['cat_id'];
		else
		{
			$selected_category = current($categories);
			$selected_category = $selected_category['id'];
		}
		
	}
	else
	{
		$selected_category  = current($categories);
		$selected_category = $selected_category['id'];
	}	

	
	// *******************
	// Get list of avatars on the page	
	// *******************	
	$avatars = array();

	foreach($cache -> cache['avatars'] as $avatar_id => $avatar)
		if($avatar['cat_id'] == $selected_category && $cp_user_info['posts'] >= $avatar['min_posts'])
			$avatars[$avatar_id] = $avatar;		
				
	sort_array_by_member($avatars, "order");

	
	// *******************	
	// Output the page
	// *******************	
	$output -> add(
		$template_control_panel -> cp_avatar_gallery($categories, $selected_category, $avatars),
		$output -> buffer_2
	);

	finish_cp_output();

}


/*
 * Uploading facilitiy for avatars
 */
function avatar_upload()
{


	global $db, $lang, $output, $cache, $user, $template_global, $template_control_panel;
	global $no_entry, $cp_user_info, $cp_user_id;

	if($no_entry)
		return;

	// ****************
	// Get permission stuff
	// ****************
	if(!is_array($cp_user_info['secondary_user_group']))
		$cp_user_info['secondary_user_group'] = explode(",", $cp_user_info['secondary_user_group']);
	
	$user_perm_info = return_user_perm_array($cp_user_info['user_group'], $cp_user_info['secondary_user_group']);
		
	// *******************
	// Check global and group permission
	// *******************
	if(!$user_perm_info['perm_avatar_allow_upload'] || !$cache -> cache['config']['avatar_upload_on'])
	{
		
		$output -> add(
			$template_global -> normal_error($lang['avatar_upload_no_perm']),
			$output -> buffer_2			
		);
		
		cp_avatar();
		return;
		
	}
	
	$limits = array(
		"width" => (!$cache -> cache['config']['avatar_max_width']) ? $user_perm_info['perm_avatar_width'] : $cache -> cache['config']['avatar_max_width'],
		"height" => (!$cache -> cache['config']['avatar_max_height']) ? $user_perm_info['perm_avatar_height'] : $cache -> cache['config']['avatar_max_height'],
		"filesize" => (!$cache -> cache['config']['avatar_max_filesize']) ? $user_perm_info['perm_avatar_filesize'] : $cache -> cache['config']['avatar_max_filesize']
	);
	
	// *******************	
	// Output the page
	// *******************
	$lang['avatar_upload_dimension_limit'] = $output ->replace_number_tags($lang['avatar_upload_dimension_limit'], array($limits['width'], $limits['height']));
	$lang['avatar_upload_filesize_limit'] = $output ->replace_number_tags($lang['avatar_upload_filesize_limit'], array($limits['filesize']));
	
	$output -> add(
		$template_control_panel -> cp_avatar_upload($limits),
		$output -> buffer_2
	);

	finish_cp_output();
	
}


/*
 * Selecting an external image for an avatar
 */
function avatar_external()
{

	global $db, $lang, $output, $cache, $user, $parser, $template_control_panel;
	global $no_entry, $cp_user_info, $cp_user_id;

	if($no_entry)
		return;

	// ****************
	// Get permission stuff
	// ****************
	if(!is_array($cp_user_info['secondary_user_group']))
		$cp_user_info['secondary_user_group'] = explode(",", $cp_user_info['secondary_user_group']);
	
	$user_perm_info = return_user_perm_array($cp_user_info['user_group'], $cp_user_info['secondary_user_group']);
		
	// *******************
	// Check global and group permission
	// *******************
	if(!$user_perm_info['perm_avatar_allow_external'] || !$cache -> cache['config']['avatar_external_on'])
	{
		
		$output -> add(
			$template_global -> normal_error($lang['avatar_external_no_perm']),
			$output -> buffer_2			
		);
		
		cp_avatar();
		return;
		
	}
	
	$limits = array(
		"width" => (!$cache -> cache['config']['avatar_max_width']) ? $user_perm_info['perm_avatar_width'] : $cache -> cache['config']['avatar_max_width'],
		"height" => (!$cache -> cache['config']['avatar_max_height']) ? $user_perm_info['perm_avatar_height'] : $cache -> cache['config']['avatar_max_height'],
		"filesize" => (!$cache -> cache['config']['avatar_max_filesize']) ? $user_perm_info['perm_avatar_filesize'] : $cache -> cache['config']['avatar_max_filesize']
	);
	
	// *******************	
	// Output the page
	// *******************
	$lang['avatar_upload_dimension_limit'] = $output ->replace_number_tags($lang['avatar_upload_dimension_limit'], array($limits['width'], $limits['height']));
	$lang['avatar_upload_filesize_limit'] = $output ->replace_number_tags($lang['avatar_upload_filesize_limit'], array($limits['filesize']));
	
	$output -> add(
		$template_control_panel -> cp_avatar_upload($limits),
		$output -> buffer_2
	);

	finish_cp_output();
			
}


/*
 * After choosing our avatar, this one works out if we're allowed it and does the update
 */
function avatar_update()
{
	
	global $db, $lang, $output, $cache, $user, $template_global, $template_control_panel;
	global $no_entry, $cp_user_info, $cp_user_id;

	if($no_entry)
		return;

	$method = trim($_GET['method']); 		
	
	if(!$method)
		return;

	// ****************
	// Get permission stuff
	// ****************
	if(!is_array($cp_user_info['secondary_user_group']))
		$cp_user_info['secondary_user_group'] = explode(",", $cp_user_info['secondary_user_group']);
	
	$user_perm_info = return_user_perm_array($cp_user_info['user_group'], $cp_user_info['secondary_user_group']);

	// ****************
	// are we allowed an avatar?
	// ****************
	if(
		(!$user_perm_info['perm_avatar_allow'] || !$cache -> cache['config']['avatars_on'])
		||
		(!$user_perm_info['perm_avatar_allow_'.$method] || !$cache -> cache['config']['avatar_'.$method.'_on'])
	)
	{
		$output -> add(
			$template_global -> normal_error($lang['avatar_no_permission']),
			$output -> buffer_2			
		);
		
		finish_cp_output();
		return;		
	}
	
	switch($method)
	{

		// ******************
		// Updating from gallery
		// ******************
		case "gallery":

			$_GET['id'] = (int)$_GET['id'];
			
			if(!$_GET['id'])
				return false;

			// *******************				
			// Check the new avatar exists
			// *******************			
			$avatar_info = $cache -> cache['avatars'][$_GET['id']];				
			
			if(!is_array($avatar_info))
				return false;
	
			// *******************	
			// Are we allowed to use this avatar?
			// *******************
			$not_a_cat = return_small_image_perms($user_perm_info); 
			
			if(in_array($avatar_info['cat_id'], $not_a_cat))
			{
				$output -> add(
					$template_global -> normal_error($lang['avatar_no_permission']),
					$output -> buffer_2			
				);
				
				finish_cp_output();
				return;		
			}

			
			// *******************			
			// Okay great, we should delete any avatars that are uploaded already
			// *******************			
			if($cp_user_info['avatar_type'] == "upload")
				if(file_exists(ROOT.$cp_user_info['avatar_address']))
					@unlink(ROOT.$cp_user_info['avatar_address']);

					
			// *******************			
			// Clean up completed, let's update the user table
			// *******************			
			$update_array = array(
				"avatar_type" => "gallery",
				"avatar_address" => $_GET['id']
			);
			
			if(!$db -> basic_update("users", $update_array, "id=".$cp_user_id))
			{
		
				$output -> add(
					$template_global -> normal_error($lang['avatar_update_error']),
					$output -> buffer_2			
				);
				
				cp_avatar();
				return;
								
			}
			
			
			// *******************
			// FSBoard - Where do you want to go today?
			// *******************
			$output -> redirect(ROOT."index.php?m=control&m2=avatar", $lang['avatar_updated_successfully']);				
				
			break;
			
			
		// ******************
		// Updating from uploaded image
		// ******************
		case "upload":
		
			// *******************
			// Load up the upload class and put config vals in
			// *******************
			include_once ROOT."common/class/upload.class.php";
			
			$limits = array(
				"width" => (!$cache -> cache['config']['avatar_max_width']) ? $user_perm_info['perm_avatar_width'] : $cache -> cache['config']['avatar_max_width'],
				"height" => (!$cache -> cache['config']['avatar_max_height']) ? $user_perm_info['perm_avatar_height'] : $cache -> cache['config']['avatar_max_height'],
				"filesize" => (!$cache -> cache['config']['avatar_max_filesize']) ? $user_perm_info['perm_avatar_filesize'] : $cache -> cache['config']['avatar_max_filesize']
			);
			
			$upload = new upload(true);
			$upload -> destination_path = $cache -> cache['config']['user_avatar_upload_path'];
			$upload -> allowed_dimensions = array("width" => $limits['width'], "height" => $limits['height']);
			$upload -> max_filesize = $limits['filesize'];
			

			// *******************
			// Do the first check pass 
			// *******************						
			if(($error = $upload -> check_upload_from_form("avatar_image")) !== True)
			{
		
				$output -> add(
					$template_global -> normal_error($error),
					$output -> buffer_2			
				);
				
				avatar_upload();
				return;
								
			}	

			// *******************
			// First check was okay, first delete any old ones
			// *******************					
			if($cp_user_info['avatar_type'] == "upload")
				if(file_exists(ROOT.$cp_user_info['avatar_address']))
					@unlink(ROOT.$cp_user_info['avatar_address']);
						
					
			// *******************
			// Set the destination filename to user_id.ext
			// *******************			
			$upload -> name_to_upload = $cp_user_id.".".$upload -> extension;
			
			
			// *******************
			// Complete the upload
			// *******************
			if(($error = $upload -> complete_upload_from_form()) !== True)
			{
		
				$output -> add(
					$template_global -> normal_error($error),
					$output -> buffer_2			
				);
				
				avatar_upload();
				return;
								
			}	

			
			// *******************
			// With everything done, update the database
			// *******************
			$update_array = array(
				"avatar_type" => "upload",
				"avatar_address" => $upload -> destination_path.$upload -> name_to_upload
			);
			
			if(!$db -> basic_update("users", $update_array, "id=".$cp_user_id))
			{
		
				$output -> add(
					$template_global -> normal_error($lang['avatar_update_error']),
					$output -> buffer_2			
				);
				
				cp_avatar();
				return;
								
			}			
			
			
			// *******************
			// Rrr-rrr-redirect!
			// *******************
        	$output -> redirect(ROOT."index.php?m=control&m2=avatar", $lang['avatar_updated_successfully']);				
			
			break;
			

		// ******************
		// Updating from external URL
		// ******************
		case "external":
			
			break;

			
		// ******************
		// Bad request
		// ******************
		default:
			
			return;
			
	}
	
}
	
?>