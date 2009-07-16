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
 * Admin page for post insignia
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
// oh no you di'nt'
//***********************************************
load_language_group("admin_insignia");


$output -> add_breadcrumb($lang['breadcrumb_insignia'], "index.php?m=insignia");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_insignia(true);
                break;

        case "doadd":
                do_add_insignia();
                break;
               
        case "edit":
                page_add_edit_insignia();
                break;

        case "doedit":
                do_edit_insignia();
                break;
               
        case "delete":
                do_delete_insignia();
                break;
                               
        default:
                page_main();

}



//***********************************************
// Pretty insignia
//***********************************************
function page_main()
{

        global $lang, $output, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['insignia_main_title'];

        // ********************
        // Start table
        // ********************
        // Create class
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['insignia_main_title'], "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['insignia_main_message'], "normalcell",  "padding : 5px", "left", "100%", "4").
                
                $table -> add_row(
                        array(
                                array($lang['insignia_main_insignia'], "auto"),
                                array($lang['insignia_main_posts'], "auto"),
                                array($lang['insignia_main_newline'], "auto"),
                                array($lang['insignia_main_actions'], "auto")
                        )
                , "strip2")
        );
        
        // ********************
        // Grab all insignia
        // ********************
        $insignia_q = $db -> basic_select("user_insignia", "*", "", "user_group, min_posts", "", "asc");

        // No insignia?
        if($db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_insignia']."</b>", "normalcell",  "padding : 10px")
                );        
                
        else
        {

		// Fetch user groups
		$groups_array = array();
		
		$db -> basic_select("user_groups", "id, name", "", "id");
	
		while($g = $db -> fetch_array())
			$groups_array[$g['id']] = $g['name'];

		$current_user_group = false;
		
                while($i_array = $db-> fetch_array($insignia_q))
                {
			
			// User group title
			if($current_user_group === false || $current_user_group != $i_array['user_group'])
				if($i_array['user_group'] == "-1")
					$output -> add(				
			                        $table -> add_basic_row("<b>".$lang['insignia_main_all_groups']."</b>", "normalcell",  "padding : 5px")
                			);
				else
					$output -> add(				
			                        $table -> add_basic_row("<b>".$groups_array[$i_array['user_group']]."</b>", "normalcell",  "padding : 5px")
                			);
			
			// Actions
                        $actions = "
                        <a href=\"".ROOT."admin/index.php?m=insignia&amp;m2=edit&amp;id=".$i_array['id']."\" title=\"".$lang['insignia_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=insignia&amp;m2=delete&amp;id=".$i_array['id']."\" onclick=\"return confirm('".$lang['delete_insignia_confirm']."')\" title=\"".$lang['insignia_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

			// Insignia text
			if($i_array['text'])
				$insignia_1 = $i_array['text'];
			else
				$insignia_1 = "<img src=\"".ROOT.$i_array['image']."\" />";

			$insignia = "";
			
			if($i_array['repeat_no'] > 0)
				for($a = 1; $a <= $i_array['repeat_no']; $a++)
					$insignia .= $insignia_1;
			else
				$insignia = "&nbsp;";
						
			// Row
                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($insignia, "auto"),
                                                array($i_array['min_posts'], "auto"),
                                                array(
                                                	($i_array['newline']) ? $lang['yes'] : $lang['no']
                                                , "auto"),
                                                array($actions, "auto")
                                        )
                                , "normalcell")
                        );
                	
                	// Save group
                	$current_user_group = $i_array['user_group'];
                	
                }
                
        }     
           
        // ********************
        // End table
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> button("addtitle", $lang['add_insignia_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=insignia&m2=add';\"")
                , "strip3").
                $table -> end_table().
                $form -> end_form()
        );        
        
}


