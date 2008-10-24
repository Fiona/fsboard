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
*       Language Editor         *
*       Started by Fiona        *
*       15th Jan 2006           *
*********************************
*       Last edit by Fiona      *
*       26th Feb 2006           *
*********************************

*/

// TODO : Add language direction. 



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Language file! For the language manager?! CRAZY
//***********************************************
load_language_group("admin_languages");


//***********************************************
// Functions plzkthx
//***********************************************
include ROOT."admin/common/funcs/languages.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_languages'], "index.php?m=langs");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
        case "newlanguage":
                do_new_language();
                break;

        case "editlanguage":
                page_edit_language();
                break;

        case "doeditlanguage":
                do_edit_language();
                break;

        case "deletelanguage":
                do_delete_language();
                break;
        
        case "dodefault":
                do_default_language();
                break;

        case "viewgroup":
                page_language_groups();
                break;

        case "doupdatephrases";
                do_update_phrases();
                break;

        case "revert";
                do_revert_phrases();
                break;
        
        case "search";
                page_search_phrases();
                break;
        
        case "dosearch";
                do_search_phrases();
                break;
        
        case "editsingle";
                page_edit_single_phrase();
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
        case "newgroup":
		do_new_language_group();
		break;

        case "newphrase":
		do_new_language_phrase();
		break;
		                                
        default:
        
                page_main();
                
}

