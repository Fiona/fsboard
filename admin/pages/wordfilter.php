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
*       Word Filter	        *
*       Started by Fiona        *
*       19th Jan 2007           *
*********************************
*       Last edit by Fiona      *
*       19th Jan 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// These words don't get filtered! giggle..'
//***********************************************
load_language_group("admin_wordfilter");


$output -> add_breadcrumb($lang['breadcrumb_wordfilter'], "index.php?m=wordfilter");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_wordfilter(true);
                break;
                
        case "doadd":
                do_add_wordfilter();
                break;

        case "edit":
                page_add_edit_wordfilter();
                break;
                
        case "doedit":
                do_edit_wordfilter();
                break;
               
        case "delete":
                do_delete_wordfilter();
                break;
               
        default:
                page_main();

}


//***********************************************
// Word filter listing
//***********************************************
function page_main()
{

        global $lang, $output, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['wordfilter_main_title'];


        // ********************
        // Start table
        // ********************
        // Create class
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['wordfilter_main_title'], "strip1",  "", "left", "100%", "3").
                $table -> add_basic_row($lang['wordfilter_main_message'], "normalcell",  "padding : 5px", "left", "100%", "3").
                
                $table -> add_row(
                        array(
                                array($lang['wordfilter_main_word'], "auto"),
                                array($lang['wordfilter_main_replacement'], "auto"),
                                array($lang['wordfilter_main_actions'], "auto")
                        )
                , "strip2")
        );


        // ********************
        // Grab all filters
        // ********************
        $db -> basic_select("wordfilter", "*", "", "word", "", "asc");

        // No filters?
        if($db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_filters']."</b>", "normalcell",  "padding : 10px")
                );        
                
        else
        {

                while($w_array = $db-> fetch_array())
                {

                        $actions = "
                       <a href=\"".ROOT."admin/index.php?m=wordfilter&amp;m2=edit&amp;id=".$w_array['id']."\" title=\"".$lang['wordfilter_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=wordfilter&amp;m2=delete&amp;id=".$w_array['id']."\" onclick=\"return confirm('".$lang['delete_wordfilter_confirm']."')\" title=\"".$lang['wordfilter_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($w_array['word'], "auto"),
                                                array($w_array['replacement'], "auto"),
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
                        $form -> button("addfilter", $lang['add_wordfilter_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=wordfilter&m2=add';\"")
                , "strip3").
                $table -> end_table().
                $form -> end_form()
        );
                
}



//***********************************************
// Form for adding or editing wordfilter
//***********************************************
function page_add_edit_wordfilter($adding = false, $wordfilter_info = "")
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
                $output -> page_title = $lang['add_wordfilter_title'];

		$output -> add_breadcrumb($lang['breadcrumb_wordfilter_add'], "index.php?m=wordfilter&m2=add");

                $output -> add(
                        $form -> start_form("addfilter", ROOT."admin/index.php?m=wordfilter&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['add_wordfilter_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_wordfilter_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_wordfilter_submit'];
                
        }
        else
        {

	        // **************************
	        // Grab the filter
	        // **************************
	        $get_id = trim($_GET['id']);
	
	        if(!$db -> query_check_id_rows("wordfilter", $get_id, "*"))
	        {
	                $output -> add($template_admin -> critical_error($lang['invalid_wordfilter_id']));
	                page_main();
	                return;
	        }
	  
	        if(!$wordfilter_info)
	                $wordfilter_info = $db -> fetch_array();

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_wordfilter_title'];

		$output -> add_breadcrumb($lang['breadcrumb_wordfilter_edit'], "index.php?m=wordfilter&m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("editfilter", ROOT."admin/index.php?m=wordfilter&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['edit_wordfilter_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_wordfilter_submit'];
                                
        }

        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> simple_input_row_text($form, $lang['add_wordfilter_word'], "word", $wordfilter_info['word']).
                $table -> simple_input_row_text($form, $lang['add_wordfilter_replacement'], "replacement", $wordfilter_info['replacement']).
                $table -> simple_input_row_yesno($form, $lang['add_wordfilter_perfect']."<br /><font class=\"small_text\">".$lang['add_wordfilter_perfect_desc']."</font>", "perfect_match", $wordfilter_info['perfect_match']).
                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );   
        
} 


//***********************************************
// Add the filter
//***********************************************
function do_add_wordfilter()
{

        global $output, $lang, $db, $template_admin, $cache;


        // **********************
        // Get stuff from the post
        // **********************
        $wordfilter_info = array(
                "word"		=> $_POST['word'],
                "replacement"	=> $_POST['replacement'],
                "perfect_match"	=> $_POST['perfect_match']
        );

        // ***************************
        // Check there's something in the word and replacement
        // ***************************
        if(trim($wordfilter_info['word']) == "" || trim($wordfilter_info['replacement']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_wordfilter_fill_in_all']));
                page_add_edit_wordfilter(true, $wordfilter_info);
                return;
        }               

        // *********************
        // Check tag doesn't already exist
        // *********************
        $db -> basic_select("wordfilter", "id", "word = '".$wordfilter_info['replacement']."'");

        // Die if it does
        if($db -> num_rows() > 0)
        {
                $output -> add($template_admin -> normal_error($lang['add_wordfilter_word_already_exists']));
                page_add_edit_wordfilter(true, $wordfilter_info);
                return;
        }

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("wordfilter", $wordfilter_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_wordfilter_insert_error']));
                page_add_edit_wordfilter(true, $wordfilter_info);
                return;
        }               

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("wordfilter");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("wordfilter", "doadd", "Added word filter: ".$wordfilter_info['word']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=wordfilter", $lang['wordfilter_created_sucessfully']);
        
}
      
      
//***********************************************
// Finish editing the filter
//***********************************************
function do_edit_wordfilter()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the filter
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("wordfilter", $get_id, "*"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_wordfilter_id']));
                page_main();
                return;
        }

        // **********************
        // Get stuff from the post
        // **********************
        $wordfilter_info = array(
                "word"		=> $_POST['word'],
                "replacement"	=> $_POST['replacement'],
                "perfect_match"	=> $_POST['perfect_match']
        );

        // ***************************
        // Check there's something in the word and replacement
        // ***************************
        if(trim($wordfilter_info['word']) == "" || trim($wordfilter_info['replacement']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_wordfilter_fill_in_all']));
                page_add_edit_wordfilter(false, $wordfilter_info);
                return;
        }               

        // *********************
        // Check tag doesn't already exist
        // *********************
        $db -> basic_select("wordfilter", "id", "word = '".$wordfilter_info['replacement']."' and id <> '".$get_id."'");

        // Die if it does
        if($db -> num_rows() > 0)
        {
                $output -> add($template_admin -> normal_error($lang['add_wordfilter_word_already_exists']));
                page_add_edit_wordfilter(false, $wordfilter_info);
                return;
        }

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("wordfilter", $wordfilter_info, "id = '".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_editing_wordfilter']));
                page_main();
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("wordfilter");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("wordfilter", "doedit", "Edited word filter: ".$wordfilter_info['word']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=wordfilter", $lang['wordfilter_edited_sucessfully']);
              
}     


//***********************************************
// Getting rid of a filter
//***********************************************
function do_delete_wordfilter()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the filter
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("wordfilter", $get_id, "id,word"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_wordfilter_id']));
                page_main();
                return;
        }
        
        $wordfilter_info = $db -> fetch_array();

        // ********************
        // Delete it
        // ********************
        $db -> basic_delete("wordfilter", "id = '".$get_id."'");

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("wordfilter");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("wordfilter", "delete", "Deleted word filter: ".$wordfilter_info['word']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=wordfilter", $lang['wordfilter_deleted_sucessfully']);

}  

?>
