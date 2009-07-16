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
 * Admin page for attatchments and filetypes
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Wordilating
//***********************************************
load_language_group("admin_attachments");


$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        // -------------------
        // Filetypes
        // -------------------
        case "filetypes":
                page_filetypes_main();
                break;

        case "filetypeadd":
                page_filetypes_add_edit(true);
                break;

        case "dofiletypeadd":
                do_filetypes_add();
                break;

        case "filetypeedit":
                page_filetypes_add_edit();
                break;

        case "dofiletypeedit":
                do_filetypes_edit();
                break;

        case "filetypedelete":
                do_filetypes_delete();
                break;

}



//***********************************************
// Filetypes list
//***********************************************
function page_filetypes_main()
{

        global $lang, $output, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['filetypes_main_title'];

	$output -> add_breadcrumb($lang['breadcrumb_filetypes'], "index.php?m=attachments&m2=filetypes");

        // Create class
        $table = new table_generate;
        $form = new form_generate;

        // ********************
        // Start table
        // ********************
        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['filetypes_main_title'], "strip1",  "", "left", "100%", "6").
                $table -> add_basic_row($lang['filetypes_main_message'], "normalcell",  "padding : 5px", "left", "100%", "6").

                $table -> add_row(array(
                        $lang['filetypes_main_extension'],
                        $lang['filetypes_main_icon'],
                        $lang['filetypes_main_use_avatar'],
                        $lang['filetypes_main_use_attachment'],
                        $lang['filetypes_main_enabled'],
                        $lang['filetypes_main_actions']
                ), "strip2")
        );

        // ********************
        // Grab all filetypes
        // ********************
        $db -> query("select * from ".$db -> table_prefix."filetypes order by `extension` asc");

        // No filetypes?
        if($db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['filetypes_main_no_filetypes']."</b>", "normalcell",  "padding : 10px", "center")
                );        
                
        else
        {

                // *************************
                // Print row for each filetype
                // *************************
                while($f_array = $db-> fetch_array())
                {

                        // Wordy
                        $use_avatar             = ($f_array['use_avatar'])      ? $lang['yes'] : "&nbsp;";
                        $use_attachment         = ($f_array['use_attachment'])  ? $lang['yes'] : "&nbsp;";
                        $enabled                = ($f_array['enabled'])         ? $lang['yes'] : "&nbsp;";

                        // Pic?
                        $icon = ($f_array['icon_file']) ? "<img src=".ROOT.$f_array['icon_file']." alt=\"Icon\">" : "&nbsp;";

                        // Linky linky to actions
                        $actions = "
                                <a href=\"index.php?m=attachments&amp;m2=filetypeedit&amp;id=".$f_array['id']."\" title=\"".$lang['filetypes_main_edit']."\">
                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                                <a href=\"index.php?m=attachments&amp;m2=filetypedelete&amp;id=".$f_array['id']."\" title=\"".$lang['filetypes_main_delete']."\" onclick=\"return confirm('".$lang['delete_filetype_confirm']."')\">
                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                        // Print it
                        $output -> add(
                                $table -> add_row(      
                                        array(
                                                $f_array['name']." (<b>".$f_array['extension']."</b>)",
                                                $icon,
                                                $use_avatar,
                                                $use_attachment,
                                                $enabled,
                                                $actions
                                        ),
                                "normalcell")
                        );
                        
                }
                
        }        

        
        // ********************
        // End table
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> button("addfiletype", $lang['filetypes_main_add_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=attachments&amp;m2=filetypeadd';\"")
                , "strip3", "", "center").
                $table -> end_table().
                $form -> end_form()
        );
        
}



