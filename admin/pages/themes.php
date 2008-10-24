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
*       Themes Editor           *
*       Started by Fiona        *
*       12th Jan 2006           *
*********************************
*       Last edit by Fiona      *
*       17th Jan 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Werds are power
//***********************************************
load_language_group("admin_themes");


//***********************************************
// Include functions
//***********************************************
include ROOT."admin/common/funcs/themes.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_themes'], "index.php?m=themes");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
        case "newtheme":
        
                do_new_theme();
                break;

        case "deletetheme":
        
                do_delete_theme();
                break;

        case "edittheme":
        
                page_edit_theme();
                break;

        case "doedittheme":
        
                do_edit_theme();
                break;

        case "importexport":
        
                page_import_export();
                break;

        case "doimport":
        
                do_import();
                break;

        case "doexport":
        
                do_export();
                break;
                
        default:
        
                page_main();
                
}


//***********************************************
// Main theme view
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // Grab all themes
        $themes = $db -> query("select * from ".$db -> table_prefix."themes order by name");

        // Get amount
        $theme_amount = $db -> num_rows($themes);

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_themes_title'];
        
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ----------------
        // THEME LIST
        // ----------------
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['admin_themes_title'], "strip1",  "", "left", "100%", "3").
                $table -> add_basic_row($lang['admin_themes_message'], "normalcell",  "padding : 5px", "left", "100%", "3").
                // ---------------
                // List header
                // ---------------
                $table -> add_row(array($lang['theme_name'],$lang['theme_author'],$lang['theme_actions']), "strip2")
        );

        // For dropdown
        $new_dropdown = array();
        $new_dropdown_text = array();

        // Go through all themes
        while($theme_array = $db -> fetch_array($themes))
        {

                $actions = "
                <a href=\"index.php?m=themes&amp;m2=edittheme&amp;id=".$theme_array['id']."\" title=\"".$lang['edit_theme']."\">
                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                <a href=\"index.php?m=themes&amp;m2=deletetheme&amp;id=".$theme_array['id']."\" onclick=\"return confirm('".$lang['delete_theme_confirm']."')\" title=\"".$lang['delete_theme']."\">
                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>
                ";

                // Add to dropdown arrays
                $new_dropdown[] .= $theme_array['id'];
                $new_dropdown_text[] .= $theme_array['name'];
                
                $output -> add(
                        $table -> add_row(
                                array(
                                        array($theme_array['name'], "30%"),
                                        array($theme_array['author'], "30%"),
                                        array($actions, "40%")
                                )
                        , "normalcell")
                );
        
        }

        // ----------------
        // NEW THEME
        // ----------------
        $output -> add(
                $table -> end_table().
                $form -> start_form("newtheme", ROOT."admin/index.php?m=themes&amp;m2=newtheme", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['new_theme'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['new_theme_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Input
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['new_theme_name'], "50%"),
                                array($form -> input_text("name", ""), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['new_theme_author'], "50%"),
                                array($form -> input_text("author", ""), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['new_theme_inherit'], "50%"),
                                array($form -> input_dropdown("inherit", "", $new_dropdown, $new_dropdown_text), "50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['new_theme_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()                        
        );
        
}


//***********************************************
// Creating a new theme
//***********************************************
function do_new_theme()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ----------------
        // Collect inheritance first
        // ----------------
        $post_id = $_POST['inherit'];

        // Grab wanted theme
        $theme = $db -> query("select * from ".$db -> table_prefix."themes where id='".$post_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($theme) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        } 

        $theme_array = $db -> fetch_array($theme);

        // ----------------
        // Try to create the theme
        // ----------------
        $info = array(
                "name" => addslashes($_POST['name']),
                "css" => $theme_array['css'],
                "image_dir" => $theme_array['image_dir'],
                "author" => addslashes($_POST['author'])
        );
        
        if(!$db -> basic_insert("themes", $info))
        {
                $output -> add($template_admin -> critical_error($lang['new_theme_error']));
                page_main();
                return;
        }               

        // Log it!
        log_admin_action("themes", "newtheme", "Created new theme: ".trim($_POST['name']));

        // Update cache
        $cache -> update_cache("themes");
        
        // Done
        $output -> redirect(ROOT."admin/index.php?m=themes", $lang['theme_created_sucessfully']);

}


//***********************************************
// Deleting a theme
//***********************************************
function do_delete_theme()
{

        global $output, $lang, $db, $template_admin, $cache;


        // ----------------
        // First check if we can delete it
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        }

        // Grab wanted theme
        $theme = $db -> query("select id,name from ".$db -> table_prefix."themes where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($theme) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        } 

        $theme_array = $db -> fetch_array($theme);

        // Remove the set entry itself
        if(!$db -> basic_delete("themes", "id = '".$theme_array['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_theme']));
                page_main();
                return;
        }

        // ----------------
        // If someone has this theme switch it
        // ----------------
        $info = array("theme" => "-1");                      

        if(!$db -> basic_update("users", $info, "theme = '".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_switching_user_themes']));
                page_main();
                return;
        }
        
        // Log it!
        log_admin_action("themes", "deletetheme", "Deleted theme: ".trim($theme_array['name']));

        // Update cache
        $cache -> update_cache("themes");
        
        // Done
        $output -> redirect(ROOT."admin/index.php?m=themes", $lang['theme_deleted_sucessfully']);
                
}


//***********************************************
// Editing a theme
//***********************************************
function page_edit_theme()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // First check if we can delete it
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        }

        // Grab wanted theme
        $theme = $db -> query("select * from ".$db -> table_prefix."themes where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($theme) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        } 

        $theme_array = $db -> fetch_array($theme);

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['edit_theme'];

	$output -> add_breadcrumb($lang['breadcrumb_edit_theme'], "index.php?m=themes&amp;m2=edittheme&amp;id=".$get_id);
        
        // ----------------
        // SHOW FORM
        // ----------------
        $form = new form_generate;
        $table = new table_generate;
        
        $output -> add(
                $table -> end_table().
                $form -> start_form("edittheme", ROOT."admin/index.php?m=themes&amp;m2=doedittheme&amp;id=".$theme_array['id'], "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['edit_theme'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['edit_theme_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Input
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['edit_theme_name'], "30%"),
                                array($form -> input_text("name", $theme_array['name']), "70%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_theme_author'], "30%"),
                                array($form -> input_text("author", $theme_array['author']), "70%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_theme_imgdir'], "30%"),
                                array($form -> input_text("image_dir", $theme_array['image_dir']), "70%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_theme_style'], "30%"),
                                array($form -> input_textbox("css", $theme_array['css'], "20"), "70%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['edit_theme_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

}


//***********************************************
// Do the editing of an individual theme
//***********************************************
function do_edit_theme()
{

        global $output, $lang, $db, $template_admin, $cache;

        $get_id = $_GET['id'];

        // ----------------
        // First check if we can edit
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        }

        // Grab wanted theme
        $theme = $db -> query("select id,name from ".$db -> table_prefix."themes where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($theme) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_theme_id']));
                page_main();
                return;
        } 

        $theme_array = $db -> fetch_array($theme);


        // ----------------
        // Sort Database stuff
        // ----------------
        if(trim($_POST['name']) == "")
                $temp_name = $theme_array['name'];
        else
                $temp_name = trim($_POST['name']);

        $info = array(
                "name" => $temp_name,
                "css" => trim($_POST['css']),
                "author" => $_POST['author'],
                "image_dir" => $_POST['image_dir']
        );


        // Execute it
        if(!$db -> basic_update("themes", $info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_theme']));
                page_edit_theme();
                return;
        }

        // Log it!
        log_admin_action("themes", "edittheme", "Edited theme: ".trim($theme_array['name']));

        // Update cache
        $cache -> update_cache("themes");
        
        // Done
        $output -> redirect(ROOT."admin/index.php?m=themes&amp;m2=edittheme&amp;id=".$theme_array['id'], $lang['theme_edited_sucessfully']);

}



//***********************************************
// Show the page
//***********************************************
function page_import_export()
{

        global $output, $lang, $db;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_menu_export_themes'];

	$output -> add_breadcrumb($lang['breadcrumb_importexport_themes'], "index.php?m=themes&amp;m2=importexport");

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // Grab all themes
        $themes = $db -> query("select id,name from ".$db -> table_prefix."themes order by name");
        
        $themes_dropdown[] .= "-1";
        $themes_dropdown_text[] .= $lang['all_themes_dropdown'];
        
        // Go through all themes
        while($theme_array = $db -> fetch_array($themes))
        {
                // Add to dropdown arrays
                $themes_dropdown[] .= $theme_array['id'];
                $themes_dropdown_text[] .= $theme_array['name'];
        }

        // ----------------
        // EXPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("exportthemes", ROOT."admin/index.php?m=themes&amp;m2=doexport", "post", false, true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['export_themes_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['export_themes_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Export form
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['export_filename']."<br /><font class=\"small_text\">".$lang['export_filename_message']."</font>","50%"),
                                array($form -> input_text("filename", "fsboard-themes.xml"),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['export_which_theme']."<br /><font class=\"small_text\">".$lang['export_which_theme_message']."</font>","50%"),
                                array($form -> input_dropdown("theme", "", $themes_dropdown, $themes_dropdown_text),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['export_themes_submit']).$form->reset("reset", $lang['export_themes_reset']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );


        // ----------------
        // IMPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("importthemes", ROOT."admin/index.php?m=themes&amp;m2=doimport", "post", true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['import_themes_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['import_themes_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Import form
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['import_upload']."<br /><font class=\"small_text\">".$lang['import_upload_message']."</font>","50%"),
                                array($form -> input_file("file"),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['import_filename']."<br /><font class=\"small_text\">".$lang['import_filename_message']."</font>","50%"),
                                array($form -> input_text("filename", "includes/fsboard-themes.xml"),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['import_themes_submit']).$form->reset("reset", $lang['import_themes_reset']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );
        
}



//***********************************************
// Trying to export
//***********************************************
function do_export()
{

        global $output, $lang, $db, $template_admin;

  
        if($_POST['theme'] > "-1")
                $single_id = 'where id = "'.$_POST['theme'].'"';
		else
                $single_id = "";

        // *************************
        // Select the theme(s)
        // *************************
        $select_themes = $db -> query("select * from ".$db -> table_prefix."themes ".$single_id);

        if($db -> num_rows($select_themes) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['getting_themes_error']));
                page_import_export();
                return;
        }


        // *************************
        // Start XML'ing
        // *************************
        $xml = new xml;
        $xml -> export_xml_start();
        $xml -> export_xml_root("theme_file");


        // *************************
        // Spin through themes
        // *************************
        while($themes_array = $db -> fetch_array($select_themes))
        {

                // *************************
                // Start off the group
                // *************************
                $xml -> export_xml_start_group(
                        "theme",
                        array(
                                "name" => $themes_array['name'],
                                "author" => $themes_array['author'],
                                "image_dir" => $themes_array['image_dir']
                        )
                );

                // Add the css entry
                $xml -> export_xml_add_group_entry(
                        "theme_css",
                        array(),
                        $themes_array['css']
                );

                // *************************
                // Finish group
                // *************************
                $xml -> export_xml_generate_group();
                                                
        }


        // *************************
        // Finish XML'ing
        // *************************
        $xml -> export_xml_generate();


        // *************************
        // Work out output file name                
        // *************************
        if($_POST['filename'] == '')                
                $filename = "fsboard-themes.xml";
        else
                $filename = $_POST['filename'];

        
        // *************************
        // Chuck the file out
        // *************************
        output_file($xml -> export_xml, $filename, "text/xml");
         
}


//***********************************************
// Trying to import
//***********************************************
function do_import()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Get file from upload
        if(file_exists($_FILES['file']['tmp_name']))
                $xml_contents = file_get_contents($_FILES['file']['tmp_name']);
        // Get file from server
        elseif(file_exists(ROOT.$_POST['filename']))
                $xml_contents = file_get_contents(ROOT.$_POST['filename']);
        // No file
        else
        {
                $output -> add($template_admin -> normal_error($lang['xml_file_not_found'].$_POST['filename']));
                page_import_export();
                return;
        }

        // *************************
        // Import...
        // *************************
        $get_error = import_themes_xml($xml_contents);

        // If we have version mismatch
        if((string)$get_error == "VERSION")
        {
                $output -> add($template_admin -> critical_error($lang['xml_version_mismatch']));
                return false;
        }

        // Update cache
        $cache -> update_cache("themes");
                
        $output -> add($template_admin -> message($lang['import_done_title'], $lang['import_done_message']));

}

?>
