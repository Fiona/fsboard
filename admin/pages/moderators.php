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
*       Moderators Edit         *
*       Started by Fiona        *
*       11th Feb 2006           *
*********************************
*       Last edit by Fiona      *
*       09th Mar 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Yeah, I'm using the forums language file.
// Shut up.
//***********************************************
load_language_group("admin_forums");


//***********************************************
// Functions plzkthx
//***********************************************
include ROOT."admin/common/funcs/forums.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_moderators'], "index.php?m=moderators");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_moderator(true);
                break;

        case "doadd":
                do_add_moderator();
                break;

        case "edit":
                page_add_edit_moderator();
                break;

        case "doedit":
                do_edit_moderator();
                break;

        case "dodelete":
                do_delete_moderator();
                break;

        default:
                page_main();
                
}


//***********************************************
// Main forum view and mod list
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['moderators_title'];

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ----------------
        // FORUMS LIST
        // ----------------
        $output -> add(
                $form -> start_form("newmods", ROOT."admin/index.php?m=moderators&amp;m2=add", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	    	$table -> add_top_table_header($lang['moderators_title'], 3, "forums").
                $table -> add_row(array($lang['moderators_check'],$lang['moderators_name'],$lang['moderators_current']), "strip2")
        );

	// Fetch user groups
	$groups_array = array();
	
	$db -> basic_select("user_groups", "id, name", "", "id");
	
	while($g = $db -> fetch_array())
		$groups_array[$g['id']] = $g['name'];
			
        // Grab all forums
        $forums = $db -> query("select id, parent_id, name, is_category from ".$db -> table_prefix."forums order by position asc");

        // Get amount
        $forums_amount = $db -> num_rows($forums);

        // No forums?
        if($forums_amount < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['admin_no_forums']."</b>", "normalcell",  "padding : 10px", "center", "100%", "3")
                );        
                
        else
        {

                // Sort it out
                $forums_tree = return_admin_forums_tree($forums);

                // Print out the form
                print_admin_moderators_layout(-1, $forums_tree, $table, $form, 0, 0, $groups_array);
        
        }

        // Build drop down
        foreach($groups_array as $gid => $gname)
        {
                $group_dropdown_values[] .= $gid;
                $group_dropdown_text[] .= $gname;
        }

        $output -> add(
                $table -> end_table().
                // --------------
                // New mod form
                // --------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['moderators_add_title'], "strip1",  "", "left", "100%", "2").
                
                $table -> add_row(
                        array(
                                array($lang['moderators_add_user_title'], "50%"),
                                array($form -> input_text("username", ""), "50%")
                        ),
                 "normalcell").
                $table -> add_row(
                        array(
                                array($lang['moderators_add_usergroup_title'], "50%"),
                                array($form -> input_dropdown("usergroup", "", $group_dropdown_values, $group_dropdown_text), "50%")
                        ),
                 "normalcell").
                
                $table -> add_basic_row($form -> submit("submit", $lang['moderators_add_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );
        
}



//***********************************************
// Form for adding or editing a moderator
//***********************************************
function page_add_edit_moderator($adding = false, $mod_info = "")
{

        global $output, $lang, $db, $template_admin;

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ***************************
        // Need different headers
        // ***************************
        if($adding)
        {

                // Checking user?
                if($_POST['username'])
                {

                        $user = $db -> query("select id, username from ".$db->table_prefix."users where username='".$_POST['username']."' limit 1");
                        
                        if($db -> num_rows($user) < 1)                
                        {
                                $output -> add($template_admin -> normal_error($lang['add_mod_invalid_username']));
                                page_main();
                                return;
                        }                

                        $user_array = $db -> fetch_array($user);
                        
                        $title = $output -> replace_number_tags($lang['add_mod_title_user'], array($user_array['username']));         

                        // *********************
                        // Set page title
                        // *********************
                        $output -> page_title = $lang['add_mod_page_title_user'];
                        
                }
                // Checking user group?
                else
                {

                        $group = $db -> query("select id, name from ".$db->table_prefix."user_groups where id='".$_POST['usergroup']."' limit 1");
                                        
                        if($db -> num_rows($group) < 1)                
                        {
                                $output -> add($template_admin -> normal_error($lang['add_mod_invalid_usergroup']));
                                page_main();
                                return;
                        }       

                        $group_array = $db -> fetch_array($group);
                        
                        $title = $output -> replace_number_tags($lang['add_mod_title_usergroup'], array($group_array['name']));         

                        // *********************
                        // Set page title
                        // *********************
                        $output -> page_title = $lang['add_mod_page_title_usergroup'];
                
                }

                // Get forums from form
                $forums_array = $_POST['forums'];
                
                // None?
                if(count($forums_array) == null)
                {
                        $output -> add($template_admin -> normal_error($lang['add_mod_no_forums']));
                        page_main();
                        return;
                }            
                
                $forums_values = "";
                
                // Build forum hidden value
                foreach($forums_array as $key => $val)    
                        $forums_values .= $key."|";
                        
                $forums_values = substr($forums_values, 0, -1);

                // Do the form top
                $output -> add(
                        $form -> start_form("addmod", ROOT."admin/index.php?m=moderators&amp;m2=doadd", "post").
                        $form -> hidden("forums", $forums_values)
                );
                
                if($_POST['username'])
                        $output -> add($form -> hidden("user", $user_array['id']));
                else
                        $output -> add($form -> hidden("usergroup", $group_array['id']));

		$output -> add_breadcrumb($lang['breadcrumb_moderators_add'], "index.php?m=moderators&amp;m2=add");
                        
                $output -> add(
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		    	$table -> add_top_table_header($title, 2, "forums")
                );
                
                $submit_lang = $lang['add_mod_submit'];
                
        }
        // ********************
        // Editing header
        // ********************
        else
        {

                // Nab moderator
                $moderator = $db -> query("select * from ".$db->table_prefix."moderators where id='".$_GET['id']."' limit 1");
                                
                if($db -> num_rows($moderator) < 1)                
                {
                        $output -> add($template_admin -> normal_error($lang['invalid_moderator_id']));
                        page_main();
                        return;
                }       
                
                $mod_info = $db -> fetch_array($moderator);
                
                // Work out the title
                if($mod_info['group_id'] > -1)
                        $title = $output -> replace_number_tags($lang['edit_mod_title_usergroup'], array($mod_info['group_name']));         
                else
                        $title = $output -> replace_number_tags($lang['add_mod_title_user'], array($mod_info['username']));         

		$output -> add_breadcrumb($lang['breadcrumb_moderators_edit'], "index.php?m=moderators&amp;m2=&amp;id=".$_GET['id']);
                
                // Do the form top
                $output -> add(
                        $form -> start_form("addmod", ROOT."admin/index.php?m=moderators&amp;m2=doedit&amp;id=".$_GET['id'], "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		    	$table -> add_top_table_header($title, 2, "forums")
                );

                $submit_lang = $lang['edit_mod_submit'];
        
        }

        // ***************
        // Rest of form
        // ***************
        $output -> add(
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_edit_post'], "perm_edit_post", $mod_info['perm_edit_post'], "perm_edit_post").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_edit_topic'], "perm_edit_topic", $mod_info['perm_edit_topic'], "perm_edit_topic").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_delete_post'], "perm_delete_post", $mod_info['perm_delete_post'], "perm_delete_post").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_delete_topic'], "perm_delete_topic", $mod_info['perm_delete_topic'], "perm_delete_topic").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_close_topic'], "perm_close_topic", $mod_info['perm_close_topic'], "perm_close_topic").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_move_topic'], "perm_move_topic", $mod_info['perm_move_topic'], "perm_move_topic").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_sticky_topic'], "perm_sticky_topic", $mod_info['perm_sticky_topic'], "perm_sticky_topic").
                $table -> simple_input_row_yesno($form, $lang['add_mod_perm_view_ip'], "perm_view_ip", $mod_info['perm_view_ip'], "perm_view_ip").
		$table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );
                
}



