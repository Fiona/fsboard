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
*       Forums Manager          *
*       Started by Fiona        *
*       23rd Jan 2006           *
*********************************
*       Last edit by Fiona      *
*       09th Mar 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Werds are power
//***********************************************
load_language_group("admin_forums");


//***********************************************
// Functions plzkthx
//***********************************************
include ROOT."admin/common/funcs/forums.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_forums'], "index.php?m=forums");

$_GET['m2'] = ($_GET['m2']) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_forum(true);
                break;

        case "doadd":
                do_add_forum();
                break;

        case "edit":
                page_add_edit_forum();
                break;

        case "doedit":
                do_edit_forum();
                break;

        case "updatepositions":
                do_update_positions();
                break;

        case "delete":
                page_delete_forum();
                break;

        case "dodelete":
                do_delete_forum();
                break;

        case "editperms":
                page_edit_perms();
                break;
                
        case "doeditperms":
                do_edit_perms();
                break;

        case "deleteperms":
                do_delete_all_perms();
                break;
                
        case "noallperms":
                do_deny_all_perms();
                break;
                
        case "main":                
                page_main();
                break;

}

//***********************************************
// Main forum view
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_forums_title'];

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ----------------
        // FORUMS LIST
        // ----------------
        $output -> add(
                "
                <script type=\"text/javascript\">
                        function do_forum_action(id)
                        {

                                var _value = eval(\"document.editforums.forum_\"+id+\"_action.options[document.editforums.forum_\"+id+\"_action.selectedIndex].value\");
                                var go_to = \"\";

                                document.editforums.reset();

                                if(_value != '')                                
                                {
                               
                                        switch(_value)
                                        {
                                        
                                                case 'edit':
                                                        go_to = \"".ROOT."admin/index.php?m=forums&m2=edit&id=\"+id;
                                                        break;
                                        
                                                case 'add_child':
                                                        go_to = \"".ROOT."admin/index.php?m=forums&m2=add&parent_id=\"+id;
                                                        break;
                                        
                                                case 'view':
                                                        go_to = \"".ROOT."admin/index.php?m=forums\";
                                                        break;
                                                        
                                                case 'delete':
                                                        go_to = \"".ROOT."admin/index.php?m=forums&m2=delete&id=\"+id;
                                                        break;
                                        
                                        }

                                        window.location = go_to;
                                        
                                }
                                
                        }


                        function do_forum_perms(id)
                        {

                                var _value = eval(\"document.editforums.forum_\"+id+\"_perms.options[document.editforums.forum_\"+id+\"_perms.selectedIndex].value\");
                                var go_to = \"\";

                                document.editforums.reset();

                                if(_value != '')                                
                                {
                               
                                        switch(_value)
                                        {
                                        
                                                case 'delete':
                                                        if(confirm('".$lang['forums_perms_delete_msg']."'))
                                                                go_to = \"".ROOT."admin/index.php?m=forums&m2=deleteperms&id=\"+id;
                                                        else
                                                                go_to = \"\";
                                                        break;        
                                        
                                                case 'noall':
                                                        if(confirm('".$lang['forums_perms_noall_msg']."'))
                                                                go_to = \"".ROOT."admin/index.php?m=forums&m2=noallperms&id=\"+id;
                                                        else
                                                                go_to = \"\";
                                                        break;        
                                                        
                                                default:
                                                        go_to = \"".ROOT."admin/index.php?m=forums&m2=editperms&id=\"+id+\"&g_id=\"+_value;
                                                
                                        }

                                        if(go_to != '')
                                                window.location = go_to; 
                                        
                                }
                                
                        }
                </script>
                ".
                $form -> start_form("editforums", ROOT."admin/index.php?m=forums&amp;m2=updatepositions", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_top_table_header($lang['admin_forums_title'], 4, "forums").
                $table -> add_basic_row($lang['admin_forums_message'], "normalcell",  "padding : 5px", "left", "100%", "4").
                // ---------------
                // List header
                // ---------------
                $table -> add_row(array($lang['forums_name'],$lang['forums_actions'],$lang['forums_permissions'],$lang['forums_order']), "strip2")
        );

        // Grab all forums
        $forums = $db -> query("select id, parent_id, name, is_category, position from ".$db -> table_prefix."forums order by position asc");

        // Get amount
        $forums_amount = $db -> num_rows($forums);

        // No forums?
        if($forums_amount < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['admin_no_forums']."</b>", "normalcell",  "padding : 10px", "center", "100%", "4")
                );        
                
        else
        {

                // Fetch user groups and permissions stuff
                $groups_array = array();
                $forums_perms_array = array();
                
                $db -> basic_select("user_groups", "id, name", "", "id");
                
                while($g = $db -> fetch_array())
                        $groups_array[$g['id']] = $g['name'];
                
                $db -> basic_select("forums_perms", "id, forum_id, group_id");
                
                while($p = $db -> fetch_array())
                {
                        $forums_perms_array[$p['id']]['forum_id'] = $p['forum_id'];
                        $forums_perms_array[$p['id']]['group_id'] = $p['group_id'];
                }
                
                // Sort it out
                $forums_tree = return_admin_forums_tree($forums);

                // Print out the form
                print_admin_forums_layout(-1, $forums_tree, $table, $form, 0, 0, $groups_array, $forums_perms_array);
        
        }

        $output -> add(
                $table -> add_basic_row(
                        $form -> submit("submit", $lang['update_forum_positions']).
                        " ".
                        $form -> button("addforum", $lang['add_forum_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=forums&m2=add';\"")
                , "strip3",  "", "center", "100%", "4").
                $table -> end_table().
                $form -> end_form()
        );

}



//***********************************************
// Form for adding or editing a forum
//***********************************************
function page_add_edit_forum($adding = false, $forum_info = "")
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
                $output -> page_title = $lang['add_forum_title'];

                $output -> add_breadcrumb($lang['breadcrumb_add_forum'], "index.php?m=forums&amp;m2=add");

                // ADDING
                $output -> add(
                        $form -> start_form("addforum", ROOT."admin/index.php?m=forums&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_top_table_header($lang['add_forum_title'], 2, "forums")
                );

                $submit_lang = $lang['add_forum_submit'];

                if(!$forum_info)
                {
                
                        $forum_info['bbcode_on'] = "1";
                        $forum_info['emoticons_on'] = "1";
                        $forum_info['polls_on'] = "1";
                        $forum_info['show_forum_jump'] = "1";
                        $forum_info['add_post_count'] = "1";
                        $forum_info['quick_reply_on'] = "1";
                        
                        if($_GET['parent_id'])
                                $forum_info['parent_id'] = $_GET['parent_id'];
                        else
                                $forum_info['parent_id'] = "-1";
                        
                }
                
        }
        else
        {
        
                // EDITING

                // ----------------
                // Grab the forum
                // ----------------
                $get_id = trim($_GET['id']);
                
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                        page_main();
                        return;
                }
                        
                // Grab wanted forum
                $forum = $db -> query("select * from ".$db -> table_prefix."forums where id='".$get_id."'");

                // Die if it doesn't exist
                if($db -> num_rows($forum) == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                        page_main();
                        return;
                }

                $forum_info = $db -> fetch_array($forum);

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_forum_title']. " ".$forum_info['name'];

                $output -> add_breadcrumb($forum_info['name'], "index.php?m=forums&amp;m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("editforum", ROOT."admin/index.php?m=forums&amp;m2=doedit&amp;id=".$_GET['id'], "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_top_table_header($lang['edit_forum_title']. " <b>".$forum_info['name']."</b> (ID: <i>".$_GET['id']."</i>)", 2, "forums")

                );

                $submit_lang = $lang['edit_forum_submit'];
                
        }

        // ***************************
        // Get the forums dropdown
        // ***************************
        // Grab all forums
        $forums = $db -> query("select id, parent_id, name from ".$db -> table_prefix."forums order by position asc");

        $forums_tree = return_admin_forums_tree($forums);

        $dropdown = return_admin_forums_dropdown($forums_tree, $lang['forums_add_root']);


        // ***************************
        // Get the themes dropdown
        // ***************************
        // Default theme select       
        $themes_dropdown_text[] .= $lang['forums_add_default_theme']; 
        $themes_dropdown_values[] .= "-1"; 

        $db -> basic_select("themes", "id,name");

        if($db -> num_rows() > 0)
        {

                while($t_array = $db -> fetch_array())
                {
                
                        $themes_dropdown_text[] .= $t_array['name']; 
                        $themes_dropdown_values[] .= $t_array['id']; 
                
                }
                
        }


        // ***************************
        // Print some of the form
        // ***************************
        $output -> add(
                // -----------
                // Basic Information
                // -----------
                $table -> add_basic_row($lang['forums_add_basic_info'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_text($form, $lang['add_forums_name'], "name", $forum_info['name'], "name").
                $table -> simple_input_row_text($form, $lang['add_forums_description'], "description", $forum_info['description'], "description").
                $table -> simple_input_row_dropdown($form, $lang['add_forums_parent_forum'], "parent_id", $forum_info['parent_id'], $dropdown['values'], $dropdown['text'], "parent").
                $table -> simple_input_row_int($form, $lang['add_forums_position'], "position", $forum_info['position'], "position").
                $table -> simple_input_row_yesno($form, $lang['add_forums_is_category'], "is_category", $forum_info['is_category'], "is_category").
                $table -> end_table().

                // -----------
                // URL redirecting
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forums_add_redirect_title'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forums_add_redirect'], "redirect", $forum_info['redirect'], "redirect").
                $table -> simple_input_row_text($form, $lang['forums_add_redirect_url'], "redirect_url", $forum_info['redirect_url'], "redirect_url").
                $table -> end_table().

                // -----------
                // Themes
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forums_add_themes_title'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forums_add_override_user_theme'], "override_user_theme", $forum_info['redirect'], "override_user_theme").
                $table -> simple_input_row_dropdown($form, $lang['forums_add_theme_id'], "theme_id", $forum_info['theme_id'], $themes_dropdown_values, $themes_dropdown_text, "theme_id").
                $table -> end_table().

                // -----------
                // Password
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forums_add_password_title'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_text($form, $lang['forums_add_password'], "password", $forum_info['password'], "password").
                $table -> end_table().

                // -----------
                // Status
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forums_add_status_header'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forums_add_hide_forum'], "hide_forum", $forum_info['hide_forum'], "hide_forum").
                $table -> simple_input_row_yesno($form, $lang['forums_add_close_forum'], "close_forum", $forum_info['close_forum'], "close_forum").
                $table -> end_table().
                
                // -----------
                // Rules                
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forums_add_rules_header'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forums_add_rules_on'], "rules_on", $forum_info['rules_on'], "rules_on").
                $table -> simple_input_row_yesno($form, $lang['forums_add_use_site_rules'], "use_site_rules", $forum_info['use_site_rules'], "use_site_rules").
                $table -> simple_input_row_text($form, $lang['forums_add_rules_title'], "rules_title", $forum_info['rules_title'], "rules_title").
                $table -> simple_input_row_textbox($form, $lang['forums_add_rules_text'], "rules_text", $forum_info['rules_text'], 3, "rules_text").
                $table -> end_table().

                // -----------
                // Features        
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forums_add_features_header'], "strip2", "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forums_add_bbcode_on'], "bbcode_on", $forum_info['bbcode_on'], "bbcode_on").
                $table -> simple_input_row_yesno($form, $lang['forums_add_html_on'], "html_on", $forum_info['html_on'], "html_on").
                $table -> simple_input_row_yesno($form, $lang['forums_add_emoticons_on'], "emoticons_on", $forum_info['emoticons_on'], "emoticons_on").
                $table -> simple_input_row_yesno($form, $lang['forums_add_polls_on'], "polls_on", $forum_info['polls_on'], "polls_on").
                $table -> simple_input_row_yesno($form, $lang['forums_add_quick_reply_on'], "quick_reply_on", $forum_info['quick_reply_on'], "quick_reply_on").
                $table -> simple_input_row_yesno($form, $lang['forums_add_add_post_count'], "add_post_count", $forum_info['add_post_count'], "add_post_count").
                $table -> simple_input_row_yesno($form, $lang['forums_add_show_forum_jump'], "show_forum_jump", $forum_info['show_forum_jump'], "show_forum_jump").

                // -----------
                // Submit Buttons
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );               

}



