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
 * View profile page
 *
 * Users visit this when they follow links to get
 * information about users; their profile page.
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 *
 * @started 01 Jun 2007
 * @edited 12 Jun 2007
 */




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


$template_view_profile = load_template_class("template_view_profile");

load_language_group("view_profile");

include ROOT."common/funcs/users.funcs.php";

view_profile();


/*
 * This function is the basis for the entire script, it shows up a profile.
 */
function view_profile()
{

	global $db, $lang, $output, $cache, $user, $parser, $template_view_profile, $template_global;
	
	//***********************************************
	// See if we're allowed to see profiles first please.
	//***********************************************
	if(!$user -> perms['perm_see_profile'])
	{
	
		$output -> add(
			$template_global -> normal_error($lang['global_no_permission'])
		);
		return;
	    
	}


	//***********************************************
	// Get the id/name
	//***********************************************
	$gusername = (isset($_GET['uname'])) ? trim($_GET['uname']) : False;
	$gid = intval(trim(
		(isset($_GET['uid'])) ? trim($_GET['uid']) : 0
	));
	
	if($gusername && !$gid)
	{
	
	    $db -> basic_select("users", "id", "username = '".$db -> escape_string($gusername)."'");
	    $userid = $db -> result();
	    
	    if(!$userid)
	    {

            $output -> add(
                $template_global -> normal_error($lang['user_not_found'])
            );
            return;

        }

    }
    elseif(!$gid)
    {
		
		$output -> add(
	        $template_global -> normal_error($lang['user_not_found'])
		);
		return;

    }
    else
        $userid = $gid;

    //***********************************************
    // Select the info from the database
    //***********************************************
    $db -> query($db -> special_queries -> query_view_profile($userid));
    
    if($db -> num_rows() < 1)
    {
	
	    $output -> add(
	        $template_global -> normal_error($lang['user_not_found'])
	    );
	    return;

    }

    $user_db_info = $db -> fetch_array();

	$exploded_secondary_groups = explode(",", $user_db_info['secondary_user_group']);
	$user_perm_info = return_user_perm_array($user_db_info['user_group'], $exploded_secondary_groups);
		

    //***********************************************
    // Start with the most basic info
    //***********************************************
    // Need the parser class of course
    $parser -> options(true, false, false, false, false, true);

    $info['username'] = $parser -> do_parser($user_db_info['username']);

    
    if(!$user_db_info['real_name'])
    	$info['real_name'] = $lang['no_info'];	 
    else
		$info['real_name'] = $parser -> do_parser($user_db_info['real_name']);

	$info['posts'] = intval($user_db_info['posts']);


    //***********************************************
	// Avatar
    //***********************************************
	$avatar_user_groups = $exploded_secondary_groups;
	$avatar_user_groups[] = $user_db_info['user_group'];
		
	$info['avatar'] = return_avatar_url($user_db_info, $avatar_user_groups, $user_perm_info);

	$lang['foos_avatar'] = $output -> replace_number_tags($lang['foos_avatar'], $info['username']);
	

    //***********************************************
	// User title
    //***********************************************
	$info['user_title'] = return_display_user_title($user_db_info, $user_perm_info);
		

    //***********************************************
	// Display username (with prefix/suffix)
    //***********************************************
    $prefix = $cache -> cache['user_groups'][$user_db_info['user_group']]['prefix'];
    $suffix = $cache -> cache['user_groups'][$user_db_info['user_group']]['suffix'];
	$info['display_username'] = $prefix.$user_db_info['username'].$suffix;


    //***********************************************
	// E-mail and homepage
    //***********************************************
	if(!$user_db_info['email'])
		$info['email'] = $lang['no_info'];		
	else
		if($user_db_info['hide_email'])
			$info['email'] = $lang['private_email'];
		else
		{
			$email = $parser -> do_parser($user_db_info['email']);
			$info['email'] = "<a href=\"mailto:".$email."\">".$email."</a>";
		}
			
	if(!$user_db_info['homepage'])
		$info['homepage'] = $lang['no_info'];		
	else			
	{
		$homepage = $parser -> do_parser($user_db_info['homepage']);
		$info['homepage'] = "<a href=\"".$homepage."\" target=\"_blank\">".$homepage."</a>";
	}


    //***********************************************
	// Messenger crap
    //***********************************************
	if(!$user_db_info['yahoo_messenger'])
		$info['yahoo_messenger'] = $lang['no_info'];		
	else			
		$info['yahoo_messenger'] = $parser -> do_parser($user_db_info['yahoo_messenger']);

	if(!$user_db_info['aol_messenger'])
		$info['aol_messenger'] = $lang['no_info'];		
	else			
		$info['aol_messenger'] = $parser -> do_parser($user_db_info['aol_messenger']);

	if(!$user_db_info['msn_messenger'])
		$info['msn_messenger'] = $lang['no_info'];		
	else			
		$info['msn_messenger'] = $parser -> do_parser($user_db_info['msn_messenger']);

	if(!$user_db_info['icq_messenger'])
		$info['icq_messenger'] = $lang['no_info'];		
	else			
		$info['icq_messenger'] = $parser -> do_parser($user_db_info['icq_messenger']);

	if(!$user_db_info['gtalk_messenger'])
		$info['gtalk_messenger'] = $lang['no_info'];		
	else			
		$info['gtalk_messenger'] = $parser -> do_parser($user_db_info['gtalk_messenger']);

			
    //***********************************************
	// Time settings
    //***********************************************
	// Date of registration
    $info['registered'] = return_formatted_date(
		$cache -> cache['config']['format_date'],
		$user_db_info['registered']
	);

	// Last active time
	if($user_db_info['last_active'] == "-1")
		$info['last_active'] = $lang['never'];
	else		
		$info['last_active'] = return_formatted_date(
	    	$cache -> cache['config']['format_date']." ".$cache -> cache['config']['format_time'],
	    	$user_db_info['last_active']
		);
	        	
	// Last post time
	if($user_db_info['last_post_time'] == "-1")
		$info['last_post'] = $lang['never'];
	else			
        $info['last_post'] = return_formatted_date(
	    	$cache -> cache['config']['format_date']." ".$cache -> cache['config']['format_time'],
	    	$user_db_info['last_post_time']
        );

	// Birthday		
	if(!$user_db_info['birthday_day'] || !$user_db_info['birthday_month'] || !$user_db_info['birthday_year'])
	{
			
		$info['birthday'] = $lang['no_info'];
		$info['age'] = $lang['no_info'];
			
	}
	else
	{
			
		$birthday = mktime( 0, 0, 0, $user_db_info['birthday_month'], $user_db_info['birthday_day'], $user_db_info['birthday_year']);
        $info['birthday'] = return_formatted_date($cache -> cache['config']['format_date'], $birthday);			
			
		$info['age'] = return_formatted_date("Y", TIME) - $user_db_info['birthday_year']; 
			
	}			


    //***********************************************
	// User groups
    //***********************************************
    $info['user_group'] = "<a href=\"#\">".$cache -> cache['user_groups'][$user_db_info['user_group']]['name']."</a>";
    
    if($user_db_info['secondary_user_group'])
    {
        	
    	if(count($exploded_secondary_groups) > 0 && is_array($exploded_secondary_groups))
    	{
    	
    		$secondary_groups_names = array();
    		
    		foreach($exploded_secondary_groups as $gval)
    			$secondary_groups_names[] =  "<a href=\"#\">".$cache -> cache['user_groups'][$gval]['name']."</a>";
        			
			$info['secondary_user_group'] = implode(", ", $secondary_groups_names);        			
        		
    	}

    }
    else
    	$info['secondary_user_group'] = "";


	//***********************************************
	// Custom profile fields
	//***********************************************
	$custom_profile_fields = "";
	
	if(is_array($cache -> cache['profile_fields']) && count($cache -> cache['profile_fields']) > 0)
	{
	
		$entries = array();
	
		foreach($cache -> cache['profile_fields'] as $field_id => $profile_field)
		{
			
			// is this field allowed to appear?
			if($profile_field['is_private'] || ($profile_field['admin_only_field'] && !$user -> perms['perm_admin_area']))
				continue;

			$final_value = ""; 

			// Which type
			if(isset($user_db_info['field_'.$field_id]) && $user_db_info['field_'.$field_id] != "")
			{

				switch($profile_field['field_type'])
				{
					
					// ---Dropdowns
					case "dropdown":
	
						$dropdown_text = explode("|", $profile_field['dropdown_text']);
						$dropdown_text = array_map("trim", $dropdown_text);
	
						$dropdown_values = explode("|", $profile_field['dropdown_values']);
						$dropdown_values = array_map("trim", $dropdown_values);
						
						foreach($dropdown_values as $val_id => $val)
							if($val == trim($user_db_info['field_'.$field_id]))
								$final_value = $dropdown_text[$val_id];
		 
						break;
	
					// ---Yes/no radios					
					case "yesno":
						
						if($user_db_info['field_'.$field_id])
							$final_value = $lang['yes'];
						else
							$final_value = $lang['no'];
						
						break;
					
					// ---All others
					default:					
						
						$final_value = _htmlentities(trim($user_db_info['field_'.$field_id]));
					
				}

			}
			// In the end we had no data
			else
				$final_value = $lang['no_info'];

			$entries[] = array(
				"name" 	=> $profile_field['name'],
				"value" => $final_value
			);
						
		}
		

		// So we have some entries, that means we need to put the info up
		if($entries)
			$custom_profile_fields = $template_view_profile -> profile_view_custom($entries);
				
	}

		
    //***********************************************
    // Signature
    //***********************************************
    if(trim($user_db_info['signature']) == "")
    	$info['signature'] = "-";
	else
	{        	

		$use_bbcode = ($user_perm_info['perm_use_bbcode']) ? true : false;
		$use_emotes = ($user_perm_info['perm_use_emoticons']) ? true : false;
		$word_filter = (!$user -> perms['perm_no_word_filter']) ? true : false;
		
        $parser -> options(true, $use_bbcode, true, $use_bbcode, true, $word_filter);
        $info['signature'] = $parser -> do_parser($user_db_info['signature']);
 
	}
	
	
    //***********************************************
    // Show up the page
    //***********************************************
    $lang['profile_page_title'] = $output -> replace_number_tags($lang['profile_page_title'], array($info['username']));
    $output -> page_title = $lang['profile_page_title'];

    $output -> add(
        $template_view_profile -> profile($info, $custom_profile_fields)
    );

}

?>