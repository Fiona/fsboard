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
*       User Groups Editing     *
*       Started by Fiona        *
*       10th Feb 2006           *
*********************************
*       Last edit by Fiona      *
*       20th Jan 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Werds are power
//***********************************************
load_language_group("admin_usergroups");


$output -> add_breadcrumb($lang['breadcrumb_usergroups'], "index.php?m=usergroups");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_group(true);
                break;

        case "doadd":
                do_add_group();
                break;

        case "edit":
                page_add_edit_group();
                break;

        case "doedit":
                do_edit_group();
                break;

        case "delete":
                page_delete_group();
                break;
                
        case "dodelete":
                do_delete_group();
                break;
                
        default:
                page_main();

}



//***********************************************
// Main group listings
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_usergroups_title'];

        // Create classes
        $table = new table_generate;

        // ----------------
        // GROUP LIST
        // ----------------
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['admin_usergroups_title'], "strip1",  "", "left", "100%", "5").
                $table -> add_basic_row($lang['admin_usergroups_message'], "normalcell",  "padding : 5px", "left", "100%", "5").
                $table -> add_row(      
                        array(
                                array($lang['usergroups_main_name'], "25%"),
                                array($lang['usergroups_main_admin_area'], "20%"),
                                array($lang['usergroups_main_global_mod'], "20%"),
                                array($lang['usergroups_main_members'], "10%"),
                                array($lang['usergroups_main_actions'], "25%")
                        ),
                "strip2")
        );

        // *************************
        // Grab all groups
        // *************************
        $user_groups = $db -> query("select g.id, g.name, g.perm_admin_area, g.perm_global_mod, g.removable, 
        count(u.id) as count from ".$db -> table_prefix."user_groups as g
        left join ".$db -> table_prefix."users as u on (u.user_group = g.id)
        group by g.id order by g.id asc");

        // Get amount
        $groups_amount = $db -> num_rows($user_groups);


        // *************************
        // No user groups?
        // *************************
        if($groups_amount < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['usergroups_main_no_groups']."</b>", "normalcell",  "padding : 10px", "center", "100%", "5")
                );        
                
        else
        {

                // *************************
                // Go through each group if we have some
                // *************************
                while($g_array = $db-> fetch_array($user_groups))
                {
                
                        // Admin?
                        if($g_array['perm_admin_area'])
                                $admin_html = "<b>".$lang['yes']."</b>";
                        else
                                $admin_html = $lang['no'];
                                
                        // Mod?
                        if($g_array['perm_global_mod'])
                                $mod_html = "<b>".$lang['yes']."</b>";
                        else
                                $mod_html = $lang['no'];

                        // Sort picture links out
                        $actions = "
                        <a href=\"index.php?m=usergroups&amp;m2=edit&amp;id=".$g_array['id']."\" title=\"".$lang['usergroups_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>";
                        
                        if($g_array['removable'])
                                $actions .= " <a href=\"index.php?m=usergroups&amp;m2=delete&amp;id=".$g_array['id']."\" title=\"".$lang['usergroups_main_delete']."\">
                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>
                                ";
                        
                        // *************************
                        // Print row
                        // *************************
                        $output -> add(
                                $table -> add_row(      
                                        array(
                                                array($g_array['name'], "25%"),
                                                array($admin_html, "20%"),
                                                array($mod_html, "20%"),
                                                array($g_array['count'], "10%"),
                                                array($actions, "25%")
                                        ),
                                "normalcell")
                        );
                        
                        // Save stuff for the new groups form
                        $new_dropdown_text[] .= $g_array['name'];
                        $new_dropdown_values[] .= $g_array['id'];
                        
                }                
                
        }
        
        // *************************
        // New form
        // *************************
        $form = new form_generate;
        
        $output -> add(
                $table -> end_table().
                
                $form -> start_form("addforum", ROOT."admin/index.php?m=usergroups&amp;m2=add", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['usergroups_add_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(      
                        array(
                                array($lang['usergroups_add_inherit_from'], "25%"),
                                array($form->input_dropdown("inherit", "", $new_dropdown_values, $new_dropdown_text), "25%")
                        ),
                "normalcell").
                $table -> add_basic_row($form -> submit("submit", $lang['usergroups_add_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()                
        );

}


//***********************************************
// Form for adding or editing a user group
//***********************************************
function page_add_edit_group($adding = false, $group_info = "")
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

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['usergroups_add_form_title'];

		$output -> add_breadcrumb($lang['breadcrumb_usergroups_add'], "index.php?m=usergroups&amp;m2=add");

                $output -> add(
                        $form -> start_form("addforum", ROOT."admin/index.php?m=usergroups&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['usergroups_add_form_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['usergroups_add_form_message'], "normalcell",  "padding : 5px;", "left", "100%", "2")
                );

                $submit_lang = $lang['add_group_submit'];

                if(!$group_info)
                {

                        // ----------------
                        // Grab the inherited group
                        // ----------------
                        $post_id = trim($_POST['inherit']);
                
                        // No ID
                        if($post_id == '')
                        {
                                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                                page_main();
                                return;
                        }
                                
                        // Grab wanted group
                        $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$post_id."'");
        
                        // Die if it doesn't exist
                        if($db -> num_rows($group) == 0)
                        {
                                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                                page_main();
                                return;
                        }
        
                        $group_info = $db -> fetch_array($group);

                        $group_info['name'] = "";
                                        
                }

        }
        else
        {

                // ----------------
                // Grab the group we're editing
                // ----------------
                $get_id = trim($_GET['id']);
        
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                        page_main();
                        return;
                }
                        
                // Grab wanted group
                $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$get_id."'");

                // Die if it doesn't exist
                if($db -> num_rows($group) == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                        page_main();
                        return;
                }

                $group_info = $db -> fetch_array($group);

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['usergroups_edit_form_title'];

		$output -> add_breadcrumb($lang['breadcrumb_usergroups_edit'], "index.php?m=usergroups&amp;m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("addforum", ROOT."admin/index.php?m=usergroups&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['usergroups_edit_form_title']. " <b>".$group_info['name']."</b> (ID: <i>".$get_id."</i>)", "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_group_submit'];
        
        }
        
        // ***************************
        // THE FORM
        // Holy crap
        // ***************************

        $output -> add(
                // --------------------
                // Basic Info
                // --------------------
                $table -> add_row(
                        array(
                                array($lang['add_group_name'], "50%"), 
                                array($form -> input_text("name", $group_info['name']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_prefix']."<br /><font class=\"small_text\">".$lang['add_group_prefix_desc']."</font>", 
                                $form -> input_text("prefix", $group_info['prefix'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_suffix']."<br /><font class=\"small_text\">".$lang['add_group_suffix_desc']."</font>", 
                                $form -> input_text("suffix", $group_info['suffix'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_flood_control_time']."<br /><font class=\"small_text\">".$lang['add_group_flood_control_time_desc']."</font>", 
                                $form -> input_int("flood_control_time", $group_info['flood_control_time'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_edit_time']."<br /><font class=\"small_text\">".$lang['add_group_edit_time_desc']."</font>", 
                                $form -> input_int("edit_time", $group_info['edit_time'])
                        ),
                "normalcell").
                $table -> end_table().

                // --------------------
                // Global Perms
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_global_perms_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_admin_area'], "50%"), 
                                array($form -> input_yesno("perm_admin_area", $group_info['perm_admin_area']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_see_maintenance_mode'], 
                                $form -> input_yesno("perm_see_maintenance_mode", $group_info['perm_see_maintenance_mode'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_global_mod']."<br /><font class=\"small_text\">".$lang['add_group_global_mod_desc']."</font>", 
                                $form -> input_yesno("perm_global_mod", $group_info['perm_global_mod'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_banned']."<br /><font class=\"small_text\">".$lang['add_group_banned_desc']."</font>", 
                                $form -> input_yesno("banned", $group_info['banned'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_edit_own_profile'], 
                                $form -> input_yesno("perm_edit_own_profile", $group_info['perm_edit_own_profile'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_see_member_list'], 
                                $form -> input_yesno("perm_see_member_list", $group_info['perm_see_member_list'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_see_profile'], 
                                $form -> input_yesno("perm_see_profile", $group_info['perm_see_profile'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Visibility Options
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_visibility_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_hide_from_member_list'], "50%"), 
                                array($form -> input_yesno("hide_from_member_list", $group_info['hide_from_member_list']), "50%")
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // PM Perms
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_pm_perms'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_use_pm'], "50%"), 
                                array($form -> input_yesno("perm_use_pm", $group_info['perm_use_pm']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_pm_total']."<br /><font class=\"small_text\">".$lang['add_group_pm_total_desc']."</font>", 
                                $form -> input_int("pm_total", $group_info['pm_total'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Avatar Settings
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_avatar_settings'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow", $group_info['perm_avatar_allow']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow_gallery'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow_gallery", $group_info['perm_avatar_allow_gallery']), "50%")
                        ),
                "normalcell").                        
                     $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow_upload'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow_upload", $group_info['perm_avatar_allow_upload']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow_external'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow_external", $group_info['perm_avatar_allow_external']), "50%")
                        ),
                "normalcell").                
                $table -> add_row(
                        array(
                                $lang['add_group_avatar_width']."<br /><font class=\"small_text\">".$lang['add_group_avatar_width_desc']."</font>", 
                                $form -> input_int("perm_avatar_width", $group_info['perm_avatar_width'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_avatar_height']."<br /><font class=\"small_text\">".$lang['add_group_avatar_height_desc']."</font>", 
                                $form -> input_int("perm_avatar_height", $group_info['perm_avatar_height'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_avatar_filesize']."<br /><font class=\"small_text\">".$lang['add_group_avatar_filesize_desc']."</font>", 
                                $form -> input_int("perm_avatar_filesize", $group_info['perm_avatar_filesize'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // User title Settings
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_user_title_settings'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_display_user_title'], "50%"), 
                                array($form -> input_text("display_user_title", $group_info['display_user_title']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_override_user_title'], "50%"), 
                                array($form -> input_yesno("override_user_title", $group_info['override_user_title']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_custom_user_title'], "50%"), 
                                array($form -> input_yesno("perm_custom_user_title", $group_info['perm_custom_user_title']), "50%")
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Forum/Topic Viewing
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_forum_viewing'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_see_board'], "50%"), 
                                array($form -> input_yesno("perm_see_board", $group_info['perm_see_board']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_search'],
                                $form -> input_yesno("perm_use_search", $group_info['perm_use_search'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_view_other_topic'],
                                $form -> input_yesno("perm_view_other_topic", $group_info['perm_view_other_topic'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Topic/Reply Posting
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_topic_posting'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_post_topic'], "50%"), 
                                array($form -> input_yesno("perm_post_topic", $group_info['perm_post_topic']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_reply_own_topic'], 
                                $form -> input_yesno("perm_reply_own_topic", $group_info['perm_reply_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_reply_other_topic'], 
                                $form -> input_yesno("perm_reply_other_topic", $group_info['perm_reply_other_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_edit_own_post'], 
                                $form -> input_yesno("perm_edit_own_post", $group_info['perm_edit_own_post'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_edit_own_topic_title'], 
                                $form -> input_yesno("perm_edit_own_topic_title", $group_info['perm_edit_own_topic_title'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_delete_own_post'], 
                                $form -> input_yesno("perm_delete_own_post", $group_info['perm_delete_own_post'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_delete_own_topic'], 
                                $form -> input_yesno("perm_delete_own_topic", $group_info['perm_delete_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_move_own_topic'], 
                                $form -> input_yesno("perm_move_own_topic", $group_info['perm_move_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_close_own_topic'], 
                                $form -> input_yesno("perm_close_own_topic", $group_info['perm_close_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_post_closed_topic'], 
                                $form -> input_yesno("perm_post_closed_topic", $group_info['perm_post_closed_topic'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Posting Options
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_posting_options'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_remove_edited_by'], "50%"), 
                                array($form -> input_yesno("perm_remove_edited_by", $group_info['perm_remove_edited_by']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_html']."<br /><font class=\"small_text\">".$lang['add_group_perm_use_html_desc']."</font>", 
                                $form -> input_yesno("perm_use_html", $group_info['perm_use_html'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_bbcode']."<br /><font class=\"small_text\">".$lang['add_group_perm_use_bbcode_desc']."</font>", 
                                $form -> input_yesno("perm_use_bbcode", $group_info['perm_use_bbcode'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_emoticons'], 
                                $form -> input_yesno("perm_use_emoticons", $group_info['perm_use_emoticons'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_no_word_filter'], 
                                $form -> input_yesno("perm_no_word_filter", $group_info['perm_no_word_filter'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Polls
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_polls_perms'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_new_polls'], "50%"), 
                                array($form -> input_yesno("perm_new_polls", $group_info['perm_new_polls']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_vote_polls'], 
                                $form -> input_yesno("perm_vote_polls", $group_info['perm_vote_polls'])
                        ),
                "normalcell").
                $table -> end_table().

                // -----------
                // Submit Buttons
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($form -> submit("submit", $submit_lang), "strip3", "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

}



//***********************************************
// Add the groups
//***********************************************
function do_add_group()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Get stuff from the post
        $group_info = array(
                "name"                          => $_POST['name'],
                "prefix"                        => $_POST['prefix'],
                "suffix"                        => $_POST['suffix'],
                "flood_control_time"            => $_POST['flood_control_time'],
                "edit_time"                     => $_POST['edit_time'],
                "perm_admin_area"               => $_POST['perm_admin_area'],
                "perm_see_maintenance_mode"     => $_POST['perm_see_maintenance_mode'],
                "perm_global_mod"               => $_POST['perm_global_mod'],
                "banned"                        => $_POST['banned'],
                "perm_edit_own_profile"         => $_POST['perm_edit_own_profile'],
                "perm_see_member_list"          => $_POST['perm_see_member_list'],
                "perm_see_profile"              => $_POST['perm_see_profile'],
                "hide_from_member_list"         => $_POST['hide_from_member_list'],
                "perm_use_pm"                   => $_POST['perm_use_pm'],
                "pm_total"                      => $_POST['pm_total'],
                "perm_see_board"                => $_POST['perm_see_board'],
                "perm_use_search"               => $_POST['perm_use_search'],
                "perm_view_other_topic"         => $_POST['perm_view_other_topic'],
                "perm_post_topic"               => $_POST['perm_post_topic'],
                "perm_reply_own_topic"          => $_POST['perm_reply_own_topic'],
                "perm_reply_other_topic"        => $_POST['perm_reply_other_topic'],
                "perm_edit_own_post"            => $_POST['perm_edit_own_post'],
                "perm_edit_own_topic_title"     => $_POST['perm_edit_own_topic_title'],
                "perm_delete_own_post"          => $_POST['perm_delete_own_post'],
                "perm_delete_own_topic"         => $_POST['perm_delete_own_topic'],
                "perm_move_own_topic"           => $_POST['perm_move_own_topic'],
                "perm_close_own_topic"          => $_POST['perm_close_own_topic'],
                "perm_post_closed_topic"        => $_POST['perm_post_closed_topic'],
                "perm_remove_edited_by"         => $_POST['perm_remove_edited_by'],
                "perm_use_html"                 => $_POST['perm_use_html'],
                "perm_use_bbcode"               => $_POST['perm_use_bbcode'],
                "perm_use_emoticons"            => $_POST['perm_use_emoticons'],
                "perm_no_word_filter"           => $_POST['perm_no_word_filter'],
                "perm_new_polls"                => $_POST['perm_new_polls'],
                "perm_vote_polls"               => $_POST['perm_vote_polls'],
                "perm_avatar_allow"             => $_POST['perm_avatar_allow'],
                "perm_avatar_allow_gallery"     => $_POST['perm_avatar_allow_gallery'],
                "perm_avatar_allow_upload"      => $_POST['perm_avatar_allow_upload'],
                "perm_avatar_allow_external"    => $_POST['perm_avatar_allow_exeternal'],
                "perm_avatar_width"             => $_POST['perm_avatar_width'],
                "perm_avatar_height"            => $_POST['perm_avatar_height'],
                "perm_avatar_filesize"          => $_POST['perm_avatar_filesize'],
                "display_user_title"			=> $_POST['display_user_title'],
                "override_user_title"			=> $_POST['override_user_title'],
                "perm_custom_user_title"		=> $_POST['perm_custom_user_title']
        );

        // ----------------------
        // Check there's something in the name
        // ----------------------
        if(trim($group_info['name']) == "")
        {
                $output -> add($template_admin -> critical_error($lang['add_group_no_name']));
                page_add_edit_group(true, $group_info);
                return;
        }               

        // ----------------------
        // Add it!
        // ----------------------
        if(!$db -> basic_insert("user_groups", $group_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_group_error']));
                page_add_edit_group(true, $group_info);
                return;
        }               
       
        // ----------------------
        // Update cache
        // ----------------------
        $cache -> update_cache("user_groups");        
        
        // ----------------------
        // Log it!
        // ----------------------
        log_admin_action("usergroups", "doadd", "Added user group: ".$group_info['name']);
        
        // ----------------------
        // Done
        // ----------------------
        $output -> redirect(ROOT."admin/index.php?m=usergroups", $lang['usergroup_created_sucessfully']);
        
}



//***********************************************
// Edit like a crazy bear
//***********************************************
function do_edit_group()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ----------------
        // Grab the group we're editing
        // ----------------
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

 
        // ----------------------
        // Get stuff from the post
        // ----------------------
        $group_info = array(
                "name"                          => $_POST['name'],
                "prefix"                        => $_POST['prefix'],
                "suffix"                        => $_POST['suffix'],
                "flood_control_time"            => $_POST['flood_control_time'],
                "edit_time"                     => $_POST['edit_time'],
                "perm_admin_area"               => $_POST['perm_admin_area'],
                "perm_see_maintenance_mode"     => $_POST['perm_see_maintenance_mode'],
                "perm_global_mod"               => $_POST['perm_global_mod'],
                "banned"                        => $_POST['banned'],
                "perm_edit_own_profile"         => $_POST['perm_edit_own_profile'],
                "perm_see_member_list"          => $_POST['perm_see_member_list'],
                "perm_see_profile"              => $_POST['perm_see_profile'],
                "hide_from_member_list"         => $_POST['hide_from_member_list'],
                "perm_use_pm"                   => $_POST['perm_use_pm'],
                "pm_total"                      => $_POST['pm_total'],
                "perm_see_board"                => $_POST['perm_see_board'],
                "perm_use_search"               => $_POST['perm_use_search'],
                "perm_view_other_topic"         => $_POST['perm_view_other_topic'],
                "perm_post_topic"               => $_POST['perm_post_topic'],
                "perm_reply_own_topic"          => $_POST['perm_reply_own_topic'],
                "perm_reply_other_topic"        => $_POST['perm_reply_other_topic'],
                "perm_edit_own_post"            => $_POST['perm_edit_own_post'],
                "perm_edit_own_topic_title"     => $_POST['perm_edit_own_topic_title'],
                "perm_delete_own_post"          => $_POST['perm_delete_own_post'],
                "perm_delete_own_topic"         => $_POST['perm_delete_own_topic'],
                "perm_move_own_topic"           => $_POST['perm_move_own_topic'],
                "perm_close_own_topic"          => $_POST['perm_close_own_topic'],
                "perm_post_closed_topic"        => $_POST['perm_post_closed_topic'],
                "perm_remove_edited_by"         => $_POST['perm_remove_edited_by'],
                "perm_use_html"                 => $_POST['perm_use_html'],
                "perm_use_bbcode"               => $_POST['perm_use_bbcode'],
                "perm_use_emoticons"            => $_POST['perm_use_emoticons'],
                "perm_no_word_filter"           => $_POST['perm_no_word_filter'],
                "perm_new_polls"                => $_POST['perm_new_polls'],
                "perm_vote_polls"               => $_POST['perm_vote_polls'],
                "perm_avatar_allow"             => $_POST['perm_avatar_allow'],
                "perm_avatar_allow_gallery"     => $_POST['perm_avatar_allow_gallery'],        
                "perm_avatar_allow_upload"      => $_POST['perm_avatar_allow_upload'],
                "perm_avatar_allow_external"    => $_POST['perm_avatar_allow_external'],
                "perm_avatar_width"             => $_POST['perm_avatar_width'],
                "perm_avatar_height"            => $_POST['perm_avatar_height'],
                "perm_avatar_filesize"          => $_POST['perm_avatar_filesize'],
                "display_user_title"			=> $_POST['display_user_title'],
                "override_user_title"			=> $_POST['override_user_title'],
                "perm_custom_user_title"		=> $_POST['perm_custom_user_title']
        );
 


        // ----------------------
        // Check there's something in the name
        // ----------------------
        if(trim($group_info['name']) == "")
        {
                $output -> add($template_admin -> critical_error($lang['add_group_no_name']));
                page_add_edit_group(false, $group_info);
                return;
        }               

        // ----------------------
        // Do the query
        // ----------------------
        if(!$db -> basic_update("user_groups", $group_info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_usergroup']));
                page_main();
                return;
        }

        // ----------------------
        // Update cache
        // ----------------------
        $cache -> update_cache("user_groups");        
        
        // ----------------------
        // Log it!
        // ----------------------
        log_admin_action("usergroups", "doedit", "Edited user group: ".$group_info['name']);
        
        // ----------------------
        // Done
        // ----------------------
        $output -> redirect(ROOT."admin/index.php?m=usergroups&amp;m2=edit&amp;id=".$get_id, $lang['usergroup_updated_sucessfully']);

}


//***********************************************
// Baleetion!
//***********************************************
function page_delete_group()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // Grab the group we're editing
        // ----------------
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select id,name,removable from ".$db -> table_prefix."user_groups where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        // array me
        $group_array = $db -> fetch_array($group);
        
        // Can we delete it?
        if(!$group_array['removable'])
        {
                $output -> add($template_admin -> critical_error($lang['group_non_removable']));
                page_main();
                return;
        }

        // *************************
        // Build group dropdown
        // *************************
        $user_groups = $db -> query("select id, name from ".$db -> table_prefix."user_groups where id != '".$get_id."' order by id asc");

        while($g_array = $db -> fetch_array($user_groups))
        {
        
                $groups_dropdown_text[] .= $g_array['name']; 
                $groups_dropdown_values[] .= $g_array['id']; 
        
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['usergroups_delete_title'];

	$output -> add_breadcrumb($lang['breadcrumb_usergroups_delete'], "index.php?m=usergroups&amp;m2=delete&amp;id=".$get_id);

        // *************************
        // Delete form
        // *************************
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ***************************
        // Need different headers
        // ***************************
        $output -> add(
                $form -> start_form("deletegroup", ROOT."admin/index.php?m=usergroups&amp;m2=dodelete&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_delete_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($output -> replace_number_tags($lang['usergroups_delete_message'], array($group_array['name'])), "50%"),
                                array($form -> input_dropdown("replace", "", $groups_dropdown_values, $groups_dropdown_text), "50%")
                        ),
                "normalcell").
                $table -> add_basic_row($form->submit("submit", $lang['usergroups_delete_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

}


//***********************************************
// Actually deleting this time
//***********************************************
function do_delete_group()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ********************
        // Grab the group we're editing
        // ********************
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select id,removable,name from ".$db -> table_prefix."user_groups where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        // array me
        $group_array = $db -> fetch_array($group);
        
        // Can we delete it?
        if(!$group_array['removable'])
        {
                $output -> add($template_admin -> critical_error($lang['group_non_removable']));
                page_main();
                return;
        }


        // ********************
        // Check the group to move to
        // ********************
        $replace_id = trim($_POST['replace']);

        // No ID
        if($replace_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $replace_group = $db -> query("select id from ".$db -> table_prefix."user_groups where id='".$replace_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($replace_group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        // ********************
        // Delete it
        // ********************
        if(!$db -> basic_delete("user_groups", "id = '".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['usergroup_delete_fail']));
                page_main();
                return;
        }
        
        // ********************
        // Move users
        // ********************
        if(!$db -> basic_update("users", array("user_group" => $replace_id), "user_group='".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['usergroup_delete_move_fail']));
                page_main();
                return;
        }

	// Remove everyone with this as secondary group
	// Got this little beaut' from a comment in the mysql docs         
        if(!$db -> query("UPDATE ".$db -> table_prefix."users SET secondary_user_group = TRIM(BOTH ',' FROM REPLACE(  CONCAT(',', `secondary_user_group`, ',') , CONCAT(',', '".$get_id."', ',') , ','  ))"))
        {
                $output -> add($template_admin -> critical_error($lang['usergroup_delete_move_fail']));
                page_main();
                return;
        }

	// Kill promotions
        $db -> basic_delete("promotions", "group_id = '".$get_id."' OR group_to_id = '".$get_id."'");

        // ********************
        // Update cache
        // ********************
        $cache -> update_cache("user_groups");        

        // Delete/Update perms
	$db -> basic_delete("forums_perms", "group_id = '".$get_id."'");
        $cache -> update_cache("forums_perms");        

        // Delete/Update moderators
	$db -> basic_delete("moderators", "group_id = '".$get_id."'");
        $cache -> update_cache("moderators");        
        
        // ********************
        // Log it!
        // ********************
        log_admin_action("usergroups", "dodelete", "Deleted user group: ".$group_array['name']);
        
        // ----------------------
        // Done
        // ----------------------
        $output -> redirect(ROOT."admin/index.php?m=usergroups", $lang['usergroup_deleted_sucessfully']);

}

?>