//***********************************************
// Add the pissing forum
//***********************************************
function do_add_forum()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Get stuff from the post
        $forum_info = array(
                "name"                  => $_POST['name'],
                "description"           => $_POST['description'],
                "parent_id"             => $_POST['parent_id'],
                "position"              => $_POST['position'],
                "is_category"           => $_POST['is_category'],
                "theme_id"              => $_POST['theme_id'],
                "override_user_theme"   => $_POST['override_user_theme'],
                "password"              => $_POST['password'],
                "redirect_url"          => $_POST['redirect_url'],
                "rules_on"              => $_POST['rules_on'],
                "rules_title"           => $_POST['rules_title'],
                "rules_text"            => $_POST['rules_tekt'],
                "use_site_rules"        => $_POST['use_site_rules'],
                "hide_forum"            => $_POST['hide_forum'],
                "close_forum"           => $_POST['close_forum'],
                "bbcode_on"             => $_POST['bbcode_on'],
                "html_on"               => $_POST['html_on'],
                "polls_on"              => $_POST['polls_on'],
                "quick_reply_on"        => $_POST['quick_reply_on'],
                "add_post_count"        => $_POST['add_post_count'],
                "show_forum_jump"       => $_POST['show_forum_jump'],
        );

        // Check there's something in the name
        if(trim($forum_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_forum_no_name']));
                page_add_edit_forum(true, $forum_info);
                return;
        }               

        // Add it!
        if(!$db -> basic_insert("forums", $forum_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_forum_error']));
                page_add_edit_forum(true, $forum_info);
                return;
        }               
       
        // Update cache
        $cache -> update_cache("forums");

        // Log it!
        log_admin_action("forums", "doadd", "Added forum: ".$forum_info['name']);
        
        // Done
        $output -> redirect(ROOT."admin/index.php?m=forums", $lang['forum_created_sucessfully']);
        
}


//***********************************************
// Positions updating
//***********************************************
function do_update_positions()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Get vaules
        $position_array = $_POST['position'];
        
        $db -> basic_select("forums", "id, position");
        $forums_position = array();
        
        while($f = $db -> fetch_array())
                $forums_position[$f['id']] = $f['position'];
        
        // If we have any
        if(count($position_array) > 0)
        {
        
                // Go through the ones we have
                foreach($position_array as $key => $value)
                {
                
                        // Is there a change?
                        if($value == $forums_position[$key])
                                continue; // skip it
                                
                        // Do the query
                        $update_info = array("position" => (int)$value);
                
                        if(!$db -> basic_update("forums", $update_info, "id='".$key."'"))        
                        {
                                $output -> add($template_admin -> critical_error($lang['error_updating_positions']));
                                page_main();
                                return;
                        }
                
                }

                // Update cache
                $cache -> update_cache("forums");
        
        }

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=forums", $lang['forum_positions_updated']);
        
}