//***********************************************
// Doing the add
//***********************************************
function do_add_moderator()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Crap from form
        $mod_info = 
                array(
                        "perm_edit_post"        => $_POST['perm_edit_post'],
                        "perm_edit_topic"       => $_POST['perm_edit_topic'],
                        "perm_delete_post"      => $_POST['perm_delete_post'],
                        "perm_delete_topic"     => $_POST['perm_delete_topic'],
                        "perm_view_ip"          => $_POST['perm_view_ip'],
                        "perm_close_topic"      => $_POST['perm_close_topic'],
                        "perm_move_topic"       => $_POST['perm_move_topic'],
                        "perm_sticky_topic"     => $_POST['perm_sticky_topic']
                );
        
        // Checking user?
        if($_POST['user'])
        {

                $user = $db -> query("select id, username from ".$db->table_prefix."users where id='".$_POST['user']."' limit 1");
                
                if($db -> num_rows($user) < 1)                
                {
                        $output -> add($template_admin -> normal_error($lang['add_mod_invalid_username']));
                        page_main();
                        return;
                }                

                $user_array = $db -> fetch_array($user);

                $mod_info['user_id'] = $user_array['id'];
                $mod_info['username'] = $user_array['username'];
                
        }
        // Checking user group?
        else
        {

                $group = $db -> query("select id, name from ".$db->table_prefix."user_groups where id='".$_POST['usergroup']."' limit 1");
                                
                if($db -> num_rows($group) < 1)                
                {
                        $output -> add($template_admin -> normal_error($lang['add_mod_invalid_usergroup']));
                        page_main();
                        return;
                }       

                $group_array = $db -> fetch_array($group);

                $mod_info['group_id'] = $group_array['id'];
                $mod_info['group_name'] = $group_array['name'];
                
        }


        // ******************
        // Spin through each forum
        // ******************
        $forums_array = explode("|", $_POST['forums']);
        
        // None?
        if(count($forums_array) == null || count($forums_array) == 0)
        {
                $output -> add($template_admin -> normal_error($lang['add_mod_no_forums']));
                page_main();
                return;
        }            

        foreach($forums_array as $val)
        {

                $mod_info['forum_id'] = $val;
                
                // ******************
                // Add it!
                // ******************
                if(!$db -> basic_insert("moderators", $mod_info))
                {
                        $output -> add($template_admin -> critical_error($lang['add_moderator_error']));
                        page_add_edit_moderator(true, $mod_info);
                        return;
                }               
                
        }
               
        // ******************
        // Update cache
        // ******************
        $cache -> update_cache("moderators");

        // ******************
        // Log it!
        // ******************
        log_admin_action("moderators", "doadd", "Added moderator");
        
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=moderators", $lang['moderator_added_sucessfully']);

}