//***********************************************
// Filetype adding/editing form
//***********************************************
function page_filetypes_add_edit($adding = false, $filetype_info = "")
{

        global $output, $lang, $db;

	$output -> add_breadcrumb($lang['breadcrumb_filetypes'], "index.php?m=attachments&m2=filetypes");

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ***************************
        // Adding?
        // ***************************
        if($adding)
        {

                // Set page title
                $output -> page_title = $lang['add_filetype_title'];

		$output -> add_breadcrumb($lang['breadcrumb_filetype_add'], "index.php?m=attachments&m2=filetypeadd");

                // Start of form
                $output -> add(
                        $form -> start_form("addfiletype", ROOT."admin/index.php?m=attachments&amp;m2=dofiletypeadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                        $table -> add_basic_row($lang['add_filetype_title'], "strip1",  "", "left", "100%", "2")
                );

                // Set submit button text
                $submit_lang = $lang['add_filetype_submit'];

                // Initialise a value or two
                if(!$filetype_info)
                {

                        $filetype_info['icon_file'] = "images/filetypes/unknown.gif";
                        $filetype_info['mime_type'] = "Content-type: unknown/unknown";
                        $filetype_info['enabled'] = 1;
                        $filetype_info['use_avatar'] = 1;
                        $filetype_info['use_attachment'] = 1;

                }
                
        }
        // ***************************
        // Editing?
        // ***************************
        else
        {

                // Set page title
                $output -> page_title = $lang['edit_filetype_title'];

                // Grab the field
                $get_id = trim($_GET['id']);
        
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_filetype_id']));
                        page_filetypes_main();
                        return;
                }
                
                // Grab wanted field
                $filetype = $db -> query("select * from ".$db -> table_prefix."filetypes where id='".$get_id."'");

                // Die if it doesn't exist
                if($db -> num_rows($filetype) == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_filetype_id']));
                        page_filetypes_main();
                        return;
                }

                $filetype_info = $db -> fetch_array($filetype);

		$output -> add_breadcrumb($lang['breadcrumb_filetype_edit'], "index.php?m=attachments&amp;m2=filetype&amp;id=".$get_id);

                // Start of form
                $output -> add(
                        $form -> start_form("editfiletype", ROOT."admin/index.php?m=attachments&amp;m2=dofiletypeedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                        $table -> add_basic_row($lang['edit_filetype_title'], "strip1",  "", "left", "100%", "2")
                );

                // Set submit button text
                $submit_lang = $lang['edit_filetype_submit'];

        }

        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> add_row(
                        array(
                                array($lang['add_filetype_name'], "50%"),
                                array($form -> input_text("name", $filetype_info['name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_extension']."<br /><font class=\"small_text\">".$lang['add_filetype_extension_desc']."</font>",
                                $form -> input_text("extension", $filetype_info['extension'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_mime_type']."<br /><font class=\"small_text\">".$lang['add_filetype_mime_type_desc']."</font>",
                                $form -> input_text("mime_type", $filetype_info['mime_type'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_icon_file']."<br /><font class=\"small_text\">".$lang['add_filetype_icon_file_desc']."</font>",
                                $form -> input_text("icon_file", $filetype_info['icon_file'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_enabled']."<br /><font class=\"small_text\">".$lang['add_filetype_enabled_desc']."</font>",
                                $form -> input_yesno("enabled", $filetype_info['enabled'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_use_avatar']."<br /><font class=\"small_text\">".$lang['add_filetype_use_avatar_desc']."</font>",
                                $form -> input_yesno("use_avatar", $filetype_info['use_avatar'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_use_attachment']."<br /><font class=\"small_text\">".$lang['add_filetype_use_attachment_desc']."</font>",
                                $form -> input_yesno("use_attachment", $filetype_info['use_attachment'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_max_file_size']."<br /><font class=\"small_text\">".$lang['add_filetype_max_file_size_desc']."</font>",
                                $form -> input_int("max_file_size", $filetype_info['max_file_size'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_max_width']."<br /><font class=\"small_text\">".$lang['add_filetype_max_width_desc']."</font>",
                                $form -> input_int("max_width", $filetype_info['max_width'])
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_filetype_max_height']."<br /><font class=\"small_text\">".$lang['add_filetype_max_height_desc']."</font>",
                                $form -> input_int("max_height", $filetype_info['max_height'])
                        )
                , "normalcell").
                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );

}


//***********************************************
// Adding the file type fo' real
//***********************************************
function do_filetypes_add()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Get stuff from the post
        // **********************
        $filetype_info = array(
                "name"                  => $_POST['name'],
                "extension"             => $_POST['extension'],
                "mime_type"             => $_POST['mime_type'],
                "use_avatar"            => $_POST['use_avatar'],
                "use_attachment"        => $_POST['use_attachment'],
                "enabled"               => $_POST['enabled'],
                "icon_file"             => $_POST['icon_file'],
                "max_file_size"         => $_POST['max_file_size'],
                "max_width"             => $_POST['max_width'],
                "max_height"            => $_POST['max_height']
        );

        // **********************
        // Check there's something in the extension
        // **********************
        if(trim($filetype_info['extension']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_filetype_no_ext']));
                page_filetypes_add_edit(true, $filetype_info);
                return;
        }               

        // **********************
        // Check it doesn't already exist
        // **********************
        $db -> query("select id from ".$db -> table_prefix."filetypes where extension='".$filetype_info['extension']."'");
        if($db -> num_rows() > 0) 
        {
                $output -> add($template_admin -> normal_error($lang['add_filetype_ext_exists']));
                page_filetypes_add_edit(true, $filetype_info);
                return;
        }    

        // **********************
        // Add it!
        // **********************
        if(!$db -> basic_insert("filetypes", $filetype_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_filetype_error']));
                page_filetypes_add_edit(true, $filetype_info);
                return;
        }    

        // **********************
        // Update cache
        // **********************
        $cache -> update_cache("filetypes");

        // **********************
        // Log it!
        // **********************
        log_admin_action("attachments", "dofiletypeadd", "Added file type: ".$filetype_info['extension']);
        
        // **********************
        // Done
        // **********************
        $output -> redirect(ROOT."admin/index.php?m=attachments&amp;m2=filetypes", $lang['filetype_created_sucessfully']);
        
}


//***********************************************
// Edit a file type submit lol stuff
//***********************************************
function do_filetypes_edit()
{

        global $output, $lang, $db, $template_admin, $cache;


        // **********************
        // Grab the field
        // **********************
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_filetype_id']));
                page_filetypes_main();
                return;
        }
        
        // Grab wanted field
        $filetype = $db -> query("select extension from ".$db -> table_prefix."filetypes where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($filetype) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_filetype_id']));
                page_filetypes_main();
                return;
        }

        // Grab
        $old_filetype = $db -> fetch_array($filetype);

        // **********************
        // Get stuff from the post
        // **********************
        $filetype_info = array(
                "name"                  => $_POST['name'],
                "extension"             => $_POST['extension'],
                "mime_type"             => $_POST['mime_type'],
                "use_avatar"            => $_POST['use_avatar'],
                "use_attachment"        => $_POST['use_attachment'],
                "enabled"               => $_POST['enabled'],
                "icon_file"             => $_POST['icon_file'],
                "max_file_size"         => $_POST['max_file_size'],
                "max_width"             => $_POST['max_width'],
                "max_height"            => $_POST['max_height']
        );

        // **********************
        // Check there's something in the extension
        // **********************
        if(trim($filetype_info['extension']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_filetype_no_ext']));
                page_filetypes_add_edit(false, $filetype_info);
                return;
        }               

        // **********************
        // Check it doesn't already exist
        // **********************
        if($old_filetype['extension'] != $filetype_info['extension'])
        {
        
                $db -> query("select id from ".$db -> table_prefix."filetypes where extension='".$filetype_info['extension']."'");
                if($db -> num_rows() > 0) 
                {
                        $output -> add($template_admin -> normal_error($lang['add_filetype_ext_exists']));
                        page_filetypes_add_edit(false, $filetype_info);
                        return;
                }    

        }
        
        // **********************
        // Do the query
        // **********************
        if(!$db -> basic_update("filetypes", $filetype_info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_editing_filetype']));
                page_filetypes_add_edit(false, $filetype_info);
                return;
        }

        // **********************
        // Update cache
        // **********************
        $cache -> update_cache("filetypes");

        // **********************
        // Log it!
        // **********************
        log_admin_action("attachments", "dofiletypeedit", "Edited file type: ".$filetype_info['extension']);
        
        // **********************
        // Done
        // **********************
        $output -> redirect(ROOT."admin/index.php?m=attachments&amp;m2=filetypes", $lang['filetype_edited_sucessfully']);
        
}



//***********************************************
// BALEETE File type
//***********************************************
function do_filetypes_delete()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Grab the field
        // **********************
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_filetype_id']));
                page_filetypes_main();
                return;
        }
        
        // Grab wanted field
        $filetype = $db -> query("select extension from ".$db -> table_prefix."filetypes where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($filetype) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_filetype_id']));
                page_filetypes_main();
                return;
        }

        // Grab
        $filetype_array = $db -> fetch_array($filetype);

        // ********************
        // Delete it
        // ********************
        if(!$db -> query("DELETE FROM ".$db -> table_prefix."filetypes WHERE id='".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['delete_filetype_fail']));
                page_main();
                return;
        }
        
        // **********************
        // Update cache
        // **********************
        $cache -> update_cache("filetypes");

        // **********************
        // Log it!
        // **********************
        log_admin_action("attachments", "dofiletypedelete", "Deleted file type: ".$filetype_array['extension']);
        
        // **********************
        // Done
        // **********************
        $output -> redirect(ROOT."admin/index.php?m=attachments&amp;m2=filetypes", $lang['filetype_deleted_sucessfully']);

}
?>
