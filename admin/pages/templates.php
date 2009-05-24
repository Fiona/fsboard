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
*       Templates Editor        *
*       Started by Fiona        *
*       5th Sep 2005            *
*********************************
*       Last edit by Fiona      *
*       26th Feb 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Werds are power
//***********************************************
load_language_group("admin_templates");


//***********************************************
// Functions  - This is require_once because the debug mode
// that loads templates from the database already loads this
// file.
//***********************************************
require_once ROOT."admin/common/funcs/templates.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_templates'], "index.php?m=templates");


$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
        case "newset":
        
                do_new_template_set();
                break;

        case "deleteset":
        
                do_delete_template_set();
                break;

        case "setdefault":
        
                do_set_default_template_set();
                break;
                
        case "editset":
        
                page_edit_template_set();
                break;

        case "doeditset":
        
                do_edit_template_set();
                break;

        case "showtemplates":
        
                page_show_templates();
                break;

        case "edittemplate":
        
                page_edit_template();
                break;

        case "doedittemplate":
        
                do_edit_template();
                break;

        case "previewtemplate":
        
                page_preview_template();
                break;
        
        case "rebuild":

                do_rebuild_file();
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

        // ---------
        // Dev stuff
        // ---------
        case "newtemplate":
        
                do_new_template();
                break;
                
        default:
        
                page_main();
                
}

