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
*       User Titles	        *
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
// lmbo
//***********************************************
load_language_group("admin_titles");


$output -> add_breadcrumb($lang['breadcrumb_titles'], "index.php?m=titles");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_title(true);
                break;

        case "doadd":
                do_add_title();
                break;
               
        case "edit":
                page_add_edit_title();
                break;

        case "doedit":
                do_edit_title();
                break;
               
        case "delete":
                do_delete_title();
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
        $output -> page_title = $lang['titles_main_title'];

        // ********************
        // Start table
        // ********************
        // Create class
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['titles_main_title'], "strip1",  "", "left", "100%", "3").
                $table -> add_basic_row($lang['titles_main_message'], "normalcell",  "padding : 5px", "left", "100%", "3").
                
                $table -> add_row(
                        array(
                                array($lang['titles_main_name'], "auto"),
                                array($lang['titles_main_posts'], "auto"),
                                array($lang['titles_main_actions'], "auto")
                        )
                , "strip2")
        );

        // ********************
        // Grab all titles
        // ********************
        $db -> basic_select("user_titles", "*", "", "min_posts", "", "asc");

        // No titles?
        if($db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_titles']."</b>", "normalcell",  "padding : 10px")
                );        
                
        else
        {
        	
                while($t_array = $db-> fetch_array())
                {

                        $actions = "
                       <a href=\"".ROOT."admin/index.php?m=titles&amp;m2=edit&amp;id=".$t_array['id']."\" title=\"".$lang['titles_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=titles&amp;m2=delete&amp;id=".$t_array['id']."\" onclick=\"return confirm('".$lang['delete_titles_confirm']."')\" title=\"".$lang['titles_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array("<b>".$t_array['title']."</b>", "auto"),
                                                array($t_array['min_posts'], "auto"),
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
                        $form -> button("addtitle", $lang['add_title_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=titles&m2=add';\"")
                , "strip3").
                $table -> end_table().
                $form -> end_form()
        );        
        
}


//***********************************************
// Form for adding or editing user titles
//***********************************************
function page_add_edit_title($adding = false, $title_info = "")
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
                $output -> page_title = $lang['add_titles_title'];

		$output -> add_breadcrumb($lang['breadcrumb_titles_add'], "index.php?m=titles&m2=add");

                $output -> add(
                        $form -> start_form("addtitle", ROOT."admin/index.php?m=titles&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['add_titles_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_titles_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_titles_submit'];

        }
        else
        {

	        // **************************
	        // Grab the title
	        // **************************
	        $get_id = trim($_GET['id']);
	
	        if(!$db -> query_check_id_rows("user_titles", $get_id, "*"))
	        {
	                $output -> add($template_admin -> critical_error($lang['edit_titles_invalid_id']));
	                page_main();
	                return;
	        }
	  
	        if(!$title_info)
	                $title_info = $db -> fetch_array();

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_titles_title'];

		$output -> add_breadcrumb($lang['breadcrumb_titles_edit'], "index.php?m=titles&m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("edittitle", ROOT."admin/index.php?m=titles&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['edit_titles_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_titles_submit'];
        	
        }

        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> simple_input_row_text($form, $lang['add_titles_name'], "title", $title_info['title']).
                $table -> simple_input_row_int($form, $lang['add_titles_min_posts'], "min_posts", $title_info['min_posts']).
                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );   
        
}


//***********************************************
// Add the user title
//***********************************************
function do_add_title()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Get stuff from the post
        // **********************
        $title_info = array(
                "title"		=> $_POST['title'],
                "min_posts"	=> $_POST['min_posts']
        );

        // ***************************
        // Check there's something in the title
        // ***************************
        if(trim($title_info['title']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_titles_fill_in_title']));
                page_add_edit_title(true, $title_info);
                return;
        }               

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("user_titles", $title_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_titles_insert_error']));
                page_add_edit_title(true, $title_info);
                return;
        }               

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_titles");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("titles", "doadd", "Added user title: ".$title_info['title']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=titles", $lang['add_titles_created_sucessfully']);

}


//***********************************************
// Edit the user title
//***********************************************
function do_edit_title()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the title
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("user_titles", $get_id, "*"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_titles_invalid_id']));
                page_main();
                return;
        }

        // **********************
        // Get stuff from the post
        // **********************
        $title_info = array(
                "title"		=> $_POST['title'],
                "min_posts"	=> $_POST['min_posts']
        );

        // ***************************
        // Check there's something in the title
        // ***************************
        if(trim($title_info['title']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_titles_fill_in_title']));
                page_add_edit_title(false, $title_info);
                return;
        }               

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("user_titles", $title_info, "id = '".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['edit_titles_error_editing']));
                page_main();
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_titles");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("titles", "doedit", "Edited user title: ".$title_info['title']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=titles", $lang['edit_titles_edited_sucessfully']);

}


//***********************************************
// Getting rid of a user title
//***********************************************
function do_delete_title()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the title
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("user_titles", $get_id, "id,title"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_titles_invalid_id']));
                page_main();
                return;
        }
        
        $title_info = $db -> fetch_array();

        // ********************
        // Delete it
        // ********************
        $db -> basic_delete("user_titles", "id = '".$get_id."'");

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("user_titles");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("titles", "delete", "Deleted user title: ".$title_info['title']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=titles", $lang['delete_titles_deleted_sucessfully']);

}  

?>