//***********************************************
// Form for adding or editing insignia
//***********************************************
function page_add_edit_insignia($adding = false, $insignia_info = "")
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
                $output -> page_title = $lang['add_insignia_title'];

		$output -> add_breadcrumb($lang['breadcrumb_insignia_add'], "index.php?m=insignia&m2=add");

                $output -> add(
                        $form -> start_form("addinsignia", ROOT."admin/index.php?m=insignia&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['add_insignia_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_insignia_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_insignia_submit'];
                
                if($insignia_info == "")
                	$insignia_info['repeat_no'] = "1";

        }
        else
        {

	        // **************************
	        // Grab the insignia
	        // **************************
	        $get_id = trim($_GET['id']);
	
	        if(!$db -> query_check_id_rows("user_insignia", $get_id, "*"))
	        {
	                $output -> add($template_admin -> critical_error($lang['edit_insignia_invalid_id']));
	                page_main();
	                return;
	        }
	  
	        if(!$insignia_info)
	                $insignia_info = $db -> fetch_array();

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_insignia_title'];

		$output -> add_breadcrumb($lang['breadcrumb_insignia_edit'], "index.php?m=insignia&m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("editinsignia", ROOT."admin/index.php?m=insignia&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['edit_insignia_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_insignia_submit'];

        }

	// Fetch user groups
	$groups_dropdown_text = array();
	$groups_dropdown_values = array();
	
	$db -> basic_select("user_groups", "id, name", "", "id");

	$groups_dropdown_text[] = $lang['add_insignia_groups_dropdown_all'];
	$groups_dropdown_values[] = "-1";
	
	while($g = $db -> fetch_array())
	{
		$groups_dropdown_text[] = $g['name'];
		$groups_dropdown_values[] = $g['id'];
	}
	
        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> simple_input_row_int($form, $lang['add_insignia_min_posts'], "min_posts", $insignia_info['min_posts']).
                $table -> simple_input_row_dropdown($form, $lang['add_insignia_user_group'], "user_group", $insignia_info['user_group'], $groups_dropdown_values, $groups_dropdown_text).
                $table -> simple_input_row_yesno($form, $lang['add_insignia_newline'], "newline", $insignia_info['newline']).
                $table -> simple_input_row_int($form, $lang['add_insignia_repeat_no'], "repeat_no", $insignia_info['repeat_no']).
                $table -> simple_input_row_text($form, $lang['add_insignia_image'], "image", $insignia_info['image']).
                $table -> simple_input_row_text($form, $lang['add_insignia_text'], "text", $insignia_info['text']).
                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );   
        
}


//***********************************************
// Add the post insignia
//***********************************************
function do_add_insignia()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Get stuff from the post
        // **********************
        $insignia_info = array(
                "min_posts"	=> $_POST['min_posts'],
                "user_group"	=> $_POST['user_group'],
                "newline"	=> $_POST['newline'],
                "repeat_no"	=> $_POST['repeat_no'],
                "image"		=> $_POST['image'],
                "text"		=> $_POST['text']
        );

        // ***************************
        // Check there's something for the insignia
        // ***************************
        if(trim($insignia_info['image']) == "" && trim($insignia_info['text']) == "" )
        {
                $output -> add($template_admin -> normal_error($lang['add_insignia_fill_in_something']));
                page_add_edit_insignia(true, $insignia_info);
                return;
        }               

	if($insignia_info['text'])
		$insignia_info['image'] = NULL;
	else
		$insignia_info['text'] = NULL;
		
        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("user_insignia", $insignia_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_insignia_insert_error']));
                page_add_edit_title(true, $insignia_info);
                return;
        }               

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_insignia");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("insignia", "doadd", "Added post insignia");

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=insignia", $lang['add_insignia_created_sucessfully']);

}


//***********************************************
// Finish editing the post insignia
//***********************************************
function do_edit_insignia()
{

        global $output, $lang, $db, $template_admin, $cache;

	// **************************
	// Grab the insignia
	// **************************
	$get_id = trim($_GET['id']);
	
	if(!$db -> query_check_id_rows("user_insignia", $get_id, "*"))
	{
	        $output -> add($template_admin -> critical_error($lang['edit_insignia_invalid_id']));
	        page_main();
	        return;
	}

        // **********************
        // Get stuff from the post
        // **********************
        $insignia_info = array(
                "min_posts"	=> $_POST['min_posts'],
                "user_group"	=> $_POST['user_group'],
                "newline"	=> $_POST['newline'],
                "repeat_no"	=> $_POST['repeat_no'],
                "image"		=> $_POST['image'],
                "text"		=> $_POST['text']
        );

        // ***************************
        // Check there's something for the insignia
        // ***************************
        if(trim($insignia_info['image']) == "" && trim($insignia_info['text']) == "" )
        {
                $output -> add($template_admin -> normal_error($lang['add_insignia_fill_in_something']));
                page_add_edit_insignia(false, $insignia_info);
                return;
        }               

	if($insignia_info['text'])
		$insignia_info['image'] = NULL;
	else
		$insignia_info['text'] = NULL;

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("user_insignia", $insignia_info, "id = '".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['edit_insignia_error']));
                page_main();
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_insignia");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("insignia", "doedit", "Edited post insignia");

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=insignia", $lang['edit_insignia_edited_sucessfully']);
        
}


//***********************************************
// Delete a post insignia
//***********************************************
function do_delete_insignia()
{

        global $output, $lang, $db, $template_admin, $cache;

	// **************************
	// Grab the insignia
	// **************************
	$get_id = trim($_GET['id']);
	
	if(!$db -> query_check_id_rows("user_insignia", $get_id, "*"))
	{
	        $output -> add($template_admin -> critical_error($lang['edit_insignia_invalid_id']));
	        page_main();
	        return;
	}

        // ********************
        // Delete it
        // ********************
        $db -> basic_delete("user_insignia", "id = '".$get_id."'");

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_insignia");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("insignia", "delete", "Deleted post insignia.");

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=insignia", $lang['delete_insignia_deleted_sucessfully']);
	
}
	
?>
