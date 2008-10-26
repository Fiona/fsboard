<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2006 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

FSBoard is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
--------------------------------------------------------------------------

*********************************
*       FSBoard                 *
*       by Fiona 2006           *
*********************************
*       Cache Class	        *
*       Started by Fiona        *
*       22nd Jan 2007           *
*********************************
*       Last edit by Fiona      *
*       26th Feb 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");




class cache
{

	var $cache = array();

	var $cache_types = array(
			"avatars",
			"config",
			"custom_bbcode",
			"emoticons",
			"filetypes",			//#5
			"forums",
			"forums_perms",
			"languages",
			"moderators",
			"post_icons",			//#10
			"profile_fields",
			"small_image_cats",
			"small_image_cats_perms",
			"themes",
			"user_groups",			//#15
			"wordfilter",
			"user_titles",
			"user_insignia",
			"user_reputations",
			"plugins"				//#20
		);

	var $always_load = array(
			"config",
			"moderators",
			"forums",
			"forums_perms",
			"languages",	
			"themes",
			"user_groups",
			"plugins"
		);
		

        //***********************
	// Main loading function
        //***********************
	function load_cache()
	{
	
	        global $db, $extra_cache;
	
		// Select the cache
		if(defined('ADMIN'))
		{
			
			// We also want a cache of the admin help places.
			$db -> basic_select("admin_area_help", "page, action, field", "", "`order`");
			
			if($db -> num_rows() > 0)
			{
				
				$this -> cache['admin_area_help'] = array();
				
				while($help_array = $db -> fetch_array())
				{

					$help_page = $help_array['page'];
					$help_action = $help_array['action'];
					$help_field = $help_array['field'];

					$split = explode(",", $help_action);
					
					if(count($split) > 1)
					{

						foreach($split as $action)
							if($help_array['field'])
								$this -> cache['admin_area_help'][$help_page][$action][$help_field]['__yes__'] = true;
							else
								$this -> cache['admin_area_help'][$help_page][$action]['__yes__'] = true;

					}
					else
					{
						
						if($help_array['field'])
							$this -> cache['admin_area_help'][$help_page][$help_action][$help_field]['__yes__'] = true;
						elseif($help_array['action'])
							$this -> cache['admin_area_help'][$help_page][$help_action]['__yes__'] = true;
						else
							$this -> cache['admin_area_help'][$help_page]['__yes__'] = true;

					}
										
				}

			}			

			// Load the real cache from DB or filesystem
			if(defined("DATABASECACHE"))
				$db -> basic_select("cache", "name,content");
			else
				foreach($this -> cache_types as $val)
				{
					include ROOT."cache/cache_".$val.".php";
					eval("\$this -> cache[strtolower(\$val)] = return_cache_array_".$val."();"); 
				}
						
		}
		else
		{	
	
			$loading_cache_types = array();
				
			// Database fall back cache							
			if(defined("DATABASECACHE"))
			{

				foreach($this -> cache_types as $val)
					if(in_array($val, $extra_cache) || in_array($val, $this -> always_load))
						$loading_cache_types[] = "'".$val."'";
	
	 			$db -> basic_select("cache", "name,content", "name IN(".implode(",", $loading_cache_types).")");

			}
			// Filesystem load
			else
			{
			
				foreach($this -> cache_types as $val)
				{
					
					if(in_array($val, $extra_cache) || in_array($val, $this -> always_load))
					{
						include ROOT."cache/cache_".$val.".php";
						eval("\$this -> cache[strtolower(\$val)] = return_cache_array_".$val."();"); 
					}
			
				}

			}
			
		}
		
		// If we did database, this makes it work
		if(defined("DATABASECACHE"))
		{
			
			while($cache_array = $db -> fetch_array())
			{
			
				$this -> cache[strtolower($cache_array['name'])] = array();
				
				if(trim($cache_array['content']) == "")
					$this -> cache[strtolower($cache_array['name'])] = NULL;
				else
					$this -> cache[strtolower($cache_array['name'])] = unserialize($cache_array['content']);	                
	
			}

		}

	}


