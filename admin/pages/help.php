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
 * Help displayer
 * 
 * Takes a few attributes and displays help documentation accordingly.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 * 
 * @started 26th Feb 2007
 * @edited 01st Feb 2008
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



//***********************************************
// Get the language file...
//***********************************************
load_language_group("admin_area_help");


//***********************************************
// ...and functions please!
//***********************************************
include ROOT."admin/common/funcs/help.funcs.php";


//***********************************************
// Bye
//***********************************************
$output -> show_breadcrumb = false;


$_GET['m2'] = (isset($_GET['m2'])) ? $_GET['m2'] : "show";
$secondary_mode = $_GET['m2'];


switch($secondary_mode)
{

	case "show":
		page_show_help();
		break;

	// ---------
	// Dev stuff
	// ---------
	case "devview":
		page_developer_view();
		break;

	case "devadd":
		page_developer_add_edit(true);
		break;

	case "devdoadd":
		do_developer_add();
		break;

	case "devedit":
		page_developer_add_edit();
		break;

	case "devdoedit":
		do_developer_edit();
		break;
		
	case "devdodelete":
		do_developer_delete();
		break;

	case "devexport":
		page_developer_export();
		break;

	case "devdoexport":
		do_developer_export();
		break;

}


/*
 * Shows up the help documentation.  
 */