//***********************************************
// Holy crap let's edit something
//***********************************************
function do_edit_forum()
{

        global $output, $lang, $db, $template_admin, $cache;

        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }

        // Get stuff from the post
        $forum_info = array(
                "name"                  => $_POST['name'],
                "description"           => $_POST['description'],
                "parent_id"             => $_POST['parent_id'],
                "position"              => $_POST['position'],
                "is_category"           => $_POST['is_category'],
                "theme_id"              => $_POST['theme_id'],
                "override_user_theme"   => $_POST['override_user_theme'],
                "password"              => $_POST['password'],
                "redirect_url"          => $_POST['redirect_url'],
                "rules_on"              => $_POST['rules_on'],
                "rules_title"           => $_POST['rules_title'],
                "rules_text"            => $_POST['rules_tekt'],
                "use_site_rules"        => $_POST['use_site_rules'],
                "hide_forum"            => $_POST['hide_forum'],
                "close_forum"           => $_POST['close_forum'],
                "bbcode_on"             => $_POST['bbcode_on'],
                "html_on"               => $_POST['html_on'],
                "polls_on"              => $_POST['polls_on'],
                "quick_reply_on"        => $_POST['quick_reply_on'],
                "add_post_count"        => $_POST['add_post_count'],
                "show_forum_jump"       => $_POST['show_forum_jump'],
        );

        // Check there's something in the name
        if(trim($forum_info['name']) == "")
        {
                $output -> add($template_admin -> critical_error($lang['add_forum_no_name']));
                page_add_edit_forum(false, $forum_info);
                return;
        }               

        // ********************************
        // Check we can move it here!
        // ********************************
        // Get the whole pissing lot        
        $select_forums = $db -> query("SELECT id,name,parent_id FROM ".$db -> table_prefix."forums");
        $forums_tree = return_admin_forums_tree($select_forums);

        $current_f = $forum_info['parent_id'];        
        $target_f_invalid = false;
        
        // Go through them all
        while($current_f != -1)
        {
        
                // WHOOPS Can't do that!
                if($current_f == $_GET['id'])
                {
                        $target_f_invalid = true;
                        break;
                }

                $current_f = $forums_tree[$current_f]['parent_id'];

        }

        // If we can't move it here
        if($target_f_invalid)
        {
                $output -> add($template_admin -> normal_error($lang['cant_move_forum_here']));
                page_add_edit_forum(false, $forum_info);
                return;
        }

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("forums", $forum_info, "id='".$_GET['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_forum']));
                page_main();
                return;
        }

        // *********************
        // Update cache
        // *********************
        $cache -> update_cache("forums");

        // *********************
        // Log action
        // *********************
        log_admin_action("forums", "doedit", "Edited forum: ".$forum_info['name']);

        // *********************
        // Redirect the user
        // *********************
        $output -> redirect(ROOT."admin/index.php?m=forums&amp;m2=edit&amp;id=".$_GET['id'], $lang['forum_updated']);

}