//***********************************************
// Main template set view
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // Grab all template sets
        $template_sets = $db -> query("select * from ".$db -> table_prefix."template_sets order by name");

        // Get amount
        $template_set_amount = $db -> num_rows($template_sets);

        // Default template set is?
        $db -> basic_select("config", "value", "name='default_template_set'");
        $default_set = $db -> result();

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_template_set_title'];
        
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // For dropdown
        $new_dropdown = array();
        $new_dropdown_text = array();

        // ----------------
        // TEMPLATE LIST
        // ----------------
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['admin_template_set_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['admin_template_set_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // List header
                // ---------------
                $table -> add_row(array($lang['template_set_name'],$lang['actions']), "strip2")
        );

        // Go through all sets
        while($template_array = $db -> fetch_array($template_sets))
        {
                $cell_1 = ""; $cell_2 = "";
                
                // If it's default show it!
                if($template_array['id'] == $default_set)
                        $cell_1 .= "<img src=\"".IMGDIR."/default-icon.gif\" style=\"vertical-align:bottom;\" title=\"".$lang['default_template_blip']."\">";
                       
                // Name and edit links 
                $cell_1 .= "<b>".$template_array['name']."</b>
                <a href=\"index.php?m=templates&amp;m2=editset&amp;id=".$template_array['id']."\" title=\"".$lang['edit_template_set']."\">
                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>";
        
                // Only print the delete button if we're allowed
                if($template_set_amount > 1 && $template_array['id'] != $default_set)
                        $cell_1 .= " <a href=\"index.php?m=templates&amp;m2=deleteset&amp;id=".$template_array['id']."\" onclick=\"return confirm('".$lang['delete_set_confirm']."')\" title=\"".$lang['delete_template_set']."\">
                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                // Second cell. View temp links.
                $cell_2 .= "[ <a href=\"index.php?m=templates&amp;m2=showtemplates&amp;id=".$template_array['id']."\">".$lang['edit_templates']."</a>";

                // If it's not defualt allow us to change it!
                if($template_array['id'] != $default_set)
                        $cell_2 .= " - <a href=\"index.php?m=templates&amp;m2=setdefault&amp;id=".$template_array['id']."\">".$lang['set_default']."</a>";

                $cell_2 .= " - <a href=\"".ROOT."admin/index.php?m=templates&amp;m2=rebuild&amp;group=ALL&amp;id=".$template_array['id']."\" onClick=\"return confirm('".$lang['templates_rebuild_all_confirm']."')\">".$lang['rebuild_all_files']."</a> ]";

                // Add to dropdown arrays
                $new_dropdown[] .= $template_array['id'];
                $new_dropdown_text[] .= $template_array['name'];
                
                $output -> add(
                        $table -> add_row(
                                array(
                                        array($cell_1, "50%"),
                                        array($cell_2, "50%")
                                )
                        , "normalcell")
                );
                
        }
                
        $output -> add($table -> end_table());

        // ----------------
        // NEW TEMPLATE
        // ----------------
        // Check if the skin dir is writable
        if(!is_writable(ROOT."templates"))
        {
        
                $output -> add($template_admin -> critical_error($lang['templates_dir_not_writable']));
        
        }
        else        
                $output -> add(
                        $form -> start_form("newtemplateset", ROOT."admin/index.php?m=templates&amp;m2=newset", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['new_template_set'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['new_template_set_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                        // ---------------
                        // Input
                        // ---------------
                        $table -> add_row(
                                array(
                                        array($lang['new_template_name'], "50%"),
                                        array($form -> input_text("setname", ""), "50%")
                                )
                        , "normalcell").
                        $table -> add_row(
                                array(
                                        array($lang['new_template_author'], "50%"),
                                        array($form -> input_text("setauthor", ""), "50%")
                                )
                        , "normalcell").
                        $table -> add_row(
                                array(
                                        array($lang['new_template_inherit'], "50%"),
                                        array($form -> input_dropdown("setinherit", "", $new_dropdown, $new_dropdown_text), "50%")
                                )
                        , "normalcell").
                        // ---------------
                        // Submit
                        // ---------------
                        $table -> add_basic_row($form->submit("submit", $lang['new_template_submit']), "strip3",  "", "center", "100%", "2").
                        $table -> end_table().
                        $form -> end_form()                        
                );

}


//***********************************************
// Creating a new template set
//***********************************************
function do_new_template_set()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Collect inheritance first
        // ----------------
        $post_id = $_POST['setinherit'];

        // Grab wanted template set
        $template_set = $db -> query("select default_theme from ".$db -> table_prefix."template_sets where id='".$post_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        } 

        $template_set_array = $db -> fetch_array($template_set);

        // Steal the templates from the database
        $templates_query = $db -> query("select * from ".$db -> table_prefix."templates where set_id='".$post_id."' order by class_name,name");

        // ----------------
        // Try to create the set
        // ----------------
        $insert_array = array(
                        "name" => trim($_POST['setname']),
                        "default_theme" => $template_set_array['default_theme'],
                        "author" => trim($_POST['setauthor'])
                );
                
        if(!$db -> basic_insert("template_sets", $insert_array))
        {
                $output -> add($template_admin -> critical_error($lang['template_set_sql_error']));
                page_main();
                return;
        }               

        // Id please
        $new_set_id = $db -> insert_id();

        // ----------------
        // Stick the inherited templates in the DB
        // ----------------
        while($template_array = $db -> fetch_array($templates_query))
        {

                // Insert the template!
                $insert_array = array(
                                "name"                 => $template_array['name'],
                                "class_name"         => $template_array['class_name'],
                                "set_id"         => $new_set_id,
                                "function_name" => $template_array['function_name'],
                                "text"                 => $template_array['text'],
                                "parameters"         => $template_array['parameters']
                        );

                if(!$db -> basic_insert("templates", $insert_array))
                {
                        $output -> add($template_admin -> critical_error($lang['template_set_temp_sql_error']));
                        page_main();
                        return;
                }               
        
        }

        // ----------------
        // Create the files
        // ----------------
        // Make the directory
        if(!mkdir(ROOT."templates/template_id".$new_set_id, 0777))
        {
                $output -> add($template_admin -> critical_error($lang['template_set_dir_error']));
                page_main();
                return;
        }
        
        // Build the new files
        build_template_files($new_set_id);

        // Log it!
        log_admin_action("templates", "newset", "Created new template set: ".trim($_POST['setname']));
        
        // Done, PHEW!
        $output -> redirect(ROOT."admin/index.php?m=templates", $lang['set_created_sucessfully']);

}


//***********************************************
// Deleting a template set
//***********************************************
function do_delete_template_set()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // First check if we can delete it
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
                
        // Grab wanted template set
        $template_set = $db -> query("select id,name from ".$db -> table_prefix."template_sets where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
        else
                $template_array = $db -> fetch_array($template_set);
 
        // Default template set is?
        $db -> basic_select("config", "value", "name='default_template_set'");
        $default_set = $db -> result();
        
        // Die if it's set as default
        if($template_array['id'] == $default_set)
        {
                $output -> add($template_admin -> critical_error($lang['delete_template_set_default']));
                page_main();
                return;
        }


        // ----------------
        // Sort out database stuff
        // ----------------
        // Remove the templates in the set
        if(!$db -> basic_delete("templates", "set_id = '".$template_array['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_templates_in_set']));
                page_main();
                return;
        }

        // Remove the set entry itself
        if(!$db -> basic_delete("template_sets", "id = '".$template_array['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_template_set']));
                page_main();
                return;
        }


        // ----------------
        // Play with the files
        // ----------------
        // Remove the folder
        foreach(glob(ROOT."templates/template_id".$template_array['id']."/*.*") as $filename)
        {
                unlink($filename);
        }
        rmdir(ROOT."templates/template_id".$template_array['id']);

        // Log it!
        log_admin_action("templates", "deleteset", "Removed template set: ".$template_array['name']);

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=templates", $lang['deleted_template_set']);


}