function page_show_help()
{
	
	global $db, $lang, $output, $template_admin;

	if(defined("DEVELOPER"))
		add_dev_menu();

	$output -> page_title = $lang['admin_help_title']; 

	$page = $_GET['page'];
	$action = $_GET['action'];
	$field = $_GET['field'];

	// No page
	if(!$page)
	{
                $output -> add($template_admin -> critical_error($lang['admin_help_page_not_defined']));
                return;
	}
	
	// Select what we want
	$db -> query($db -> special_queries -> query_get_help($page, $action, $field));
	
	$help_amount = $db -> num_rows();
	
	// Didn't find any
	if($help_amount < 1)
	{
                $output -> add($template_admin -> critical_error($lang['admin_help_no_pages_found']));
                return;
	}

	$normal = array();
	$general = array();
	
	// Go through them all and save normal or general
	while($help_page = $db -> fetch_array())
	{
		
		if($help_page['action'])
			$normal[] = $help_page; 
		else
			$general[] = $help_page;
			
	}

	$table = new table_generate;

	// More than one? table of contents please.
	if($help_amount > 1)
	{

		$output -> add(
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center").
	                $table -> add_basic_row($lang['admin_help_toc_title'], "strip1", "", "left")
		);		

		if(count($normal) > 0)
		{

			$output -> add(
		                $table -> add_basic_row($lang['admin_help_normal_topics_title'], "strip2", "", "left")
			);		
			
			foreach($normal as $help)
			{

				$lang_var = get_help_lang_var($help['page'], $help['action'], $help['field']);

				$output -> add(
			                $table -> add_basic_row("<a href=\"#".$help['id']."\">".$lang[$lang_var."_title"]."</a>", "normalcell", "padding:4px;", "left")
				);
						
			}
			
		}
		
		if(count($general) > 0)
		{

			$output -> add(
		                $table -> add_basic_row($lang['admin_help_general_topics_title'], "strip2", "", "left")
			);		

			foreach($general as $help)
			{
				$lang_var = get_help_lang_var($help['page'], $help['action'], $help['field']);

				$output -> add(
			                $table -> add_basic_row("<a href=\"#".$help['id']."\">".$lang[$lang_var."_title"]."</a>", "normalcell", "padding:4px;", "left")
				);		
			}
			
		}

		$output -> add($table -> end_table());
		
	}	


	// Normal topics 
	if(count($normal) > 0)
	{

		$output -> add($table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center"));

		if($help_amount > 1)
			$output -> add($table -> add_basic_row($lang['admin_help_normal_topics_title'], "strip1", "", "left"));
			
		foreach($normal as $help_page)
		{
		
			$lang_var = get_help_lang_var($help_page['page'], $help_page['action'], $help_page['field']);
	
			$output -> add(
		                $table -> add_basic_row("<a id=\"".$help_page['id']."\"></a>".$lang[$lang_var."_title"], "strip2", "", "left").
		                $table -> add_basic_row($lang[$lang_var."_text"], "normalcell", "", "left")
			);
			
		}

		$output -> add($table -> end_table());
				
	}
			
	// General topics 
	if(count($general) > 0)
	{

		$output -> add(
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center").
	                $table -> add_basic_row($lang['admin_help_general_topics_title'], "strip1", "", "left")
		);
			
		foreach($general as $help_page)
		{
		
			$lang_var = get_help_lang_var($help_page['page'], $help_page['action'], $help_page['field']);

			$output -> add(
		                $table -> add_basic_row("<a id=\"".$help_page['id']."\"></a>".$lang[$lang_var."_title"], "strip2", "", "left").
		                $table -> add_basic_row($lang[$lang_var."_text"], "normalcell", "", "left")
			);
			
		}
		
		$output -> add($table -> end_table());
				
	}
	
}


/*
 * For editing the help docs
 */
function page_developer_view()
{

	global $db, $lang, $output, $template_admin;

	if(!defined("DEVELOPER"))
		return;
	
	add_dev_menu();

	$output -> page_title = $lang['admin_help_title']; 

	$table = new table_generate;

	$output -> add(
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
		$table -> add_top_table_header($lang['help_dev_edit_title'], 4).
		$table -> add_row(
			array(
				$lang['help_dev_edit_action'],
				$lang['help_dev_edit_field'],
				$lang['help_dev_edit_name'],
				$lang['help_dev_edit_actions']
			),
			"strip2"
		)
	);

	// ************************
	// Select the help docs
	// ************************
	$db -> basic_select("admin_area_help", "*", "", "page, `action`, `order`");

	if($db -> num_rows() < 1)
	{
		
		$output -> add(
			$table -> add_basic_row($lang['help_dev_edit_empty'], "normalcell").
			$table -> end_table()
		);
		return;
		
	}

	// ************************
	// Go through them all
	// ************************
	$current_help_page = "";
	
	while($help_doc = $db -> fetch_array())
	{

                // Check if we want to put up the page header
                if($current_help_page != $help_doc['page'])
			$output -> add($table -> add_basic_row("<b>".$help_doc['page']."</b>", "normalcell", "padding : 10px;"));
		
		$current_help_page = $help_doc['page'];
		
		$lang_var = get_help_lang_var($help_doc['page'], $help_doc['action'], $help_doc['field']);
		
		$td_stuff = array(
			$help_doc['action'],
			$help_doc['field'],
			$lang[$lang_var.'_title'],
			"<a href=\"".ROOT."admin/index.php?m=help&amp;m2=devedit&amp;id=".$help_doc['id']."\">".$lang['help_dev_edit_edit']."</a> - ".
			"<a href=\"".ROOT."admin/index.php?m=help&amp;m2=devdodelete&amp;id=".$help_doc['id']."\" onclick=\"return confirm('".$lang['help_dev_delete_confirm']."')\">".$lang['help_dev_edit_delete']."</a>"						
		);
		
		$output -> add($table -> add_row($td_stuff, "normalcell"));
		
	}

	$output -> add(
		$table -> end_table()
	);
	
}


/*
 * Form for edting and adding help docs
 */
function page_developer_add_edit($adding = false, $help_info = "")
{

	global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
		return;
		
	$output -> page_title = $lang['admin_help_title']; 

	add_dev_menu();
	
    // Create classes
    $table = new table_generate;
	$form = new form_generate;

    // ***************************
	// Need different headers
	// ***************************
	if($adding)
	{

		$output -> add(
			$form -> start_form("addhelp", ROOT."admin/index.php?m=help&amp;m2=devdoadd").
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
			$table -> add_top_table_header($lang['admin_dev_add_form_title'], 2)
		);

		if($help_info === "")
			$help_info = array(
				"page" 		=> "",
				"action" 	=> "",
				"field" 	=> "",
				"order" 	=> "",
				"title" 	=> "",
				"text" 		=> ""
			);
			
	}
	else
	{

		// Grab the help entry
		$get_id = trim($_GET['id']);

		if($db -> query_check_id_rows("admin_area_help", $get_id) < 1)
		{
			$output -> add($template_admin -> critical_error($lang['admin_dev_edit_form_error']));
			page_developer_view();
			return;
		}		
        	
		$help_info = $db -> fetch_array();
	
		$output -> add(
			$form -> start_form("edithelp", ROOT."admin/index.php?m=help&amp;m2=devdoedit&amp;id=".$get_id).
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
			$table -> add_top_table_header($lang['admin_dev_edit_form_title'], 2)
		);
                
		$lang_var = get_help_lang_var($help_info['page'], $help_info['action'], $help_info['field']);
		$help_info['title'] = $lang[$lang_var.'_title'];
		$help_info['text'] =  _htmlentities($lang[$lang_var.'_text']);
		                
	}

	$output -> add(
		$table -> simple_input_row_text($form, $lang['admin_dev_add_form_page'], "page", $help_info['page']).
		$table -> simple_input_row_text($form, $lang['admin_dev_add_form_action'], "action", $help_info['action']).
		$table -> simple_input_row_text($form, $lang['admin_dev_add_form_field'], "field", $help_info['field']).
		$table -> simple_input_row_int($form, $lang['admin_dev_add_form_order'], "order", $help_info['order']).
		$table -> simple_input_row_text($form, $lang['admin_dev_add_form_doc_title'], "title", $help_info['title']).
		$table -> simple_input_row_textbox($form, $lang['admin_dev_add_form_text'], "text", $help_info['text'], 6).
		$table -> add_submit_row($form).
		$table -> end_table().
		$form -> end_form()		
	);
        	
}


/*
 * Actually adding help docs
 */
function do_developer_add()
{

        global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
		return;

	$input = array(
			"page"		=> $_POST['page'],
			"action"	=> $_POST['action'],
			"field"		=> $_POST['field'],
			"order"		=> intval($_POST['order']),
			"title"		=> $_POST['title'],
			"text"		=> $_POST['text']
		);

	$input = array_map("trim", $input);		

	// ******************
	// Empty?
	// ******************
	if($input['page'] == "")
        {
		$output -> add($template_admin -> normal_error($lang['admin_dev_add_do_input']));
                page_developer_add_edit(true, $input);
	        return;
        }

	// ******************
	// Try inputting
	// ******************
	$help_data = array(
		"page"   => $input['page'],
		"action" => $input['action'],
		"field"  => $input['field'],
		"order"  => $input['order']
	);
	
	if(!$db -> basic_insert("admin_area_help", $help_data))
        {
                $output -> add($template_admin -> normal_error($lang['admin_dev_add_do_fail']));
                page_developer_view();
                return;
        }


	// ******************
	// Create the title phrase
	// ******************
	$phrase_data = array(
		"language_id" 	=> LANG_ID,
		"variable_name" => get_help_lang_var($input['page'], $input['action'], $input['field'])."_title",
		"group"		=> "admin_area_help",
		"text"		=> $input['title'],
		"default_text"	=> $input['title']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
        {
                $output -> add($template_admin -> normal_error($lang['admin_dev_add_do_phrase_fail']));
                page_developer_view();
                return;
        }


	// ******************
	// Create the text phrase
	// ******************
	$phrase_data = array(
		"language_id" 	=> LANG_ID,
		"variable_name" => get_help_lang_var($input['page'], $input['action'], $input['field'])."_text",
		"group"		=> "admin_area_help",
		"text"		=> $input['text'],
		"default_text"	=> $input['text']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
        {
                $output -> add($template_admin -> normal_error($lang['admin_dev_add_do_phrase_fail']));
                page_developer_view();
                return;
        }

	include ROOT."admin/common/funcs/languages.funcs.php";	
        build_language_files(LANG_ID, "admin_area_help");

	// ******************
	// Done
	// ******************
        $output -> redirect(ROOT."admin/index.php?m=help&amp;m2=devview", $lang['admin_dev_add_do_success']);
                  
}


/*
 * Actually editing help docs
 */
function do_developer_edit()
{

        global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
		return;

	$input = array(
			"page"		=> $_POST['page'],
			"action"	=> $_POST['action'],
			"field"		=> $_POST['field'],
			"order"		=> intval($_POST['order']),
			"title"		=> $_POST['title'],
			"text"		=> $_POST['text']
		);

	$input = array_map("trim", $input);		

	// ******************
        // Grab the help entry
	// ******************
        $get_id = trim($_GET['id']);

	if($db -> query_check_id_rows("admin_area_help", $get_id) < 1)
    	{
                $output -> add($template_admin -> critical_error($lang['admin_dev_edit_form_error']));
            	page_developer_view();
                return;
    	}	
        	
	// ******************
	// Empty?
	// ******************
	if($input['page'] == "")
        {
		$output -> add($template_admin -> normal_error($lang['admin_dev_add_do_input']));
                page_developer_add_edit(false, $input);
	        return;
        }

	// ******************
	// Try inputting
	// ******************
	$help_data = array(
		"page"   => $input['page'],
		"action" => $input['action'],
		"field"  => $input['field'],
		"order"  => $input['order']
	);
	
	if(!$db -> basic_update("admin_area_help", $help_data, "id='".$get_id."'"))
        {
                $output -> add($template_admin -> normal_error($lang['admin_dev_edit_do_fail']));
                page_developer_view();
                return;
        }

	// ******************
	// Update the text phrase
	// ******************
	$phrase_data = array("text" => $input['title']);

	if(!$db -> basic_update("language_phrases", $phrase_data, "language_id = '".LANG_ID."' AND variable_name = '".get_help_lang_var($input['page'], $input['action'], $input['field'])."_title' AND `group` = 'admin_area_help'"))
        {
                $output -> add($template_admin -> normal_error($lang['admin_dev_edit_do_phrase_fail']));
                page_developer_view();
                return;
        }

	// ******************
	// Update the text phrase
	// ******************
	$phrase_data = array("text" => $input['text']);

	if(!$db -> basic_update("language_phrases", $phrase_data, "language_id = '".LANG_ID."' AND variable_name = '".get_help_lang_var($input['page'], $input['action'], $input['field'])."_text' AND `group` = 'admin_area_help'"))
        {
                $output -> add($template_admin -> normal_error($lang['admin_dev_edit_do_phrase_fail']));
                page_developer_view();
                return;
        }

	include ROOT."admin/common/functions_languages.php";	
        build_language_files(LANG_ID, "admin_area_help");

	// ******************
	// Done
	// ******************
        $output -> redirect(ROOT."admin/index.php?m=help&amp;m2=devview", $lang['admin_dev_edit_do_success']);
     
}


/*
 * Actually deleting help docs
 */
function do_developer_delete()
{

        global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
		return;

	// ******************
        // Grab the help entry
	// ******************
        $get_id = trim($_GET['id']);

	if($db -> query_check_id_rows("admin_area_help", $get_id) < 1)
    	{
                $output -> add($template_admin -> critical_error($lang['admin_dev_edit_form_error']));
            	page_developer_view();
                return;
    	}
    	
    	$help_info = $db -> fetch_array();

	// ******************
	// Delete phrases
	// ******************
	$db -> basic_delete("admin_area_help", "id = '".$get_id."'");
	
	$db -> basic_delete("language_phrases", "language_id = '".LANG_ID."' AND variable_name = '".get_help_lang_var($help_info['page'], $help_info['action'], $help_info['field'])."_title' AND `group` = 'admin_area_help'");
	$db -> basic_delete("language_phrases", "language_id = '".LANG_ID."' AND variable_name = '".get_help_lang_var($help_info['page'], $help_info['action'], $help_info['field'])."_text' AND `group` = 'admin_area_help'");

	include ROOT."admin/common/functions_languages.php";	
        build_language_files(LANG_ID, "admin_area_help");

	// ******************
	// Done
	// ******************
        $output -> redirect(ROOT."admin/index.php?m=help&amp;m2=devview", $lang['admin_dev_delete_do_success']);

}


/**
 *Exporting the help docs as XML for installer. 
 */
function page_developer_export()
{

	global $db, $lang, $output, $template_admin;

	if(!defined("DEVELOPER"))
		return;
	
	add_dev_menu();

	$output -> page_title = $lang['admin_help_title']; 

	$table = new table_generate;
	$form = new form_generate;

	$output -> add(
		$form -> start_form("exporthelp", ROOT."admin/index.php?m=help&amp;m2=devdoexport", "POST", false, true).
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
		$table -> add_top_table_header($lang['help_dev_export_title'], 2).
		$table -> simple_input_row_text($form, $lang['help_dev_export_filename'], "filename", "fsboard-adminhelp.xml").
		$table -> add_submit_row($form).
		$table -> end_table().
		$form -> end_form()
	);

}


/**
 * Finally exporting
 */
function do_developer_export()
{

	global $db, $lang, $output, $template_admin;

	if(!defined("DEVELOPER"))
		return;
	
        // *************************
        // Select the help files
        // *************************
        $db -> basic_select("admin_area_help", "*", "", "page,`order`");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> critical_error($lang['help_dev_edit_empty']));
                page_developer_export();
                return;
        }

        // *************************
        // Start XML'ing
        // *************************
        $xml = new xml;
        $xml -> export_xml_start();
        $xml -> export_xml_root("admin_help_file");

        // *************************
        // Go through them all
        // *************************
        while($help_array = $db -> fetch_array())
        {

		$attributes = array(
			"page"   => $help_array['page'],
			"action" => $help_array['action'],
			"field"  => $help_array['field'],
			"order"  => $help_array['order'],
		);

                $xml -> export_xml_add_single_entry("help_entry", $attributes);
                                                
        }

        // *************************
        // Finish XML'ing
        // *************************
        $xml -> export_xml_generate();

        // *************************
        // Work out output file name                
        // *************************
        if($_POST['filename'] == '')                
                $filename = "fsboard-adminhelp.xml";
        else
                $filename = $_POST['filename'];
        
        // *************************
        // Chuck the file out
        // *************************
        output_file($xml -> export_xml, $filename, "text/xml");
	
}
?>