//***********************************************
// Main language view
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;


        // Grab all languages
        $languages = $db -> query("select * from ".$db -> table_prefix."languages order by name");

        // Get amount
        $languages_amount = $db -> num_rows($languages);

	// Default language is?
	$db -> basic_select("config", "value", "name='default_lang'");
	$default_lang = $db -> result();

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_languages_title'];
        
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // For dropdown
        $new_dropdown = array();
        $new_dropdown_text = array();

        // ----------------
        // LANGUAGES LIST
        // ----------------
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['admin_languages_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['admin_languages_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // List header
                // ---------------
                $table -> add_row(array($lang['languages_name'],$lang['languages_actions']), "strip2")
        );

        // Go through all sets
        while($language_array = $db -> fetch_array($languages))
        {
                $cell_1 = ""; $cell_2 = "";
                
                // If it's default show it!
                if($language_array['id'] == $default_lang)
                        $cell_1 .= "<img src=\"".IMGDIR."/default-icon.gif\" style=\"vertical-align:bottom;\" title=\"".$lang['default_language_blip']."\">";

                // Name and edit links 
                $cell_1 .= "<b>".$language_array['name']."</b>
                <a href=\"index.php?m=langs&amp;m2=editlanguage&amp;id=".$language_array['id']."\" title=\"".$lang['edit_language']."\">
                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>";

                // Only print the delete button if we're allowed
                if($languages_amount > 1 && $language_array['id'] != $default_lang)
                        $cell_1 .= " <a href=\"index.php?m=langs&amp;m2=deletelanguage&amp;id=".$language_array['id']."\" onclick=\"return confirm('".$lang['delete_language_confirm']."')\" title=\"".$lang['delete_language']."\">
                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

                // Second cell. View temp links.
                $cell_2 .= "[ <a href=\"index.php?m=langs&amp;m2=viewgroup&amp;id=".$language_array['id']."\">".$lang['edit_phrases']."</a>
                - <a href=\"".ROOT."admin/index.php?m=langs&amp;m2=rebuild&amp;group=ALL&amp;id=".$language_array['id']."\" onClick=\"return confirm('".$lang['languages_rebuild_all_confirm']."')\">".$lang['rebuild_all_files']."</a>";

                // If it's not defualt allow us to change it!
                if($language_array['id'] != $default_lang)
                        $cell_2 .= " - <a href=\"index.php?m=langs&amp;m2=dodefault&amp;id=".$language_array['id']."\">".$lang['set_default']."</a>";

                $cell_2 .= " ]";

                // Add to dropdown arrays
                $new_dropdown[] .= $language_array['id'];
                $new_dropdown_text[] .= $language_array['name'];
                
                $output -> add(
                        $table -> add_row(
                                array(
                                        array($cell_1, "40%"),
                                        array($cell_2, "60%")
                                )
                        , "normalcell")
                );

        }

        $output -> add($table -> end_table());
        
        // ----------------
        // NEW LANGUAGE
        // ----------------
        // Check if the language dir is writable
        if(!is_writable(ROOT."languages"))
        {
        
                $output -> add($template_admin -> critical_error($lang['languages_dir_not_writable']));
        
        }
        else        
                $output -> add(
                        $form -> start_form("newlanguage", ROOT."admin/index.php?m=langs&amp;m2=newlanguage", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['new_language'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['new_language_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                        // ---------------
                        // Input
                        // ---------------
                        $table -> add_row(
                                array(
                                        array($lang['new_language_name'], "50%"),
                                        array($form -> input_text("langname", ""), "50%")
                                )
                        , "normalcell").
                        $table -> add_row(
                                array(
                                        array($lang['new_language_inherit'], "50%"),
                                        array($form -> input_dropdown("langinherit", "", $new_dropdown, $new_dropdown_text), "50%")
                                )
                        , "normalcell").
                        // ---------------
                        // Submit
                        // ---------------
                        $table -> add_basic_row($form->submit("submit", $lang['new_language_submit']), "strip3",  "", "center", "100%", "2").
                        $table -> end_table().
                        $form -> end_form()                        
                );
                
}


//***********************************************
// Create a new lang
//***********************************************
function do_new_language()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ----------------
        // Collect inheritance first
        // ----------------
        $post_id = $_POST['langinherit'];

        // Grab wanted lang
        $language = $db -> query("select * from ".$db -> table_prefix."languages where id='".$post_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        } 

        $language_array = $db -> fetch_array($language);

        // ----------------
        // Try to create the language
        // ----------------
        $info = array(
                "name" => addslashes($_POST['langname']),
                "short_name" => $language_array['short_name'],
                "charset" => $language_array['charset'],
                "allow_user_select" => $language_array['allow_user_select'],
                "author" => $language_array['author'],
                "direction" => $language_array['strlen']
        );
        
        if(!$db -> basic_insert("languages", $info))
        {
                $output -> add($template_admin -> critical_error($lang['new_language_error']));
                page_main();
                return;
        }               

        // I believe you have my ID
        $new_set_id = $db -> insert_id();

        // Log it!
        log_admin_action("langs", "newlanguage", "Created new language: ".trim($_POST['langname']));

        // Make the directory
        if(!mkdir(ROOT."languages/lang_id".$new_set_id, 0777))
        {
                $output -> add($template_admin -> critical_error($lang['language_dir_error']));
                page_main();
                return;
        }
        
        // --------------------
        // Sort out inherited phrases
        // --------------------        
        
        // Steal the phrases from the database
        $phrases_query = $db -> query("select * from ".$db -> table_prefix."language_phrases where language_id='".$post_id."' order by `group`,variable_name");

        // ----------------
        // Stick the inherited phrases in the DB
        // ----------------
        $repalce_sql = "";
        
        while($phrase_array = $db -> fetch_array($phrases_query))
        {

/*
                // Insert the phrase!
                $info = array(
                        "language_id" => $new_set_id,
                        "variable_name" => $phrase_array['variable_name'],
                        "group" => $phrase_array['group'],
                        "text" => $phrase_array['text'],
                        "default_text" => $phrase_array['text']
                );

                if(!$db -> basic_insert("language_phrases", $info))
                {
                        $output -> add($template_admin -> critical_error($lang['language_phrase_sql_error']));
                        page_main();
                        return;
                }            
*/                
                // Build big replace query, too many individual inserts broke it.
                $info = array(
                        "'".$phrase_array['variable_name']."'",
                        "'".$new_set_id."'",
                        "'".$phrase_array['group']."'",
                        "'".$db -> escape_string($phrase_array['text'])."'",
                        "'".$db -> escape_string($phrase_array['text'])."'",
                );
                
                $replace_sql .= "(". implode(", ", $info) .")";
                
                if(strlen($replace_sql) > 100000)
                {
                
                        if(!$db -> query("REPLACE INTO ".$db -> table_prefix."language_phrases(`variable_name`, `language_id`, `group`, `text`, `default_text`) VALUES ".$replace_sql))
                        {
                                $output -> add($template_admin -> critical_error($lang['language_phrase_sql_error']));
                                page_main();
                                return;                        
                        }
                        
                        $replace_sql = "";
                
                }
                else
                        $replace_sql .= ", ";

        
        }

        // anything we forgot?        
        if($replace_sql)
        {

                if(!$db -> query("REPLACE INTO ".$db -> table_prefix."language_phrases(`variable_name`, `language_id`, `group`, `text`, `default_text`) VALUES "._substr($replace_sql, 0, -2)))
                {
                        $output -> add($template_admin -> critical_error($lang['language_phrase_sql_error']));
                        page_main();
                        return;                        
                }

        }
        
        // ----------------
        // Create the files
        // ----------------
        // Update cache
        $cache -> update_cache("languages");
        
        // Build the new files
        build_language_files($new_set_id);        

        $output -> redirect(ROOT."admin/index.php?m=langs", $lang['language_created_sucessfully']);

}



//***********************************************
// Page to edit a language
//***********************************************
function page_edit_language()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // Grab the set
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select * from ".$db -> table_prefix."languages where id='".$get_id."'");
        $language_array = $db -> fetch_array($language);
        
        // Die if it doesn't exist
        if($db -> num_rows($language) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_languages_id']));
                page_main();
                return;
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['edit_language_title'];

	$output -> add_breadcrumb($lang['breadcrumb_languages_edit'], "index.php?m=langs&amp;m2=edit&amp;id=".$get_id);
        
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
                $form -> start_form("editlanguage", ROOT."admin/index.php?m=langs&amp;m2=doeditlanguage&amp;id=".$language_array['id'], "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['edit_language_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['edit_language_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Form entries
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['edit_language_name'], "50%"),
                                array($form -> input_text("name", $language_array['name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_language_short_name'], "50%"),
                                array($form -> input_text("short_name", $language_array['short_name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_language_charset'], "50%"),
                                array($form -> input_text("charset", $language_array['charset']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_language_author'], "50%"),
                                array($form -> input_text("author", $language_array['author']), "50%")
                        )
                , "normalcell").
                $table -> add_basic_row($form -> submit("submit", $lang['admin_edit_language_submit']), "strip3",  "padding : 5px").
                // ---------------
                // Finish off
                // ---------------
                $table -> end_table().
                $form -> end_form()
        );

}


//***********************************************
// Editing a language
//***********************************************
function do_edit_language()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ----------------
        // Grab the set
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select * from ".$db -> table_prefix."languages where id='".$get_id."'");
        $language_array = $db -> fetch_array($language);
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }

        // ----------------
        // Do the query
        // ----------------
        $info = array(
                "name" => $_POST['name'],
                "author" => $_POST['author'],
                "short_name" => $_POST['short_name'],
                "charset" => $_POST['charset']
        );                        

        if (!$db -> basic_update("languages", $info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_language']));
                page_main();
                return;
        }

        // Update cache
        $cache -> update_cache("languages");

        // Log it!
        log_admin_action("langs", "doeditlang", "Edited language: ".$language_array['name']);

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=langs&amp;m2=editlanguage&amp;id=".$get_id, $lang['language_updated_sucessfully']);

}


//***********************************************
// Deleting a language
//***********************************************
function do_delete_language()
{

        global $output, $lang, $db, $template_admin, $cache;


        // ----------------
        // First check if we can delete it
        // ----------------
        $get_id = trim($_GET['id']);

        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id,name from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

	// Default language is?
	$db -> basic_select("config", "value", "name='default_lang'");
	$default_lang = $db -> result();

        // Die if it's set as default
        if($language_array['id'] == $default_lang)
        {
                $output -> add($template_admin -> critical_error($lang['delete_language_default']));
                page_main();
                return;
        }

        // ----------------
        // Sort out database stuff
        // ----------------
        // Remove the phrases in the set
        if(!$db -> basic_delete("language_phrases", "language_id = '".$language_array['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_phrases_in_language']));
                page_main();
                return;
        }

        // Remove the language entry itself
        if(!$db -> basic_delete("languages", "id = '".$language_array['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_language']));
                page_main();
                return;
        }

        // ----------------
        // If someone has this language switch it
        // ----------------
        $info = array("language" => $default_lang);                       

        if(!$db -> basic_update("users", $info))        
        {
                $output -> add($template_admin -> critical_error($lang['error_switching_user_languages']));
                page_main();
                return;
        }

        // ----------------
        // Play with the files
        // ----------------
        // Remove the folder
        foreach(glob(ROOT."languages/lang_id".$language_array['id']."/*.*") as $filename)
        {
                unlink($filename);
        }
        rmdir(ROOT."languages/lang_id".$language_array['id']);

        // Update cache
        $cache -> update_cache("languages");

        // Log it!
        log_admin_action("langs", "deletelanguage", "Removed language: ".$language_array['name']);

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=langs", $lang['deleted_language']);

}



