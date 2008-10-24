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
*       Reputation Titles       *
*       Started by Fiona        *
*       22nd Jan 2007           *
*********************************
*       Last edit by Fiona      *
*       22nd Jan 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// include.. include this... oh please include this
//***********************************************
load_language_group("admin_reptutations");


$output -> add_breadcrumb($lang['breadcrumb_reputations'], "index.php?m=reputations");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_reputations(true);
                break;

        case "doadd":
                do_add_reputations();
                break;
               
        case "edit":
                page_add_edit_reputations();
                break;

        case "doedit":
                do_edit_reputations();
                break;
               
        case "delete":
                do_delete_reputations();
                break;
               
        default:
                page_main();

}



//***********************************************
// Check out dese titles
//***********************************************
function page_main()
{

        global $lang, $output, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['reputations_main_title'];

        // ********************
        // Start table
        // ********************
        // Create class
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['reputations_main_title'], "strip1",  "", "left", "100%", "3").
                $table -> add_basic_row($lang['reputations_main_message'], "normalcell",  "padding : 5px", "left", "100%", "3").
                
                $table -> add_row(
                        array(
                                array($lang['reputations_main_min_rep'], "auto"),
                                array($lang['reputations_main_name'], "auto"),
                                array($lang['reputations_main_actions'], "auto")
                        )
                , "strip2")
        );

        // ********************
        // Grab all reps
        // ********************
        $db -> basic_select("user_reputations", "*", "", "min_rep", "", "asc");

        // No titles?
        if($db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_reputation']."</b>", "normalcell",  "padding : 10px")
                );        
                
        else
        {

                while($r_array = $db-> fetch_array())
                {

                        $actions = "
                       <a href=\"".ROOT."admin/index.php?m=reputations&amp;m2=edit&amp;id=".$r_array['id']."\" title=\"".$lang['reputations_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=reputations&amp;m2=delete&amp;id=".$r_array['id']."\" onclick=\"return confirm('".$lang['delete_reputations_confirm']."')\" title=\"".$lang['reputations_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($r_array['min_rep'], "auto"),
                                                array("<b>".$r_array['name']."</b>", "auto"),
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
                        $form -> button("addreputation", $lang['add_reputations_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=reputations&m2=add';\"")
                , "strip3").
                $table -> end_table().
                $form -> end_form()
        );        

}


//***********************************************
// Form for adding or editing reputation titles
//***********************************************
function page_add_edit_reputations($adding = false, $reputations_info = "")
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
                $output -> page_title = $lang['add_reputations_title'];

		$output -> add_breadcrumb($lang['breadcrumb_reputations_add'], "index.php?m=reputations&m2=add");

                $output -> add(
                        $form -> start_form("addreputations", ROOT."admin/index.php?m=reputations&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['add_reputations_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_reputations_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_reputations_submit'];

        }
        else
        {
        	
	        // **************************
	        // Grab the reputation title
	        // **************************
	        $get_id = trim($_GET['id']);
	
	        if(!$db -> query_check_id_rows("user_reputations", $get_id, "*"))
	        {
	                $output -> add($template_admin -> critical_error($lang['edit_reputations_invalid_id']));
	                page_main();
	                return;
	        }
	  
	        if(!$reputations_info)
	                $reputations_info = $db -> fetch_array();

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_reputations_title'];

		$output -> add_breadcrumb($lang['breadcrumb_reputations_edit'], "index.php?m=reputations&m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("editreputations", ROOT."admin/index.php?m=reputations&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['edit_reputations_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_reputations_submit'];

        }

        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> simple_input_row_text($form, $lang['add_reputations_name'], "name", $reputations_info['name']).
                $table -> simple_input_row_int($form, $lang['add_reputations_min_rep'], "min_rep", $reputations_info['min_rep']).
                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );   
        
}    


//***********************************************
// Add the reputation title
//***********************************************
function do_add_reputations()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Get stuff from the post
        // **********************
        $reputations_info = array(
                "name"		=> $_POST['name'],
                "min_rep"	=> $_POST['min_rep']
        );

        // ***************************
        // Check there's something in the name
        // ***************************
        if(trim($reputations_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_reputations_fill_in_title']));
                page_add_edit_reputations(true, $reputations_info);
                return;
        }               

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("user_reputations", $reputations_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_reputations_insert_error']));
                page_add_edit_reputations(true, $reputations_info);
                return;
        }               

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_reputations");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("titles", "doadd", "Added reputations title: ".$reputations_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=reputations", $lang['add_reputations_created_successfully']);

}
    
    
//***********************************************
// Edit the reputation title
//***********************************************
function do_edit_reputations()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the reputation title
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("user_reputations", $get_id, "*"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_reputations_invalid_id']));
                page_main();
                return;
        }

        // **********************
        // Get stuff from the post
        // **********************
        $reputations_info = array(
                "name"		=> $_POST['name'],
                "min_rep"	=> $_POST['min_rep']
        );

        // ***************************
        // Check there's something in the name
        // ***************************
        if(trim($reputations_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['edit_reputations_fill_in_title']));
                page_add_edit_reputations(false, $reputations_info);
                return;
        }                 

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("user_reputations", $reputations_info, "id = '".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['edit_reputations_error_editing']));
                page_main();
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_reputations");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("reputations", "doedit", "Edited reputation title: ".$reputations_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=reputations", $lang['edit_reputations_edited_successfully']);

}



//***********************************************
// Getting rid of a reputation title
//***********************************************
function do_delete_reputations()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the reputation title
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("user_reputations", $get_id, "*"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_reputations_invalid_id']));
                page_main();
                return;
        }
        
        $reputations_info = $db -> fetch_array();

        // ********************
        // Delete it
        // ********************
        $db -> basic_delete("user_reputations", "id = '".$get_id."'");

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_reputations");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("reputations", "delete", "Deleted reputation title: ".$reputations_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=reputations", $lang['delete_reputations_deleted_successfully']);

}  

    
?>
