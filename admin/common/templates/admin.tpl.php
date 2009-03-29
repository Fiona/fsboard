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
*       Admin Template Class    *
*       Started by Fiona        *
*       08th Aug 2005           *
*********************************
*       Last edit by Fiona      *
*       04th Apr 2007           *
*********************************

This provides all the templates for the admin area.
This is all edited by hand, unlike the main template files
which are generated with code.
*/




// ----------------------------------------------------------------------------------------------------------------------


class template_admin 
{
	

	var $nav_current_entries = "";
	var $nav_group_id = 1;
		

	function admin_header($menu, $breadcrumb)
	{

		global $cache, $lang;

		$back = l("");
		$logout = l("admin/logout/");

		return <<<END

	<script language="javascript" type="text/javascript" src="{$cache -> cache['config']['board_url']}/admin/jscript/admin_global.js"></script>  
	<script language="javascript" type="text/javascript" src="{$cache -> cache['config']['board_url']}/admin/jscript/codemirror/codemirror.js"></script>

	<style type="text/css">
		p.admin_frame_top_left
		{
			padding:2px;
			font-size: 15px;
			float:right;
		}
		p.admin_frame_top_right
		{
			height:20px;
			background:#CDF3CD;
			border-bottom:1px dotted #aae6aa;
			margin-bottom : 20px;
			padding:2px;
			font-size: 15px;
		}

		div.admin_frame_menu
		{
			position: absolute;
			width: 200px;
		}

		div.admin_frame_main
		{
			width: auto;
			margin-left: 220px;
		}

		/* -------------------- */
		/* Breadcrumb stuff */
		/* -------------------- */
		.breadcrumb_wrapper
		{
			float : left;
			clear : left;
			margin : 10px auto;
			width : 95%;
			text-align : right;
			border : 1px dotted #99ff99;
			background-color : #ebffeb;
		}
 
		.breadcrumb_entry
		{
			font-size : 11px;
			padding : 4px;
		}
 
		.breadcrumb_seperator
		{
			font-size : 10px;
			color : #00f000;
		}
 
		a.breadcrumb_link:link, a.breadcrumb_link:visited, a.breadcrumb_link:active
		{
			color : #008800;
			text-decoration : none;
		}
		a.breadcrumb_link:hover
		{
			color : #00b600;
			text-decoration : none;
		}

		/* -------------------- */
		/* Menu                 */
		/* -------------------- */
		.admin_menu_groups_wrapper
		{
			padding: 3px;
			margin : 3px;
			margin-top : 10px;
			border : 1px solid;
		}
 
		.admin_menu_groups_wrapper_colour_1{ background-color: #d9ffd5; border-color: #C9F3C9; }
		.admin_menu_groups_wrapper_colour_2{ background-color: #d9fffb; border-color: #83fffb; }
		.admin_menu_groups_wrapper_colour_3{ background-color: #f0ddfb; border-color: #deadfb; }
		.admin_menu_groups_wrapper_colour_4{ background-color: #f9ffce; border-color: #f2ff96; }
		.admin_menu_groups_wrapper_colour_5{ background-color: #fbdddd; border-color: #fba2a2; }
		.admin_menu_groups_wrapper_colour_6{ background-color: #fbe1bb; border-color: #fbc678; }
		.admin_menu_groups_wrapper_colour_7{ background-color: #d4d4d4; border-color: #9a9797; }
		.admin_menu_groups_wrapper_colour_8{ background-color: #edceef; border-color: #e993ef; }
		.admin_menu_groups_wrapper_colour_9{ background-color: #c1e3ef; border-color: #77ccec; }
 
		.admin_menu_moveabit
		{
			margin-top : 7px;
		}
 
		.adminmenulink
		{
			background-color : #C9F3C9;
			margin : 0px;
			padding : 1px;
		}
		.adminmenulinkhover
		{
			background-color : #a5eba5;
			margin : 0px;
			padding : 1px;
		}
 
		.admin_menu_group
		{
			background-image : url('admin_menu_link_header.gif');
			background-repeat: repeat-y;
			background-position: bottom left;
			border : 1px dotted #99ff99;
			background-color : #9ae79a;
			width : auto;
			padding : 0 2px 0 2px;
		}
 
		.admin_menu_group_header
		{
			cursor : pointer;
			padding : 0px;
			margin : 0px;
		}
		
		.admin_menu_header_text
		{
			font-size : 9px;
			font-weight : bold;
			height : 19px;
			vertical-align : middle;
			margin : 1px 0 0 5px;
		}
		
		.admin_menu_header_button
		{
			vertical-align: middle;
		}
 
		.admin_menu_header_icon
		{
			float : right;
		}
 
		.admin_menu_link_group
		{
			width : auto;
			font-size : 9px;
			border : 1px solid #C9F3C9;
			background-color : #81e181;
			border-top :0px;
			margin-bottom : 2px;
		}

		.admin_menu_link_group_close
		{
			display : none;
		}

		/* -------------------- */
		/* Definiton lists      */
		/* -------------------- */
		dl.admin_info_list
		{
			margin: 10px 0 10px 10px;
			float : left;
			clear : left;
		}
		dl.admin_info_list dt
		{
			clear : left;
			float : left;
			width : 230px;
		}
		dl.admin_info_list dd
		{
			float : left;
		}


		/* ------------------------------- */
		/* Admin page specific form stuff  */
		/* ------------------------------- */
		img.header_icon
		{
			vertical-align : middle;
		}

		/* ------------------------------- */
		/* Admin area helpful help buttons */
		/* ------------------------------- */
		span.admin_help 
		{
			float : right;
		}
			span.admin_help a
			{
				text-decoration : none;
			}
			span.admin_help img
			{
				border : 0;
				vertical-align : middle;
			}



		/* --------------- */
		/* Hack to sort    */
		/* width on debug  */
		/* --------------- */
		.debug_level_2_wrapper
		{
			margin-left:200px; !important
		}

	</style>

	<p class="admin_frame_top_left">
        <b>
			<a href="{$back}" target="_top">Back to message board</a> -
			<a href="{$logout}" >{$lang['admin_logout']}</a>
		</b>
	</p>
	<p class="admin_frame_top_right">
		<b>FSBoard {$cache -> cache['config']['current_version']} {$lang['admin_area']}</b> ({$cache -> cache['config']['board_name']})
	</p>

	<div class="admin_frame_menu">
		{$menu}
	</div>
	<div class="admin_frame_main">
		{$breadcrumb}
		
		<div style="clear : left;"></div>
END;

	}


	function admin_footer($breadcrumb)
	{

		return <<<END
		{$breadcrumb}
	</div>
	<div style="clear: both;"></div>
END;

	}


	function admin_breadcrumb()
	{

		global $output;

		$breadcrumb = "";
		$a = 0;
                
		if(count($output -> breadcrumb) > 0 and $output -> show_breadcrumb)
		{
                        
			foreach($output -> breadcrumb as $crumb)
				if(++$a != count($output -> breadcrumb))
					$breadcrumb .= "<span class=\"breadcrumb_entry\"><a href=\"".$crumb['url']."\" class=\"breadcrumb_link\">".$crumb['title']."</a></span> <span class=\"breadcrumb_seperator\">&gt;</span> ";
				else
					$breadcrumb .= "<span class=\"breadcrumb_entry\"><b>".$crumb['title']."</b></span>";
                        
			$breadcrumb = "<div class=\"breadcrumb_wrapper\">".$breadcrumb."</div>";
                        
		}
     
		return $breadcrumb;

	}

	function form_header_icon($icon_name)
	{


		$imgdir = IMGDIR;

		return <<<END
			<img src="{$imgdir}icons/{$icon_name}.png" class="header_icon" /> 
END;

	}



	function help_button($text, $page, $action = "", $field = "")
	{
		
		global $lang;
        
		if($text)
			$text = $lang['help_button_text'];
        
		$imgdir = IMGDIR;

		return <<<END
			<span class="admin_help">
				<a href="#" rel="{$page}|{$action}|{$field}">
					{$text} <img src="{$imgdir}help.png" title="{$lang['help_button_text']}" />
				</a>
			</span>
END;
        
	}

	//***********************************************
	// The Admin navigation menu bar thing jobby you know
	//***********************************************
	function admin_menu()
	{
		
		global $lang, $cache, $output;

		// *****************
		// Admin logo
		// *****************
		$return = '	  
						<p align="center" class="small_text" style="margin : 10px">
								<img src="'.IMGDIR.'adminlogo.png" alt="'.$cache -> cache['config']['board_name'].'"><br />
								<a href="'.l("admin/").'">'.$lang['admin_menu_home'].'</a>
						</p>';
		

		// *****************
		// General				  
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_1\">";

		$return .= $this -> generate_menu_entry($lang['admin_menu_configuration'], l("admin/config/"));
		$return .= $this -> generate_menu_entry($lang['admin_menu_config_maintenance'], l("admin/config/show_group/maintenance/"), true);
		$return .= $this -> generate_menu_entry($lang['admin_menu_config_import'], l("admin/config/backup/"));
		$return .= $this -> generate_menu_header("general");

		$return .= $this -> generate_menu_move_a_bit();

		// *****************
		// Forums
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_configure_forums'], ROOT."admin/index.php?m=forums");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_add_forum'], ROOT."admin/index.php?m=forums&amp;m2=add");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_moderators'], ROOT."admin/index.php?m=moderators");
		$return .= $this -> generate_menu_header("forums", "*** ");
				
		$return .= "</div>";

		// *****************
		// Users
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_2\">";

		$return .= $this -> generate_menu_entry($lang['admin_menu_new_user'], l("admin/users/add/"));
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_search_users'], l("admin/users/search/"));
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_search_ip'], l("admin/users/ipsearch/"));
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_profile_fields'], l("admin/profile_fields/"));
		$return .= $this -> generate_menu_header("users", "*** ");

		$return .= $this -> generate_menu_move_a_bit();
				

		// *****************
		// Usergroups
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_user_groups'], ROOT."admin/index.php?m=usergroups");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_promotions'], ROOT."admin/index.php?m=promotions");
		$return .= $this -> generate_menu_header("usergroups", "*** ");

		$return .= "</div>";
								
		// *****************
		// Titles, insignia, reputations
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_3\">";
				
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_titles'], ROOT."admin/index.php?m=titles");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_insignia'], ROOT."admin/index.php?m=insignia");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_reputation'], ROOT."admin/index.php?m=reputations");
		$return .= $this -> generate_menu_header("titles_insignia_reputation", "*");

		$return .= $this -> generate_menu_move_a_bit();

		// *****************
		// Titles, insignia, reputations
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_send_mail'], ROOT."admin/index.php?m=mailer");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_email_logs'], ROOT."admin/index.php?m=emaillogs", true);
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_config_email'], ROOT."admin/index.php?m=config&amp;m2=group&amp;group=email", true);
		$return .= $this -> generate_menu_header("mailer", "*** ");

		$return .= "</div>";
		
		// *****************
		// Emoticons
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_4\">";

		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_manage_emoticons'], ROOT."admin/index.php?m=emoticons");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_add_emoticons'], ROOT."admin/index.php?m=emoticons&amp;m2=add");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_import_export_emoticons'], ROOT."admin/index.php?m=emoticons&amp;m2=importexport");
		$return .= $this -> generate_menu_header("emoticons", "*** ");