//***********************************************
// Baleetion!
//***********************************************
function page_delete_forum()
{

        global $output, $lang, $db, $template_admin;

        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id,name from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
        
        $forum_array = $db -> fetch_array($forum);

        // *********************
        // Print the page
        // *********************
        $output -> page_title = $lang['delete_forum_title'];
        $output -> add_breadcrumb($lang['breadcrumb_forum_delete'], "index.php?m=forums&amp;m2=delete&amp;id=".$get_id);
        $lang['delete_forum_message'] = $output -> replace_number_tags($lang['delete_forum_message'], array($forum_array['name'], ROOT, $forum_array['id']));

        $table = new table_generate;

        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                    $table -> add_top_table_header($lang['delete_forum_title'], 0, "forums").
                $table -> add_basic_row($lang['delete_forum_message'], "normalcell").
                    $table -> end_table()
        ); 

}


//***********************************************
// Actually deleting this time
//***********************************************
function do_delete_forum()
{

        global $output, $lang, $db, $template_admin, $cache;

        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id,name from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }

        $forum_info = $db -> fetch_array($forum);

        // *****************
        // Get the forums
        // *****************
        $select_forums = $db -> query("SELECT id,name,parent_id FROM ".$db -> table_prefix."forums");
        $forums_tree = return_admin_forums_tree($select_forums);

        // *****************
        // Use this function to delete
        // *****************
        recursive_delete_forum_children($_GET['id'], $forums_tree);

        // *****************
        // Log it
        // *****************
        log_admin_action("forums", "dodelete", "Deleted forum: ".$forum_info['name']);

        // *****************
        // Update Cache
        // *****************
        $cache -> update_cache("forums");
        $cache -> update_cache("forums_perms");
        $cache -> update_cache("moderators");

        // *****************
        // Redirect
        // *****************
        $output -> redirect(ROOT."admin/index.php?m=forums", $lang['forum_deleted']);

}