        //***********************
	// Update with this one
        //***********************
	function update_cache($cache_name = "ALL")
	{

	        if($cache_name == "ALL")
	        {

	                foreach($this -> cache_types as $name)
	                {
	                	
	                	if(!$this -> update_single_cache($name))
	                		return false;

	                }

	        }
		else
		{
			
                	if($this -> update_single_cache($cache_name))
                		return true;
			else
                		return true;

		}
		
    		return true;
		
	}	

        //***********************
	// Meow
        //***********************
	function update_single_cache($cache_name, $include_root = "")
	{
	
		global $db;
	
		$include_root = ($include_root) ? $include_root : ROOT;
		 
                if(!is_writable($include_root."cache/"))
			return false;
                	
		if(!$cache_name)
			return false;

		// Get the cache array
		$cache_array = array();
                eval("\$cache_array = \$this -> cache_".strtolower($cache_name)."();");
                
                // Update cache
                $info = array("content" => serialize($cache_array));

                if(!$db -> basic_update("cache", $info, "name='".$cache_name."'"))
			return false;

		// Build the file
		$file_contents = 
'<?php
function return_cache_array_'.strtolower($cache_name).'()
{
	$cache_array = array();
';

		foreach($cache_array as $lev1_key => $lev1_val)
		{

			// 2 Dimensional			
			if(is_array($lev1_val))
				foreach($lev1_val as $lev2_key => $lev2_val)
					$file_contents .= 
					'	$cache_array[\''.$lev1_key.'\'][\''.$lev2_key.'\'] = "'.addslashes($lev2_val).'";
';
			// 1 Dimensional
			else
				$file_contents .= 
				'	$cache_array[\''.$lev1_key.'\'] = "'.addslashes($lev1_val).'";
';
			
		}
		
		$file_contents .= '
	return $cache_array;
}
?>';		

                // Write the file
                if(!$fh = fopen($include_root."cache/cache_".strtolower($cache_name).".php", "w"))
                	return false;
                
                if(!fwrite($fh, $file_contents))
                	return false;
                	
                fclose($fh);

				@chmod($include_root."cache/cache_".strtolower($cache_name).".php", 0777);
                
                return true;
                						
	}	



	// --------------------------------------------------------------------------------
	// Individual cache functions below here
	// --------------------------------------------------------------------------------


	function cache_avatars()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("small_images", "*", "type='avatars'", "`order`", "", "asc");
                
                while($row = $db -> fetch_array())
                {
                
                        $cache[$row['id']]['cat_id']      = $row['cat_id'];
                        $cache[$row['id']]['name']        = $row['name'];
                        $cache[$row['id']]['order']       = $row['order'];
                        $cache[$row['id']]['min_posts']   = $row['min_posts'];
                        $cache[$row['id']]['filename']    = $row['filename'];
                
                }
		
