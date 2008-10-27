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
*       Custom BBCode           *
*       Started by Fiona        *
*       16th Apr 2006           *
*********************************
*       Last edit by Fiona      *
*       21st Jun 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// include my words
//***********************************************
load_language_group("admin_bbcode");


$output -> add_breadcrumb($lang['breadcrumb_bbcode'], "index.php?m=bbcode");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_bbcode(true);
                break;
                
        case "doadd":
                do_add_bbcode();
                break;

        case "edit":
                page_add_edit_bbcode();
                break;
                
        case "doedit":
                do_edit_bbcode();
                break;

        case "delete":
                do_delete_bbcode();
                break;
                
        default:
                page_main();

}


//***********************************************
// The BBcode listing
//***********************************************
function page_main()
{

        global $lang, $output, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['bbcode_main_title'];

        // Create class
        $table = new table_generate;
        $form = new form_generate;


        // ********************
        // Start table
        // ********************
        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['bbcode_main_title'], "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['bbcode_main_message'], "normalcell",  "padding : 5px", "left", "100%", "4").
                
                $table -> add_row(
                        array(
                                array($lang['bbcode_main_name'], "auto"),
                                array($lang['bbcode_main_tag'], "auto"),
                                array($lang['bbcode_main_example'], "auto"),
                                array($lang['bbcode_main_actions'], "auto")
                        )
                , "strip2")
        );
        

        // ********************
        // Grab all code
        // ********************
        $db -> query("select * from ".$db -> table_prefix."bbcode order by `tag` asc");

        // No bbcode?
        if( $db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_bbcode']."</b>", "normalcell",  "padding : 10px", "center")
                );        
                
        else
        {

                // *************************
                // Go through each code if we have some
                // *************************
                while($b_array = $db-> fetch_array())
                {

                        // Linky linky to actions
                        $actions = "
                        <a href=\"".ROOT."admin/index.php?m=bbcode&amp;m2=edit&amp;id=".$b_array['id']."\" title=\"".$lang['bbcode_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=bbcode&amp;m2=delete&amp;id=".$b_array['id']."\" onclick=\"return confirm('".$lang['delete_bbcode_confirm']."')\" title=\"".$lang['bbcode_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($b_array['name'], "auto"),
                                                array("[<b>".$b_array['tag']."</b>]", "auto"),
                                                array($b_array['example'], "auto"),
                                                array($actions, "auto")
                                        )
                                , "normalcell")
                        );
                        
                }

        }

        // ********************
        // End table
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> button("addbbcode", $lang['add_bbcode_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=bbcode&m2=add';\"")
                , "strip3", "", "center", "100%").
                $table -> end_table().
                $form -> end_form()
        );
                        
}