//***********************************************
// Setting a template set as default
//***********************************************
function do_set_default_template_set()
{

        global $output, $lang, $db, $template_admin, $cache;


        // ----------------
        // Check if we can set it as default
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
                
        // Grab wanted template set
        $template_set = $db -> query("select id from ".$db -> table_prefix."template_sets where id='".$get_id."'");
        $template_array = $db -> fetch_array($template_set);
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }

        // Default template set is?
        $db -> basic_select("config", "value", "name='default_template_set'");
        $default_set = $db -> result();

        // Die if it's set as default
        if($template_array['id'] == $default_set)
        {
                $output -> add($template_admin -> critical_error($lang['setdefault_template_set_default']));
                page_main();
                return;
        }


        // ----------------
        // Set the template set to default
        // ----------------
        $setting = array(
                "value" => $template_array['id']
        );                        

        if(!$db -> basic_update("config", $setting, "name = 'default_template_set'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_setting_default_set']));
                page_main();
                return;
        }

        // Update config cache
        $cache -> update_cache("config");

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=templates", $lang['setdefault_sucessfully']);

}

//***********************************************
// Page to edit a template set
//***********************************************
function page_edit_template_set()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // Grab the set
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
                
        // Grab wanted template set
        $template_set = $db -> query("select * from ".$db -> table_prefix."template_sets where id='".$get_id."'");
        $template_array = $db -> fetch_array($template_set);
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }


        // ----------------
        // Build theme dropdown
        // ----------------
        // Grab all the themes for the dropdown                        
        $select_themes_query = $db -> query("select id, name from ".$db -> table_prefix."themes order by name");
        
        // Get the arrays ready for the dropdown
        while($theme_array = $db -> fetch_array($select_themes_query))
        {
                $dropdown_values_array[] .= $theme_array['id'];
                $dropdown_text[] .= $theme_array['name'];                
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['edit_template_set_title'];

        $output -> add_breadcrumb($lang['breadcrumb_templates_edit_set'], "index.php?m=templates&amp;m2=editset&amp;id=".$template_array['id']);
        
        // ----------------
        // Generate the form
        // ----------------
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ----------------
        // EDIT FORM
        // ----------------
        $output -> add(
                $form -> start_form("editset", ROOT."admin/index.php?m=templates&amp;m2=doeditset&amp;id=".$template_array['id'], "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['edit_template_set_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['edit_template_set_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Form entries
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['edit_template_set_name'], "50%"),
                                array($form -> input_text("name", $template_array['name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_template_set_author'], "50%"),
                                array($form -> input_text("author", $template_array['author']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_template_set_theme'], "50%"),
                                array($form -> input_dropdown("default_theme", $template_array['default_theme'], $dropdown_values_array, $dropdown_text), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_template_set_can_change_theme'], "50%"),
                                array($form -> input_yesno("can_change_theme", $template_array['can_change_theme']), "50%")
                        )
                , "normalcell").
                $table -> add_basic_row($form -> submit("submit", $lang['admin_templateset_submit']), "strip3",  "padding : 5px").
                // ---------------
                // Finish off
                // ---------------
                $table -> end_table().
                $form -> end_form()
        );

}

//***********************************************
// Editing a template set
//***********************************************
function do_edit_template_set()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Grab the set
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
                
        // Grab wanted template set
        $template_set = $db -> query("select * from ".$db -> table_prefix."template_sets where id='".$get_id."'");
        $template_array = $db -> fetch_array($template_set);
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }

        // ----------------
        // Do the query
        // ----------------
        $info = array(
                "name" => $_POST['name'],
                "author" => $_POST['author'],
                "default_theme" => $_POST['default_theme'],
                "can_change_theme" => $_POST['can_change_theme']
        );                        

        if(!$db -> basic_update("template_sets", $info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_set']));
                page_main();
                return;
        }

        // Log it!
        log_admin_action("templates", "doeditset", "Edited template set: ".$template_array['name']);

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=templates&amp;m2=editset&amp;id=".$get_id, $lang['set_updated_sucessfully']);

}


//***********************************************
// Showing the template list
//***********************************************
function page_show_templates()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Grab the set
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
                
        // Grab wanted template set
        $template_set = $db -> query("select name from ".$db -> table_prefix."template_sets where id='".$get_id."'");
        $template_set_array = $db -> fetch_array($template_set);
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }


        // ----------------
        // Steal the templates from the database
        // ----------------
        $templates_query = $db -> query("select id,name,class_name from ".$db -> table_prefix."templates where set_id='".$get_id."' order by class_name,name");

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['template_list_title'].$template_set_array['name'];

        $output -> add_breadcrumb($lang['breadcrumb_templates_list'], "index.php?m=templates&amp;m2=showtemplates&amp;id=".$get_id);
        
        // ----------------
        // Page header
        // ----------------
        $table = new table_generate;
        $output -> add(
                "<script src=\"".ROOT."admin/admin_jscript.js\" type=\"text/javascript\"></script>".
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['template_list_title'].$template_set_array['name'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['template_list_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                $table -> end_table()
        );


        // ----------------
        // Go through all of the templates
        // ----------------
        $current_class_name = "";
        $group_number = 0;
        $div = "";

        while($template_array = $db -> fetch_array($templates_query))
        {
        
                // Check if we want to put up the group header
                if($current_class_name != $template_array['class_name'])
                {

                        if($current_class_name != $template_array['class_name'] && $group_number > 0)
                        {
                                $div .= "<p align=center> [ <a href=\"".ROOT."admin/index.php?m=templates&amp;m2=rebuild&amp;group=".$current_class_name."&amp;id=".$get_id."\">".$lang['rebuild_this_file']."</a> ]</p>";
                                $output -> add($table->add_basic_row($div, "normalcell", "", "left"));
                        }
                        
                        $group_number ++;       
                         
                        $output -> add($table->end_table());
                        
                        if(trim($_GET['open_set']) == $template_array['class_name'])
                        {
                                $display_css = "";
                                $collapse_image = "/collapse.gif";
                        }
                        else
                        {
                                $display_css = "display:none; ";
                                $collapse_image = "/expand.gif";
                        }   
                        
                        $output -> add(
                                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                                $table -> add_basic_row(
                                        "<img id=\"img_".$group_number."\" src=\"".IMGDIR.$collapse_image."\"> ".$lang['template_group_name_'.$template_array['class_name']]
                                , "strip2", "cursor:pointer;", "left", "100%", "", "onclick=\"javascript:collapse('".$group_number."', '".IMGDIR."');\"")
                        );
                        
                        $div = "<div style=\"".$display_css."margin : 0px;\" id=\"tpl_row_".$group_number."\">";
                                     
                }

                // Save the class name
                $current_class_name = $template_array['class_name'];                

                $div .= "
                <div class=\"darkernormalcell\" style=\"margin : 3px; padding : 2px;\">           
                        <p>
                                <a href=\"index.php?m=templates&amp;m2=edittemplate&amp;id=".$template_array['id']."\" title=\"".$lang['edit_template_button']."\"><img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                                <a href=\"index.php?m=templates&amp;m2=previewtemplate&amp;id=".$template_array['id']."\" title=\"".$lang['preview_template_button']."\" target=\"_blank\"><img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-preview.png\"></a>
                                <b>".$template_array['name']."</b>
                        </p>
                </div>
                ";
                
        }
        
        if($div != "")
        {
                $div .= "<p align=center> [ <a href=\"".ROOT."admin/index.php?m=templates&amp;m2=rebuild&amp;group=".$current_class_name."&amp;id=".$get_id."\">".$lang['rebuild_this_file']."</a> ]</p>";
                $output -> add($table->add_basic_row($div, "normalcell", "", "left"));
        }
        
        $output -> add(
                $table->end_table()
        );

        // As delveloper we can add a template
        if(defined("DEVELOPER"))
        {

                $form = new form_generate;
                
                $output -> add(
                        $form -> start_form("newtemplate", ROOT."admin/index.php?m=templates&amp;m2=newtemplate&amp;id=".$get_id).
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_top_table_header($lang['template_add'], 2).
                        $table -> simple_input_row_text($form, $lang['template_add_name'], "name", "").
                        $table -> simple_input_row_text($form, $lang['template_add_function_name'], "function_name", "").
                        $table -> simple_input_row_text($form, $lang['template_add_parameters'], "parameters", "").
                        $table -> simple_input_row_text($form, $lang['template_add_class_name'], "class_name", "").
                        $table -> simple_input_row_textbox($form, $lang['template_add_text'], "text", "", 4).
                        $table -> add_submit_row($form).
                        $table -> end_table().
                        $form -> end_form()
                );        
                                
        }
                        
}


//***********************************************
// Editing an individual template
//***********************************************
function page_edit_template()
{

        global $output, $lang, $db, $template_admin;

        $get_id = $_GET['id'];

        // ----------------
        // Grab the template
        // ----------------
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_id']));
                page_main();
                return;
        }
                
        // Grab wanted template
        $template = $db -> query("select name, set_id, class_name, text, parameters from ".$db -> table_prefix."templates where id='".$get_id."'");
        $template_array = $db -> fetch_array($template);
        
        // Die if it doesn't exist
        if($db -> num_rows($template) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_id']));
                page_main();
                return;
        }


        // ----------------
        // Can we edit it?
        // ----------------
        $template_file_path = ROOT."templates/template_id".$template_array['set_id']."/".$template_array['class_name'].".php";

        // Check if we can write to the template
        if(!is_writable($template_file_path))
        {
                $output -> add($template_admin -> critical_error($lang['error_template_not_writable']."<br />(<b>".$template_file_path."</b>)"));
                page_main();
                return;
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['edit_template_title'].$template_array['name'];

        $output -> add_breadcrumb($lang['breadcrumb_templates_list'], "index.php?m=templates&amp;m2=showtemplates&amp;id=".$template_array['set_id']."&amp;open_set=".$template_array['class_name']);
        $output -> add_breadcrumb($lang['breadcrumb_edit_template'], "index.php?m=templates&amp;m2=edittemplate&amp;id=".$get_id);

        // ----------------
        // SHOW FORM ITSELF
        // ----------------
        $table = new table_generate;
        $form = new form_generate;
        $output -> add(
                $form -> start_form("edittemplate", ROOT."admin/index.php?m=templates&amp;m2=doedittemplate&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['edit_template_title'].$template_array['name'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['edit_template_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Form
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['edit_template_name'], "20%"),
                                array($form -> input_text("name", $template_array['name']), "80%")
                        )
                ,"normalcell", "padding : 5px").
                $table -> add_basic_row($lang['edit_template_full_text'], "strip2", "", "left", "100%", "2").
                $table -> add_basic_row($form -> input_textbox("text", _htmlspecialchars($template_array['text']), 20, "inputtext", "99%"), "normalcell", "", "center", "100%", "2").
                // ---------------
                // Show Params
                // ---------------
                $table -> add_basic_row(
                        "<a href=\"index.php?m=templates&amp;m2=previewtemplate&amp;id=".$get_id."\" title=\"".$lang['preview_template_button']."\" target=\"_blank\"><img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-preview.png\"></a>".
                        " (".$lang['edit_template_params']." <b>".$template_array['parameters']."</b>)"
                , "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // End Form
                // ---------------
                $table -> add_basic_row($form -> submit("submit", $lang['admin_edittemplate_submit']), "strip3",  "padding : 5px").
                $table -> end_table().
                $form -> end_form()
        );

}