		$return .= $this -> generate_menu_move_a_bit();

		// *****************
		// Avatars
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_manage_avatars'], ROOT."admin/index.php?m=avatars");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_add_avatars'], ROOT."admin/index.php?m=avatars&amp;m2=add");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_import_export_avatars'], ROOT."admin/index.php?m=avatars&amp;m2=importexport");
		$return .= $this -> generate_menu_header("avatars", "*** ");

		$return .= $this -> generate_menu_move_a_bit();

		// *****************
		// Post icons
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_manage_post_icons'], ROOT."admin/index.php?m=posticons");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_add_post_icons'], ROOT."admin/index.php?m=posticons&amp;m2=add");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_import_export_post_icons'], ROOT."admin/index.php?m=posticons&amp;m2=importexport");
		$return .= $this -> generate_menu_header("post_icons", "*** ");
				
		$return .= "</div>";

		// *****************
		// Attatchments
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_5\">";

		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_filetypes'], ROOT."admin/index.php?m=attachments&amp;m2=filetypes");
		$return .= $this -> generate_menu_header("attachments", "*");

		$return .= $this -> generate_menu_move_a_bit();
				
		// *****************
		// BBCode
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_bbcode'], ROOT."admin/index.php?m=bbcode");
		$return .= $this -> generate_menu_header("bbcode", "*** ");

		$return .= $this -> generate_menu_move_a_bit();
				
		// *****************
		// Word filter
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_wordfilter'], ROOT."admin/index.php?m=wordfilter");
		$return .= $this -> generate_menu_header("wordfilter_title", "*** ");
				
		$return .= "</div>";
				
		// *****************
		// Templates and themes
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_6\">";
				
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_templates'], ROOT."admin/index.php?m=templates");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_export_templates'], ROOT."admin/index.php?m=templates&amp;m2=importexport");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_themes'], ROOT."admin/index.php?m=themes");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_export_themes'], ROOT."admin/index.php?m=themes&amp;m2=importexport");
		$return .= $this -> generate_menu_header("templates_and_themes", "*** ");
				
		$return .= $this -> generate_menu_move_a_bit();
								
		// *****************
		// Languages
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_language_manager'], ROOT."admin/index.php?m=langs");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_search_in_phrases'], ROOT."admin/index.php?m=langs&amp;m2=search");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_language_import'], ROOT."admin/index.php?m=langs&amp;m2=importexport");
		$return .= $this -> generate_menu_header("languages_and_phrases", "*** ");
				
		$return .= "</div>";
								
		// *****************
		// Cache and logs
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_7\">";
				
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_cache'], ROOT."admin/index.php?m=cache");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_admin_logs'], ROOT."admin/index.php?m=adminlogs");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_email_logs'], ROOT."admin/index.php?m=emaillogs");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_email_error_logs'], ROOT."admin/index.php?m=emaillogs&amp;error=1");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_config_email'], ROOT."admin/index.php?m=config&amp;m2=group&amp;group=email", true);
		$return .= $this -> generate_menu_header("cache_and_logs", "*** ");
				
		$return .= $this -> generate_menu_move_a_bit();
				
		// *****************
		// SQL Tools
		// *****************
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_sql_explorer'], ROOT."admin/index.php?m=sqltools");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_sql_query'], ROOT."admin/index.php?m=sqltools&amp;m2=query");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_sql_backup'], ROOT."admin/index.php?m=sqltools&amp;m2=backup");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_sql_server_status'], ROOT."admin/index.php?m=sqltools&amp;m2=serverstatus");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_sql_system_vars'], ROOT."admin/index.php?m=sqltools&amp;m2=systemvars");
		$return .= $this -> generate_menu_header("sql_tools", "*** ");
				
		$return .= "</div>";				

		// *****************
		// Common tasks
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_8\">";
				
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_task_manager'], ROOT."admin/index.php?m=tasks");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_task_logs'], ROOT."admin/index.php?m=tasklogs");
		$return .= $this -> generate_menu_header("common_tasks", "*** ");
				
		$return .= "</div>";				


		// *****************
		// Plugins
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_9\">";
				
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_plugins_manager'], ROOT."admin/index.php?m=plugins");
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_import_export_plugins'], ROOT."admin/index.php?m=plugins&amp;m2=importexport");
		$return .= $this -> generate_menu_header("plugins", "*** ");
				
		$return .= "</div>";				
				
		// *****************
		// Undelete
		// *****************
		$return .= "<div class=\"admin_menu_groups_wrapper admin_menu_groups_wrapper_colour_1\">";
				
		$return .= $this -> generate_menu_entry("*** ".$lang['admin_menu_undelete_tool'], ROOT."admin/index.php?m=undelete");
		$return .= $this -> generate_menu_header("undelete", "*** ");
				
		$return .= "</div>";				
				
		return $return;
				
	}


	// -----------------------------------------------------------------------------
		
		
	function generate_menu_header($header_name, $marker = "")
	{

		global $lang, $user;
			   
		$table = new table_generate;

		$return = "";

		// Menu entry heade

		// check if we should be open
		if(in_array($this -> nav_group_id, $user -> admin_menu))
		{
			$extra_class = "";
			$img_name = "collapse.gif";
		}
		else
		{
			$extra_class = " admin_menu_link_group_close";
			$img_name = "expand.gif";
		}

		$image_dir = IMGDIR;

		$extra_class = 
			$return .= <<<END
			<div class="admin_menu_group">
			<div class="admin_menu_group_header">
			<p class="admin_menu_header_text">
			<img src="{$image_dir}icons/{$header_name}.png"  class="admin_menu_header_icon" title="{$lang['admin_menu_'.$header_name]}" />
			<img id="img_{$this -> nav_group_id}" src="{$image_dir}{$img_name}" class="admin_menu_header_button">&nbsp;{$marker}{$lang['admin_menu_'.$header_name]}
		</p>
			  </div>
			  <div class="admin_menu_link_group{$extra_class}" id="row_{$this -> nav_group_id}">{$this -> nav_current_entries}</div>
																																	</div>
END;

		$this -> nav_group_id ++;
		$this -> nav_current_entries = "";
					
		// Send it back
		return $return;
			
	}
		
		
	function generate_menu_entry($link_text, $link_url, $menu_extra = false)
	{

		$row = "";
		
		$row .= '<div onclick="window.location = \''.$link_url.'\';" class="adminmenulink">';
		
		if($menu_extra)
			$row .= '<img src="'.IMGDIR.'/menu_extra_icon.gif"> <a href="'.$link_url.'"><i>'.$link_text.'</i></a>';
		else
			$row .= '<a href="'.$link_url.'">'.$link_text.'</a>';
												
		$row .= '</div>';
		
		$this -> nav_current_entries .= $row;

	}

		
	function generate_menu_move_a_bit()
	{
		
		return "<div class=\"admin_menu_moveabit\"></div>";
		
	}
                
}

?>