//***********************************************
// Oh edit me baby
//***********************************************
function do_edit_moderator()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Crap from form
        $mod_info = 
                array(
                        "perm_edit_post"        => $_POST['perm_edit_post'],
                        "perm_edit_topic"       => $_POST['perm_edit_topic'],
                        "perm_delete_post"      => $_POST['perm_delete_post'],
                        "perm_delete_topic"     => $_POST['perm_delete_topic'],
                        "perm_view_ip"          => $_POST['perm_view_ip'],
                        "perm_close_topic"      => $_POST['perm_close_topic'],
                        "perm_move_topic"       => $_POST['perm_move_topic'],
                        "perm_sticky_topic"     => $_POST['perm_sticky_topic']
                );
        
        // Nab moderator
        $moderator = $db -> query("select id,username,group_name from ".$db->table_prefix."moderators where id='".$_GET['id']."' limit 1");
                        
        if($db -> num_rows($moderator) < 1)                
        {
                $output -> add($template_admin -> normal_error($lang['invalid_moderator_id']));
                page_main();
                return;
        }       
        
        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("moderators", $mod_info, "id='".$_GET['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_moderator']));
                page_main();
                return;
        }

        // ******************
        // Update cache
        // ******************
        $cache -> update_cache("moderators");

        // ******************
        // Log it!
        // ******************
        log_admin_action("moderators", "doedit", "Edited moderator");
        
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=moderators", $lang['moderator_edited_sucessfully']);

}


//***********************************************
// Lol baleete
//***********************************************
function do_delete_moderator()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Nab moderator
        $moderator = $db -> query("select id,username,group_name from ".$db->table_prefix."moderators where id='".$_GET['id']."' limit 1");
                        
        if($db -> num_rows($moderator) < 1)                
        {
                $output -> add($template_admin -> normal_error($lang['invalid_moderator_id']));
                page_main();
                return;
        }       

        if(!$db -> basic_delete("moderators", "id='".$_GET['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_deleting_moderator']));
                page_main();
                return;
        }

        // ******************
        // Update cache
        // ******************
        $cache -> update_cache("moderators");

        // ******************
        // Log it!
        // ******************
        log_admin_action("moderators", "doedit", "Deleted moderator");
        
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=moderators", $lang['moderator_deleted_sucessfully']);

}
?>