//***********************************************
// Do the editing of an individual template
//***********************************************
function do_edit_template()
{

        global $output, $lang, $db, $template_admin;

        $get_id = $_GET['id'];

        // ----------------
        // Grab the template
        // ----------------
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_id']));
                page_main();
                return;
        }
                
        // Grab wanted template
        $template = $db -> query("select name, set_id, class_name, text, parameters from ".$db -> table_prefix."templates where id='".$get_id."'");
        $template_array = $db -> fetch_array($template);
        
        // Die if it doesn't exist
        if($db -> num_rows($template) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_id']));
                page_main();
                return;
        }


        // ----------------
        // Can we edit it?
        // ----------------
        $template_file_path = ROOT."templates/template_id".$template_array['set_id']."/".$template_array['class_name'].".php";

        // Check if we can write to the template
        if(!is_writable($template_file_path))
        {
                $output -> add($template_admin -> critical_error($lang['error_template_not_writable']."<br />(<b>".$template_file_path."</b>)"));
                page_main();
                return;
        }


        // ----------------
        // Sort Database stuff
        // ----------------
        if(trim($_POST['name']) == "")
                $temp_name = $template_array['name'];
        else
                $temp_name = trim($_POST['name']);

        $info = array(
                "text" => trim($_POST['text']),
                "name" => $temp_name
        );
        
        // Execute it
        if(!$db -> basic_update("templates", $info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_template_sql']));
                page_edit_template();
                return;
        }
        

        // ----------------
        // Write the file
        // ----------------
        $fh = fopen($template_file_path, "w");

        if(fwrite($fh, build_template_files($template_array['set_id'], $template_array['class_name'], false)) == FALSE)
        {
                fclose($fh);
                $output -> add($template_admin -> critical_error($lang['error_updating_template_file']));
                page_edit_template();
                return;
        } 

        fclose($fh);

        // Log it!
        log_admin_action("templates", "doedittemplate", "Edited a template: ".$template_array['name']);
        
        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=templates&m2=edittemplate&id=".$get_id, $lang['template_updated_sucessfully']);

}