//***********************************************
// Form for adding or editing bbcode
//***********************************************
function page_add_edit_bbcode($adding = false, $bbcode_info = "")
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
                $output -> page_title = $lang['add_bbcode_title'];

		$output -> add_breadcrumb($lang['breadcrumb_bbcode_add'], "index.php?m=bbcode&m2=add");

                $output -> add(
                        $form -> start_form("addbbcode", ROOT."admin/index.php?m=bbcode&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['add_bbcode_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_bbcode_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_bbcode_submit'];

        }
        else
        {

                // **************************
                // Grab the code
                // **************************
                $get_id = trim($_GET['id']);
                
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_bbcode_id']));
                        page_main();
                        return;
                }
                        
                // Grab wanted task
                $db -> query("select * from ".$db -> table_prefix."bbcode where id='".$get_id."'");
        
                // Die if it doesn't exist
                if($db -> num_rows() == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_bbcode_id']));
                        page_main();
                        return;
                }

                $bbcode_info = $db -> fetch_array();
        
                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_bbcode_title'];

		$output -> add_breadcrumb($lang['breadcrumb_bbcode_edit'], "index.php?m=bbcode&m2=edit");

                $output -> add(
                        $form -> start_form("editbbcode", ROOT."admin/index.php?m=bbcode&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['edit_bbcode_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_bbcode_submit'];
        
        }

        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_name'], "50%"),
                                array($form -> input_text("name", $bbcode_info['name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_description']."<br /><font class=\"small_text\">".$lang['add_bbcode_description_desc']."</font>", "50%"),
                                array($form -> input_textbox("description", $bbcode_info['description']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_example']."<br /><font class=\"small_text\">".$lang['add_bbcode_example_desc']."</font>", "50%"),
                                array($form -> input_textbox("example", $bbcode_info['example']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_button_image']."<br /><font class=\"small_text\">".$lang['add_bbcode_example_desc']."</font>", "50%"),
                                array($form -> input_text("button_image", $bbcode_info['button_image']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_tag']."<br /><font class=\"small_text\">".$lang['add_bbcode_tag_desc']."</font>", "50%"),
                                array("[ ".$form -> input_text("tag", $bbcode_info['tag'], "inputtext", "50%")." ]", "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_use_param']."<br /><font class=\"small_text\">".$lang['add_bbcode_use_param_desc']."</font>", "50%"),
                                array($form -> input_yesno("use_param", $bbcode_info['use_param']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_bbcode_replacement']."<br /><font class=\"small_text\">".$lang['add_bbcode_replacement_desc']."</font>", "50%"),
                                array($form -> input_textbox("replacement", $bbcode_info['replacement']), "50%")
                        )
                , "normalcell").

                // -----------
                // Submit Button
                // -----------
                $table -> add_basic_row($form -> submit("submit", $submit_lang), "strip3", "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );   
                
}


//***********************************************
// Add the BBCode
//***********************************************
function do_add_bbcode()
{

        global $output, $lang, $db, $template_admin, $cache;


        // **********************
        // Get stuff from the post
        // **********************
        $bbcode_info = array(
                "name"                  => $_POST['name'],
                "description"           => $_POST['description'],
                "example"               => $_POST['example'],
                "button_image"          => $_POST['button_image'],
                "tag"                   => $_POST['tag'],
                "use_param"             => $_POST['use_param'],
                "replacement"           => $_POST['replacement']
        );

        // ***************************
        // Check there's something in the tag and stuff
        // ***************************
        if(trim($bbcode_info['tag']) == "" || trim($bbcode_info['replacement']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_bbcode_tag_empty']));
                page_add_edit_bbcode(true, $bbcode_info);
                return;
        }               

        // *********************
        // Check tag doesn't already exist
        // *********************
        $db -> query("select id from ".$db -> table_prefix."bbcode where tag='".$bbcode_info['tag']."'");

        // Die if it does
        if($db -> num_rows() > 0)
        {
                $output -> add($template_admin -> critical_error($lang['bbcode_tag_already_exists']));
                page_add_edit_bbcode(true, $bbcode_info);
                return;
        }

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("bbcode", $bbcode_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_bbcode_error']));
                page_add_edit_bbcode(true, $bbcode_info);
                return;
        }               

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("custom_bbcode");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("bbcode", "doadd", "Added BBCode: ".$bbcode_info['tag']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=bbcode", $lang['bbcode_created_sucessfully']);

}


//***********************************************
// Edit a BBCode
//***********************************************
function do_edit_bbcode()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the code
        // **************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_bbcode_id']));
                page_main();
                return;
        }
                
        // Grab wanted task
        $db -> query("select id from ".$db -> table_prefix."bbcode where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows() == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_bbcode_id']));
                page_main();
                return;
        }

        // **********************
        // Get stuff from the post
        // **********************
        $bbcode_info = array(
                "name"                  => $_POST['name'],
                "description"           => $_POST['description'],
                "example"               => $_POST['example'],
                "button_image"          => $_POST['button_image'],
                "tag"                   => $_POST['tag'],
                "use_param"             => $_POST['use_param'],
                "replacement"           => $_POST['replacement']
        );

        // ***************************
        // Check there's something in the tag and stuff
        // ***************************
        if(trim($bbcode_info['tag']) == "" || trim($bbcode_info['replacement']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_bbcode_tag_empty']));
                page_add_edit_bbcode(false, $bbcode_info);
                return;
        }               

        // *********************
        // Check tag doesn't already exist
        // *********************
        $db -> query("select id from ".$db -> table_prefix."bbcode where tag='".$bbcode_info['tag']."' and id <> '".$get_id."'");

        // Die if it does
        if($db -> num_rows() > 0)
        {
                $output -> add($template_admin -> critical_error($lang['bbcode_tag_already_exists']));
                page_add_edit_bbcode(false, $bbcode_info);
                return;
        }

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("bbcode", $bbcode_info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_editing_bbcode']));
                page_main();
                return;
        }
        
        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("custom_bbcode");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("bbcode", "doedit", "Edited BBCode: ".$bbcode_info['tag']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=bbcode", $lang['bbcode_edited_sucessfully']);

}


//***********************************************
// Nuke some BBcode
//***********************************************
function do_delete_bbcode()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the code
        // **************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_bbcode_id']));
                page_main();
                return;
        }
                
        // Grab wanted task
        $db -> query("select id,tag from ".$db -> table_prefix."bbcode where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows() == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_bbcode_id']));
                page_main();
                return;
        }
        
        $bbcode_info = $db -> fetch_array();

        // ********************
        // Delete it
        // ********************
        $db -> query("DELETE FROM ".$db -> table_prefix."bbcode WHERE id='".$get_id."'");

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("custom_bbcode");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("bbcode", "dodelete", "Removed BBCode: ".$bbcode_info['tag']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=bbcode", $lang['bbcode_deleted_sucessfully']);

}

?>
