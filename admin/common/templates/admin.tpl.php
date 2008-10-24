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
	var $nav_group_id = 0;
		

        //***********************************************
        // Page wrapper.
        //***********************************************
        function page_wrapper($theme_folder, $page, $title, $breadcrumb, $debug_level_1, $debug_level_2)
        {
        
                global $lang, $cache;
                
                return '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta HTTP-EQUIV="content-type" CONTENT="text/html; charset='.CHARSET.'">
    <title>'.$title.' ('.$lang['powered_by'].' FSBoard)</title>
    <link rel="stylesheet" type="text/css" href="'.ROOT.'admin/themes/'.$theme_folder.'/style.css" />
    
    <script src="'.ROOT.'jscript/jquery.js" type="text/javascript"></script>  
    <script src="'.ROOT.'admin/admin_jscript.js" type="text/javascript"></script>  

</head>
<body>

<script type="text/javascript">define_parent_title();</script>
		
'.$breadcrumb.'

'.$page.'

'.$breadcrumb.'

        <br /><br /><p class="footer">
                '.$debug_level_1.'<br />

                <b>Administration Area</b><br />
                <a href="http://www.fsboard.com/">FSBoard</a> Development Version '.$cache -> cache['config']['current_version'].'
                &copy; 2006
                <i>Fiona Burrows</i><br />
		'.$cache -> cache['config']['copyright_text'].'
                <br />
        </p>

        <div style="margin:5px">
                '.$debug_level_2.'
        </div>

</body>

</html>';
        
        }
        
        //***********************************************
        // Redirect message
        //***********************************************
        function redirect($msg, $redirect_to, $header, $theme_folder)
        {
        
                global $lang, $cache;
        
                return '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

    <meta HTTP-EQUIV="content-type" CONTENT="text/html; charset='.CHARSET.'">
    <title>'.$lang['redirecting'].'</title>
    '.$header.'
    <link rel="stylesheet" type="text/css" href="'.ROOT.'admin/themes/'.$theme_folder.'/style.css" />

</head>

<body>

        <br />
        <table width=60% align="center" style="border-collapse: collapse;">
                <tr>
                        <td class="strip3" colspan=2>
                                <p><b>'.$lang['redirecting'].'</b></p>
                        </td>
                </tr>
                <tr>
                        <td class="normalcell" style="padding : 10px;" align="center">
                                <p>'.$msg.'<br /> <br />
                                '.$lang['forwarding_you'].' <a href="'.$redirect_to.'">'.$lang['here'].'</a>.</p>
                        </td>
                </tr>
        </table>

</body>

</html>';
        
        }

        //***********************************************
        // Generic Message box
        //***********************************************
        function message($msg_title="", $msg="")
        {

                global $lang;

                $table = new table_generate;
                
                return 
                        $table -> start_table("", "border-collapse: collapse; margin-top : 10px; margin-bottom : 10px;", "center", "60%").
                        $table -> add_basic_row('<b>'.$msg_title.':</b>', "strip2", "", "left").
                        $table -> add_basic_row($msg, "normalcell", "", "left").
                        $table -> end_table();
                
        }
        
        //***********************************************
        // Errors
        //***********************************************
        function critical_error($msg)
        {
        
                global $lang;

                $table = new table_generate;
                
                return 
                        $table -> start_table("", "border-collapse: collapse; margin-top : 10px; margin-bottom : 10px;", "center", "60%").
                        $table -> add_basic_row('<b>'.$lang['error_found'].':</b>', "errorheader", "", "left").
                        $table -> add_basic_row($msg, "errorcell", "", "left").
                        $table -> end_table();

        }
        
        function normal_error($msg, $title = "")
        {
        
                global $lang;
        
                if($title == "")
                        $title = $lang['error_found'];
        
                $table = new table_generate;
                
                return 
                        $table -> start_table("", "border-collapse: collapse; margin-top : 10px; margin-bottom : 10px;", "center", "60%").
                        $table -> add_basic_row('<b>'.$title.':</b>', "strip2", "", "left").
                        $table -> add_basic_row($msg, "errorcell2", "", "left").
                        $table -> end_table();
                
        }

        //***********************************************
        // Admin area login form
        //***********************************************        
        function login($extra_url = "")
        {
        
                global $lang, $cache;
        
                return '
        <br />
        <script language=\'JavaScript\' type=\'text/javascript\'>
        <!--
        function ValidateForm() {
                var Check = 0;
                if (document.loginform.username.value == \'\') { Check = 1; }
                if (document.loginform.password.value == \'\') { Check = 1; }

                if (Check == 1) {
                        alert(\'You must input your username and password!\');
                        return false;
                } else {
                        document.loginform.submit.disabled = true;
                        return true;
                }
        }
        //-->
        </script>
        <form action="index.php?m=login'.$extra_url.'" method="post" name="loginform" onsubmit="return ValidateForm()"">
        
        <table width=60% align="center" style="border-collapse: collapse;">
                <tr>
                        <td class="strip3" colspan=2>
                                <p><b>'.$lang['admin_login_title'].'</b></p>
                        </td>
                </tr>
                <tr>
                        <td  class="normalcell" style="padding:10px" colspan=2>
                                <p>'.$lang['admin_login_msg'].'</p>
                        </td>
                </tr>
                <tr>
                        <td class="normalcell" width=50%>
                                <p><b>'.$lang['admin_username'].':</b></p>
                        </td>
                        <td class="normalcell" width=50%>
                                <input type="text" class="inputtext" name="username" style="width : 99%">
                        </td>
                </tr>
                <tr>
                        <td class="normalcell" width=50%>
                                <p><b>'.$lang['admin_password'].':</b></p>
                        </td>
                        <td class="normalcell" width=50%>
                                <input type="password" class="inputtext" name="password" style="width : 99%">
                        </td>
                </tr>
                <tr>
                        <td class="strip2" align="center" colspan=2>
                                <input class="submitbutton" type="submit" name="submit" value="'.$lang['admin_login'].'">
                        </td>
                </tr>
        </table>
        
        </form>
        <br />';
        
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
                        <script src="'.ROOT.'admin/admin_jscript.js" type="text/javascript"></script>  
                        
                        <p align="center" class="small_text" style="margin : 10px">
                                <img src="'.IMGDIR.'/adminlogo.png" alt="'.$cache -> cache['config']['board_name'].'"><br />
                                <a href="'.ROOT.'admin/index.php?m=index" target="page">'.$lang['admin_menu_home'].'</a>
                        </p>';
        

                // *****************
                // General                
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_1\">";

				$return .= $this -> generate_menu_entry($lang['admin_menu_configuration'], ROOT."admin/index.php?m=config");
				$return .= $this -> generate_menu_entry($lang['admin_menu_config_maintenance'], ROOT."admin/index.php?m=config&amp;m2=group&amp;group=maintenance", true);
				$return .= $this -> generate_menu_entry($lang['admin_menu_config_import'], ROOT."admin/index.php?m=config&amp;m2=importexport");
                $return .= $this -> generate_menu_header("general");

                $return .= $this -> generate_menu_move_a_bit();

                // *****************
				// Forums
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_configure_forums'], ROOT."admin/index.php?m=forums");
				$return .= $this -> generate_menu_entry($lang['admin_menu_add_forum'], ROOT."admin/index.php?m=forums&amp;m2=add");
				$return .= $this -> generate_menu_entry($lang['admin_menu_moderators'], ROOT."admin/index.php?m=moderators");
                $return .= $this -> generate_menu_header("forums");
                
                $return .= "</div>";

                // *****************
				// Users
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_2\">";

				$return .= $this -> generate_menu_entry($lang['admin_menu_new_user'], ROOT."admin/index.php?m=users&amp;m2=add");
				$return .= $this -> generate_menu_entry($lang['admin_menu_search_users'], ROOT."admin/index.php?m=users&m2=search");
				$return .= $this -> generate_menu_entry($lang['admin_menu_search_ip'], ROOT."admin/index.php?m=users&m2=ipsearch");
				$return .= $this -> generate_menu_entry($lang['admin_menu_profile_fields'], ROOT."admin/index.php?m=profilefields");
                $return .= $this -> generate_menu_header("users");

                $return .= $this -> generate_menu_move_a_bit();
                

                // *****************
				// Usergroups
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_user_groups'], ROOT."admin/index.php?m=usergroups");
				$return .= $this -> generate_menu_entry($lang['admin_menu_promotions'], ROOT."admin/index.php?m=promotions");
                $return .= $this -> generate_menu_header("usergroups");

                $return .= "</div>";
                                
                // *****************
				// Titles, insignia, reputations
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_3\">";
                
                $return .= $this -> generate_menu_entry($lang['admin_menu_titles'], ROOT."admin/index.php?m=titles");
				$return .= $this -> generate_menu_entry($lang['admin_menu_insignia'], ROOT."admin/index.php?m=insignia");
				$return .= $this -> generate_menu_entry($lang['admin_menu_reputation'], ROOT."admin/index.php?m=reputations");
                $return .= $this -> generate_menu_header("titles_insignia_reputation");

                $return .= $this -> generate_menu_move_a_bit();

                // *****************
				// Titles, insignia, reputations
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_send_mail'], ROOT."admin/index.php?m=mailer");
				$return .= $this -> generate_menu_entry($lang['admin_menu_email_logs'], ROOT."admin/index.php?m=emaillogs", true);
				$return .= $this -> generate_menu_entry($lang['admin_menu_config_email'], ROOT."admin/index.php?m=config&amp;m2=group&amp;group=email", true);
                $return .= $this -> generate_menu_header("mailer");

                $return .= "</div>";
        
                // *****************
				// Emoticons
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_4\">";

				$return .= $this -> generate_menu_entry($lang['admin_menu_manage_emoticons'], ROOT."admin/index.php?m=emoticons");
				$return .= $this -> generate_menu_entry($lang['admin_menu_add_emoticons'], ROOT."admin/index.php?m=emoticons&amp;m2=add");
				$return .= $this -> generate_menu_entry($lang['admin_menu_import_export_emoticons'], ROOT."admin/index.php?m=emoticons&amp;m2=importexport");
                $return .= $this -> generate_menu_header("emoticons");

                $return .= $this -> generate_menu_move_a_bit();

                // *****************
                // Avatars
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_manage_avatars'], ROOT."admin/index.php?m=avatars");
				$return .= $this -> generate_menu_entry($lang['admin_menu_add_avatars'], ROOT."admin/index.php?m=avatars&amp;m2=add");
				$return .= $this -> generate_menu_entry($lang['admin_menu_import_export_avatars'], ROOT."admin/index.php?m=avatars&amp;m2=importexport");
                $return .= $this -> generate_menu_header("avatars");

                $return .= $this -> generate_menu_move_a_bit();

                // *****************
                // Post icons
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_manage_post_icons'], ROOT."admin/index.php?m=posticons");
				$return .= $this -> generate_menu_entry($lang['admin_menu_add_post_icons'], ROOT."admin/index.php?m=posticons&amp;m2=add");
				$return .= $this -> generate_menu_entry($lang['admin_menu_import_export_post_icons'], ROOT."admin/index.php?m=posticons&amp;m2=importexport");
                $return .= $this -> generate_menu_header("post_icons");
                
                $return .= "</div>";

                // *****************
				// Attatchments
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_5\">";

				$return .= $this -> generate_menu_entry($lang['admin_menu_filetypes'], ROOT."admin/index.php?m=attachments&amp;m2=filetypes");
                $return .= $this -> generate_menu_header("attachments");

                $return .= $this -> generate_menu_move_a_bit();
                
                // *****************
                // BBCode
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_bbcode'], ROOT."admin/index.php?m=bbcode");
                $return .= $this -> generate_menu_header("bbcode");

                $return .= $this -> generate_menu_move_a_bit();
                
                // *****************
                // Word filter
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_wordfilter'], ROOT."admin/index.php?m=wordfilter");
                $return .= $this -> generate_menu_header("wordfilter_title");
                
                $return .= "</div>";
                
                // *****************
				// Templates and themes
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_6\">";
                
				$return .= $this -> generate_menu_entry($lang['admin_menu_templates'], ROOT."admin/index.php?m=templates");
				$return .= $this -> generate_menu_entry($lang['admin_menu_export_templates'], ROOT."admin/index.php?m=templates&amp;m2=importexport");
				$return .= $this -> generate_menu_entry($lang['admin_menu_themes'], ROOT."admin/index.php?m=themes");
				$return .= $this -> generate_menu_entry($lang['admin_menu_export_themes'], ROOT."admin/index.php?m=themes&amp;m2=importexport");
                $return .= $this -> generate_menu_header("templates_and_themes");
                
                $return .= $this -> generate_menu_move_a_bit();
                                
                // *****************
				// Languages
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_language_manager'], ROOT."admin/index.php?m=langs");
				$return .= $this -> generate_menu_entry($lang['admin_menu_search_in_phrases'], ROOT."admin/index.php?m=langs&amp;m2=search");
				$return .= $this -> generate_menu_entry($lang['admin_menu_language_import'], ROOT."admin/index.php?m=langs&amp;m2=importexport");
                $return .= $this -> generate_menu_header("languages_and_phrases");
                
                $return .= "</div>";
                                
                // *****************
				// Cache and logs
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_7\">";
                
				$return .= $this -> generate_menu_entry($lang['admin_menu_cache'], ROOT."admin/index.php?m=cache");
				$return .= $this -> generate_menu_entry($lang['admin_menu_admin_logs'], ROOT."admin/index.php?m=adminlogs");
				$return .= $this -> generate_menu_entry($lang['admin_menu_email_logs'], ROOT."admin/index.php?m=emaillogs");
				$return .= $this -> generate_menu_entry($lang['admin_menu_email_error_logs'], ROOT."admin/index.php?m=emaillogs&amp;error=1");
				$return .= $this -> generate_menu_entry($lang['admin_menu_config_email'], ROOT."admin/index.php?m=config&amp;m2=group&amp;group=email", true);
                $return .= $this -> generate_menu_header("cache_and_logs");
                
                $return .= $this -> generate_menu_move_a_bit();
                
                // *****************
				// SQL Tools
                // *****************
				$return .= $this -> generate_menu_entry($lang['admin_menu_sql_explorer'], ROOT."admin/index.php?m=sqltools");
				$return .= $this -> generate_menu_entry($lang['admin_menu_sql_query'], ROOT."admin/index.php?m=sqltools&amp;m2=query");
				$return .= $this -> generate_menu_entry($lang['admin_menu_sql_backup'], ROOT."admin/index.php?m=sqltools&amp;m2=backup");
				$return .= $this -> generate_menu_entry($lang['admin_menu_sql_server_status'], ROOT."admin/index.php?m=sqltools&amp;m2=serverstatus");
				$return .= $this -> generate_menu_entry($lang['admin_menu_sql_system_vars'], ROOT."admin/index.php?m=sqltools&amp;m2=systemvars");
                $return .= $this -> generate_menu_header("sql_tools");
                
                $return .= "</div>";                

                // *****************
				// Common tasks
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_8\">";
                
				$return .= $this -> generate_menu_entry($lang['admin_menu_task_manager'], ROOT."admin/index.php?m=tasks");
				$return .= $this -> generate_menu_entry($lang['admin_menu_task_logs'], ROOT."admin/index.php?m=tasklogs");
                $return .= $this -> generate_menu_header("common_tasks");
                
                $return .= "</div>";                


                // *****************
				// Plugins
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_9\">";
                
				$return .= $this -> generate_menu_entry($lang['admin_menu_plugins_manager'], ROOT."admin/index.php?m=plugins");
				$return .= $this -> generate_menu_entry($lang['admin_menu_import_export_plugins'], ROOT."admin/index.php?m=plugins&amp;m2=importexport");
                $return .= $this -> generate_menu_header("plugins");
                
                $return .= "</div>";                
                
                // *****************
				// Undelete
                // *****************
                $return .= "<div class=\"adminmenugroupwrapper adminmenugroupwrapper_colour_1\">";
                
				$return .= $this -> generate_menu_entry($lang['admin_menu_undelete_tool'], ROOT."admin/index.php?m=undelete");
                $return .= $this -> generate_menu_header("undelete");
                
                $return .= "</div>";                
                
                return $return;
                
        }


        // -----------------------------------------------------------------------------
        
        
        function generate_menu_header($header_name)
        {

		global $lang;        	
               
                $table = new table_generate;

                $return = "";

                // Menu entry header      
                $return .= $table -> start_table("adminmenutable", "", "center", "100%").
                        $table -> add_row(
                                array('
                                <div class = "admingroupheader">
	                                <img src="'.IMGDIR.'/icons/'.$header_name.'.png"  class="adminmenuheadericon" title="'.$lang['admin_menu_'.$header_name].'" />
                                        <img id="img_'.$this -> nav_group_id.'" src="'.IMGDIR.'/expand.gif" class="adminmenuheaderbutton">&nbsp;<span class="adminmenuheadertext">'.$lang['admin_menu_'.$header_name].'</span>
                                </div>')
                        , "admingroupheaderrow", "", 'onclick="javascript:collapse(\''.$this -> nav_group_id.'\', \''.IMGDIR.'\');"');

		// The entries
		$return .= $table -> add_row(array('<div style="display:none;" class = "adminlinkgroup" id="row_'.$this -> nav_group_id.'">'.$this -> nav_current_entries.'</div>'), "adminlinkgrouprow", "");

		// Footer
                $return .= $table -> end_table();

        	$this -> nav_group_id ++;
        	$this -> nav_current_entries = "";
	                
                // Send it back
                return $return;
        	
        }
        
        
        function generate_menu_entry($link_text, $link_url, $menu_extra = false)
        {

		$row = "";
		
		$row .= '<div onclick="parent.frames.page.location = \''.$link_url.'\';" class="adminmenulink" onmouseover="this.className=\'adminmenulinkhover\'" onmouseout="this.className=\'adminmenulink\'">';
		
		if($menu_extra)
		        $row .= '<img src="'.IMGDIR.'/menu_extra_icon.gif"> <a href="'.$link_url.'" target="page"><i>'.$link_text.'</i></a>';
		else
		        $row .= '<a href="'.$link_url.'" target="page">'.$link_text.'</a>';
		                                        
		$row .= '</div>';
		
		$this -> nav_current_entries .= $row;

        }

        
        function generate_menu_move_a_bit()
        {
        
        	return "<div class=\"adminmenutable_moveabit\"></div>";
        
        }
        
        
}
?>