//***********************************************
// Setting a language as default
//***********************************************
function do_default_language()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ----------------
        // First check if we can set it as default
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

	// Default language is?
	$db -> basic_select("config", "value", "name='default_lang'");
	$default_lang = $db -> result();

        // Die if it's set as default
        if($language_array['id'] == $default_lang['default_lang'])
        {
                $output -> add($template_admin -> critical_error($lang['set_default_language_default']));
                page_main();
                return;
        }

        // ----------------
        // Set the language to default
        // ----------------
        $setting = array(
                "value" => $language_array['id']
        );                        

        if(!$db -> basic_update("config", $setting, "name = 'default_lang'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_setting_default_language']));
                page_main();
                return;
        }

        // Update config cache
        $cache -> update_cache("config");

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=langs", $lang['language_set_to_default']);

}



//***********************************************
// Language groups listing
//***********************************************
function page_language_groups()
{

        global $output, $lang, $db, $template_admin;

        $words_per_page = "15";

        // ----------------
        // Language exists?
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id,name from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

        // Title fix
        $lang['language_group_title'] = $output -> replace_number_tags($lang['language_group_title'], array($language_array['name']));

        if($_GET['group'])
                $_POST['group'] = $_GET['group'];                

        $current_group = $_POST['group'];

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['language_group_page_title'];
                
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

	$output -> add_breadcrumb($lang['breadcrumb_languages_groups'], "index.php?m=langs&amp;m2=viewgroup&amp;id=".$get_id);

        if($current_group == "")        
        {
                
                $output -> add(
                        $form -> start_form("langgroupselect", ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;id=".$language_array['id']).
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title
                        // ---------------
                        $table -> add_basic_row($lang['language_group_title'], "strip1",  "", "left", "100%", "2").
                        // ---------------
                        // Main bit
                        // ---------------
                        $table -> add_row(
                                array(
                                        array($lang['language_group_message'],"50%"),
                                        array(language_group_menu('', true),"50%")
                                )
                        , "normalcell").
                        // ---------------
                        // Submit
                        // ---------------
                        $table -> add_basic_row($form->submit("submit", $lang['language_group_go']), "strip3",  "", "center", "100%", "2").
                        $table -> end_table().
                        $form -> end_form()
                );

		// As delveloper we can add a group
		if(defined("DEVELOPER"))
		{

	                $output -> add(
	                        $form -> start_form("newlanggroup", ROOT."admin/index.php?m=langs&amp;m2=newgroup&amp;id=".$language_array['id']).
	                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
				$table -> add_top_table_header($lang['language_group_add'], 2).
				$table -> simple_input_row_text($form, $lang['language_group_name'], "name", "").
				$table -> simple_input_row_text($form, $lang['language_group_shortname'], "shortname", "").
				$table -> add_submit_row($form).
				$table -> end_table().
				$form -> end_form()
			);	
					
		}

        }
        else
        {

		$output -> add_breadcrumb($lang['group_menu_'.$_POST['group']], "index.php?m=langs&amp;m2=viewgroup&amp;id=".$get_id);

                // ----------------
                // Menu!
                // ----------------
                $output -> add(
                        $form -> start_form("langgroupselect", ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;id=".$language_array['id']).
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_basic_row($lang['language_group_title'], "strip1",  "", "left", "100%").
                        $table -> add_basic_row(language_group_menu($_POST['group'], false).$form->submit("submit", $lang['language_group_go']), "normalcell",  "padding:10px;", "left", "100%").
                        $table -> end_table().
                        $form -> end_form()
                );

                if($_POST['limit'] > 1)
                        $limit_sql = " LIMIT ".($_POST['limit'] * $words_per_page - $words_per_page).", ".$words_per_page;
                else
                        $limit_sql = " LIMIT ".$words_per_page;

                // Select wanted words
                $query_words = $db -> query("SELECT id,variable_name,text,default_text from ".$db->table_prefix."language_phrases WHERE language_id='".$_GET['id']."' AND `group`='".$_POST['group']."' ".$limit_sql);

                // None in it
                $word_amount = $db -> num_rows();
                if($word_amount < 1)
                        $output -> add($template_admin -> critical_error($lang['language_group_empty']));
		else
		{
			
	                // Title fix
	                $lang['language_phrases_title'] = $output -> replace_number_tags($lang['language_phrases_title'], array($lang['group_menu_'.$_POST['group']]));
	                
	                // -------------
	                // Print the words
	                // -------------
	                $output -> add(
	                        $form -> start_form("updatewords", ROOT."admin/index.php?m=langs&amp;m2=doupdatephrases&amp;group=".$_POST['group']."&amp;id=".$language_array['id']).
	                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	                        $table -> add_basic_row(
	                                $lang['language_phrases_title']." [ <a href=\"".ROOT."admin/index.php?m=langs&amp;m2=rebuild&amp;group=".$_POST['group']."&amp;id=".$language_array['id']."\">".$lang['rebuild_file']."</a> ]"
	                        , "strip1",  "", "left", "100%")
	                );                
	                
	                while($phrase_array = $db -> fetch_array($query_words))
	                {
	
	                        if(trim($phrase_array['text']) != trim($phrase_array['default_text']))
	                                $revert_link = "
	                                <div style=\"float: right; clear: left;\">
	                                        [
	                                        <a href=\"index.php?m=langs&amp;m2=revert&amp;group=".$_POST['group']."&amp;id=".$language_array['id']."&amp;wordid=".$phrase_array['id']."\" onClick=\"return confirm('".$lang['reset_confirm'].$phrase_array['variable_name']."')\" title=\"".$lang['reset_message']."\">
	                                                ".$lang['reset_value']."</a>
	                                        ]
	                                </div>";
	                        else
	                                $revert_link = "";
	                                
	                        $output -> add(
	                                $table -> add_basic_row($revert_link.$phrase_array['variable_name'], "strip2",  "", "left", "100%").
	                                $table -> add_basic_row($form -> input_textbox("words[".$phrase_array['id']."]", _htmlentities($phrase_array['text'])), "normalcell",  "", "center", "100%")
	                        );
	                                        
	                }
	
	                $output -> add(
	                        $table -> add_basic_row($form -> submit("submit", $lang['save_phrases']), "strip3",  "", "center", "100%").
	                        $table -> end_table().
	                        $form -> end_form()
	                );
	                
	                // -----------
	                // Page links
	                // -----------
	                $db -> query("select count(id) from ".$db->table_prefix."language_phrases WHERE language_id='".$_GET['id']."' AND `group`='".$_POST['group']."'");
	                $total_word_amount = $db -> result();
	                
	                if($total_word_amount > $words_per_page)
	                {
	
	                        $output -> add(
	                                $form -> start_form("pageselect", ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;id=".$language_array['id']).
	                                $form -> hidden("group", $_POST['group']).
	                                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%")
	                        );                
	                
	                        $pages_wanted = ceil($total_word_amount / $words_per_page);
	                
	                        for($a = 1; $a <= $pages_wanted; $a++)
	                                $submit_buttons .= $form -> submit("limit", $a) . " ";
	
	
	                        $output -> add(
	                                $table -> add_basic_row($submit_buttons, "strip3",  "", "center", "100%").
	                                $table -> end_table().
	                                $form -> end_form()
	                        );
	                        
	                }

		}
		
		// ------------
		// As delveloper we can add a phrase
		// ------------
		if(defined("DEVELOPER"))
		{

	                $output -> add(
	                        $form -> start_form("newphrase", ROOT."admin/index.php?m=langs&amp;m2=newphrase&amp;group=".$_POST['group']."&amp;id=".$language_array['id']).
	                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
				$table -> add_top_table_header($lang['language_phrase_add'], 2).
				$table -> simple_input_row_text($form, $lang['language_phrase_add_varname'], "variable_name", "").
				$table -> simple_input_row_textbox($form, $lang['language_phrase_add_text'], "text", "", 3).
				$table -> add_submit_row($form).
				$table -> end_table().
				$form -> end_form()
			);	
					
		}  
		                                             
        }        
        
}



//***********************************************
// Update given phrases
//***********************************************
function do_update_phrases()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Language exists?
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

        // --------------
        // Empty group
        // --------------
        if(!$_GET['group'])
        {
                $output -> add($template_admin -> critical_error($lang['group_not_specified']));
                page_language_groups();
                return;
        }

        $post_words = $_POST['words'];

        // --------------
        // Update each word
        // --------------
        foreach($post_words as $key => $val)
        	$db -> basic_update("language_phrases", array("text" => $val), "id='".$key."' and `group`='".$_GET['group']."' and `language_id`='".$language_array['id']."'");

        // Build the new file
        build_language_files($language_array['id'], $_GET['group']);

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;group=".$_GET['group']."&amp;id=".$language_array['id'], $lang['phrases_updated']);
        
}


//***********************************************
// Revert phrase
//***********************************************
function do_revert_phrases()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Language exists?
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

        if(!$_GET['wordid'])
                die();

    	$db -> basic_update("language_phrases", array("text" => "`default_text`"), "id='".$_GET['wordid']."' AND `group`='".$_GET['group']."' AND `language_id`='".$_GET['id']."'");
               
        // Build the file
        build_language_files($_GET['id'], $_GET['group']);

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;group=".$_GET['group']."&amp;id=".$language_array['id'], $lang['phrase_reverted']);

}



//***********************************************
// Search form
//***********************************************
function page_search_phrases()
{

        global $output, $lang, $db, $template_admin;


        // Grab all languages
        $languages = $db -> query("select id,name from ".$db -> table_prefix."languages order by name");

        // Get amount
        $languages_amount = $db -> num_rows($languages);

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // For dropdown
        $dropdown = array();
        $dropdown_text = array();

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['search_languages_title'];

	$output -> add_breadcrumb($lang['breadcrumb_languages_search'], "index.php?m=langs&amp;m2=search");

        // ----------------
        // TEH FORM LOL
        // ----------------
        $output -> add(
                $form -> start_form("search", ROOT."admin/index.php?m=langs&amp;m2=dosearch").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['search_languages_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['search_languages_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Input
                // ---------------
                $table -> add_row(array($lang['search_for'],$form -> input_text("search_for", "")), "normalcell")
        );

        // Go through all sets
        while($language_array = $db -> fetch_array($languages))
        {
                $dropdown[] .= $language_array['id'];
                $dropdown_text[] .= $language_array['name'];
        }

        $output -> add(
                $table -> add_row(array($lang['search_in_which_language'],$form -> input_dropdown("search_language", "", $dropdown, $dropdown_text)), "normalcell").
                $table -> add_row(
                        array(
                                $lang['search_in_what'],
                                $form -> input_dropdown("search_what", "", 
                                        array("0", "1", "2"),
                                        array($lang['search_phrase_only'], $lang['search_variable_only'], $lang['search_both'])
                                )
                        )
                , "normalcell").
                $table -> add_row(array($lang['search_case_sensitive'],$form -> input_yesno("search_case", "0")), "normalcell").
                $table -> add_basic_row($form -> submit("submit", $lang['search_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form
        );
        
}



//***********************************************
// Searching here OKAY
//***********************************************
function do_search_phrases()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Input given?
        // ----------------
        if(!trim($_POST['search_for']))
        {
                $output -> add($template_admin -> normal_error($lang['error_no_search_term']));
                page_search_phrases();
                return;
        }


        // ----------------
        // Language exists?
        // ----------------
        $get_id = trim($_POST['search_language']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_search_phrases();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_search_phrases();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

        // -------------
        // Build query
        // -------------
        // case sensitive?
        if($_POST['search_case'])
                $binary_reading_goose = " BINARY ";
        else
                $binary_reading_goose = "";

        // What in please what
        switch($_POST['search_what'])
        {
                case "0":
                        $search_for = " ".$binary_reading_goose."`text` LIKE '%".$_POST['search_for']."%'";
                        break;  
                              
                case "1":
                        $search_for = " ".$binary_reading_goose."`variable_name` LIKE '%".$_POST['search_for']."%'";
                        break;  
                              
                case "2":
                        $search_for = " ".$binary_reading_goose."`text` LIKE '%".$_POST['search_for']."%' OR ".$binary_reading_goose."`variable_name` LIKE '%".$_POST['search_for']."%'";
                        break;        
        }
        
        // Execute the query...
        $db -> query("SELECT `id`,`text`,`variable_name` FROM ".$db->table_prefix."language_phrases WHERE ".$search_for." AND `language_id`='".$language_array['id']."' ORDER BY `group`");

        // -------------
        // Have some?!
        // -------------
        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin ->normal_error($lang['search_phrases_empty']));
                page_search_phrases();
                return;
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['search_languages_title'];

	$output -> add_breadcrumb($lang['breadcrumb_languages_search_results'], "index.php?m=langs&amp;m2=search");

        // -------------
        // Post some frigging results
        // -------------
        $table = new table_generate;
        
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['search_languages_title'], "strip1",  "", "left", "100%", "2")
        );
        
        while($search_array = $db -> fetch_array())
        {

                $text = _htmlentities($search_array['text']);
                $variable = $search_array['variable_name'];
                
                if($_POST['search_what'] == "0" || $_POST['search_what'] == "2")
                {
                        if($_POST['search_case'])
                                $text = preg_replace('@'.preg_quote($_POST['search_for']).'@', "<b>".$_POST['search_for']."</b>", $text);
                        else
                                $text = preg_replace('@'.preg_quote($_POST['search_for']).'@i', "<b>".$_POST['search_for']."</b>", $text);
                }
                
                if($_POST['search_what'] == "1" || $_POST['search_what'] == "2")
                {
                        if($_POST['search_case'])
                                $variable = preg_replace('@'.preg_quote($_POST['search_for']).'@', "<b>".$_POST['search_for']."</b>", $search_array['variable_name']);
                        else
                                $variable = preg_replace('@'.preg_quote($_POST['search_for']).'@i', "<b>".$_POST['search_for']."</b>", $search_array['variable_name']);
                }
                 
                $output -> add(
                        $table -> add_row(
                                array(
                                        $variable."<br />[ <a href=\"".ROOT."admin/index.php?m=langs&amp;m2=editsingle&amp;langid=".$language_array['id']."&amp;wordid=".$search_array['id']."\">".$lang['search_edit']."</a> ]",
                                        $text
                                )
                        , "normalcell")
                );
        
        }

        $output -> add(
                $table -> end_table()
        );
        
}



//***********************************************
// Page to play with one phrase. Yes. PLAY.
//***********************************************
function page_edit_single_phrase()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // Language exists?
        // ----------------
        $get_id = trim($_GET['langid']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

        // ----------------
        // Phrase exists?
        // ----------------
        $get_id = trim($_GET['wordid']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_phrase_id']));
                page_main();
                return;
        }
                
        // Grab wanted phrase
        $phrase = $db -> query("select `id`,`variable_name`,`text`,`group` from ".$db -> table_prefix."language_phrases where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($phrase) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_phrase_id']));
                page_main();
                return;
        }
        else
                $phrase_array = $db -> fetch_array($phrase);

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['edit_single_phrase_title'];

	$output -> add_breadcrumb($lang['breadcrumb_languages_edit_single'], "index.php?m=langs&amp;m2=editsingle&amp;langid=".$language_array['id']."&amp;wordid=".$phrase_array['id']);

        // --------------
        // Form itself
        // --------------
        // sort title
        $output -> replace_number_tags($lang['edit_single_phrase_title'], array($phrase_array['variable_name']));
        
        // Generate form
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("updatewords", ROOT."admin/index.php?m=langs&amp;m2=doupdatephrases&amp;group=".$phrase_array['group']."&amp;id=".$language_array['id']).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['edit_single_phrase_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array($lang['edit_single_phrase_text'], $form -> input_textbox("words[".$phrase_array['id']."]", _htmlentities($phrase_array['text']), 10))
                , "normalcell").
                $table -> add_basic_row($form -> submit("submit", $lang['save_phrases']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );                

}



//***********************************************
// Rebuild one or more of the php language files
//***********************************************
function do_rebuild_file()
{

        global $output, $lang, $db, $template_admin;

        // ----------------
        // Language exists?
        // ----------------
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
                
        // Grab wanted language
        $language = $db -> query("select id from ".$db -> table_prefix."languages where id='".$get_id."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($language) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        else
                $language_array = $db -> fetch_array($language);

        // Check the directory
        if(!is_dir(ROOT."languages/lang_id".$language_array['id']))
        {
                if(!mkdir(ROOT."languages/lang_id".$language_array['id'], 0777))
                {
                        $output -> add($template_admin -> critical_error($lang['language_dir_error']));
                        page_main();
                        return;
                }
        }
                
        // Build the file(s)
        if($_GET['group'] == "ALL")
        {
                build_language_files($language_array['id']);
                // Redirect the user
                $output -> redirect(ROOT."admin/index.php?m=langs", $lang['file_rebuilt']);
        }
        else
        {
                build_language_files($language_array['id'], $_GET['group']);
                // Redirect the user
                $output -> redirect(ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;group=".$_GET['group']."&amp;id=".$language_array['id'], $lang['file_rebuilt']);
        }
        
}


//***********************************************
// Import/Export form
//***********************************************
function page_import_export()
{

        global $output, $lang, $db;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_menu_language_import'];

	$output -> add_breadcrumb($lang['breadcrumb_languages_importexport'], "index.php?m=langs&amp;m2=importexport");

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // Grab all languages
        $languages = $db -> query("select id,name from ".$db -> table_prefix."languages order by name");
        
        $languages_dropdown[] .= "-1";
        $languages_dropdown_text[] .= $lang['all_languages_dropdown'];
        
        // Go through all languages
        while($language_array = $db -> fetch_array($languages))
        {
                // Add to dropdown arrays
                $languages_dropdown[] .= $language_array['id'];
                $languages_dropdown_text[] .= $language_array['name'];
        }
        
        // ----------------
        // EXPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("exportlanguages", ROOT."admin/index.php?m=langs&amp;m2=doexport", "post", false, true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['export_languages_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['export_languages_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Export form
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['export_filename']."<br /><font class=\"small_text\">".$lang['export_filename_message']."</font>","50%"),
                                array($form -> input_text("filename", "fsboard-languages.xml"),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['export_which_language']."<br /><font class=\"small_text\">".$lang['export_which_language_message']."</font>","50%"),
                                array($form -> input_dropdown("language", "", $languages_dropdown, $languages_dropdown_text),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['export_languages']).$form->reset("reset", $lang['export_languages_reset']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

        // ----------------
        // IMPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("importlanguages", ROOT."admin/index.php?m=langs&amp;m2=doimport", "post", true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['import_languages_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['import_languages_message'], "normalcell",  "padding : 5px", "left", "100%", "2").
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
                                array($form -> input_text("filename", "includes/fsboard-languages.xml"),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['import_languages']).$form->reset("reset", $lang['import_languages_reset']), "strip3",  "", "center", "100%", "2").
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
        if($_POST['language'] > "-1")
	        $single_id = 'where id = "'.$_POST['language'].'"';
		else
			$single_id = "";               

        // Select the language(s)
        $select_languages = $db -> query("select * from ".$db -> table_prefix."languages ".$single_id);

        if($db -> num_rows($select_languages) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['getting_languages_error']));
                page_import_export();
                return;
        }
 
 
        // *************************
        // Start XML'ing
        // *************************
        $xml = new xml;
        $xml -> export_xml_start();
        $xml -> export_xml_root("languages_file");

        // *************************
        // Spin through sets
        // *************************
        while($language_array = $db -> fetch_array($select_languages))
        {
        
                // *************************
                // Start off the language
                // *************************
                $xml -> export_xml_start_group(
                        "language",
                        array(
                                "name"              => $language_array['name'],
                                "short_name"        => $language_array['short_name'],
                                "author"            => $language_array['author'],
                                "charset"           => $language_array['charset'],
                                "allow_user_select" => $language_array['allow_user_select'],
                                "direction"         => $language_array['direction']
                        )
                );

                // *************************
                // Select the phrases in this language
                // *************************
                $select_phrases = $db -> query("select * from ".$db -> table_prefix."language_phrases where language_id = \"".$language_array['id']."\" order by `group`,variable_name");

                if($db -> num_rows($select_phrases) > 0)
                {

                        while($phrase_array = $db -> fetch_array($select_phrases))
                        {

                                // Add the parameters entry
                                $xml -> export_xml_add_group_entry(
                                        "phrases",
                                        array(
                                                'variable_name' => $phrase_array['variable_name'],
                                                'group' => $phrase_array['group']
                                        ),
                                        $phrase_array['text']
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
                $filename = "fsboard-languages.xml";
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
        $get_error = import_languages_xml($xml_contents);

        // If we have version mismatch
        if((string)$get_error == "VERSION")
        {
                $output -> add($template_admin -> critical_error($lang['xml_version_mismatch']));
                return false;
        }
                
        $output -> add($template_admin -> message($lang['import_done_title'], $lang['import_done_message']));

}



//***********************************************
// Create a new language group
//***********************************************
function do_new_language_group()
{

        global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
			"name" 		=> $_POST['name'],
			"shortname" 	=> $_POST['shortname'],
			"id"		=> $_GET['id']
		);

	$input = array_map("trim", $input);
	
	// Check language
	if($db -> query_check_id_rows("languages", $input['id'], "id") < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }
        
        
	// Empty?
	if($input['name'] == "" || $input['shortname'] == "")
        {
                $output -> add($template_admin -> normal_error($lang['new_language_group_input']));
                page_language_groups();
                return;
        }
        

        // Check the directory
        if(!is_dir(ROOT."languages/lang_id".$input['id']))
        {
                if(!mkdir(ROOT."languages/lang_id".$input['id'], 0777))
                {
                        $output -> add($template_admin -> critical_error($lang['language_dir_error']));
                        page_main();
                        return;
                }
        }
                

	// Try inputting
	$group_data = array("short_name" => $input['shortname']);
	
	if(!$db -> basic_insert("language_groups", $group_data))
        {
                $output -> add($template_admin -> normal_error($lang['new_language_group_error_insert']));
                page_language_groups();
                return;
        }
        
	
	// Create the phrase
	$phrase_data = array(
		"language_id" 	=> $input['id'],
		"variable_name" => "group_menu_".$input['shortname'],
		"group"		=> "admin_languages",
		"text"		=> $input['name'],
		"default_text"	=> $input['name']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
        {
                $output -> add($template_admin -> normal_error($lang['new_language_group_error_phrase']));
                page_language_groups();
                return;
        }
	
        build_language_files($input['id'], "admin_languages");        	

        $output -> redirect(ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;id=".$input['id'], $lang['new_language_group_done']);

}


//***********************************************
// Create a new phrase
//***********************************************
function do_new_language_phrase()
{

        global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
			"variable_name"	=> $_POST['variable_name'],
			"text"		=> $_POST['text'],
			"lang_id"	=> $_GET['id'],
			"group"		=> $_GET['group']
		);

	$input = array_map("trim", $input);
	
	// Check language
	if($db -> query_check_id_rows("languages", $input['lang_id'], "id") < 1)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_language_id']));
                page_main();
                return;
        }

        
	// Empty?
	if($input['variable_name'] == "" || $input['text'] == "" || $input['group'] == "")
        {
                $output -> add($template_admin -> normal_error($lang['new_language_phrase_input']));
                page_language_groups();
                return;
        }
        
	// Create the phrase
	$phrase_data = array(
		"language_id" 	=> $input['lang_id'],
		"variable_name" => $input['variable_name'],
		"group"		=> $input['group'],
		"text"		=> $input['text'],
		"default_text"	=> $input['text']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
        {
                $output -> add($template_admin -> normal_error($lang['new_language_phrase_error']));
                page_language_groups();
                return;
        }
	
        build_language_files($input['lang_id'], $input['group']);        	

        $output -> redirect(ROOT."admin/index.php?m=langs&amp;m2=viewgroup&amp;group=".$input['group']."&amp;id=".$input['lang_id'], $lang['new_language_phrase_done']);
        
}

?>