//***********************************************
// Form for editing permissions
//***********************************************
function page_edit_perms()
{

        global $output, $lang, $db, $template_admin;


        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id,name from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
        
        $forum_array = $db -> fetch_array($forum);

        // ********************
        // Grab the group we're editing
        // ********************
        $group_id = trim($_GET['g_id']);

        // No ID
        if($group_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$group_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        $group_array = $db -> fetch_array($group);

        // ********************
        // Get the permission then
        // ********************
        $perms = $db -> query("select * from ".$db -> table_prefix."forums_perms where forum_id='".$get_id."' and group_id='".$group_id."'");

        if($db -> num_rows($perms) == 0)
                $perms_info = $group_array;
        else
                $perms_info = $db -> fetch_array($perms);


        // ********************
        // Do the bloody form!
        // ********************
        // sort out the title
        $lang['forum_perms_title'] = $output ->  replace_number_tags($lang['forum_perms_title'], array($forum_array['name'], $group_array['name']));

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['forum_perms_page_title'];

        $output -> add_breadcrumb($forum_array['name'], "index.php?m=forums&amp;m2=edit&amp;id=".$get_id);
        $output -> add_breadcrumb($lang['breadcrumb_forum_edit_perms'], "index.php?m=forums&amp;m2=editperms&amp;id=".$get_id."&amp;g_id=".$group_id);
        
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("addforum", ROOT."admin/index.php?m=forums&amp;m2=doeditperms&amp;id=".$get_id."&amp;g_id=".$group_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                    $table -> add_top_table_header($lang['forum_perms_title'], 2, "forums").
                $table -> end_table().

                // --------------------
                // Forum/Topic Viewing
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forum_perms_forum_viewing'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_see_board'], "perm_see_board", $perms_info['perm_see_board'], "perm_see_board").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_use_search'], "perm_use_search", $perms_info['perm_use_search'], "perm_use_search").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_view_other_topic'], "perm_view_other_topic", $perms_info['perm_view_other_topic'], "perm_view_other_topic").
                $table -> end_table().

                // --------------------
                // Topic/Reply Posting
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forum_perms_topic_posting'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_post_topic'], "perm_post_topic", $perms_info['perm_post_topic'], "perm_post_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_reply_own_topic'], "perm_reply_own_topic", $perms_info['perm_reply_own_topic'], "perm_reply_own_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_reply_other_topic'], "perm_reply_other_topic", $perms_info['perm_reply_other_topic'], "perm_reply_other_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_edit_own_post'], "perm_edit_own_post", $perms_info['perm_edit_own_post'], "perm_edit_own_post").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_edit_own_topic_title'], "perm_edit_own_topic_title", $perms_info['perm_edit_own_topic_title'], "perm_edit_own_topic_title").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_delete_own_post'], "perm_delete_own_post", $perms_info['perm_delete_own_post'], "perm_delete_own_post").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_delete_own_topic'], "perm_delete_own_topic", $perms_info['perm_delete_own_topic'], "perm_delete_own_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_move_own_topic'], "perm_move_own_topic", $perms_info['perm_move_own_topic'], "perm_move_own_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_close_own_topic'], "perm_close_own_topic", $perms_info['perm_close_own_topic'], "perm_close_own_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_post_closed_topic'], "perm_post_closed_topic", $perms_info['perm_post_closed_topic'], "perm_post_closed_topic").
                $table -> end_table().

                // --------------------
                // Posting Options
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forum_perms_posting_options'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_remove_edited_by'], "perm_remove_edited_by", $perms_info['perm_remove_edited_by'], "perm_remove_edited_by").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_use_html']."<br /><font class=\"small_text\">".$lang['forum_perms_perm_use_html_desc']."</font>",
                                                "perm_use_html", $perms_info['perm_use_html'], "perm_use_html").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_use_bbcode']."<br /><font class=\"small_text\">".$lang['forum_perms_perm_use_bbcode_desc']."</font>",
                                                "perm_use_bbcode", $perms_info['perm_use_bbcode'], "perm_use_bbcode").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_close_own_topic'], "perm_close_own_topic", $perms_info['perm_close_own_topic'], "perm_close_own_topic").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_use_emoticons'], "perm_use_emoticons", $perms_info['perm_use_emoticons'], "perm_use_emoticons").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_no_word_filter'], "perm_no_word_filter", $perms_info['perm_no_word_filter'], "perm_no_word_filter").
                $table -> end_table().

                // --------------------
                // Polls
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['forum_perms_polls_perms'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_new_polls'], "perm_new_polls", $perms_info['perm_new_polls'], "perm_new_polls").
                $table -> simple_input_row_yesno($form, $lang['forum_perms_perm_vote_polls'], "perm_vote_polls", $perms_info['perm_vote_polls'], "perm_vote_polls").
                $table -> end_table().

                // -----------
                // Submit Buttons
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_submit_row($form, "submit", $lang['perm_edit_submit']).
                
                $table -> end_table().
                $form -> end_form()                
        );
        
}