		return $cache;
		
	}
	

	function cache_config()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("config", "name,value");
                
                while($row = $db -> fetch_array())
                {
                
                        $cache[$row['name']] = $row['value'];
                
                }
		
		return $cache;
		
	}
	

	function cache_custom_bbcode()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("bbcode", "*", "", "tag");
                
                while($row = $db -> fetch_array())
                {
                
                        $cache[$row['id']]['tag'] 		= $row['tag'];
                        $cache[$row['id']]['replacement'] 	= $row['replacement'];
                        $cache[$row['id']]['name'] 		= $row['name'];
                        $cache[$row['id']]['description'] 	= $row['description'];
                        $cache[$row['id']]['example']		= $row['example'];
                        $cache[$row['id']]['use_param'] 	= $row['use_param'];
                        $cache[$row['id']]['button_image'] 	= $row['button_image'];
                
                }
		
		return $cache;
		
	}
	

	function cache_emoticons()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("small_images", "*", "type='emoticons'", "`order`", "", "asc");
                
                while($row = $db -> fetch_array())
                {
                
                        $cache[$row['id']]['cat_id']      	= $row['cat_id'];
                        $cache[$row['id']]['name']        	= $row['name'];
                        $cache[$row['id']]['order']       	= $row['order'];
                        $cache[$row['id']]['emoticon_code']	= $row['emoticon_code'];
                        $cache[$row['id']]['filename']    	= $row['filename'];
                
                }
		
		return $cache;
		
	}
	

	function cache_filetypes()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("filetypes", "*", "", "extension");
                
                while($row = $db -> fetch_array())
                {
                
			$cache[$row['id']]['extension'] 	= $row['extension'];
			$cache[$row['id']]['mime_type'] 	= $row['mime_type'];
			$cache[$row['id']]['icon_file'] 	= $row['icon_file'];
			$cache[$row['id']]['use_avatar']	= $row['use_avatar'];
			$cache[$row['id']]['use_attachment'] 	= $row['use_attachment'];
			$cache[$row['id']]['enabled'] 		= $row['enabled'];
			$cache[$row['id']]['max_width'] 	= $row['max_width'];
			$cache[$row['id']]['max_height'] 	= $row['max_height'];
			$cache[$row['id']]['max_file_size'] 	= $row['max_file_size'];
                
                }
		
		return $cache;
		
	}


	function cache_forums()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("forums", "*");
                
                while($row = $db -> fetch_array())
                {
                
			$cache[$row['id']]['name'] 			= $row['name'];
			$cache[$row['id']]['description'] 		= $row['description'];
			$cache[$row['id']]['parent_id'] 		= $row['parent_id'];
			$cache[$row['id']]['position'] 			= $row['position'];
			$cache[$row['id']]['is_category'] 		= $row['is_category'];
			$cache[$row['id']]['theme_id'] 			= $row['theme_id'];
			$cache[$row['id']]['override_user_theme'] 	= $row['override_user_theme'];
			$cache[$row['id']]['password'] 			= $row['password'];
			$cache[$row['id']]['redirect_url'] 		= $row['redirect_url'];
			$cache[$row['id']]['rules_on'] 			= $row['rules_on'];
			$cache[$row['id']]['rules_title'] 		= $row['rules_title'];
			$cache[$row['id']]['rules_text'] 		= $row['rules_text'];
			$cache[$row['id']]['use_site_rules'] 		= $row['use_site_rules'];
			$cache[$row['id']]['hide_forum'] 		= $row['hide_forum'];
			$cache[$row['id']]['close_forum'] 		= $row['close_forum'];
			$cache[$row['id']]['bbcode_on'] 		= $row['bbcode_on'];
			$cache[$row['id']]['html_on'] 			= $row['html_on'];
			$cache[$row['id']]['polls_on'] 			= $row['polls_on'];
			$cache[$row['id']]['quick_reply_on'] 		= $row['quick_reply_on'];
			$cache[$row['id']]['add_post_count']		= $row['add_post_count'];
			$cache[$row['id']]['show_forum_jump'] 		= $row['show_forum_jump'];
			
                }
		
		return $cache;
		
	}
	

	function cache_forums_perms()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("forums_perms", "*");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['forum_id'] 			= $row['forum_id'];
	                $cache[$row['id']]['group_id'] 			= $row['group_id'];
	                $cache[$row['id']]['perm_see_board'] 		= $row['perm_see_board'];
	                $cache[$row['id']]['perm_use_search'] 		= $row['perm_use_search'];
	                $cache[$row['id']]['perm_view_other_topic'] 	= $row['perm_view_other_topic'];
	                $cache[$row['id']]['perm_post_topic'] 		= $row['perm_post_topic'];
	                $cache[$row['id']]['perm_reply_own_topic'] 	= $row['perm_reply_own_topic'];
	                $cache[$row['id']]['perm_reply_other_topic'] 	= $row['perm_reply_other_topic'];
	                $cache[$row['id']]['perm_edit_own_post'] 	= $row['perm_edit_own_post'];
	                $cache[$row['id']]['perm_edit_own_topic_title'] = $row['perm_edit_own_topic_title'];
	                $cache[$row['id']]['perm_delete_own_post'] 	= $row['perm_delete_own_post'];
	                $cache[$row['id']]['perm_delete_own_topic'] 	= $row['perm_delete_own_topic'];
	                $cache[$row['id']]['perm_move_own_topic'] 	= $row['perm_move_own_topic'];
	                $cache[$row['id']]['perm_close_own_topic'] 	= $row['perm_close_own_topic'];
	                $cache[$row['id']]['perm_post_closed_topic'] 	= $row['perm_post_closed_topic'];
	                $cache[$row['id']]['perm_remove_edited_by'] 	= $row['perm_remove_edited_by'];
	                $cache[$row['id']]['perm_use_html'] 		= $row['perm_use_html'];
	                $cache[$row['id']]['perm_use_bbcode'] 		= $row['perm_use_bbcode'];
	                $cache[$row['id']]['perm_use_emoticons'] 	= $row['perm_use_emoticons'];
	                $cache[$row['id']]['perm_no_word_filter'] 	= $row['perm_no_word_filter'];
	                $cache[$row['id']]['perm_new_polls'] 		= $row['perm_new_polls'];
	                $cache[$row['id']]['perm_vote_polls'] 		= $row['perm_vote_polls'];
	                                
                }
		
		return $cache;
		
	}


	function cache_languages()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("languages", "*");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['short_name'] 	= $row['short_name'];
	                $cache[$row['id']]['name'] 		= $row['name'];
	                $cache[$row['id']]['allow_user_select'] = $row['allow_user_select'];
	                $cache[$row['id']]['direction'] 	= $row['direction'];
	                $cache[$row['id']]['charset'] 		= $row['charset'];                
                }
		
		return $cache;
		
	}


	function cache_moderators()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("moderators", "*");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['forum_id'] 		= $row['forum_id'];
	                $cache[$row['id']]['user_id'] 		= $row['user_id'];
	                $cache[$row['id']]['username'] 		= $row['username'];
	                $cache[$row['id']]['group_id'] 		= $row['group_id'];
	                $cache[$row['id']]['group_name'] 	= $row['group_name'];
	                $cache[$row['id']]['perm_edit_post'] 	= $row['perm_edit_post'];
	                $cache[$row['id']]['perm_edit_topic'] 	= $row['perm_edit_topic'];
	                $cache[$row['id']]['perm_delete_post'] 	= $row['perm_delete_post'];
	                $cache[$row['id']]['perm_delete_topic'] = $row['perm_delete_topic'];
	                $cache[$row['id']]['perm_view_ip'] 	= $row['perm_view_ip'];
	                $cache[$row['id']]['perm_close_topic'] 	= $row['perm_close_topic'];
	                $cache[$row['id']]['perm_move_topic'] 	= $row['perm_move_topic'];
	                $cache[$row['id']]['perm_sticky_topic'] = $row['perm_sticky_topic'];
                
                }
		
		return $cache;
		
	}
	

	function cache_post_icons()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("small_images", "*", "type='post_icons'", "`order`", "", "asc");
                
                while($row = $db -> fetch_array())
                {
                
                        $cache[$row['id']]['cat_id']      = $row['cat_id'];
                        $cache[$row['id']]['name']        = $row['name'];
                        $cache[$row['id']]['order']       = $row['order'];
                        $cache[$row['id']]['min_posts']   = $row['min_posts'];
                        $cache[$row['id']]['filename']    = $row['filename'];
                
                }
		
		return $cache;
		
	}	
	

	function cache_profile_fields()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("profile_fields", "*", "", "`order`");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['name'] 		= $row['name'];
	                $cache[$row['id']]['description'] 	= $row['description'];
	                $cache[$row['id']]['field_type'] 	= $row['field_type'];
	                $cache[$row['id']]['size'] 		= $row['size'];
	                $cache[$row['id']]['max_length'] 	= $row['max_length'];
	                $cache[$row['id']]['order'] 		= $row['order'];
	                $cache[$row['id']]['dropdown_values'] 	= $row['dropdown_values'];
	                $cache[$row['id']]['dropdown_text'] 	= $row['dropdown_text'];
	                $cache[$row['id']]['show_on_reg'] 	= $row['show_on_reg'];
	                $cache[$row['id']]['user_can_edit'] 	= $row['user_can_edit'];
	                $cache[$row['id']]['is_private'] 	= $row['is_private'];
	                $cache[$row['id']]['admin_only_field'] 	= $row['admin_only_field'];
	                $cache[$row['id']]['must_be_filled'] 	= $row['must_be_filled'];
	                $cache[$row['id']]['topic_html'] 	= $row['topic_html'];   
	                             
                }
		
		return $cache;
		
	}
		

	function cache_small_image_cats()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("small_image_cat", "*", "", "`order`");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['id']			= $row['id']; 
	                			// Yeah i know. Re-dun-dant or what... I really did need it though I promise.
	                $cache[$row['id']]['name']			= $row['name'];
	                $cache[$row['id']]['order']			= $row['order'];
	                $cache[$row['id']]['type']			= $row['type'];
					$cache[$row['id']]['image_num']		= $row['image_num'];

                }
		
		return $cache;
		
	}
	
	
	function cache_small_image_cats_perms()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("small_image_cat_perms", "*");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['cat_id']		= $row['cat_id'];
	                $cache[$row['id']]['user_group_id']	= $row['user_group_id'];

                }
		
		return $cache;
		
	}
		
	/*
	function cache_stats()
	{
		
		global $db;
		
		$cache = array();		

                // Total members...
                $db -> basic_select("users", "count(*)", "user_group <> 5");
                $q_r = $db -> result();
                $cache['total_members'] = $q_r;

                // Newest member members...
                $db -> basic_select("users", "id,username", "user_group <> 5", "registered", "1", "desc");
                $q_r = $db -> fetch_array();
                $cache['newest_member_id'] = $q_r['id'];
                $cache['newest_member_username'] = $q_r['username'];
		
		return $cache;
		
	}*/
	

	function cache_themes()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("themes", "id,name,image_dir");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['name'] 	= $row['name'];
	                $cache[$row['id']]['image_dir'] = $row['image_dir'];

                }
		
		return $cache;
		
	}
	
	
	function cache_user_groups()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("user_groups", "*");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['name'] 			= $row['name'];
	                $cache[$row['id']]['prefix'] 			= $row['prefix'];
	                $cache[$row['id']]['suffix'] 			= $row['suffix'];
	                $cache[$row['id']]['flood_control_time'] 	= $row['flood_control_time'];
	                $cache[$row['id']]['edit_time'] 		= $row['edit_time'];
	                $cache[$row['id']]['perm_admin_area'] 		= $row['perm_admin_area'];
	                $cache[$row['id']]['perm_see_maintenance_mode'] = $row['perm_see_maintenance_mode'];
	                $cache[$row['id']]['perm_global_mod'] 		= $row['perm_global_mod'];
	                $cache[$row['id']]['banned'] 			= $row['banned'];
	                $cache[$row['id']]['perm_edit_own_profile'] 	= $row['perm_edit_own_profile'];
	                $cache[$row['id']]['perm_see_member_list'] 	= $row['perm_see_member_list'];
	                $cache[$row['id']]['perm_see_profile'] 		= $row['perm_see_profile'];
	                $cache[$row['id']]['hide_from_member_list'] 	= $row['hide_from_member_list'];
	                $cache[$row['id']]['perm_use_pm'] 		= $row['perm_use_pm'];
	                $cache[$row['id']]['pm_total'] 			= $row['pm_total'];
	                $cache[$row['id']]['perm_see_board'] 		= $row['perm_see_board'];
	                $cache[$row['id']]['perm_use_search'] 		= $row['perm_use_search'];
	                $cache[$row['id']]['perm_view_other_topic'] 	= $row['perm_view_other_topic'];
	                $cache[$row['id']]['perm_post_topic'] 		= $row['perm_post_topic'];
	                $cache[$row['id']]['perm_reply_own_topic'] 	= $row['perm_reply_own_topic'];
	                $cache[$row['id']]['perm_reply_other_topic'] 	= $row['perm_reply_other_topic'];
	                $cache[$row['id']]['perm_edit_own_post'] 	= $row['perm_edit_own_post'];
	                $cache[$row['id']]['perm_edit_own_topic_title'] = $row['perm_edit_own_topic_title'];
	                $cache[$row['id']]['perm_delete_own_post'] 	= $row['perm_delete_own_post'];
	                $cache[$row['id']]['perm_delete_own_topic'] 	= $row['perm_delete_own_topic'];
	                $cache[$row['id']]['perm_move_own_topic'] 	= $row['perm_move_own_topic'];
	                $cache[$row['id']]['perm_close_own_topic'] 	= $row['perm_close_own_topic'];
	                $cache[$row['id']]['perm_post_closed_topic'] 	= $row['perm_post_closed_topic'];
	                $cache[$row['id']]['perm_remove_edited_by'] 	= $row['perm_remove_edited_by'];
	                $cache[$row['id']]['perm_use_html'] 		= $row['perm_use_html'];
	                $cache[$row['id']]['perm_use_bbcode'] 		= $row['perm_use_bbcode'];
	                $cache[$row['id']]['perm_use_emoticons'] 	= $row['perm_use_emoticons'];
	                $cache[$row['id']]['perm_no_word_filter'] 	= $row['perm_no_word_filter'];
	                $cache[$row['id']]['perm_new_polls'] 		= $row['perm_new_polls'];
	                $cache[$row['id']]['perm_vote_polls'] 		= $row['perm_vote_polls'];
	                $cache[$row['id']]['perm_avatar_allow'] 	= $row['perm_avatar_allow'];
	                $cache[$row['id']]['perm_avatar_allow_gallery'] 	= $row['perm_avatar_allow_gallery'];
	                $cache[$row['id']]['perm_avatar_allow_upload'] 	= $row['perm_avatar_allow_upload'];
	                $cache[$row['id']]['perm_avatar_allow_external'] 	= $row['perm_avatar_allow_external'];
	                $cache[$row['id']]['perm_avatar_width'] 	= $row['perm_avatar_width'];
	                $cache[$row['id']]['perm_avatar_height'] 	= $row['perm_avatar_height'];
	                $cache[$row['id']]['perm_avatar_filesize'] 	= $row['perm_avatar_filesize'];
	                $cache[$row['id']]['display_user_title'] 	= $row['display_user_title'];
	                $cache[$row['id']]['override_user_title'] 	= $row['override_user_title'];
	                $cache[$row['id']]['perm_custom_user_title'] 	= $row['perm_custom_user_title'];
	                // My fingers hurt. Can I stop now?

                }
		
		return $cache;
		
	}	
	

	function cache_wordfilter()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("wordfilter", "*");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['word'] 		= $row['word'];
	                $cache[$row['id']]['replacement'] 	= $row['replacement'];
	                $cache[$row['id']]['perfect_match'] 	= $row['perfect_match'];

                }
		
		return $cache;
		
	}
	
		
	function cache_user_titles()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("user_titles", "*", "", "min_posts", "", "asc");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['title']	= $row['title'];
	                $cache[$row['id']]['min_posts']	= $row['min_posts'];

                }
		
		return $cache;
		
	}
	

	function cache_user_insignia()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("user_insignia", "*", "", "user_group, min_posts", "", "asc");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['user_group']	= $row['user_group'];
	                $cache[$row['id']]['min_posts']		= $row['min_posts'];
	                $cache[$row['id']]['newline']		= $row['newline'];
	                $cache[$row['id']]['image']		= $row['image'];
	                $cache[$row['id']]['text']		= $row['text'];
	                $cache[$row['id']]['repeat_no']		= $row['repeat_no'];

                }
		
		return $cache;
		
	}


	function cache_user_reputations()
	{
		
		global $db;
		
		$cache = array();		
                $db -> basic_select("user_reputations", "*", "", "min_rep", "", "asc");
                
                while($row = $db -> fetch_array())
                {
                
	                $cache[$row['id']]['name']	= $row['name'];
	                $cache[$row['id']]['min_rep']	= $row['min_rep'];

                }
		
		return $cache;
		
	}	
	


	function cache_plugins()
	{
		
		global $db;
		
		$cache = array();		

        $db -> basic_select("plugins", "`id`", "enabled=1 AND installed=1");
		
        if($db -> num_rows() < 1)
        	return $cache;

        while($plugin = $db -> fetch_array())
        {
        	
			$plugin_files = $db -> basic_select("plugins_files", "hook_file, hook_name", "", "hook_file, hook_name");
	        
			if($db -> num_rows($plugin_files))
		        while($row = $db -> fetch_array($plugin_files))
		            $cache[$row['hook_file'].":".$row['hook_name']][$plugin['id']]	= 1;

        }
        	   
		return $cache;
		
	}	
	
		
}

?>