//***********************************************
// Template preview
//***********************************************
function page_preview_template()
{

        global $output, $lang, $db, $template_admin;

        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_id']));
                page_show_templates();
                return;
        }
                
        // Grab wanted template
        $template = $db -> query("select text from ".$db -> table_prefix."templates where id='".$get_id."'");
        $template_array = $db -> fetch_array($template);
        
        // Die if it doesn't exist
        if($db -> num_rows($template) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_id']));
                page_show_templates();
                return;
        }
        
        die($template_array['text']);

}


//***********************************************
// Rebuild one or more of the php language files
//***********************************************
function do_rebuild_file()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Grab the set
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }
                
        // Grab wanted template set
        $template_set = $db -> query("select * from ".$db -> table_prefix."template_sets where id='".$get_id."'");
        $template_array = $db -> fetch_array($template_set);
        
        // Die if it doesn't exist
        if($db -> num_rows($template_set) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_template_set_id']));
                page_main();
                return;
        }

        // ----------------
        // Check the directory
        // ----------------
        if(!is_dir(ROOT."templates/template_id".$get_id))
        {
                if(!mkdir(ROOT."templates/template_id".$get_id, 0777))
                {
                        $output -> add($template_admin -> critical_error($lang['template_set_dir_error']));
                        page_main();
                        return;
                }
        }

        // ----------------
        // Build files
        // ----------------
        if($_GET['group'] == "ALL")
        {
                build_template_files($get_id);
                // Redirect the user
                $output -> redirect(ROOT."admin/index.php?m=templates", $lang['file_rebuilt']);
        }
        else
        {
                build_template_files($get_id, $_GET['group']);
                // Redirect the user
                $output -> redirect(ROOT."admin/index.php?m=templates&amp;m2=showtemplates&amp;id=".$get_id, $lang['file_rebuilt']);
        }

}