//***********************************************
// Do permission editing
//***********************************************
function do_edit_perms()
{

        global $output, $lang, $db, $template_admin, $cache;


        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id,name from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
        
        $forum_array = $db -> fetch_array($forum);

        // ********************
        // Grab the group we're editing
        // ********************
        $group_id = trim($_GET['g_id']);

        // No ID
        if($group_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$group_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        $group_array = $db -> fetch_array($group);


        // ********************
        // Sort it out
        // ********************
        $perms_info = array(
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
                "perm_vote_polls"               => $_POST['perm_vote_polls']
        );


        // ********************
        // Check if we submitted defaults
        // ********************
        if(check_perm_same($perms_info, $group_array))
                $db -> query("DELETE FROM ".$db -> table_prefix."forums_perms WHERE forum_id='".$get_id."' and group_id='".$group_id."'");
        else
        {

                $perms_info["group_id"] = $group_id;
                $perms_info["forum_id"] = $get_id;
        
                // Perm row exist?
                if($db -> num_rows($db->query("select id from ".$db -> table_prefix."forums_perms WHERE forum_id='".$get_id."' and group_id='".$group_id."'")) > 0)
                {
                        $db -> basic_update("forums_perms", $perms_info, "forum_id='".$get_id."' and group_id='".$group_id."'");
                }
                else
                        $db -> basic_insert("forums_perms", $perms_info);

        }


        // *****************
        // Kids inherit this
        // *****************
        perms_copy_to_children($perms_info["group_id"], $perms_info["forum_id"], $perms_info);

        // *****************
        // Update Cache
        // *****************
        $cache -> update_cache("forums_perms");

        // *****************
        // Log it
        // *****************
        log_admin_action("forums", "doeditperms", "Updated forum permissions for ".$group_array['name'].": ".$forum_array['name']);

        // *****************
        // Redirect
        // *****************
        $output -> redirect(ROOT."admin/index.php?m=forums", $lang['forum_permissions_saved']);
                             
}



//***********************************************
// Deleteing a great chunk of perms
//***********************************************
function do_delete_all_perms()
{

        global $output, $lang, $db, $template_admin, $cache;

        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id,name from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
        
        $forum_array = $db -> fetch_array($forum);

        // ********************
        // Delete it then
        // ********************
        $db -> query("DELETE FROM ".$db -> table_prefix."forums_perms WHERE forum_id='".$get_id."'");

        // *****************
        // Update Cache
        // *****************
        $cache -> update_cache("forums_perms");

        // *****************
        // Log it
        // *****************
        log_admin_action("forums", "doeditperms", "Deleted all forum permissions: ".$forum_array['name']);

        // *****************
        // Redirect
        // *****************
        $output -> redirect(ROOT."admin/index.php?m=forums", $lang['forum_permissions_saved']);
                             
}


//***********************************************
// No to all perms
//***********************************************
function do_deny_all_perms()
{

        global $output, $lang, $db, $template_admin, $cache;

        // -*-*-*-*-*-*-*-*-*-*-*
        // Grab the forum
        // -*-*-*-*-*-*-*-*-*-*-*
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
                
        // Grab wanted forum
        $forum = $db -> query("select id,name from ".$db -> table_prefix."forums where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($forum) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_forum_id']));
                page_main();
                return;
        }
        
        $forum_array = $db -> fetch_array($forum);


        // *********************
        // Init perms to 0
        // *********************
        $perms_info = array(
                "perm_see_board"                => "0",
                "perm_use_search"               => "0",
                "perm_view_other_topic"         => "0",
                "perm_post_topic"               => "0",
                "perm_reply_own_topic"          => "0",
                "perm_reply_other_topic"        => "0",
                "perm_edit_own_post"            => "0",
                "perm_edit_own_topic_title"     => "0",
                "perm_delete_own_post"          => "0",
                "perm_delete_own_topic"         => "0",
                "perm_move_own_topic"           => "0",
                "perm_close_own_topic"          => "0",
                "perm_post_closed_topic"        => "0",
                "perm_remove_edited_by"         => "0",
                "perm_use_html"                 => "0",
                "perm_use_bbcode"               => "0",
                "perm_use_emoticons"            => "0",
                "perm_no_word_filter"           => "0",
                "perm_new_polls"                => "0",
                "perm_vote_polls"               => "0"
        );


        // *********************
        // Go through all groups
        // *********************
        $groups_array = array();
        
        $db -> basic_select("user_groups", "id", "", "id");
        
        while($g = $db -> fetch_array())
                $groups_array[$g['id']] = true;
        
        foreach($groups_array as $gid => $vals)
        {
        
                $perms_info["forum_id"] = $get_id;
                $perms_info["group_id"] = $gid;
                
                // *********************
                // Perm row exist?
                // *********************
                if($db -> num_rows($db->query("select id from ".$db -> table_prefix."forums_perms WHERE forum_id='".$get_id."' and group_id='".$gid."'")) > 0)
                        $db -> basic_update("forums_perms", $perms_info, "forum_id='".$get_id."' and group_id='".$gid."'");
                else
                        $db -> basic_insert("forums_perms", $perms_info);

                perms_copy_to_children($gid, $get_id, $perms_info);
                
        }
        
        // *****************
        // Update Cache
        // *****************
        $cache -> update_cache("forums_perms");

        // *****************
        // Log it
        // *****************
        log_admin_action("forums", "notoall", "Set all forum perms to no: ".$forum_array['name']);

        // *****************
        // Redirect
        // *****************
        $output -> redirect(ROOT."admin/index.php?m=forums", $lang['forum_permissions_saved']);
        
}

?>