//***********************************************
// Show the import/export page
//***********************************************
function page_import_export()
{

        global $output, $lang, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_menu_export_templates'];

        $output -> add_breadcrumb($lang['breadcrumb_templates_importexport'], "index.php?m=templates&amp;m2=importexport");

        // Create classes
        $table = new table_generate;
        $form = new form_generate;


        // Grab all template sets
        $template_sets = $db -> query("select id,name from ".$db -> table_prefix."template_sets order by name");
        
        $sets_dropdown[] .= "-1";
        $sets_dropdown_text[] .= $lang['all_sets_dropdown'];
        
        // Go through all sets
        while($template_array = $db -> fetch_array($template_sets))
        {
                // Add to dropdown arrays
                $sets_dropdown[] .= $template_array['id'];
                $sets_dropdown_text[] .= $template_array['name'];
        }
        
    
        // ----------------
        // EXPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("exporttemplates", ROOT."admin/index.php?m=templates&amp;m2=doexport", "post", false, true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['export_templates_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['export_templates_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Export form
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['export_filename']."<br /><font class=\"small_text\">".$lang['export_filename_message']."</font>","50%"),
                                array($form -> input_text("filename", "fsboard-templates.xml"),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['export_which_template']."<br /><font class=\"small_text\">".$lang['export_which_template_message']."</font>","50%"),
                                array($form -> input_dropdown("set", "", $sets_dropdown, $sets_dropdown_text),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['export_templates']).$form->reset("reset", $lang['export_templates_reset']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

        // ----------------
        // IMPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("importtemplates", ROOT."admin/index.php?m=templates&amp;m2=doimport", "post", true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['import_templates_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['import_templates_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
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
                                array($form -> input_text("filename", "includes/fsboard-templates.xml"),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['import_templates']).$form->reset("reset", $lang['import_templates_reset']), "strip3",  "", "center", "100%", "2").
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


        // *************************
        // Select the set(s)
        // *************************
        if($_POST['set'] > "-1")
                $single_id = 'where id = "'.$_POST['set'].'"';
		else
                $single_id = "";
                
        $select_template_sets = $db -> query("select * from ".$db -> table_prefix."template_sets ".$single_id);

        if($db -> num_rows($select_template_sets) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['getting_template_sets_error']));
                page_import_export();
                return;
        }
 
 
        // *************************
        // Start XML'ing
        // *************************
        $xml = new xml;
        $xml -> export_xml_start();
        $xml -> export_xml_root("template_set_file");

        // *************************
        // Spin through sets
        // *************************
        while($template_set_array = $db -> fetch_array($select_template_sets))
        {
        
                // *************************
                // Start off the group
                // *************************
                $xml -> export_xml_start_group(
                        "template_set",
                        array(
                                "name" => $template_set_array['name'],
                                "can_change_theme" => $template_set_array['can_change_theme'],
                                "author" => $template_set_array['author'],
                                "default_theme" => $template_set_array['default_theme']
                        )
                );

                // *************************
                // Select the templates in this group
                // *************************
                $select_templates = $db -> query("select * from ".$db -> table_prefix."templates where set_id = \"".$template_set_array['id']."\" order by class_name,name");

                if($db -> num_rows($select_templates) > 0)
                {

                        while($template_array = $db -> fetch_array($select_templates))
                        {

                                // Add the template entry
                                $xml -> export_xml_add_group_entry(
                                        "template",
                                        array(
                                                "name" => $template_array['name'],
                                                "class_name" => $template_array['class_name'],
                                                "function_name" => $template_array['function_name']
                                        ),
                                        $template_array['text']
                                );
                                
                                // Add the parameters entry
                                $xml -> export_xml_add_group_entry(
                                        "template_parameters",
                                        "",
                                        $template_array['parameters']
                                );

                        }
                        
                }
                                                                                          
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
                $filename = "fsboard-templates.xml";
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

        global $output, $lang, $db, $template_admin;

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
        $get_error = import_templates_xml($xml_contents);

        // If we have version mismatch
        if((string)$get_error == "VERSION")
        {
                $output -> add($template_admin -> critical_error($lang['xml_version_mismatch']));
                return false;
        }
                
        $output -> add($template_admin -> message($lang['import_done_title'], $lang['import_done_message']));

}


//***********************************************
// Add a new template please
//***********************************************
function do_new_template()
{
        
        global $output, $lang, $db, $template_admin;

        if(!defined("DEVELOPER"))
        {
                page_main();
                return;
        }
        
        $input = array(
                        "name"                => $_POST['name'],
                        "function_name"        => $_POST['function_name'],
                        "parameters"        => $_POST['parameters'],
                        "class_name"        => $_POST['class_name'],
                        "text"                => $_POST['text'],
                        "set_id"        => $_GET['id']
                );

        $input = array_map("trim", $input);

        // ******************
        // Empty?
        // ******************
        if($input['name'] == "" || $input['function_name'] == "" || $input['class_name'] == "")
        {
                $output -> add($template_admin -> normal_error($lang['template_add_error_input']));
                page_show_templates();
                return;
        }                


        // ******************
        // Check template set...
        // ******************
        if($db -> query_check_id_rows("template_sets", $input['set_id'], "id") < 1)
        {
                $output -> add($template_admin -> critical_error($lang['template_add_set_error']));
                page_main();
                return;
        }
        
        // ******************
        // Create the template
        // ******************
        if(!$db -> basic_insert("templates", $input))
        {
                $output -> add($template_admin -> normal_error($lang['template_add_error']));
                page_show_templates();
                return;
        }

        build_template_files($input['set_id'], $input['class_name']);
                
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=templates&amp;m2=showtemplates&amp;id=".$input['set_id']."&amp;open_set=".$input['class_name'], $lang['template_add_done']);
                 
}

?>
